<?php
class WPS_MNM_Process_Order
{
    
    public function __construct()
    {
        add_action( 'woocommerce_thankyou', array($this,'wps_mnm_save_subscription_info'));
        add_filter('manage_edit-wps-mnm-subscription_columns', array($this,'wps_mnm_subscription_extra_columns'));
        add_action('manage_posts_custom_column', array($this,'wps_mnm_subscription_extra_columns_content'));
    }
    public static function wps_mnm_save_subscription_info( $order_id ) { 
        if ( ! $order_id ) 
           return;
        $order = wc_get_order( $order_id ); 
        $items = $order->get_items();
        $b = array();
        foreach ( $items as $item_id => $item_data ) 
        {
            $var = $item_data['product_id'];
            $b[] = $var;      
        }
        $sub_product_ids = implode(',',$b);
        
        /* Get user information Start */
			
		$current_user = wp_get_current_user();

        /* Get user information End */
        
        $subscription_information = array(
          'post_name'      =>  'wpscribe-mnm new product subscription order by '.$current_user->user_firstname.' '.$current_user->user_lastname.', '.$current_user->user_email, 
          'post_title'     =>  'WPScribe Subscription by '.$current_user->user_firstname.' '.$current_user->user_lastname.', '.$current_user->user_email, 
          'post_type'      =>  'wps-mnm-subscription' ,
          'post_status'    =>  'publish'
        );
        $post_id = wp_insert_post($subscription_information);
        if ($post_id) 
        {
            WPS_MNM_Process_Order::wps_mnm_save_subscription_metas($post_id,$order_id,$sub_product_ids);
        }
            
    }
    public static function wps_mnm_save_subscription_metas($post_id,$order_id,$sub_product_ids){
		//$orderIds = array();
		//$orderIds[] = $order_id; 
		
		// If payment through Authorize.net, get this Subscription ID Start.
		$payment_method = get_post_meta($order_id, '_payment_method', true );
		if($payment_method == 'wps_mnm_auth_net')
		{
			$auth_sub_id = get_post_meta($order_id,'_wps-mnm-sub-created_id_auth_dashboard', true);
			update_post_meta( $post_id, '_wps-mnm-sub-auth_sub_id', $auth_sub_id );
		}
		// If payment through Authorize.net, get this Subscription ID End.
		
		// If payment through Stripe,  Start.
		if($payment_method == 'wps_mnm_stripe')
		{
			$stripe_sub_id = get_post_meta($order_id,'_wps-mnm-sub-Subscription_Id_related_to_dashboard', true);
			update_post_meta( $post_id, '_wps-mnm-sub-stripe_sub_id', $stripe_sub_id );
			
			$stripe_cust_id = get_post_meta($order_id,'_wps-mnm-sub-Customer_Id_related_to_dashboard', true);
			update_post_meta( $post_id, '_wps-mnm-sub-stripe_cust_id', $stripe_cust_id );
		}
		// If payment through Stripe,  End.
		
		// If payment through 2Checkout, Start
		if($payment_method == 'twocheckout')
		{
			$Check_sale_id = get_post_meta($order_id,'_wps-mnm-sub-sale_id_dashboard', true);
			update_post_meta( $post_id, '_wps-mnm-sub-check_sale_id', $Check_sale_id );
			
			$check_transac_id = get_post_meta($order_id,'_wps-mnm-sub-transac_id_dashboard', true);
			update_post_meta( $post_id, '_wps-mnm-sub-check_transac_id', $check_transac_id );
		}
		// If payment through 2Checkout, End.
		
        update_post_meta( $post_id, '_wps-mnm-sub-order-id', $order_id );
        update_post_meta( $post_id, '_wps-mnm-sub-user-id', get_current_user_id() );
        update_post_meta( $post_id, '_wps-mnm-sub-product-ids', $sub_product_ids );
        update_post_meta( $post_id, '_wps-mnm-sub-status', 'Processing' );
        
        update_post_meta( $order_id, '_wps-mnm-sub_id_for_order', $post_id );
    }
    public static function wps_mnm_subscription_extra_columns($columns)
    {
        unset($columns['date']);
        $newcolumns = array(
            "cb"        => "<input type  = \"checkbox\" />",
            "sub_ID"    => esc_html__('ID', 'woocommerce'),
            "title"     => esc_html__('Title', 'woocommerce'),
            "status"    => esc_html__('Status', 'woocommerce'),
            "wps_mnm_date"      => esc_html__('Date','woocommerce')
        );
     
        $columns = array_merge($newcolumns, $columns);
        
        return $columns;
    }
    public static function wps_mnm_subscription_extra_columns_content($column)
    {
        global $post;
        
        $subscription_id = $post->ID;
     
        switch ($column)
        {
            case "sub_ID":
                echo $subscription_id;
                break;  
            case "status":
                echo get_post_meta( $subscription_id, '_wps-mnm-sub-status', true );
                break;
            case "wps_mnm_date":
                echo 'Created on <b>'.get_the_date().'</b> at <b>'.get_the_time('', $post->ID).'</b>';
                break;
            
        }
    }

}new WPS_MNM_Process_Order;
