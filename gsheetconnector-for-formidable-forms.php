<?php

/**
 * Plugin Name:  GSheetConnector for Formidable Forms
 * Description:  Send your Formidable Forms data to your Google Sheets spreadsheet.
 * Author:       GSheetConnector
 * Author URI:   https://www.gsheetconnector.com/
 * Version:      1.0.0
 * Text Domain:  gsheetconnector-for-formidable-forms
 * Domain Path: /languages
 * License:      GPLv3
 * License URI:  https://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if accessed directly.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FRMDFORM_GOOGLESHEET_VERSION_VERSION', '1.0.0' );
define( 'FRMDFORM_GOOGLESHEET_VERSION_DB_VERSION', '1.0.0' );
define( 'FRMDFORM_GOOGLESHEET_VERSION_ROOT', __DIR__ );
define( 'FRMDFORM_GOOGLESHEET_VERSION_URL', plugins_url( '/', __FILE__ ) );
define( 'FRMDFORM_GOOGLESHEET_VERSION_BASE_FILE', basename( __DIR__ ) . '/gsheetconnector-for-formidable-forms.php' );
define( 'FRMDFORM_GOOGLESHEET_VERSION_BASE_NAME', plugin_basename( __FILE__ ) );
define( 'FRMDFORM_GOOGLESHEET_VERSION_PATH', plugin_dir_path( __FILE__ ) ); // use for include files to other files.
define( 'FRMDFORM_GOOGLESHEET_VERSION_PRODUCT_NAME', 'GSheetConnector for Formidable Forms' );
define( 'FRMDFORM_GOOGLESHEET_VERSION_CURRENT_THEME', get_stylesheet_directory() );
load_plugin_textdomain( 'gsheetconnector-for-formidable-forms', false, basename( __DIR__ ) . '/languages' );


/*
 * include utility classes
 */
if ( ! class_exists( 'Frm_Gsc_Connector_Utility' ) ) {
	include FRMDFORM_GOOGLESHEET_VERSION_ROOT . '/includes/class-frm-gsc-connector-utility.php';
	$frm_google_sheet_connector = new Frm_Gsc_Connector_Utility();
}

/*
 * Add Sub Menu
 */
if ( isset( $frm_google_sheet_connector ) ) {
	add_action( 'admin_menu', array( &$frm_google_sheet_connector, 'frm_admin_page' ), 20 );
}

/*
 * Setting Page
 */
add_action( 'formidable_forms_loaded', 'frmgsc_googlesheet_integration' );

/**
 * Include Integration Page
 */
function frmgsc_googlesheet_integration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-formidable-forms-integration.php';
}

// Include Library Files.
require_once FRMDFORM_GOOGLESHEET_VERSION_ROOT . '/lib/vendor/autoload.php';
require_once FRMDFORM_GOOGLESHEET_VERSION_ROOT . '/lib/google-sheets.php';

/**
 * Main class GSC FORMS
 *
 * @since 1.0
 */
class Gsheetconnector_For_Formidable_Forms {

	public function __construct() {

		// save client id and secret id manual api.
		add_action( 'wp_ajax_save_client_id_sec_id_gapi_frmgsc', array( $this, 'save_client_id_sec_id_gapi_frmgsc' ) );

		// deactivate auth token manual api.
		add_action( 'wp_ajax_deactivate_auth_token_gapi_frmgsc', array( $this, 'deactivate_auth_token_gapi_manual_frmgsc' ) );

		// run on activation of plugin.
		register_activation_hook( __FILE__, array( $this, 'frmdforms_gs_connector_activate' ) );

		// run on deactivation of plugin.
		register_deactivation_hook( __FILE__, array( $this, 'frmdforms_gs_connector_deactivate' ) );

		// run on uninstall.
		register_uninstall_hook( __FILE__, array( 'Gsheetconnector_For_Formidable_Forms', 'frmdforms_gs_connector_uninstall' ) );

		// validate is Formidable forms plugin exist.
		add_action( 'admin_init', array( $this, 'validate_parent_plugin_exists' ) );

		// Display widget to dashboard.
		add_action( 'wp_dashboard_setup', array( $this, 'add_frmdform_gs_connector_summary_widget' ) );

		// clear debug logs method using ajax for system status tab.
		add_action( 'wp_ajax_frm_clear_debug_logs', array( $this, 'frm_clear_debug_logs' ) );

		// load the js and css files.
		add_action( 'init', array( $this, 'load_css_and_js_files' ) );

		// Add custom link for our plugin.
		add_filter( 'plugin_action_links_' . FRMDFORM_GOOGLESHEET_VERSION_BASE_NAME, array( $this, 'frmdforms_gs_connector_plugin_action_links' ) );

		// For register action in FF form.
		add_filter( 'frm_registered_form_actions', array( $this, 'frmgsc_register_actions' ) );
	}

