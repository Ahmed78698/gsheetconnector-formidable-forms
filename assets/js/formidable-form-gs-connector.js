jQuery(document).ready(function () {
    


/**
 * verify the auth manual api code
 * @since 1.0
 */
jQuery(document).on('click', '#save-gs-frmgsc-manual', function (event) {
    event.preventDefault();
     jQuery('#error_spread').html('');
    var clientId = jQuery('#gs-frmgsc-client-id').val();
    var secretId = jQuery('#gs-frmgsc-secret-id').val();

    if(clientId !="" && secretId !=""){
    jQuery(".loading-sign-frmgsc").addClass("loading");
    var data = {
        action: 'save_client_id_sec_id_gapi_frmgsc',
        client_id: jQuery('#gs-frmgsc-client-id').val(),
        secret_id: jQuery('#gs-frmgsc-secret-id').val(),
        gs_frmgsc_client_token: jQuery('#gs-frmgsc-client-token').val(),
        security: jQuery('#gs-ajax-nonce').val()
    };
    jQuery.post(ajaxurl, data, function (response) {
        if (!response.success) {
            jQuery(".loading-sign-frmgsc").removeClass("loading");
            jQuery("#gs-frmgsc-validation-message").empty();
            jQuery("<span class='error-message'>Access code Can't be blank.</span>").appendTo('#gs-frmgsc-validation-message');
        } else {
            jQuery(".loading-sign").removeClass("loading");
            jQuery("#gs-frmgsc-validation-message").empty();
            jQuery("<span class='frmgsc-valid-message'>Your Google Access Code is Authorized and Saved.</span> ").appendTo('#gs-frmgsc-validation-message');

            setTimeout(function () {
                window.location.href = jQuery("#redirect_auth_frmgsc").val();
            }, 1000);
           
        }
    });
}
else{
  jQuery('#error_spread').html('Please Enter Client ID and Client Secret Value.');
}
});

/**
 * verify the deactivate manual api code
 * @since 1.0
 */
jQuery(document).on('click', '#gs-frmgsc-deactivate-auth', function (event) {
    event.preventDefault();
    jQuery(".loading-sign-frmgsc").addClass("loading");
    var data = {
        action: 'deactivate_auth_token_gapi_frmgsc',
        security: jQuery('#gs-ajax-nonce').val()
    };
    jQuery.post(ajaxurl, data, function (response) {
        if (!response.success) {
            jQuery(".loading-sign-frmgsc").removeClass("loading");
            jQuery("#gs-frmgsc-validation-message").empty();
            
            } else {
            jQuery(".loading-sign-frmgsc").removeClass("loading");
            jQuery("#gs-frmgsc-validation-message").empty();
            jQuery("<span class='frmgsc-valid-message'>Your account is removed. Reauthenticate again to integrate Contact Form with Google Sheet.</span>").appendTo('#gs-frmgsc-validation-message');
            setTimeout(function () {
                location.reload();
            }, 1000);
        }
    });

});
   
    
  /**
     * reset form
     @since 1.0
     */
    jQuery(document).on('click', '#save-frmgsc-reset', function (event) {
        jQuery("#gs-frmgsc-client-id").val('');
        jQuery("#gs-frmgsc-secret-id").val('');
        jQuery("#gs-frmgsc-client-token").val('');
        jQuery("#gs-frmgsc-client-id").removeAttr('disabled');
        jQuery("#gs-frmgsc-secret-id").removeAttr('disabled');
        jQuery("#save-gs-frmgsc-manual").removeAttr('disabled');

    });


	function html_decode(input) {
      var doc = new DOMParser().parseFromString(input, "text/html");
      return doc.documentElement.textContent;
   }

  /**
    * Clear debug for system status tab
    */
   jQuery(document).on('click', '.clear-content-logs-frm', function () {

      jQuery(".clear-loading-sign-logs-frm").addClass("loading");
      var data = {
         action: 'frm_clear_debug_logs',
         security: jQuery('#gs-ajax-nonce').val()
      };
      jQuery.post(ajaxurl, data, function ( response ) {
         if (response == -1) {
            return false; // Invalid nonce
         }
         
         if (response.success) {
            jQuery(".clear-loading-sign-logs-frm").removeClass("loading");
            jQuery('.clear-content-logs-msg-frm').html('Logs are cleared.');
            setTimeout(function () {
                        location.reload();
                    }, 1000);
         }
      });
   });
});
