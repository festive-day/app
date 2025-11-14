<?php

namespace ASENHA\Classes;

/**
 * Class for Various Admin UI Enhancements module
 *
 * @since 7.0.2
 */
class Various_Admin_Ui_Enhancements {

    /**
     * Custom sort on the plugins listing to show active plugins first
     * 
     * @link https://plugins.trac.wordpress.org/browser/display-active-plugins-first/tags/1.1/display-active-plugins-first.php
     * @since 6.7.0
     */
    public function show_active_plugins_first() {
        global $wp_list_table, $status;

        if ( ! in_array( $status, array( 'active', 'inactive', 'recently_activated', 'mustuse' ), true ) ) {
            if ( is_array( $wp_list_table->items ) ) {
                uksort( $wp_list_table->items, array( $this, 'plugins_order_callback' ) );
            }
        }
    }
    
    /**
     * Reorder plugins list to show active ones first
     * 
     * @link https://plugins.trac.wordpress.org/browser/display-active-plugins-first/tags/1.1/display-active-plugins-first.php
     * @since 6.7.0
     */
    public function plugins_order_callback( $a, $b ) {
        global $wp_list_table;
        $items = $wp_list_table->items;

        $a_active = is_plugin_active( $a );
        $b_active = is_plugin_active( $b );

        if ( $a_active && ! $b_active ) {
            return -1;
        } elseif ( ! $a_active && $b_active ) {
            return 1;
        } else {
            if ( isset( $items[$a] ) && isset( $items[$b] ) ) {
                return strcasecmp( $items[$a]['Name'], $items[$b]['Name'] );                
            }
        }
    }
    
    /**
     * Preserve visual hierarchy of taxonomy terms in the classic editor
     * 
     * @link https://developer.wordpress.org/reference/hooks/wp_terms_checklist_args/
     * @link https://plugins.trac.wordpress.org/browser/preserve-taxonomy-hierarchy/tags/1.0.1/preserve-taxonomy-hierarchy.php#L20
     * @since 7.0.2
     */
    public function preserve_taxonomy_hierarchy__premium_only() {
        add_filter( 'wp_terms_checklist_args', [ $this, 'disable_checked_on_top__premium_only' ] );
    }
    
    /**
     * Modify checklist arguments and add script
     * 
     * @link https://developer.wordpress.org/reference/functions/wp_terms_checklist/
     * @link https://plugins.trac.wordpress.org/browser/preserve-taxonomy-hierarchy/tags/1.0.1/preserve-taxonomy-hierarchy.php#L33
     * @since 7.0.2
     */
    public function disable_checked_on_top__premium_only( $args ) {
        add_action( 'admin_footer', [ $this, 'scroll_to_first_checked_term__premium_only'] );

        $args['checked_ontop'] = false;
        return $args;
    }

    /**
     * Scroll the taxonomy meta box to the first checked/selected term
     * 
     * @link https://plugins.trac.wordpress.org/browser/preserve-taxonomy-hierarchy/tags/1.0.1/preserve-taxonomy-hierarchy.php#L90
     * @since 7.0.2
     */
    public function scroll_to_first_checked_term__premium_only() {
        ?>
        <script type="text/javascript">
            jQuery(function () {
                jQuery('[id$="-all"] > ul.categorychecklist').each(function () {
                    var $list = jQuery(this);
                    var $firstChecked = $list.find(':checkbox:checked').first();
                    if (!$firstChecked.length) return;
                    var first_one = $list.find(':checkbox').position().top;
                    var checked_one = $firstChecked.position().top;
                    $list.closest('.tabs-panel').scrollTop(checked_one - first_one + 10);
                });
            });
        </script>
        <?php
    }

    /**
     * Enable dashboard columns settings. Allow selecting between 1 to 4 columns.
     * 
     * @link https://plugins.trac.wordpress.org/browser/add-dashboard-columns/tags/2.0.0/add-dashboard-columns.php#L27
     * @since 7.0.2
     */
    public function enable_dashboard_columns_settings__premium_only() {
        if ( is_readable( ASENHA_PATH . 'assets/premium/css/enable-dashboard-columns.css' ) 
            && is_readable( ASENHA_PATH . 'assets/premium/js/enable-dashboard-columns.js' ) 
        ) {
            add_screen_option(
                'layout_columns',
                array(
                    'max'     => 4,
                    'default' => 2,
                )
            );
            add_action(
                'admin_enqueue_scripts',
                function () {
                    wp_enqueue_style( 'add-dashboard-columns', ASENHA_URL . 'assets/premium/css/enable-dashboard-columns.css', array(), ASENHA_VERSION );
                    wp_enqueue_script( 'add-dashboard-columns', ASENHA_URL . 'assets/premium/js/enable-dashboard-columns.js', array( 'jquery' ), ASENHA_VERSION, true );
                },
                999 /** Use high priority for the css properties, so it's loaded later and can override other CSS loaded earlier */
            );
        }
    }
    
    /**
     * Add user roles to admin body classes
     * 
     * @since 7.4.8
     */
    public function add_user_roles_to_admin_body_classes__premium_only( $classes ) {
        $user = wp_get_current_user();
        if ( isset( $user->roles ) && is_array( $user->roles ) ) {
            $current_user_roles = $user->roles;
        }
        
        foreach ( $current_user_roles as $role_slug ) {
            $classes .= ' ' . $role_slug;
        }

        return $classes;
    }

    /**
     * Add user roles to admin body classes
     * 
     * @since 7.5.1
     */
    public function add_username_to_admin_body_classes__premium_only( $classes ) {
        $user = wp_get_current_user();
        $username = $user->user_login;

        $classes .= ' ' . $username;

        return $classes;
    }
    
    /**
     * Force all admin links to open in new tab
     * 
     * @since 7.6.6
     */
    public function script_to_open_admin_links_in_new_tab__premium_only() {
        if ( is_admin() ) {
            ?>
            <script type="text/javascript" id="asenha-open-admin-links-in-new-tab">
                document.addEventListener('DOMContentLoaded', function() {
                  var links = document.getElementsByTagName('a');

                  for (var i = 0; i < links.length; i++) {
                     var url = links[i].getAttribute('href');
                     var target = links[i].getAttribute('target');

                     if (url != null && url != '#') {
                       target = '_blank';
                       links[i].setAttribute('href', url);
                       links[i].setAttribute('target', target);
                     }
                  }
                });
            </script>
            <?php
        }
    }
}