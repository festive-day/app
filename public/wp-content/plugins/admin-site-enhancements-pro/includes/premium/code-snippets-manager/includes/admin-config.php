<?php
/**
 * Code Snippets Manager
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Code_Snippets_Manager_AdminConfig 
 */
class Code_Snippets_Manager_AdminConfig {

    var $settings_default;

    var $settings;

    /**
     * Constructor
     */
    public function __construct() {
        // Get the "default settings"
        $settings_default = apply_filters( 'csm_settings_default', array() );

        // Get the saved settings
        $extra_options = get_option( ASENHA_SLUG_U . '_extra', array() );
        $settings = isset( $extra_options['code_snippets_manager_settings'] ) ? $extra_options['code_snippets_manager_settings'] : array();

        if ( ! is_array( $settings ) || count( $settings ) === 0 ) {
            $settings = $settings_default;
        } else {
            foreach( $settings_default as $_key => $_value ) {
                if ( ! isset($settings[$_key] ) ) {
                    $settings[$_key] = $_value;
                }
            }
        }
        $this->settings = $settings;
        $this->settings_default = $settings_default;

        //Add actions and filters
        // add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

        add_action( 'csm_settings_form', array( $this, 'general_extra_form' ), 11 );
        add_filter( 'csm_settings_default', array( $this, 'general_extra_default' ) );
        add_filter( 'csm_settings_save', array( $this, 'general_extra_save' ) );
    }


    /**
     * Add submenu pages
     */
    function admin_menu() {
        $menu_slug = 'edit.php?post_type=asenha_code_snippet';

        add_submenu_page( $menu_slug, 'Settings', 'Settings', 'manage_options', 'code-snippets-manager-config', array( $this, 'config_page' ) );

    }


    /**
     * Enqueue the scripts and styles
     */
    public function admin_enqueue_scripts( $hook ) {

        $screen = get_current_screen();

        // Only for code-snippets-manager post type
        if ( $screen->post_type != 'asenha_code_snippet' ) 
            return false;

        if ( $hook != 'code-snippets-manager_page_code-snippets-manager-config' ) 
            return false;

        // Some handy variables
        $a = plugins_url( '/', CSM_PLUGIN_FILE). 'assets';
        $v = CSM_VERSION; 

        wp_enqueue_script( 'tipsy', $a . '/jquery.tipsy.js', array('jquery'), $v, false );
        wp_enqueue_style( 'tipsy', $a . '/tipsy.css', array(), $v );
    }



    /**
     * Template for the config page
     */
    function config_page() {

        if ( isset( $_POST['code_snippets_manager_settings-nonce'] ) ) {
            check_admin_referer('code_snippets_manager_settings', 'code_snippets_manager_settings-nonce');

            $data = apply_filters( 'csm_settings_save', array() );

            $extra_options = get_option( ASENHA_SLUG_U . '_extra', array() );
            $settings =  isset( $extra_options['code_snippets_manager_settings'] ) ? $extra_options['code_snippets_manager_settings'] : array();

            if ( !isset($settings['add_role'] ) ) $settings['add_role'] = false;
            if ( !isset($settings['remove_comments'] ) ) $settings['remove_comments'] = false;

            // If the "add role" option changed
            if ( $data['add_role'] !== $settings['add_role'] && current_user_can('update_plugins')) {
                // Add the 'code_snippets_editor' role
                if ( $data['add_role'] ) {
                    Code_Snippets_Manager_Install::create_roles();
                }

                // Remove the 'code_snippets_editor' role
                if ( !$data['add_role'] ) {
                    remove_role('code_snippets_editor');
                }
                flush_rewrite_rules();
            }

            $extra_options = get_option( ASENHA_SLUG_U . '_extra', array() );
            $extra_options['code_snippets_manager_settings'] = $data;
            // unset( $extra_options['csm_htmlentities'] );
            // unset( $extra_options['csm_htmlentities2'] );
            // unset( $extra_options['csm_autocomplete'] );
            // unset( $extra_options['add_role'] );
            // unset( $extra_options['remove_comments'] );
            update_option( ASENHA_SLUG_U. '_extra', $extra_options, true );


        } else {
            $data = $this->settings;
        }

        ?>
        <div class="wrap">

        <?php $this->config_page_header('editor'); ?>

        <form action="<?php echo admin_url('edit.php'); ?>?post_type=asenha_code_snippet&page=code-snippets-manager-config" id="csm_settings" method="post">

        <?php do_action( 'csm_settings_form' ); ?>
        
        </form>
        </div>
        <?php
    }


    /**
     * Template for config page header 
     */
    function config_page_header( $tab = 'editor' ) {
  
        $url = '?post_type=asenha_code_snippet&page=code-snippets-manager-config';

        $active = array('editor' => '', 'general' => '', 'debug' => '');
        $active[$tab] = 'nav-tab-active';

        ?>
        <style type="text/css">
            .code-snippets-manager_page_code-snippets-manager-config h1 { margin-bottom: 40px; }
            .form-table { margin-left: 2%; width: 98%;}
            .form-table th { width: 500px; } 
        </style>
        <h1>Custom CSS & JS Settings</h1>

        <?php     
    }



