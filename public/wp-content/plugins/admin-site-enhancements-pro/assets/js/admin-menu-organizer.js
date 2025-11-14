(function( $ ) {
   'use strict';

   $(document).ready( function() {

      // Save changes
      $('#amo-save-changes').click(function(e) {
         e.preventDefault();
         $('.asenha-saving-changes').fadeIn();
         
         var menu_data = {
            'action':'save_admin_menu',
            'nonce': amoPageVars.saveMenuNonce,            
            'custom_menu_order': document.getElementById('custom_menu_order').value,
            'custom_menu_titles': document.getElementById('custom_menu_titles').value,
            'custom_menu_hidden': document.getElementById('custom_menu_hidden').value
         }

         /*! <fs_premium_only> */
         menu_data = {
            'action':'save_admin_menu',
            'nonce': amoPageVars.saveMenuNonce,            
            'custom_menu_order': document.getElementById('custom_menu_order').value,
            'custom_menu_titles': document.getElementById('custom_menu_titles').value,
            'custom_menu_hidden': document.getElementById('custom_menu_hidden').value, // This will be empty
            'custom_submenus_order': document.getElementById('custom_submenus_order').value,
            'custom_menu_always_hidden': document.getElementById('custom_menu_always_hidden').value,
            'custom_submenu_always_hidden': document.getElementById('custom_submenu_always_hidden').value,
            'custom_menu_new_separators': document.getElementById('custom_menu_new_separators').value
         }
         /*! </fs_premium_only> */
         
         $.ajax({
            type: "post",
            url: ajaxurl,
            data: menu_data,
            success:function(data) {
               $('.asenha-saving-changes').hide();
               $('.asenha-changes-saved').fadeIn(400).delay(2500).fadeOut(400);
            },
            error:function(errorThrown) {
               console.log(errorThrown);
            }
         });
      });

      /*! <fs_premium_only> */
      // Reset admin menu via AJAX
      $('#reset-menu').click(function(e) {
         e.preventDefault();
         $('.reset-menu-spinner').show();
         $.ajax({
            type: "post",
            url: ajaxurl,
            data: {
               'action':'reset_admin_menu',
               'nonce': amoPageVars.resetMenuNonce
            },
            success:function(data) {
               var data = data.slice(0,-1); // remove strange trailing zero in string returned by AJAX call
               var response = JSON.parse(data);

               if ( response.status == 'success' ) {
                  document.location.reload(true);
               }
            },
            error:function(errorThrown) {
               console.log(errorThrown);
            }
         });
      });
      /*! </fs_premium_only> */

   }); // END OF $(document).ready()

})( jQuery );