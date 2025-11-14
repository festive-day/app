(function( $ ) {
   'use strict';

   $(document).ready( function() {

      // Make page header sticky on scroll. Using https://github.com/AndrewHenderson/jSticky
      $('#asenha-header').sticky({
         topSpacing: 0, // Space between element and top of the viewport (in pixels)
         zIndex: 100, // z-index
         stopper: '', // Id, class, or number value
         stickyClass: 'asenha-sticky' // Class applied to element when it's stuck. Class name or false.
      })

      // Clicking on header save button triggers click of the hidden form submit button
      $('.asenha-save-button').click( function(e) {

         e.preventDefault();
         
         $('.asenha-saving-changes').fadeIn();

         // Get current tab's URL hash and save it in cookie
         var hash = decodeURI(window.location.hash).substr(1); // get hash without the # character
         Cookies.set('asenha_tab', hash, { expires: 1 }); // expires in 1 day

         // Submit the settings form
         $('input[type="submit"]#asenha-submit').click();

      });

      // Search modules
      var searchInput = $('#module-search-input');
      
      $(searchInput).keyup(delay(function (e) {
         var searchVal = $(this).val();
         var filterItems = $('[data-search-filter]');

         if ( searchVal != '' ) {
            setTimeout(function() {
               $(searchInput).addClass('has-text-input');
               $('.modules-tab').hide();
               $('.search-tab').show();
               $('.asenha-fields.section-visible').addClass('originally-visible');
               $('.asenha-fields').removeClass('section-visible');
               $('.asenha-fields').removeClass('section-hidden');
               $('.asenha-fields').addClass('section-visible-for-search');
               filterItems.parents('.asenha-toggle').addClass('result-is-hidden');
               $('[data-search-filter][data-module-info*="' + searchVal.toLowerCase() + '"]').parents('.asenha-toggle').removeClass('result-is-hidden');
            }, 250 );
            refreshCodeMirror();
         } else {
            setTimeout(function() {
               searchInput.removeClass('has-text-input');
               filterItems.parents('.asenha-toggle').removeClass('result-is-hidden');
               clear_search();
               refreshCodeMirror();
            }, 250 );
         }
      }, 200));

      // Restore all results when the x button on search input field is clicked. 
      // The click triggers a 'search' event we're listening to below

      if ( searchInput.length > 0 ) {
         document.getElementById("module-search-input").addEventListener("search", function(event) {
            clear_search();
            refreshCodeMirror();
         });         
      }
      
      // Ref: https://stackoverflow.com/a/1909508
      function delay(fn, ms) {
         let timer = 0
         return function(...args) {
            clearTimeout(timer)
            timer = setTimeout(fn.bind(this, ...args), ms || 0)
         }
      }
            
      function clear_search() {
            searchInput.removeClass('has-text-input');
            $('[data-search-filter]').each( function() {
               $(this).parents('.asenha-toggle').removeClass('result-is-hidden');
               $('.modules-tab').show();
               $('.search-tab').hide();
               $('.asenha-fields').removeClass('section-visible-for-search');
               $('.asenha-fields').addClass('section-hidden');
               // Has no effect. Compensate with CSS .asenha-fields.section-visible.section-hidden { display: block; }
               // $('.asenha-fields.originally-visible').removeClass('section-hidden'); 
               $('.asenha-fields.originally-visible').addClass('section-visible');
               $('.asenha-fields').removeClass('originally-visible');
            });
      }

      // Show all / less toggler for field options | Modified from https://codepen.io/symonsays/pen/rzgEgY
      $('.asenha-field-with-options.field-show-more > .show-more').click(function(e) {

         e.preventDefault();

         var $this = $(this);
         $this.toggleClass('show-more');

         if ($this.hasClass('show-more')) {
            $this.next().removeClass('opened',0);
            $this.html(adminPageVars.expandText + ' &#9660;');
         } else {
            $this.next().addClass('opened',0);
            $this.html(adminPageVars.collapseText + ' &#9650;');
         }

      });
      
      // Email Delivery >> Send test email
      $('#send-test-email').click(function(e) {
         e.preventDefault();
         var emailTo = $('#test-email-to').val();
         if ( emailTo ) {
            $('#ajax-result').show();
            $('.sending-test-email').show();
            $('.test-email-result').hide();
            $('#test-email-success').hide();
            $('#test-email-failed').hide();
            $.ajax({
               url: ajaxurl,
               data: {
                  'action':'send_test_email',
                  'email_to': emailTo,
                  'nonce': adminPageVars.sendTestEmailNonce
               },
               success:function(data) {
                  var data = data.slice(0,-1); // remove strange trailing zero in string returned by AJAX call
                  var response = JSON.parse(data);
                  if ( response.status == 'success' ) {
                     setTimeout( function() {
                        $('.sending-test-email').hide();
                        // $('.test-email-result').show();
                        $('#test-email-success').show();
                     }, 1500);
                  }
                  if ( response.status == 'failed' ) {
                     setTimeout( function() {
                        $('.sending-test-email').hide();
                        // $('.test-email-result').show();
                        $('#test-email-failed').show();
                     }, 1500);                     
                  }
               },
               error:function(errorThrown) {
                  console.log(errorThrown);
                  setTimeout( function() {
                     $('.sending-test-email').hide();
                     $('.test-email-result').show();
                     $('#test-email-failed').show();
                  }, 1500);
               }
            });
         } else {
            alert( 'Please enter destination email address first.' );
         }
      });

      // Form Builder >> Send test email
      $('#form-builder-send-test-email').click(function(e) {
         e.preventDefault();
         var emailTo = $('#form-builder-test-email-to').val();
         var emailTemplate = $('.form-builder-email-template select').val();
         if ( emailTo ) {
            $('#form-builder-ajax-result').show();
            $('.form-builder-sending-test-email').show();
            $('.test-email-result').hide();
            $('#form-builder-test-email-success').hide();
            $('#form-builder-test-email-failed').hide();
            $.ajax({
               type: 'POST',
               url: ajaxurl,
               data: {
                  action: 'formbuilder_test_email_template',
                  email_template: emailTemplate,
                  test_email: emailTo,
                  nonce: adminPageVars.formBuilderSendTestEmailNonce
               },
               success:function(data) {
                  var response = JSON.parse(data);
                  if ( response.success ) {
                     setTimeout( function() {
                        $('.form-builder-sending-test-email').hide();
                        $('#form-builder-test-email-success').show();
                     }, 1500);
                  }
                  if ( ! response.success ) {
                     setTimeout( function() {
                        $('.form-builder-sending-test-email').hide();
                        $('#form-builder-test-email-failed').show();
                     }, 1500);                     
                  }
               },
               error:function(errorThrown) {
                  console.log(errorThrown);
                  setTimeout( function() {
                     $('.sending-test-email').hide();
                     $('.test-email-result').show();
                     $('#test-email-failed').show();
                  }, 1500);
               }
            });
         } else {
            alert( 'Please enter destination email address first.' );
         }
      });

      // Initialize data tables
      var table = $("#login-attempts-log").DataTable({
         pageLength: 10,
         order: [[2, 'desc']],
         language: {
            emptyTable: adminPageVars.dataTable.emptyTable,
            info: adminPageVars.dataTable.info,
            infoEmpty: adminPageVars.dataTable.infoEmpty,
            infoFiltered: adminPageVars.dataTable.infoFiltered,
            lengthMenu: adminPageVars.dataTable.lengthMenu,
            search: adminPageVars.dataTable.search,
            zeroRecords: adminPageVars.dataTable.zeroRecords,
            paginate: {
                first: adminPageVars.dataTable.paginate.first,
                last: adminPageVars.dataTable.paginate.last,
                next: adminPageVars.dataTable.paginate.next,
                previous: adminPageVars.dataTable.paginate.previous
            },
         }
      });

      // Place fields into the "Content Management" tab
      /*! <fs_premium_only> */
      $('.custom-content-types').appendTo('.fields-content-management > table > tbody');
      $('.custom-field-groups').appendTo('.fields-content-management .custom-content-types .asenha-subfields');
      $('.custom-content-types-description').appendTo('.fields-content-management .custom-content-types .asenha-subfields');
      /*! </fs_premium_only> */
      $('.enable-duplication').appendTo('.fields-content-management > table > tbody');
      $('.duplication-redirect-destination').appendTo('.fields-content-management .enable-duplication .asenha-subfields');
      /*! <fs_premium_only> */
      $('.enable-duplication-link-at').appendTo('.fields-content-management .enable-duplication .asenha-subfields');
      $('.enable-duplication-on-post-types-type').appendTo('.fields-content-management .enable-duplication .asenha-subfields');
      $('.heading-for-public-cpt-duplication').appendTo('.fields-content-management .enable-duplication .asenha-subfields');
      $('.enable-duplication-on-public-post-types').appendTo('.fields-content-management .enable-duplication .asenha-subfields');
      $('.heading-for-nonpublic-cpt-duplication').appendTo('.fields-content-management .enable-duplication .asenha-subfields');
      $('.enable-duplication-on-nonpublic-post-types').appendTo('.fields-content-management .enable-duplication .asenha-subfields');
      $('.heading-for-enable-duplication-for').appendTo('.fields-content-management .enable-duplication .asenha-subfields');
      $('.enable-duplication-for').appendTo('.fields-content-management .enable-duplication .asenha-subfields');
      /*! </fs_premium_only> */
      $('.content-order').appendTo('.fields-content-management > table > tbody');
      // $('.content-order-subfields-heading').appendTo('.fields-content-management .content-order .asenha-subfields');
      $('.content-order-for').appendTo('.fields-content-management .content-order .asenha-subfields');
      /*! <fs_premium_only> */
      $('.content-order-for-non-hierarchical-description').appendTo('.fields-content-management .content-order .asenha-subfields');
      $('.content-order-for-other-post-types').appendTo('.fields-content-management .content-order .asenha-subfields');
      $('.content-order-frontend').appendTo('.fields-content-management .content-order .asenha-subfields');
      /*! </fs_premium_only> */
      /*! <fs_premium_only> */
      $('.terms-order').appendTo('.fields-content-management > table > tbody');
      $('.terms-order-for').appendTo('.fields-content-management .terms-order .asenha-subfields');
      $('.terms-order-frontend').appendTo('.fields-content-management .terms-order .asenha-subfields');
      $('.enable-media-categories').appendTo('.fields-content-management > table > tbody');
      $('.media-categories-side-panel').appendTo('.fields-content-management .enable-media-categories .asenha-subfields');
      /*! </fs_premium_only> */
      $('.enable-media-replacement').appendTo('.fields-content-management > table > tbody');
      $('.disable-media-replacement-cache-busting').appendTo('.fields-content-management .enable-media-replacement .asenha-subfields');
      $('.enable-svg-upload').appendTo('.fields-content-management > table > tbody');
      $('.enable-svg-upload-for').appendTo('.fields-content-management .enable-svg-upload .asenha-subfields');
      $('.enable-avif-upload').appendTo('.fields-content-management > table > tbody');
      $('.avif-support-status').appendTo('.fields-content-management .enable-avif-upload .asenha-subfields');
      /*! <fs_premium_only> */
      $('.public-preview-for-drafts').appendTo('.fields-content-management > table > tbody');
      $('.public-preview-max-days').appendTo('.fields-content-management .public-preview-for-drafts .asenha-subfields');
      $('.public-drafts-preview-for').appendTo('.fields-content-management .public-preview-for-drafts .asenha-subfields');
      /*! </fs_premium_only> */
      $('.enable-external-permalinks').appendTo('.fields-content-management > table > tbody');
      $('.enable-external-permalinks-for').appendTo('.fields-content-management .enable-external-permalinks .asenha-subfields');
      $('.external-links-new-tab').appendTo('.fields-content-management > table > tbody');
      $('.custom-nav-menu-items-new-tab').appendTo('.fields-content-management > table > tbody');
      $('.enable-missed-schedule-posts-auto-publish').appendTo('.fields-content-management > table > tbody');

      // Place fields into "Admin Interface" tab
      $('.hide-modify-elements').appendTo('.fields-admin-interface > table > tbody');
      $('.hide-ab-wp-logo-menu').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.hide-ab-site-menu').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.hide-ab-customize-menu').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.hide-ab-updates-menu').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.hide-ab-comments-menu').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.hide-ab-new-content-menu').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.hide-ab-howdy').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.hide-help-drawer').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      /*! <fs_premium_only> */
      $('.plugins-extra-admin-bar-items').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.disabled-plugins-admin-bar-items').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.plugins-extra-admin-bar-items-description').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      /*! </fs_premium_only> */
      $('.hide-admin-notices').appendTo('.fields-admin-interface > table > tbody');
      /*! <fs_premium_only> */
      $('.hide-admin-notices-for-nonadmins').appendTo('.fields-admin-interface .hide-admin-notices .asenha-subfields');
      $('.hide-admin-notices-menu-for-nonadmins').appendTo('.fields-admin-interface .hide-admin-notices .asenha-subfields');
      /*! </fs_premium_only> */
      $('.disable-dashboard-widgets').appendTo('.fields-admin-interface > table > tbody');
      $('.disable-welcome-panel-in-dashboard').appendTo('.fields-admin-interface .disable-dashboard-widgets .asenha-subfields');
      $('.disabled-dashboard-widgets').appendTo('.fields-admin-interface .disable-dashboard-widgets .asenha-subfields');
      $('.hide-admin-bar').appendTo('.fields-admin-interface > table > tbody');
      /*! <fs_premium_only> */
      $('.heading-for-hide-admin-bar-on-frontend').appendTo('.fields-admin-interface .hide-admin-bar .asenha-subfields');
      /*! </fs_premium_only> */
      $('.hide-admin-bar-for').appendTo('.fields-admin-interface .hide-admin-bar .asenha-subfields');
      $('.hide-admin-bar-always-show-for-admins').appendTo('.fields-admin-interface .hide-admin-bar .asenha-subfields');
      /*! <fs_premium_only> */
      $('.heading-for-hide-admin-bar-on-backend').appendTo('.fields-admin-interface .hide-admin-bar .asenha-subfields');
      $('.hide-admin-bar-on-backend-for').appendTo('.fields-admin-interface .hide-admin-bar .asenha-subfields');
      /*! </fs_premium_only> */
      $('.hide-admin-bar-description').appendTo('.fields-admin-interface .hide-admin-bar .asenha-subfields');
      /*! <fs_premium_only> */
      $('.admin-logo').appendTo('.fields-admin-interface > table > tbody');
      $('.admin-logo-location').appendTo('.fields-admin-interface .admin-logo .asenha-subfields');
      $('.admin-logo-image').appendTo('.fields-admin-interface .admin-logo .asenha-subfields');
      media_frame_init( '#admin-logo-image', '#admin-logo-image-button' );
      $('.admin-logo-link-heading').appendTo('.fields-admin-interface .admin-logo .asenha-subfields');
      $('.admin-logo-link-frontend').appendTo('.fields-admin-interface .admin-logo .asenha-subfields');
      /*! </fs_premium_only> */
      $('.wider-admin-menu').appendTo('.fields-admin-interface > table > tbody');
      $('.admin-menu-width').appendTo('.fields-admin-interface .wider-admin-menu .asenha-subfields');
      $('.customize-admin-menu').appendTo('.fields-admin-interface > table > tbody');
      /*! <fs_premium_only> */
      $('.admin-columns-manager').appendTo('.fields-admin-interface > table > tbody');
      /*! </fs_premium_only> */
      $('.show-custom-taxonomy-filters').appendTo('.fields-admin-interface > table > tbody');
      /*! <fs_premium_only> */
      $('.show-custom-taxonomy-filters-non-hierarchical').appendTo('.fields-admin-interface .show-custom-taxonomy-filters .asenha-subfields');
      /*! </fs_premium_only> */
      $('.enhance-list-tables').appendTo('.fields-admin-interface > table > tbody');
      $('.show-featured-image-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.show-excerpt-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.show-last-modified-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.show-id-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.show-file-size-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.show-id-in-action_row').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.hide-date-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.hide-comments-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.hide-post-tags-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.various-admin-ui-enhancements').appendTo('.fields-admin-interface > table > tbody');
      $('.media-library-infinite-scrolling').appendTo('.fields-admin-interface .various-admin-ui-enhancements .asenha-subfields');
      $('.display-active-plugins-first').appendTo('.fields-admin-interface .various-admin-ui-enhancements .asenha-subfields');
      /*! <fs_premium_only> */
      $('.preserve-taxonomy-hierarchy').appendTo('.fields-admin-interface .various-admin-ui-enhancements .asenha-subfields');
      $('.enable-dashboard-columns-settings').appendTo('.fields-admin-interface .various-admin-ui-enhancements .asenha-subfields');
      $('.add-user-roles-to-admin-body-classes').appendTo('.fields-admin-interface .various-admin-ui-enhancements .asenha-subfields');
      $('.add-username-to-admin-body-classes').appendTo('.fields-admin-interface .various-admin-ui-enhancements .asenha-subfields');
      $('.open-admin-links-in-new-tab').appendTo('.fields-admin-interface .various-admin-ui-enhancements .asenha-subfields');
      /*! </fs_premium_only> */
      $('.custom-admin-footer-text').appendTo('.fields-admin-interface > table > tbody');
      $('.custom-admin-footer-left').appendTo('.fields-admin-interface .custom-admin-footer-text .asenha-subfields');
      reinitWpEditor('admin_site_enhancements--custom_admin_footer_left');
      $('.custom-admin-footer-right').appendTo('.fields-admin-interface .custom-admin-footer-text .asenha-subfields');
      reinitWpEditor('admin_site_enhancements--custom_admin_footer_right');

      // Place fields into "Log In | Log Out" tab
      $('.change-login-url').appendTo('.fields-login-logout > table > tbody');
      $('.custom-login-slug').appendTo('.fields-login-logout .change-login-url .asenha-subfields');
      $('.custom-login-whitelist').appendTo('.fields-login-logout .change-login-url .asenha-subfields');
      $('.default-login-redirect-slug').appendTo('.fields-login-logout .change-login-url .asenha-subfields');
      $('.change-login-url-description').appendTo('.fields-login-logout .change-login-url .asenha-subfields');
      $('.login-id-type-restriction').appendTo('.fields-login-logout > table > tbody');
      $('.login-id-type').appendTo('.fields-login-logout .login-id-type-restriction .asenha-subfields');
      /*! <fs_premium_only> */
      $('.login-page-customizer').appendTo('.fields-login-logout > table > tbody');
      $('.login-page-form-position').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-form-color-scheme').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-form-color-scheme-custom').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-form-section-color-bg').appendTo('.login-page-form-color-scheme-custom');
      $('#login-page-form-section-color-bg.color-picker').wpColorPicker();
      $('.login-page-form-section-color-transparency').appendTo('.login-page-form-color-scheme-custom');
      $('.login-page-logo-image-type').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-logo-image').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      media_frame_init( '#login-page-logo-image', '#login-page-logo-image-button' );
      $('.login-page-logo-image-external').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-logo-image-external-description').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-logo-image-attachment-id').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-logo-image-width-original').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-logo-image-height-original').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-logo-image-size').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-logo-image-width').appendTo('.login-page-logo-image-size');
      $('.login-page-logo-image-height').appendTo('.login-page-logo-image-size');
      $('.login-page-logo-image-width-external').appendTo('.login-page-logo-image-size');
      $('.login-page-logo-image-height-external').appendTo('.login-page-logo-image-size');
      $('.login-page-logo-image-description').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-logo-site-icon-description').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-background').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-background-image').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-background-pattern').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      media_frame_init( '#login-page-background-image', '#login-page-background-image-button' );
      $('.login-page-background-color').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('#login-page-background-color.color-picker').wpColorPicker();
      $('.login-page-login-button-color').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('#login-page-login-button-color.color-picker').wpColorPicker();
      // $('.login-page-disable-registration').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-hide-elements').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-hide-remember-me').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-hide-registration-reset').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-hide-homepage-link').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-hide-language-switcher').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-external-css').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      $('.login-page-custom-css').appendTo('.fields-login-logout .login-page-customizer .asenha-subfields');
      /*! </fs_premium_only> */
      $('.site-identity-on-login').appendTo('.fields-login-logout > table > tbody');
      $('.enable-login-logout-menu').appendTo('.fields-login-logout > table > tbody');
      /*! <fs_premium_only> */
      $('.custom-login-menu-label').appendTo('.fields-login-logout .enable-login-logout-menu .asenha-subfields');
      $('.custom-logout-menu-label').appendTo('.fields-login-logout .enable-login-logout-menu .asenha-subfields');
      $('.custom-login-logout-label-description').appendTo('.fields-login-logout .enable-login-logout-menu .asenha-subfields');
      /*! </fs_premium_only> */
      $('.enable-last-login-column').appendTo('.fields-login-logout > table > tbody');
      $('.registration-date-column').appendTo('.fields-login-logout > table > tbody');
      $('.redirect-after-login').appendTo('.fields-login-logout > table > tbody');
      /*! <fs_premium_only> */
      $('.redirect-after-login-type').appendTo('.fields-login-logout .redirect-after-login .asenha-subfields');
      /*! </fs_premium_only> */
      $('.redirect-after-login-to-slug').appendTo('.fields-login-logout .redirect-after-login .asenha-subfields');
      $('.redirect-after-login-for').appendTo('.fields-login-logout .redirect-after-login .asenha-subfields');
      /*! <fs_premium_only> */
      $('.redirect-after-login-for-separate').appendTo('.fields-login-logout .redirect-after-login .asenha-subfields');
      /*! </fs_premium_only> */
      $('.redirect-after-logout').appendTo('.fields-login-logout > table > tbody');
      /*! <fs_premium_only> */
      $('.redirect-after-logout-type').appendTo('.fields-login-logout .redirect-after-logout .asenha-subfields');
      /*! </fs_premium_only> */
      $('.redirect-after-logout-to-slug').appendTo('.fields-login-logout .redirect-after-logout .asenha-subfields');
      $('.redirect-after-logout-for').appendTo('.fields-login-logout .redirect-after-logout .asenha-subfields');
      /*! <fs_premium_only> */
      $('.redirect-after-logout-for-separate').appendTo('.fields-login-logout .redirect-after-logout .asenha-subfields');
      /*! </fs_premium_only> */

      // Place fields into "Custom Code" tab
      /*! <fs_premium_only> */
      $('.enable-code-snippets-manager').appendTo('.fields-custom-code > table > tbody');
      $('.code-snippets-manager-description').appendTo('.fields-custom-code .enable-code-snippets-manager .asenha-subfields');
      $('.code-snippets-editor-theme').appendTo('.fields-custom-code .enable-code-snippets-manager .asenha-subfields');
      /*! </fs_premium_only> */
      $('.enable-custom-admin-css').appendTo('.fields-custom-code > table > tbody');
      $('.custom-admin-css').appendTo('.fields-custom-code .enable-custom-admin-css .asenha-subfields');
      $('.enable-custom-frontend-css').appendTo('.fields-custom-code > table > tbody');
      /*! <fs_premium_only> */
      $('.custom-frontend-css-priority').appendTo('.fields-custom-code .enable-custom-frontend-css .asenha-subfields');
      /*! </fs_premium_only> */
      $('.custom-frontend-css').appendTo('.fields-custom-code .enable-custom-frontend-css .asenha-subfields');
      $('.insert-head-body-footer-code').appendTo('.fields-custom-code > table > tbody');
      $('.disable-code-unslash').appendTo('.fields-custom-code .insert-head-body-footer-code .asenha-subfields');
      $('.head-code-priority').appendTo('.fields-custom-code .insert-head-body-footer-code .asenha-subfields');
      $('.head-code').appendTo('.fields-custom-code .insert-head-body-footer-code .asenha-subfields');
      $('.body-code-priority').appendTo('.fields-custom-code .insert-head-body-footer-code .asenha-subfields');
      $('.body-code').appendTo('.fields-custom-code .insert-head-body-footer-code .asenha-subfields');
      $('.footer-code-priority').appendTo('.fields-custom-code .insert-head-body-footer-code .asenha-subfields');
      $('.footer-code').appendTo('.fields-custom-code .insert-head-body-footer-code .asenha-subfields');
      $('.enable-custom-body-class').appendTo('.fields-custom-code > table > tbody');
      $('.enable-custom-body-class-for').appendTo('.fields-custom-code .enable-custom-body-class .asenha-subfields');
      $('.manage-ads-appads-txt').appendTo('.fields-custom-code > table > tbody');
      $('.ads-txt-content').appendTo('.fields-custom-code .manage-ads-appads-txt .asenha-subfields');
      $('.app-ads-txt-content').appendTo('.fields-custom-code .manage-ads-appads-txt .asenha-subfields');
      $('.manage-robots-txt').appendTo('.fields-custom-code > table > tbody');
      $('.robots-txt-content').appendTo('.fields-custom-code .manage-robots-txt .asenha-subfields');

      // Place fields into the "Disable Components" tab
      $('.disable-gutenberg').appendTo('.fields-disable-components > table > tbody');
      /*! <fs_premium_only> */
      $('.disable-gutenberg-type').appendTo('.fields-disable-components .disable-gutenberg .asenha-subfields');
      /*! </fs_premium_only> */
      $('.disable-gutenberg-for').appendTo('.fields-disable-components .disable-gutenberg .asenha-subfields');
      $('.disable-gutenberg-frontend-styles').appendTo('.fields-disable-components .disable-gutenberg .asenha-subfields');
      $('.disable-comments').appendTo('.fields-disable-components > table > tbody');
      /*! <fs_premium_only> */
      $('.disable-comments-type').appendTo('.fields-disable-components .disable-comments .asenha-subfields');
      /*! </fs_premium_only> */
      $('.disable-comments-for').appendTo('.fields-disable-components .disable-comments .asenha-subfields');
      $('.disable-rest-api').appendTo('.fields-disable-components > table > tbody');
      /*! <fs_premium_only> */
      $('.disable-rest-api-excluded-routes').appendTo('.fields-disable-components .disable-rest-api .asenha-subfields');
      $('.heading-for-enable-rest-api-for').appendTo('.fields-disable-components .disable-rest-api .asenha-subfields');
      $('.enable-rest-api-for').appendTo('.fields-disable-components .disable-rest-api .asenha-subfields');
      /*! </fs_premium_only> */
      $('.disable-feeds').appendTo('.fields-disable-components > table > tbody');
      $('.disable-embeds').appendTo('.fields-disable-components > table > tbody');
      $('.disable-all-updates').appendTo('.fields-disable-components > table > tbody');
      $('.disable-author-archives').appendTo('.fields-disable-components > table > tbody');
      $('.disable-smaller-components').appendTo('.fields-disable-components > table > tbody');
      $('.disable-head-generator-tag').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-feed-generator-tag').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-resource-version-number').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-head-wlwmanifest-tag').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-head-rsd-tag').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-head-shortlink-tag').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-frontend-dashicons').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-emoji-support').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-jquery-migrate').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-block-widgets').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-lazy-load').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-application-passwords').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-plugin-theme-editor').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');

      // Place fields into "Security" tab
      $('.limit-login-attempts').appendTo('.fields-security > table > tbody');
      $('.login-fails-allowed').appendTo('.fields-security .limit-login-attempts .asenha-subfields');
      $('.login-lockout-maxcount').appendTo('.fields-security .limit-login-attempts .asenha-subfields');
      /*! <fs_premium_only> */
      $('.limit-login-attempts-ip-whitelist').appendTo('.fields-security .limit-login-attempts .asenha-subfields');
      /*! </fs_premium_only> */
      $('.limit-login-attempts-header-override').appendTo('.fields-security .limit-login-attempts .asenha-subfields');
      $('.limit-login-attempts-header-override-description').appendTo('.fields-security .limit-login-attempts .asenha-subfields');
      $('.login-attempts-log-table').appendTo('.fields-security .limit-login-attempts .asenha-subfields');
      /*! <fs_premium_only> */
      $('.captcha-protection').appendTo('.fields-security > table > tbody');
      $('.captcha-wp-locations').appendTo('.fields-security .captcha-protection .asenha-subfields');
      $('.captcha-woo-locations').appendTo('.fields-security .captcha-protection .asenha-subfields');
      $('.captcha-protection-types').appendTo('.fields-security .captcha-protection .asenha-subfields');
      $('.captcha-protection-altcha-wrapper').appendTo('.fields-security .captcha-protection .asenha-subfields');
      $('.captcha-protection-recaptcha-wrapper').appendTo('.fields-security .captcha-protection .asenha-subfields');
      $('.captcha-protection-turnstile-wrapper').appendTo('.fields-security .captcha-protection .asenha-subfields');
      $('.altcha-widget').appendTo('.captcha-protection-altcha-wrapper');
      $('.altcha-secret-key').appendTo('.captcha-protection-altcha-wrapper');
      $('.altcha-complexity').appendTo('.captcha-protection-altcha-wrapper');
      $('.altcha-expiration').appendTo('.captcha-protection-altcha-wrapper');
      $('.altcha-auto-verification').appendTo('.captcha-protection-altcha-wrapper');
      $('.altcha-enable-delay').appendTo('.captcha-protection-altcha-wrapper');
      $('.altcha-hide-logo').appendTo('.captcha-protection-altcha-wrapper');
      $('.altcha-hide-byline').appendTo('.captcha-protection-altcha-wrapper');
      $('.altcha-advanced-wrapper').appendTo('.captcha-protection-altcha-wrapper');
      $('.altcha-advanced-toggler').appendTo('.captcha-protection-altcha-wrapper');
      $('.altcha-checkbox-label').appendTo('.altcha-advanced-wrapper');
      $('.altcha-verifying-text').appendTo('.altcha-advanced-wrapper');
      $('.altcha-verifying-wait-text').appendTo('.altcha-advanced-wrapper');
      $('.altcha-verified-text').appendTo('.altcha-advanced-wrapper');
      $('.altcha-verification-failed-text').appendTo('.altcha-advanced-wrapper');
      $('.recaptcha-widget').appendTo('.captcha-protection-recaptcha-wrapper');
      $('.recaptcha-site-key-v2-checkbox').appendTo('.captcha-protection-recaptcha-wrapper');
      $('.recaptcha-secret-key-v2-checkbox').appendTo('.captcha-protection-recaptcha-wrapper');
      $('.recaptcha-site-key-v3-invisible').appendTo('.captcha-protection-recaptcha-wrapper');
      $('.recaptcha-secret-key-v3-invisible').appendTo('.captcha-protection-recaptcha-wrapper');
      $('.recaptcha-setup-instruction').appendTo('.captcha-protection-recaptcha-wrapper');
      $('.turnstile-widget-theme').appendTo('.captcha-protection-turnstile-wrapper');
      $('.turnstile-site-key').appendTo('.captcha-protection-turnstile-wrapper');
      $('.turnstile-secret-key').appendTo('.captcha-protection-turnstile-wrapper');
      /*! </fs_premium_only> */
      $('.obfuscate-author-slugs').appendTo('.fields-security > table > tbody');
      $('.obfuscate-email-address').appendTo('.fields-security > table > tbody');
      $('.obfuscate-email-address-description').appendTo('.fields-security .obfuscate-email-address .asenha-subfields');
      /*! <fs_premium_only> */
      $('.obfuscate-email-address-in-content').appendTo('.fields-security .obfuscate-email-address .asenha-subfields');
      $('.obfuscate-email-address-visitor-only').appendTo('.fields-security .obfuscate-email-address .asenha-subfields');
      /*! </fs_premium_only> */
      $('.disable-xmlrpc').appendTo('.fields-security > table > tbody');

      // Place fields into "Optimizations" tab
      $('.image-upload-control').appendTo('.fields-optimizations > table > tbody');
      $('.image-max-width').appendTo('.fields-optimizations .image-upload-control .asenha-subfields');
      $('.image-max-height').appendTo('.fields-optimizations .image-upload-control .asenha-subfields');
      /*! <fs_premium_only> */
      $('.image-conversion-wrapper').appendTo('.fields-optimizations .image-upload-control .asenha-subfields');
      $('.convert-to-jpg-quality').appendTo('.image-conversion-wrapper');
      $('.convert-to-webp').appendTo('.image-conversion-wrapper');
      $('.convert-to-webp-quality').appendTo('.image-conversion-wrapper');

      // $('.keep-original-image').appendTo('.fields-optimizations .image-upload-control .asenha-subfields');
      /*! </fs_premium_only> */
      $('.image-upload-control-description').appendTo('.fields-optimizations .image-upload-control .asenha-subfields');
      /*! <fs_premium_only> */
      $('.disable-image-conversion').appendTo('.fields-optimizations .image-upload-control .asenha-subfields');
      $('.heading-for-disabled-image-sizes').appendTo('.fields-optimizations .image-upload-control .asenha-subfields');
      $('.disabled-image-sizes').appendTo('.fields-optimizations .image-upload-control .asenha-subfields');
      /*! </fs_premium_only> */
      $('.enable-revisions-control').appendTo('.fields-optimizations > table > tbody');
      $('.revisions-max-number').appendTo('.fields-optimizations .enable-revisions-control .asenha-subfields');
      $('.enable-revisions-control-for').appendTo('.fields-optimizations .enable-revisions-control .asenha-subfields');
      $('.enable-heartbeat-control').appendTo('.fields-optimizations > table > tbody');
      $('.heartbeat-control-for-admin-pages').appendTo('.fields-optimizations .enable-heartbeat-control .asenha-subfields');
      $('.heartbeat-interval-for-admin-pages').appendTo('.fields-optimizations .enable-heartbeat-control .asenha-subfields');
      $('.heartbeat-control-for-post-edit').appendTo('.fields-optimizations .enable-heartbeat-control .asenha-subfields');
      $('.heartbeat-interval-for-post-edit').appendTo('.fields-optimizations .enable-heartbeat-control .asenha-subfields');
      $('.heartbeat-control-for-frontend').appendTo('.fields-optimizations .enable-heartbeat-control .asenha-subfields');
      $('.heartbeat-interval-for-frontend').appendTo('.fields-optimizations .enable-heartbeat-control .asenha-subfields');

      // Place fields into "Utilities" tab
      $('.smtp-email-delivery').appendTo('.fields-utilities > table > tbody');
      $('.smtp-default-from-description').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-default-from-name').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-default-from-email').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-force-from').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      /*! <fs_premium_only> */
      $('.smtp-replyto-name').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-replyto-email').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-bcc-emails').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      /*! </fs_premium_only> */
      $('.smtp--description').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-host').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-port').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-security').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      /*! <fs_premium_only> */
      $('.smtp-authentication').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      /*! </fs_premium_only> */
      $('.smtp-username').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-password').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-bypass-ssl-verification').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-debug').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      /*! <fs_premium_only> */
      $('.smtp-email-log').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-email-log-entries-amount-to-keep').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      /*! </fs_premium_only> */
      $('.smtp-send-test-email-description').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-send-test-email-to').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-send-test-email-result').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      /*! <fs_premium_only> */
      $('.form-builder').appendTo('.fields-utilities > table > tbody');
      $('.form-builder-email-delivery-description').appendTo('.fields-utilities .form-builder .asenha-subfields');
      $('.form-builder-email-template').appendTo('.fields-utilities .form-builder .asenha-subfields');
      $('.form-builder-email-header-image').appendTo('.fields-utilities .form-builder .asenha-subfields');
      media_frame_init( '#form-builder-email-header-image', '#form-builder-email-header-image-button' );
      $('.form-builder-email-header-image-attachment-id').appendTo('.fields-utilities .form-builder .asenha-subfields');
      $('.form-builder-send-test-email-to').appendTo('.fields-utilities .form-builder .asenha-subfields');
      $('.form-builder-send-test-email-result').appendTo('.fields-utilities .form-builder .asenha-subfields');
      $('.form-builder-email-custom-css').appendTo('.fields-utilities .form-builder .asenha-subfields');
      $('.form-builder-captcha-description').appendTo('.fields-utilities .form-builder .asenha-subfields');
      $('.form-builder-captcha-info-wrapper').appendTo('.fields-utilities .form-builder .asenha-subfields');
      $('.form-builder-captcha-info-toggler').appendTo('.fields-utilities .form-builder .asenha-subfields');
      $('.form-builder-altcha-secret-key').appendTo('.form-builder-captcha-info-wrapper');
      $('.form-builder-recaptcha-site-key-v2-checkbox').appendTo('.form-builder-captcha-info-wrapper');
      $('.form-builder-recaptcha-secret-key-v2-checkbox').appendTo('.form-builder-captcha-info-wrapper');
      $('.form-builder-recaptcha-site-key-v3-invisible').appendTo('.form-builder-captcha-info-wrapper');
      $('.form-builder-recaptcha-secret-key-v3-invisible').appendTo('.form-builder-captcha-info-wrapper');
      $('.form-builder-turnstile-site-key').appendTo('.form-builder-captcha-info-wrapper');
      $('.form-builder-turnstile-secret-key').appendTo('.form-builder-captcha-info-wrapper');
      $('.local-user-avatar').appendTo('.fields-utilities > table > tbody');
      /*! </fs_premium_only> */
      $('.multiple-user-roles').appendTo('.fields-utilities > table > tbody');
      $('.image-sizes-panel').appendTo('.fields-utilities > table > tbody');
      $('.view-admin-as-role').appendTo('.fields-utilities > table > tbody');
      $('.view-admin-as-role-description').appendTo('.fields-utilities .view-admin-as-role .asenha-subfields');
      $('.enable-password-protection').appendTo('.fields-utilities > table > tbody');
      $('.password-protection-password').appendTo('.fields-utilities .enable-password-protection .asenha-subfields');
      /*! <fs_premium_only> */
      $('.password-protection-ip-whitelist').appendTo('.fields-utilities .enable-password-protection .asenha-subfields');
      $('.password-protection-header-override').appendTo('.fields-utilities .enable-password-protection .asenha-subfields');
      $('.password-protection-header-override-description').appendTo('.fields-utilities .enable-password-protection .asenha-subfields');
      $('.password-protection-description-url-parameter').appendTo('.fields-utilities .enable-password-protection .asenha-subfields');
      $('.password-protection-description-design').appendTo('.fields-utilities .enable-password-protection .asenha-subfields');
      $('.password-protection-advanced-wrapper').appendTo('.fields-utilities .enable-password-protection .asenha-subfields');
      $('.password-protection-advanced-toggler').appendTo('.fields-utilities .enable-password-protection .asenha-subfields');
      $('.password-protection-password-field-label').appendTo('.password-protection-advanced-wrapper');
      $('.password-protection-button-label').appendTo('.password-protection-advanced-wrapper');
      /*! </fs_premium_only> */
      $('.maintenance-mode').appendTo('.fields-utilities > table > tbody');
      /*! <fs_premium_only> */
      $('.maintenance-page-type').appendTo('.fields-utilities .maintenance-mode .asenha-subfields');
      /*! </fs_premium_only> */
      $('.maintenance-page-type-custom').appendTo('.fields-utilities .maintenance-mode .asenha-subfields');
      /*! <fs_premium_only> */
      $('.maintenance-page-title').appendTo('.maintenance-page-type-custom');
      /*! </fs_premium_only> */
      $('.maintenance-page-heading').appendTo('.maintenance-page-type-custom');
      $('.maintenance-page-description').appendTo('.maintenance-page-type-custom');
      /*! <fs_premium_only> */
      reinitWpEditor('admin_site_enhancements--maintenance_page_heading');
      reinitWpEditor('admin_site_enhancements--maintenance_page_description');
      /*! </fs_premium_only> */
      $('.maintenance-page-background').appendTo('.maintenance-page-type-custom');
      /*! <fs_premium_only> */
      $('.maintenance-page-background-pattern').appendTo('.maintenance-page-type-custom');
      $('.maintenance-page-background-image').appendTo('.maintenance-page-type-custom');
      media_frame_init( '#maintenance-page-background-image', '#maintenance-page-background-image-button' );
      $('.maintenance-page-background-color').appendTo('.maintenance-page-type-custom');
      $('#maintenance-page-background-color.color-picker').wpColorPicker();
      $('.maintenance-page-head-code').appendTo('.maintenance-page-type-custom');
      $('.maintenance-page-custom-css').appendTo('.maintenance-page-type-custom');
      $('.maintenance-page-slug').appendTo('.fields-utilities .maintenance-mode .asenha-subfields');
      $('.maintenance-page-exclude-urls').appendTo('.fields-utilities .maintenance-mode .asenha-subfields');
      $('.heading-for-maintenance-mode-access-for').appendTo('.fields-utilities .maintenance-mode .asenha-subfields');
      $('.maintenance-mode-access-for').appendTo('.fields-utilities .maintenance-mode .asenha-subfields');
      /*! </fs_premium_only> */
      $('.maintenance-mode-description').appendTo('.fields-utilities .maintenance-mode .asenha-subfields');
      $('.redirect-404-to-homepage').appendTo('.fields-utilities > table > tbody');
      /*! <fs_premium_only> */
      $('.redirect-404-to-slug').appendTo('.fields-utilities .redirect-404-to-homepage .asenha-subfields');
      /*! </fs_premium_only> */
      $('.display-system-summary').appendTo('.fields-utilities > table > tbody');
      $('.search-engine-visibility-status').appendTo('.fields-utilities > table > tbody');
      /*! <fs_premium_only> */
      $('.live-site-url').appendTo('.fields-utilities .search-engine-visibility-status .asenha-subfields');
      $('.live-site-url-description').appendTo('.fields-utilities .search-engine-visibility-status .asenha-subfields');
      /*! </fs_premium_only> */

      // Remove empty .form-table that originally holds the fields
      const formTableCount = $('.form-table').length;
      // $('.form-table')[formTableCount-1].remove();

      // Enable Custom Admin CSS => Initialize CodeMirror
      var adminCssTextarea = document.getElementById("admin_site_enhancements[custom_admin_css]");
      // if ( typeof CodeMirror != "undefined" ) {
      //    alert('CodeMirror is available');
      // }
      var adminCssEditor = CodeMirror.fromTextArea(adminCssTextarea, {
         mode: "css",
         lineNumbers: true,
         lineWrapping: true
      });

      adminCssEditor.setSize("100%",600);

      // Enable Custom Frontend CSS => Initialize CodeMirror
      var frontendCssTextarea = document.getElementById("admin_site_enhancements[custom_frontend_css]");
      var frontendCssEditor = CodeMirror.fromTextArea(frontendCssTextarea, {
         mode: "css",
         lineNumbers: true,
         lineWrapping: true
      });

      frontendCssEditor.setSize("100%",600);

      // Manage ads.txt and app-ads.txt=> Initialize CodeMirror
      var adsTxtTextarea = document.getElementById("admin_site_enhancements[ads_txt_content]");
      var adsTxtEditor = CodeMirror.fromTextArea(adsTxtTextarea, {
         mode: "markdown",
         lineNumbers: true,
         lineWrapping: true
      });

      adsTxtEditor.setSize("100%",300);

      var appAdsTxtTextarea = document.getElementById("admin_site_enhancements[app_ads_txt_content]");
      var appAdsTxtEditor = CodeMirror.fromTextArea(appAdsTxtTextarea, {
         mode: "markdown",
         lineNumbers: true,
         lineWrapping: true
      });

      appAdsTxtEditor.setSize("100%",300);

      // Manage robots.txt => Initialize CodeMirror
      var robotsTxtTextarea = document.getElementById("admin_site_enhancements[robots_txt_content]");
      var robotsTxtEditor = CodeMirror.fromTextArea(robotsTxtTextarea, {
         mode: "markdown",
         lineNumbers: true,
         lineWrapping: true
      });

      robotsTxtEditor.setSize("100%",400);

      // Insert <head>, <body> and <footer> code => Initialize CodeMirror
      var headCodeTextarea = document.getElementById("admin_site_enhancements[head_code]");
      var headCodeEditor = CodeMirror.fromTextArea(headCodeTextarea, {
         mode: "htmlmixed",
         lineNumbers: true,
         lineWrapping: true
      });
      headCodeEditor.setSize("100%",300);

      var bodyCodeTextarea = document.getElementById("admin_site_enhancements[body_code]");
      var bodyCodeEditor = CodeMirror.fromTextArea(bodyCodeTextarea, {
         mode: "htmlmixed",
         lineNumbers: true,
         lineWrapping: true
      });
      bodyCodeEditor.setSize("100%",300);

      var footerCodeTextarea = document.getElementById("admin_site_enhancements[footer_code]");
      var footerCodeEditor = CodeMirror.fromTextArea(footerCodeTextarea, {
         mode: "htmlmixed",
         lineNumbers: true,
         lineWrapping: true
      });
      footerCodeEditor.setSize("100%",300);

      /*! <fs_premium_only> */
      // Enable Login Page Custom CSS => Initialize CodeMirror
      var loginPageCssTextarea = document.getElementById("admin_site_enhancements[login_page_custom_css]");
      if ( loginPageCssTextarea ) {
         var loginPageCssEditor = CodeMirror.fromTextArea(loginPageCssTextarea, {
            mode: "css",
            lineNumbers: true,
            lineWrapping: true
         });
         loginPageCssEditor.setSize("100%",300);         
      }

      // Enable Form Builder Email Custom CSS => Initialize CodeMirror
      var formBuilderEmailCssTextarea = document.getElementById("admin_site_enhancements[form_builder_email_custom_css]");
      if ( formBuilderEmailCssTextarea ) {
         var formBuilderEmailCssEditor = CodeMirror.fromTextArea(formBuilderEmailCssTextarea, {
            mode: "css",
            lineNumbers: true,
            lineWrapping: true
         });
         formBuilderEmailCssEditor.setSize("100%",300);         
      }

      // Enable Maintenance Page Custom <head> code => Initialize CodeMirror
      var maintenancePageHeadCodeTextarea = document.getElementById("admin_site_enhancements[maintenance_page_head_code]");
      if ( maintenancePageHeadCodeTextarea ) {
         var maintenancePageHeadCodeEditor = CodeMirror.fromTextArea(maintenancePageHeadCodeTextarea, {
            mode: "htmlmixed",
            lineNumbers: true,
            lineWrapping: true
         });
         maintenancePageHeadCodeEditor.setSize("100%",150);         
      }

      // Enable Maintenance Page Custom CSS => Initialize CodeMirror
      var maintenanceModeCssTextarea = document.getElementById("admin_site_enhancements[maintenance_page_custom_css]");
      if ( maintenanceModeCssTextarea ) {
         var maintenanceModeCssEditor = CodeMirror.fromTextArea(maintenanceModeCssTextarea, {
            mode: "css",
            lineNumbers: true,
            lineWrapping: true
         });
         maintenanceModeCssEditor.setSize("100%",300);         
      }
      /*! </fs_premium_only> */

      function refreshCodeMirror() {
         /*! <fs_premium_only> */
         if ( loginPageCssTextarea ) {
            loginPageCssEditor.refresh(); // Maintenance page >> CodeMirror
         }
         /*! </fs_premium_only> */
         adminCssEditor.refresh(); // Custom Admin CSS >> CodeMirror
         frontendCssEditor.refresh(); // Custom Fronend CSS >> CodeMirror
         adsTxtEditor.refresh(); // Manage ads.txt >> CodeMirror
         appAdsTxtEditor.refresh(); // Manage app-ads.txt >> CodeMirror
         headCodeEditor.refresh(); // Insert <head>, <body> and <footer> code >> CodeMirror
         bodyCodeEditor.refresh(); // Insert <head>, <body> and <footer> code >> CodeMirror
         footerCodeEditor.refresh(); // Insert <head>, <body> and <footer> code >> CodeMirror
         robotsTxtEditor.refresh(); // Manage robots.txt >> CodeMirror
         /*! <fs_premium_only> */
         if ( formBuilderEmailCssTextarea ) {
            formBuilderEmailCssEditor.refresh(); // Form Builder Email >> CodeMirror
         }
         if ( maintenancePageHeadCodeTextarea ) {
            maintenancePageHeadCodeEditor.refresh(); // Maintenance page >> CodeMirror
         }
         if ( maintenanceModeCssTextarea ) {
            maintenanceModeCssEditor.refresh(); // Maintenance page >> CodeMirror
         }
         /*! </fs_premium_only> */         
      }

      // Show and hide corresponding fields on tab clicks

      function tabSwitcher( tabSlug ) {
         $('.asenha-fields.fields-'+tabSlug).addClass('section-visible');
         $('.asenha-fields.fields-'+tabSlug).removeClass('section-hidden');
         $('.asenha-fields:not(.fields-'+tabSlug+')').removeClass('section-visible');
         $('.asenha-fields:not(.fields-'+tabSlug+')').addClass('section-hidden');
         window.location.hash = tabSlug;
         Cookies.set('asenha_tab', tabSlug, { expires: 1 }); // expires in 1 day
      }

      $('#tab-content-management + label').click( function() {
         tabSwitcher('content-management');
      });

      $('#tab-admin-interface + label').click( function() {
         tabSwitcher('admin-interface');
      });

      $('#tab-login-logout + label').click( function() {
         tabSwitcher('login-logout');
         refreshCodeMirror();
      });

      $('#tab-custom-code + label').click( function() {
         tabSwitcher('custom-code');
         refreshCodeMirror();
      });

      $('#tab-disable-components + label').click( function() {
         tabSwitcher('disable-components');
      });

      $('#tab-security + label').click( function() {
         tabSwitcher('security');
      });

      $('#tab-optimizations + label').click( function() {
         tabSwitcher('optimizations');
      });

      $('#tab-utilities + label').click( function() {
         tabSwitcher('utilities');
         refreshCodeMirror();
      });

      // Open tab set in 'asenha_tab' cookie set on saving changes. Defaults to content-management tab when cookie is empty
      var asenhaTabHash = Cookies.get('asenha_tab');

      if (typeof asenhaTabHash === 'undefined') {
         $('#tab-content-management + label').trigger('click');
      } else {
         $('#tab-' + asenhaTabHash + ' + label').trigger('click');
      }
      
      // Show or hide subfields on document ready and on toggle click

      function subfieldsToggler( fieldId, fieldClass, sortableId, codeMirrorInstances ) {

         if (document.getElementById('admin_site_enhancements['+fieldId+']')) {

            // Show/hide subfields on document ready, depending on if module is enabled or not
            if ( document.getElementById('admin_site_enhancements['+fieldId+']').checked ) {

               $('.'+fieldClass+' .asenha-subfields').show();
               if (document.querySelector('.'+fieldClass+' .asenha-subfield-select-inner')) {
                  $('.'+fieldClass+' .asenha-subfield-select-inner').show();
               }
               $('.asenha-toggle.'+fieldClass+' td .asenha-field-with-options').addClass('is-enabled');
               if ( codeMirrorInstances ) {
                  Object.keys(codeMirrorInstances).forEach(function(key) {
                     if ( codeMirrorInstances[key] ) {
                        codeMirrorInstances[key].refresh();
                     }
                  });
               }

            } else {

               $('.'+fieldClass+' .asenha-subfields').hide();
               if (document.querySelector('.'+fieldClass+' .asenha-subfield-select-inner')) {
                  $('.'+fieldClass+' .asenha-subfield-select-inner').hide();
               }

            }

            // Show/hide subfields on toggle click
            document.getElementById('admin_site_enhancements['+fieldId+']').addEventListener('click', event => {
               if (event.target.checked) {

                  $('.'+fieldClass+' .asenha-subfields').fadeIn();
                  if (document.querySelector('.'+fieldClass+' .asenha-subfield-select-inner')) {
                     $('.'+fieldClass+' .asenha-subfield-select-inner').show();
                  }
                  $('.'+fieldClass+' .asenha-field-with-options').toggleClass('is-enabled');
                  if (document.getElementById(sortableId)) {
                     // Initialize sortable elements: https://api.jqueryui.com/sortable/
                     $('#' + sortableId ).sortable();                     
                  }
                  if ( codeMirrorInstances ) {
                     Object.keys(codeMirrorInstances).forEach(function(key) {
                        if ( codeMirrorInstances[key] ) {
                           codeMirrorInstances[key].refresh();                        
                        }
                     });
                  }

               } else {

                  $('.'+fieldClass+' .asenha-subfields').hide();
                  if (document.querySelector('.'+fieldClass+' .asenha-subfield-select-inner')) {
                     $('.'+fieldClass+' .asenha-subfield-select-inner').hide();
                  }
                  $('.'+fieldClass+' .asenha-field-with-options').toggleClass('is-enabled');

               }
            });
            
         }
         
      }
      
       // Re-init wp_editor for snippet description. Required because the wp_editor was moved in the DOM after document ready.
       // Ref: https://stackoverflow.com/a/21519323
       // Ref: https://core.trac.wordpress.org/ticket/19173
      function reinitWpEditor(id) {
         tinymce.execCommand('mceRemoveEditor', true, id);
         var init = tinymce.extend( {}, tinyMCEPreInit.mceInit[ id ] );
         try { tinymce.init( init ); } catch(e){}
         $('textarea[id="' + id + '"]').closest('form').find('input[type="submit"]').click(function(){
          if( getUserSetting( 'editor' ) == 'tmce' ){
              var id = mce.find( 'textarea' ).attr( 'id' );
              tinymce.execCommand( 'mceRemoveEditor', false, id );
              tinymce.execCommand( 'mceAddEditor', false, id );
          }
          return true;
         });
      }

      /*! <fs_premium_only> */
      subfieldsToggler( 'custom_content_types', 'custom-content-types' );
      /*! </fs_premium_only> */
      subfieldsToggler( 'enable_duplication', 'enable-duplication' );
      subfieldsToggler( 'content_order', 'content-order' );
      /*! <fs_premium_only> */
      subfieldsToggler( 'terms_order', 'terms-order' );
      subfieldsToggler( 'enable_media_categories', 'enable-media-categories' );
      /*! </fs_premium_only> */
      subfieldsToggler( 'enable_media_replacement', 'enable-media-replacement' );
      subfieldsToggler( 'enable_svg_upload', 'enable-svg-upload' );
      subfieldsToggler( 'enable_avif_upload', 'enable-avif-upload' );
      /*! <fs_premium_only> */
      subfieldsToggler( 'public_preview_for_drafts', 'public-preview-for-drafts' );
      /*! </fs_premium_only> */
      subfieldsToggler( 'enable_external_permalinks', 'enable-external-permalinks' );
      /*! <fs_premium_only> */
      subfieldsToggler( 'show_custom_taxonomy_filters', 'show-custom-taxonomy-filters' );
      /*! </fs_premium_only> */
      subfieldsToggler( 'enhance_list_tables', 'enhance-list-tables' );
      subfieldsToggler( 'custom_admin_footer_text', 'custom-admin-footer-text' );
      subfieldsToggler( 'wider_admin_menu', 'wider-admin-menu' );
      subfieldsToggler( 'customize_admin_menu', 'customize-admin-menu', 'custom-admin-menu' );
      subfieldsToggler( 'disable_dashboard_widgets', 'disable-dashboard-widgets' );
      subfieldsToggler( 'various_admin_ui_enhancements', 'various-admin-ui-enhancements' );
      /*! <fs_premium_only> */
      subfieldsToggler( 'admin_logo', 'admin-logo' );
      /*! </fs_premium_only> */
      // Clean Up Admin Bar
      subfieldsToggler( 'hide_modify_elements', 'hide-modify-elements' );
      /*! <fs_premium_only> */
      subfieldsToggler( 'hide_admin_notices', 'hide-admin-notices' );
      /*! </fs_premium_only> */
      subfieldsToggler( 'hide_admin_bar', 'hide-admin-bar' );
      subfieldsToggler( 'change_login_url', 'change-login-url' );
      subfieldsToggler( 'login_id_type_restriction', 'login-id-type-restriction' );
      /*! <fs_premium_only> */
      subfieldsToggler( 'login_page_customizer', 'login-page-customizer', '', {loginPageCssEditor} );
      subfieldsToggler( 'enable_login_logout_menu', 'enable-login-logout-menu' );
      /*! </fs_premium_only> */
      subfieldsToggler( 'redirect_after_login', 'redirect-after-login' );
      subfieldsToggler( 'redirect_after_logout', 'redirect-after-logout' );
      subfieldsToggler( 'enable_custom_admin_css', 'enable-custom-admin-css', '', {adminCssEditor} );
      subfieldsToggler( 'enable_custom_frontend_css', 'enable-custom-frontend-css', '', {frontendCssEditor} );
      subfieldsToggler( 'insert_head_body_footer_code', 'insert-head-body-footer-code', '', {headCodeEditor,bodyCodeEditor,footerCodeEditor} );
      subfieldsToggler( 'enable_custom_body_class', 'enable-custom-body-class' );
      subfieldsToggler( 'manage_ads_appads_txt', 'manage-ads-appads-txt', '', {adsTxtEditor,appAdsTxtEditor} );
      subfieldsToggler( 'manage_robots_txt', 'manage-robots-txt', '', {robotsTxtEditor} );
      /*! <fs_premium_only> */
      subfieldsToggler( 'enable_code_snippets_manager', 'enable-code-snippets-manager' );
      /*! </fs_premium_only> */
      subfieldsToggler( 'disable_gutenberg', 'disable-gutenberg' );
      subfieldsToggler( 'disable_comments', 'disable-comments' );
      /*! <fs_premium_only> */
      subfieldsToggler( 'disable_rest_api', 'disable-rest-api' );
      /*! </fs_premium_only> */
      subfieldsToggler( 'disable_smaller_components', 'disable-smaller-components' );
      subfieldsToggler( 'limit_login_attempts', 'limit-login-attempts' );
      /*! <fs_premium_only> */
      subfieldsToggler( 'captcha_protection', 'captcha-protection' );
      /*! </fs_premium_only> */
      subfieldsToggler( 'obfuscate_email_address', 'obfuscate-email-address' );
      subfieldsToggler( 'image_upload_control', 'image-upload-control' );
      subfieldsToggler( 'enable_revisions_control', 'enable-revisions-control' );
      subfieldsToggler( 'enable_heartbeat_control', 'enable-heartbeat-control' );
      /*! <fs_premium_only> */
      subfieldsToggler( 'form_builder', 'form-builder', '', {formBuilderEmailCssEditor} );
      subfieldsToggler( 'redirect_404_to_homepage', 'redirect-404-to-homepage' );
      subfieldsToggler( 'search_engine_visibility_status', 'search-engine-visibility-status' );
      /*! </fs_premium_only> */

      // Enable Heartbeat Control => Check if "Modify interval" is chosen/clicked and show/hide the corresponding select field
      if ( $('input[name="admin_site_enhancements[heartbeat_control_for_admin_pages]"]:checked').val() == 'modify' ) {
         $('.heartbeat-interval-for-admin-pages .asenha-subfield-select-inner').show();
      }

      $('input[name="admin_site_enhancements[heartbeat_control_for_admin_pages]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'modify' ) {
            $('.heartbeat-interval-for-admin-pages .asenha-subfield-select-inner').show();
         } else {
            $('.heartbeat-interval-for-admin-pages .asenha-subfield-select-inner').hide();            
         }
      });

      if ( $('input[name="admin_site_enhancements[heartbeat_control_for_post_edit]"]:checked').val() == 'modify' ) {
         $('.heartbeat-interval-for-post-edit .asenha-subfield-select-inner').show();
      }

      $('input[name="admin_site_enhancements[heartbeat_control_for_post_edit]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'modify' ) {
            $('.heartbeat-interval-for-post-edit .asenha-subfield-select-inner').show();
         } else {
            $('.heartbeat-interval-for-post-edit .asenha-subfield-select-inner').hide();            
         }
      });

      if ( $('input[name="admin_site_enhancements[heartbeat_control_for_frontend]"]:checked').val() == 'modify' ) {
         $('.heartbeat-interval-for-frontend .asenha-subfield-select-inner').show();
      }

      $('input[name="admin_site_enhancements[heartbeat_control_for_frontend]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'modify' ) {
            $('.heartbeat-interval-for-frontend .asenha-subfield-select-inner').show();
         } else {
            $('.heartbeat-interval-for-frontend .asenha-subfield-select-inner').hide();            
         }
      });

      subfieldsToggler( 'smtp_email_delivery', 'smtp-email-delivery' );

      /*! <fs_premium_only> */
      // Show hide Username and Password input based on the value of Authentication radio buttons
      if ( $('input[name="admin_site_enhancements[smtp_authentication]"]:checked').val() == 'enable' ) {
         $('tr.smtp-username').show();
         $('tr.smtp-password').show();
      } else {
         $('tr.smtp-username').hide();
         $('tr.smtp-password').hide();
      }

      $('input[name="admin_site_enhancements[smtp_authentication]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'enable' ) {
            $('tr.smtp-username').show();
            $('tr.smtp-password').show();
         } else {
            $('tr.smtp-username').hide();
            $('tr.smtp-password').hide();
         }
      });
      /*! </fs_premium_only> */
      
      // SMTP Email Delivery => Empty field value on click, so new password can be easily entered
      var oldSmtpPassValue = '';

      $('input[name="admin_site_enhancements[smtp_password]"]').focusin(function() {
         oldSmtpPassValue = $(this).val();
         $(this).val('');
      });

      $('input[name="admin_site_enhancements[smtp_password]"]').focusout(function() {
         if ( $(this).val() == '' ) {
            $(this).val(oldSmtpPassValue);
         }
      });

      subfieldsToggler( 'view_admin_as_role', 'view-admin-as-role' );
      subfieldsToggler( 'enable_password_protection', 'enable-password-protection' );

      // Enable Password protection => Empty field value on click, so new password can be easily entered
      var oldValue = '';
      $('input[name="admin_site_enhancements[password_protection_password]"]').focusin(function() {
         oldValue = $(this).val();
         $(this).val('');
      });

      $('input[name="admin_site_enhancements[password_protection_password]"]').focusout(function() {
         if ( $(this).val() == '' ) {
            $(this).val(oldValue);
         }
      });
      
      /*! <fs_premium_only> */
      if ( maintenanceModeCssEditor != 'undefined' ) {
         subfieldsToggler( 'maintenance_mode', 'maintenance-mode', '', {maintenancePageHeadCodeEditor,maintenanceModeCssEditor} );
      } else {
      /*! </fs_premium_only> */
         subfieldsToggler( 'maintenance_mode', 'maintenance-mode' );
      /*! <fs_premium_only> */
      }
      /*! </fs_premium_only> */

      /*! <fs_premium_only> */
      // Login Page Customizer => Check if 'Custom' is chosen/clicked and show/hide the corresponding subfields      

      // Login page form section color scheme
      if ( $('input[name="admin_site_enhancements[login_page_form_color_scheme]"]:checked').val() == 'light' ) {
         $('.login-page-form-color-scheme-custom').hide();
      } else if ( $('input[name="admin_site_enhancements[login_page_form_color_scheme]"]:checked').val() == 'dark' ) {
         $('.login-page-form-color-scheme-custom').hide();
      } else if ( $('input[name="admin_site_enhancements[login_page_form_color_scheme]"]:checked').val() == 'custom' ) {
         $('.login-page-form-color-scheme-custom').show();
      } else {
         $('.login-page-form-color-scheme-custom').hide();
      }

      $('input[name="admin_site_enhancements[login_page_form_color_scheme]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'light' ) {
            $('.login-page-form-color-scheme-custom').hide();
         } else if ( radioValue == 'dark' ) {
            $('.login-page-form-color-scheme-custom').hide();
         } else if ( radioValue == 'custom' ) {
            $('.login-page-form-color-scheme-custom').show();
         } else {
            $('.login-page-form-color-scheme-custom').hide();
         }
      });

      // Login page login image type
      if ( $('input[name="admin_site_enhancements[login_page_logo_image_type]"]:checked').val() == 'site_icon' ) {
         $('tr.login-page-logo-image').hide();
         $('tr.login-page-logo-image-external').hide();
         $('tr.login-page-logo-image-external-description').hide();
         $('tr.login-page-logo-image-size').hide();
         $('tr.login-page-logo-image-width').hide();
         $('tr.login-page-logo-image-height').hide();
         $('tr.login-page-logo-image-width-external').hide();
         $('tr.login-page-logo-image-height-external').hide();
         $('tr.login-page-logo-image-description').hide();
         $('tr.login-page-logo-site-icon-description').show();
      } else if ( $('input[name="admin_site_enhancements[login_page_logo_image_type]"]:checked').val() == 'custom' ) {         
         $('tr.login-page-logo-image').show();
         $('tr.login-page-logo-image-external').hide();
         $('tr.login-page-logo-image-external-description').hide();
         $('tr.login-page-logo-image-size').show();
         $('tr.login-page-logo-image-width').show();
         $('tr.login-page-logo-image-height').show();
         $('tr.login-page-logo-image-width-external').hide();
         $('tr.login-page-logo-image-height-external').hide();
         $('tr.login-page-logo-image-description').hide();
         $('tr.login-page-logo-site-icon-description').hide();
      } else if ( $('input[name="admin_site_enhancements[login_page_logo_image_type]"]:checked').val() == 'custom_url' ) {         
         $('tr.login-page-logo-image').hide();
         $('tr.login-page-logo-image-external').show();
         $('tr.login-page-logo-image-external-description').show();
         $('tr.login-page-logo-image-size').show();
         $('tr.login-page-logo-image-width').hide();
         $('tr.login-page-logo-image-height').hide();
         $('tr.login-page-logo-image-width-external').show();
         $('tr.login-page-logo-image-height-external').show();
         $('tr.login-page-logo-image-description').show();
         $('tr.login-page-logo-site-icon-description').hide();
      } else {
         $('tr.login-page-logo-image').show();
         $('tr.login-page-logo-image-size').show();
         $('tr.login-page-logo-image-width').show();
         $('tr.login-page-logo-image-height').show();
         $('tr.login-page-logo-image-description').show();
         $('tr.login-page-logo-site-icon-description').hide();
      }

      $('input[name="admin_site_enhancements[login_page_logo_image_type]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'site_icon' ) {
            $('tr.login-page-logo-image').hide();
            $('tr.login-page-logo-image-external').hide();
            $('tr.login-page-logo-image-external-description').hide();
            $('tr.login-page-logo-image-size').hide();
            $('tr.login-page-logo-image-width').hide();
            $('tr.login-page-logo-image-height').hide();
            $('tr.login-page-logo-image-width-external').hide();
            $('tr.login-page-logo-image-height-external').hide();
            $('tr.login-page-logo-image-description').hide();
            $('tr.login-page-logo-site-icon-description').show();
         } else if ( radioValue == 'custom' ) {
            $('tr.login-page-logo-image').show();
            $('tr.login-page-logo-image-external').hide();
            $('tr.login-page-logo-image-external-description').hide();
            $('tr.login-page-logo-image-size').show();
            $('tr.login-page-logo-image-width').show();
            $('tr.login-page-logo-image-height').show();
            $('tr.login-page-logo-image-width-external').hide();
            $('tr.login-page-logo-image-height-external').hide();
            $('tr.login-page-logo-image-description').hide();
            $('tr.login-page-logo-site-icon-description').hide();
         } else if ( radioValue == 'custom_url' ) {
            $('tr.login-page-logo-image').hide();
            $('tr.login-page-logo-image-external').show();
            $('tr.login-page-logo-image-external-description').show();
            $('tr.login-page-logo-image-size').show();
            $('tr.login-page-logo-image-width').hide();
            $('tr.login-page-logo-image-height').hide();
            $('tr.login-page-logo-image-width-external').show();
            $('tr.login-page-logo-image-height-external').show();
            $('tr.login-page-logo-image-description').show();
            $('tr.login-page-logo-site-icon-description').hide();
         } else {
            $('tr.login-page-logo-image').show();
            $('tr.login-page-logo-image-size').show();
            $('tr.login-page-logo-image-width').show();
            $('tr.login-page-logo-image-height').show();
            $('tr.login-page-logo-image-description').show();
            $('tr.login-page-logo-site-icon-description').hide();
         }
      });

      // Login page background
      if ( $('input[name="admin_site_enhancements[login_page_background]"]:checked').val() == 'pattern' ) {
         $('tr.login-page-background-pattern').show();
         $('tr.login-page-background-image').hide();
         $('tr.login-page-background-color').hide();
      } else if ( $('input[name="admin_site_enhancements[login_page_background]"]:checked').val() == 'image' ) {
         $('tr.login-page-background-pattern').hide();
         $('tr.login-page-background-image').show();
         $('tr.login-page-background-color').hide();
      } else if ( $('input[name="admin_site_enhancements[login_page_background]"]:checked').val() == 'solid_color' ) {
         $('tr.login-page-background-pattern').hide();
         $('tr.login-page-background-image').hide();
         $('tr.login-page-background-color').show();
      } else {
         $('tr.login-page-background-pattern').hide();
         $('tr.login-page-background-image').hide();
         $('tr.login-page-background-color').hide();         
      }

      $('input[name="admin_site_enhancements[login_page_background]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'pattern' ) {
            $('tr.login-page-background-pattern').show();
            $('tr.login-page-background-image').hide();
            $('tr.login-page-background-color').hide();
         } else if ( radioValue == 'image' ) {
            $('tr.login-page-background-pattern').hide();
            $('tr.login-page-background-image').show();
            $('tr.login-page-background-color').hide();
         } else if ( radioValue == 'solid_color' ) {
            $('tr.login-page-background-pattern').hide();
            $('tr.login-page-background-image').hide();
            $('tr.login-page-background-color').show();
         } else {
            $('tr.login-page-background-pattern').hide();
            $('tr.login-page-background-image').hide();
            $('tr.login-page-background-color').hide();         
         }
      });

      // Redirect After Login
      if ( $('input[name="admin_site_enhancements[redirect_after_login_type]"]:checked').val() == 'single_url' ) {
         $('tr.redirect-after-login-to-slug').show();
         $('tr.redirect-after-login-for').show();
         $('tr.redirect-after-login-for-separate').hide();         
      } else {
         $('tr.redirect-after-login-to-slug').hide();
         $('tr.redirect-after-login-for').hide();
         $('tr.redirect-after-login-for-separate').show();         
      }

      $('input[name="admin_site_enhancements[redirect_after_login_type]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'single_url' ) {
            $('tr.redirect-after-login-to-slug').show();
            $('tr.redirect-after-login-for').show();
            $('tr.redirect-after-login-for-separate').hide();         
         } else {
            $('tr.redirect-after-login-to-slug').hide();
            $('tr.redirect-after-login-for').hide();
            $('tr.redirect-after-login-for-separate').show();         
         }
      });

      // Redirect After Logout
      if ( $('input[name="admin_site_enhancements[redirect_after_logout_type]"]:checked').val() == 'single_url' ) {
         $('tr.redirect-after-logout-to-slug').show();
         $('tr.redirect-after-logout-for').show();
         $('tr.redirect-after-logout-for-separate').hide();         
      } else {
         $('tr.redirect-after-logout-to-slug').hide();
         $('tr.redirect-after-logout-for').hide();
         $('tr.redirect-after-logout-for-separate').show();         
      }

      $('input[name="admin_site_enhancements[redirect_after_logout_type]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'single_url' ) {
            $('tr.redirect-after-logout-to-slug').show();
            $('tr.redirect-after-logout-for').show();
            $('tr.redirect-after-logout-for-separate').hide();         
         } else {
            $('tr.redirect-after-logout-to-slug').hide();
            $('tr.redirect-after-logout-for').hide();
            $('tr.redirect-after-logout-for-separate').show();         
         }
      });
      
      // Disable Gutenberg
      if ( $('input[name="admin_site_enhancements[disable_gutenberg_type]"]:checked').val() == 'all-post-types' ) {
         $('.asenha-checkbox-item.disable-gutenberg-for').hide();
         $('.disable-gutenberg-type').removeClass('asenha-th-border-bottom');
      } else {
         $('.asenha-checkbox-item.disable-gutenberg-for').show();
         $('.disable-gutenberg-type').addClass('asenha-th-border-bottom');
      }

      $('input[name="admin_site_enhancements[disable_gutenberg_type]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'all-post-types' ) {
            $('.asenha-checkbox-item.disable-gutenberg-for').hide();
            $('.disable-gutenberg-type').removeClass('asenha-th-border-bottom');
         } else {
            $('.asenha-checkbox-item.disable-gutenberg-for').show();
            $('.disable-gutenberg-type').addClass('asenha-th-border-bottom');
         }
      });

      // Disable Comments
      if ( $('input[name="admin_site_enhancements[disable_comments_type]"]:checked').val() == 'all-post-types' ) {
         $('.asenha-checkbox-item.disable-comments-for').hide();
         $('.disable-comments-type').removeClass('asenha-th-border-bottom');
      } else {
         $('.asenha-checkbox-item.disable-comments-for').show();
         $('.disable-comments-type').addClass('asenha-th-border-bottom');
      }

      $('input[name="admin_site_enhancements[disable_comments_type]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'all-post-types' ) {
            $('.asenha-checkbox-item.disable-comments-for').hide();
            $('.disable-comments-type').removeClass('asenha-th-border-bottom');
         } else {
            $('.asenha-checkbox-item.disable-comments-for').show();
            $('.disable-comments-type').addClass('asenha-th-border-bottom');
         }
      });

      // CAPTCHA Protection
      // CAPTCHA Type conditional show/hide
      $(document).on('change', 'select[name="admin_site_enhancements[captcha_protection_types]"]', function() {
         var selectValue = this.value;
         if ( 'altcha' == selectValue ) {
            $('.captcha-protection-altcha-wrapper').show();
            $('.captcha-protection-recaptcha-wrapper').hide();
            $('.captcha-protection-turnstile-wrapper').hide();
            // $('input#captcha_woo_locations_woo_checkout_form').prop('disabled', '');
            // $('input#captcha_woo_locations_woo_checkout_form').show();
            // $('label[for="captcha_woo_locations_woo_checkout_form"]').show();
         } else if ( 'recaptcha' == selectValue ) {
            $('.captcha-protection-altcha-wrapper').hide();
            $('.captcha-protection-recaptcha-wrapper').show();
            $('.captcha-protection-turnstile-wrapper').hide();
            // $('input#captcha_woo_locations_woo_checkout_form').prop('disabled', '');
            // $('input#captcha_woo_locations_woo_checkout_form').show();
            // $('label[for="captcha_woo_locations_woo_checkout_form"]').show();
         } else if ( 'turnstile' == selectValue ) {
            $('.captcha-protection-altcha-wrapper').hide();
            $('.captcha-protection-recaptcha-wrapper').hide();
            $('.captcha-protection-turnstile-wrapper').show();
            // $('input#captcha_woo_locations_woo_checkout_form').prop('disabled', 'disabled');
            // $('input#captcha_woo_locations_woo_checkout_form').hide();
            // $('label[for="captcha_woo_locations_woo_checkout_form"]').hide();
         }
      });
      
      if ( 'altcha' == $('select[name="admin_site_enhancements[captcha_protection_types]"]').val() ) {
         $('.captcha-protection-altcha-wrapper').show();
         $('.captcha-protection-recaptcha-wrapper').hide();
         $('.captcha-protection-turnstile-wrapper').hide();
         $('input#captcha_woo_locations_woo_checkout_form').prop('disabled', '');
         $('input#captcha_woo_locations_woo_checkout_form').show();
         $('label[for="captcha_woo_locations_woo_checkout_form"]').show();
      } else if ( 'recaptcha' == $('select[name="admin_site_enhancements[captcha_protection_types]"]').val() ) {
         $('.captcha-protection-altcha-wrapper').hide();
         $('.captcha-protection-recaptcha-wrapper').show();
         $('.captcha-protection-turnstile-wrapper').hide();
         $('input#captcha_woo_locations_woo_checkout_form').prop('disabled', '');
         $('input#captcha_woo_locations_woo_checkout_form').show();
         $('label[for="captcha_woo_locations_woo_checkout_form"]').show();
      } else if ( 'turnstile' == $('select[name="admin_site_enhancements[captcha_protection_types]"]').val() ) {
         $('.captcha-protection-altcha-wrapper').hide();
         $('.captcha-protection-recaptcha-wrapper').hide();
         $('.captcha-protection-turnstile-wrapper').show();
         $('input#captcha_woo_locations_woo_checkout_form').prop('disabled', 'disabled');
         $('input#captcha_woo_locations_woo_checkout_form').hide();
         $('label[for="captcha_woo_locations_woo_checkout_form"]').hide();
      }

      // ALTCHA Floating UI conditional value selection
      $(document).on('change', 'select[name="admin_site_enhancements[altcha_widget]"]', function() {
         var selectValue = this.value;
         if ( 'checkbox' == selectValue ) {
            $('select[name="admin_site_enhancements[altcha_auto_verification]"]').val('');
         } else if ( 'invisible' == selectValue ) {
            $('select[name="admin_site_enhancements[altcha_auto_verification]"]').val('onsubmit');
         }
      });

      if ( 'checkbox' == $('select[name="admin_site_enhancements[altcha_widget]"]').val() ) {
         $('select[name="admin_site_enhancements[altcha_auto_verification]"]').val('');
      } else if ( 'invisible' == $('select[name="admin_site_enhancements[altcha_widget]"]').val() ) {
         $('select[name="admin_site_enhancements[altcha_auto_verification]"]').val('onsubmit');
      }

      // reCAPTCHA Type conditional show/hide
      $(document).on('change', 'select[name="admin_site_enhancements[recaptcha_widget]"]', function() {
         var selectValue = this.value;
         if ( 'v2_checkbox' == selectValue ) {
            $('.recaptcha-site-key-v2-checkbox').show();
            $('.recaptcha-secret-key-v2-checkbox').show();
            $('.recaptcha-site-key-v3-invisible').hide();
            $('.recaptcha-secret-key-v3-invisible').hide();
         } else if ( 'v3_invisible' == selectValue ) {
            $('.recaptcha-site-key-v2-checkbox').hide();
            $('.recaptcha-secret-key-v2-checkbox').hide();
            $('.recaptcha-site-key-v3-invisible').show();
            $('.recaptcha-secret-key-v3-invisible').show();
         }
      });

      if ( 'v2_checkbox' == $('select[name="admin_site_enhancements[recaptcha_widget]"]').val() ) {
         $('.recaptcha-site-key-v2-checkbox').show();
         $('.recaptcha-secret-key-v2-checkbox').show();
         $('.recaptcha-site-key-v3-invisible').hide();
         $('.recaptcha-secret-key-v3-invisible').hide();
      } else if ( 'v3_invisible' == $('select[name="admin_site_enhancements[recaptcha_widget]"]').val() ) {
         $('.recaptcha-site-key-v2-checkbox').hide();
         $('.recaptcha-secret-key-v2-checkbox').hide();
         $('.recaptcha-site-key-v3-invisible').show();
         $('.recaptcha-secret-key-v3-invisible').show();
      }

      // Email Address Obfuscator
      if ( $('input[name="admin_site_enhancements[obfuscate_email_address_in_content]"]').is(':checked') ) {
         $('.obfuscate-email-address-visitor-only').show();
      } else {
         $('.obfuscate-email-address-visitor-only').hide();
      }

      $('input[name="admin_site_enhancements[obfuscate_email_address_in_content]"]').click(function() {
         if ($(this).is(':checked')) {
            $('.obfuscate-email-address-visitor-only').show();
         } else {
            $('.obfuscate-email-address-visitor-only').hide();
         }
      });

      // Image Upload Control
      if ( $('input[name="admin_site_enhancements[convert_to_webp]"]').is(':checked') ) {
         $('.convert-to-jpg-quality').hide();
         $('.convert-to-webp-quality').show();
      } else {
         $('.convert-to-jpg-quality').show();
         $('.convert-to-webp-quality').hide();
      }

      $('input[name="admin_site_enhancements[convert_to_webp]"]').click(function() {
         if ($(this).is(':checked')) {
            $('.convert-to-jpg-quality').hide();
            $('.convert-to-webp-quality').show();
         } else {
            $('.convert-to-jpg-quality').show();
            $('.convert-to-webp-quality').hide();
         }
      });

      if ( $('input[name="admin_site_enhancements[disable_image_conversion]"]').is(':checked') ) {
         $('.image-conversion-wrapper').hide();
      } else {
         $('.image-conversion-wrapper').show();
      }

      $('input[name="admin_site_enhancements[disable_image_conversion]"]').click(function() {
         if ($(this).is(':checked')) {
            $('.image-conversion-wrapper').hide();
         } else {
            $('.image-conversion-wrapper').show();
         }
      });

      function field_value_initial_sync( originSelector, targetSelector ) {
         if ( $(originSelector).val() != '' ) {
            $(targetSelector).val($(originSelector).val());
         }
      }

      function field_value_live_sync( originSelector, targetSelector ) {
         $('.asenha-body').on('input', originSelector, function() {
            var originValue = $(this).val();
            $(targetSelector).val(originValue);
         });
      }
      
      function field_value_syncing( originSelector, targetSelector ) {
         field_value_initial_sync( originSelector, targetSelector );
         field_value_live_sync( originSelector, targetSelector );
      }

      // Form Builder site and secret keys syncing
      field_value_syncing('input[name="admin_site_enhancements[altcha_secret_key]"]', 'input[name="admin_site_enhancements[form_builder_altcha_secret_key]"]');
      field_value_syncing('input[name="admin_site_enhancements[recaptcha_site_key_v2_checkbox]"]', 'input[name="admin_site_enhancements[form_builder_recaptcha_site_key_v2_checkbox]"]');
      field_value_syncing('input[name="admin_site_enhancements[recaptcha_secret_key_v2_checkbox]"]', 'input[name="admin_site_enhancements[form_builder_recaptcha_secret_key_v2_checkbox]"]');
      field_value_syncing('input[name="admin_site_enhancements[recaptcha_site_key_v3_invisible]"]', 'input[name="admin_site_enhancements[form_builder_recaptcha_site_key_v3_invisible]"]');
      field_value_syncing('input[name="admin_site_enhancements[recaptcha_secret_key_v3_invisible]"]', 'input[name="admin_site_enhancements[form_builder_recaptcha_secret_key_v3_invisible]"]');
      field_value_syncing('input[name="admin_site_enhancements[turnstile_site_key]"]', 'input[name="admin_site_enhancements[form_builder_turnstile_site_key]"]');
      field_value_syncing('input[name="admin_site_enhancements[turnstile_secret_key]"]', 'input[name="admin_site_enhancements[form_builder_turnstile_secret_key]"]');
      
      // Maintenance Mode => Use a customizable page vs Use an existing page      
      if ( $('input[name="admin_site_enhancements[maintenance_page_type]"]:checked').val() == 'custom' ) {
         $('.maintenance-page-type-custom').show();
         $('tr.maintenance-page-slug').hide();
      } else if ( $('input[name="admin_site_enhancements[maintenance_page_type]"]:checked').val() == 'existing' ) {
         $('.maintenance-page-type-custom').hide();
         $('tr.maintenance-page-slug').show();
      } else {
         $('.maintenance-page-type-custom').show();
         $('tr.maintenance-page-slug').hide();
      }

      $('input[name="admin_site_enhancements[maintenance_page_type]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'custom' ) {
         $('.maintenance-page-type-custom').show();
            $('tr.maintenance-page-slug').hide();
         } else if ( radioValue == 'existing' ) {
         $('.maintenance-page-type-custom').hide();
            $('tr.maintenance-page-slug').show();
         } else {
            $('.maintenance-page-type-custom').show();
            $('tr.maintenance-page-slug').hide();
         }
      });

      // Maintenance Mode => Check if 'Image' or 'Color' is chosen/clicked and show/hide the corresponding select field      
      if ( $('input[name="admin_site_enhancements[maintenance_page_background]"]:checked').val() == 'pattern' ) {
         $('tr.maintenance-page-background-pattern').show();
         $('tr.maintenance-page-background-image').hide();
         $('tr.maintenance-page-background-color').hide();         
      } else if( $('input[name="admin_site_enhancements[maintenance_page_background]"]:checked').val() == 'image' ) {
         $('tr.maintenance-page-background-pattern').hide();
         $('tr.maintenance-page-background-image').show();
         $('tr.maintenance-page-background-color').hide();
      } else if ( $('input[name="admin_site_enhancements[maintenance_page_background]"]:checked').val() == 'solid_color' ) {
         $('tr.maintenance-page-background-pattern').hide();
         $('tr.maintenance-page-background-image').hide();
         $('tr.maintenance-page-background-color').show();
      } else {
         $('tr.maintenance-page-background-pattern').hide();
         $('tr.maintenance-page-background-image').hide();
         $('tr.maintenance-page-background-color').hide();         
      }

      $('input[name="admin_site_enhancements[maintenance_page_background]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'pattern' ) {
            $('tr.maintenance-page-background-pattern').show();
            $('tr.maintenance-page-background-image').hide();
            $('tr.maintenance-page-background-color').hide();            
         } else if ( radioValue == 'image' ) {
            $('tr.maintenance-page-background-pattern').hide();
            $('tr.maintenance-page-background-image').show();
            $('tr.maintenance-page-background-color').hide();
         } else if ( radioValue == 'solid_color' ) {
            $('tr.maintenance-page-background-pattern').hide();
            $('tr.maintenance-page-background-image').hide();
            $('tr.maintenance-page-background-color').show();
         } else {
            $('tr.maintenance-page-background-pattern').hide();
            $('tr.maintenance-page-background-image').hide();
            $('tr.maintenance-page-background-color').hide();         
         }
      });
      /*! </fs_premium_only> */
      
      // Content Toggler
      $('.asenha-body').on('click', '.asenha-content-toggler', function(e) {
         e.preventDefault();
         var targetSelector = $(this).data('target-selector');
         var showText = $(this).data('show-text');
         var hideText = $(this).data('hide-text');
         var expanded = $(this).attr('data-expanded');
         // $(targetSelector).toggle();
         if (expanded == 'no') {
            $(targetSelector).slideDown(200);
            $(this).html(hideText + ' <span>▲</span>');
            $(this).attr('data-expanded','yes');
         } else {
            $(targetSelector).slideUp(200);
            $(this).html(showText + ' <span>▼</span>');
            $(this).attr('data-expanded','no');
         }
      });
      
      // Media frame handler for image selection / upload fields
      // Reference: https://plugins.trac.wordpress.org/browser/bm-custom-login/trunk/bm-custom-login.php
      function media_frame_init( selector, button_selector ) {
         // media_frame_init( '#login-page-logo-image', '#login-page-logo-image-button' );
         var theSelector = $(selector);
         var button = $(button_selector);

         button.click(function (event) {
            event.preventDefault();

            // Configuration of the media frame new instance
            wp.media.frames.frame = wp.media({
               title: adminPageVars.mediaFrameTitle,
               multiple: false,
               library: {
                  type: 'image'
               },
               button: {
                  text: adminPageVars.mediaFrameButtonText
               }
            });

            // Function used for the image selection and media manager closing
            var media_set_image = function() {
               var selection = wp.media.frames.frame.state().get('selection');

               // Nothing is selected
               if (!selection) {
                  return;
               }

               // Iterate through selected elements
               selection.each(function(attachment) {
                  // console.log(attachment);
                  var url = attachment.attributes.url;
                  url = url.replace( adminPageVars.wpcontentUrl, '' );
                  theSelector.val(url);

                  if ( '#login-page-logo-image' == selector ) {
                     var attachmentId = $('.login-page-logo-image-attachment-id input');
                     var originalWidthInput = $('.login-page-logo-image-width-original input');
                     var originalHeightInput = $('.login-page-logo-image-height-original input');
                     var widthInput = $('.login-page-logo-image-width input');
                     var heightInput = $('.login-page-logo-image-height input');
                     attachmentId.val(attachment.attributes.id);
                     originalWidthInput.val(attachment.attributes.width);
                     originalHeightInput.val(attachment.attributes.height);
                     widthInput.val(attachment.attributes.width);
                     heightInput.val(attachment.attributes.height);                     
                  }

                  if ( '#form-builder-email-header-image' == selector ) {
                     var attachmentId = $('.form-builder-email-header-image-attachment-id input');
                     attachmentId.val(attachment.attributes.id);
                  }
               });
            };

            wp.media.frames.frame.on('close', media_set_image);
            wp.media.frames.frame.on('select', media_set_image);
            wp.media.frames.frame.open();
         });
      }

      // Form Builder - Empty out stored/hidden attachment ID when "Email header image" field is emptied
      var emailHeaderImage = $('#form-builder-email-header-image');
      var emailHeaderImageAttachmentId = $('.form-builder-email-header-image-attachment-id input');
      
      $(emailHeaderImage).keyup(delay(function (e) {
         if ($(this).val().length === 0) {
            emailHeaderImageAttachmentId.val("");
         }
      }, 200));

      // =============== Image Ratio Calculator / Preservation for Login Page Customizer >> Logo Image =================

      // Code modified from: https://codepen.io/tobiasdev/pen/XNjxdZ by Tobias Bogliolo

      var initialWidth, initialHeight, newWidth, newHeight, aspectRatio;

      //Get new values:
      function getValues(){
         initialWidth = $(".login-page-logo-image-width-original input").val();
         initialHeight = $(".login-page-logo-image-height-original input").val();
         newWidth = $(".login-page-logo-image-width input").val();
         newHeight = $(".login-page-logo-image-height input").val();
      };

      //Aspect ratio:
      function getAspectRatio(){
         // Formula: "Aspect Ratio = Width / Height".
         return aspectRatio = initialWidth/initialHeight;
      };

      //Get new height:
      $(".login-page-logo-image-width input").on("change keyup", function(){
         // Refresh data.
         getValues();
         getAspectRatio();
         // New height = new width / (original width / original height).
         newHeight = Math.round(newWidth/aspectRatio);
         // Output:
         $(".login-page-logo-image-height input").val(newHeight);
      });

      //Get new width:
      $(".login-page-logo-image-height input").on("change keyup", function(){
         // Refresh data.
         getValues();
         getAspectRatio();
         // New width = (original width / original height) * new height.
         newWidth = Math.round(newHeight*aspectRatio);
         // Output:
         $(".login-page-logo-image-width input").val(newWidth);
      });

      //Reset:
      $(".login-page-logo-image-width-original input, .login-page-logo-image-height-original input").on("change keyup", function(){
         // Output:
         $(".login-page-logo-image-width input").val("");
         $(".login-page-logo-image-height input").val("");
      });
            
      // =============== ASE PRO =================

      if ( asenhaStats.isYearEndPromoPeriod ) {

         // Promo nudge
         if ( asenhaStats.hidePromoNudge ) {
            $('.asenha-promo-nudge').hide();
            $('#bottom-upgrade-nudge').show();
         } else {
            $('.asenha-promo-nudge').show();
            $('#bottom-upgrade-nudge').hide();
         }
         
         $('#dismiss-promo-nudge').click(function(e) {
            e.preventDefault();
            $.ajax({
               url: ajaxurl,
               data: {
                  'action':'dismiss_promo_nudge',
                  'nonce': adminPageVars.nonce
               },
               success:function(data) {
                  $('.asenha-promo-nudge').hide();
               },
               error:function(errorThrown) {
                  console.log(errorThrown);
               }
            });
         });
         
      } else {

         // Upgrade nudge to Pro
         if ( asenhaStats.hideUpgradeNudge ) {
            $('.asenha-upgrade-nudge').hide();
            $('#bottom-upgrade-nudge').show();
         } else {
            $('.asenha-upgrade-nudge').show();
            $('#bottom-upgrade-nudge').hide();
         }

         $('#dismiss-upgrade-nudge').click(function(e) {
            e.preventDefault();
            $.ajax({
               url: ajaxurl,
               data: {
                  'action':'dismiss_upgrade_nudge',
                  'nonce': adminPageVars.nonce
               },
               success:function(data) {
                  $('.asenha-upgrade-nudge').hide();
                  // $('#bottom-upgrade-nudge').show();
               },
               error:function(errorThrown) {
                  console.log(errorThrown);
               }
            });
         });

      }
      
      // =============== SPONSORSHIP =================

      // Stats on saving changes from asenha_admin_scripts() wp_localize_script() is availble in the 'asenhaStats' object-----
      // console.log( asenhaStats );
      // alert(JSON.stringify(asenhaStats));
      if ( asenhaStats.showSupportNudge ) {
         $('.asenha-support-nudge').show();
      } else {
         $('.asenha-support-nudge').hide();
      }

      $('#have-shared,#have-reviewed').click(function(e) {
         e.preventDefault();
         // $.ajax({
         //    url: 'https://bowo.io/asenha-sp-ndg',
         //    method: 'GET',
         //    dataType: 'jsonp',
         //    crossDomain: true
         // });
         $.ajax({
            url: ajaxurl,
            data: {
               'action':'have_supported',
               'nonce': adminPageVars.nonce
            },
            success:function(data) {
               $('.asenha-support-nudge').hide();
            },
            error:function(errorThrown) {
               console.log(errorThrown);
            }
         });
      });
      
      $('#support-nudge-dismiss').click(function(e) {
         e.preventDefault();
         // $.ajax({
         //    url: 'https://bowo.io/asenha-sp-ndg',
         //    method: 'GET',
         //    dataType: 'jsonp',
         //    crossDomain: true
         // });
         $.ajax({
            url: ajaxurl,
            data: {
               'action':'dismiss_support_nudge',
               'nonce': adminPageVars.nonce
            },
            success:function(data) {
               $('.asenha-support-nudge').hide();
            },
            error:function(errorThrown) {
               console.log(errorThrown);
            }
         });
      });

      // Expand support notice | Modified from https://codepen.io/symonsays/pen/rzgEgY
      $('.asenha-support-nudge.nudge-show-more > .show-more').click(function(e) {

         e.preventDefault();

         var $this = $(this);
         $this.toggleClass('show-more');
         $this.hide();

         if ($this.hasClass('show-more')) {
            $this.next().removeClass('opened',0);
         } else {
            $this.next().addClass('opened',0);
         }

      });

      // Collapse support notice | Modified from https://codepen.io/symonsays/pen/rzgEgY
      $('#support-nudge-show-less').click(function(e) {

         e.preventDefault();

         $('.nudge-wrapper-show-more').removeClass('opened',0);
         $('#support-nudge-show-moreless').addClass('show-more');
         $('#support-nudge-show-moreless').show();

      });

      /*! <fs_premium_only> */      
      // Open Export / Import settings
      if ( $('#settings-export-import').length ) {
         $('body').on('click', '#settings-export-import',function(e) {
            e.preventDefault();
            $('#asenha-footer').toggle();
            var yPosition = $(window).scrollTop();
            $(window).scrollTop(yPosition+720);
            // $('html, body').animate({scrollTop: '+=720px'}, 100);
         });
      }
      /*! </fs_premium_only> */

      // Modal for sponsoring plugin dev and maintenance: https://stephanwagner.me/jBox

      // var sponsorModal = new jBox('Modal', {
      //    attach: '#plugin-sponsor',
      //    trigger: 'click', // or 'mouseenter'
      //    // content: 'Test'
      //    content: $('#asenha-sponsor'),
      //    width: 740, // pixels
      //    closeButton: 'box',
      //    addClass: 'plugin-sponsor-modal',
      //    overlayClass: 'plugin-sponsor-modal-overlay',
      //    target: '#wpwrap', // where to anchor the modal
      //    position: {
      //       x: 'center',
      //       y: 'top'
      //    },
      //    // fade: 1000,
      //    animation: {
      //       open: 'slide:top',
      //       close: 'slide:top'
      //    }
      // });
      
      // $('#plugin-sponsor').click( function() {
      //    $.ajax({
      //       url: 'https://bowo.io/asenha-sp-btn',
      //       method: 'GET',
      //       dataType: 'jsonp',
      //       crossDomain: true
      //       // success: function(response) {
      //       //    console.log(response);
      //       // }
      //    });
      // });

   }); // END OF $(document).ready()

})( jQuery );