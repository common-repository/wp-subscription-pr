<?php

add_action( 'plugins_loaded', 'wps_mnm_woo_payment_gateway' );


function wps_mnm_woo_payment_gateway() {
	//echo 'hi!!';
    class WC_Gateway_Wps_Mnm_Gateway extends WC_Payment_Gateway {
				public $apiContext = null;
				public function __construct() {
				$this->id                 	= 'wps_mnm_auth_net'; 
				$this->method_title       	= __( 'WPScribe Authorize.Net', 'wps-mnm' );  
				$this->method_description 	= __( 'WPScribe Payment Gateway for subscribers', 'wps-mnm' );
				$this->title              	= __( 'WPScribe Authorize.Net', 'wps-mnm' );
				$this->has_fields = true;
				//$this->supports = array( 'products');
				// support default form with credit card
				$this->supports = array( 'default_credit_card_form');

				// Load the settings.
				$this->init_form_fields();
				$this->init_settings();
				
				foreach ( $this->settings as $setting_key => $value ) {
					$this->$setting_key = $value;
				}
				
				$this->enabled 		= $this->get_option('enabled');
				//$client_id =  $this->get_option('api_login');
				//$client_secret =  $this->get_option('trans_key');	
				//add_action( 'check_woopaypal', array( $this, 'check_response') );
				
				// Save settings
				if ( is_admin() ) {
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
				}

			}
		
			/**
			 * Get a field name supports
			 *
			 * @access      public
			 * @param       string $name
			 * @return      string
			 */
			public function field_name( $name ) {
				return $this->supports( 'tokenization' ) ? '' : ' name="' . esc_attr( $this->id . '-' . $name ) . '" ';
			}
		
			/**
			 * Output payment fields, optional additional fields and woocommerce cc form
			 *
			 * @access      public
			 * @return      void
			*/
			public function payment_fields() {
				if ( $this->supports( 'default_credit_card_form' ) && is_checkout() ) {
				$this->form(); // Create Credit Card form
				}
				if ( $this->supports( 'save_cards' ) && is_checkout() && is_user_logged_in()) {
				$this->form2();  // Create Tokenization form
				}

			}
					

			public function form() {
				wp_enqueue_script( 'wc-credit-card-form' );

				$fields = array();

				$cvc_field = '<p class="form-row form-row-last">
					<label for="' . esc_attr( $this->id ) . '-card-cvc">' . esc_html__( 'Card code', 'woocommerce' ) . ' <span class="required">*</span></label>
					<input id="' . esc_attr( $this->id ) . '-card-cvc" name="wpc-mnm-auth-card-cvc" class="input-text wc-credit-card-form-card-cvc" inputmode="numeric" autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" maxlength="4" placeholder="' . esc_attr__( 'CVC', 'woocommerce' ) . '" ' . $this->field_name( 'card-cvc' ) . ' style="width:100px" />
				</p>';
				
				$default_fields = array(
					'card-number-field' => '<p class="form-row form-row-wide">
						<label for="' . esc_attr( $this->id ) . '-card-number">' . esc_html__( 'Card number', 'woocommerce' ) . ' <span class="required">*</span></label>
						<input id="' . esc_attr( $this->id ) . '-card-number" name="wpc-mnm-auth-card-number" class="input-text wc-credit-card-form-card-number" inputmode="numeric" autocomplete="cc-number" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" ' . $this->field_name( 'card-number' ) . ' />
					</p>',
					'card-expiry-field' => '<p class="form-row form-row-first">
						<label for="' . esc_attr( $this->id ) . '-card-expiry">' . esc_html__( 'Expiry (MM/YYYY)', 'woocommerce' ) . ' <span class="required">*</span></label>
						<input id="' . esc_attr( $this->id ) . '-card-expiry" name="wpc-mnm-auth-card-expiry" class="input-text wc-credit-card-form-card-expiry" inputmode="numeric" autocomplete="cc-exp" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="' . esc_attr__( 'MM / YYYY', 'woocommerce' ) . '" ' . $this->field_name( 'card-expiry' ) . ' />
					</p>',
				);

				if ( ! $this->supports( 'credit_card_form_cvc_on_saved_method' ) ) {
					$default_fields['card-cvc-field'] = $cvc_field;
				}

				$fields = wp_parse_args( $fields, apply_filters( 'woocommerce_credit_card_form_fields', $default_fields, $this->id ) );
				?>
				 <input id="payment_method_cc" style="display:none;" class="input-radio" name="wpc_mnm_auth_payment_method" value="wpc_mnm_auth_credit_card" data-order_button_text="" type="radio" checked="checked" onclick="merrcocc()">
				 <!--<label for="payment_method_cc" onclick="merrcocc()"> Credit Card </label>-->
				 <?php 
				  $customer_orders = get_posts( array( 'numberposts' => -1, 'meta_key' => '_customer_user', 'meta_value' => get_current_user_id(), 'post_type' => wc_get_order_types(), 'post_status' => array_keys( wc_get_order_statuses() ), ) );
				//echo '<pre>'; print_r($customer_orders); echo '</pre>';
				$i=0;
				 foreach($customer_orders as $oid) {
						if(metadata_exists('post', $oid->ID, '_merrco_token_card_info')) {
							 $listcard[$i]=get_post_meta($oid->ID,'_merrco_token_card_info');
						}
				$i++;
				 } 

				 if(is_user_logged_in() && !empty($listcard) && $this->saved_cards == "yes")
				 { ?>
				 <input id="payment_method_token" class="input-radio" name="wpc_mnm_auth_payment_method" value="wpc_mnm_auth_token" data-order_button_text="" type="radio" onclick="merrcotoken()">
				 <label for="payment_method_token" onclick="merrcotoken()"> Save Card </label>
				 <?php  }?>
				<fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-cc-form" class='wc-credit-card-form wc-payment-form'>
					<?php do_action( 'woocommerce_credit_card_form_start', $this->id ); ?>
					<?php
						foreach ( $fields as $field ) {
						echo $field;
						}
					?>
					<?php do_action( 'woocommerce_credit_card_form_end', $this->id ); ?>
					<div class="clear"></div>
				</fieldset>
				<?php

				if ( $this->supports( 'credit_card_form_cvc_on_saved_method' ) ) {
					echo '<fieldset>' . $cvc_field . '</fieldset>';
				}
			
			
			}
			
		
		public function init_form_fields() {
				$this->form_fields = array(
				'enabled' => array(
					'title'		=> __( 'Enable / Disable', 'wps-mnm' ),
					'label'		=> __( 'Enable this payment gateway', 'wps-mnm' ),
					'type'		=> 'checkbox',
					'default'	=> 'yes',
				),
				/*'title' => array(
					'title'		=> __( 'Title', 'wps-mnm' ),
					'type'		=> 'text',
					'desc_tip'	=> __( 'Payment title of checkout process.', 'wps-mnm' ),
					'default'	=> __( 'Credit card', 'wps-mnm' ),
				),
				'description' => array(
					'title'		=> __( 'Description', 'wps-mnm' ),
					'type'		=> 'textarea',
					'desc_tip'	=> __( 'Payment title of checkout process.', 'wps-mnm' ),
					'default'	=> __( 'Successfully payment through credit card.', 'wps-mnm' ),
					'css'		=> 'max-width:450px;'
				),*/
				'api_login' => array(
					'title'		=> __( 'Authorize.net API Login', 'wps-mnm' ),
					'type'		=> 'text',
					'desc_tip'	=> __( 'This is the API Login provided by Authorize.net when you signed up for an account.', 'wps-mnm' ),
				),
				'trans_key' => array(
					'title'		=> __( 'Authorize.net Transaction Key', 'wps-mnm' ),
					'type'		=> 'password',
					'desc_tip'	=> __( 'This is the Transaction Key provided by Authorize.net when you signed up for an account.', 'wps-mnm' ),
				),
				'environment' => array(
					'title'		=> __( 'Authorize.net Test Mode', 'wps-mnm' ),
					'label'		=> __( 'Enable Test Mode', 'wps-mnm' ),
					'type'		=> 'checkbox',
					'description' => __( 'This is the test mode of gateway.', 'wps-mnm' ),
					'default'	=> 'no',
				)
			);		
		}
		
		// Response handled for payment gateway
		public function process_payment( $order_id ) {
			if ( $this->send_to_wps_mnm_auth_gateway( $order_id ) ) {
					//$this->order_complete();
					global $woocommerce;
	    		    //echo '|-->'.$order_id; die;
	    		    $order = new WC_Order( $order_id );

					$order->update_status('completed', __( 'Complete', 'woocommerce' ));
		            //wc_reduce_stock_levels($order_id);
			       	//WC()->cart->empty_cart();
		            $result = array(
		                'result' => 'success',
		                'redirect' => $this->get_return_url( $order )
		            );
		            return $result;
		        } else {
		            $this->payment_failed();
		            // Add a generic error message if we don't currently have any others
		           wc_add_notice( __( 'Transaction Error: Could not complete your payment: Please check the Payment Details and try again', 'mer-merrcopayments-aim' ), 'error' );
		      	  return false;
		          }		
		}
		
		 public function send_to_wps_mnm_auth_gateway( $order_id ) {

			 // Include the recurring Authorize . Net payment files.
			//include (WPS_MNM_INC . '/AuthnetARB.class.php') ; 
			require (WPS_MNM_INC . '/AuthnetARB.class.php') ; 
			
			$order = new WC_Order( $order_id );
			$items = $order->get_items(); 
			//echo '<pre>'; print_r($items); 
			$b = array();
			foreach ( $items as $item ) {
			   $var = $item['product_id'];
			   $b[] = $var;
				  
			}
			$product_ids = implode(',',$b);
			//echo $product_ids; die();
			
			$interval_length = get_post_meta( $product_ids, 'wps_mnm_subscription_renew_int', true );
			$interval_unit = get_post_meta( $product_ids, 'wps_mnm_subscription_renew_char', true );

			$total_length 	=  get_post_meta( $product_ids, 'wps_mnm_subscription_total_length_int', true );
			$total_unit 	=  get_post_meta( $product_ids, 'wps_mnm_subscription_total_length_char', true );

			if($interval_unit == 'weeks'){
				$interval_unit = 'week';
			}

			if($total_unit == 'weeks'){
				$total_unit = 'week';
			}
			
			/* Interval  transfer to days Start */
			if($interval_unit == 'days'){
				$new_tot_day = $interval_length;
			}
			elseif($interval_unit =='week' ){
				$new_tot_day = ($interval_length * 7);
				//$interval_unit = 'week';
			}
			elseif($interval_unit =='months' ){
				$new_tot_day = ($interval_length * 30);
			}
			elseif($interval_unit =='years' ){
				$new_tot_day = ($interval_length * 365);
			}
			
			/* Interval transfer to days End */

			/*$total_length 	=  get_post_meta( $product_ids, 'wps_mnm_subscription_total_length_int', true );
			$total_unit 	=  get_post_meta( $product_ids, 'wps_mnm_subscription_total_length_char', true );*/
			
			/**** Interval unit transfer to days Start**/
			if($total_unit == 'days'){
			$new_tot_length = $total_length;
			}
			elseif($total_unit =='week' ){
				$new_tot_length = ($total_length * 7);
				//$total_unit = 'week';
			}
			elseif($total_unit =='months' ){
			$new_tot_length = ($total_length * 30);
			}
			elseif($total_unit =='years' ){
			$new_tot_length = ($total_length * 365);
			}
			/**** Interval unit transfer to days End**/
			
			$total_occurrences = floor($new_tot_length/$new_tot_day);
			$env 	= $this->get_option('environment');
			
			$client_id =  $this->get_option('api_login');
			$client_secret =  $this->get_option('trans_key');
			
			/*$client_id =  '48QyB6Fy64f';
			$client_secret =  '49hS925K7ShxBV9d';*/	
			
	        //$subscription = new AuthnetARB($client_id, $client_secret, $interval_length, $interval_unit, $total_occurrences, AuthnetARB::USE_DEVELOPMENT_SERVER);
	        if($env == 'yes')
			{
				$subscription = new AuthnetARB($client_id, $client_secret, $interval_length, $interval_unit, $total_occurrences, AuthnetARB::USE_DEVELOPMENT_SERVER);
			}
			else
			//if($env == 'no')
			{
				$subscription = new AuthnetARB($client_id, $client_secret, $interval_length, $interval_unit, $total_occurrences, AuthnetARB::USE_PRODUCTION_SERVER);
			}
			global $woocommerce;

			$customer_order = new WC_Order( $order_id );
			
			$customer = new WC_Customer($order_id);
			
			$ship_first_name = get_user_meta( $current_user->ID, 'shipping_first_name', true );
			$ship_last_name = get_user_meta( $current_user->ID, 'shipping_last_name', true );
			$ship_to_address = get_user_meta( $current_user->ID, 'shipping_address_1', true ); 
			$address_2 = get_user_meta( $current_user->ID, 'shipping_address_2', true );
			$ship_to_city = get_user_meta( $current_user->ID, 'shipping_city', true );
			$ship_to_state = get_user_meta( $current_user->ID, 'shipping_state', true );
			$ship_to_zip = get_user_meta( $current_user->ID, 'shipping_postcode', true );

			
			
			$amount = $customer_order->order_total;
			$card_num = str_replace( array(' ', '-' ), '', $_POST['wpc-mnm-auth-card-number'] );
			$exp_date = str_replace( array( '/', ' '), '', $_POST['wpc-mnm-auth-card-expiry'] );
			$cardCvv=str_replace( array(' ', '-' ), '', $_POST['wpc-mnm-auth-card-cvc'] );
			
			// Billing Information
			$first_name = $customer_order->billing_first_name;
			$last_name = $customer_order->billing_last_name;
			$address = $customer_order->billing_address_1;
			$city = $customer_order->billing_city;
			$state = $customer_order->billing_state;
			$zip = $customer_order->billing_postcode;
			$country = $customer_order->billing_country;
			$phone = $customer_order->billing_phone;
			$fax = $customer_order->billing_fax;
			$email = $customer_order->billing_email;
			
			// information customer
			$cust_id = $customer_order->user_id;
			
			
			$subscription->setParameter('amount', $amount);
			$subscription->setParameter('cardNumber', $card_num);
			$subscription->setParameter('expirationDate', $exp_date);
			$subscription->setParameter('card_code', $cardCvv);
			$subscription->setParameter('firstName', $first_name);
			$subscription->setParameter('lastName', $last_name);
			$subscription->setParameter('address', $address);
			$subscription->setParameter('city', $city);
			$subscription->setParameter('state', $state);
			$subscription->setParameter('zip', $zip);
			$subscription->setParameter('email', $email);
			$subscription->setParameter('subscrName', $first_name);		
			$subscription->setParameter('refID', $order_id);
			$subscription->setParameter('orderInvoiceNumber', $order_id);
			$subscription->setParameter('orderDescription', 'Test Process');
			$subscription->setParameter('customerId', $cust_id);
			$subscription->setParameter('customerEmail', $email);
			$subscription->setParameter('customerPhoneNumber', $phone);
			
			if($fax != ''){
			$subscription->setParameter('customerFaxNumber', $fax);
			}
			if($ship_to_company != ''){
			$subscription->setParameter('company', $ship_to_company);
			}
			if($ship_first_name != ''){
			$subscription->setParameter('shipFirstName', $ship_first_name);
			}
			if($ship_last_name != ''){
			$subscription->setParameter('shipLastName', $ship_last_name);
			}
			if($ship_to_company != ''){
			$subscription->setParameter('shipCompany', $ship_to_company);
			}
			if($ship_to_address != ''){
			$subscription->setParameter('shipAddress', $ship_to_address);
			}
			if($ship_to_city != ''){
			$subscription->setParameter('shipCity', $ship_to_city);
			}
			if($ship_to_state != ''){
			$subscription->setParameter('shipState', $ship_to_state);
			}
			if($ship_to_zip != ''){
			$subscription->setParameter('shipZip', $ship_to_zip);
			}

			$subscription->createAccount();
			

			if ($subscription->isSuccessful())
			{
				// Get the subscription ID
				$subscription_id = $subscription->getSubscriberID();
				
				//echo '<pre>Second'; print_r($subscription_id); echo '</pre>'; die();
				if($subscription_id){
					
					$customer_order->add_order_note( __( 'CIM Subscription ID:'. $subscription_id ) );
					
				// Save this Subscription ID to Database
					update_post_meta($order_id,'_wps-mnm-sub-created_id_auth_dashboard',$subscription_id);	
				 
				// paid order marked
					$customer_order->payment_complete();
						
				// this is important part for empty cart
					$woocommerce->cart->empty_cart();
					
					//echo 'Amit'; die;
					// Redirect to thank you page
					/*return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $customer_order ),
					);*/
					return true;
				
				}
				
			}
			else
			{
				//echo '123'; die;
				wc_add_notice( 'Not Sucessfull', 'error' );
				$customer_order->add_order_note( 'Error: '. 'Not Sucessfull' );
			}
			
		 }
		
		// Validate fields
			public function validate_fields() {
				$authMethod=$_POST['wpc_mnm_auth_payment_method'];
	        if($authMethod=="wpc_mnm_auth_credit_card"){
		      	if($_POST['wpc-mnm-auth-card-number']!="" && $_POST['wpc-mnm-auth-card-cvc']!="" && $_POST['wpc-mnm-auth-card-expiry']!="") {

                     $accountNumber=str_replace( array(' ', '-' ), '', $_POST['wpc-mnm-auth-card-number'] );

                     $cardCode = array(
					        "vi"  => "Visa Card",
					        "mc"  => "MasterCard",
					        "ae"  => "American Express",
					        "di"  => "Discover",
					        "jcb" => "JCB",
					        "dn"  => "Dinner Card"
					              );

					 $cardType = array(
					        "visa"       => "/^4[0-9]{12}(?:[0-9]{3})?$/",
					        "mastercard" => "/^5[1-5][0-9]{14}$/",
					        "amex"      => "/(^3[47])((\d{11}$)|(\d{13}$))/",
					        "discover"   => "/^6(?:011|5[0-9]{2})[0-9]{12}$/",
					        "jcb"   => "/(^(352)[8-9](\d{11}$|\d{12}$))|(^(35)[3-8](\d{12}$|\d{13}$))/",
					        "dn"  => "/(^(30)[0-5]\d{11}$)|(^(36)\d{12}$)|(^(38[0-8])\d{11}$)/"
					             );

						if (preg_match($cardType['visa'],$accountNumber)) {							
						    $result='vi'; 
						} elseif (preg_match($cardType['mastercard'],$accountNumber)) {							
						    		$result='mc';  
						    } elseif (preg_match($cardType['amex'],$accountNumber)) {							
						              $result='ae';							
						        } elseif (preg_match($cardType['discover'],$accountNumber)) {							
						                  $result='di';
						            } elseif (preg_match($cardType['jcb'],$accountNumber)) {
							                  $result='jcb';
						                } elseif (preg_match($cardType['dn'],$accountNumber)) {
							                  $result='dn';
						                    } else {
							    	            wc_add_notice( "Wrong card", 'error' );
							                    return false;
						                       }                                                
                        $cardTypes=$this->card_type_field;
                        if($cardTypes!="") {                               
                                        $active_cards='';
                                        foreach($cardTypes as $key) {
										    if(array_key_exists($key, $cardCode)) {
										        $active_cards.=$cardCode[$key].", ";
										    }
										}

		                        if (!in_array($result, $cardTypes)) {
		                        	 $cards_allow=rtrim($active_cards,', ');
									 wc_add_notice("Card type should be <b> $cards_allow </b>" , 'error' );
									 return false;
								} 
						}

                } else {
			        wc_add_notice( "Provide the Card detials", 'error' );
		      		return false;
		      	  }           
	      }
	}
			
			public function do_ssl_check() {
				if( $this->enabled == "yes" ) {
					if( get_option( 'woocommerce_force_ssl_checkout' ) == "no" ) {
						echo "<div class=\"error\"><p>". sprintf( __( "<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>" ), $this->method_title, admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) ."</p></div>";	
					}
				}		
			}
	}
}
function wps_mnm_woo_add_gateway_class( $methods ) {
    $methods[] = 'WC_Gateway_Wps_Mnm_Gateway'; 
    return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'wps_mnm_woo_add_gateway_class' );
