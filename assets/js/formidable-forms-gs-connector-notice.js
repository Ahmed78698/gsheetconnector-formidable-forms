jQuery(document).ready(function () {
   // Upgrade notification scripts
   jQuery('.frmdforms_gs_upgrade_later').click(function () {
      var data = {
         action: 'set_upgrade_notification_interval',
         security: jQuery('#frmdforms_gs_upgrade_ajax_nonce').val()
      };

      jQuery.post(ajaxurl, data, function (response) {
         if (response.success) {
            jQuery('.frmdforms-gs-upgrade').slideUp('slow');
         }
      });
   });
   
   jQuery('.frmdforms_gs_upgrade').click(function () {
      var data = {
         action: 'close_upgrade_notification_interval',
         security: jQuery('#frmdforms_gs_upgrade_ajax_nonce').val()
      };

      jQuery.post(ajaxurl, data, function (response) {
         if (response.success) {
            jQuery('.frmdforms-gs-upgrade').slideUp('slow');
         }
      });
   });
});