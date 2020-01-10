<?php

define( 'DIVILIFE_EDD_DIVIOVERLAYS_URL', 'https://divilife.com' );
define( 'DIVILIFE_EDD_DIVIOVERLAYS_ID', 48763 );
define( 'DIVILIFE_EDD_DIVIOVERLAYS_NAME', 'Divi Overlays' );
define( 'DIVILIFE_EDD_DIVIOVERLAYS_AUTHOR', 'Tim Strifler' );
define( 'DIVILIFE_EDD_DIVIOVERLAYS_VERSION', DOV_VERSION );
define( 'DIVILIFE_EDD_DIVIOVERLAYS_PAGE_SETTINGS', 'dovs-settings' );

// the name of the settings page for the license input to be displayed
define( 'DIVILIFE_EDD_DIVIOVERLAYS_LICENSE_PAGE', 'divioverlays-license' );

function divilife_edd_divioverlays_updater() {
	
	// retrieve our license key from the DB
	$license_key = trim( get_option( 'divilife_edd_divioverlays_license_key' ) );
	
	// setup the updater
	$edd_updater = new edd_divioverlays( DIVILIFE_EDD_DIVIOVERLAYS_URL, DOV_PLUGIN_BASENAME, array(
			'version' 	=> DIVILIFE_EDD_DIVIOVERLAYS_VERSION,
			'license' 	=> $license_key,
			'item_name' => DIVILIFE_EDD_DIVIOVERLAYS_NAME,
			'author' 	=> DIVILIFE_EDD_DIVIOVERLAYS_AUTHOR,
			'beta'		=> false
		)
	);
}
add_action( 'admin_init', 'divilife_edd_divioverlays_updater', 0 );


add_action( 'admin_menu', array( 'DiviOverlays', 'add_admin_submenu' ), 5 );

class DiviOverlays {
	
	private static $_show_errors = FALSE;
	private static $initiated = FALSE;
	private static $helper_admin = NULL;
	
	public static $helper = NULL;
	
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	public static $options;
	
	
	public static function add_admin_submenu() {
		
		// Admin page
		add_submenu_page( 'edit.php?post_type=divi_overlay', 'Divi Overlays', 'Settings', 'edit_posts', 'dovs-settings', array( 'DiviOverlays', 'admin_settings' ) );
	}
	
	
	public static function admin_settings() {
		
		self::display_configuration_page();
	}
	
	public static function display_configuration_page() {
		
		DiviOverlays::$options = get_option( 'dov_settings' );
	?>
	<div class="wrap">
		<h1>Divi Overlays</h1>
		<form method="post" action="options.php">
		<?php
			// This prints out all hidden setting fields
			settings_fields( 'dovs_settings' );
			do_settings_sections( 'dovs-settings' );
			submit_button();
		?>
		</form>
	</div>
	<?php
	
	$license = get_option( 'divilife_edd_divioverlays_license_key' );
	$status  = get_option( 'divilife_edd_divioverlays_license_status' );
	$check_license = divilife_edd_divioverlays_check_license( TRUE );
	
	if ( $license != '' ) {
		
		$license = '*******';
	}
	
	if ( isset( $check_license->expires ) ) {
		
		$license_expires = strtotime( $check_license->expires );
		$now = strtotime('now');
		$timeleft = $license_expires - $now;
		
		$daysleft = round( ( ( $timeleft / 24 ) / 60 ) / 60 );
		if ( $daysleft > 0 ) {
			
			$daysleft = '( ' . ( ( $daysleft > 1 ) ? $daysleft . ' days left' : $daysleft . ' day left' ) . ' )';
			
		} else {
			
			$daysleft = '';
		}
	}
	?>
	<div class="wrap">
		<h2><?php _e('Divi Overlays - License'); ?></h2>
		<form method="post" action="options.php">

			<?php settings_fields('divilife_edd_divioverlays_license'); ?>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e('License Key'); ?>
						</th>
						<td>
							<input id="divilife_edd_divioverlays_license_key" name="divilife_edd_divioverlays_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
							<label class="description" for="divilife_edd_divioverlays_license_key"><?php _e('Enter your license key'); ?></label>
						</td>
					</tr>
					<?php if ( FALSE !== $license ) { ?>
					
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e('License Status'); ?>
							</th>
							<td>
								<?php if ( $status === false ) {
									wp_nonce_field( 'divilife_edd_divioverlays_nonce', 'divilife_edd_divioverlays_nonce' ); ?>
									<input type="submit" class="button-secondary" name="divilife_edd_divioverlays_license_activate" value="<?php _e('Activate License'); ?>"/>
								<?php } ?>
								
								<h3 class="dl-edm-heading-msg no-margin">
									<?php 
										if ( $status !== false && $check_license->license == 'valid' ) {
											
											print '<span class="dashicons dashicons-yes dashicons-success dashicons-large"></span><br><br>';
										}
										else {
											
											if ( $check_license->license == 'expired' ) {
											
												print '<span class="dashicons dashicons-no-alt dashicons-fail dashicons-large"></span>';
												print '&nbsp;&nbsp;<span class="small-text">( Expired on ' . date( 'F d, Y', strtotime( $check_license->expires ) ) . ' )</span>';
											}
											
											if ( $check_license->license == NULL && $status !== false ) {
												
												print '<span class="dashicons dashicons-no-alt dashicons-fail dashicons-large"></span>';
												print '&nbsp;&nbsp;<span class="small-text">( Cannot retrieve license status from Divi Life server. Please contact Divi Life support. )</span>';
											}
										}
									?>
								</h3>
								<br>
								
								<?php if ( $status !== false && $check_license->license == 'valid' ) { ?>
									<?php wp_nonce_field( 'divilife_edd_divioverlays_nonce', 'divilife_edd_divioverlays_nonce' ); ?>
									<input type="submit" class="button-secondary" name="divilife_edd_divioverlays_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
								<?php } ?>
							</td>
						</tr>
						
						<?php
						
						if ( $status !== false ) { 
							
							if ( $daysleft != '' && $check_license->license == 'valid' ) { ?>
							<tr valign="top">
								<th scope="row" valign="top">
									<?php _e('License Expires on'); ?>
								</th>
								<td>
									<h4 class="no-margin">
										<?php print date( 'F d, Y', strtotime( $check_license->expires ) ); ?>
										<?php print $daysleft; ?>
									</h4>
								</td>
							<?php
							}
						}
						?>
						
					<?php } ?>
				</tbody>
			</table>
			<?php submit_button(); ?>

		</form>
	<?php
	
	}
	