	/**
	 * AJAX function - Save Client Id and Secret Id Manual Api
	 *
	 * @since 1.0
	 */
	public function save_client_id_sec_id_gapi_frmgsc() {

		// nonce checksave_gs_frmgsc_settings.
		check_ajax_referer( 'gs-ajax-nonce', 'security' );
		/* sanitize incoming data */
		$client_id = sanitize_text_field( $_POST['client_id'] );
		$secret_id = sanitize_text_field( $_POST['secret_id'] );

		// save google setting with manual client id and secret id

		if ( ( ! empty( $client_id ) ) && ( ! empty( $secret_id ) ) ) {
			update_option( 'frmgsc_client_id', $client_id );
			update_option( 'frmgsc_secret_id', $secret_id );
			$Code = '';
			if ( isset( $_POST['gs_frmgsc_client_token'] ) ) {
				$Code = sanitize_text_field( $_POST['gs_frmgsc_client_token'] );
			}

			if ( ! empty( $Code ) ) {
				update_option( 'frmgsc_access_manual_code', $Code );
			} else {
				wp_send_json_success();
				return;
			}

			if ( get_option( 'frmgsc_access_manual_code' ) != '' ) {
				include_once FRMDFORM_GOOGLESHEET_VERSION_ROOT . '/lib/google-sheets.php';
				frmgsc_googlesheet::preauth_manual( get_option( 'frmgsc_access_manual_code' ), get_option( 'frmgsc_client_id' ), get_option( 'frmgsc_secret_id' ), esc_html( admin_url( 'admin.php?page=formidable-form-google-sheet-config' ) ) );

				wp_send_json_success();
			} else {
				update_option( 'frmgsc_gs_verify', 'invalid' );
				wp_send_json_error();
			}
		} else {
			update_option( 'frmgsc_client_id', '' );
			update_option( 'frmgsc_secret_id', '' );
			wp_send_json_success();

			return;
		}
	}

	/**
	 * AJAX function - deactivate activation - Manual
	 *
	 * @since 1.2
	 */
	public function deactivate_auth_token_gapi_manual_frmgsc() {
		// nonce check
		check_ajax_referer( 'gs-ajax-nonce', 'security' );
		if ( get_option( 'frmgsc_gs_token_manual' ) !== '' ) {

			$accesstoken = get_option( 'frmgsc_gs_token_manual' );
			$client      = new frmgsc_googlesheet();
			$client->revokeToken_auto_manual( $accesstoken );

			delete_option( 'frmgsc_gs_token_manual' );
			delete_option( 'frmgsc_gs_verify' );
			delete_option( 'frmgsc_access_manual_code' );

			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 *
	 *
	 */

	/**
	 * Do things on plugin activation
	 *
	 * @since 1.0
	 */
	public function frmdforms_gs_connector_activate( $network_wide ) {
		try {
			global $wpdb;
			$this->run_on_activation();
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				// check if it is a network activation - if so, run the activation function for each blog id.
				if ( $network_wide ) {
					// Get all blog ids.
					$blogids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );
					foreach ( $blogids as $blog_id ) {
						switch_to_blog( $blog_id );
						$this->run_for_site();
						restore_current_blog();
					}
					return;
				}
			}
			// for non-network sites only.
			$this->run_for_site();
		} catch ( Exception $e ) {
			Frm_Gsc_Connector_Utility::gs_debug_log( 'Something Wrong : - ' . $e->getMessage() );
		}
	}

	/**
	 * AJAX function - clear log file for system status tab
	 *
	 * @since 2.1
	 */
	public function frm_clear_debug_logs() {
		// nonce check.
		check_ajax_referer( 'gs-ajax-nonce', 'security' );
		$handle = fopen( WP_CONTENT_DIR . '/debug.log', 'w' );
		fclose( $handle );
		wp_send_json_success();
	}



	/**
	 * Deactivate the plugin
	 *
	 * @since 1.0
	 */
	public function frmdforms_gs_connector_deactivate() {
	}

	/**
	 *  Runs on plugin uninstall.
	 *  a static class method or function can be used in an uninstall hook
	 *
	 *  @since 1.0
	 */
	public static function frmdforms_gs_connector_uninstall() {
		// Not like register_uninstall_hook(), you do NOT have to use a static function.
		gs_FFfree()->add_action( 'after_uninstall', 'gs_frmdfree_uninstall_cleanup' );
	}

	/**
	 * Validate parent Plugin FF Form exist and activated
	 *
	 * @access public
	 * @since 1.0
	 */
	public function validate_parent_plugin_exists() {

		$plugin = plugin_basename( __FILE__ );
		if ( ( ! is_plugin_active( 'formidable/formidable.php' ) ) ) {
			add_action( 'admin_notices', array( $this, 'frmdform_missing_notice' ) );
			add_action( 'network_admin_notices', array( $this, 'frmdform_missing_notice' ) );
			deactivate_plugins( $plugin );
			if ( isset( $_GET['activate'] ) ) {
				// Do not sanitize it because we are destroying the variables from URL.
				unset( $_GET['activate'] );
				unset( $GLOBALS['gs_FFfree'] );// unset global variable after deactivate plugins.
			}
		}
	}


	/**
	 * If FF Form plugin is not installed or activated then throw the error
	 *
	 * @access public
	 * @return mixed error_message, an array containing the error message
	 *
	 * @since 1.0 initial version
	 */
	public function frmdform_missing_notice() {
		$plugin_error = Frm_Gsc_Connector_Utility::instance()->admin_notice(
			array(
				'type'    => 'error',
				'message' => __( 'GSheetConnector Add-on requires Formidable Form plugin to be installed and activated.', 'gsheetconnector-for-formidable-forms' ),
			)
		);
		// issues to after approve.
		echo esc_html( $plugin_error );
	}

	public function load_css_and_js_files() {
		add_action( 'admin_print_styles', array( $this, 'add_css_files' ) );
		add_action( 'admin_print_scripts', array( $this, 'add_js_files' ) );
	}

