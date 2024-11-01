<?php
class WPS_MNM_Register
{
	
	public function __construct()
	{
		add_action( 'init', array($this,'register_subscription_type' ));
		add_filter( 'post_updated_messages', array($this,'subscription_updated_messages' ));
	}
	public static function register_subscription_type() {
		$labels = array(
			'name'               => _x( 'Subscription', 'subscription', 'wpspfLan' ),
			'singular_name'      => _x( 'Subscription', 'subscription', 'wpspfLan' ),
			'menu_name'          => _x( 'Subscription', 'admin menu', 'wpspfLan' ),
			'name_admin_bar'     => _x( 'Subscription', 'add new on admin bar', 'wpspfLan' ),
			'add_new'            => _x( 'Add New Subscription', 'Subscription', 'wpspfLan' ),
			'add_new_item'       => __( 'Add New Subscription', 'wpspfLan' ),
			'new_item'           => __( 'New Subscription', 'wpspfLan' ),
			'edit_item'          => __( 'Edit Subscription', 'wpspfLan' ),
			'view_item'          => __( 'View Subscription', 'wpspfLan' ),
			'all_items'          => __( 'All Subscription', 'wpspfLan' ),
			'search_items'       => __( 'Search Subscription', 'wpspfLan' ),
			'parent_item_colon'  => __( 'Parent Subscription:', 'wpspfLan' ),
			'not_found'          => __( 'No Subscription found.', 'wpspfLan' ),
			'not_found_in_trash' => __( 'No Subscription found in Trash.', 'wpspfLan' )
		);

		$args = array(
			'labels'             => $labels,
	        'description'        => __( 'Description.', 'wpspfLan' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-cart',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'wps-mnm-subscription' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'supports'           => array( 'title')
		);

		register_post_type( 'wps-mnm-subscription', $args );
	}
	public static function subscription_updated_messages( $messages ) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages['wps-mnm-subscription'] = array(
			0  => '',
			1  => __( 'Subscription updated.', 'wpspfLan' ),
			2  => __( 'Custom field updated.', 'wpspfLan' ),
			3  => __( 'Custom field deleted.', 'wpspfLan' ),
			4  => __( 'Subscription updated.', 'wpspfLan' ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Subscription restored to revision from %s', 'wpspfLan' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Subscription published.', 'wpspfLan' ),
			7  => __( 'Subscription saved.', 'wpspfLan' ),
			8  => __( 'Subscription submitted.', 'wpspfLan' ),
			9  => sprintf(
				__( 'Subscription scheduled for: <strong>%1$s</strong>.', 'wpspfLan' ),
				date_i18n( __( 'M j, Y @ G:i', 'wpspfLan' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Subscription draft updated.', 'wpspfLan' )
		);

		if ( $post_type_object->publicly_queryable && 'wps-mnm-subscription' === $post_type ) {
			$permalink = get_permalink( $post->ID );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View subscription', 'wpspfLan' ) );
			$messages[ $post_type ][1] .= $view_link;
			$messages[ $post_type ][6] .= $view_link;
			$messages[ $post_type ][9] .= $view_link;

			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview subscription', 'wpspfLan' ) );
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}
}new WPS_MNM_Register;
