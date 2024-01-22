<?php
/**
 * File Description: This file handles the processing of a specific form submission.
 *
 * @package GSheetConnector for Formidable Forms
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


$gs_frmgsc_client_id = get_option('frmgsc_client_id');
$gs_frmgsc_secret_id = get_option('frmgsc_secret_id');
$gs_frmgsc_manual_code_db = get_option('frmgsc_access_manual_code');


$ffgsc_code = "";
$header = "";
if (isset($_GET['code'])) {
    if(is_string($_GET['code'])){
        $ffgsc_code = sanitize_text_field($_GET['code']);
    }
    update_option('is_new_client_secret_frmgsc', 1);
    $header = esc_url_raw(admin_url('admin.php?page=formidable-form-google-sheet-config'));
}

?>  
<div class="side">  
    <div class="card-wp">
        <span class="ffforms-setting-field log-setting">
            <input type="hidden" name="redirect_auth_frmgsc" id="redirect_auth_frmgsc"
                   value="<?php echo esc_attr(isset($header) ? $header : '' ); ?>">
            <!-- set nonce -->
           <input type="hidden" name="gs-ajax-nonce" id="gs-ajax-nonce"
            value="<?php echo wp_create_nonce('gs-ajax-nonce'); ?>" />
            <h2 class="titles">
                <span class="title1"><?php esc_html_e('Formidable Forms - ', 'gsheetconnector-for-formidable-forms'); ?></span>
                <span class="title"><?php esc_html_e('Google Sheet Integration', 'gsheetconnector-for-formidable-forms'); ?></span>
            </h2>
            <hr>
           <!-- manual api start -->
            <div class="inside">
            <p class="gs-alert">
                <?php echo esc_html(__('Create new google APIs with Client ID and Client Secret keys to get an access for the google drive and google sheets. ', 'gsheetconnector-for-formidable-forms')); ?>
            </p>
           <p id="gs-frmgsc-validation-message" style="color: #2bb723;"></p>
           <span class="error_msg" id="error_spread" style="color: #d63638;"></span>
           <div class="wg_api_option_frmgsc">
                <div class="wg_api_label_frmgsc">
                    <label><?php echo esc_html(__('Client Id', 'gsheetconnector-for-formidable-forms')); ?></label>
                </div>
                <div class="wg_api_input_frmgsc">
                    <input type="text" name="gs-frmgsc-client-id" id="gs-frmgsc-client-id"
                        value="<?php echo esc_attr($gs_frmgsc_client_id); ?>"
                        placeholder="<?php echo esc_html(__('Client ID', 'gsheetconnector-for-formidable-forms')); ?>" />
                </div>
            </div>

            <div class="wg_api_option_frmgsc">
                <div class="wg_api_label_frmgsc">
                    <label><?php echo esc_html(__('Client Secret', 'gsheetconnector-for-formidable-forms')); ?></label>
                </div>
                <div class="wg_api_input_frmgsc">
                    <input type="text" name="gs-frmgsc-secret-id" id="gs-frmgsc-secret-id"
                        value="<?php echo esc_attr($gs_frmgsc_secret_id); ?>"
                        placeholder="<?php echo esc_html(__('Client Secret', 'gsheetconnector-for-formidable-forms')); ?>" />
                </div>
            </div>

            <?php 
             if (!empty(get_option('frmgsc_gs_token_manual')) && get_option('frmgsc_gs_token_manual') !== "") {
                //resolved - google sheet permission issues - START
             if (!empty(get_option('frmgsc_gs_verify')) && (get_option('frmgsc_gs_verify') == "invalid-auth")) {
                    ?>
           <p style="color:#c80d0d; font-size: 14px; border: 1px solid;padding: 8px;">
                <?php 
                  echo esc_html(__('Something went wrong! It looks you have not given the permission of Google Drive and Google Sheets from your google account.Please Deactivate Auth and Re-Authenticate again with the permissions.', 'gsheetconnector-for-formidable-forms'));
                   ?>
            </p>
            <p style="color:#c80d0d;border: 1px solid;padding: 8px;"><img width="350px"
                    src="<?php echo FRMDFORM_GOOGLESHEET_VERSION_URL; ?>assets/img/permission_screen.png"></p>
            <p style="color:#c80d0d; font-size: 14px; border: 1px solid;padding: 8px;">
                <?php echo esc_html(__('Also,', 'gsheetconnector-for-formidable-forms')); ?><a href="https://myaccount.google.com/permissions"
                    target="_blank"> <?php echo esc_html(__('Click Here ', 'gsheetconnector-for-formidable-forms')); ?></a>
                <?php echo esc_html(__('and if it displays "GSheetConnector for WP Contact Forms" under Third-party apps with account access then remove it.', 'gsheetconnector-for-formidable-forms')); ?>
            </p>
            <?php
            }
            //resolved - google sheet permission issues - END
            // connected email account
           else{

                    $google_sheet = new frmgsc_googlesheet();
                    $email_account = $google_sheet->gsheet_print_google_account_email_manual(); 
                   
          if(!empty($email_account) ) { ?>
           <div class="wg_api_option_frmgsc">
                <div class="wg_api_label_frmgsc">
                    <label><?php echo esc_html(__('Connected Email Account', 'gsheetconnector-for-formidable-forms')); ?></label>
                </div>
                <div class="wg_api_input_frmgsc">
                    <p class="connected-account-manual-frmgsc">
                        <?php printf( __( '%s', 'gsheetconnector-for-formidable-forms' ), $email_account ); ?>
                    <p>
                </div>
            </div>
            <?php }else{?>
           <p style="color:red">
                <?php echo esc_html(__('Something wrong ! Your Auth code may be wrong or expired Please Deactivate and Do Re-Auth Code ', 'gsheetconnector-for-formidable-forms')); ?>
            </p>
            <?php 
                      }
                  }
                    }         
                                     
                     $gs_frmgsc_code = "";
                    if(isset($_GET['code']))
                        $gs_frmgsc_code = sanitize_text_field($_GET['code']);
                      
                      ?>
             <?php if($gs_frmgsc_client_id != "" || $gs_frmgsc_secret_id != "" ) {
                          if(!(empty($gs_frmgsc_manual_code_db))){
                              $auth_butt_display = "none";
                              $auth_input_display = "block";
                            }elseif (!empty($gs_frmgsc_code)) {
                              $auth_butt_display = "none";
                              $auth_input_display = "block";
                            }
                            else{
                              $auth_butt_display = "block";
                              $auth_input_display = "none";
                            }
                        ?>

            <div class="wg_api_option_frmgsc">
                 <div class="wg_api_label_frmgsc">
                     <label><?php echo esc_html(__('Client Token', 'gsheetconnector-for-formidable-forms')); ?></label>
                </div>
               <div class="wg_api_input_frmgsc">
                    <input type="text"
                        value="<?php echo (!isset($gs_frmgsc_code) || $gs_frmgsc_code=="") && (isset($gs_frmgsc_manual_code_db) || $gs_frmgsc_manual_code_db!="") ? esc_attr($gs_frmgsc_manual_code_db) : esc_attr($gs_frmgsc_code) ?>"
                        name="gs-frmgsc-client-token" id="gs-frmgsc-client-token" placeholder=""
                        style="display: <?php echo $auth_input_display; ?>" disabled />

                    <?php
                if ( get_option( 'frmgsc_gs_token_manual' ) !== '' ) {  
                    $gsfrmgsc_auth_url = frmgsc_googlesheet::getClient_auth(0, $gs_frmgsc_client_id,$gs_frmgsc_secret_id);
                  ?>
                     <div class="wg_api_option_auth_url_frmgsc" style="display: <?php echo $auth_butt_display; ?>">


                        <a href="<?php echo esc_url($gsfrmgsc_auth_url); ?>" id="authlink_gsfrmgsc" target="_blank">
                            <div class="gsfrmgsc-button-auth gsfrmgsc-button-secondary">
                                <?php echo esc_html__( "Click here to generate an Authentication Token", "gsheetconnector-for-formidable-forms" ); ?>
                            </div>
                        </a>
                    </div>

                    <?php } ?>
                </div>
            </div>

            <input type="button" class="gs-frmgsc-deactivate-auth" name="gs-frmgsc-deactivate-auth"
                id="gs-frmgsc-deactivate-auth" value="Deactivate"
                style="display:<?php echo ($gs_frmgsc_manual_code_db!="") ? "block" : "none"; ?>">
            <?php } ?>

              <div class="wg_api_option_frmgsc">
                 <input type="button" class="gs-frmgsc-save" name="save-gs-frmgsc-manual" id="save-gs-frmgsc-manual" value="Save">
                <?php if(empty($gs_frmgsc_manual_code_db)){ ?>
                 <input type="reset" class="gs-frmgsc-reset" name="save-frmgsc-reset" id="save-frmgsc-reset" value="Reset">
            <?php } ?>
            </div>
             <span class="loading-sign-frmgsc">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
        </div>	
          <!-- manual api end -->	
        </span>
        </span>
    </div>
</div>
