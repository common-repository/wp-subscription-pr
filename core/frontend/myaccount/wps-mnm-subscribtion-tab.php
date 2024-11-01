<?php
class WPS_MNM_Subscription_Tab
{
	
	public function __construct()
	{
		add_action( 'init', array($this,'wps_mnm_add_subscription_endpoint' ));
		add_filter( 'query_vars', array($this,'wps_mnm_subscription_query_vars'), 0 );
		add_filter( 'woocommerce_account_menu_items', array($this,'wps_mnm_add_subscription_link_my_account' ));
		add_action( 'woocommerce_account_wps-mnm-subscription_endpoint', array($this,'wps_mnm_subscription_content' ));
		add_action( 'init', array($this,'wps_mnm_subscription_status_change' ));
		//add_action( 'init', array($this,'wps_mnm_auth_recurring_cancel'));
			
		add_action('wp_footer', array($this,'wps_mnm_click_pay'));
	}
	
	public static function wps_mnm_click_pay(){?>
	<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery("#pay_options").hide();
		jQuery("#yesCheck").click(function(){
			jQuery("#pay_options").show();
		})
		
	})
	function open_pay_form(id){
		//alert(" "+id);
		jQuery(".form-style-2").hide();
		jQuery("#pay_options_"+id).show();
	}

	</script>
	<?php
		}
		
	public static function wps_mnm_add_subscription_endpoint() {
	    add_rewrite_endpoint( 'wps-mnm-subscription', EP_ROOT | EP_PAGES );
	    flush_rewrite_rules();
	}
	 

	 
	public static function wps_mnm_subscription_query_vars( $vars ) {
	    $vars[] = 'wps-mnm-subscription';
	    return $vars;
	}
	 

	 
	public static function wps_mnm_add_subscription_link_my_account( $items ) {
	    $items['wps-mnm-subscription'] = 'Subscription';
	    return $items;
	}
	 

	public static function wps_mnm_subscription_content() {
		echo '<h3>Subscribtion Details</h3>';
		/*echo $_GET['wp_payment_msg'];
		unset($_GET['wp_payment_msg']);*/
		$args = array(
	    	'author' => get_current_user_id(),
	    	'post_type' => 'wps-mnm-subscription',
	    	'post_per_page' => -1
	    	/*'meta_key'      => '_wps-mnm-sub-status',
			'meta_value'    => array('Active','Pending','Processing','Onhold','Completed','Cancelled','Failed')*/
		);

	    $users_subscription = new WP_Query( $args ); 
	    //echo '<pre>';print_r($users_subscription);?>
		
			<table>
				<thead>
					<tr>
						<th>Subscription Id</th>
						<th>Subscription Date</th>
						<th>Subscription Status</th>
						<th>Payment Method</th>
						<th>Action</th>
					</tr>
				</thead>
				
				<tbody>
			<?php 
		 if ($users_subscription->have_posts()) : while ($users_subscription->have_posts()) : $users_subscription->the_post(); 
			?>	
			<?php		
		        $order_id = get_post_meta(get_the_ID(),'_wps-mnm-sub-order-id',true);
		        //print_r($order_id);
		        $payment_method = get_post_meta($order_id, '_payment_method', true );
		        global $post;
				$subscription_id = $post->ID;
				//echo 'abc '.$subscription_id ;
					
				$authorID = $post->post_author;				
				$user_info = get_userdata($authorID);
				$user_email = $user_info->user_email;
		        ?>
		        <tr>
		        	<td>#<?php echo get_the_ID(); ?></td>
		        	<td><?php echo get_the_date('F j, Y',get_the_ID()); ?></td>
		        	<td><?php echo $sub_status = get_post_meta( $subscription_id, '_wps-mnm-sub-status', true ); ?></td>
		        	<td><?php echo $payment_method; ?></td>
		        	
		        	<td>
						<?php if($sub_status != 'Cancelled'){ ?>
						<form method="POST" action="" name="sub_action_form_front" id="sub_action_form_front">
						<input type="hidden" name="sub_id" value="<?php echo $subscription_id; ?>" />
						<input type="hidden" name="wps_mnm_hidden_action" value="status_change_frontend" />
						<input type="hidden" name="sub_action" value="Cancelled" />							
						<input type="submit" name ="substatus" value="Cancel"  />			
						</form>	
						<?php }else{ ?>
							-------
							<?php }?>			
					</td>
					
		        </tr>      

				 <?php endwhile; ?>
				<?php endif; ?>
					</tbody>
				</table>
		    <?php
			
		    wp_reset_postdata();
		
	    
	}
	
	public static function wps_mnm_subscription_status_change() {
		
		if(isset($_POST['wps_mnm_hidden_action'])){
			if($_POST['wps_mnm_hidden_action'] == 'status_change_frontend'){
				if(WPS_MNM_Subscription_Tab::wps_mnm_auth_recurring_cancel($_POST['sub_id']))
				{			
					update_post_meta($_POST['sub_id'],'_wps-mnm-sub-status',$_POST['sub_action']);
				}
			}
		}
    }
    
    public static function wps_mnm_auth_recurring_cancel($sub_id){
	
		$order_id = get_post_meta($sub_id,'_wps-mnm-sub-order-id',true);
		
		$payment_method = get_post_meta($order_id, '_payment_method', true );
		
		if($payment_method == 'wps_mnm_auth_net')
		{
			require_once (WPS_MNM_INC . '/anet_php_sdk-master/AuthorizeNet.php') ;
			 
			/*define("AUTHORIZENET_API_LOGIN_ID", "48QyB6Fy64f");
			define("AUTHORIZENET_TRANSACTION_KEY", "6qfLy5x9G429KHLw");
			define("AUTHORIZENET_SANDBOX", true);*/
			
			$woocommerce_wps_mnm_auth_net_settings =  get_option('woocommerce_wps_mnm_auth_net_settings');
			//echo '<pre>';print_r($woocommerce_wps_mnm_auth_net_settings);
			$api_login = esc_attr($woocommerce_wps_mnm_auth_net_settings['api_login']);
			$trans_key = esc_attr($woocommerce_wps_mnm_auth_net_settings['trans_key']);
			$env = esc_attr($woocommerce_wps_mnm_auth_net_settings['environment']);
			
			if($env == 'no'){
				define("AUTHORIZENET_SANDBOX", false);
			}else{
				define("AUTHORIZENET_SANDBOX", true);
			}
			
			define("AUTHORIZENET_API_LOGIN_ID", $api_login);
			define("AUTHORIZENET_TRANSACTION_KEY", $trans_key );
			
			//$subscription_id = 4797731;
			$subscription_id = get_post_meta($sub_id, '_wps-mnm-sub-auth_sub_id', true);
			
		// Cancel the subscription
			$cancellation = new AuthorizeNetARB;
			$cancel_response = $cancellation->cancelSubscription($subscription_id);
			//$ob= simplexml_load_string($cancel_response);
			$json  = json_encode($cancel_response);		
			$configData = json_decode($json, true);
			
			$xml = $configData['xml'];
			$messages = $xml['messages'];
			$resultCode = $messages['resultCode']; // Error    OR     Ok
				
			if ($resultCode == "Ok") 
			{
				//echo "Subcription ID <b>" .$subscription_id. "</b> was successfully cancelled.<br><br>";
				return true;
			} 
			else 
			{
				//echo "The operation failed.";
				return false;
			}
			
        }
        else if($payment_method == 'twocheckout')
        {
			//require_once (WPS_MNM_INC . '/2Checkout/TwocheckoutApi.php') ; 
			require_once (WPS_MNM_INC . '/2checkout-php-master/lib/Twocheckout.php') ;  //die();
			
			$woocommerce_twocheckout_settings =  get_option('woocommerce_twocheckout_settings');
			//echo '<pre>';print_r($woocommerce_twocheckout_settings); die();
			$seller_id = $woocommerce_twocheckout_settings['seller_id'];
			$publishable_key = $woocommerce_twocheckout_settings['publishable_key'];
			$private_key = $woocommerce_twocheckout_settings['private_key'];
			$env = $woocommerce_twocheckout_settings['sandbox'];
			$Api_user = $woocommerce_twocheckout_settings['api_user_name'];
			$Api_password = $woocommerce_twocheckout_settings['api_user_password'];
			
			if($env == 'no'){
				//echo 'Sandbox Active nei';
				Twocheckout::sandbox(false);
			}else{
				//echo 'Sandbox Active A6e';
				Twocheckout::sandbox(true); 
			}
			Twocheckout::username($Api_user);
			Twocheckout::password($Api_password);

			Twocheckout::privateKey($private_key);
			Twocheckout::sellerId($seller_id);
			//Twocheckout::format('json');
			
			$sale_id = get_post_meta($sub_id, '_wps-mnm-sub-check_sale_id', true); 
			//Twocheckout::sandbox(true);  #Uncomment to use Sandbox
			$args = array(
				'sale_id' => $sale_id
			);
			$response = Twocheckout_Sale::stop($args);
			//echo '<pre>'; print_r($response); echo '</pre>'; die();
			if($response['response_code'] == 'OK'){
				//echo 'Successfully Stop Recurring';
				return true;
			}
			else{
				//echo 'Dur!! erom ki6u nei';
				return false;
			}
			
			/*
			try {
			$response = Twocheckout_Sale::stop($args);
			$this->assertEquals("OK", $response['response_code']);
			} catch (Twocheckout_Error $e) {
			$this->assertEquals("No recurring lineitems to stop.", $e->getMessage());
			}*/
		}
		else if($payment_method == 'wps_mnm_stripe')
        {
			//include (WPS_MNM_INC . '/stripe/Stripe.php') ; //die();
			include (WPS_MNM_INC . '/stripe-new/init.php') ; 
			$woocommerce_stripe_settings =  get_option('woocommerce_wps_mnm_stripe_settings');		
			//echo '<pre>';print_r($woocommerce_stripe_settings); die();			
			$env 		= $woocommerce_stripe_settings['environment']; 
			$test_s_key =  $woocommerce_stripe_settings['stripe_test_secret_key'];		
			$live_s_key =  $woocommerce_stripe_settings['stripe_live_secret_key']; 
		
			if($env == 'yes') {
				$secret_key = $test_s_key;
			} else {
				$secret_key = $live_s_key;
			}
			$customer_id = get_post_meta($sub_id, '_wps-mnm-sub-stripe_cust_id', true);
			$subscription_id = get_post_meta($sub_id, '_wps-mnm-sub-stripe_sub_id', true);
			
			//Stripe::setApiKey($secret_key);
			\Stripe\Stripe::setApiKey($secret_key);
			$subscription = \Stripe\Subscription::retrieve($subscription_id);
			//echo '<pre>';print_r($subscription);die(); echo '</pre>';
			$result = $subscription->cancel();
			
			$json  = json_encode($result);		
			$configData = json_decode($json, true);
			//echo '<pre>';print_r($configData);die(); echo '</pre>';
			//echo '<pre>';print_r($result);die(); echo '</pre>';
			if ($configData['status'] == "canceled") 
			{
				return true;
			} 
			else 
			{
				return false;
			}
		}
		else 
		{
			//$woocommerce_paypal_settings =  get_option('woocommerce_wps_mnm_paypal_settings');
			//echo 'Paypal code Cancel Subscription goes here....';
		}
	}

    
}new WPS_MNM_Subscription_Tab;
