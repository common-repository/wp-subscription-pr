<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* Add a custom payment class to WC
  ------------------------------------------------------------ */
add_action('plugins_loaded', 'wps_mnm_woo_payment_gateway_2checkout', 0);

function wps_mnm_woo_payment_gateway_2checkout(){
    if (!class_exists('WC_Payment_Gateway'))
        return; // if the WC payment gateway class is not available, do nothing
    if(class_exists('WC_Twocheckout'))
        return;

    class WC_Gateway_Wps_Mnm_Gateway_2checkout extends WC_Payment_Gateway{

        // Logging
        public static $log_enabled = false;
        public static $log = false;

        public function __construct(){

            $plugin_dir = plugin_dir_url(__FILE__);

            global $woocommerce;

            $this->id = 'twocheckout';
            $this->has_fields = true;

            // Load the settings
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables

            $this->seller_id = $this->get_option('seller_id');
            $this->publishable_key = $this->get_option('publishable_key');
            $this->private_key = $this->get_option('private_key');
            $this->sandbox = $this->get_option('sandbox');
            
            $this->title 				= __( 'WPScribe 2Checkout', 'woocommerce' );
            $this->method_title       	= __( 'WPScribe 2Checkout', 'woocommerce' );  
			$this->method_description 	= __( 'WPScribe 2Checkout Payment Gateway for subscribers', 'woocommerce' );


            self::$log_enabled = $this->debug;

            // Actions
            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));

            // Save options
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

            // Payment listener/API hook
            add_action('woocommerce_api_wc_' . $this->id, array($this, 'check_ipn_response'));
			
			add_filter( 'woocommerce_before_calculate_totals' . $this->id, array($this, 'custom_cart_items_prices' ));
			
            if (!$this->is_valid_for_use()){
                $this->enabled = false;
            }
        }

        /**
        * Logging method
        * @param  string $message
        */
        public static function log( $message ) {
            if ( self::$log_enabled ) {
                if ( empty( self::$log ) ) {
                    self::$log = new WC_Logger();
                }
                self::$log->add( 'twocheckout', $message );
            }
        }

        /**
         * Check if this gateway is enabled and available in the user's country
         *
         * @access public
         * @return bool
         */
        function is_valid_for_use() {
          $supported_currencies = array(
            'AFN', 'ALL', 'DZD', 'ARS', 'AUD', 'AZN', 'BSD', 'BDT', 'BBD',
            'BZD', 'BMD', 'BOB', 'BWP', 'BRL', 'GBP', 'BND', 'BGN', 'CAD',
            'CLP', 'CNY', 'COP', 'CRC', 'HRK', 'CZK', 'DKK', 'DOP', 'XCD',
            'EGP', 'EUR', 'FJD', 'GTQ', 'HKD', 'HNL', 'HUF', 'INR', 'IDR',
            'ILS', 'JMD', 'JPY', 'KZT', 'KES', 'LAK', 'MMK', 'LBP', 'LRD',
            'MOP', 'MYR', 'MVR', 'MRO', 'MUR', 'MXN', 'MAD', 'NPR', 'TWD',
            'NZD', 'NIO', 'NOK', 'PKR', 'PGK', 'PEN', 'PHP', 'PLN', 'QAR',
            'RON', 'RUB', 'WST', 'SAR', 'SCR', 'SGF', 'SBD', 'ZAR', 'KRW',
            'LKR', 'SEK', 'CHF', 'SYP', 'THB', 'TOP', 'TTD', 'TRY', 'UAH',
            'AED', 'USD', 'VUV', 'VND', 'XOF', 'YER');

            if ( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_twocheckout_supported_currencies', $supported_currencies ) ) ) return false;

            return true;
        }

        /**
         * Admin Panel Options
         * - Options for bits like 'title' and availability on a country-by-country basis
         *
         * @since 1.0.0
         */
       /* public function admin_options() {

            ?>
            <h3><?php _e( '2Checkout', 'woocommerce' ); ?></h3>
            <p><?php _e( '2Checkout - Credit Card/Paypal', 'woocommerce' ); ?></p>

            <?php if ( $this->is_valid_for_use() ) : ?>

                <table class="form-table">
                    <?php
                    // Generate the HTML For the settings form.
                    $this->generate_settings_html();
                    ?>
                </table><!--/.form-table-->

            <?php else : ?>
                <div class="inline error"><p><strong><?php _e( 'Gateway Disabled', 'woocommerce' ); ?></strong>: <?php _e( '2Checkout does not support your store currency.', 'woocommerce' ); ?></p></div>
            <?php
            endif;
        }*/


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
                    'label' => __( 'Enable 2Checkout', 'woocommerce' ),
                    'default' => 'no'
                ),
                'seller_id' => array(
                    'title' => __( 'Seller ID', 'woocommerce' ),
                    'type'          => 'text',
                    'description' => __( 'Please enter your 2Checkout account number; this is needed in order to take payment.', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder'   => ''
                ),
                'publishable_key' => array(
                    'title' => __( 'Publishable Key', 'woocommerce' ),
                    'type'          => 'text',
                    'description' => __( 'Please enter your 2Checkout Publishable Key; this is needed in order to take payment.', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder'   => ''
                ),
                'private_key' => array(
                    'title' => __( 'Private Key', 'woocommerce' ),
                    'type'          => 'text',
                    'description' => __( 'Please enter your 2Checkout Private Key; this is needed in order to take payment.', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder'   => ''
                ),
                /*'api_user_name' => array(
                    'title' => __( 'API UserName', 'woocommerce' ),
                    'type'          => 'text',
                    'description' => __( 'Please enter your 2Checkout User Name; this is needed in order to cancel subscription.', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder'   => ''
                ),
                 'api_user_password' => array(
                    'title' => __( 'API Password', 'woocommerce' ),
                    'type'          => 'password',
                    'description' => __( 'Please enter your 2Checkout Password; this is needed in order to cancel subscription.', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder'   => ''
                ),*/
                'sandbox' => array(
                    'title' => __( 'Sandbox/Production', 'woocommerce' ),
                    'type' => 'checkbox',
                    'label' => __( 'Use 2Checkout Sandbox', 'woocommerce' ),
                    'default' => 'no'
                )                          
            );

        }

        /**
         * Generate the credit card payment form
         *
         * @access public
         * @param none
         * @return string
         */
        function payment_fields() {
            $plugin_dir = plugin_dir_url(__FILE__);
            // Description of payment method from settings
            if ($this->description) { ?>
                <p><?php
                echo $this->description; ?>
                </p><?php
            } ?>

            <ul class="woocommerce-error" style="display:none" id="twocheckout_error_creditcard">
            <li>Credit Card details are incorrect, please try again.</li>
            </ul>

            <fieldset>
            <input id="sellerId" type="hidden" maxlength="16" width="20" value="<?php echo $this->seller_id ?>">
            <input id="publishableKey" type="hidden" width="20" value="<?php echo $this->publishable_key ?>">
            <input id="token" name="token" type="hidden" value="">

            <!-- Credit card number -->
            <p class="form-row form-row-first">
                <label for="ccNo"><?php echo __( 'Card number', 'woocommerce' ) ?> <span class="required">*</span></label>
                <input type="text" class="input-text" id="ccNo" autocomplete="off" value="" />

            </p>

            <div class="clear"></div>

            <!-- Credit card expiration -->
            <p class="form-row form-row-first">
                <label for="cc-expire-month"><?php echo __( 'Expiry', 'woocommerce') ?> <span class="required">*</span></label>
                <select id="expMonth" class="woocommerce-select woocommerce-cc-month">
                    <option value=""><?php _e( 'Month', 'woocommerce' ) ?></option><?php
                    $months = array();
                    for ( $i = 1; $i <= 12; $i ++ ) {
                        $timestamp = mktime( 0, 0, 0, $i, 1 );
                        $months[ date( 'n', $timestamp ) ] = date( 'F', $timestamp );
                    }
                    foreach ( $months as $num => $name ) {
                        printf( '<option value="%02d">%s</option>', $num, $name );
                    } ?>
                </select>
                <select id="expYear" class="woocommerce-select woocommerce-cc-year">
                    <option value=""><?php _e( 'Year', 'woocommerce' ) ?></option>
                    <?php
                    $years = array();
                    for ( $i = date( 'y' ); $i <= date( 'y' ) + 15; $i ++ ) {
                        printf( '<option value="20%u">20%u</option>', $i, $i );
                    }
                    ?>
                </select>
            </p>
            <div class="clear"></div>

            <!-- Credit card security code -->
            <p class="form-row">
            <label for="cvv"><?php _e( 'Card code', 'woocommerce' ) ?> <span class="required">*</span></label>
            <input type="password" class="input-text" id="cvv" autocomplete="off" maxlength="4" style="width:55px" />
            </p>

            <div class="clear"></div>

            </fieldset>

           <script type="text/javascript">
                var formName = "order_review";
                var myForm = document.getElementsByName('checkout')[0];
                if(myForm) {
                    myForm.id = "tcoCCForm";
                    formName = "tcoCCForm";
                }
                jQuery('#' + formName).on("click", function(){
                    jQuery('#place_order').unbind('click');
                    jQuery('#place_order').click(function(e) {
                        e.preventDefault();
                        retrieveToken();
                    });
                });

                function successCallback(data) {
                    clearPaymentFields();
                    jQuery('#token').val(data.response.token.token);
                    jQuery('#place_order').unbind('click');
                    jQuery('#place_order').click(function(e) {
                        return true;
                    });
                    jQuery('#place_order').click();
                }

                function errorCallback(data) {
                    if (data.errorCode === 200) {
                        TCO.requestToken(successCallback, errorCallback, formName);
                    } else if(data.errorCode == 401) {
                        clearPaymentFields();
                        jQuery('#place_order').click(function(e) {
                            e.preventDefault();
                            retrieveToken();
                        });
                        jQuery("#twocheckout_error_creditcard").show();

                    } else{
                        clearPaymentFields();
                        jQuery('#place_order').click(function(e) {
                            e.preventDefault();
                            retrieveToken();
                        });
                        alert(data.errorMsg);
                    }
                }

                var retrieveToken = function () {
                    jQuery("#twocheckout_error_creditcard").hide();
                    if (jQuery('div.payment_method_twocheckout:first').css('display') === 'block') {
                        jQuery('#ccNo').val(jQuery('#ccNo').val().replace(/[^0-9\.]+/g,''));
                        TCO.requestToken(successCallback, errorCallback, formName);
                    } else {
                        jQuery('#place_order').unbind('click');
                        jQuery('#place_order').click(function(e) {
                            return true;
                        });
                        jQuery('#place_order').click();
                    }
                }

                function clearPaymentFields() {
                    jQuery('#ccNo').val('');
                    jQuery('#cvv').val('');
                    jQuery('#expMonth').val('');
                    jQuery('#expYear').val('');
                }

            </script>

            <?php if ($this->sandbox == 'yes'): ?>
                <script type="text/javascript" src="https://sandbox.2checkout.com/checkout/api/script/publickey/<?php echo $this->seller_id ?>"></script>
                <!--<script type="text/javascript" src="https://sandbox.2checkout.com/checkout/api/2co.js"></script>-->
                <script type="text/javascript" src="https://www.2checkout.com/checkout/api/2co.min.js"></script>
            <?php else: ?>
                <script type="text/javascript" src="https://www.2checkout.com/checkout/api/script/publickey/<?php echo $this->seller_id ?>"></script>
                <!--<script type="text/javascript" src="https://www.2checkout.com/checkout/api/2co.js"></script>-->
                <script type="text/javascript" src="https://www.2checkout.com/checkout/api/2co.min.js"></script>
            <?php endif ?>
            <?php
        }

        /**
         * Process the payment and return the result
         *
         * @access public
         * @param int $order_id
         * @return array
         */
        function process_payment($order_id) {
			global $woocommerce;
			
            $order = new WC_Order($order_id);
            
            $items = $order->get_items(); 
			$b = array();
			foreach ( $items as $item ) {
			   $var = $item['product_id'];
			   $b[] = $var;
				  
			}
			$product_ids = implode(',',$b);
			//echo $product_ids;
			
			$lineItems_name = get_post_meta( $product_ids, 'wps_mnm_subscription_product_name_field', true );
			$lineItems_quantity = get_post_meta( $product_ids, 'wps_mnm_subscription_quantity_field', true );
			$lineItems_price = get_post_meta( $product_ids, 'wps_mnm_subscription_2checkout_price_field', true );
			$lineItems_id = get_post_meta( $product_ids, 'wps_mnm_subscription_product_id_field', true );
			$lineItems_startupFee = get_post_meta( $product_ids, 'wps_mnm_subscription_startup_fees_field', true );
			$lineItems_description = get_post_meta( $product_ids, 'wps_mnm_subscription_description_field', true );
			$lineItems_transible = get_post_meta( $product_ids, 'wps_mnm_subscription_trangible', true );
			
			$interval_length = get_post_meta( $product_ids, 'wps_mnm_subscription_renew_int', true );
			$interval_unit = get_post_meta( $product_ids, 'wps_mnm_subscription_renew_char', true );

			$total_length 	=  get_post_meta( $product_ids, 'wps_mnm_subscription_total_length_int', true );
			$total_unit 	=  get_post_meta( $product_ids, 'wps_mnm_subscription_total_length_char', true );
			
			if($interval_unit == 'weeks'){
				$interval_unit = 'Week';
			}
			if($interval_unit == 'months'){
				$interval_unit = 'Month';
			}
			if($interval_unit == 'years'){
				$interval_unit = 'Year';
			}

			if($total_unit == 'weeks'){
				$total_unit = 'Week';
			}
			if($total_unit == 'months'){
				$total_unit = 'Month';
			}
			if($total_unit == 'years'){
				$total_unit = 'Year';
			}
			
			$lineItems_recurrence = $interval_length.' '.$interval_unit;
			$lineItems_duration   = $total_length.' '.$total_unit;
			
            //echo'<pre>>>>>'.$order->billing_first_name; die();
          //echo $token = $_POST['token']; die();
          //echo $sellerid = $this->seller_id; die();
          //echo $currency = get_woocommerce_currency(); die();
          //echo $marchent = $order->get_order_number(); die();
			if(isset($_POST['token']) && $_POST['token']!="")
			{
				$token = $_POST['token']; 
				$this->seller_id; 
				get_woocommerce_currency(); 
				$order->get_total(); 
				$order->get_order_number();
				$twocheckout_args = array(
            
				'token'         => $_POST['token'],
				'sellerId'      => $this->seller_id,
				'currency' 		=> get_woocommerce_currency(),
				//'total'         => $order->get_total(),

				// Order key
				'merchantOrderId'    => $order->get_order_number(),
				
                "billingAddr" => array(
					'name'          => $order->billing_first_name . ' ' . $order->billing_last_name,
					'addrLine1'     => $order->billing_address_1,
					'addrLine2'     => $order->billing_address_2,
					'city'          => $order->billing_city,
					'state'         => $order->billing_state,
					'zipCode'       => $order->billing_postcode,
					'country'       => $order->billing_country,
					'email'         => $order->billing_email,
					'phoneNumber'   => $order->billing_phone,                              
				),
                
                
                "lineItems" => array(
                array(
                'type' => "product",
                'name' => $lineItems_name,
                'quantity' => $lineItems_quantity,
                'price' => $lineItems_price,
                'tangible' => $lineItems_transible,
                'productId' => $lineItems_id,
                'recurrence' => $lineItems_recurrence,
                'duration' => $lineItems_duration,
                'startupFee' => $lineItems_startupFee,
                'description' => $lineItems_description,
                )
                )
                 /*"shippingAddr" => array(
                    "name" => 'Testing Tester',
                    "addrLine1" => '123 Test St',
                    "city" => 'Columbus',
                    "state" => 'OH',
                    "zipCode" => '43123',
                    "country" => 'USA',
                    "email" => 'testingtester@2co.com',
                    "phoneNumber" => '555-555-5555'
                )*/

            );
				try {
					if ($this->sandbox == 'yes') {
						Twocheckout::sandbox(true);
						Twocheckout::privateKey($this->private_key);
						Twocheckout::sellerId($this->seller_id);
						//TwocheckoutApi::setCredentials($this->seller_id, $this->private_key, 'sandbox');
					} else {
						Twocheckout::sandbox(false);
						Twocheckout::privateKey($this->private_key);
						Twocheckout::sellerId($this->seller_id);
						//TwocheckoutApi::setCredentials($this->seller_id, $this->private_key);
					}
					$charge = Twocheckout_Charge::auth($twocheckout_args);
					/*echo $charge['response']['orderNumber'];
					echo $charge['response']['transactionId'];
					echo '<pre>'; print_r($charge); die();*/
					
					
					if ($charge['response']['responseCode'] == 'APPROVED') {
						$order->add_order_note( __( '2Checkout Order ID: '.$charge['response']['orderNumber'] ) );
						$order->add_order_note( __( '2Checkout Transaction ID: '.$charge['response']['transactionId'] ) );
						//update_post_meta($order->get_order_number(),'_order_total',$lineItems_price);
						$order->payment_complete();
					// Save this Sale ID equal to orderNumber to Database
						update_post_meta($order_id,'_wps-mnm-sub-sale_id_dashboard',$charge['response']['orderNumber']);
						update_post_meta($order_id,'_wps-mnm-sub-transac_id_dashboard',$charge['response']['transactionId']);
						return array(
							'result' => 'success',
							'redirect' => $this->get_return_url( $order )
						);
					}
				} 
				catch (Twocheckout_Error $e) {
					wc_add_notice($e->getMessage(), $notice_type = 'error' );
					//update_post_meta($order->get_order_number(),'_order_total',$lineItems_price);
					return;
				}

        } 
    }

 }

   //include (WPS_MNM_INC . '/2Checkout/TwocheckoutApi.php') ;  
   require_once (WPS_MNM_INC . '/2checkout-php-master/lib/Twocheckout.php') ; 


    /**
     * Add the gateway to WooCommerce
     **/
    function wps_mnm_woo_add_gateway_2checkout($methods){
        $methods[] = 'WC_Gateway_Wps_Mnm_Gateway_2checkout';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'wps_mnm_woo_add_gateway_2checkout');

}