	/**
	 * Enqueue CSS files
	 *
	 * @since 1.0
	 */
	public function add_css_files() {
		if ( is_admin() && ( isset( $_GET['page'] ) && ( $_GET['page'] == 'formidable-form-google-sheet-config' ) ) ) {
			wp_enqueue_style( 'frmdform-gs-connector-css', FRMDFORM_GOOGLESHEET_VERSION_URL . 'assets/css/formidable-form-gs-connector.css', FRMDFORM_GOOGLESHEET_VERSION_VERSION, true );
			wp_enqueue_style( 'frmdform-gs-connector-font', FRMDFORM_GOOGLESHEET_VERSION_URL . 'assets/css/font-awesome.min.css', FRMDFORM_GOOGLESHEET_VERSION_VERSION, true );
		}
	}

	/**
	 * Enqueue JS files
	 *
	 * @since 1.0
	 */
	public function add_js_files() {
		if ( is_admin() && ( isset( $_GET['page'] ) && ( $_GET['page'] == 'formidable-form-google-sheet-config' ) ) ) {
			wp_enqueue_script( 'frmdform-gs-connector-js', FRMDFORM_GOOGLESHEET_VERSION_URL . 'assets/js/formidable-form-gs-connector.js', array(), FRMDFORM_GOOGLESHEET_VERSION_VERSION, true );
		}

		if ( is_admin() ) {
			wp_enqueue_script( 'frmdform-gs-connector-notice-css', FRMDFORM_GOOGLESHEET_VERSION_URL . 'assets/js/formidable-forms-gs-connector-notice.js', array(), FRMDFORM_GOOGLESHEET_VERSION_VERSION, true );
		}
	}


	/**
	 * Add custom link for the plugin beside activate/deactivate links
	 *
	 * @param array $links Array of links to display below our plugin listing.
	 * @return array Amended array of links.    *
	 * @since 1.0
	 */
	// issues solved of Setting Links 9-5-23(Ahmed).
	public function frmdforms_gs_connector_plugin_action_links( $links ) {
		// Check if Formidable plugin is active.
		if ( is_plugin_active( 'formidable/formidable.php' ) ) {
			// Remove the 'Edit' link.
			unset( $links['edit'] );

			// Add a 'Settings' link.
			$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=formidable-form-google-sheet-config' ) ) . '">' . esc_html__( 'Settings', 'gsheetconnector-for-formidable-forms' ) . '</a>';

			// Add the 'Settings' link to the returned array value.
			array_unshift( $links, $settings_link );

			return $links;
		} else {
			return $links;
		}
	}



	/**
	 * Called on activation.
	 * Creates the site_options (required for all the sites in a multi-site setup)
	 * If the current version doesn't match the new version, runs the upgrade
	 *
	 * @since 1.0
	 */
	private function run_on_activation() {
		try {
			$plugin_options = get_site_option( 'frmdforms_gs_info' );
			if ( false === $plugin_options ) {
				$frmdforms_gs_info = array(
					'version'    => FRMDFORM_GOOGLESHEET_VERSION_VERSION,
					'db_version' => FRMDFORM_GOOGLESHEET_VERSION_DB_VERSION,
				);
				update_site_option( 'frmdforms_gs_info', $frmdforms_gs_info );
			} elseif ( FRMDFORM_GOOGLESHEET_VERSION_DB_VERSION != $plugin_options['version'] ) {
				$this->run_on_upgrade();
			}
			$redirect_url = esc_url( admin_url( 'admin.php?page=formidable-form-google-sheet-config' ) );
			wp_redirect( $redirect_url );
		} catch ( Exception $e ) {
			Frm_Gsc_Connector_Utility::gs_debug_log( 'Something Wrong : - ' . $e->getMessage() );
		}
	}

	/**
	 * Called on upgrade.
	 * checks the current version and applies the necessary upgrades from that version onwards
	 *
	 * @since 1.0
	 */
	public function run_on_upgrade() {
		$plugin_options = get_site_option( 'frmdforms_gs_info' );

		// update the version value.
		$google_sheet_info = array(
			'version'    => FRMDFORM_GOOGLESHEET_VERSION_ROOT,
			'db_version' => FRMDFORM_GOOGLESHEET_VERSION_DB_VERSION,
		);
		update_site_option( 'frmdforms_gs_info', $google_sheet_info );
	}


	/**
	 * Called on activation.
	 * Creates the options and DB (required by per site)
	 *
	 * @since 1.0
	 */
	private function run_for_site() {
		try {
			if ( ! get_option( 'frmgsc_access_manual_code' ) ) {
				update_option( 'frmgsc_access_manual_code', '' );
			}
			if ( ! get_option( 'frmgsc_gs_verify' ) ) {
				update_option( 'frmgsc_gs_verify', 'invalid' );
			}
			if ( ! get_option( 'frmgsc_gs_verify' ) ) {
				update_option( 'frmgsc_gs_verify', '' );
			}
			if ( ! get_option( 'frmgsc_gs_verify' ) ) {
				update_option( 'frmgsc_gs_verify', 'false' );
			}
		} catch ( Exception $e ) {
			Frm_Gsc_Connector_Utility::gs_debug_log( 'Something Wrong : - ' . $e->getMessage() );
		}
	}

	/**
	 * Called on uninstall - deletes site specific options
	 *
	 * @since 1.0
	 */
	private static function delete_for_site() {
		try {
			delete_option( 'frmgsc_access_manual_code' );
			delete_option( 'frmgsc_gs_verify' );
			delete_option( 'frmgsc_gs_token_manual' );
			delete_post_meta_by_key( 'ffforms_gs_settings' );
		} catch ( Exception $e ) {
			Frm_Gsc_Connector_Utility::gs_debug_log( 'Something Wrong : - ' . $e->getMessage() );
		}
	}

