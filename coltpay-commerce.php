<?php
/**
 * @wordpress-plugin
 * Plugin Name: Coltpay Commerce
 * Description: A payment gateway that allows your customers to pay with cryptocurrency via Coltpay Commerce
 * Author: Leaping Logic LLC
 * Version: 1.0
 * Author URI: https://leapinglogic.com
 * Copyright: 2019 Coltpay
 */

add_action( 'plugins_loaded', 'rsm_init_coltpay_gateway_class' );
function rsm_init_coltpay_gateway_class() {
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		require_once 'class-wc-gateway-coltpay.php';
		require_once 'class-shortcode-coltpay.php';
		add_filter( 'woocommerce_payment_gateways', 'rsm_add_coltpay_gateway_class' );
		add_action( 'woocommerce_admin_order_data_after_order_details', 'rsm_coltpay_order_meta_general' );
		add_action( 'woocommerce_order_details_after_order_table', 'rsm_coltpay_order_meta_general' );
		add_filter( 'woocommerce_email_order_meta_fields', 'rsm_custom_woocommerce_email_order_meta_fields', 10, 3 );

		add_action( 'woocommerce_api_wc_gateway_coltpay', 'rsm_coltpay_webhook_callback2' );
	}
}

function rsm_add_coltpay_gateway_class( $methods ) {
    $methods[] = 'WC_Gateway_ColtPay'; 
    return $methods;
}

function rsm_coltpay_order_meta_general( $order ) { 
	$invoice = $order->get_meta( '_coltpay_invoice' );

	if ( empty( $invoice ) ) return;
	?>

	<br class="clear" />
	<h3>ColtPay Data</h3>
	<div class="">
		<p>ColtPay Commerce Reference #<br>
			<?php echo __("Invoice: #", 'coltpay') . $invoice['code'] ?><br>
			<?php echo __("Bitcoin address  : ", 'coltpay') . $invoice['address'] ?><br>
			<?php echo __("total   : ", 'coltpay') . $invoice['total'] . __(' BTC', 'coltpay') ?><br>
		</p>
	</div>

	<?php
}

function rsm_custom_woocommerce_email_order_meta_fields( $fields, $sent_to_admin, $order ) {

	$invoice = $order->get_meta( '_coltpay_invoice' );
	if ( empty( $invoice ) ) return $fields;

    $fields['coinbase_commerce_reference'] = array(
        'label' => __( 'Coinbase Commerce Invoice Code #', 'coltpay' ),
        'value' => $invoice['code'],
    );

    return $fields;
}

function rsm_coltpay_webhook_callback2() {

	// validation
	$coltpay_setting = get_option('woocommerce_coltpay_settings');
	$result = '';
    if ( ! isset( $_POST['api_key'] ) || $_POST['api_key'] != $coltpay_setting['api_key'] ) {
    	exit;
    }

	if ( ! isset( $_POST['order_id'] ) ) {
    	exit;
    }

	$order = wc_get_order( $_POST['order_id'] );
	if ( $order === false ) {
    	exit;
    }

	$order->payment_complete( $order->get_id() );
	WC()->cart->empty_cart();
	echo 'OK';
    exit;
}
