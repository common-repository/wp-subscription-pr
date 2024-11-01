<?php

add_action( 'plugins_loaded', 'wps_mnm_woo_payment_gateway_stripe' );

function wps_mnm_woo_payment_gateway_stripe() {
    class WC_Gateway_Wps_Mnm_Gateway_Stripe extends WC_Payment_Gateway {
		public $apiContext = null;
		public function __construct() {
		$this->id                 	= 'wps_mnm_stripe'; 
		$this->method_title       	= __( 'WPScribe Stripe', 'wps-stripe' );  
		$this->method_description 	= __( 'WPScribe Payment Gateway for subscribers', 'wps-stripe' );
		$this->title              	= __( 'WPScribe Stripe', 'wps-stripe' );
		$this->has_fields = true;
	
		$this->supports = array( 'default_credit_card_form');

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
		
		foreach ( $this->settings as $setting_key => $value ) {
			$this->$setting_key = $value;
		}
		
		$this->enabled 		= $this->get_option('enabled');

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
			<input id="' . esc_attr( $this->id ) . '-card-cvc" name="wpc-mnm-stripe-card-cvc" id="card-cvc" class="input-text wc-credit-card-form-card-cvc" inputmode="numeric" autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" maxlength="4" placeholder="' . esc_attr__( 'CVC', 'woocommerce' ) . '" ' . $this->field_name( 'card-cvc' ) . ' style="width:100px" />
		</p>';
		
		$default_fields = array(
			'card-number-field' => '<p class="form-row form-row-wide">
				<label for="' . esc_attr( $this->id ) . '-card-number">' . esc_html__( 'Card number', 'woocommerce' ) . ' <span class="required">*</span></label>
				<input id="' . esc_attr( $this->id ) . '-card-number" name="wpc-mnm-stripe-card-number" id="card-number"  class="input-text wc-credit-card-form-card-number" inputmode="numeric" autocomplete="cc-number" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" ' . $this->field_name( 'card-number' ) . ' />
			</p>',
			'card-expiry-field' => '<p class="form-row form-row-first">
				<label for="' . esc_attr( $this->id ) . '-card-expiry">' . esc_html__( 'Expiry (MM/YYYY)', 'woocommerce' ) . ' <span class="required">*</span></label>
				<input id="' . esc_attr( $this->id ) . '-card-expiry" name="wpc-mnm-stripe-card-expiry" class="input-text wc-credit-card-form-card-expiry" inputmode="numeric" autocomplete="cc-exp" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="' . esc_attr__( 'MM / YYYY', 'woocommerce' ) . '" ' . $this->field_name( 'card-expiry' ) . ' />
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
		 <input id="payment_method_token" class="input-radio" name="wpc_mnm_stripe_payment_method" value="wpc_mnm_stripe_token" data-order_button_text="" type="radio" onclick="merrcotoken()">
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
	


		
	public function process_payment( $order_id ) {
        
        include (WPS_MNM_INC . '/stripe/Stripe.php') ;
        global $woocommerce;
        $order = new WC_Order( $order_id );
        $items = $order->get_items(); 
        $b = array();
        foreach ( $items as $item ) {
           $var = $item['product_id'];
           $b[] = $var;
              
        }
        $product_ids = implode(',',$b);
            
        $plan_id = get_post_meta( $product_ids, 'wps_mnm_subscription_plan_field', true ); 
        
        $mode =  $this->get_option('environment');

        $test_s_key =  $this->get_option('stripe_test_secret_key');
        
        $live_s_key =  $this->get_option('stripe_live_secret_key');
    
        if($mode == 'yes') {
            $secret_key = $test_s_key;
        } else {
            $secret_key = $live_s_key;
        }


        $card_num 	= str_replace( array(' ', '-' ), '', $_POST['wpc-mnm-stripe-card-number'] );
		$exp_date 	= explode( '/', $_POST['wpc-mnm-stripe-card-expiry'] );
		$cardCvv 	= str_replace( array(' ', '-' ), '', $_POST['wpc-mnm-stripe-card-cvc'] );

        Stripe::setApiKey($secret_key);

        $result = Stripe_Token::create(
                    array(
                        "card" => array(
                            "number" => trim($card_num),
                            "exp_month" => trim($exp_date[0]),
                            "exp_year" => trim($exp_date[1]),
                            "cvc" => trim($cardCvv)
                        )
                    )
                );

        $token = $result['id'];

        try {           
            $customer = Stripe_Customer::create(array(
                    'card' => $token,
                    'plan' => $plan_id
                )
            );
            $subscriptions = $customer['subscriptions']['data'][0];
            $order->add_order_note( __( 'Stripe Customer ID: '.$customer['id'] ) );
            $order->add_order_note( __( 'Stripe Subscription ID: '.$subscriptions['id'] ) );
			$order->payment_complete();
			
			// Save Subscription ID and Customer ID to Database
			update_post_meta($order_id,'_wps-mnm-sub-Subscription_Id_related_to_dashboard',$subscriptions['id']);
			update_post_meta($order_id,'_wps-mnm-sub-Customer_Id_related_to_dashboard',$customer['id']);
					
			$woocommerce->cart->empty_cart();
			$result = array(
                'result' => 'success',
                'redirect' => $this->get_return_url( $order )
            );
            return $result;
            
        } catch (Exception $e) {
            
	        $body = $e->getJsonBody();
			$err  = $body['error'];
			/*print('Status is:' . $e->getHttpStatus() . "\n");
			print('Type is:' . $err['type'] . "\n");
			print('Code is:' . $err['code'] . "\n");
			print('Param is:' . $err['param'] . "\n");*/
			print('Message is:' . $err['message'] . "\n");
			$order->add_order_note( 'Error: '. 'Not Sucessfull' );
			return false;
		} catch (\Stripe\Error\RateLimit $e) {
			wc_add_notice( 'Too Many Request Happening, Please Try After Sometime.', 'error' );
			$order->add_order_note( 'Error: '. 'Not Sucessfull' );
			return false;
		} catch (\Stripe\Error\InvalidRequest $e) {
			wc_add_notice( 'Invalid Data Is Provided', 'error' );
			$order->add_order_note( 'Error: '. 'Not Sucessfull' );
			return false;
		} catch (\Stripe\Error\Authentication $e) {
			wc_add_notice( 'Problem With API Key, Please Contact With Site Administrator', 'error' );
			$order->add_order_note( 'Error: '. 'Not Sucessfull' );
			return false;
		} catch (\Stripe\Error\ApiConnection $e) {
			wc_add_notice( 'Network Connection Error Occurs', 'error' );
			$order->add_order_note( 'Error: '. 'Not Sucessfull' );
			return false;
		} catch (Exception $e) {
			wc_add_notice( 'Not Sucessfull', 'error' );
			$order->add_order_note( 'Error: '. 'Not Sucessfull' );
			return false;
		}
    }

	
	// Validate fields
			public function validate_fields() {
				$authMethod=$_POST['wpc_mnm_stripe_payment_method'];
	        if($authMethod=="wpc_mnm_stripe_credit_card"){
		      	if($_POST['wpc-mnm-stripe-card-number']!="" && $_POST['wpc-mnm-stripe-card-cvc']!="" && $_POST['wpc-mnm-stripe-card-expiry']!="") {

                     $accountNumber=str_replace( array(' ', '-' ), '', $_POST['wpc-mnm-stripe-card-number'] );

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
		
			
		
	public function init_form_fields() {
			$this->form_fields = array(
			'enabled' => array(
				'title'		=> __( 'Enable / Disable', 'wps-stripe' ),
				'label'		=> __( 'Enable Stripe payment gateway', 'wps-stripe' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
			),
			'stripe_live_secret_key' => array(
				'title'		=> __( 'Live Secret Key', 'wps-stripe' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Live secret key provide by Stripe when you signed up for an account..', 'wps-stripe' ),
			),
			
			'stripe_test_secret_key' => array(
				'title'		=> __( 'Test Secret Key', 'wps-stripe' ),
				'desc_tip'	=> __( 'This is the test secret Key provided by stripe when you signed up for an account.', 'wps-stripe' ),
			),
			'environment' => array(
				'title'		=> __( 'Stripe Test Mode', 'wps-stripe' ),
				'label'		=> __( 'Enable Test Mode', 'wps-stripe' ),
				'type'		=> 'checkbox',
				'description' => __( 'This is the Stripe test mode of gateway.', 'wps-stripe' ),
				'default'	=> 'no',
			)
		);		
	}
		
	
	}
}
function wps_mnm_woo_add_gateway_stripe_class( $methods ) {
    $methods[] = 'WC_Gateway_Wps_Mnm_Gateway_Stripe'; 
    return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'wps_mnm_woo_add_gateway_stripe_class' );
