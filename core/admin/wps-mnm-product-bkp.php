<?php



/**
 * Register the custom product type after init
 */
function register_spwcsubscription_product_type() {
	/**
	 * This should be in its own separate file.
	 */
	class WC_Product_spwcsubscription extends WC_Product {
		public function __construct( $product ) {
			$this->product_type = 'spwcsubscription';
			$this->supports[]   = 'ajax_add_to_cart';
			parent::__construct( $product );
			//add_action('woocommerce_spwcsubscription_add_to_cart', array($this, 'spwcsubscription_add_to_cart'),30);
		}
	}
}
add_action( 'plugins_loaded', 'register_spwcsubscription_product_type' );
/**
 * Add to product type drop down.
 */
function add_spwcsubscription_product( $types ){
	// Key should be exactly the same as in the class
	$types[ 'spwcsubscription' ] = __( 'Subscription Product' );
	return $types;
}
add_filter( 'product_type_selector', 'add_spwcsubscription_product' );
/**
 * Show pricing fields for spwcsubscription product.
 */
function spwcsubscription_custom_js() {
	if ( 'product' != get_post_type() ) :
		return;
	endif;
	?><script type='text/javascript'>
		jQuery( document ).ready( function() {
			jQuery( '.options_group.pricing' ).addClass( 'show_if_spwcsubscription' ).show();
		});
	</script><?php
}
add_action( 'admin_footer', 'spwcsubscription_custom_js' );

if (! function_exists( 'woocommerce_spwcsubscription_add_to_cart' ) ) {
function spwcsubscription_add_to_cart() {
    wc_get_template( 'single-product/add-to-cart/simple.php' );
}
add_action('woocommerce_spwcsubscription_add_to_cart',  'spwcsubscription_add_to_cart');
}

/**
 * Add a custom product tab.
 */
function custom_product_tabs( $tabs) {
	$tabs['rental'] = array(
		'label'		=> __( 'Subscription', 'woocommerce' ),
		'target'	=> 'rental_options',
		'class'		=> array( 'show_if_spwcsubscription'  ),
	);
	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'custom_product_tabs' );


/** If Active stripe than add a new tab called Choose a plan id Start */

function custom_product_tabs_if_enable_stripe( $tabs) {
	$gateways        = WC()->payment_gateways->payment_gateways();
	foreach ( $gateways as $id => $gateway ) {
		if($gateway->id == 'wps_mnm_stripe' && $gateway->enabled == 'yes'){
			//echo 'Yes > '.$gateway->id;
			$tabs['stripe_plan'] = array(
				'label'		=> __( 'Stripe Configuration', 'woocommerce' ),
				'description'  => __( 'According to Stripe map your plan id here, otherwise it will not work.', 'woocommerce' ),
				'target'	=> 'plan_id',
				'priority'  => 50,
				'class'		=> array( 'show_if_spwcsubscription' ),
			);
		}
	}
	return $tabs;
}

add_filter( 'woocommerce_product_data_tabs', 'custom_product_tabs_if_enable_stripe' );



/** If Active stripe than add a new tab called Choose a plan id End **/

/** If Active 2Checkout than add a new tab Start */

function custom_product_tabs_if_enable_2Checkout( $tabs) {
	$gateways        = WC()->payment_gateways->payment_gateways();
	foreach ( $gateways as $id => $gateway ) {
		if($gateway->id == 'twocheckout' && $gateway->enabled == 'yes'){
			//echo 'Yes > '.$gateway->id;
			$tabs['product_id'] = array(
				'label'		=> __( '2Checkout Configuration', 'woocommerce' ),
				'description'  => __( 'According to 2Checkout map your details here, otherwise it will not work.', 'woocommerce' ),
				'target'	=> 'product_id',
				'priority'  => 50,
				'class'		=> array( 'show_if_spwcsubscription' ),
			);
		}
	}
	return $tabs;
}

add_filter( 'woocommerce_product_data_tabs', 'custom_product_tabs_if_enable_2Checkout' );



/** If Active 2Checkout than add a new tab End **/

/**
 * Contents of the 2Checkout in product tab.
 */
