<?php
/**
 * WooCommerce quantity input override — Sistema Strada custom stepper.
 *
 * @see woocommerce/templates/global/quantity-input.php
 * @version 10.1.0
 */

defined( 'ABSPATH' ) || exit;

$label = ! empty( $args['product_name'] )
	? sprintf( esc_html__( '%s quantità', 'mondosegnaletica' ), wp_strip_all_tags( $args['product_name'] ) )
	: esc_html__( 'Quantità', 'mondosegnaletica' );
?>
<div class="quantity quantity-selector">
	<?php do_action( 'woocommerce_before_quantity_input_field' ); ?>

	<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $label ); ?></label>

	<button type="button" class="qty-btn" data-qty="minus" aria-label="<?php esc_attr_e( 'Riduci quantità', 'mondosegnaletica' ); ?>">
		<span class="material-symbols-outlined" aria-hidden="true">remove</span>
	</button>

	<input
		type="<?php echo esc_attr( $type ); ?>"
		<?php echo $readonly ? 'readonly="readonly"' : ''; ?>
		id="<?php echo esc_attr( $input_id ); ?>"
		class="qty-input <?php echo esc_attr( join( ' ', (array) $classes ) ); ?>"
		name="<?php echo esc_attr( $input_name ); ?>"
		value="<?php echo esc_attr( $input_value ); ?>"
		aria-label="<?php esc_attr_e( 'Quantità', 'mondosegnaletica' ); ?>"
		min="<?php echo esc_attr( $min_value ); ?>"
		<?php if ( 0 < $max_value ) : ?>
			max="<?php echo esc_attr( $max_value ); ?>"
		<?php endif; ?>
		<?php if ( ! $readonly ) : ?>
			step="<?php echo esc_attr( $step ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			inputmode="<?php echo esc_attr( $inputmode ); ?>"
			autocomplete="<?php echo esc_attr( isset( $autocomplete ) ? $autocomplete : 'on' ); ?>"
		<?php endif; ?>
	/>

	<button type="button" class="qty-btn" data-qty="plus" aria-label="<?php esc_attr_e( 'Aumenta quantità', 'mondosegnaletica' ); ?>">
		<span class="material-symbols-outlined" aria-hidden="true">add</span>
	</button>

	<?php do_action( 'woocommerce_after_quantity_input_field' ); ?>
</div>