	// Divi Overlay settings
	public static function register_dovs_settings( $args ) {
				
		register_setting( 
			'dovs_settings', 
			'dov_settings', 
			array( 'DiviOverlays', 'sanitize' )
		);
		
		add_settings_section(
			'dov_settings_description',
			'Settings',
			array( 'DiviOverlays', 'doDescriptionSettings' ),
			'dovs-settings'
		);  
		
		$options = array( 
			'type' => 'select',
			'name' => 'dov_timezone',
			'default_value' => DOV_SERVER_TIMEZONE
		);
		
		add_settings_field(
			'dov_timezone', 
			'Default Time Zone', 
			array( 'DiviOverlays', 'doParseFields' ),
			'dovs-settings', 
			'dov_settings_description',
			$options
		);
	}
	
	public static function doDescriptionSettings() {
		print '';
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public static function sanitize( $input ) {
		
		$new_input = array();
		
		if ( isset( $input['dov_timezone'] ) ) {
			
			$new_input['dov_timezone'] = sanitize_text_field( $input['dov_timezone'] );
		}
		
		return $new_input;
	}

	public static function doParseFields( $options ) {
		
		$field_type = isset( $options['type'] ) ? $options['type'] : '';
		
		$field_name = $optionname = isset( $options['name'] ) ? $options['name'] : '';
		
		$field_default_value = isset( $options['default_value'] ) ? $options['default_value'] : '';
		
		if ( 'text' == $field_type ) {
			
			printf(
				'<input type="text" id="' . $field_name . '" name="dov_settings[' . $field_name . ']" value="%s" />',
				isset( self::$options[ $field_name ] ) ? esc_attr( self::$options[ $field_name ] ) : $field_default_value
			);
		}
		else if ( 'select' == $field_type ) {
			
			$valid_options = array();
			
			$selected = isset( self::$options[ $field_name ] ) ? esc_attr( self::$options[ $field_name ] ) : $field_default_value;
			
			if ( $selected != $field_default_value ) {
				
				$field_default_value = $selected;
			}
			
			?>
			<select name="dov_settings[<?php print $field_name; ?>]" data-defaultvalue="<?php print $field_default_value ?>" class="select-<?php print $options['name'] ?>">
			<?php
			
			if ( isset( $options['options'] ) ) {
			
				foreach ( $options['options'] as $option ) {
					
					?>
					<option <?php selected( $selected, $option['value'] ); ?> value="<?php print $option['value']; ?>"><?php print $option['title']; ?></option>
					<?php
				}
			}
			
			?>
			</select>
			<?php
		}
	}
}
add_action( 'admin_init', array( 'DiviOverlays', 'register_dovs_settings' ) );


function divilife_edd_divioverlays_register_option() {
	
	// creates our settings in the options table
	register_setting('divilife_edd_divioverlays_license', 'divilife_edd_divioverlays_license_key', 'divilife_edd_divioverlays_sanitize_license' );
}
add_action('admin_init', 'divilife_edd_divioverlays_register_option');


function divilife_edd_divioverlays_sanitize_license( $new ) {
	
	$old = get_option( 'divilife_edd_divioverlays_license_key' );
	if( $old && $old != $new ) {
		delete_option( 'divilife_edd_divioverlays_license_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}


function divilife_edd_divioverlays_activate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['divilife_edd_divioverlays_license_activate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'divilife_edd_divioverlays_nonce', 'divilife_edd_divioverlays_nonce' ) )
			return; // get out if we didn't click the Activate button
		
		// retrieve the license from the database
		$license = trim( get_option( 'divilife_edd_divioverlays_license_key' ) );
		
		
		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( DIVILIFE_EDD_DIVIOVERLAYS_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( DIVILIFE_EDD_DIVIOVERLAYS_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				
				$message = $response->get_error_message();
				
			} else {
				
				$message = __( 'An error occurred, please try again.' );
			}

		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				switch( $license_data->error ) {

					case 'expired' :

						$message = sprintf(
							__( 'Your license key expired on %s.' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;

					case 'revoked' :

						$message = __( 'Your license key has been disabled.' );
						break;

					case 'missing' :

						$message = __( 'Invalid license.' );
						break;

					case 'invalid' :
					case 'site_inactive' :

						$message = __( 'Your license is not active for this URL.' );
						break;

					case 'item_name_mismatch' :

						$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), DIVILIFE_EDD_DIVIOVERLAYS_NAME );
						break;

					case 'no_activations_left':

						$message = __( 'Your license key has reached its activation limit.' );
						break;

					default :

						$message = __( 'An error occurred, please try again.' );
						break;
				}

			}

		}

		// Check if anything passed on a message constituting a failure
		if ( ! empty( $message ) ) {
			$base_url = admin_url( 'edit.php?post_type=divi_overlay&page=dovs-settings' );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );
			
			wp_redirect( $redirect );
			exit();
		}

		update_option( 'divilife_edd_divioverlays_license_status', $license_data->license );
		wp_redirect( admin_url( 'edit.php?post_type=divi_overlay&page=dovs-settings' ) );
		exit();
	}
}
add_action('admin_init', 'divilife_edd_divioverlays_activate_license');


function divilife_edd_divioverlays_deactivate_license() {

	// listen for our activate button to be clicked
	if ( isset( $_POST['divilife_edd_divioverlays_license_deactivate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'divilife_edd_divioverlays_nonce', 'divilife_edd_divioverlays_nonce' ) )
			return; // get out if we didn't click the Activate button
		
		// retrieve the license from the database
		$license = trim( get_option( 'divilife_edd_divioverlays_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( DIVILIFE_EDD_DIVIOVERLAYS_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( DIVILIFE_EDD_DIVIOVERLAYS_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
		
		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

			$base_url = admin_url( 'edit.php?post_type=divi_overlay&page=dovs-settings' );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' ) {
			delete_option( 'divilife_edd_divioverlays_license_status' );
		}

		wp_redirect( admin_url( 'edit.php?post_type=divi_overlay&page=dovs-settings' ) );
		exit();

	}
}
add_action('admin_init', 'divilife_edd_divioverlays_deactivate_license');


/**
 * This is a means of catching errors from the activation method above and displaying it to the customer
 */
function divilife_edd_divioverlays_admin_notices() {
	if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

		switch( $_GET['sl_activation'] ) {

			case 'false':
				$message = urldecode( $_GET['message'] );
				?>
				<div class="error">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;

			case 'true':
			default:
				// Developers can put a custom success message here for when activation is successful if they way.
				break;

		}
	}
}
add_action( 'admin_notices', 'divilife_edd_divioverlays_admin_notices' );


function divilife_edd_divioverlays_check_license( $msg = FALSE ) {

	global $wp_version;

	$license = trim( get_option( 'divilife_edd_divioverlays_license_key' ) );

	$api_params = array(
		'edd_action' => 'check_license',
		'license' => $license,
		'item_name' => urlencode( DIVILIFE_EDD_DIVIOVERLAYS_NAME ),
		'url'       => home_url()
	);

	// Call the custom API.
	$response = wp_remote_post( DIVILIFE_EDD_DIVIOVERLAYS_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	if ( is_wp_error( $response ) )
		return false;

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	
	if ( $license_data->license == 'valid' ) {
		
		if ( $msg ) {
			
			return $license_data;
			
		} else {
		
			return TRUE;
		}
		
	} else {
		
		if ( $msg ) {
			
			return $license_data;
			
		} else {
		
			return FALSE;
		}
	}
}


/**
 * Displays an inactive notice when the plugin is inactive.
 */
function divilife_edd_divioverlays_inactive_notice() {
	
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	if ( isset( $_GET[ 'page' ] ) && DIVILIFE_EDD_DIVIOVERLAYS_PAGE_SETTINGS == $_GET[ 'page' ] ) {
		return;
	}
	
	$status = get_option( 'divilife_edd_divioverlays_license_status' );
	if ( $status === false ) {
	
		?>
		<div class="notice notice-error">
			<p>
			<?php 
			
			printf(
				__( 'The <strong>%s</strong> API Key has not been activated, so the plugin is inactive! %sClick here%s to activate <strong>%s</strong>.', DIVILIFE_EDD_DIVIOVERLAYS_NAME ), 
				esc_attr( DIVILIFE_EDD_DIVIOVERLAYS_NAME ), 
				'<a href="' . esc_url( admin_url( 'edit.php?post_type=divi_overlay&page=dovs-settings' ) ) . '">', 
				'</a>', esc_attr( DIVILIFE_EDD_DIVIOVERLAYS_NAME )
			);
			
			?>
			</p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'divilife_edd_divioverlays_inactive_notice', 0 );


