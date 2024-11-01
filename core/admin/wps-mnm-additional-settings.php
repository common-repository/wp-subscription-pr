<?php
class WPS_MNM_Additional_Settings
{
    public function __construct()
    {
        add_action('admin_footer', array($this,'wps_mnm_add_alert_guest_checkout_prevent'));
        add_action( 'add_meta_boxes', array($this,'wps_mnm_add_order_metabox' ));
    }

    public static function wps_mnm_add_alert_guest_checkout_prevent(){
        ?>
        <script type="text/javascript">
        jQuery(function($) {
            jQuery("#woocommerce_enable_guest_checkout").change(function()
            {
                var $this = $(this);
                if ($this.is(":checked"))
                {
                    if(confirm('Guset Checkout is not applicable for Subscription Product.') == true){
                    }
                    else{
                        $this.removeAttr("checked");
                    }
                }
            });
        });
        </script>

        <?php
    }

    
    public static function wps_mnm_add_order_metabox(){
        add_meta_box(
            'wps-mnm-order-subscription',
            'Subscription Link',
            array('WPS_MNM_Additional_Settings','wps_mnm_order_metabox_for_subscription'),
            'shop_order',
            'side',
            'high'
        );
    }

    public static function wps_mnm_order_metabox_for_subscription( $post ){
        $order_id = $post->ID;
        $subscription_id = get_post_meta( $order_id, '_wps-mnm-sub_id_for_order', true ); 
       // $subscription_id = WPS_MNM_Additional_Settings::get_post_id_by_meta_key_and_value('_wps-mnm-sub-order-id',$order_id);
        if($subscription_id !=''){
        ?>
        <a href="<?php echo get_edit_post_link($subscription_id); ?>" target="_blank">Click here to view Subscription Details</a>
        <?php
        }else{
            echo "Subscription Is Unavailable For This Order.";
        }
    }

    
    
    public static function send_subscription_status_email_to_customer($subscription_id,$sub_status){
		
		global $post;
		$subscription_id = $post->ID;
				
		$authorID = $post->post_author;
		
		$user_info = get_userdata($authorID);
		$user_email = $user_info->user_email;
				
		$sub_status = get_post_meta( $subscription_id, '_wps-mnm-sub-status', true ); 
				
		//$to = 'debnidhi@matrixnmedia.com';
		$to = $user_email;
		$subject = 'Status Has benn Changed';
		$message = 'Hello, Your Subscription Status has been changed to '.$sub_status.' ';

		wp_mail( $to, $subject, $message );
	}
}new WPS_MNM_Additional_Settings;?>