	/**
	 * Register action in FF Email & Action tab
	 */
	public function frmgsc_register_actions( $actions ) {

		require_once FRMDFORM_GOOGLESHEET_VERSION_ROOT . '/includes/Action/class-frmformsgsheetconnector.php';
		$actions['frmgsc_forms_google_sheet'] = 'FRMFormsGSheetConnector';
		return $actions;
	}

	/**
	 * Register action in FF Email & Action tab
	 */
	public static function template( $file_name = '', array $data = array() ) {

		if ( ! $file_name ) {
			return;
		}
		extract( $data );

		include FRMDFORM_GOOGLESHEET_VERSION_PATH . 'includes/Templates/' . $file_name;
	}

	/**
	 * Add widget to the dashboard
	 *
	 * @since 1.0
	 */
	public function add_frmdform_gs_connector_summary_widget() {
		wp_add_dashboard_widget( 'frmdform_gs_dashboard', __( 'Formidable Forms -  GSheetConnector', 'gsheetconnector-for-formidable-forms' ), array( $this, 'frmdform_gs_connector_summary_dashboard' ) );
	}

	/**
	 * Display widget conetents
	 *
	 * @since 1.0
	 */
	public function frmdform_gs_connector_summary_dashboard() {
		include_once FRMDFORM_GOOGLESHEET_VERSION_ROOT . '/includes/pages/formidable-form-dashboard-widget.php';
	}