    /**
     * Add the defaults for the `General Settings` form 
     */
    function general_extra_default( $defaults = array() ) {
        return array_merge( $defaults, array( 
            'csm_htmlentities'      => false, 
            'csm_htmlentities2'     => false,
			'csm_autocomplete'		=> true,
            'add_role'              => false,
            'remove_comments'       => false,
        ) );
    }


    /**
     * Add the `General Settings` form values to the $_POST for the Settings page
     */
    function general_extra_save( $data = array() ) {
        $values = $this->general_extra_default();

        foreach($values as $_key => $_value ) {
            $values[$_key] = isset($_POST[$_key]) ? true : false;
        }

        return array_merge( $data, $values );
    }


    /**
     * Extra fields for the `General Settings` Form 
     */
    function general_extra_form() {

        // Get the setting
        $extra_options = get_option( ASENHA_SLUG_U . '_extra', array() );
        $settings =  isset( $extra_options['code_snippets_manager_settings'] ) ? $extra_options['code_snippets_manager_settings'] : array();

        $defaults = $this->general_extra_default();

        foreach( $defaults as $_key => $_value ) {
            if ( !isset($settings[$_key] ) ) {
                $settings[$_key] = $_value;
            }
        }

        if ( !get_role('code_snippets_editor') && $settings['add_role'] ) {
            $settings['add_role'] = false;
            update_option( ASENHA_SLUG_U . '_extra', $settings, true );
        }

        if ( get_role('code_snippets_editor') && !$settings['add_role']) {
            $settings['add_role'] = true;
            update_option( ASENHA_SLUG_U . '_extra', $settings, true );
        }

        $csm_htmlentities_help = 'If you want to use an HTML entity in your code (for example '. htmlentities('&gt; or &quot;').'), but the editor keeps on changing them to its equivalent character (&gt; and &quot; for the previous example), then you might want to enable this option.';

        $csm_htmlentities2_help = 'If you use HTML tags in your code (for example '.htmlentities('<input> or <textarea>').') and you notice that they disappear and the editor looks weird, then you need to enable this option.';

        $remove_comments_help = 'In your page\'s HTML there is a comment added before and after the internal CSS or JS in order to help you locate your custom code. Enable this option in order to remove that comment.';

        ?>

        <h2>Editor Settings</h2>
        <table class="form-table">
        <tr>
        <th scope="row"><label for="csm_htmlentities">Keep the HTML entities, don't convert to its character <span class="dashicons dashicons-editor-help tipsy-no-html" rel="tipsy" title="<?php echo $csm_htmlentities_help; ?>"></span>
        </label></th>
        <td><input type="checkbox" name="csm_htmlentities" id="csm_htmlentities" value="1" <?php checked($settings['csm_htmlentities'], true); ?> />
        </td>
        </tr>
        <tr>
        <th scope="row"><label for="csm_htmlentities2">Encode the HTML entities <span class="dashicons dashicons-editor-help tipsy-no-html" rel="tipsy" title="<?php echo $csm_htmlentities2_help; ?>"></span></label></th>
        <td><input type="checkbox" name="csm_htmlentities2" id="csm_htmlentities2" value="1" <?php checked($settings['csm_htmlentities2'], true); ?> />
        </td>
        </tr>
        <tr>
        <th scope="row"><label for="csm_autocomplete">Autocomplete in the editor</label></th>
        <td><input type="checkbox" name="csm_autocomplete" id="csm_autocomplete" value="1" <?php checked($settings['csm_autocomplete'], true); ?> />
        </td>
        </tr>



        </table>


        
        <?php if ( current_user_can('update_plugins') ) : ?> 
            <?php $add_role_help = esc_html('By default only the Administrator will be able to publish/edit/delete Code Snippets. By enabling this option there is also a "Code Snippet Editor" role created which can be assigned to a non-admin user in order to publish/edit/delete Code Snippets.'); ?>
            <h2>General Settings</h2>
            <table class="form-table">
            <tr>
            <th scope="row"><label for="add_role">Add the "Code Snippet Editor" role <span class="dashicons dashicons-editor-help" rel="tipsy" title="<?php echo $add_role_help; ?>"></span></label></th>
            <td><input type="checkbox" name="add_role" id = "add_role" value="1" <?php checked($settings['add_role'], true); ?> />
            </td>
            </tr>
            </table>
        <?php endif; ?>
        <table class="form-table">
        <tr>
        <th scope="row"><label for="remove_comments">Remove the comments from HTML <span class="dashicons dashicons-editor-help" rel="tipsy" title="<?php echo $remove_comments_help; ?>"></span></label></th>
        <td><input type="checkbox" name="remove_comments" id = "remove_comments" value="1" <?php checked($settings['remove_comments'], true); ?> />
        </td>
        </tr>
        </table>

        <table class="form-table">
        <tr>
        <th>&nbsp;</th>
        <td>
        <input type="submit" name="Submit" class="button-primary" value="Save" />
        <?php wp_nonce_field('code_snippets_manager_settings', 'code_snippets_manager_settings-nonce', false); ?>
        </td>
        </tr>
        </table>

        <?php
    }

}

return new Code_Snippets_Manager_AdminConfig();
