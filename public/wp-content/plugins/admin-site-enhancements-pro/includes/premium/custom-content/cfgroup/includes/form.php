<?php

class cfgroup_form
{

    public $used_types;
    public $assets_loaded;
    public $session;


    public function __construct() {
        $this->used_types = [];
        $this->assets_loaded = false;

        add_action( 'init', [ $this, 'save_form' ], 100 );
        add_action( 'saved_term', [ $this, 'pre_save_form' ], 100, 5 );
        add_action( 'init', [ $this, 'add_frontend_form_shortcode' ], 101 );
        add_action( 'admin_head', [ $this, 'head_scripts' ] );
        add_action( 'admin_print_footer_scripts', [ $this, 'footer_scripts' ] );
        add_action( 'admin_notices', [ $this, 'admin_notice' ] );
    }

    /**
     * Before saving term data
     * 
     * @since 7.8.10
     */
    public function pre_save_form( $term_id, $tt_id, $taxonomy, $update, $args ) {
        $cfgroup_data = isset( $args['cfgroup'] ) ? $args['cfgroup'] : array();
        $term_name = isset( $args['name'] ) ? $args['name'] : '';
        
        $saved_term_data = array(
            // 'update'    => $update, // If false, is adding new term. If true, is updating existing term.
            'term_id'   => $term_id,
            'term_name' => $term_name,
            'taxonomy'  => $taxonomy,
            'cfgroup'   => $cfgroup_data,
        );
        // vi( $saved_term_data, '', 'pre_save_form' );
        
        $this->save_form( $saved_term_data );
    }

    /**
     * Save custom field group form data
     * 
     * @param  array  $object_data may contain term data coming from the saved_term hook
     * @return [type]              [description]
     * @since 1.8.5
     */
    public function save_form( $object_data = array() ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX && empty( $object_data ) ) {
            // If there's an AJAX request with empty $object_data, we stop here.
            // When addomg mew taxonomy term via AJAX, $object_data contains term data, we continue
            return;
        }

        if ( isset( $_POST['wp-preview'] ) && 'dopreview' == $_POST['wp-preview'] ) {
            return;
        }
                        
        // Get the type of object the CFG form is used on: 'post' | 'taxonomy-term'
        if ( isset( $_POST['cfgroup']['save_for'] ) 
            || isset( $object_data['cfgroup']['save_for'] )
        ) {
            if ( isset( $_POST['cfgroup']['save_for'] ) ) {
                $save_for = $_POST['cfgroup']['save_for'];
            } else if ( isset( $object_data['cfgroup']['save_for'] ) ) {
                $save_for = $object_data['cfgroup']['save_for'];
            } else {
                $save_for = 'post';
            }
            // vi( $save_for );
            
            switch ( $save_for ) {
                case 'post':
                    $is_saving_for_post = true;
                    $is_saving_for_term = false;
                    break;
                    
                case 'term':
                    $is_saving_for_post = false;
                    $is_saving_for_term = true;
                    break;
            }
        } else {
            $is_saving_for_post = true;
            $is_saving_for_term = false;
        }

        if ( $is_saving_for_post ) {
            $this->session = new cfgroup_session();
        }
        