	/**
	 * Build System Information String
	 *
	 * @global object $wpdb
	 * @return string
	 * @since 1.2
	 */
	public function get_frmforms_system_info() {

		global $wpdb;

		// Get WordPress version.
		$wp_version = get_bloginfo( 'version' );

		// Get theme info.
		$theme_data         = wp_get_theme();
		$theme_name_version = $theme_data->get( 'Name' ) . ' ' . $theme_data->get( 'Version' );
		$parent_theme       = $theme_data->get( 'Template' );

		if ( ! empty( $parent_theme ) ) {
			$parent_theme_data         = wp_get_theme( $parent_theme );
			$parent_theme_name_version = $parent_theme_data->get( 'Name' ) . ' ' . $parent_theme_data->get( 'Version' );
		} else {
			$parent_theme_name_version = 'N/A';
		}

		// Check plugin version and subscription plan.
		$plugin_version    = defined( 'FRMDFORM_GOOGLESHEET_VERSION_VERSION' ) ? FRMDFORM_GOOGLESHEET_VERSION_VERSION : 'N/A';
		$subscription_plan = 'FREE';

		// Check Google Account Authentication.
		// $api_token = get_option('gs_token');
		// $google_sheet = new CF7GSC_googlesheet_PRO();
		// $email_account = $google_sheet->gsheet_print_google_account_email();

		$api_token_auto = get_option( 'frmgsc_gs_token_manual' );

		if ( ! empty( $api_token_auto ) ) {
			// The user is authenticated through the auto method.
			$google_sheet_auto  = new frmgsc_googlesheet();
			$email_account_auto = $google_sheet_auto->gsheet_print_google_account_email_manual();
			$connected_email    = ! empty( $email_account_auto ) ? esc_html( $email_account_auto ) : 'Not Auth';
		} else {
			// Auto authentication is the only method available.
			$connected_email = 'Not Auth';
		}

		// Check Google Permission.
		$gs_verify_status  = get_option( 'frmgsc_gs_verify' );
		$search_permission = ( $gs_verify_status === 'valid' ) ? 'Given' : 'Not Given';

		// Create the system info HTML.
		$system_info  = '<div class="system-statuswc">';
		$system_info .= '<h4><button id="show-info-button" class="info-button">GSheetConnector<span class="dashicons dashicons-arrow-down"></span></h4>';
		$system_info .= '<div id="info-container" class="info-content" style="display:none;">';
		$system_info .= '<h3>GSheetConnector</h3>';
		$system_info .= '<table>';
		$system_info .= '<tr><td>Plugin Version</td><td>' . esc_html( $plugin_version ) . '</td></tr>';
		$system_info .= '<tr><td>Plugin Subscription Plan</td><td>' . esc_html( $subscription_plan ) . '</td></tr>';
		$system_info .= '<tr><td>Connected Email Account</td><td>' . $connected_email . '</td></tr>';
		$system_info .= '<tr><td>Google Drive Permission</td><td>' . esc_html( $search_permission ) . '</td></tr>';
		$system_info .= '<tr><td>Google Sheet Permission</td><td>' . esc_html( $search_permission ) . '</td></tr>';
		$system_info .= '</table>';
		$system_info .= '</div>';
		// Add WordPress info.
		// Create a button for WordPress info.
		$system_info .= '<h4><button id="show-wordpress-info-button" class="info-button">WordPress Info<span class="dashicons dashicons-arrow-down"></span></h4>';
		$system_info .= '<div id="wordpress-info-container" class="info-content" style="display:none;">';
		$system_info .= '<h3>WordPress Info</h3>';
		$system_info .= '<table>';
		$system_info .= '<tr><td>Version</td><td>' . get_bloginfo( 'version' ) . '</td></tr>';
		$system_info .= '<tr><td>Site Language</td><td>' . get_bloginfo( 'language' ) . '</td></tr>';
		$system_info .= '<tr><td>Debug Mode</td><td>' . ( WP_DEBUG ? 'Enabled' : 'Disabled' ) . '</td></tr>';
		$system_info .= '<tr><td>Home URL</td><td>' . get_home_url() . '</td></tr>';
		$system_info .= '<tr><td>Site URL</td><td>' . get_site_url() . '</td></tr>';
		$system_info .= '<tr><td>Permalink structure</td><td>' . get_option( 'permalink_structure' ) . '</td></tr>';
		$system_info .= '<tr><td>Is this site using HTTPS?</td><td>' . ( is_ssl() ? 'Yes' : 'No' ) . '</td></tr>';
		$system_info .= '<tr><td>Is this a multisite?</td><td>' . ( is_multisite() ? 'Yes' : 'No' ) . '</td></tr>';
		$system_info .= '<tr><td>Can anyone register on this site?</td><td>' . ( get_option( 'users_can_register' ) ? 'Yes' : 'No' ) . '</td></tr>';
		$system_info .= '<tr><td>Is this site discouraging search engines?</td><td>' . ( get_option( 'blog_public' ) ? 'No' : 'Yes' ) . '</td></tr>';
		$system_info .= '<tr><td>Default comment status</td><td>' . get_option( 'default_comment_status' ) . '</td></tr>';

		$server_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? filter_var( wp_unslash( $_SERVER['REMOTE_ADDR'] ), FILTER_VALIDATE_IP ) : '';

		if ( filter_var( $server_ip, FILTER_VALIDATE_IP ) === false ) {
			// Invalid IP address, handle this error (you might want to set a default value).
			$environment_type = 'unknown';
		} else {
			// Validate against known local addresses.
			$known_local_ips = array( '127.0.0.1', '::1' ); // Add more if needed.

			$isLocalhost = in_array( $server_ip, $known_local_ips );

			$environment_type = $isLocalhost ? 'localhost' : 'production';
		}

		$system_info .= '<tr><td>Environment type</td><td>' . esc_html( $environment_type ) . '</td></tr>';

		$user_count   = count_users();
		$total_users  = $user_count['total_users'];
		$system_info .= '<tr><td>User Count</td><td>' . esc_html( $total_users ) . '</td></tr>';

		$system_info .= '<tr><td>Communication with WordPress.org</td><td>' . ( get_option( 'blog_publicize' ) ? 'Yes' : 'No' ) . '</td></tr>';
		$system_info .= '</table>';
		$system_info .= '</div>';

		// info about active theme.
		$active_theme = wp_get_theme();

		$system_info .= '<h4><button id="show-active-info-button" class="info-button">Active Theme<span class="dashicons dashicons-arrow-down"></span></h4>';
		$system_info .= '<div id="active-info-container" class="info-content" style="display:none;">';
		$system_info .= '<h3>Active Theme</h3>';
		$system_info .= '<table>';
		$system_info .= '<tr><td>Name</td><td>' . $active_theme->get( 'Name' ) . '</td></tr>';
		$system_info .= '<tr><td>Version</td><td>' . $active_theme->get( 'Version' ) . '</td></tr>';
		$system_info .= '<tr><td>Author</td><td>' . $active_theme->get( 'Author' ) . '</td></tr>';
		$system_info .= '<tr><td>Author website</td><td>' . $active_theme->get( 'AuthorURI' ) . '</td></tr>';
		$system_info .= '<tr><td>Theme directory location</td><td>' . $active_theme->get_template_directory() . '</td></tr>';
		$system_info .= '</table>';
		$system_info .= '</div>';

		// Get a list of other plugins you want to check compatibility with.
		$other_plugins = array(
			'plugin-folder/plugin-file.php', // Replace with the actual plugin slug
			// Add more plugins as needed.
		);

		// Network Active Plugins.
		if ( is_multisite() ) {
			$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
			if ( ! empty( $network_active_plugins ) ) {
				$system_info .= '<h4><button id="show-netplug-info-button" class="info-button">Network Active plugins<span class="dashicons dashicons-arrow-down"></span></h4>';
				$system_info .= '<div id="netplug-info-container" class="info-content" style="display:none;">';
				$system_info .= '<h3>Network Active plugins</h3>';
				$system_info .= '<table>';
				foreach ( $network_active_plugins as $plugin => $plugin_data ) {
					$plugin_data  = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
					$system_info .= '<tr><td>' . $plugin_data['Name'] . '</td><td>' . $plugin_data['Version'] . '</td></tr>';
				}
				// Add more network active plugin statuses here...
				$system_info .= '</table>';
				$system_info .= '</div>';
			}
		}
		// Active plugins.
		$system_info .= '<h4><button id="show-acplug-info-button" class="info-button">Active plugins<span class="dashicons dashicons-arrow-down"></span></h4>';
		$system_info .= '<div id="acplug-info-container" class="info-content" style="display:none;">';
		$system_info .= '<h3>Active plugins</h3>';
		$system_info .= '<table>';

		// Retrieve all active plugins data.
		$active_plugins_data = array();
		$active_plugins      = get_option( 'active_plugins', array() );

		// Include the necessary WordPress file.
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$all_plugins = get_plugins();
		foreach ( $active_plugins as $plugin ) {
			if ( isset( $all_plugins[ $plugin ] ) ) {
				$plugin_data                    = $all_plugins[ $plugin ];
				$active_plugins_data[ $plugin ] = array(
					'name'    => $plugin_data['Name'],
					'version' => $plugin_data['Version'],
					'count'   => 0, // Initialize the count to zero.
				);
			}
		}

		// Count the number of active installations for each plugin.
		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( array_key_exists( $plugin_file, $active_plugins_data ) ) {
				++$active_plugins_data[ $plugin_file ]['count'];
			}
		}

		// Sort plugins based on the number of active installations (descending order).
		uasort(
			$active_plugins_data,
			function ( $a, $b ) {
				return $b['count'] - $a['count'];
			}
		);

