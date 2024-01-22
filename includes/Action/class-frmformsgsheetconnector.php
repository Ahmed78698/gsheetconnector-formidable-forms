<?php

/**
 * File: class-frmformsgsheetconnector.php
 * Description: This file contains the FRMFormsGSheetConnector class for sending form data to Google Sheets.
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'FrmFormAction' ) ) {
	exit;
}

/**
 * Class for the FRMFormsGSheetConnector.
 */
class FRMFormsGSheetConnector extends FrmFormAction {
	/**
	 * Constructor for the FRMFormsGSheetConnector class.
	 */
	public function __construct() {
		// parent::__construct();

		$id                    = 'GSheetConnector';
		$name                  = 'GSheetConnector'; // Set the action name to 'GsheetConnector'
		$group                 = 'GSheetConnector';
		$icon                  = 'frm_icon_font frm_credit_card_alt_icon'; // Set the icon to 'google sheet'
		$post_type             = 'frm_form_action';
		$has_custom_style      = true;
		$has_conditional_logic = true; // $has_conditional_logic = true;
		$has_limit_option      = true;
		$has_custom_html       = true;
		$has_fieldmap          = true;
		$description           = __( 'Send Your Forms Data To Google Sheet', 'gsheetconnector-for-formidable-forms' );

		// Pass the $id and $name arguments directly to the parent __construct() method.
		parent::__construct( $id, $name );
	}

	public function form( $form_action, $args = array() ) {
		global $wpdb;

		// Get the form ID dynamically.
		$form_id = $args['form']->id;

		// Retrieve the options for the form from the wp_frm_forms table.
		$options_serialized = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT `options` FROM {$wpdb->prefix}frm_forms WHERE `id` = %d",
				$form_id
			)
		);

		// Sanitize and validate the data before using unserialize.
		$options = maybe_unserialize( $options_serialized );

		// Output the form settings fields.
		$this->display_form_settings( $options );
	}

	// Display form settings.
	private function display_form_settings( $options ) {
		// Check if the user is authenticated.
		$authenticated = get_option( 'frmgsc_gs_token_manual' );
		$per           = get_option( 'frmgsc_gs_verify' );
		$show_setting  = 0;

		if ( ! empty( $authenticated ) && $per === 'valid' ) {
			$show_setting = 1;
		} else {
			$this->display_authentication_required_message();
		}

		if ( $show_setting == 1 ) {
			$this->display_settings_table( $options );
		}
	}

	// Display the authentication required message.
	private function display_authentication_required_message() {
		?>
		<div class="authentication-required" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 4px;">
			<div class="message" style="margin: 0; padding: 0;">
				<strong><?php echo esc_html__( 'Authentication Required:', 'gsheetconnector-for-formidable-forms' ); ?></strong>
				<?php echo esc_html__( 'You must have to', 'gsheetconnector-for-formidable-forms' ); ?> <a href="admin.php?page=formidable-form-google-sheet-config" target="_blank" style="color: #721c24; text-decoration: underline;"><?php echo esc_html__( 'Authenticate using your Google Account', 'gsheetconnector-for-formidable-forms' ); ?></a> <?php echo esc_html__( 'along with Google Drive and Google Sheets Permissions in order to enable the settings for configuration.', 'gsheetconnector-for-formidable-forms' ); ?>
			</div>
		</div>
		<?php
	}

	// Display the form settings table.
	private function display_settings_table( $options ) {
		?>
		<table class="form-table frm-no-margin">
			<tr>
				<th>
					<?php echo esc_html( wp_kses( __( 'Sheet Name', 'gsheetconnector-for-formidable-forms' ), array() ) ); ?>
				</th>
				<td>
					<input type="text" name="options[sheet_name]" value="<?php echo esc_attr( isset( $options['sheet_name'] ) ? $options['sheet_name'] : '' ); ?>">
				</td>
			</tr>
			<tr>
				<th>
					<?php echo esc_html( wp_kses( __( 'Sheet ID', 'gsheetconnector-for-formidable-forms' ), array() ) ); ?>
				</th>
				<td>
					<input type="text" name="options[sheet_id]" value="<?php echo esc_attr( isset( $options['sheet_id'] ) ? $options['sheet_id'] : '' ); ?>">
				</td>
			</tr>
			<tr>
				<th>
					<?php echo esc_html( wp_kses( __( 'Tab Name', 'gsheetconnector-for-formidable-forms' ), array() ) ); ?>
				</th>
				<td>
					<input type="text" name="options[tab_name]" value="<?php echo esc_attr( isset( $options['tab_name'] ) ? $options['tab_name'] : '' ); ?>">
				</td>
			</tr>
			<tr>
				<th>
					<?php echo esc_html( wp_kses( __( 'Tab ID', 'gsheetconnector-for-formidable-forms' ), array() ) ); ?>
				</th>
				<td>
					<input type="text" name="options[tab_id]" value="<?php echo esc_attr( isset( $options['tab_id'] ) ? $options['tab_id'] : '' ); ?>">
				</td>
			</tr>
			<!-- Add the Google Sheets link button here -->
			<?php
				// Assuming $options['sheet_id'] and $options['tab_id'] hold the required values.
				$sheet_id = isset( $options['sheet_id'] ) ? $options['sheet_id'] : '';
				$tab_id   = isset( $options['tab_id'] ) ? $options['tab_id'] : '';

			if ( ! empty( $sheet_id ) ) {
				?>
			<tr>
				<th>
				<?php echo esc_html( wp_kses( __( 'Google Sheets Link', 'gsheetconnector-for-formidable-forms' ), array() ) ); ?>
				</th>
				<td>
				<?php
				// Trim any extra spaces from $sheet_id.
				$sheet_id = trim( $sheet_id );
				$tab_id   = trim( $tab_id );

				$link = 'https://docs.google.com/spreadsheets/d/' . $sheet_id . '/edit#gid=' . $tab_id;
				echo '<a href="' . esc_url( $link ) . '" target="_blank" class="google-sheets-button" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; border-radius: 5px;" >View Google Sheet</a>';
				?>
				</td>
			</tr>
		<?php } ?>
		<!-- End of Google Sheets link button -->
		</table>
		<?php
	}
}

$obj = new FRMFormsGSheetConnector();
// $obj->description = __('Send Your Forms Data To Google Sheet', 'gsheetconnector-formidableforms');
$description           = __( 'Send Your Forms Data To Google Sheet', 'gsheetconnector-for-formidable-forms' );
$group                 = 'GSheetConnector';
$icon                  = 'frm_icon_font frm_credit_card_alt_icon';
$post_type             = 'frm_form_action';
$has_custom_style      = true;
$has_conditional_logic = true; // $has_conditional_logic = true;
$has_limit_option      = true;
$has_custom_html       = true;
$has_fieldmap          = true;
