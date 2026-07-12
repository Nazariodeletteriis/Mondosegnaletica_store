<?php
/**
 * My Account — elenco ordini.
 *
 * Override di woocommerce/templates/myaccount/orders.php
 *
 * @package mondosegnaletica
 * @version 9.5.0
 *
 * @var bool   $has_orders
 * @var object $customer_orders
 * @var int    $current_page
 */

defined( 'ABSPATH' ) || exit;

$ms_button_class = isset( $wp_button_class ) ? (string) $wp_button_class : '';

do_action( 'woocommerce_before_account_orders', $has_orders );
?>

<?php if ( $has_orders ) : ?>

	<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table account-table">
		<thead>
			<tr>
				<?php foreach ( wc_get_account_orders_columns() as $ms_column_id => $ms_column_name ) : ?>
					<th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $ms_column_id ); ?>">
						<span class="nobr"><?php echo esc_html( $ms_column_name ); ?></span>
					</th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
			<?php
			foreach ( $customer_orders->orders as $ms_customer_order ) :
				$order      = wc_get_order( $ms_customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$item_count = $order->get_item_count() - $order->get_item_count_refunded();
				?>
				<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $order->get_status() ); ?> order">
					<?php
					foreach ( wc_get_account_orders_columns() as $ms_column_id => $ms_column_name ) :
						$ms_is_order_number = 'order-number' === $ms_column_id;
						?>

						<?php if ( $ms_is_order_number ) : ?>
							<th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $ms_column_id ); ?>" data-title="<?php echo esc_attr( $ms_column_name ); ?>" scope="row">
						<?php else : ?>
							<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $ms_column_id ); ?>" data-title="<?php echo esc_attr( $ms_column_name ); ?>">
						<?php endif; ?>

							<?php if ( has_action( 'woocommerce_my_account_my_orders_column_' . $ms_column_id ) ) : ?>
								<?php do_action( 'woocommerce_my_account_my_orders_column_' . $ms_column_id, $order ); ?>

							<?php elseif ( $ms_is_order_number ) : ?>
								<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: numero ordine */ __( 'Vedi ordine numero %s', 'mondosegnaletica' ), $order->get_order_number() ) ); ?>">
									<?php echo esc_html( '#' . $order->get_order_number() ); ?>
								</a>

							<?php elseif ( 'order-date' === $ms_column_id ) : ?>
								<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>">
									<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
								</time>

							<?php elseif ( 'order-status' === $ms_column_id ) : ?>
								<span class="order-status order-status--<?php echo esc_attr( $order->get_status() ); ?>">
									<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
								</span>

							<?php elseif ( 'order-total' === $ms_column_id ) : ?>
								<?php
								echo wp_kses_post(
									sprintf(
										/* translators: 1: totale ordine 2: numero articoli */
										_n( '%1$s per %2$s articolo', '%1$s per %2$s articoli', $item_count, 'mondosegnaletica' ),
										$order->get_formatted_order_total(),
										$item_count
									)
								);
								?>

							<?php elseif ( 'order-actions' === $ms_column_id ) : ?>
								<?php
								$ms_actions = wc_get_account_orders_actions( $order );

								foreach ( $ms_actions as $ms_key => $ms_action ) {
									$ms_aria = empty( $ms_action['aria-label'] )
										/* translators: 1: azione 2: numero ordine */
										? sprintf( __( '%1$s ordine numero %2$s', 'mondosegnaletica' ), $ms_action['name'], $order->get_order_number() )
										: $ms_action['aria-label'];

									printf(
										'<a href="%1$s" class="woocommerce-button%2$s button %3$s" aria-label="%4$s">%5$s</a>',
										esc_url( $ms_action['url'] ),
										esc_attr( $ms_button_class ),
										esc_attr( sanitize_html_class( $ms_key ) ),
										esc_attr( $ms_aria ),
										esc_html( $ms_action['name'] )
									);
								}
								?>
							<?php endif; ?>

						<?php if ( $ms_is_order_number ) : ?>
							</th>
						<?php else : ?>
							</td>
						<?php endif; ?>

					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

	<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
		<nav class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination" aria-label="<?php esc_attr_e( 'Navigazione ordini', 'mondosegnaletica' ); ?>">
			<?php if ( 1 !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr( $ms_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>">
					<?php esc_html_e( 'Precedenti', 'mondosegnaletica' ); ?>
				</a>
			<?php endif; ?>

			<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr( $ms_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>">
					<?php esc_html_e( 'Successivi', 'mondosegnaletica' ); ?>
				</a>
			<?php endif; ?>
		</nav>
	<?php endif; ?>

<?php else : ?>

	<div class="account-empty">
		<span class="account-empty__label"><?php esc_html_e( 'Nessun ordine', 'mondosegnaletica' ); ?></span>
		<p class="account-empty__title"><?php esc_html_e( 'Storico vuoto.', 'mondosegnaletica' ); ?></p>
		<p class="account-empty__text">
			<?php esc_html_e( 'Non risultano ordini registrati su questo account. Il catalogo comprende segnaletica verticale, orizzontale, cantieristica e dispositivi di sicurezza stradale, tutti omologati secondo il Codice della Strada.', 'mondosegnaletica' ); ?>
		</p>
		<a class="btn btn--primary" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php esc_html_e( 'Vai al catalogo', 'mondosegnaletica' ); ?>
		</a>
	</div>

<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