function rental_plan_options_product_2checkout_content() {
	global $post;
	//echo 'dhshsh'.$post->ID; 
	?><div id='product_id' class='panel woocommerce_options_panel'><?php
		?><div class='options_group'><?php
			 woocommerce_wp_text_input(
			  array(
			   'name'                => 'wps_mnm_subscription_product_name_field',
			   'id'                => 'wps_mnm_subscription_product_name_field',
			   'label'             => __( 'Name', 'woocommerce' ),
			   'placeholder'       => 'Name here...',
			   'desc_tip'    => 'true',
			   'description'       => __( 'According to 2Checkout map your Name here, otherwise it will not work.', 'woocommerce' ),
			   'type'              => 'text'
			   ));
			   
			woocommerce_wp_text_input(
			  array(
			   'name'                => 'wps_mnm_subscription_product_id_field',
			   'id'                => 'wps_mnm_subscription_product_id_field',
			   'label'             => __( 'Product ID', 'woocommerce' ),
			   'placeholder'       => 'Product id here...',
			   'desc_tip'    => 'true',
			   'description'       => __( 'According to 2Checkout map your product id here, otherwise it will not work.', 'woocommerce' ),
			   'type'              => 'text'
			   ));
			   
			 woocommerce_wp_text_input(
			  array(
			   'name'                => 'wps_mnm_subscription_startup_fees_field',
			   'id'                => 'wps_mnm_subscription_startup_fees_field',
			   'label'             => __( 'Startup Fees', 'woocommerce' ),
			   'placeholder'       => 'Startup Fees here...',
			   'desc_tip'    => 'true',
			   'description'       => __( 'According to 2Checkout map your Startup Fees here, otherwise it will not work.', 'woocommerce' ),
			   'type'              => 'text'
			   ));
			   
			   woocommerce_wp_text_input(
			  array(
			   'name'                => 'wps_mnm_subscription_quantity_field',
			   'id'                => 'wps_mnm_subscription_quantity_field',
			   'label'             => __( 'Quantity', 'woocommerce' ),
			   'placeholder'       => 'Quantity here...',
			   'desc_tip'   	   => 'true',
			   'description'       => __( 'According to 2Checkout map your Quantity here, otherwise it will not work.', 'woocommerce' ),
			   'type'              => 'text'
			   ));
			   
			   woocommerce_wp_text_input(
			  array(
			   'name'                => 'wps_mnm_subscription_2checkout_price_field',
			   'id'                => 'wps_mnm_subscription_2checkout_price_field',
			   'label'             => __( 'Price', 'woocommerce' ),
			   'placeholder'       => 'Price here...',
			   'desc_tip'          => 'true',
			   'description'       => __( 'According to 2Checkout map your Price here, otherwise it will not work.', 'woocommerce' ),
			   'type'              => 'text'
			   ));
			   
			  woocommerce_wp_radio(array(
			  'options' => array("Y" => "Yes", "N" => "No"), 
			  'name' => 'wps_mnm_subscription_trangible', 
			  'value' => get_post_meta( $post->ID, 'wps_mnm_subscription_trangible', true ), 
			  'id' => 'wps_mnm_subscription_trangible', 
			  'label' => __('Trangible', 'woocommerce'), 
			  'desc_tip' => 'true', 
			  'description' => __('This indicates whether or not your product is tangible. Tangible goods must be shipped in a timely manner in order for you to receive payment. This field must be set correctly, if 2Checkout becomes aware you have deliberately lied about whether or not a good is tangible, your account will be suspended, and further penalties may apply.', 'woocommerce')));
			   
			  woocommerce_wp_textarea_input(
			  array(
			   'name'              => 'wps_mnm_subscription_description_field',
			   'id'                => 'wps_mnm_subscription_description_field',
			   'label'             => __( 'Description', 'woocommerce' ),
			   'placeholder'       => 'Description here...',
			   'desc_tip'    		=> 'true',
			   'description'       => __( 'According to 2Checkout map your description here, otherwise it will not work.', 'woocommerce' ),
			   'type'              => 'text'
			   ));
			   
			 
		?></div>

	</div><?php
}
add_action( 'woocommerce_product_data_panels', 'rental_plan_options_product_2checkout_content' );


/**
 * Save the 2Checkout fields.
 */
