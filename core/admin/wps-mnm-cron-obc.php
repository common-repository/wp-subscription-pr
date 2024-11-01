<?php
class WPS_MNM_Cron
{
	function __construct($id)
	{
		$this->subid = $id;
		//echo '|>>'.$this->subid;
		add_action('init', array($this,'wps_mnm_cron_starter_activation'));
		add_filter( 'cron_schedules', array($this,'wps_mnm_cron_time_filters' ));
		add_action ('wps_mnm_subscription_cron', array($this,'wps_mnm_execute_data'));
	}
	public function wps_mnm_cron_starter_activation($subid){
		
		if( !wp_next_scheduled( 'wps_mnm_subscription_cron' ) ) {  
		   wp_schedule_event( time(), 'everyminute', 'wps_mnm_subscription_cron');  
		}
	}

	public function wps_mnm_cron_time_filters(){
		
		/** 09/10/2017 **/
		//$orderId = get_post_meta( $this->subid, '_wps-mnm-sub-order-id', true );
		/*$order = new WC_Order( $orderId );
		$items = $order->get_items();
		$prod_new_array = array();
		foreach ( $items as $item ) {
			$product_id = $item['product_id'];
			$prod_new_array[] = $product_id; 
		}
		$prod_id = implode(',',$prod_new_array);*/
		/*global $wpdb;
		$result = $wpdb->get_results('select t1.order_item_id, t2.* FROM 
		wp_woocommerce_order_items as t1 JOIN wp_woocommerce_order_itemmeta as t2 ON t1.order_item_id = t2.order_item_id
		where t1.order_id='.$orderId);
		$prod_id = $result['_product_id'];*/


		
		
		/*** 09/10/2017 ***/

		$prod_id = 186;
		$subscription_renew_int = get_post_meta($prod_id, 'wps_mnm_subscription_renew_int', true);
		$subscription_renew_char = get_post_meta($prod_id, 'wps_mnm_subscription_renew_char', true);

		$secondsInAMinute 	= 60;
	    $secondsInAnHour  	= 60 * $secondsInAMinute;
	    $secondsInADay    	= 24 * $secondsInAnHour;
	    $secondsInAWeek 	= 7 * $secondsInADay;
	    $secondsInAMonth 	= 30 * $secondsInADay;
	    $secondsInAYear 	= 12 * $secondsInAMonth;

		if($subscription_renew_char =='weeks' ){
			$seconds = $secondsInAWeek*$subscription_renew_int;
		}
		elseif($subscription_renew_char =='days' ){
			$seconds = $secondsInADay*$subscription_renew_int;
		}
		elseif($subscription_renew_char =='months' ){
			$seconds = $secondsInAMonth*$subscription_renew_int;
		}
		elseif($subscription_renew_char =='years' ){
			$seconds = $secondsInAMonth*$subscription_renew_int;
		}
		elseif($subscription_renew_char =='minutes'){
			$seconds = $secondsInAMinute*$subscription_renew_int;
		}

		$schedules['everyminute'] = array(
		    'interval' => $seconds,
		    'display' => 'For every '. $subscription_renew_int .' ' .$subscription_renew_char
	    );
	    return $schedules;
	}

	public function wps_mnm_execute_data(){
		$prod_id = get_post_meta( $this->subid, '_wps-mnm-sub-product-ids', true );
		 $my_post = array(
		   'post_title'    => $prod_id.' Cron on '.date("d-m-Y h:i:s"),
		   'post_content'  => 'test',
		   'post_status'   => 'publish',
		   'post_author'   => 1,
		   'post_type'	  => 'cron'
		 );
		 
		// Insert the post into the database
		wp_insert_post( $my_post );
		//$orderId = get_post_meta( $this->subid, '_wps-mnm-sub-order-id', true );
		
		/** 09/10/2017 **/
		/*$order = new WC_Order( $orderId );
		$items = $order->get_items();
		$prod_array = array();
		foreach ( $items as $item ) {
			$product_id = $item['product_id'];
			$prod_array[] = $product_id; 
		}
		$productIds = implode(',',$prod_array);*/
		/*global $wpdb;
		$result = $wpdb->get_results('select t1.order_item_id, t2.* FROM 
		wp_woocommerce_order_items as t1 JOIN wp_woocommerce_order_itemmeta as t2 ON t1.order_item_id = t2.order_item_id
		where t1.order_id='.$orderId);
		$productIds = $result['_product_id'];*/
		/** 09/10/2017 **/
		//$productIds = get_post_meta( $this->subid, '_wps-mnm-sub-product-ids', true );
		/*$productIds =  186;
		global $wpdb;
		$table = $wpdb->prefix . 'postmeta';
 		$sql = 'SELECT * FROM `'. $table . '` WHERE post_id = '. $orderId;
 		$result = $wpdb->get_results($sql);*/
        /*foreach($result as $res) {
            if( $res->meta_key == '_billing_phone'){
                   $phone = $res->meta_value;      // get billing phone
            }
            if( $res->meta_key == '_billing_first_name'){
                   $firstname = $res->meta_value;   // get billing first name
            }
            if( $res->meta_key == '_billing_last_name'){
                   $lastname = $res->meta_value;   // get billing last name
            }
            if( $res->meta_key == '_billing_email'){
                   $email = $res->meta_value;   // get billing email
            }
            if( $res->meta_key == '_billing_company'){
                   $company = $res->meta_value;   // get billing company name
            }
            if( $res->meta_key == '_billing_phone'){
                   $phone = $res->meta_value;   // get billing phone number
            }
            if( $res->meta_key == '_billing_address_1'){
                   $addr1 = $res->meta_value;   // get billing address one
            }
            if( $res->meta_key == '_billing_address_2'){
                   $addr2 = $res->meta_value;   // get billing address two
            }
            if( $res->meta_key == '_billing_country'){
                   $country = $res->meta_value;   // get billing country
            }
            if( $res->meta_key == '_billing_postcode'){
                   $postcode = $res->meta_value;   // get billing post code
            }
            if( $res->meta_key == '_billing_state'){
                   $state = $res->meta_value;   // get billing state
            }
            if( $res->meta_key == '_billing_city'){
                   $city = $res->meta_value;   // get billing city
            }
        }*/
		/*global $woocommerce;

		  $address = array(
		      'first_name' => $firstname,
		      'last_name'  => $lastname,
		      'company'    => $company,
		      'email'      => $email,
		      'phone'      => $phone,
		      'address_1'  => $addr1,
		      'address_2'  => $addr2,
		      'city'       => $city,
		      'state'      => $state,
		      'postcode'   => $postcode,
		      'country'    => $country
		  );

		  // Now we create the order
		  $order = wc_create_order();

		  // The add_product() function below is located in /plugins/woocommerce/includes/abstracts/abstract_wc_order.php
		  for($i=0; $i<=count($productIds); $i++)
		  {
		  	$order->add_product( get_product($productIds[$i]), 1);
		  }
		   // This is an existing SIMPLE product
		  
		  $order->set_address( $address, 'billing' );
		  //
		  $order->calculate_totals();
		  $order->update_status("Completed", 'Imported order', TRUE);  */
	}
}

$args = array(
	'numberposts' => -1,
  	'post_type'   => 'wps-mnm-subscription'
);
 
$all_subscription = get_posts( $args );
foreach ($all_subscription as $sub) {
	new WPS_MNM_Cron($sub->ID);
	break;
}
