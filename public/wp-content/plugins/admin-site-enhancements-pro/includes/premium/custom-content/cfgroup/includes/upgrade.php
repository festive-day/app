<?php

class cfgroup_upgrade
{

    public $version;
    public $last_version;


    public function __construct() {
        $this->version = CFG_VERSION;
        $options = get_option( ASENHA_SLUG_U . '_extra', array() );

        // This will be false (zero) on a fresh install
        if ( isset( $options['cfgroup_version'] ) ) {
            $this->last_version = $options['cfgroup_version'];
        } else {
            $this->last_version = get_option('cfgroup_version');
            $options['cfgroup_version'] = get_option('cfgroup_version');
            update_option( ASENHA_SLUG_U . '_extra', $options, true );
            delete_option( 'cfgroup_version' );
        }

        if ( version_compare( $this->last_version, $this->version, '<' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            if ( version_compare( $this->last_version, '1.0.0', '<' ) ) {
                $this->clean_install();
            }
            else {
                $this->run_upgrade();
            }

            $options = get_option( ASENHA_SLUG_U . '_extra', array() );
            $options['cfgroup_version'] = $this->version;
            update_option( ASENHA_SLUG_U . '_extra', $options, true );
        }
    }

    private function clean_install() {
        global $wpdb;

        // Create table for custom fields values in posts and options pages
        $sql = "
        CREATE TABLE {$wpdb->prefix}asenha_cfgroup_values (
            id INT unsigned not null auto_increment,
            field_id INT unsigned,
            meta_id INT unsigned,
            post_id INT unsigned,
            base_field_id INT unsigned default 0,
            hierarchy TEXT,
            depth INT unsigned default 0,
            weight INT unsigned default 0,
            sub_weight INT unsigned default 0,
            PRIMARY KEY (id),
            INDEX field_id_idx (field_id),
            INDEX post_id_idx (post_id)
        ) DEFAULT CHARSET=utf8";
        dbDelta( $sql );

        // Create table for custom fields values in taxonomy terms
        $this->create_table_for_term_cfg_values();
        
        // Create table to store sessions for handling custom fields in posts / options pages
        $sql = "
        CREATE TABLE {$wpdb->prefix}asenha_cfgroup_sessions (
            id VARCHAR(32),
            data TEXT,
            expires VARCHAR(10),
            PRIMARY KEY (id)
        ) DEFAULT CHARSET=utf8";
        dbDelta( $sql );

        // Set the field counter
        // update_option( 'cfgroup_next_field_id', 1 );
        $options = get_option( ASENHA_SLUG_U . '_extra', array() );
        $options['cfgroup_next_field_id'] = 1;
        update_option( ASENHA_SLUG_U . '_extra', $options, true );
    }

    private function run_upgrade() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'asenha_cfgroup_values_for_terms';
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

        // Table for saving custom field values in taxonomy terms does not yet exist
        if ( ! $wpdb->get_var( $query ) == $table_name ) {
            $this->create_table_for_term_cfg_values();
        }
    }
    
    private function create_table_for_term_cfg_values() {
        global $wpdb;

        $sql = "
        CREATE TABLE {$wpdb->prefix}asenha_cfgroup_values_for_terms (
            id INT unsigned not null auto_increment,
            field_id INT unsigned,
            meta_id INT unsigned,
            term_id INT unsigned,
            base_field_id INT unsigned default 0,
            hierarchy TEXT,
            depth INT unsigned default 0,
            weight INT unsigned default 0,
            sub_weight INT unsigned default 0,
            PRIMARY KEY (id),
            INDEX field_id_idx (field_id),
            INDEX term_id_idx (term_id)
        ) DEFAULT CHARSET=utf8";

        dbDelta( $sql );
    }
}

new cfgroup_upgrade();
