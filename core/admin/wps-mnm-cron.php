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
		$prod_id = get_post_meta( $this->subid, '_wps-mnm-sub-product-ids', true );
		//$prod_id = 186;
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
		
		/*$subscription_renew_int = 100;
		$subscription_renew_char = 200;
		$seconds = 60;*/

		$schedules['everyminute'] = array(
		    'interval' => $seconds,
		    'display' => 'For every '. $subscription_renew_int .' ' .$subscription_renew_char
	    );
	    return $schedules;
	}

	public function wps_mnm_execute_data(){
		$status = get_post_meta($this->subid, '_wps-mnm-sub-status', true);
		if($status == 'Processing'){
		 /*$my_post = array(
		   'post_title'    => $this->subid.' Cron on '.date("d-m-Y h:i:s"),
		'post_content'  => 'test',
			'post_status'   => 'publish',
		  'post_author'   => 1,
		   'post_type'	  => 'cron'
		 );
		 
		// // Insert the post into the database
		wp_insert_post( $my_post );*/
		$orderId = get_post_meta( $this->subid, '_wps-mnm-sub-order-id', true );
		$productIds = get_post_meta( $this->subid, '_wps-mnm-sub-product-ids', true );
		//$productIds =  186;
		global $wpdb;
		$table = $wpdb->prefix . 'postmeta';
 		$sql = 'SELECT * FROM `'. $table . '` WHERE post_id = '. $orderId;
 		$result = $wpdb->get_results($sql);
        foreach($result as $res) {
			/*if( $res->meta_key == '_customer_user'){
                   $customer_id = $res->meta_value;      // get Customer ID
            }*/
            if( $res->meta_key == '_billing_phone'){
                   $phone = $res->meta_value;      // get billing phone
            }
            if( $res->meta_key == '_billing_first_name'){
                   $firstname = $res->meta_value;   // get billing first name
            }
            if( $res->meta_key == '_billing_last_name'){
                   $lastname = $res->meta_value;   // get billing first name
            }
            if( $res->meta_key == '_billing_email'){
                   $email = $res->meta_value;   // get billing first name
            }
            if( $res->meta_key == '_billing_company'){
                   $company = $res->meta_value;   // get billing first name
            }
            if( $res->meta_key == '_billing_phone'){
                   $phone = $res->meta_value;   // get billing first name
            }
            if( $res->meta_key == '_billing_address_1'){
                   $addr1 = $res->meta_value;   // get billing first name
            }
            if( $res->meta_key == '_billing_address_2'){
                   $addr2 = $res->meta_value;   // get billing first name
            }
            if( $res->meta_key == '_billing_country'){
                   $country = $res->meta_value;   // get billing first name
            }
            if( $res->meta_key == '_billing_postcode'){
                   $postcode = $res->meta_value;   // get billing first name
            }
            if( $res->meta_key == '_billing_state'){
                   $state = $res->meta_value;   // get billing first name
            }
            if( $res->meta_key == '_billing_city'){
                   $city = $res->meta_value;   // get billing first name
            }
        }
		global $woocommerce;
		$customer_id = get_post_meta( $this->subid, '_wps-mnm-sub-user-id', true );
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
		      'country'    => $country,
		      'customer_id'    => $customer_id
		  );

		  // Now we create the order
		 
		  $order = wc_create_order();
		 
		  global $wpdb;

			$query = "SELECT ID FROM $wpdb->posts WHERE post_type='shop_order' ORDER BY ID DESC LIMIT 0,1";

			$result = $wpdb->get_results($query);
			$row = $result[0];
			$order_latest_id = $row->ID;
		  // The add_product() function below is located in /plugins/woocommerce/includes/abstracts/abstract_wc_order.php
		  /*for($i=0; $i<=count($productIds); $i++)
		  {
		  	$order->add_product( get_product($productIds[$i]), 1);
		  }*/
		   // This is an existing SIMPLE product
		  $order->add_product( get_product($productIds));
		  $order->set_address( $address, 'billing' );
		  //
		  $order->calculate_totals();
		  $order->update_status("Completed", 'Imported order', TRUE);
		  
		  
		  $order_id_arr = get_post_meta( $this->subid, '_wps-mnm-sub-order-id', true );
		  if(is_array($order_id_arr)){
			//$order_id_arr = json_decode($order_id_arr);
			array_push($order_id_arr,$order_latest_id);
		}
		else{
			$order_id_arr = array();
			$old_order_id = get_post_meta( $this->subid, '_wps-mnm-sub-order-id', true );
			array_push($order_id_arr,$old_order_id);
			array_push($order_id_arr,$order_latest_id);
			
		}
		/*$customer_id = get_post_meta( $this->subid, '_wps-mnm-sub-user-id', true );
		$my_post = array(
		   'post_title'    => $customer_id.' Cron on '.date("d-m-Y h:i:s"),
		   'post_content'  => 'test',
		   'post_status'   => 'publish',
		   'post_author'   => 1,
		   'post_type'	  => 'cron'
		 );
		 
		// // Insert the post into the database
		wp_insert_post( $my_post );*/
		
		update_post_meta( $order_latest_id, '_wps-mnm-sub_id_for_order', $this->subid );

			  
		update_post_meta( $this->subid, '_wps-mnm-sub-order-id', $order_id_arr );
		
		
		update_post_meta($order_latest_id, '_customer_user', $customer_id);
		}
		  
	}
	
	
}

$args = array(
	'numberposts' => -1,
  	'post_type'   => 'wps-mnm-subscription'
);
 
$all_subscription = get_posts( $args );
foreach ($all_subscription as $sub) {
	//echo 'DTDC '.$sub->ID ;
	new WPS_MNM_Cron($sub->ID);
	break;
}
