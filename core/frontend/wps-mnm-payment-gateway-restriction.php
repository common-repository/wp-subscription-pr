<?php
class WPS_MNM_Payment_Gateway_Restriction
{
	public function __construct()
	{		
		add_filter( 'woocommerce_available_payment_gateways', array($this, 'wps_mnm_conditional_payment_gateways'), 10, 1);
		add_filter( 'woocommerce_is_sold_individually', array($this, 'wps_mnm_remove_quantity_field_for_subscription_product' ), 10,2);
		add_filter('woocommerce_create_account_default_checked' , array($this, 'wps_mnm_create_account_default_checked'));
		add_action('wp_footer', array($this, 'wps_mnm_disabled_create_account_checked_box'));

		add_action('woocommerce_before_checkout_form',array($this,'wps_mnm_subscription_alone_in_cart'));
		add_action( 'woocommerce_check_cart_items', array($this,'wps_mnm_subscription_alone_in_cart' ));
	}
	
	public static function wps_mnm_disabled_create_account_checked_box()
	{ ?> 
		 <script type="text/javascript">
			jQuery(function($) {
				$("#createaccount").prop("disabled", true);
				  });
        </script>
        <style> 
		.woocommerce-form__label span {
		display: none;
		}
		.woocommerce-form__label .woocommerce-form__input-checkbox {
			display: none;
		}
		</style>
	<?php
	} 
		
	public static function wps_mnm_create_account_default_checked($checked)
	{
		return true;
	}	
	
	public static function wps_mnm_remove_quantity_field_for_subscription_product( $return, $product) 
	{
		if($product->product_type == 'spwcsubscription' )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	
	public static function wps_mnm_conditional_payment_gateways( $available_gateways ) 
	{
		global $woocommerce;
		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$prod_subscription = false;
			$product = wc_get_product($cart_item['product_id']);
			$prod_id = $cart_item['data']->get_id();
			if($product->is_type('spwcsubscription')) $prod_subscription = true;
		}
		
		
		// Remove  payment gateway for subscription products
		$subscription_arr = array("wps_mnm_auth_net","wps_mnm_stripe","twocheckout");
		if($prod_subscription){
			foreach($available_gateways as $key){
				$key = key($available_gateways);
				if(!in_array($key,$subscription_arr))
				{
					unset($available_gateways[$key]);
				}
			}
			
		}
		/* 4th December*/
		//$prod_id = $cart_item['data']->get_id();
		$method = get_post_meta( $prod_id, 'wps_mnm_subscription_payment_method', true );
		if($method == 'twocheckout')
		{
			unset($available_gateways['wps_mnm_stripe']); 
			unset($available_gateways['wps_mnm_auth_net']); 
			unset($available_gateways['wps_mnm_paypal']); 
		}
		else if($method == 'wps_mnm_stripe')
		{
			unset($available_gateways['wps_mnm_auth_net']); 
			unset($available_gateways['wps_mnm_paypal']); 
			unset($available_gateways['twocheckout']); 
		}
		else if($method == 'wps_mnm_auth_net')
		{
			unset($available_gateways['wps_mnm_paypal']); 
			unset($available_gateways['twocheckout']); 
			unset($available_gateways['wps_mnm_stripe']); 
		}
		else
		{
			unset($available_gateways['twocheckout']); 
			unset($available_gateways['wps_mnm_stripe']);
			unset($available_gateways['wps_mnm_auth_net']); 
		}
		return $available_gateways;
	}

	public static function wps_mnm_subscription_alone_in_cart() {
	    global $woocommerce;
	    $subscription = 0;
	    $other = 0; 
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			
			$product = get_product( $cart_item['data']->id );
			
			if( $product->is_type( 'spwcsubscription' ) ){
				$subscription = 1;
			}
			if( $product->is_type( 'simple' ) || $product->is_type( 'variable' ) || $product->is_type( 'grouped' ) )
			{
				$other = 1;
			}

		}
		if($subscription == 1 && $other == 1){
			wc_add_notice( 'Subscription product is not available for checkout with other products.', 'error' );
		}
	}
	
	
}new WPS_MNM_Payment_Gateway_Restriction;



