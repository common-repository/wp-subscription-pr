<?php
/*
 * Plugin Name: WordPress Subscription Product(WPScribe)
 * Plugin URI: http://www.rpigroup.com/home.htm
 * Description: Create subscription product for WooCommerce Store.
 * Author: vivekrpigroup
 * Author URI: http://www.rpigroup.com/home.htm
 * Version: 1.0.0
 * WC requires at least: 2.2 
 * WC tested up to: 3.4.4
 * Requires PHP: 5.6
 *
 */

/*
Create environment.
*/
 
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
define( 'WPS_MNM_BASE', plugin_basename( __FILE__ ) );
define( 'WPS_MNM_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPS_MNM_URL', plugin_dir_url( __FILE__ ) );
define( 'WPS_MNM_AST', plugin_dir_url( __FILE__ ).'assets/' );
define( 'WPS_MNM_IMG', plugin_dir_url( __FILE__ ).'assets/images' );
define( 'WPS_MNM_CSS', plugin_dir_url( __FILE__ ).'assets/css' );
define( 'WPS_MNM_JS', plugin_dir_url( __FILE__ ).'assets/js' );
define( 'WPS_MNM_INC', plugin_dir_path( __FILE__ ).'core/includes' );
/*
Check if WooCommerce is active or not, and if it isn't, disable Subscriptions product for woocommerce.
*/
 
if(!is_plugin_active( 'woocommerce/woocommerce.php' )) {
	deactivate_plugins( plugin_basename( __FILE__ ) );
	add_action( 'admin_notices', 'woocommerce_not_active_admin_notice' );
}
else{
	//wp_clear_scheduled_hook( 'wps_mnm_subscription_cron' );
	require 'core/admin/wps-mnm-register.php';
	require 'core/admin/wps-mnm-product.php';
	require 'core/admin/wps-mnm-process-order.php';
	require 'core/admin/wps-mnm-metaboxes.php';
	require 'core/admin/wps-mnm-additional-settings.php';
	require 'core/frontend/myaccount/wps-mnm-subscribtion-tab.php';
	require 'core/frontend/wps-mnm-payment-gateway-restriction.php';
	require 'core/admin/wps-mnm-cron.php';
	require 'core/admin/wps-mnm-payment-settings.php';
	require 'core/payment/aurhorize.net/wps-mnm-authorize.net.php';
	require 'core/payment/stripe/wps-mnm-stripe.php';
	require 'core/payment/2checkout/wps-mnm-2checkout.php';
	//require 'core/payment/paypal/wps-mnm-paypal.php';
	register_deactivation_hook (__FILE__, 'wps_mnm_subscription_cron_deactivate');
}

function woocommerce_not_active_admin_notice() {
    echo '<div class="error"><p>Please activate <b>Woocommerce Plugin</b> before you activate <b>WPScribe Plugin</b>.</p></div>';

}

function wps_mnm_subscription_cron_deactivate() {	
	$timestamp = wp_next_scheduled ('wps_mnm_subscription_cron');
	wp_unschedule_event ($timestamp, 'wps_mnm_subscription_cron');
} 


add_action('admin_footer', 'test');
function test(){?>
	<script type="text/javascript">
	jQuery(function($) {
			//alert('ok');
			jQuery("#wps_mnm_subscription_2checkout_price_field").on('keyup',function(){
				var dInput = this.value;
				if(/^\+?(0|[1-9]\d*)$/.test(dInput)){
					jQuery("#_regular_price").val(dInput);
					jQuery("#_sale_price").val('');
					jQuery("#wps_mnm_subscription_stripe_price_field").val('');
				}
				else{
					jQuery("#wps_mnm_subscription_2checkout_price_field").val('');
				}
			});
			jQuery("#wps_mnm_subscription_stripe_price_field").on('keyup',function(){
				var dInput = this.value;
				if(/^\+?(0|[1-9]\d*)$/.test(dInput)){
					jQuery("#_regular_price").val(dInput);
					jQuery("#_sale_price").val('');
					jQuery("#wps_mnm_subscription_2checkout_price_field").val('');
				}
				else{
					jQuery("#wps_mnm_subscription_stripe_price_field").val('');
				}
			});
		});
	</script>
	<?php
		}



?>
