<?php
class WPS_MNM_Metaboxes
{
	
	public function __construct()
	{
		add_action( 'add_meta_boxes', array($this,'wps_mnm_add_subscription_metaboxes' ),30);
		add_action( 'save_post', array($this,'save_subscription_status_field'  ));
		add_action('admin_footer', array($this,'wps_mnm_calender_pop_in_subscription_area'));		
	}
	public static function wps_mnm_calender_pop_in_subscription_area(){?>
	<!--<script type="text/javascript">
	jQuery(function($) {
		 alert('ok');
				jQuery("#subscription_start_date").datepicker({
					dateFormat: "yy/mm/dd",
				});
				jQuery("#subscription_trial_date").datepicker({
					dateFormat: "yy/mm/dd",
				});
				jQuery("#subscription_end_date").datepicker({
					dateFormat: "yy/mm/dd",
				});
		});
	</script>-->
	<?php
		}

	public static function wps_mnm_add_subscription_metaboxes(){
		add_meta_box('wps-mnm-subscription-to-order-link', 'Order Link', array('WPS_MNM_Metaboxes','wps_mnm_subscription_to_order_link'), 'wps-mnm-subscription', 'side', 'high');

		
		//add_meta_box('wps-mnm-subscription-date', 'Subscription Date', array('WPS_MNM_Metaboxes','wps_mnm_subscription_date'), 'wps-mnm-subscription', 'side', 'high');
		
		add_meta_box('wps-mnm-subscription-status-details', 'Subscription Status Details', array('WPS_MNM_Metaboxes','wps_mnm_subscription_status_details'), 'wps-mnm-subscription', 'side', 'high');
		
		add_meta_box('wps-mnm-subscription-billing-schedule', 'Subscription Billing Schedule', array('WPS_MNM_Metaboxes','wps_mnm_subscription_billing_schedule'), 'wps-mnm-subscription', 'side', 'high');
		
	}
	
	public static function wps_mnm_subscription_to_order_link() {
		global $post;
		$order_id = get_post_meta($post->ID, '_wps-mnm-sub-order-id', true);
		//echo '<pre>'; print_r($order_id); echo '</pre>';
		$tot_order_id = count($order_id);
		if($order_id){
			echo '<ul>';
			/*for ($i = 0; $i < $tot_order_id; $i++) {
			  $url = admin_url().'post.php?post='.$order_id.'&action=edit';
			  echo '<li><a href = "'.$url.'" target="_blank">Click here to view Order #'.$order_id.' Details</a></li>';
			}*/
			for ($i = 0; $i < $tot_order_id; $i++) {
				if($i == 0 && $tot_order_id == 1)
				{
					$url = admin_url().'post.php?post='.$order_id.'&action=edit';
					echo '<li><a href = "'.$url.'" target="_blank">Click here to view Order #'.$order_id.' Details</a></li>';
				}
				else
				{
					 $url = admin_url().'post.php?post='.$order_id[$i].'&action=edit';
					 echo '<li><a href = "'.$url.'" target="_blank">Click here to view Order #'.$order_id[$i].' Details</a></li>';
				}		 		  
			}
			echo '</ul>';
		}
		else{
			echo 'No Order Found';
		}
		/*$url = admin_url().'post.php?post='.$order_id.'&action=edit';
		echo '<a href = "'.$url.'" target="_blank">Click here to view Order Details</a>';*/
	}

	public static function wps_mnm_subscription_details() {
		global $post;
		$order_id = get_post_meta($post->ID, '_wps-mnm-sub-order-id', true);
		echo "Subscription Details Comming Soon...";
	}

	public static function wps_mnm_subscription_date() {
		global $post;
		$order_date = get_the_date();
	    echo $order_date ;
	}
	
	public static function wps_mnm_subscription_status_details() {
		global $post;
		$subscription_id = $post->ID;
		
		/*$order_id = get_post_meta($post->ID, '_wps-mnm-sub-order-id', true);
		$order = new WC_Order( $order_id );
		$order_status = $order->get_status();  
		echo $order_status;*/  
		woocommerce_wp_select( 
				array( 
				'id'          => '_select['.$loop.']', 
				'name'          => 'wps_mnm_sub_status', 
				'value'       => get_post_meta( $subscription_id, '_wps-mnm-sub-status', true ),
				'options' => array(
					'Active'   => __( 'Active', 'woocommerce' ),
					'Pending'   => __( 'Pending', 'woocommerce' ),
					'Processing'   => __( 'Processing', 'woocommerce' ),
					'Onhold'   => __( 'OnHold', 'woocommerce' ),
					'Completed'   => __( 'Completed', 'woocommerce' ),
					'Cancelled'   => __( 'Cancelled', 'woocommerce' ),
					'Failed' => __( 'Failed', 'woocommerce' ),
					)
				)
				); ?>
				<input name="save" type="submit" class="button button-primary button-large" id="publish" value="Update">
	<?php
	}
	