		// Display the top 5 most used plugins.
		$counter = 0;
		foreach ( $active_plugins_data as $plugin_data ) {
			$system_info .= '<tr><td>' . $plugin_data['name'] . '</td><td>' . $plugin_data['version'] . '</td></tr>';
			// ++$counter;
			// if ( $counter >= 5 ) {
			// break;
			// }
		}

		$system_info .= '</table>';
		$system_info .= '</div>';
		// Webserver Configuration.
		$system_info .= '<h4><button id="show-server-info-button" class="info-button">Server<span class="dashicons dashicons-arrow-down"></span></h4>';
		$system_info .= '<div id="server-info-container" class="info-content" style="display:none;">';
		$system_info .= '<h3>Server</h3>';
		$system_info .= '<table>';
		$system_info .= '<p>The options shown below relate to your server setup. If changes are required, you may need your web host`s assistance.</p>';
		// Add Server information.
		$system_info    .= '<tr><td>Server Architecture</td><td>' . esc_html( php_uname( 's' ) ) . '</td></tr>';
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
		$system_info    .= '<tr><td>Web Server</td><td>' . esc_html( wp_kses( esc_attr( $server_software ), 'post' ) ) . '</td></tr>';
		$system_info    .= '<tr><td>PHP Version</td><td>' . esc_html( phpversion() ) . '</td></tr>';
		$system_info    .= '<tr><td>PHP SAPI</td><td>' . esc_html( php_sapi_name() ) . '</td></tr>';
		$system_info    .= '<tr><td>PHP Max Input Variables</td><td>' . esc_html( ini_get( 'max_input_vars' ) ) . '</td></tr>';
		$system_info    .= '<tr><td>PHP Time Limit</td><td>' . esc_html( ini_get( 'max_execution_time' ) ) . ' seconds</td></tr>';
		$system_info    .= '<tr><td>PHP Memory Limit</td><td>' . esc_html( ini_get( 'memory_limit' ) ) . '</td></tr>';
		$system_info    .= '<tr><td>Max Input Time</td><td>' . esc_html( ini_get( 'max_input_time' ) ) . ' seconds</td></tr>';
		$system_info    .= '<tr><td>Upload Max Filesize</td><td>' . esc_html( ini_get( 'upload_max_filesize' ) ) . '</td></tr>';
		$system_info    .= '<tr><td>PHP Post Max Size</td><td>' . esc_html( ini_get( 'post_max_size' ) ) . '</td></tr>';
		$system_info    .= '<tr><td>cURL Version</td><td>' . esc_html( curl_version()['version'] ) . '</td></tr>';
		$system_info    .= '<tr><td>Is SUHOSIN Installed?</td><td>' . ( extension_loaded( 'suhosin' ) ? 'Yes' : 'No' ) . '</td></tr>';
		$system_info    .= '<tr><td>Is the Imagick Library Available?</td><td>' . ( extension_loaded( 'imagick' ) ? 'Yes' : 'No' ) . '</td></tr>';
		$system_info    .= '<tr><td>Are Pretty Permalinks Supported?</td><td>' . ( get_option( 'permalink_structure' ) ? 'Yes' : 'No' ) . '</td></tr>';
		$system_info    .= '<tr><td>.htaccess Rules</td><td>' . esc_html( is_writable( '.htaccess' ) ? 'Writable' : 'Non Writable' ) . '</td></tr>';
		$system_info    .= '<tr><td>Current Time</td><td>' . esc_html( current_time( 'mysql' ) ) . '</td></tr>';
		$system_info    .= '<tr><td>Current UTC Time</td><td>' . esc_html( current_time( 'mysql', true ) ) . '</td></tr>';
		$system_info    .= '<tr><td>Current Server Time</td><td>' . esc_html( gmdate( 'Y-m-d H:i:s' ) ) . '</td></tr>';
		$system_info    .= '</table>';
		$system_info    .= '</div>';

		// Database Configuration.
		$system_info            .= '<h4><button id="show-database-info-button" class="info-button">Database<span class="dashicons dashicons-arrow-down"></span></h4>';
		$system_info            .= '<div id="database-info-container" class="info-content" style="display:none;">';
		$system_info            .= '<h3>Database</h3>';
		$system_info            .= '<table>';
		$database_extension      = 'mysqli';
		$database_server_version = $wpdb->get_var( 'SELECT VERSION() as version' );
		$database_client_version = $wpdb->db_version();
		$database_username       = DB_USER;
		$database_host           = DB_HOST;
		$database_name           = DB_NAME;
		$table_prefix            = $wpdb->prefix;
		$database_charset        = $wpdb->charset;
		$database_collation      = $wpdb->collate;
		$max_allowed_packet_size = $wpdb->get_var( "SHOW VARIABLES LIKE 'max_allowed_packet'" );
		$max_connections_number  = $wpdb->get_var( "SHOW VARIABLES LIKE 'max_connections'" );

