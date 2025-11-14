<?php

namespace ASENHA\Classes;

/**
 * Class for Display System Summary module
 *
 * @since 6.9.5
 */
class Display_System_Summary {
    
    /**
     * Display system summary in the "At a Glance" dashboard widget
     * 
     * @since 5.6.0
     */
    public function display_system_summary() {

        // When user is logged-in as in an administrator
        if ( is_user_logged_in() ) {
            if ( current_user_can( 'manage_options' ) ) {

                if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
                    $server_software_raw = str_replace( "/", " ", $_SERVER['SERVER_SOFTWARE'] );
                    $server_software_parts = explode( " (", $server_software_raw );
                    $server_software = ucfirst( $server_software_parts[0] );
                } else {
                    $server_software = 'Unknown';
                }

                $php_version = phpversion();
                
                // From WP core /wp-admin/includes/class-wp-debug-data.php
                global $wpdb;
                $db_server = $wpdb->get_var( 'SELECT VERSION()' );
                $db_server_parts = explode( ':', $db_server );
                $db_server = $db_server_parts[0];
                $db_separator = '&9670;';

                $ip = 'localhost';

                if ( isset( $_SERVER['HTTP_X_SERVER_ADDR'] ) ) {
                    $ip = sanitize_text_field( $_SERVER['HTTP_X_SERVER_ADDR'] );
                } elseif ( isset( $_SERVER['SERVER_ADDR'] ) ) {
                    $ip = sanitize_text_field( $_SERVER['SERVER_ADDR'] );
                } else {}

                echo '<div class="system-summary"><a href="' . esc_url( admin_url( 'site-health.php?tab=debug' ) ) . '">System</a>: ' . esc_html( $server_software ) . ' &#9642; PHP ' . esc_html( $php_version ) . ' (' . esc_html( php_sapi_name() ) . ') &#9642;' . esc_html( $db_server ) . ' &#9642; IP: ' . esc_html( $ip ) . '</div>';
                
                if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
                    // Reference: https://plugins.trac.wordpress.org/browser/my-simple-space/tags/1.2.9/my-simple-space.php#L145

                    // Get DB size
                    if ( false === get_transient( 'db_size_raw' ) ) {
                        $result = $wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );
                        $rows = count( $result );
                        $db_size = 0;

                        if ( $wpdb->num_rows > 0 ) {
                            foreach ( $result as $row ) {
                                $db_size += $row[ "Data_length" ] + $row[ "Index_length" ];
                            }
                            $db_size_formatted = size_format( $db_size, 2 );
                            // Set transient, expires in 24 hour
                            set_transient( 'db_size_raw', $db_size, 24 * HOUR_IN_SECONDS );
                        }                        
                    } else {
                        $db_size = get_transient( 'db_size_raw' );
                        $db_size_formatted = size_format( $db_size, 2 );
                    }
                    
                    // Get size of all folders
                    if( strpos( WP_CONTENT_DIR, ABSPATH ) !== false ) {
                        // WP_CONTENT_DIR is in ABSPATH
                        $all_folders_size = $this->get_dir_size__premium_only( ABSPATH );
                        $all_folders_size_formatted = size_format( $all_folders_size, 2 );
                    } else{
                        // WP_CONTENT_DIR is outside ABSPATH
                        $all_folders_size = $this->get_dir_size__premium_only( ABSPATH ) + $this->get_dir_size__premium_only( WP_CONTENT_DIR );
                        $all_folders_size_formatted = size_format( $all_folders_size, 2 );
                    }
                    
                    // Get size of entire site
                    $site_size = $db_size + $all_folders_size;
                    $site_size_formatted = size_format( $site_size, 2 );
                                        
                    // Get upload directory array without creating it
                    $uploads = wp_get_upload_dir();
                    $uploads_dir = $uploads['basedir'];

                    // WP Content and selected subfolders
                    $contents = array(
                        'wp-content'    => WP_CONTENT_DIR,
                        'plugins'       => WP_PLUGIN_DIR,
                        'themes'        => get_theme_root(),
                        'uploads'       => $uploads_dir,
                    );
                    
                    echo '<div class="site-sizes">';
                    echo '<div class="first-sizes">';
                    echo '<div class="size-item"><span class="item-heading">' . esc_html__( 'Entire site', 'admin-site-enhancements' ) . '</span> ' . $site_size_formatted . '</div>';
                    echo '<div class="size-item"><span class="item-heading">' . esc_html__( 'Database', 'admin-site-enhancements' ) . '</span> ' . $db_size_formatted . '</div>';
                    echo '<div class="size-item"><span class="item-heading">' . esc_html__( 'All files', 'admin-site-enhancements' ) . '</span> ' . $all_folders_size_formatted . '</div>';
                    echo '</div>';
                    echo '<div class="second-sizes">';
                    foreach ( $contents as $name => $value ) {
                        $name = __( $name, 'admin-site-enhancements' );
                        if ( false === get_transient( $value ) ) {
                            echo '<div class="size-item"><span class="item-heading">' . esc_html( $name ) . '</span> ' . esc_html( size_format( $this->get_dir_size__premium_only( $value ), 2 ) ) . '</div>'; 
                        } else {
                            echo '<div class="size-item"><span class="item-heading">' . esc_html( $name ) . '</span> ' . esc_html( size_format( get_transient( $value ), 2 ) ) . '</div>';
                        }
                    }
                    echo '</div>';
                    echo '</div>';
                }

            }
        }

    }

    /**
     * Get the size of a directory 
     * 
     * @link https://plugins.trac.wordpress.org/browser/my-simple-space/tags/1.2.9/my-simple-space.php#L214
     * @param  string $path     the path to the directory
     * @return integer          the size of the directory
     */
    public function get_dir_size__premium_only( $path ) {
        // Paths that should be excluded because they'll trigger open_basedir restriction errors / warnings
        if ( false !== strpos( $path, 'wp-nc-easywp' ) ) {
            return;
        } 

        // Add trailing slash to path if missing
        if ( substr( $path, -1 ) != '/' ) $path .= '/';
        
        $total_size = get_transient( $path );

        if ( false === $total_size ) {

            $total_size = 0;
            
            foreach( new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $path, \FilesystemIterator::SKIP_DOTS ) ) as $file ) {

                $should_process = true;

                $file_path = $file->getPathname();
                // Do not process files with file path that includes the following strings. These are not default WordPress files, and can contain symlinks that causes fatal error when they are affected by open_base_dir restrictions.
                if ( false !== strpos( $file_path, '.cagefs' ) 
                    || false !== strpos( $file_path, '.cl.selector' ) 
                    || false !== strpos( $file_path, '.clwpos' ) 
                    || false !== strpos( $file_path, '.well-known' ) 
                    || false !== strpos( $file_path, '.tmb' ) 
                    || false !== strpos( $file_path, '.wp-cli' ) 
                    || false !== strpos( $file_path, 'cgi-bin' ) 
                    || false !== strpos( $file_path, 'bacups' ) 
                    || false !== strpos( $file_path, 'wp-nc-easywp' ) 
                    || 'wp' == substr( $file_path, -2 ) // specific case where there is a /wp/ folder symlinked to wp-cli
                ) {
                    $should_process = false;
                }

                if ( $should_process ) {
                    $path_length = mb_strlen( $file_path );
                    if ( is_numeric( $path_length ) ) {
                        if ( intval( $path_length ) <= PHP_MAXPATHLEN ) {
                            if ( $file->isFile() ) {
                                $total_size += $file->getSize();
                            }
                        }
                    }
                }

            }

            // Set transient, expires in 24 hour
            set_transient( $path, $total_size, 24 * HOUR_IN_SECONDS );

            return $total_size;

        } else {

            return $total_size;
        }

    }
        
}