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
		add_action( 'init', array($this,'wps_mnm_re_subscription' ));
		add_action( 'init', array($this,'wps_mnm_payment_success_or_failure' ));
		//add_action( 'init', array($this,'wps_mnm_subscription_order_pay'));
		
		add_action( 'init', array($this,'wps_mnm_payment_submitForm'));
		//add_action( 'init', array($this,'wps_mnm_subscription_payment_Done'));
		//add_action( 'init', array($this,'wps_mnm_subscription_paymentPaypal'));
		
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
		echo $_GET['wp_payment_msg'];
		unset($_GET['wp_payment_msg']);
		$args = array(
	    	'author' => get_current_user_id(),
	    	'post_type' => 'wps-mnm-subscription',
	    	'post_per_page' => -1
		);

	    $users_subscription = new WP_Query( $args );
		if( $users_subscription->have_posts() ) {
			?>
			
			<table>
				<tr>
					<th>Subscription Id</th>
					<!--<th>Order Id</th>-->
					<th>Subscription Date</th>
					<th>Subscription Status</th>
					<!--<th>Order Total</th>-->
					<th>Action</th>
				</tr>
				
			<?php
			$counter = 1;
		    while( $users_subscription->have_posts()) {
		        $users_subscription->the_post();
		        $order_id = get_post_meta(get_the_ID(),'_wps-mnm-sub-order-id',true);
		        //print_r($order_id);
		        
		        $tot_order_id = count($order_id);
				/*for ($i = 0; $i < $tot_order_id; $i++) {
				  echo $order_id[$i];
				}*/
					        
		        //$order_total = get_post_meta( $order_id, '_order_total', true);
		        $trans_id = get_post_meta( $order_id, '_transaction_id', true);
		        $order_currency = get_post_meta( $order_id, '_order_currency', true);
		        $order_tax = get_post_meta( $order_id, '_order_tax', true);
		        
		        global $post;
				$subscription_id = $post->ID;
					
				$authorID = $post->post_author;				
				$user_info = get_userdata($authorID);
				$user_email = $user_info->user_email;
		        ?>
		        <tr>
		        	<td>#<?php echo get_the_ID(); ?></td>
		        	<!--<td><a href="<?php //echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>/view-order/<?php echo $order_id; ?>" target="_blank">
		        		<?php //echo $order_id; ?>
		        	</td>-->
		        	<td><?php echo get_the_date('Y/m/d',get_the_ID()); ?></td>
		        	<!--<td><?php //echo get_post_status(get_the_ID()); ?></td>-->
		        	<td><?php echo get_post_meta( $subscription_id, '_wps-mnm-sub-status', true ); ?></td>
		        	<!--<td><?php //echo $order_total; ?></td>-->
		        	<td>
						<form method="POST" action="" name="sub_action_form_front" id="sub_action_form_front">
						<input type="hidden" name="sub_id" value="<?php echo $subscription_id; ?>" />
						<input type="hidden" name="wps_mnm_hidden_action" value="status_change_frontend" />
						<input type="hidden" name="sub_action" value="Cancelled" />
						
						<input type="submit" name ="substatus" value="Cancel" />
						</form>				
					</td>
					
					<!--<td>
						<form method="POST" action="" name="re_sub_action_form_front" id="re_sub_action_form_front">
						<input type="hidden" name="re_sub_id" value="<?php //echo $subscription_id; ?>" />
						<input type="hidden" name="wps_mnm_hidden_re_subscription" value="re_subscription_frontend" />
						
						<input type="submit" name ="resubscription" value="Re Subscription" />
						</form>	
					</td>-->
		        </tr>
		        
		        <tr>
					<th></th>
					<th>Order Id</th>
					<th>Order Total</th>
					<th>Action</th>
				</tr>
				<tr>
					<table>
					<?php for ($i = 0; $i < $tot_order_id; $i++) { ?>
					<tr>	
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td>				
							<td></td>
							<td><a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>/view-order/<?php echo $order_id[$i]; ?>" target="_blank">
									<?php echo $order_id[$i]; ?>
							</td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td><?php echo get_post_meta( $order_id[$i], '_order_total', true); ?></td>
							<td></td>
							<td></td>
							<td></td>
							<td>
							<input type="button" name ="subpay" id="yesCheck_<?php echo $i;?>" onclick="open_pay_form('<?php echo $i;?>')" value = "Pay Now"  />
							</td>
						</td>
					</tr>
					<?php }?>
					</table>
				</tr>
				<tr>
					<td>					
					<div class="form-style-2" id="pay_options_<?php echo $counter;?>" style="display:none;">	
							<form name="wps-mnm-sub-paymentSubmit" action="" method="POST" id="wps-mnm-sub-paymentSubmit-<?php echo $counter;?>">
								<div class="payment">
									
									<div class="form-style-2-heading">Payment Details</div>
									<label for="choosePayment"><span>Payment Through</span>
										<select name="choosePayment" id="choosePayment" onChange="enable();" class="select-field">
											<!--<option value="authorize">Credit Card</option>-->
											<option value="paypal">Paypal</option>
										</select>
									</label>
									
									<label>
										<span>Amount</span>
										<!--<input type="text" name="amount" class="input-field" id="amount" />-->
										<input type="text" disabled name="amount" class="input-field" id="amount" value="<?php echo $order_total;?>" />
										<div id="elmAmountError" class="errorMsg"></div>
									</label>

									<div id="paypal">
										<label><span>Card Type</span>
											<select name="_paypal_card_type" class="select-field">
												<option value="MasterCard">MasterCard</option>
												<option value="Visa">Visa</option>
												<option value="American Express">American Express</option>
												<option value="Discover">Discover</option>
											</select>
										</label>
										<label>
											<span>Card No.</span>
											<input type="text" id="cardNo1" rel="19" name="_paypal_card_no" class="input-field" />
											<div id="elmCardNo1Error" class="errorMsg"></div>
										</label>
										<label>
											<span>Card Exp. Date</span>
											<select name="_paypal_card_exp_month" class="select-field">
												<?php
													for ($i=1; $i <=12 ; $i++) { 
															echo '<option value="'.$i.'">'.$i.'</option>';
														}
												?>
											</select>
											<select name="_paypal_card_exp_year" class="select-field">
												<?php
													for ($i=date('Y'); $i <= date('Y')+100 ; $i++) { 
															echo '<option value="'.$i.'">'.$i.'</option>';
														}
												?>
											</select>
										</label>
										<label>
											<span>CVV2</span>
											<input type="text" rel="19" name="_paypal_card_cvv2" id="_paypal_card_cvv2" class="input-field" />
											<div id="elmcvv2Error" class="errorMsg"></div>
										</label>
									</div>
								</div>
								<input type="submit" name="submit" value="Pay" id="btnSubmit"  />
								<input type="hidden" name="wps_mnm_hidden_action" value="wps_mnn_manual_pay" />
								<input type="reset" name="reset" value="Reset" id="btnReset" onclick="clearForm()" />
								<input type="hidden" name="redirectUrl" value="<?php echo get_permalink();?>" />
							</form>
					</div>
					</td>
				</tr>
		        <?php
				$counter++;		
		    }
		    ?>
		    </table>
		    <script>
		    
		    </script>
		    <?php

		    wp_reset_postdata();
		}
	    
	}
	
	public static function wps_mnm_subscription_status_change() {
		if($_POST['wps_mnm_hidden_action'] == 'status_change_frontend'){
			update_post_meta($_POST['sub_id'],'_wps-mnm-sub-status',$_POST['sub_action']);
		}
    }
    
    public static function wps_mnm_payment_submitForm(){
		if($_POST['wps_mnm_hidden_action'] == 'wps_mnn_manual_pay')
		{
		   WPS_MNM_Subscription_Tab::wps_mnm_subscription_payment_Done();
		} 
	}
	
	public static function wps_mnm_subscription_payment_Done(){		
		if($_POST['choosePayment'] == 'paypal')
		{
			WPS_MNM_Subscription_Tab::wps_mnm_subscription_paymentPaypal();
		}		
	}
	
    public static function wps_mnm_subscription_paymentPaypal() {
		if(esc_attr( get_option('paypal_mode') ) == 'sandbox')
		{
			$sandbox 	= TRUE;
		}
		if(esc_attr( get_option('paypal_mode') ) == 'live')
		{
			$sandbox 	= FALSE;
		}
		
		
		$api_version 	= '85.0';
		$api_endpoint 	= $sandbox ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
		$api_username 	= $sandbox ? esc_attr( get_option('paypal_api_username') ) : 'LIVE_USERNAME_GOES_HERE';
		$api_password 	= $sandbox ? esc_attr( get_option('paypal_api_password') ) : 'LIVE_PASSWORD_GOES_HERE';
		$api_signature 	= $sandbox ? esc_attr( get_option('paypal_api_signature') ) : 'LIVE_SIGNATURE_GOES_HERE';

		$card_num 		= str_replace(' ', '', $_POST['_paypal_card_no']);
		$card_exp_date 	= $_POST['_paypal_card_exp_month'].$_POST['_paypal_card_exp_year'];
		$card_cvv2 		= $_POST['_paypal_card_cvv2'];
		$card_type 		= $_POST['_paypal_card_type'];
		
		$request_params = array
				(
				'METHOD' => 'DoDirectPayment', 
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
				'AMT' => $_POST['amount'], 
				'CURRENCYCODE' => 'USD', 
				'DESC' => 'Payment'
				);
		$nvp_string = '';
		foreach($request_params as $var=>$val)
		{
			$nvp_string .= '&'.$var.'='.urlencode($val);    
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
		$nvp_response_array = parse_str($result);
		$response_arr = explode("&",$result);
		$response_arr2 = explode("=",$response_arr[9]);
		$transaction_id = $response_arr2[1];
		$response_arr3 = explode("=",$response_arr[2]);
		//echo '<pre>'; print_r($_GET); echo '</pre>';
		$ack = $response_arr3[1];
		$tran_arr = explode("=",$response_arr[9]);
		//echo '<pre>'; print_r($nvp_response_array); echo '</pre>';
		
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

   
    public static function wps_mnm_re_subscription() {
		if($_POST['wps_mnm_hidden_re_subscription'] == 're_subscription_frontend'){
			echo $_POST['wps_mnm_hidden_re_subscription'];
			echo 'hi..................'; exit;
		}
    }
    
    public static function wps_mnm_payment_success_or_failure() {
			//echo $_GET['transation'];
			//echo 'Hello!';
    }
}new WPS_MNM_Subscription_Tab;
 

