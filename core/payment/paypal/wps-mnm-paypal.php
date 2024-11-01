<?php

/* Add a custom payment class to WC
  ------------------------------------------------------------ */
add_action('plugins_loaded', 'wps_mnm_woo_payment_gateway_paypal', 0);

function wps_mnm_woo_payment_gateway_paypal(){

    class WC_Gateway_Wps_Mnm_Gateway_paypal extends WC_Payment_Gateway{

        // Logging
        public static $log_enabled = false;
        public static $log = false;

        public function __construct(){

            $plugin_dir = plugin_dir_url(__FILE__);

            global $woocommerce;

            $this->id = 'wps_mnm_paypal';
            $this->has_fields = true;
            
            $this->supports = array( 'default_credit_card_form');

            // Load the settings
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables

            $api_user 		= $this->get_option('api_user');
            $api_password 	= $this->get_option('api_password');
            $api_signature	= $this->get_option('api_signature');
            $marchent_email	= $this->get_option('marchent_email');
            $sandbox 		= $this->get_option('sandbox');
            
            $this->title 				= __( 'WPScribe Paypal', 'woocommerce' );
            $this->method_title       	= __( 'WPScribe Paypal', 'woocommerce' );  
			$this->method_description 	= __( 'WPScribe Paypal Payment Gateway for subscribers', 'woocommerce' );


            self::$log_enabled = $this->debug;

            // Actions
            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));

            // Save options
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

            // Payment listener/API hook
            add_action('woocommerce_api_wc_' . $this->id, array($this, 'check_ipn_response'));
			
            /*if (!$this->is_valid_for_use()){
                $this->enabled = false;
            }*/
        }
        
        /**
			 * Get a field name supports
			 *
			 * @access      public
			 * @param       string $name
			 * @return      string
			 */
			function field_name( $name ) {
				return $this->supports( 'tokenization' ) ? '' : ' name="' . esc_attr( $this->id . '-' . $name ) . '" ';
			}
		
			/**
			 * Output payment fields, optional additional fields and woocommerce cc form
			 *
			 * @access      public
			 * @return      void
			*/
			function payment_fields() {
				if ( $this->supports( 'default_credit_card_form' ) && is_checkout() ) {
				$this->form(); // Create Credit Card form
				}
			}
        
        /**
         * Initialise Gateway Settings Form Fields
         *
         * @access public
         * @return void
         */
        function init_form_fields() {

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __( 'Enable/Disable', 'woocommerce' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enable Paypal', 'woocommerce' ),
                    'default' => 'no'
                ),
                'api_user' => array(
                    'title' => __( 'API Username', 'woocommerce' ),
                    'type'          => 'text',
                    'description' => __( 'Please enter your API Username; this is needed in order to take payment.', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder'   => ''
                ),
                 'api_password' => array(
                    'title' => __( 'API Password', 'woocommerce' ),
                    'type'          => 'text',
                    'description' => __( 'Please enter your API Password; this is needed in order to take payment.', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder'   => ''
                ),
                'api_signature' => array(
                    'title' => __( 'API Signature', 'woocommerce' ),
                    'type'          => 'text',
                    'description' => __( 'Please enter your 2Checkout Publishable Key; this is needed in order to take payment.', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder'   => ''
                ), 
                 'marchent_email' => array(
                    'title' => __( 'Merchant Email', 'woocommerce' ),
                    'type'          => 'text',
                    'description' => __( 'Please enter your Merchant Email; this is needed in order to take payment.', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder'   => ''
                ),      
                'sandbox' => array(
                    'title' => __( 'Sandbox/Production', 'woocommerce' ),
                    'type' => 'checkbox',
                    'label' => __( 'Use Paypal Sandbox', 'woocommerce' ),
                    'default' => 'no'
                )                          
            );

        }
        
         function form() {
			wp_enqueue_script( 'wc-credit-card-form' );
			$fields = array();

			$cvc_field = '<p class="form-row form-row-last">
				<label for="' . esc_attr( $this->id ) . '-card-cvc">' . esc_html__( 'Card code', 'woocommerce' ) . ' <span class="required">*</span></label>
				<input id="' . esc_attr( $this->id ) . '-card-cvc" name="wpc-mnm-paypal-card-cvc" class="input-text wc-credit-card-form-card-cvc" inputmode="numeric" autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" maxlength="4" placeholder="' . esc_attr__( 'CVC', 'woocommerce' ) . '" ' . $this->field_name( 'card-cvc' ) . ' style="width:100px" />
			</p>';
			
			$default_fields = array(
				'card-number-field' => '<p class="form-row form-row-wide">
					<label for="' . esc_attr( $this->id ) . '-card-number">' . esc_html__( 'Card number', 'woocommerce' ) . ' <span class="required">*</span></label>
					<input id="' . esc_attr( $this->id ) . '-card-number" name="wpc-mnm-paypal-card-number" class="input-text wc-credit-card-form-card-number" inputmode="numeric" autocomplete="cc-number" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" ' . $this->field_name( 'card-number' ) . ' />
				</p>',
				'card-expiry-field' => '<p class="form-row form-row-first">
					<label for="' . esc_attr( $this->id ) . '-card-expiry">' . esc_html__( 'Expiry (MM/YYYY)', 'woocommerce' ) . ' <span class="required">*</span></label>
					<input id="' . esc_attr( $this->id ) . '-card-expiry" name="wpc-mnm-paypal-card-expiry" class="input-text wc-credit-card-form-card-expiry" inputmode="numeric" autocomplete="cc-exp" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="' . esc_attr__( 'MM / YYYY', 'woocommerce' ) . '" ' . $this->field_name( 'card-expiry' ) . ' />
				</p>',
			);

			if ( ! $this->supports( 'credit_card_form_cvc_on_saved_method' ) ) {
				$default_fields['card-cvc-field'] = $cvc_field;
			}

			$fields = wp_parse_args( $fields, apply_filters( 'woocommerce_credit_card_form_fields', $default_fields, $this->id ) );
			?>
			 <input id="payment_method_cc" style="display:none;" class="input-radio" name="wpc_mnm_paypal_payment_method" value="wpc_mnm_paypal_credit_card" data-order_button_text="" type="radio" checked="checked" >
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
			 <input id="payment_method_token" class="input-radio" name="wpc_mnm_paypal_payment_method" value="wpc_mnm_auth_token" data-order_button_text="" type="radio">
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

       
        
        function process_payment($order_id) {
		
			global $woocommerce;
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
			
			//echo $this->get_option('sandbox'); die();
			
			if($this->get_option('sandbox') == 'yes')
			{
				//echo 'sandy'; die();
				$sandbox 	= TRUE;
			}
			if($this->get_option('sandbox') == 'no')
			{
				//echo 'live'; die();
				$sandbox 	= FALSE;
			}
			$retn_url = get_permalink();
			$canl_url = get_permalink();
				
			//$api_version 	= '85.0';
			$api_version 	= '108.0';
			$api_endpoint 	= $sandbox ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
			$api_username 	= $sandbox ? esc_attr( $this->get_option('api_user') ) : 'LIVE_USERNAME_GOES_HERE';
			$api_password 	= $sandbox ? esc_attr( $this->get_option('api_password') ) : 'LIVE_PASSWORD_GOES_HERE';
			$api_signature 	= $sandbox ? esc_attr( $this->get_option('api_signature') ) : 'LIVE_SIGNATURE_GOES_HERE';
			
			$card_num 		= str_replace(' ', '', $_POST['wpc-mnm-paypal-card-number']); 
			//$card_exp_date 	= $_POST['wpc-mnm-paypal-card-expiry'] ; 
			$exp_date 	= explode( '/', $_POST['wpc-mnm-paypal-card-expiry'] ); 
			$card_exp_date = trim($exp_date[0]).trim($exp_date[1]); 
			//$card_cvv2 		= str_replace( array(' ', '-' ), '', $_POST['wpc-mnm-paypal-card-cvc'] );
			$card_cvv2 		= $_POST['wpc-mnm-paypal-card-cvc'];
			//$card_type 		= $_POST['_paypal_card_type'];
			
			$currency 		= get_woocommerce_currency();
			$total          = $order->get_total();
			// Order key
			$merchantOrderId = $order->get_order_number();
			
			$request_params = array
				(
				'METHOD' => 'SetExpressCheckout', 
				'RETURNURL' => $retn_url,
				'CANCELURL'	=> $canl_url,
				'USER' => $api_username, 
				'PWD' => $api_password, 
				'SIGNATURE' => $api_signature, 
				'VERSION' => $api_version, 
				'PAYMENTACTION' => 'Sale',                   
				'IPADDRESS' => $_SERVER['REMOTE_ADDR'],
				'CREDITCARDTYPE' => $card_type, 
				'ACCT' => $card_num,                        
				'EXPDATE' => $card_exp_date,           
				'CVV2' => $card_cvv2, 
				'COUNTRYCODE' => 'US', 
				'AMT' => $total, 
				'CURRENCYCODE' => 'USD', 
				'DESC' => 'Payment'
				);
				//echo '<pre>'; print_r($request_params); die();
				$nvp_string = '';
				foreach($request_params as $var=>$val)
				{
					$nvp_string .= '&'.$var.'='.urlencode($val);    
					//echo $nvp_string; 
				}
			
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_VERBOSE, 1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($curl, CURLOPT_TIMEOUT, 30);
				curl_setopt($curl, CURLOPT_URL, $api_endpoint);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $nvp_string);
				 
				$result = curl_exec($curl);    				
				curl_close($curl);
				
				$nvp_response_array = parse_str($result); //print_r($result); die();
				$response_arr = explode("&",$result); 
				$response_arr2 = explode("=",$response_arr[9]);  
				$transaction_id = $response_arr2[1]; 
				$response_arr3 = explode("=",$response_arr[2]); //echo '<pre>'; print_r($response_arr3); die();
				echo '<pre>'; print_r($_GET); echo '</pre>'; die();
				$ack = $response_arr3[1];
				$tran_arr = explode("=",$response_arr[9]);
				//echo '<pre>'; print_r($nvp_response_array); echo '</pre>'; die();
				
				if($ack == 'Success')
				{
					
					$_GET['wp_payment_msg'] = 'The transaction is successfully submitted.
											<p>Transaction ID: '.$tran_arr[1].'</p>';
					$_GET['appr_status'] = 1;

				}
				if($ack == 'Failure')
				{
					$message = explode("=",$response_arr[7]);
					$garbage = array("%20","%2e","%3");
					$replace = array(" ","."," ");
					$_GET['wp_payment_msg'] = 'The transaction is not successfully submitted.
											<p>'.str_replace($garbage, $replace, $message[1]).'</p>';
					$_GET['appr_status'] = 0;
				}			
			
		
    }

   
        // Validate fields
		function validate_fields() {
			$authMethod=$_POST['wpc_mnm_paypal_payment_method'];
	        if($authMethod=="wpc_mnm_paypal_credit_card"){
		      	if($_POST['wpc-mnm-paypal-card-number']!="" && $_POST['wpc-mnm-paypal-card-cvc']!="" && $_POST['wpc-mnm-paypal-card-expiry']!="") {

                     $accountNumber=str_replace( array(' ', '-' ), '', $_POST['wpc-mnm-paypal-card-number'] );

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
			
        
    }
    /**
     * Add the gateway to WooCommerce
     **/
    function wps_mnm_woo_add_gateway_paypal($methods){
        $methods[] = 'WC_Gateway_Wps_Mnm_Gateway_paypal';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'wps_mnm_woo_add_gateway_paypal');    
}