		$system_info .= '<tr><td>Extension</td><td>' . esc_html( $database_extension ) . '</td></tr>';
		$system_info .= '<tr><td>Server Version</td><td>' . esc_html( $database_server_version ) . '</td></tr>';
		$system_info .= '<tr><td>Client Version</td><td>' . esc_html( $database_client_version ) . '</td></tr>';
		$system_info .= '<tr><td>Database Username</td><td>' . esc_html( $database_username ) . '</td></tr>';
		$system_info .= '<tr><td>Database Host</td><td>' . esc_html( $database_host ) . '</td></tr>';
		$system_info .= '<tr><td>Database Name</td><td>' . esc_html( $database_name ) . '</td></tr>';
		$system_info .= '<tr><td>Table Prefix</td><td>' . esc_html( $table_prefix ) . '</td></tr>';
		$system_info .= '<tr><td>Database Charset</td><td>' . esc_html( $database_charset ) . '</td></tr>';
		$system_info .= '<tr><td>Database Collation</td><td>' . esc_html( $database_collation ) . '</td></tr>';
		$system_info .= '<tr><td>Max Allowed Packet Size</td><td>' . esc_html( $max_allowed_packet_size ) . '</td></tr>';
		$system_info .= '<tr><td>Max Connections Number</td><td>' . esc_html( $max_connections_number ) . '</td></tr>';
		$system_info .= '</table>';
		$system_info .= '</div>';

		// WordPress constants.
		$system_info .= '<h4><button id="show-wrcons-info-button" class="info-button">WordPress Constants<span class="dashicons dashicons-arrow-down"></span></h4>';
		$system_info .= '<div id="wrcons-info-container" class="info-content" style="display:none;">';
		$system_info .= '<h3>WordPress Constants</h3>';
		$system_info .= '<table>';
		// Add WordPress Constants information.
		$system_info .= '<tr><td>ABSPATH</td><td>' . esc_html( ABSPATH ) . '</td></tr>';
		$system_info .= '<tr><td>WP_HOME</td><td>' . esc_html( home_url() ) . '</td></tr>';
		$system_info .= '<tr><td>WP_SITEURL</td><td>' . esc_html( site_url() ) . '</td></tr>';
		$system_info .= '<tr><td>WP_CONTENT_DIR</td><td>' . esc_html( WP_CONTENT_DIR ) . '</td></tr>';
		$system_info .= '<tr><td>WP_PLUGIN_DIR</td><td>' . esc_html( WP_PLUGIN_DIR ) . '</td></tr>';
		$system_info .= '<tr><td>WP_MEMORY_LIMIT</td><td>' . esc_html( WP_MEMORY_LIMIT ) . '</td></tr>';
		$system_info .= '<tr><td>WP_MAX_MEMORY_LIMIT</td><td>' . esc_html( WP_MAX_MEMORY_LIMIT ) . '</td></tr>';
		$system_info .= '<tr><td>WP_DEBUG</td><td>' . ( defined( 'WP_DEBUG' ) && WP_DEBUG ? 'Yes' : 'No' ) . '</td></tr>';
		$system_info .= '<tr><td>WP_DEBUG_DISPLAY</td><td>' . ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ? 'Yes' : 'No' ) . '</td></tr>';
		$system_info .= '<tr><td>SCRIPT_DEBUG</td><td>' . ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'Yes' : 'No' ) . '</td></tr>';
		$system_info .= '<tr><td>WP_CACHE</td><td>' . ( defined( 'WP_CACHE' ) && WP_CACHE ? 'Yes' : 'No' ) . '</td></tr>';
		$system_info .= '<tr><td>CONCATENATE_SCRIPTS</td><td>' . ( defined( 'CONCATENATE_SCRIPTS' ) && CONCATENATE_SCRIPTS ? 'Yes' : 'No' ) . '</td></tr>';
		$system_info .= '<tr><td>COMPRESS_SCRIPTS</td><td>' . ( defined( 'COMPRESS_SCRIPTS' ) && COMPRESS_SCRIPTS ? 'Yes' : 'No' ) . '</td></tr>';
		$system_info .= '<tr><td>COMPRESS_CSS</td><td>' . ( defined( 'COMPRESS_CSS' ) && COMPRESS_CSS ? 'Yes' : 'No' ) . '</td></tr>';
		// Manually define the environment type (example values: 'development', 'staging', 'production').
		$environment_type = 'development';

		// Display the environment type.
		$system_info .= '<tr><td>WP_ENVIRONMENT_TYPE</td><td>' . esc_html( $environment_type ) . '</td></tr>';

		$system_info .= '<tr><td>WP_DEVELOPMENT_MODE</td><td>' . ( defined( 'WP_DEVELOPMENT_MODE' ) && WP_DEVELOPMENT_MODE ? 'Yes' : 'No' ) . '</td></tr>';
		$system_info .= '<tr><td>DB_CHARSET</td><td>' . esc_html( DB_CHARSET ) . '</td></tr>';
		$system_info .= '<tr><td>DB_COLLATE</td><td>' . esc_html( DB_COLLATE ) . '</td></tr>';

		$system_info .= '</table>';
		$system_info .= '</div>';

		// Filesystem Permission.
		$system_info .= '<h4><button id="show-ftps-info-button" class="info-button">Filesystem Permission <span class="dashicons dashicons-arrow-down"></span></button></h4>';
		$system_info .= '<div id="ftps-info-container" class="info-content" style="display:none;">';
		$system_info .= '<h3>Filesystem Permission</h3>';
		$system_info .= '<p>Shows whether WordPress is able to write to the directories it needs access to.</p>';
		$system_info .= '<table>';
		// Filesystem Permission information.
		$system_info .= '<tr><td>The main WordPress directory</td><td>' . esc_html( ABSPATH ) . '</td><td>' . ( is_writable( ABSPATH ) ? 'Writable' : 'Not Writable' ) . '</td></tr>';
		$system_info .= '<tr><td>The wp-content directory</td><td>' . esc_html( WP_CONTENT_DIR ) . '</td><td>' . ( is_writable( WP_CONTENT_DIR ) ? 'Writable' : 'Not Writable' ) . '</td></tr>';
		$system_info .= '<tr><td>The uploads directory</td><td>' . esc_html( wp_upload_dir()['basedir'] ) . '</td><td>' . ( is_writable( wp_upload_dir()['basedir'] ) ? 'Writable' : 'Not Writable' ) . '</td></tr>';
		$system_info .= '<tr><td>The plugins directory</td><td>' . esc_html( WP_PLUGIN_DIR ) . '</td><td>' . ( is_writable( WP_PLUGIN_DIR ) ? 'Writable' : 'Not Writable' ) . '</td></tr>';
		$system_info .= '<tr><td>The themes directory</td><td>' . esc_html( get_theme_root() ) . '</td><td>' . ( is_writable( get_theme_root() ) ? 'Writable' : 'Not Writable' ) . '</td></tr>';

		$system_info .= '</table>';
		$system_info .= '</div>';

		return $system_info;
	}

	public function display_error_log() {
		// Define the path to your debug log file.
		$debug_log_file = WP_CONTENT_DIR . '/debug.log';

		// Check if the debug log file exists.
		if ( file_exists( $debug_log_file ) ) {
			// Read the contents of the debug log file.
			$debug_log_contents = file_get_contents( $debug_log_file );

			// Split the log content into an array of lines.
			$log_lines = explode( "\n", $debug_log_contents );

			// Get the last 100 lines in reversed order.
			$last_100_lines = array_slice( array_reverse( $log_lines ), 0, 100 );

			// Join the lines back together with line breaks.
			$last_100_log = implode( "\n", $last_100_lines );

			// Output the last 100 lines in reversed order in a textarea.
			?>
			<textarea class="errorlog" rows="20" cols="80"><?php echo esc_textarea( $last_100_log ); ?></textarea>
			<?php
		} else {
			echo 'Debug log file not found.';
		}
	}
}

// Initialize the njform google sheet connector class.
$init = new Gsheetconnector_For_Formidable_Forms();

/**
 * Class frmgsc_onsubmit_data
 *
 * Handles the submission of Formidable Forms entries and sends form fields to Google Sheets.
 */
class frmgsc_onsubmit_data {
	/**
	 * @var int The ID of the form being processed.
	 */
	private $form_id;

	/**
	 * Frmgsc_onsubmit_data constructor.
	 *
	 * Adds the necessary action hook for handling form submissions.
	 */
	public function __construct() {
		add_action( 'frm_after_create_entry', array( $this, 'send_form_fields_to_google_sheets' ), 30, 2 );
	}

	/**
	 * Sends form fields to Google Sheets after a Formidable Forms entry is created.
	 *
	 * @param int $entry_id The ID of the created entry.
	 * @param int $form_id  The ID of the form.
	 */
	public function send_form_fields_to_google_sheets( $entry_id, $form_id ) {
		// Retrieve the form fields for the submitted entry.
		$fields    = FrmField::getAll();
		$entry_id  = $entry_id;
		$field_ids = array();

		// Iterate through all fields to find form-specific ones.
		foreach ( $fields as $field ) {
			// Check if the field is a form field.
			if ( $field->form_id == $form_id ) {
				// Add the field ID to the list of fields to retrieve.
				$field_ids[ $field->name ] = $field->id;
			}
		}

		// Retrieve the form fields for the submitted entry.
		$meta_values = array();
		foreach ( $field_ids as $field_name => $field_id ) {
			$meta_value = FrmEntryMeta::get_entry_meta_by_field( $entry_id, $field_id );
			if ( $meta_value ) {
				$meta_values[ $field_name ] = is_array( $meta_value ) ? implode( ',', $meta_value ) : $meta_value;
			}
		}

		// Retrieve the sheet details from the database.
		global $wpdb;
		$options    = $wpdb->get_var( $wpdb->prepare( "SELECT options FROM {$wpdb->prefix}frm_forms WHERE id=%d", $form_id ) );
		$options    = maybe_unserialize( $options );
		$sheet_name = isset( $options['sheet_name'] ) ? sanitize_text_field( $options['sheet_name'] ) : '';
		$sheet_id   = isset( $options['sheet_id'] ) ? sanitize_text_field( $options['sheet_id'] ) : '';
		$tab_name   = isset( $options['tab_name'] ) ? sanitize_text_field( $options['tab_name'] ) : '';
		$tab_id     = isset( $options['tab_id'] ) ? sanitize_text_field( $options['tab_id'] ) : '';
		$range      = isset( $options['range'] ) ? sanitize_text_field( $options['range'] ) : '';

		if ( ( ! empty( $sheet_id ) ) && ( ! empty( $tab_name ) ) ) {
			try {
				include_once FRMDFORM_GOOGLESHEET_VERSION_ROOT . '/lib/google-sheets.php';
				$doc = new frmgsc_googlesheet();
				$doc->auth();
				$doc->setSpreadsheetId( $sheet_id );
				$doc->setWorkTabId( $tab_id );

				// Fetched local date and time instead of Unix date and time.
				$meta_values['date'] = date_i18n( get_option( 'date_format' ) );
				$meta_values['time'] = date_i18n( get_option( 'time_format' ) );

				$doc->add_row( $meta_values );
			} catch ( Exception $e ) {
				// Log any errors that occur during the Google Sheets update.
				$meta_values['ERROR_MSG'] = $e->getMessage();
				$meta_values['TRACE_STK'] = $e->getTraceAsString();
				Frm_Gsc_Connector_Utility::gs_debug_log( $meta_values );
			}
		}
	}
}

// Create an instance of the class to initiate the action hook.
$form_handler = new frmgsc_onsubmit_data();

