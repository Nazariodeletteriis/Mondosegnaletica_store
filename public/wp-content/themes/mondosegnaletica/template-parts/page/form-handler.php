<?php
/**
 * Gestione POST dei form di pagina (contatti, preventivo).
 *
 * Nessun plugin form: nonce WordPress + honeypot + wp_mail().
 * Il template chiama ms_page_form_process( 'contact' | 'quote' ) e riceve:
 *   [ 'status' => 'idle'|'ok'|'error', 'errors' => string[], 'values' => array ]
 *
 * Schema Post/Redirect/Get: l'invio viene elaborato su template_redirect, prima
 * che gli header partano. In caso di successo si redirige in GET, così un
 * refresh della pagina di conferma non rispedisce la mail. Gli errori di
 * validazione restano inline (nessuna mail è partita, il re-POST è innocuo) per
 * poter ristampare i valori già digitati.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'ms_page_form_process' ) ) :

	/**
	 * Campi accettati per contesto: chiave => [ etichetta, tipo ].
	 */
	function ms_page_form_fields( string $context ): array {
		if ( 'quote' === $context ) {
			return [
				'ragione_sociale' => [ 'Ragione sociale', 'text' ],
				'tipo_cliente'    => [ 'Tipologia cliente', 'text' ],
				'piva'            => [ 'P.IVA / Codice Fiscale', 'text' ],
				'referente'       => [ 'Referente', 'text' ],
				'email'           => [ 'Email', 'email' ],
				'telefono'        => [ 'Telefono', 'text' ],
				'categoria'       => [ 'Categoria merceologica', 'text' ],
				'quantita'        => [ 'Quantità stimata', 'text' ],
				'consegna'        => [ 'Consegna richiesta entro', 'text' ],
				'messaggio'       => [ 'Dettaglio richiesta', 'textarea' ],
			];
		}

		return [
			'ragione_sociale' => [ 'Ragione sociale', 'text' ],
			'referente'       => [ 'Referente', 'text' ],
			'email'           => [ 'Email', 'email' ],
			'telefono'        => [ 'Telefono', 'text' ],
			'prodotto'        => [ 'Prodotto di riferimento', 'text' ],
			'messaggio'       => [ 'Messaggio', 'textarea' ],
		];
	}

	/**
	 * Elabora il POST del form e restituisce lo stato.
	 */
	/**
	 * Esito del form per il template. Memoizzato: il POST viene elaborato una volta
	 * sola su template_redirect, poi il template richiama questa funzione in fase di
	 * render — senza cache la mail partirebbe due volte.
	 */
	function ms_page_form_process( string $context ): array {
		static $cache = [];

		if ( ! isset( $cache[ $context ] ) ) {
			$cache[ $context ] = ms_page_form_handle( $context );
		}

		return $cache[ $context ];
	}

	function ms_page_form_handle( string $context ): array {
		$result = [
			'status' => 'idle',
			'errors' => [],
			'values' => [],
		];

		// Ritorno dal redirect di successo: non c'è nessun POST da rielaborare.
		if ( isset( $_GET['ms_inviato'] ) && $context === sanitize_key( wp_unslash( (string) $_GET['ms_inviato'] ) ) ) {
			$result['status'] = 'ok';
			return $result;
		}

		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			return $result;
		}

		$nonce_action = 'quote' === $context ? 'ms_quote_form'  : 'ms_contact_form';
		$nonce_name   = 'quote' === $context ? 'ms_quote_nonce' : 'ms_contact_nonce';

		if (
			empty( $_POST[ $nonce_name ] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST[ $nonce_name ] ) ), $nonce_action )
		) {
			$result['status']   = 'error';
			$result['errors'][] = __( 'Sessione scaduta o richiesta non valida. Ricaricate la pagina e riprovate.', 'mondosegnaletica' );
			return $result;
		}

		// Honeypot: i bot compilano tutto, gli umani non vedono questo campo.
		if ( ! empty( $_POST['ms_hp'] ) ) {
			$result['status'] = 'ok';
			return $result;
		}

		// Raccolta + sanitizzazione.
		$fields = ms_page_form_fields( $context );
		$values = [];

		foreach ( $fields as $key => [ $label, $type ] ) {
			$raw = isset( $_POST[ $key ] ) ? wp_unslash( (string) $_POST[ $key ] ) : '';

			if ( 'email' === $type ) {
				$values[ $key ] = sanitize_email( $raw );
			} elseif ( 'textarea' === $type ) {
				$values[ $key ] = sanitize_textarea_field( $raw );
			} else {
				$values[ $key ] = sanitize_text_field( $raw );
			}
		}

		$result['values'] = $values;

		// Validazione minima lato server.
		if ( '' === $values['ragione_sociale'] ) {
			$result['errors'][] = __( 'Indicate la ragione sociale.', 'mondosegnaletica' );
		}
		if ( '' === $values['email'] || ! is_email( $values['email'] ) ) {
			$result['errors'][] = __( 'Indicate un indirizzo email valido.', 'mondosegnaletica' );
		}

		// Il "required" sul checkbox è solo lato browser: un POST diretto lo salta.
		// Il consenso va verificato qui, altrimenti non è raccolto davvero.
		if ( empty( $_POST['privacy'] ) ) {
			$result['errors'][] = __( 'È necessario acconsentire al trattamento dei dati personali.', 'mondosegnaletica' );
		}

		if ( ! empty( $result['errors'] ) ) {
			$result['status'] = 'error';
			return $result;
		}

		// Composizione email.
		$is_quote = 'quote' === $context;
		$subject  = sprintf(
			'[%1$s] %2$s — %3$s',
			get_bloginfo( 'name' ),
			$is_quote ? 'Richiesta preventivo B2B' : 'Nuova richiesta di contatto',
			$values['ragione_sociale']
		);

		$lines = [];
		foreach ( $fields as $key => [ $label, $type ] ) {
			if ( '' === $values[ $key ] ) {
				continue;
			}
			$lines[] = strtoupper( $label ) . ': ' . $values[ $key ];
		}

		// Riepilogo carrello (solo preventivo, se presente).
		if ( $is_quote && function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() ) {
			$lines[] = '';
			$lines[] = 'CARRELLO IN CORSO:';
			foreach ( WC()->cart->get_cart() as $item ) {
				if ( empty( $item['data'] ) || ! $item['data'] instanceof \WC_Product ) {
					continue;
				}
				$lines[] = sprintf(
					'- %1$s (SKU %2$s) × %3$d',
					$item['data']->get_name(),
					$item['data']->get_sku() ?: 'n/d',
					(int) $item['quantity']
				);
			}
		}

		$lines[] = '';
		$lines[] = '---';
		$lines[] = 'Inviato da: ' . home_url( add_query_arg( [] ) );
		$lines[] = 'Data: ' . wp_date( 'd/m/Y H:i' );

		$to      = (string) get_option( 'admin_email' );
		$headers = [ 'Content-Type: text/plain; charset=UTF-8' ];

		$reply_name = $values['referente'] ?: $values['ragione_sociale'];
		$headers[]  = sprintf( 'Reply-To: %s <%s>', $reply_name, $values['email'] );

		$sent = wp_mail( $to, $subject, implode( "\n", $lines ), $headers );

		if ( ! $sent ) {
			$result['status']   = 'error';
			$result['errors'][] = __( 'Invio non riuscito per un problema tecnico. Scrivete a info@mondosegnaletica.it o telefonate allo 0583 1646327.', 'mondosegnaletica' );
			return $result;
		}

		$result['status'] = 'ok';
		$result['values'] = [];

		return $result;
	}

	/**
	 * Valore da ristampare nel campo dopo un errore di validazione.
	 */
	function ms_page_form_value( array $form, string $key ): string {
		return isset( $form['values'][ $key ] ) ? (string) $form['values'][ $key ] : '';
	}

	/**
	 * Post/Redirect/Get: elabora il POST prima che gli header partano e, se la mail
	 * è andata, redirige in GET. Senza questo, un F5 sulla pagina di conferma
	 * rispedirebbe la richiesta al cliente.
	 */
	function ms_page_form_prg(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			return;
		}

		$context = null;
		if ( isset( $_POST['ms_quote_nonce'] ) ) {
			$context = 'quote';
		} elseif ( isset( $_POST['ms_contact_nonce'] ) ) {
			$context = 'contact';
		}

		if ( ! $context ) {
			return;
		}

		// L'elaborazione (e l'invio mail) avviene qui; il render leggerà il valore memoizzato.
		$result = ms_page_form_process( $context );

		if ( 'ok' !== $result['status'] ) {
			return; // errori inline: nessuna mail partita, il re-POST è innocuo
		}

		wp_safe_redirect(
			add_query_arg( 'ms_inviato', $context, remove_query_arg( 'ms_inviato' ) ) . '#form',
			303
		);
		exit;
	}

	add_action( 'template_redirect', 'ms_page_form_prg' );

endif;
