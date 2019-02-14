<?php

class WC_Gateway_ColtPay extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id = 'coltpay';
        $this->has_fields = true;
        $this->method_title = __('ColtPay', 'coltpay');
        $this->method_description = '<p>' .
            __( 'A payment gateway that sends your customers to ColtPay to pay with cryptocurrency.', 'coltpay' )
            . '</p><p>' .
            sprintf(
                __( 'If you do not currently have a coltpay account, you can set one up here: %s', 'coltpay' ),
                '<a target="_blank" href="https://coltpay.com/">https://coltpay.com/</a>'
            );

        $this->order_button_text = __('Proceed to ColtPay', 'coltpay');

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables.
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->debug = 'yes' === $this->get_option('debug', 'no');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

    }

    public function init_form_fields()
    {
        $page_id_arr = array();
        if ($pages = get_pages()) {
            foreach ($pages as $page) {
                $page_id_arr[$page->ID] = $page->post_title;
            }
        }
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable ColtPay Commerce Payment', 'coltpay'),
                'default' => 'yes',
            ),
            'title' => array(
                'title' => __('Title', 'woocommerce'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                'default' => __('ColtPay', 'coltpay'),
                'desc_tip' => true,
            ),
            'coltpay_description' => array(
                'title' => __('Description', 'woocommerce'),
                'type' => 'textarea',
                'default' => 'You can pay with Bitcoin via ColtPay',
            ),
            'api_key' => array(
                'title' => __('Your ColtPay API Key', 'coltpay'),
                'type' => 'text',
                'default' => '',
                'description' => 'You can find your API Key under Settings >API Reference in your ColtPay account.',
            )

        );
    }

    public function process_payment($order_id)
    {

        $api_key = $this->get_option('api_key');
        if ( empty($api_key) ) return array( 'result' => 'fail' );
        $order = wc_get_order($order_id);
        $order_data = $order->get_data();
        $total_amount = $order->get_total();

        $thankyou_page =  $this->get_return_url($order) ;

        $invoice_url = 'https://api.coltpay.com/api/gateway/checkout?api_key=' . $api_key;

        $items_arr = array();
        foreach ($order->get_items() as $item_id => $item_data) {
            $product = $item_data->get_product();
            $product_name = $product->get_name(); // Get the product name
            $item_quantity = $item_data->get_quantity(); // Get the item quantity
            $item_total = $item_data->get_total(); // Get the item line total

            $items_arr[] = array(
                'name' => $product_name,
                'amount' => $item_quantity,
                'price' => $item_total
            );
        }

        $webhook_url = WC()->api_request_url( 'WC_Gateway_ColtPay' );

        $fields_items = array(
            'amount' => $total_amount,
            'currency' => $order_data['currency'],
            'order_info' => $items_arr,
            'customer_info' => $order_data['billing'],
            'order_id' => $order_id,
            'webhook_url' => admin_url( 'admin-post.php' ),
            'cancel_url' => esc_url_raw($order->get_cancel_order_url_raw()),
            'redirect_url' => $thankyou_page,
        );


        $fields = array(
            'plugin_payload' => json_encode($fields_items),
            'via_plugin' => true
        );

        $remote_args = array(
                'body' => $fields
            );

        $response = wp_remote_post( $invoice_url, $remote_args );

        if ( is_wp_error( $response ) ) {
            return array('result' => 'fail');
        }

        $raw_result = wp_remote_retrieve_body( $response );

        $result_obj = json_decode($raw_result, true);
        if (empty($result_obj) || !isset($result_obj['status']) || $result_obj['status'] != 'OK') {
            return array('result' => 'fail');
        }

        $code = $result_obj['result']['data']['code'];
        $invoice_arr = $result_obj['result']['data'];
        $order->update_meta_data('_coltpay_invoice_id', $code);
        $order->update_meta_data('_coltpay_invoice', $invoice_arr);
        $order->save();

        $url = 'https://payment.coltpay.com' . $result_obj['result']['data']['payment_url'];
        return array(
            'result' => 'success',
            'redirect' => $url
        );
    }

    public function payment_fields()
    {
        $description = $this->get_option('coltpay_description');
        $text = '<p>' . $description . '</p>';
        echo $text;
    }

}