	/**
	 * Save the Subscribtion Status.
	 */
	public static function save_subscription_status_field( $subscription_id ) {	
		
		$sub_status = $_POST['wps_mnm_sub_status'];
		if( !empty( $sub_status ) )
		update_post_meta( $subscription_id, '_wps-mnm-sub-status', esc_attr( $sub_status) );
		WPS_MNM_Additional_Settings::send_subscription_status_email_to_customer($subscription_id,$sub_status);
		
	}


	
	public static function wps_mnm_subscription_billing_schedule() {
		global $post;
		
		//$order_date = '2017-09-25'; 
		$order_date = get_the_date('Y-m-d H:i:s'); 
		//echo $order_date;
		$minutes =  get_the_time('H'); 
		$seconds =  get_the_time('i');
		//echo $post->ID; 
		$prod_id = get_post_meta($post->ID, '_wps-mnm-sub-product-ids', true);
		
		$free_trial = get_post_meta($prod_id, 'wps_mnm_subscription_free_trial_field', true);
		$free_trial_option = get_post_meta($prod_id, 'wps_mnm_free_trial_option', true);
		
		$subscription_length_int = get_post_meta($prod_id, 'wps_mnm_subscription_total_length_int', true);
		$subscription_length_char = get_post_meta($prod_id, 'wps_mnm_subscription_total_length_char', true);

		$subscription_end = date("Y-m-d H:i:s",strtotime('+'.$subscription_length_int. ''.$subscription_length_char ,strtotime($order_date)));
		
		if($free_trial_option =='days'){
			$date = date('Y-m-d H:i:s',strtotime($order_date) + (24*3600*$free_trial));
			} 
		elseif($free_trial_option =='weeks'){
			$date = date('Y-m-d H:i:s',strtotime($order_date) + (7*24*3600*$free_trial));
			} 	
		elseif($free_trial_option =='months'){
			$date = date("Y-m-d H:i:s",strtotime('+'.$free_trial. 'months' ,strtotime($order_date)));
			}
		elseif($free_trial_option =='years'){
			$date = date('Y-m-d H:i:s', strtotime('+'.$free_trial. 'years', strtotime($order_date)));
			}		
		/* Next Payment Date Calculation */	
		
		$subscription_renew_int = get_post_meta($prod_id, 'wps_mnm_subscription_renew_int', true);
		$subscription_renew_char = get_post_meta($prod_id, 'wps_mnm_subscription_renew_char', true);

		if($subscription_renew_char =='weeks' ){
			$nex_payment_date = date('Y-m-d H:i:s',strtotime($order_date) + (7*24*3600*$subscription_renew_int));
			}
		elseif($subscription_renew_char =='days' ){
			$nex_payment_date = date('Y-m-d H:i:s',strtotime($order_date) + (24*3600*$subscription_renew_int));
			}
		elseif($subscription_renew_char =='months' ){
			$nex_payment_date = date("Y-m-d H:i:s",strtotime('+'.$subscription_renew_int. 'months' ,strtotime($order_date)));
			}
		elseif($subscription_renew_char =='years' ){
			$nex_payment_date = date('Y-m-d H:i:s', strtotime('+'.$subscription_renew_int. 'years', strtotime($order_date)));
			}
		elseif($subscription_renew_char =='minutes'){
			$nex_payment_date = date('Y-m-d H:i:s', strtotime('+'.$subscription_renew_int. 'minutes', strtotime($order_date)));
		}	
		?>
		
				
		<?php /* <h3>Recurring:</h3> <div class='recurring'><?php
		 woocommerce_wp_select( 
				array( 
				'id'          => '_select['.$loop.']', 
				'name'          => 'wps_mnm_subscription_renew_int', 
				'value'       => get_post_meta( $prod_id, 'wps_mnm_subscription_renew_int', true ),
				'options' => array(
					'2'   => __( 'every 2nd', 'woocommerce' ),
					'3'   => __( 'every 3rd', 'woocommerce' ),
					'4' => __( 'every 4th', 'woocommerce' ),
					'5' => __( 'every 5th', 'woocommerce' ),
					'6' => __( 'every 6th', 'woocommerce' )
					)
				)
				);
				
		 woocommerce_wp_select( 
				array( 
				'id'          => '_select['.$loop.']', 
				'name'          => 'wps_mnm_subscription_renew_char', 
				'value'       => get_post_meta( $prod_id, 'wps_mnm_subscription_renew_char', true ),
				'options' => array(
					'day'   => __( 'day', 'woocommerce' ),
					'week'   => __( 'week', 'woocommerce' ),
					'month' => __( 'month', 'woocommerce' ),
					'year' => __( 'year', 'woocommerce' )
					)
				)
				); ?></div>
				*/?>
		<h3>Start Date:</h3>
		<p>
			<input type="text" name="" id="subscription_start_date" value="<?php echo $order_date; ?>" />
		</p>
		<h3>Trial End:</h3>
		<p>
			<input type="text" name="" id ="subscription_trial_date" value="<?php echo $date; ?>" /> 
		</p>
		<h3>Next Payment:</h3>
		<p>
			<input type="text" name="" value="<?php echo $nex_payment_date; ?>" /> 
		</p>
		<h3>End Date:</h3>
		<p>
			<input type="text" name="subscription_end_date" id="subscription_end_date" value="<?php echo $subscription_end; ?>" /> 
		</p>
		<h3>Subscription Notes:</h3>
		<p>
			<textarea name="subscription_notes" rows="4" cols="25" placeholder="Enter notes here.."></textarea>			
		</p><?php
	    
	}
	
	
    
}new WPS_MNM_Metaboxes;



