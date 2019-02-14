<?php

if ( isset( $_POST['coltpay_nonce'] ) && wp_verify_nonce( $_POST['coltpay_nonce'], 'save_api_key' ) ) {
    //Form data sent
    $coltpay_api_key = sanitize_text_field($_POST['coltpay_api_key']);
    update_option('coltpay_api_key', $coltpay_api_key);

    ?>
    <div class="updated"><p><strong><?php _e('Options saved.', 'coltpay'); ?></strong></p></div>
    <?php
} else {
    $coltpay_api_key = get_option('coltpay_api_key');
}

?>

<div class="wrap rentmy-admin-wrap">
    <h1><?php _e("ColtPay Settings", 'coltpay'); ?></h1>
    <div class="card rentmy-admin-header">
        <div class="rentmy-admin-pull-left">
            <img src="<?php echo esc_url(plugins_url('../assets/logo.png', __FILE__)); ?>" alt="RentMy" width="200"/>
        </div>
    </div>

    <div class="card">
        <h2><?php _e("1. Connect your coltpay account", 'coltpay'); ?></h2>
        <p class="rentmy-admin-subtitle"><?php _e("Don't have a coltpay account? <a href=\"https://coltpay.com/sign-up\" target=\"_blank\" rel=\"noopener\">Get started for free</a>.", 'coltpay'); ?></p>
        <hr/>
        <form name="rentmy_form" method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="coltpay_api_key"><?php _e("Your ColtPay API Key", 'coltpay'); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="coltpay_api_key" class="regular-text" value="<?php echo $coltpay_api_key; ?>"/>
                        <?php wp_nonce_field( 'save_api_key', 'coltpay_nonce' ); ?>
                        <p class="description"><?php _e("You can find your <b>API Key</b> under <b>Settings >API Reference</b> in your ColtPay account.", 'coltpay'); ?></p>
                    </td>
                </tr>
                </tbody>
            </table>

            <p class="submit">
                <input type="submit" name="Submit" value="<?php _e('Update Options', 'coltpay') ?>" class="button button-primary"/>
            </p>
        </form>
    </div>

    <div class="card">
        <h2><?php _e("2. Add Payment Box", 'coltpay'); ?></h2>
        <p class="bq-admin-subtitle"><?php _e("To get started, simply <b>copy</b> and <b>paste</b> this shortcode to any <b>Page</b> or <b>Post</b>.", 'coltpay'); ?></p>
        <hr/>
        <table class="form-table">
            <tbody>
                <tr>
                    <td>
                        <code>[coltpay-payment id="" class="" amount="" currency=""]</code></code>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