        // Save the form
        if ( isset( $_POST['cfgroup']['save'] ) 
            || ( ! empty( $object_data ) && isset( $object_data['cfgroup']['save'] ) )
        ) {
            if ( wp_verify_nonce( $_POST['cfgroup']['save'], 'cfgroup_save_input' ) 
                || ( isset( $object_data['cfgroup']['save'] ) && wp_verify_nonce( $object_data['cfgroup']['save'], 'cfgroup_save_input' ) )
            ) {
                
                if ( isset( $_POST['cfgroup']['input'] ) ) {
                    $field_data = $_POST['cfgroup']['input'];
                } else if ( isset( $object_data['cfgroup']['input'] ) ) {
                    $field_data = $object_data['cfgroup']['input'];
                }
                // vi( $field_data );
                
                if ( $is_saving_for_post ) {
                    $session = $this->session->get();
                    // vi( $session );

                    if ( empty( $session ) ) {
                        die( 'Your session has expired.' );
                    }
                }

                if ( $is_saving_for_post
                    && isset( $_POST['cfgroup']['captcha_type'] ) 
                    && ! empty( $_POST['cfgroup']['captcha_type'] )
                    && in_array( $_POST['cfgroup']['captcha_type'], array( 'altcha', 'recaptcha', 'turnstile' ) )
                ) {

                    switch ( $_POST['cfgroup']['captcha_type'] ) {
                        case 'altcha':
                            if ( isset( $_POST['altcha'] ) ) {
                                $altcha_payload = isset( $_POST['altcha'] ) ? trim( sanitize_text_field( $_POST['altcha'] ) ) : '';
                                $altcha = new ASENHA\Classes\CAPTCHA_Protection_ALTCHA;
                                if ( false === $altcha->verify( $altcha_payload ) ) {
                                    wp_die( __( 'CAPTCHA verification has failed.', 'admin-site-enhancements' ) );
                                }
                            } else {
                                wp_die( __( 'CAPTCHA verification has failed.', 'admin-site-enhancements' ) );
                            }
                            break;

                        case 'recaptcha':
                            if ( isset( $_POST['g-recaptcha-response'] ) ) {
                                $recaptcha = new ASENHA\Classes\CAPTCHA_Protection_reCAPTCHA;
                                if ( ! $recaptcha->verify_recaptcha( 'post_cf' ) ) {
                                    wp_die( __( 'CAPTCHA verification has failed.', 'admin-site-enhancements' ) );
                                }
                            } else {
                                wp_die( __( 'CAPTCHA verification has failed.', 'admin-site-enhancements' ) );
                            }
                            break;

                        case 'turnstile':
                            if ( isset( $_POST['cf-turnstile-response'] ) ) {
                                $turnstile = new ASENHA\Classes\CAPTCHA_Protection_Turnstile;
                                $check = $turnstile->turnstile_check();
                                $success = $check['success'];
                                if ( $success != true ) {
                                    wp_die( __( 'CAPTCHA verification has failed.', 'admin-site-enhancements' ) );
                                }
                                
                            } else {
                                wp_die( __( 'CAPTCHA verification has failed.', 'admin-site-enhancements' ) );
                            }
                            break;
                    }

                }

                if ( $is_saving_for_post ) {
                    $field_groups = isset( $session['field_groups'] ) ? $session['field_groups'] : [];
                } else if ( $is_saving_for_term ) {
                    if ( isset( $_POST['taxonomy'] ) ) {
                        $taxonomy = $_POST['taxonomy'];
                    } else if ( isset( $object_data['taxonomy'] ) ) {
                        $taxonomy = $object_data['taxonomy'];
                    } else {
                        $taxonomy = '';
                    }
                    $field_groups = CFG()->api->get_matching_groups_for_taxonomy( $taxonomy );
                }

                // Sanitize field groups
                foreach ( $field_groups as $key => $val ) {
                    $field_groups[$key] = (int) $val;
                }
                // vi( $field_groups );

                if ( $is_saving_for_post ) {
                    $post_data = [];

                    // Form settings are session-based for added security
                    $post_id = (int) $session['post_id'];
    
                    // Post Title
                    if ( isset( $_POST['cfgroup']['post_title'] ) ) {
                        $post_data['post_title'] = wp_kses_post( stripslashes( $_POST['cfgroup']['post_title'] ) );
                    }

                    // Post Content
                    if ( isset( $_POST['cfgroup']['post_content'] ) ) {
                        $post_data['post_content'] = wp_kses_post( stripslashes( $_POST['cfgroup']['post_content'] ) );
                    }

                    // New posts
                    if ( $post_id < 1 ) {
                        // Post type
                        if ( isset( $session['post_type'] ) ) {
                            $post_data['post_type'] = $session['post_type'];
                        }

                        // Post status
                        if ( isset( $session['post_status'] ) ) {
                            $post_data['post_status'] = $session['post_status'];
                        }
                    }
                    else {
                        $post_data['ID'] = $post_id;
                    }                    
                } else if ( $is_saving_for_term ) {
                    $term_data = [];

                    if ( isset( $object_data['term_id'] ) ) {
                        // When adding a new term
                        if ( isset( $object_data['term_id'] ) ) {
                            $term_id = $object_data['term_id'];
                        } else {
                            $term_id = 0;
                        }

                        if ( isset( $object_data['term_name'] ) ) {
                            $term_name = $object_data['term_name'];
                        } else {
                            $term_name = '';
                        }
                    } else if ( isset( $_POST['tag_ID'] ) ) {
                        // WHen editing an existing term
                        $term_id = (int) $_POST['tag_ID'];
                        $term_name = isset( $_POST['name'] ) ? $_POST['name'] : '';
                    }
                                        
                    $term_data['ID'] = $term_id;
                    $term_data['name'] = $term_name;
                    $term_data['taxonomy'] = $taxonomy;
                    // vi( $term_data );
                }

                $options = [
                    'format'        => 'input',
                    'field_groups'  => $field_groups
                ];
                // vi( $options );

                if ( $is_saving_for_post ) {
                    // Hook parameters
                    $hook_params = [
                        'field_data'    => $field_data,
                        'post_data'     => $post_data,
                        'options'       => $options,
                    ];                    
                } else if ( $is_saving_for_term ) {
                    $hook_params = [
                        'field_data'    => $field_data,
                        'term_data'     => $term_data,
                        'options'       => $options,                    
                    ];
                }

                // Pre-save hook
                do_action( 'cfgroup_pre_save_input', $hook_params );

                // if ( $is_saving_for_post ) {
                //     vi( $field_data, '', 'saving a post' );
                //     vi( $post_data );
                //     vi( $options );
                // } else if ( $is_saving_for_term ) {
                //     vi( $field_data, '', 'saving a term' );
                //     vi( $term_data );
                //     vi( $options );                    
                // }

                // Save the input values
                if ( $is_saving_for_post ) {
                    $hook_params['post_data']['ID'] = CFG()->save(
                        $field_data,
                        $post_data,
                        $options
                    );
                } else if ( $is_saving_for_term ) {
                    $hook_params['term_data']['ID'] = CFG()->save_for_term(
                        $field_data,
                        $term_data,
                        $options
                    );                    
                }

                // After-save hook
                do_action( 'cfgroup_after_save_input', $hook_params );

                if ( $is_saving_for_post ) {
                    // Delete expired sessions
                    $this->session->cleanup();

                    // Redirect public forms
                    if ( true === $session['front_end'] ) {
                        $redirect_url = $_SERVER['REQUEST_URI'];
                        if ( ! empty( $session['confirmation_url'] ) ) {
                            $redirect_url = $session['confirmation_url'];
                        }

                        header( 'Location: ' . $redirect_url );
                        exit;
                    }                    
                }
            }
        }
    }


    /**
     * Load form dependencies
     * @since 1.8.5
     */
    public function load_assets() {
        if ( $this->assets_loaded ) {
            return;
        }
        
        $this->assets_loaded = true;

        add_action( 'wp_head', [ $this, 'head_scripts' ], 0 );
        add_action( 'wp_footer', [ $this, 'footer_scripts' ], 25 );

        // We force loading the uncompressed version of TinyMCE. This ensures we load 'wp-tinymce-root' and then 'wp-tinymce', 
        // which prevents issue where the TinyMCE editor is unusable in some scenarios
        $wp_scripts = wp_scripts();
        $wp_scripts->remove( 'wp-tinymce' );
        wp_register_tinymce_scripts( $wp_scripts, true );

        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'cfgroup-fields', CFG_URL . '/assets/js/fields.js', [ 'jquery' ], CFG_VERSION ); // Has repeater field add/remove row JS
        wp_enqueue_script( 'cfgroup-validation', CFG_URL . '/assets/js/validation.js', [ 'jquery' ], CFG_VERSION );
        wp_enqueue_script( 'cfgroup-select2', CFG_URL . '/assets/js/select2/select2.min.js', [ 'jquery' ], CFG_VERSION );
        wp_enqueue_script( 'jquery-powertip', CFG_URL . '/assets/js/jquery-powertip/jquery.powertip.min.js', [ 'jquery' ], CFG_VERSION );
        wp_enqueue_style( 'cfgroup-select2', CFG_URL . '/assets/js/select2/select2.css', [], CFG_VERSION );
        wp_enqueue_style( 'jquery-powertip', CFG_URL . '/assets/js/jquery-powertip/jquery.powertip.css', [], CFG_VERSION );
        wp_enqueue_style( 'cfgroup-input', CFG_URL . '/assets/css/input.css', [], CFG_VERSION );
        if ( ! is_user_logged_in() ) {
            wp_enqueue_style( 'dashicons-editor-buttons', CFG_URL . '/assets/css/dashicons-editor.css', [], CFG_VERSION );
        }

    }


    /**
     * Handle front-end validation
     * @since 1.8.8
     */
    function head_scripts() {
    ?>

<script>
var CFG = CFG || {};
CFG['get_field_value'] = {};
CFG['repeater_buffer'] = [];
</script>

    <?php
    }

    /**
     * Allow for custom client-side validators
     * @since 1.9.5
     */
    function footer_scripts() {
        do_action( 'cfgroup_custom_validation' );
    }


    /**
     * Add an admin notice to be displayed in the event of
     * validation errors
     * @since 2.6
     */
    function admin_notice() {
        $screen = get_current_screen();

        if ( !isset($screen->base) || $screen->base !== 'post' ) {
            return;
        }

        echo '<div class="notice notice-error" id="cfgroup-validation-admin-notice" style="display: none;"><p><strong>';
        echo __( 'One (or more) of your fields had validation errors. More information is available below.', 'admin-site-enhancements' );
        echo '</strong></p></div>';
    }


    /**
     * Add shortcode to render frontend form
     * 
     * @since 7.8.4
     */
    public function add_frontend_form_shortcode() {
        add_shortcode( 'post_cf_form', [ $this, 'post_cf_form_callback' ] );
    }

    /**
     * Callback to render frontend form
     * 
     * @since 7.8.4
     */
    public function post_cf_form_callback( $atts ) {
        $atts = shortcode_atts( array(
            'type'                  => 'create',
            'post_id'               => '', // empty | 'in_url' | the post ID
            'post_type'             => '', // the post type slug, e.g. movie
            'post_title'            => 'show', // 'show' | 'hide'
            'post_content'          => 'hide', // 'show' | 'hide'
            'post_status'           => 'draft', // 'publish' | 'draft' | 'pending' | 'private'
            'field_groups'          => '', // Should be comma-separated CFG IDs
            'excluded_fields'       => '', // Should be comma-separated field names, e.g. 'movie_release_date, movie_mpaa_rating'
            'captcha'               => '', // altcha | recaptcha | turnstile
            'submit_label'          => __( 'Submit', 'admin-site-enhancements' ),
            'confirmation_message'  => 'Success.',
            'confirmation_url'      => '',
            'front_end'             => 'true',
        ), $atts, 'post_cf_form' );
        
        // Define value for post_id parameter
        switch ( $atts['type'] ) {
            case 'create':
                $post_id = false;        
                break;
                
            case 'edit':
                if ( ! empty( $atts['post_id'] && is_numeric( $atts['post_id'] ) ) ) {
                    $post_id = intval( $atts['post_id'] );
                } else if ( 'in_url' == $atts['post_id'] && ! empty( $_GET['post_id'] ) && is_numeric( $_GET['post_id'] ) ) {
                    $post_id = intval( $_GET['post_id'] );
                } else {
                    $post_id = false;
                }
                break;
        }

        // Define value for post_title parameter
        if ( 'show' == $atts['post_title'] ) {
            $post_title = true;
        } else if ( 'hide' == $atts['post_title'] ) {
            $post_title = false;
        } else {
            $post_title = false;            
        }

        // Define value for post_content parameter
        if ( 'show' == $atts['post_content'] ) {
            $post_content = true;
        } else if ( 'hide' == $atts['post_content'] ) {
            $post_content = false;
        } else {
            $post_content = false;            
        }
        
        // Define value for post_type parameter
        $registered_post_types = get_post_types( array(), 'names' );
        $registered_post_types = array_values( $registered_post_types );
        if ( ! empty( $atts['post_type'] ) && in_array( $atts['post_type'], $registered_post_types ) ) {
            $post_type = $atts['post_type'];
        } else {
            $post_type = 'post';
        }

        // Define value for post_status parameter
        if ( ! empty( $atts['post_status'] ) && in_array( $atts['post_status'], array( 'publish', 'draft', 'pending', 'private' ) ) ) {
            $post_status = $atts['post_status'];
        } else {
            $post_status = 'draft';
        }

        // Define value for field_groups parameter
        if ( ! empty( $atts['field_groups'] ) ) {
            $field_groups = explode( ',', str_replace( ' ', '', $atts['field_groups'] ) );
        } else {
            $field_groups = array();
        }

        // Define value for excluded_fields parameter
        if ( ! empty( $atts['excluded_fields'] ) ) {
            $excluded_fields = explode( ',', str_replace( ' ', '', $atts['excluded_fields'] ) );
        } else {
            $excluded_fields = array();
        }

        // Define value for front_end parameter
        if ( 'true' == $atts['front_end'] ) {
            $front_end = true;
        } else {
            $front_end = false;
        }
        
        // Define value for CAPTCHA type when not supplied by shortcode's captcha parameter
        if ( empty( $atts['captcha'] ) ) {
            $options = get_option( 'admin_site_enhancements', array() );
            $captcha = isset( $options['captcha_protection_types'] ) ? $options['captcha_protection_types'] : 'altcha';
        } else {
            $captcha = $atts['captcha'];
        }
        
        $params = array(
            'post_id'               => $post_id, // false = new entries
            'post_type'             => $post_type,
            'post_title'            => $post_title,
            'post_content'          => $post_content,
            'post_status'           => $post_status,
            'field_groups'          => $field_groups, // indexed array of group IDs
            'excluded_fields'       => $excluded_fields, // indexed array of field names
            'captcha'               => $captcha,
            'submit_label'          => $atts['submit_label'],
            'confirmation_message'  => $atts['confirmation_message'],
            'confirmation_url'      => $atts['confirmation_url'],
            'front_end'             => $front_end,
        );

        if ( 'edit' === $atts['type'] && false === $post_id ) {
            wp_enqueue_style( 'cfgroup-frontend', CFG_URL . '/assets/css/frontend.css', [], CFG_VERSION );
            return '<div class="notice notice-warning"><p>' . __( 'Post ID has not been set...', 'admin-site-enhancements' ) . '</p></div>'; // Do not render form if trying to edit a post but there is no post ID
        } else {
            return $this->render( $params );
        }
    }

    /**
     * Render the HTML input form via CFG()->form()
     * @param array $params
     * @return string form HTML code
     * @since 1.8.5
     */
    public function render( $params ) {
        if ( isset( $params['front_end'] ) && true === $params['front_end'] ) {
            $this->load_assets();
        }

        global $post;
        // vi( $params, '', 'custom' );

        $defaults = [
            'object_type'           => 'post',
            'type'                  => 'create',
            'post_id'               => false, // false = new post
            'post_type'             => '',
            'post_title'            => true,
            'post_content'          => false,
            'post_status'           => 'draft',
            'term_id'               => false, // false = new term
            'field_groups'          => [], // group IDs
            'excluded_fields'       => [],
            'captcha'               => '',
            'submit_label'          => __( 'Submit', 'admin-site-enhancements' ),
            'confirmation_message'  => '',
            'confirmation_url'      => '',
            'front_end'             => true,
        ];

        $params = array_merge( $defaults, $params );

        // Keep track of field validators
        CFG()->validators = [];
        
        // Check whether we should render for a post (CFG placement: 'posts' | 'options-pages'), or a term (CFG placement: 'taxonomy-terms')
        if ( 'post' == $params['object_type'] ) {
            $post_id = (int) $params['post_id'];

            if ( 0 < $post_id ) {
                $post = get_post( $post_id );
            }
        } else if ( 'term' == $params['object_type'] ) {
            $term_id = (int) $params['term_id'];
        } else {}

        if ( empty( $params['field_groups'] ) ) {
            if ( 'post' == $params['object_type'] ) {
                if ( 0 < $post_id ) {
                    $field_groups = CFG()->api->get_matching_groups( $post_id, true );
                    $field_groups = array_keys( $field_groups );
                } else {
                    $field_groups = array();

                    $args = array(
                        'post_type' => 'asenha_cfgroup',
                        'post_status' => 'publish',
                        'numberposts' => -1,
                    );
                    
                    $cfgroups = get_posts( $args );

                    if ( ! empty( $cfgroups ) ) {
                        foreach ( $cfgroups as $cfgroup ) {
                            $cfgroup_rules = get_post_meta( $cfgroup->ID, 'cfgroup_rules', true );
                            $cfgroup_extras = get_post_meta( $cfgroup->ID, 'cfgroup_extras', true );
                            $cfgroup_order = $cfgroup_extras['order'];

                            $cfgroup_post_types = array();

                            if ( isset( $cfgroup_rules['placement'] )
                                && '==' == $cfgroup_rules['placement']['operator'] 
                                && 'posts' == $cfgroup_rules['placement']['values'] 
                            ) {
                                if ( isset( $cfgroup_rules['post_types'] ) && '==' == $cfgroup_rules['post_types']['operator'] ) {
                                    $cfgroup_post_types = $cfgroup_rules['post_types']['values'];
                                }                            
                            }                            

                            if ( in_array( $params['post_type'], $cfgroup_post_types ) ) {
                                $field_groups[$cfgroup_order] = $cfgroup->ID;
                            }
                            
                            ksort( $field_groups ); // sort by key, ascending
                        }
                    }
                }
            } else if ( 'term' == $params['object_type'] ) {
                // This is a non-existent scenario as $params['field_groups'] is never empty when object_type is 'term'.
                // Please look at init.php add_term_fields() and edit_term_fields() to see that field group(s) are always identifiable
            }
        } else {
            switch ( $params['object_type'] ) {
                case 'post':
                    $field_groups[] = $params['field_groups'];
                    break;
                    
                case 'term':
                    $field_groups = $params['field_groups'];
                    break;
            }
        }

        $input_fields = [];
        if ( ! empty( $field_groups ) ) {
            foreach ( $field_groups as $field_group ) {
                // vi( $field_group );
                $input_fields[$field_group] = CFG()->api->get_input_fields( [ 'group_id' => $field_group ], $params['object_type'] );
            }
        }
        // vi( $input_fields );

        // Hook to allow for overridden field settings
        $input_fields = apply_filters( 'cfgroup_pre_render_fields', $input_fields, $params );

        // The SESSION should contain all applicable field group IDs. Since add_meta_box only
        // passes 1 field group at a time, we use CFG()->group_ids from admin_head.php
        // to store all group IDs needed for the SESSION.
        $all_group_ids = ( false === $params['front_end'] ) ? CFG()->group_ids : $field_groups;

        if ( 'post' == $params['object_type'] ) {
            $session_data = [
                'post_id'               => $post_id,
                'post_type'             => $params['post_type'],
                'post_status'           => $params['post_status'],
                'field_groups'          => $all_group_ids,
                'confirmation_message'  => $params['confirmation_message'],
                'confirmation_url'      => $params['confirmation_url'],
                'front_end'             => $params['front_end'],
            ];

            // Set the SESSION
            $this->session->set( $session_data );            
        }
        
        if ( false !== $params['front_end'] ) {
        ob_start();
        ?>

            <div class="cfgroup_input no_box">
                <form id="post" method="post" action="">

                    <?php
        }
                    // For frontend posting of posts
                    if ( false !== $params['post_title'] && true === $params['front_end'] ) {
                    ?>

                    <div class="field post-title" data-validator="required">
                        <label><?php echo esc_html__( 'Title', 'admin-site-enhancements' ); ?></label>
                        <input type="text" name="cfgroup[post_title]" value="<?php echo empty( $post_id ) ? '' : esc_attr( $post->post_title ); ?>" />
                    </div>

                    <?php
                    }

                    // For frontend posting of posts
                    if ( false !== $params['post_content'] && true === $params['front_end'] ) {
                    ?>

                    <div class="field post-content-title">
                        <label><?php echo esc_html__( 'Content', 'admin-site-enhancements' ); ?></label>
                        <textarea id="asenha-post-content" name="cfgroup[post_content]"><?php echo empty( $post_id ) ? '' : esc_textarea( $post->post_content ); ?></textarea>
                    </div>

                    <?php
                    $editor_settings = array(
                        'media_buttons'     => false,
                        'textarea_rows'     => 5,
                        'tiny_mce'          => true,
                        'tinymce'           => array(
                            // 'toolbar1'      => 'bold,italic,underline,strikethrough,superscript,subscript,blockquote,bullist,numlist,alignleft,aligncenter,alignjustify,alignright,alignnone,link,unlink,fontsizeselect,forecolor,undo,redo,removeformat,code',
                            'toolbar1'      => 'bold,italic,underline,strikethrough,blockquote,bullist,numlist,alignleft,aligncenter,alignjustify,alignright,link,undo,redo,removeformat,fullscreen',
                            // 'content_css'   => ASENHA_URL . 'assets/css/settings-wpeditor.css',
                        ),
                        'editor_css'        => '',
                        'wpautop'           => true,
                        'quicktags'         => false,
                        'default_editor'    => 'tinymce', // 'tinymce' or 'html'
                    );
                    
                    wp_editor( $post->post_content, 'asenha-post-content', $editor_settings );
                    }
                    
                    $is_first_field = false;

                    // Detect tabs
                    $tabs = [];

                    foreach ( $input_fields as $field_group => $fields ) {
                        foreach( $fields as $key => $field ) {
                            if ( 'tab' == $field->type ) {
                                $tabs[$field_group][] = $field;
                            }
                        }
                    }
                                        
                    do_action( 'cfgroup_form_before_fields', $params, [
                        'group_ids'     => $all_group_ids,
                        'input_fields'  => $input_fields
                    ] );

                    foreach ( $input_fields as $field_group => $fields ) {

                        if ( isset( $params['front_end'] ) && true === $params['front_end'] ) {
                            ?>
                            <div class="field field-group-title">
                                <label><?php echo get_the_title( $field_group ); ?></label>
                            </div>
                            <?php
                        }

                        $is_first_tab = true;

                        if ( empty( $tabs[$field_group] ) ) {
                            echo '<div class="fields-wrapper">';                        
                        }

                        // Add any necessary head scripts
                        foreach ( $fields as $key => $field ) {
                            
                            // Exclude fields
                            if ( in_array( $field->name, (array) $params['excluded_fields'] ) ) {
                                continue;
                            }

                            // Skip missing field types
                            if ( ! isset( CFG()->fields[ $field->type ] ) ) {
                                continue;
                            }

                            // Output tabs
                            if ( 'tab' == $field->type && $is_first_tab ) {
                                echo '<div class="cfgroup-tabs">';
                                foreach ( $tabs[$field_group] as $key => $tab ) {
                                    echo '<div class="cfgroup-tab" rel="' . $tab->name . '">' . $tab->label . '</div>';
                                }
                                echo '</div>';
                                $is_first_tab = false;
                            }

                            // Keep track of active field types
                            if ( ! isset( $this->used_types[ $field->type ] ) ) {
                                CFG()->fields[ $field->type ]->input_head( $field );
                                $this->used_types[ $field->type ] = true;
                            }

                            $validator = '';

                            if ( in_array( $field->type, [ 'relationship', 'user', 'repeater' ] ) ) {
                                $min = empty( $field->options['limit_min'] ) ? 0 : (int) $field->options['limit_min'];
                                $max = empty( $field->options['limit_max'] ) ? 0 : (int) $field->options['limit_max'];
                                $validator = "limit|$min,$max";
                            }

                            if ( isset( $field->options['required'] ) && 0 < (int) $field->options['required'] ) {
                                if ( 'date' == $field->type ) {
                                    $validator = 'valid_date';
                                }
                                elseif ( 'color' == $field->type ) {
                                    $validator = 'valid_color';
                                }
                                else {
                                    $validator = 'required';
                                }
                            }

                            if ( ! empty( $validator ) ) {
                                CFG()->validators[ $field->name ] = [
                                    'rule'  => $validator,
                                    'type'  => $field->type
                                ];
                            }

                            // Ignore sub-fields
                            if ( 1 > (int) $field->parent_id ) {

                                // Tab handling
                                if ( 'tab' == $field->type ) {

                                    // Close the previous tab
                                    if ( $field->name != $tabs[$field_group][0]->name ) {
                                        echo '</div>'; // Close .fields-wrapper
                                        echo '</div>'; // Close previous tab
                                    }
                                    echo '<div class="cfgroup-tab-content cfgroup-tab-content-' . esc_attr( $field->name ) . '">';

                                    if ( ! empty( $field->notes ) ) {
                                        echo '<div class="cfgroup-tab-notes">' . esc_html( $field->notes ) . '</div>';
                                    }

                                    echo '<div class="fields-wrapper">';
                                } 
                                // Render fields other than tabs
                                else {
                                    switch ( $field->type ) {
                                        case 'line_break';
                                            $additional_classes = ' row-line-break';
                                            break;

                                        case 'heading';
                                            $additional_classes = ' row-heading';
                                            break;
                                            
                                        default:
                                            $additional_classes = '';
                                    }

                                    ?>

                                    <div class="field-column-<?php echo $field->column_width; ?><?php echo esc_attr( $additional_classes ); ?>">
                                        <div class="field field-<?php echo esc_attr( $field->name ); ?>" data-type="<?php echo esc_attr( $field->type ); ?>" data-name="<?php echo esc_attr( $field->name ); ?>">
                                            <?php if ( 'repeater' == $field->type ) : ?>
                                            <a href="javascript:;" class="cfgroup_repeater_toggle" title="<?php esc_html_e( 'Toggle row visibility', 'admin-site-enhancements' ); ?>"></a>
                                            <?php endif; ?>

                                            <?php if ( ! empty( $field->label ) && 'line_break' != $field->type ) : ?>
                                            <label class="field-label"><?php echo esc_html( $field->label ); ?></label>
                                            <?php endif; ?>

                                            <?php if ( ! empty( $field->notes ) ) : ?>
                                            <p class="notes"><?php echo esc_html( $field->notes ); ?></p>
                                            <?php endif; ?>

                                            <div class="cfgroup_<?php echo esc_attr( $field->type ); ?>">

                                                <?php
                                                CFG()->create_field( [
                                                    'id'            => $field->id,
                                                    'group_id'      => $field->group_id,
                                                    'type'          => $field->type,
                                                    'input_name'    => "cfgroup[input][$field->id][value]",
                                                    'input_class'   => $field->type,
                                                    'options'       => $field->options,
                                                    'value'         => $field->value,
                                                    'notes'         => $field->notes,
                                                    'column_width'  => $field->column_width,
                                                ] );
                                                ?>

                                            </div>
                                        </div>
                                    </div>

                                    <?php

                                }
                                                            
                            }
                        }

                        // Make sure to close tabs
                        if ( ! empty( $tabs[$field_group] ) ) {
                            echo '</div>'; // Close .fields-wrapper
                            echo '</div>'; // Close tabs
                        } else {
                            echo '</div>'; // Close .fields-wrapper                                                
                        }                        
                    }

                    if ( ! empty( $params['captcha'] ) 
                        && in_array( $params['captcha'], array( 'altcha', 'recaptcha', 'turnstile' ) ) 
                        && ! is_user_logged_in() 
                    ) {
                        switch ( $params['captcha'] ) {
                            case 'altcha':
                                asenha_register_altcha_assets__premium_only();
                                asenha_enqueue_altcha_assets__premium_only();
                                $altcha = new ASENHA\Classes\CAPTCHA_Protection_ALTCHA;
                                echo $altcha->altcha_wordpress_render_widget( 'captcha' );
                                break;

                            case 'recaptcha':
                                $options = get_option( 'admin_site_enhancements', array() );
                                $recaptcha_type = isset( $options['recaptcha_types'] ) ? $options['recaptcha_types'] : 'v2_checkbox';
                                // Enqueue scripts and styles for reCAPTCHA v2 "I'm not a robot" checbox
                                // v3 scripts/styles is inserted inline via CAPTCHA_Protection_reCAPTCHA->get_recaptcha_html()
                                if ( in_array( $recaptcha_type, array( 'v2_checkbox' ) ) ) {
                                    asenha_register_recaptcha_assets__premium_only();
                                    asenha_enqueue_recaptcha_assets__premium_only();        
                                }
                                $recaptcha = new ASENHA\Classes\CAPTCHA_Protection_reCAPTCHA;
                                $recaptcha->render_recaptcha_widget( 'post_cf' );
                                break;

                            case 'turnstile':
                                asenha_register_turnstile_assets__premium_only();
                                asenha_enqueue_turnstile_assets__premium_only();        
                                $turnstile = new ASENHA\Classes\CAPTCHA_Protection_Turnstile;
                                // $turnstile->show_turnstile_widget( '#post-cf-submit', 'turnstileWPCallback', 'post-cf', '-' . wp_rand(), 'post-cf', 'normal', true );
                                $turnstile->show_turnstile_widget( '', 'turnstileWPCallback', 'post-cf', '-' . wp_rand(), 'post-cf', 'normal', true );
                                break;
                        }
                    }
                    
                    do_action( 'cfgroup_form_after_fields', $params, [
                        'group_ids'     => $all_group_ids,
                        'input_fields'  => $input_fields
                    ] );
                    ?>

                    <script>
                    (function($) {
                        if ( typeof CFG === 'undefined' ) {
                            var CFG = CFG || {};
                        }

                        CFG.field_rules = CFG.field_rules || {};
                        $.extend( CFG.field_rules, <?php echo json_encode( CFG()->validators ); ?> );
                    })(jQuery);
                    </script>
                    <input type="hidden" name="cfgroup[save]" value="<?php echo wp_create_nonce( 'cfgroup_save_input' ); ?>" />
                    <input type="hidden" name="cfgroup[save_for]" value="<?php echo esc_attr( $params['object_type'] ); ?>" />
                    <input type="hidden" name="cfgroup[session_id]" value="<?php echo $this->session->session_id; ?>" />
                    <?php
                    if ( ! empty( $params['captcha'] ) 
                        && in_array( $params['captcha'], array( 'altcha', 'recaptcha', 'turnstile' ) ) 
                        && ! is_user_logged_in() 
                    ) {                    
                    ?>
                    <input type="hidden" name="cfgroup[captcha_type]" value="<?php echo esc_attr( $params['captcha'] ); ?>" />
                    <?php
                    }
                    ?>
                    <?php if ( false !== $params['front_end'] ) : ?>

                    <input id="post-cf-submit" type="submit" value="<?php echo esc_attr( $params['submit_label'] ); ?>" />
                </form>
            </div>

        <?php endif;

            if ( isset( $params['front_end'] ) && true === $params['front_end'] ) {
                return ob_get_clean(); // for the [post_cf_form] shortcode
            } else {
                echo ob_get_clean(); // for the meta box in post edit screen
            }
        }
}

CFG()->form = new cfgroup_form();
