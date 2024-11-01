<?php

function wps_mnm_subscription_payment_menu() {

  /*add_submenu_page (
    'edit.php?post_type=wps-mnm-subscription',
    'Payment Setting',
    'Payment Setting',
    'manage_options',
    'payment-setting',
    'wps_mnm_subscription_payment_setting'
    );*/
  //add_action( 'admin_init', 'register_wps_mnm_subscription_payment_settings' );
  }
add_action( 'admin_menu', 'wps_mnm_subscription_payment_menu' );

function register_wps_mnm_subscription_payment_settings() {


	register_setting( 'wps-mnm-subscription-payment-settings-group-auth', 'auth_api_username' );
	register_setting( 'wps-mnm-subscription-payment-settings-group-auth', 'auth_api_signature' );
	register_setting( 'wps-mnm-subscription-payment-settings-group-auth', 'auth_mode' );
	register_setting( 'wps-mnm-subscription-payment-settings-group-auth', 'auth_status' );

}



function wps_mnm_subscription_payment_setting()
{
	?>
	<div class="wrap" style="float:left;">
		<h2>Authorize.net Settings</h2>
		<form method="post" action="options.php">
		    <?php settings_fields( 'wps-mnm-subscription-payment-settings-group-auth' ); ?>
		    <?php do_settings_sections( 'wps-mnm-subscription-payment-settings-group-auth' ); ?>
		    <table class="form-table">
		        <tr valign="top">
			        <th scope="row">API Username/Login ID</th>
			        <td><input class="regular-text code" type="text" name="auth_api_username" value="<?php echo esc_attr( get_option('auth_api_username') ); ?>" /></td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">API Signature</th>
		        	<td><input class="regular-text code" type="text" name="auth_api_signature" value="<?php echo esc_attr( get_option('auth_api_signature') ); ?>" /></td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Mode</th>
		        		<td>
		        		<select name="auth_mode">
		        			<option value="sandbox" <?php if(esc_attr( get_option('auth_mode') )=='sandbox'){echo 'selected';}?>>Sandbox(Testing)</option>
		        			<option value="live" <?php if(esc_attr( get_option('auth_mode') )=='live'){echo 'selected';}?>>Live</option>
		        		</select>
		        		</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Status</th>
		        		<td>
		        		<select name="auth_status">
		        			<option value="active" <?php if(esc_attr( get_option('auth_status') )=='active'){echo 'selected';}?>>Active</option>
		        			<option value="inactive" <?php if(esc_attr( get_option('auth_status') )=='inactive'){echo 'selected';}?>>Inactive</option>
		        		</select>
		        		</td>
		        </tr>
		    </table>
		    <?php submit_button(); ?>
		</form>
	</div>
	<?php 
}
