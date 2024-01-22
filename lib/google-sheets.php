<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

class frmgsc_googlesheet {


	private $token;
	private $spreadsheet;
	private $worksheet;

	private static $instance;

	public function __construct() {
	}

	public static function setInstance( Google_Client $instance = null ) {
		self::$instance = $instance;
	}

	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			throw new LogicException( 'Invalid Client' );
		}

		return self::$instance;
	}


	public function auth() {

		$tokenData = json_decode( get_option( 'frmgsc_gs_token_manual' ), true );

		if ( ! isset( $tokenData['refresh_token'] ) || empty( $tokenData['refresh_token'] ) ) {
			throw new LogicException( 'Auth, Invalid OAuth2 access token' );
			exit();
		}

		try {
			$client = new Google_Client();

			$gs_ff_client_id = get_option( 'frmgsc_client_id' );
			$gs_ff_secret_id = get_option( 'frmgsc_secret_id' );
			$client->setClientId( $gs_ff_client_id );
			$client->setClientSecret( $gs_ff_secret_id );

			$client->setScopes( Google_Service_Sheets::SPREADSHEETS );
			$client->setScopes( Google_Service_Drive::DRIVE_METADATA_READONLY );
			$client->refreshToken( $tokenData['refresh_token'] );
			$client->setAccessType( 'offline' );

			self::updateToken_manual( $tokenData );

			self::setInstance( $client );
		} catch ( Exception $e ) {
			throw new LogicException( 'Auth, Error fetching OAuth2 access token, message: ' . $e->getMessage() );
			exit();
		}
	}

	public static function updateToken_manual( $tokenData ) {
		$expires_in          = isset( $tokenData['expires_in'] ) ? intval( $tokenData['expires_in'] ) : 0;
		$tokenData['expire'] = time() + $expires_in;
		try {

			if ( isset( $tokenData['scope'] ) ) {
				$permission = explode( ' ', $tokenData['scope'] );
				if ( ( in_array( 'https://www.googleapis.com/auth/drive.metadata.readonly', $permission ) ) && ( in_array( 'https://www.googleapis.com/auth/spreadsheets', $permission ) ) ) {
					update_option( 'frmgsc_gs_verify', 'valid' );
				} else {
					update_option( 'frmgsc_gs_verify', 'invalid-auth' );
				}
			}
			$tokenJson = json_encode( $tokenData );
			update_option( 'frmgsc_gs_token_manual', $tokenJson );
		} catch ( Exception $e ) {
			Frm_Gsc_Connector_Utility::gs_debug_log( 'Token write fail! - ' . $e->getMessage() );
		}
	}

	/* preauth for manual client and secret id */
	public static function preauth_manual( $access_code, $client_id, $secret_id, $redirect_url ) {
			$client = new Google_Client();
			$client->setClientId( $client_id );
			$client->setClientSecret( $secret_id );
			$client->setRedirectUri( $redirect_url );
			$client->setScopes( Google_Service_Sheets::SPREADSHEETS );
			$client->setScopes( Google_Service_Drive::DRIVE_METADATA_READONLY );
			$client->setAccessType( 'offline' );
			$client->fetchAccessTokenWithAuthCode( $access_code );
			$tokenData = $client->getAccessToken();
			self::updateToken_manual( $tokenData );
	}

	/**
	 * Generate token for the user and refresh the token if it's expired.
	 *
	 * @return array
	 */
	public static function getClient_auth( $flag = 0, $gscff_clientId = '', $gscff_clientSecert = '' ) {
		$gscff_client = new Google_Client();
		$gscff_client->setApplicationName( 'Manage Formidable Forms with Google Spreadsheet' );
		$gscff_client->setScopes( Google_Service_Sheets::SPREADSHEETS_READONLY );
		$gscff_client->setScopes( Google_Service_Drive::DRIVE_METADATA_READONLY );
		$gscff_client->addScope( Google_Service_Drive::DRIVE_FILE );// added for uploading file

		$gscff_client->addScope( Google_Service_Sheets::SPREADSHEETS );
		$gscff_client->addScope( 'https://www.googleapis.com/auth/userinfo.email' );

		$gscff_client->setClientId( $gscff_clientId );
		$gscff_client->setClientSecret( $gscff_clientSecert );
		$gscff_client->setRedirectUri( esc_html( admin_url( 'admin.php?page=formidable-form-google-sheet-config' ) ) );
		$gscff_client->setAccessType( 'offline' );
		$gscff_client->setApprovalPrompt( 'force' );
		try {
			if ( empty( $gscff_auth_token ) ) {
				$gscff_auth_url = $gscff_client->createAuthUrl();
				return $gscff_auth_url;
			}
			if ( ! empty( $gscff_gscff_accessToken ) ) {
				$gscff_accessToken = json_decode( $gscff_gscff_accessToken, true );
			} elseif ( empty( $gscff_auth_token ) ) {
					$gscff_auth_url = $gscff_client->createAuthUrl();
					return $gscff_auth_url;
			}

			$gscff_client->setAccessToken( $gscff_accessToken );
			// Refresh the token if it's expired.
			if ( $gscff_client->isAccessTokenExpired() ) {
				// save refresh token to some variable
				$gscff_refreshTokenSaved = $gscff_client->getRefreshToken();
				$gscff_client->fetchAccessTokenWithRefreshToken( $gscff_client->getRefreshToken() );
				// pass access token to some variable
				$gscff_accessTokenUpdated = $gscff_client->getAccessToken();
				// append refresh token
				$gscff_accessTokenUpdated['refresh_token'] = $gscff_refreshTokenSaved;
				// Set the new acces token
				$gscff_accessToken = $gscff_refreshTokenSaved;
				gscff::gscff_update_option( 'ffsheets_google_accessToken', json_encode( $gscff_accessTokenUpdated ) );
				$gscff_accessToken = json_decode( json_encode( $gscff_accessTokenUpdated ), true );
				$gscff_client->setAccessToken( $gscff_accessToken );
			}
		} catch ( Exception $e ) {
			if ( $flag ) {
				return $e->getMessage();
			} else {
				return false;
			}
		}
		return $gscff_client;
	}

	// preg_match is a key of error handle in this case
	public function setSpreadsheetId( $id ) {
		$this->spreadsheet = $id;
	}

	public function getSpreadsheetId() {

		return $this->spreadsheet;
	}

	public function setWorkTabId( $id ) {
		$this->worksheet = $id;
	}

	public function getWorkTabId() {
		return $this->worksheet;
	}

	public function add_row( $data ) {
		try {
			$client        = self::getInstance();
			$service       = new Google_Service_Sheets( $client );
			$spreadsheetId = $this->getSpreadsheetId();
			$work_sheets   = $service->spreadsheets->get( $spreadsheetId );

			if ( ! empty( $work_sheets ) && ! empty( $data ) ) {
				foreach ( $work_sheets as $sheet ) {
					$properties = $sheet->getProperties();

					$sheet_id     = $properties->getSheetId();
					$worksheet_id = $this->getWorkTabId();

					if ( $sheet_id == $worksheet_id ) {
							$worksheet_id  = $properties->getTitle();
							$worksheetCell = $service->spreadsheets_values->get( $spreadsheetId, $worksheet_id . '!1:1' );
							$insert_data   = array();
						if ( isset( $worksheetCell->values[0] ) ) {
							$insert_data_index = 0;
							foreach ( $worksheetCell->values[0] as $k => $name ) {
								if ( $insert_data_index == 0 ) {
									if ( isset( $data[ $name ] ) && $data[ $name ] != '' ) {
											$insert_data[] = $data[ $name ];
									} else {
										$insert_data[] = '';
									}
								} elseif ( isset( $data[ $name ] ) && $data[ $name ] != '' ) {
										$insert_data[] = $data[ $name ];
								} else {
									$insert_data[] = '';
								}
								++$insert_data_index;
							}
						}
						$range_new = $worksheet_id;

						// Create the value range Object
						$valueRange = new Google_Service_Sheets_ValueRange();

						// set values of inserted data
						$valueRange->setValues( array( 'values' => $insert_data ) );

						// Add two values
						// Then you need to add configuration
						$conf = array( 'valueInputOption' => 'USER_ENTERED' );

						// append the spreadsheet(add new row in the sheet)
						$result = $service->spreadsheets_values->append( $spreadsheetId, $range_new, $valueRange, $conf );
					}
				}
			}
		} catch ( Exception $e ) {
			return null;
			exit();
		}
	}
	// get all the spreadsheets
	public function get_spreadsheets() {
		$all_sheets = array();
		try {
			$client = self::getInstance();

			$service = new Google_Service_Drive( $client );

			$optParams = array(
				'q' => "mimeType='application/vnd.google-apps.spreadsheet'",
			);
			$results   = $service->files->listFiles( $optParams );
			foreach ( $results->files as $spreadsheet ) {
				if ( isset( $spreadsheet['kind'] ) && $spreadsheet['kind'] == 'drive#file' ) {
					$all_sheets[] = array(
						'id'    => $spreadsheet['id'],
						'title' => $spreadsheet['name'],
					);
				}
			}
		} catch ( Exception $e ) {
			return null;
			exit();
		}
		return $all_sheets;
	}

	// get worksheets title
	public function get_worktabs( $spreadsheet_id ) {

		$work_tabs_list = array();
		try {
			$client      = self::getInstance();
			$service     = new Google_Service_Sheets( $client );
			$work_sheets = $service->spreadsheets->get( $spreadsheet_id );

			foreach ( $work_sheets as $sheet ) {
				$properties       = $sheet->getProperties();
				$work_tabs_list[] = array(
					'id'    => $properties->getSheetId(),
					'title' => $properties->getTitle(),
				);
			}
		} catch ( Exception $e ) {
			return null;
			exit();
		}

		return $work_tabs_list;
	}


	/**
	 * GFGSC_googlesheet::gsheet_get_google_account
	 * Get Google Account
	 *
	 * @since 3.1
	 * @retun $user
	 **/
	public function gsheet_get_google_account() {

		try {
			$client = $this->getInstance();

			if ( ! $client ) {
				return false;
			}

			$service = new Google_Service_Oauth2( $client );
			$user    = $service->userinfo->get();
		} catch ( Exception $e ) {
			Frm_Gsc_Connector_Utility::gs_debug_log( __METHOD__ . " Error in fetching user info: \n " . $e->getMessage() );
			return false;
		}

		return $user;
	}


	/**
	 * GFGSC_googlesheet::gsheet_get_google_account_email
	 * Get Google Account Email
	 *
	 * @since 3.1
	 * @retun string $email
	 **/
	public function gsheet_get_google_account_email() {
		$google_account = $this->gsheet_get_google_account();

		if ( $google_account ) {
			return $google_account->email;
		} else {
			return '';
		}
	}

	/**
	 * GFGSC_googlesheet::gsheet_print_google_account_email
	 * Get Google Account Email
	 *
	 * @since 3.1
	 * @retun string $google_account
	 **/
	public function gsheet_print_google_account_email_manual() {
		try {
			$google_account = get_option( 'frmgsc_email_account_manual' );
			if ( false && $google_account ) {
				return $google_account;
			} else {

				$google_sheet = new frmgsc_googlesheet();
				$google_sheet->auth();
				$email = $google_sheet->gsheet_get_google_account_email();
				update_option( 'frmgsc_email_account_manual', $email );
				return $email;
			}
		} catch ( Exception $e ) {
			return false;
		}
	}

	public static function revokeToken_auto_manual( $access_code ) {

		if ( $access_code === '' ) {
			return;
		}

		$clientId     = get_option( 'frmgsc_client_id' );
		$clientSecret = get_option( 'frmgsc_secret_id' );
		if ( ! empty( $clientId ) && ! empty( $clientSecret ) ) {
			$client = new Google_Client();
			$client->setClientId( $clientId );
			$client->setClientSecret( $clientSecret );
			$tokendecode = json_decode( $access_code );
			$token       = $tokendecode->access_token;
			$client->revokeToken( $token );
		}
	}
}
