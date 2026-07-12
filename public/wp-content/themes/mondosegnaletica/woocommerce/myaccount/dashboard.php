<?php
/**
 * My Account — pannello iniziale.
 *
 * Override di woocommerce/templates/myaccount/dashboard.php
 *
 * @package mondosegnaletica
 * @version 4.4.0
 *
 * @var WP_User $current_user
 */

defined( 'ABSPATH' ) || exit;

$ms_orders_count = function_exists( 'wc_get_customer_order_count' )
	? (int) wc_get_customer_order_count( $current_user->ID )
	: 0;
?>

<div class="account-dashboard">

	<div class="account-dashboard__intro">
		<p class="account-dashboard__greeting">
			<?php
			/* translators: %s: nome utente */
			printf( esc_html__( 'Benvenuto, %s', 'mondosegnaletica' ), esc_html( $current_user->display_name ) );
			?>
		</p>
		<p class="account-dashboard__meta">
			<?php
			printf(
				/* translators: 1: numero ordini 2: url logout */
				wp_kses(
					_n(
						'%1$s ordine registrato · <a href="%2$s">Esci</a>',
						'%1$s ordini registrati · <a href="%2$s">Esci</a>',
						$ms_orders_count,
						'mondosegnaletica'
					),
					[ 'a' => [ 'href' => [] ] ]
				),
				esc_html( number_format_i18n( $ms_orders_count ) ),
				esc_url( wc_logout_url() )
			);
			?>
		</p>
	</div>

	<div class="account-cards">
		<a class="account-card" href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>">
			<span class="account-card__num">01</span>
			<span class="account-card__title"><?php esc_html_e( 'Ordini', 'mondosegnaletica' ); ?></span>
			<span class="account-card__text"><?php esc_html_e( 'Storico ordini, stato di lavorazione e documenti di trasporto.', 'mondosegnaletica' ); ?></span>
		</a>

		<a class="account-card" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address' ) ); ?>">
			<span class="account-card__num">02</span>
			<span class="account-card__title"><?php esc_html_e( 'Indirizzi', 'mondosegnaletica' ); ?></span>
			<span class="account-card__text"><?php esc_html_e( 'Indirizzo di fatturazione e destinazione della merce in cantiere.', 'mondosegnaletica' ); ?></span>
		</a>

		<a class="account-card" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-account' ) ); ?>">
			<span class="account-card__num">03</span>
			<span class="account-card__title"><?php esc_html_e( 'Dettagli', 'mondosegnaletica' ); ?></span>
			<span class="account-card__text"><?php esc_html_e( 'Dati di accesso, referente e password dell\'account.', 'mondosegnaletica' ); ?></span>
		</a>
	</div>

	<div class="account-empty" style="margin-top:var(--space-8)">
		<span class="account-empty__label"><?php esc_html_e( 'Grandi quantità', 'mondosegnaletica' ); ?></span>
		<p class="account-empty__title"><?php esc_html_e( 'Serve un preventivo dedicato?', 'mondosegnaletica' ); ?></p>
		<p class="account-empty__text">
			<?php esc_html_e( 'Per forniture da 10 pezzi in su applichiamo sconti a fasce. Inviate la richiesta: l\'ufficio commerciale risponde entro un giorno lavorativo.', 'mondosegnaletica' ); ?>
		</p>
		<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/richiedi-preventivo/' ) ); ?>">
			<?php esc_html_e( 'Richiedi preventivo', 'mondosegnaletica' ); ?>
		</a>
	</div>

</div>

<?php
/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );
