<?php

/**
 * Utilities class for Formidable Forms Google Sheet Connector
 *
 * @since 1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Utilities class - singleton class
 *
 * @since 1.0
 */
class Frm_Gsc_Connector_Utility {

	/**
	 * Add Sub Menu in Formidable Forms
	 *
	 * @return singleton instance of Frm_Gsc_Connector_Utility
	 */
	public function frm_admin_page() {
		$current_role = $this->get_current_user_role();
		add_submenu_page( 'formidable', __( 'Google Sheets', 'gsheetconnector-FFforms' ), __( 'Google Sheets', 'gsheetconnector-FFforms' ), $current_role, 'formidable-form-google-sheet-config', array( $this, 'formidableforms_google_sheet_config' ) );
	}

	/**
	 * Setting Page
	 */
	public function formidableforms_google_sheet_config() {
		include FRMDFORM_GOOGLESHEET_VERSION_PATH . 'includes/pages/formidable-gs-setting-info.php';
	}

	/**
	 * Get the singleton instance of the Frm_Gsc_Connector_Utility class
	 *
	 * @return singleton instance of Frm_Gsc_Connector_Utility
	 */
	public static function instance() {
		static $instance = null;
		if ( is_null( $instance ) ) {
			$instance = new Frm_Gsc_Connector_Utility();
		}
		return $instance;
	}

	/**
	 * Prints message (string or array) in the debug.log file
	 *
	 * @param mixed $message
	 */
	public function logger( $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( wp_json_encode( $message ) );
			} else {
				error_log( $message );
			}
		}
	}


	/**
	 * Display error or success message in the admin section
	 *
	 * @param array $data containing type and message
	 * @return string with HTML containing the error message
	 *
	 * @since 1.0
	 */
	public function admin_notice( $data = array() ) {
		// Extract message and type from the $data array.
		$message      = isset( $data['message'] ) ? esc_html( $data['message'] ) : '';
		$message_type = isset( $data['type'] ) ? $data['type'] : '';

		switch ( $message_type ) {
			case 'error':
				$admin_notice = '<div id="message" class="error notice is-dismissible">';
				break;
			case 'update':
				$admin_notice = '<div id="message" class="updated notice is-dismissible">';
				break;
			case 'update-nag':
				$admin_notice = '<div id="message" class="update-nag">';
				break;
			case 'upgrade':
				$admin_notice = '<div id="message" class="error notice frmdforms-gs-upgrade is-dismissible">';
				break;
			default:
				$message      = __( 'There\'s something wrong with your code...', 'gsheetconnector-for-formidable-forms' );
				$admin_notice = '<div id="message" class="error">';
				break;
		}

		$admin_notice .= '<p>' . $message . '</p>\n';
		$admin_notice .= '</div>\n';

		return $admin_notice;
	}


	/**
	 * Utility function to get the current user's role
	 */
	public function get_current_user_role() {
		global $wp_roles;
		foreach ( $wp_roles->role_names as $role => $name ) {
			if ( current_user_can( $role ) ) {
				return $role;
			}
		}
	}

	/**
	 * Connected Forms function
	 */
	public function get_forms_connected_to_formidable_forms() {
		global $wpdb;

		// Check if cache exists.
		$cached_forms = wp_cache_get( 'frm_connected_forms' );

		if ( $cached_forms === false ) {
			// Cache not found, fetch from database.
			$query        = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'postmeta AS pm WHERE pm.meta_key = "frm_entry_meta"' );
			$cached_forms = $query;

			// Store in cache for future use.
			wp_cache_set( 'frm_connected_forms', $cached_forms );
		}

		return $cached_forms;
	}
}