function save_2checkout_option_field( $post_id ) {
	$name_field = $_POST['wps_mnm_subscription_product_name_field'];
	if( !empty( $name_field ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_product_name_field', esc_attr( $name_field) );
	
	$id_field = $_POST['wps_mnm_subscription_product_id_field'];
	if( !empty( $id_field ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_product_id_field', esc_attr( $id_field) );
	
	$fees_field = $_POST['wps_mnm_subscription_startup_fees_field'];
	if( !empty( $fees_field ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_startup_fees_field', esc_attr( $fees_field) );
	
	$quantity_field = $_POST['wps_mnm_subscription_quantity_field'];
	if( !empty( $quantity_field ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_quantity_field', esc_attr( $quantity_field) );
	
	$price_field = $_POST['wps_mnm_subscription_2checkout_price_field'];
	if( !empty( $price_field ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_2checkout_price_field', esc_attr( $price_field) );
	
	$transible_field = $_POST['wps_mnm_subscription_trangible'];
	if( !empty( $transible_field ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_trangible', esc_attr( $transible_field) );
	
	$desc_field = $_POST['wps_mnm_subscription_description_field'];
	if( !empty( $desc_field ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_description_field', esc_attr( $desc_field) );
}
add_action( 'woocommerce_process_product_meta_spwcsubscription', 'save_2checkout_option_field'  );

/**
 * Contents of the STRIPE Plan ID in product tab.
 */
function rental_plan_options_product_tab_content() {
	global $post;
	//echo 'dhshsh'.$post->ID; 
	?><div id='plan_id' class='panel woocommerce_options_panel'><?php
		?><div class='options_group'><?php
			
			woocommerce_wp_text_input(
			  array(
			   'name'                => 'wps_mnm_subscription_plan_field',
			   'id'                => 'wps_mnm_subscription_plan_field',
			   'label'             => __( 'Plan ID', 'woocommerce' ),
			   'placeholder'       => 'Plan id here...',
			   'desc_tip'    => 'true',
			   'description'       => __( 'According to Stripe map your plan id here, otherwise it will not work.', 'woocommerce' ),
			   'type'              => 'text'
			   ));
			   
			 woocommerce_wp_text_input(
			  array(
			   'name'                => 'wps_mnm_subscription_stripe_price_field',
			   'id'                => 'wps_mnm_subscription_stripe_price_field',
			   'label'             => __( 'Price', 'woocommerce' ),
			   'placeholder'       => 'Price here...',
			   'desc_tip'    => 'true',
			   'description'       => __( 'According to Stripe map your price here as it is in plan, otherwise it will not work.', 'woocommerce' ),
			   'type'              => 'text'
			   ));
		?></div>

	</div><?php
}
add_action( 'woocommerce_product_data_panels', 'rental_plan_options_product_tab_content' );

/**
 * Save the plan ID fields.
 */
function save_rental_plan_option_field( $post_id ) {
	$plan_field = $_POST['wps_mnm_subscription_plan_field'];
	if( !empty( $plan_field ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_plan_field', esc_attr( $plan_field) );
	
	$stripe_price_field = $_POST['wps_mnm_subscription_stripe_price_field'];
	if( !empty( $stripe_price_field ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_stripe_price_field', esc_attr( $stripe_price_field) );
}
add_action( 'woocommerce_process_product_meta_spwcsubscription', 'save_rental_plan_option_field'  );

/*** Payment Method Setting Notification On Change Start ***/
add_action('admin_footer', 'wps_mnm_payment_method_notification');
function wps_mnm_payment_method_notification(){?>
<script type="text/javascript">
jQuery(function($) {
	 //alert('ok');
		$('.wps_mnm_method_notes').on('change', function() {
			/*var pay_method = $(this).find(":selected").val();
			alert(pay_method);*/
			$("#notice-stripe").hide();
			$("#notice-twocheckout").hide();
			//var scribe_pay_method = $(".wps_mnm_method_notes").find("option:selected").text();
			var scribe_pay_method = $(".wps_mnm_method_notes").val();		
			//alert(scribe_pay_method);
			if(scribe_pay_method == 'wps_mnm_stripe'){
				$("#notice-stripe").show().delay(10000).fadeOut();
				$("#notice-twocheckout").hide();
				
				$(".renew_day").prop("disabled", true);
				$(".length").prop("disabled", true);
				$(".sub_tot_renew").prop("disabled", true);
				$(".sub_tot_length").prop("disabled", true);
			}
			if(scribe_pay_method == 'twocheckout'){
				$("#notice-twocheckout").show().delay(10000).fadeOut();
				$("#notice-stripe").hide();
				
				$(".renew_day").prop("disabled", false);
				$(".length").prop("disabled", false);
				$(".sub_tot_renew").prop("disabled", false);
				$(".sub_tot_length").prop("disabled", false);
			}
		});		
});		
</script>
<?php
    }  	
/*** Payment Method Setting Notification On Change End ***/
/**
 * Contents of the rental options product tab.
 */
function rental_options_product_tab_content() {
	global $post;
	//echo 'dhshsh'.$post->ID; 
	?><div id='rental_options' class='panel woocommerce_options_panel'><?php
		?><div class='options_group'><?php
		
		$gateways = WC()->payment_gateways->payment_gateways();
		//echo '<pre>'; print_r($gateways); die();
		$b = array();
		$c = array();
		foreach ( $gateways as $id => $gateway ) 
		{
			if(($gateway->id == 'wps_mnm_stripe' || $gateway->id == 'wps_mnm_auth_net' || $gateway->id == 'wps_mnm_paypal' || $gateway->id == 'twocheckout') && $gateway->enabled == 'yes')
			{
				//echo 'ID > '.$gateway->id;
				//echo 'Title > '.$gateway->title;
				 //$var = $gateway->id;
				 $var1 = $gateway->id;
				 $var = $gateway->title;
				 $b[$gateway->id] = $var; 
				 $c[] = $var1;
			}
		}
		//echo $scribe_payment_method = implode(',',$c);
		//echo '<pre>';print_r($c);echo '</pre>';

			if(count($c) > 0)
			{
				
			} 
			else 
			{
				echo '<p class="scribe_payment_error_message" id="scribe-payment" style=" color:red;font-size: 18px;">
				Please active a Subscription Payment Method.</p>';
			}

		
		 woocommerce_wp_select( 
			array( 
			'id'          => '_select['.$loop.']', 
			'class'		  => 'wps_mnm_method_notes',
			'name'        => 'wps_mnm_subscription_payment_method', 
			'label'       => __( 'Payment Method', 'woocommerce' ), 
			'description' => __( 'Choose any payment method related to the product.', 'woocommerce' ),
			'value'       => get_post_meta( $post->ID, 'wps_mnm_subscription_payment_method', true ),
			'options'     => $b
				)
			); 
			?>
			
			<p class="notice_twocheckout" id="notice-twocheckout" style="display:none; color:blue;font-size: 18px;">
			Please configure from 2Checkout Configuration Tab.</p>
			
			<p class="notice_stripe" id="notice-stripe" style="display:none; color:blue;font-size: 18px;">
			Please configure from Stripe Configuration Tab.</p>
			
			<?php
			/*woocommerce_wp_checkbox( array(
				'id' 		=> '_enable_renta_option',
				'label' 	=> __( 'Enable rental option X', 'woocommerce' ),
			) );
			woocommerce_wp_text_input( array(
				'id'			=> '_text_input_y',
				'label'			=> __( 'What is the value of Y', 'woocommerce' ),
				'desc_tip'		=> 'true',
				'description'	=> __( 'A handy description field', 'woocommerce' ),
				'type' 			=> 'text',
			) );*/
			$options = array();
			for($i=1; $i<=31; $i++){
				$options[$i] = $i;
			}
			/*woocommerce_wp_text_input(
			  array(
			   'id'                => 'wps_mnm_subscription_price_field',
			   'label'             => __( 'Subscription price', 'woocommerce' ),
			   'placeholder'       => 'Price',
			   'desc_tip'    => 'true',
			   //'description'       => __( 'Enter Subscription Price.', 'woocommerce' ),
			   'type'              => 'text'
			   ));*/
			    woocommerce_wp_select( 
				array( 
				'id'          => '_select['.$loop.']', 
				'class'		=> 'renew_day',	
				'name'          => 'wps_mnm_subscription_renew_int', 
				'label'       => __( 'Subscription Renewal Length', 'woocommerce' ), 
				//'description' => __( 'Choose a value.', 'woocommerce' ),
				'value'       => get_post_meta( $post->ID, 'wps_mnm_subscription_renew_int', true ),
				'options' => $options
				)
				);
				 woocommerce_wp_select( 
				array( 
				'id'          => '_select['.$loop.']', 
				'name'          => 'wps_mnm_subscription_renew_char', 
				'class'			=> 'length',
				'label'       => __( '', 'woocommerce' ), 
				//'description' => __( 'Choose a value.', 'woocommerce' ),
				'value'       => get_post_meta( $post->ID, 'wps_mnm_subscription_renew_char', true ),
				'options' => array(
					'minutes' => __('Minute','woocommerce'),
					'days'   => __( 'Day', 'woocommerce' ),
					'weeks'   => __( 'Week', 'woocommerce' ),
					'months' => __( 'Month', 'woocommerce' ),
					'years' => __( 'Year', 'woocommerce' )
					)
				)
				);

			   echo '<b style="font-size:15px; color:blue;">If Payment Method is Authorize . Net,Please select Subscription Renewal Length Unit as Days or Months. </b>';
				?>
				
				<!--<p class="error_message" id="error-msg" style="display:none; color:red;font-size: 15px;">
				Please choose subscription renewal length more than 7 days.</p>-->
				<?php
				/*echo $renew_day = get_post_meta( $post->ID, 'wps_mnm_subscription_renew_int', true );
				echo $length = get_post_meta( $post->ID, 'wps_mnm_subscription_renew_char', true );*/
				
				
				woocommerce_wp_select( 
				array( 
				'id'          => '_select['.$loop.']', 
				'name'          => 'wps_mnm_subscription_total_length_int', 
				'class'			=> 'sub_tot_renew',
				'label'       => __( 'Subscription Total Length', 'woocommerce' ), 
				//'description' => __( 'Choose Subscription Length.', 'woocommerce' ),
				'value'       => get_post_meta( $post->ID, 'wps_mnm_subscription_total_length_int', true ),
				'options' => $options
				)
				);
				 woocommerce_wp_select( 
				array( 
				'id'          => 'wps_mnm_subscription_total_length_char', 
				'name'          => 'wps_mnm_subscription_total_length_char', 
				'class'			=> 'sub_tot_length',
				'label'       => __( '', 'woocommerce' ), 
				//'description' => __( 'Choose a value.', 'woocommerce' ),
				'value'       => get_post_meta( $post->ID, 'wps_mnm_subscription_total_length_char', true ),
				'options' => array(
					'days'   => __( 'Day', 'woocommerce' ),
					'weeks'   => __( 'Week', 'woocommerce' ),
					'months' => __( 'Month', 'woocommerce' ),
					'years' => __( 'Year', 'woocommerce' )
					)
				)
				);
				
			
			$interval_length = get_post_meta( $post->ID, 'wps_mnm_subscription_renew_int', true );
			$interval_unit = get_post_meta( $post->ID, 'wps_mnm_subscription_renew_char', true );
			
			/* Interval  transfer to days Start */
			if($interval_unit == 'days'){
			$new_tot_day = $interval_length;
			}
			elseif($interval_unit =='weeks' ){
			$new_tot_day = ($interval_length * 7);
			}
			elseif($interval_unit =='months' ){
			$new_tot_day = ($interval_length * 30);
			}
			elseif($interval_unit =='years' ){
			$new_tot_day = ($interval_length * 365);
			}
			
			/* Interval transfer to days End */

			$total_length 	=  get_post_meta( $post->ID, 'wps_mnm_subscription_total_length_int', true );
			$total_unit 	= get_post_meta( $post->ID, 'wps_mnm_subscription_total_length_char', true );
			
			/**** Interval unit transfer to days Start**/
			if($total_unit == 'days'){
			$new_tot_length = $total_length;
			}
			elseif($total_unit =='weeks' ){
			$new_tot_length = ($total_length * 7);
			}
			elseif($total_unit =='months' ){
			$new_tot_length = ($total_length * 30);
			}
			elseif($total_unit =='years' ){
			$new_tot_length = ($total_length * 365);
			}
			/**** Interval unit transfer to days End**/
			if($new_tot_day > $new_tot_length){
			echo 'sorry';
			}?>
			<p class="error_message" id="error-msg" style="display:none; color:red;font-size: 15px;">
			Subscription total length must greater than subscription renewal length .</p>
			 <?php 
			 /* woocommerce_wp_text_input(
			  array(
			   'id'                => 'wps_mnm_subscription_signup_fees_field',
			   'label'             => __( 'Signup Fees', 'woocommerce' ),
			   'placeholder'       => '',
			   'desc_tip'    => 'true',
			   'description'       => __( 'Enter Signup Fees.', 'woocommerce' ),
			   'type'              => 'text'
			   )); 
			   woocommerce_wp_text_input(
			  array(
			   'id'                => 'wps_mnm_subscription_sale_price_field',
			   'label'             => __( 'Sale Price', 'woocommerce' ),
			   'placeholder'       => '',
			   'desc_tip'    => 'true',
			   'description'       => __( 'Enter Sale Price.', 'woocommerce' ),
			   'type'              => 'text'
			   )); 
			   woocommerce_wp_text_input(
			  array(
			   'id'                => 'wps_mnm_subscription_sale_price_from_date_field',
			   'label'             => __( 'From Date', 'woocommerce' ),
			   'placeholder'       => 'From Date',
			   'desc_tip'    => 'true',
			   'description'       => __( 'Enter From Date.', 'woocommerce' ),
			   'type'              => 'text'
			   )); 
			    woocommerce_wp_text_input(
			  array(
			   'id'                => 'wps_mnm_subscription_sale_price_to_date_field',
			   'label'             => __( 'To Date', 'woocommerce' ),
			   'placeholder'       => 'To Date',
			   'desc_tip'    => 'true',
			   'description'       => __( 'Enter To Date.', 'woocommerce' ),
			   'type'              => 'text'
			   )); 
			   woocommerce_wp_text_input(
			  array(
			   'id'                => 'wps_mnm_subscription_free_trial_field',
			   'label'             => __( 'Free Trial', 'woocommerce' ),
			   'placeholder'       => '0',
			   'desc_tip'    => 'true',
			   'description'       => __( 'Enter Free Trial.', 'woocommerce' ),
			   'type'              => 'text'
			   )); 
			   woocommerce_wp_select( 
				array( 
				'id'          => '_select['.$loop.']', 
				'name'          => 'wps_mnm_free_trial_option', 
				'label'       => __( 'Free trial Option', 'woocommerce' ), 
				'description' => __( 'Choose a value.', 'woocommerce' ),
				'value'       => get_post_meta( $post->ID, 'wps_mnm_free_trial_option', true ),
				'options' => array(
					'days'   => __( 'days', 'woocommerce' ),
					'weeks'   => __( 'weeks', 'woocommerce' ),
					'months' => __( 'months', 'woocommerce' ),
					'years' => __( 'years', 'woocommerce' )
					)
				)
				);*/
		?></div>

	</div><?php
}
add_action( 'woocommerce_product_data_panels', 'rental_options_product_tab_content' );
/**
 * Save the custom fields.
 */
function save_rental_option_field( $post_id ) {
	$rental_option = isset( $_POST['_enable_renta_option'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_enable_renta_option', $rental_option );
	if ( isset( $_POST['_text_input_y'] ) ) :
		update_post_meta( $post_id, '_text_input_y', sanitize_text_field( $_POST['_text_input_y'] ) );
	endif;
	$price_field = $_POST['wps_mnm_subscription_price_field'];
	if( !empty( $price_field ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_price_field', esc_attr( $price_field) );
	
	$signup_fees = $_POST['wps_mnm_subscription_signup_fees_field'];
	if( !empty( $signup_fees ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_signup_fees_field', esc_attr( $signup_fees) );
	
	$free_trial = $_POST['wps_mnm_subscription_free_trial_field'];
	if( !empty( $free_trial ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_free_trial_field', esc_attr( $free_trial) );
	
	$sale_price = $_POST['wps_mnm_subscription_sale_price_field'];
	if( !empty( $sale_price ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_sale_price_field', esc_attr( $sale_price) );
	
	$sale_from_date = $_POST['wps_mnm_subscription_sale_price_from_date_field'];
	if( !empty( $sale_from_date ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_sale_price_from_date_field', esc_attr( $sale_from_date) );
	
	$sale_to_date = $_POST['wps_mnm_subscription_sale_price_to_date_field'];
	if( !empty( $sale_to_date ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_sale_price_to_date_field', esc_attr( $sale_to_date) );
	
	$range = $_POST['wps_mnm_subscription_renew_int'];
	if( !empty( $range ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_renew_int', esc_attr( $range) );
	
	$last_range = $_POST['wps_mnm_subscription_renew_char'];
	if( !empty( $last_range ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_renew_char', esc_attr( $last_range) );
	
	$subscription_length = $_POST['wps_mnm_subscription_total_length_int'];
	if( !empty( $subscription_length ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_total_length_int', esc_attr( $subscription_length) );

	$subscription_length = $_POST['wps_mnm_subscription_total_length_char'];
	if( !empty( $subscription_length ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_total_length_char', esc_attr( $subscription_length) );
	
	$free_trial_option = $_POST['wps_mnm_free_trial_option'];
	if( !empty( $free_trial_option ) )
	update_post_meta( $post_id, 'wps_mnm_free_trial_option', esc_attr( $free_trial_option) );
	
	$payment_method = $_POST['wps_mnm_subscription_payment_method'];
	if( !empty( $payment_method ) )
	update_post_meta( $post_id, 'wps_mnm_subscription_payment_method', esc_attr( $payment_method) );
	
}
add_action( 'woocommerce_process_product_meta_spwcsubscription', 'save_rental_option_field'  );

/**
 * Hide Attributes data panel.
 */
/*function hide_attributes_data_panel( $tabs) {
	$tabs['attribute']['class'][] = 'hide_if_spwcsubscription hide_if_variable_rental';
	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'hide_attributes_data_panel' );*/

add_action('admin_footer', 'wps_mnm_calender_pop_subscription_product_general_area');
function wps_mnm_calender_pop_subscription_product_general_area(){?>
<script type="text/javascript">
jQuery(function($) {
	 //alert('ok');
			jQuery("#wps_mnm_subscription_sale_price_from_date_field").datepicker({
				dateFormat: "yy/mm/dd",
			});
			jQuery("#wps_mnm_subscription_sale_price_to_date_field").datepicker({
				dateFormat: "yy/mm/dd",
			});
    });
</script>
<?php
    }
    
add_action('admin_footer', 'wps_mnm_check_renew_date_less_than_seven_days');
function wps_mnm_check_renew_date_less_than_seven_days(){?>
<script type="text/javascript">
jQuery(function($) {
	 //alert('ok');
		$('.sub_tot_length').on('change', function() {
			$("#error-msg").hide();
			var sub_tot_length = $(this).find(":selected").val();
			//alert(sub_tot_length);
			var sub_tot_renewtext = $(".sub_tot_renew").find("option:selected").text();
			var sub_tot_renew = $(".sub_tot_renew").val();		
			//alert(sub_tot_renew);
			
			
			var renew_day = $(".renew_day").val();
			//alert(renew_day);
			var renewtext = $(".length").find("option:selected").text();
			var renew_length = $(".length").val();
			//alert(renew_length);
			
			if(sub_tot_length == 'days'){
			new_tot_length = sub_tot_renew;
			}
			if(sub_tot_length =='weeks' ){
			new_tot_length = (sub_tot_renew * 7);
			}
			if(sub_tot_length =='months' ){
			new_tot_length = (sub_tot_renew * 30);
			}
			if(sub_tot_length =='years' ){
			new_tot_length = (sub_tot_renew * 365);
			//alert(new_tot_length);
			}
			
			
			if(renew_length == 'days'){
			new_tot_day = renew_day;
			}
			if(renew_length =='weeks' ){
			new_tot_day = (renew_day * 7);
			}
			if(renew_length =='months' ){
			new_tot_day = (renew_day * 30);
			}
			if(renew_length =='years' ){
			new_tot_day = (renew_day * 365);
			//alert(new_tot_day);
			}
			if(new_tot_day > new_tot_length)
            {
                $("#error-msg").show();
            }
		});
		
		
		$('.sub_tot_renew').on('change', function() {
			$("#error-msg").hide();
			var sub_tot_renew = $(this).find(":selected").val();
			//alert(sub_tot_renew);
			var sub_tot_lengthtext = $(".sub_tot_length").find("option:selected").text();
			var sub_tot_length = $(".sub_tot_length").val();		
			//alert(sub_tot_length);
			
			
			var renew_day = $(".renew_day").val();
			//alert(renew_day);
			var renewtext = $(".length").find("option:selected").text();
			var renew_length = $(".length").val();
			//alert(renew_length);
			
			if(sub_tot_length == 'days'){
			new_tot_length = sub_tot_renew;
			}
			if(sub_tot_length =='weeks' ){
			new_tot_length = (sub_tot_renew * 7);
			}
			if(sub_tot_length =='months' ){
			new_tot_length = (sub_tot_renew * 30);
			}
			if(sub_tot_length =='years' ){
			new_tot_length = (sub_tot_renew * 365);
			//alert(new_tot_length);
			}
			
			
			if(renew_length == 'days'){
			new_tot_day = renew_day;
			}
			if(renew_length =='weeks' ){
			new_tot_day = (renew_day * 7);
			}
			if(renew_length =='months' ){
			new_tot_day = (renew_day * 30);
			}
			if(renew_length =='years' ){
			new_tot_day = (renew_day * 365);
			//alert(new_tot_day);
			}
			if(new_tot_day > new_tot_length)
            {
                $("#error-msg").show();
            }
		});
		
		$('.length').on('change', function() {
			$("#error-msg").hide();
			var sub_tot_renew = $(this).find(":selected").val();
			//alert(sub_tot_renew);
			var sub_tot_lengthtext = $(".renew_day").find("option:selected").text();
			var sub_tot_length = $(".renew_day").val();		
			//alert(sub_tot_length);
			
			
			var renew_day = $(".sub_tot_renew").val();
			//alert(renew_day);
			var renewtext = $(".sub_tot_length").find("option:selected").text();
			var renew_length = $(".sub_tot_length").val();
			//alert(renew_length);
			
			if(sub_tot_renew == 'days'){
			new_tot_length = sub_tot_length;
			}
			if(sub_tot_renew =='weeks' ){
			new_tot_length = (sub_tot_length * 7);
			}
			if(sub_tot_renew =='months' ){
			new_tot_length = (sub_tot_length * 30);
			}
			if(sub_tot_renew =='years' ){
			new_tot_length = (sub_tot_length * 365);
			//alert(new_tot_length);
			}
			
			
			if(renew_length == 'days'){
			new_tot_day = renew_day;
			}
			if(renew_length =='weeks' ){
			new_tot_day = (renew_day * 7);
			}
			if(renew_length =='months' ){
			new_tot_day = (renew_day * 30);
			}
			if(renew_length =='years' ){
			new_tot_day = (renew_day * 365);
			//alert(new_tot_day);
			}
			if(new_tot_length > new_tot_day)
            {
                $("#error-msg").show();
            }
		});
		
		$('.renew_day').on('change', function() {
			$("#error-msg").hide();
			var sub_tot_renew = $(this).find(":selected").val();
			//alert(sub_tot_renew);
			var sub_tot_lengthtext = $(".length").find("option:selected").text();
			var sub_tot_length = $(".length").val();		
			//alert(sub_tot_length);
			
			
			var renew_day = $(".sub_tot_renew").val();
			//alert(renew_day);
			var renewtext = $(".sub_tot_length").find("option:selected").text();
			var renew_length = $(".sub_tot_length").val();
			//alert(renew_length);
			
			if(sub_tot_length == 'days'){
			new_tot_length = sub_tot_renew;
			}
			if(sub_tot_length =='weeks' ){
			new_tot_length = (sub_tot_renew * 7);
			}
			if(sub_tot_length =='months' ){
			new_tot_length = (sub_tot_renew * 30);
			}
			if(sub_tot_length =='years' ){
			new_tot_length = (sub_tot_renew * 365);
			//alert(new_tot_length);
			}
			
			
			if(renew_length == 'days'){
			new_tot_day = renew_day;
			}
			if(renew_length =='weeks' ){
			new_tot_day = (renew_day * 7);
			}
			if(renew_length =='months' ){
			new_tot_day = (renew_day * 30);
			}
			if(renew_length =='years' ){
			new_tot_day = (renew_day * 365);
			//alert(new_tot_day);
			}
			if(new_tot_length > new_tot_day)
            {
                $("#error-msg").show();
            }
		});
		
    });
</script>
<?php
    }   
    
add_action('admin_footer', 'wps_mnm_adad');
function wps_mnm_adad(){?> 
<style>
.hide_if_grouped{
	display: block !important;
}
</style> 
</script>
<?php
    } 
