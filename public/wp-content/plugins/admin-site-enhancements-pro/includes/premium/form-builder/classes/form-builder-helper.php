<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Helper {

    public static function get_fields_array( $form_id ) {
        $fields = Form_Builder_Fields::get_form_fields( $form_id );

        $values['fields'] = array();

        if ( empty( $fields ) )
            return $values;

        foreach ( (array) $fields as $field ) {
            $field_array = Form_Builder_Fields::covert_field_obj_to_array( $field );
            $values['fields'][] = $field_array;
        }

        $form_options_defaults = self::get_form_options_default();

        return array_merge( $form_options_defaults, $values );
    }

    /* Sanitizes value and returns param value */

    public static function get_var( $param, $sanitize = 'sanitize_text_field', $default = '' ) {
        $value = ( ( $_GET && isset( $_GET[$param] ) ) ? wp_unslash( $_GET[$param] ) : $default );
        return self::sanitize_value( $sanitize, $value );
    }

    public static function get_post( $param, $sanitize = 'sanitize_text_field', $default = '', $sanitize_array = array() ) {
        $value = ( isset( $_POST[$param] ) ? wp_unslash( $_POST[$param] ) : $default );

        if ( ! empty( $sanitize_array ) && is_array( $value ) ) {
            return self::sanitize_array( $value, $sanitize_array );
        }

        return self::sanitize_value( $sanitize, $value );
    }

    public static function sanitize_value( $sanitize, &$value ) {
        if ( ! empty( $sanitize ) ) {
            if ( is_array( $value ) ) {
                $temp_values = $value;
                foreach ( $temp_values as $k => $v ) {
                    $value[$k] = self::sanitize_value( $sanitize, $value[$k] );
                }
            } else {
                $value = call_user_func( $sanitize, ( $value ? htmlspecialchars_decode( $value ) : '' ) );
            }
        }

        return $value;
    }

    public static function get_unique_key( $table_name, $column_name, $limit = 6 ) {
        $values = 'ABCDEFGHIJKLMOPQRSTUVXWYZ0123456789';
        $count = strlen( $values );
        $count--;
        $key = '';
        for ( $x = 1; $x <= $limit; $x++) {
            $rand_var = rand( 0, $count );
            $key .= substr( $values, $rand_var, 1 );
        }

        $key = strtolower( $key );
        $existing_keys = self::check_table_keys( $table_name, $column_name );

        if ( in_array( $key, $existing_keys ) ) {
            self::get_unique_key( $table_name, $column_name, $limit = 6 );
        }

        return $key;
    }

    public static function check_table_keys( $table_name, $column_name ) {
        global $wpdb;
        $tbl_name = $wpdb->prefix . $table_name;
        $query = $wpdb->prepare("SELECT {$column_name} FROM {$tbl_name} WHERE id!=%d", 0 );
        $results = $wpdb->get_results( $query, ARRAY_A);
        return array_column( $results, $column_name );
    }

    public static function is_admin_page( $page = 'formbuilder' ) {
        $get_page = self::get_var( 'page', 'sanitize_title' );
        if ( is_admin() && $get_page === $page ) {
            return true;
        }

        return false;
    }

    public static function is_preview_page() {
        $action = self::get_var( 'action', 'sanitize_title' );
        return ( is_admin() && ( $action == 'formbuilder_preview' ) );
    }

    public static function is_form_builder_page() {
        $action = self::get_var( 'formbuilder_action', 'sanitize_title' );
        $builder_actions = self::get_form_builder_actions();
        return self::is_admin_page( 'formbuilder' ) && ( in_array( $action, $builder_actions ) );
    }

    public static function is_form_listing_page() {
        if ( ! self::is_admin_page( 'formbuilder' ) ) {
            return false;
        }

        $action = self::get_var( 'formbuilder_action', 'sanitize_title' );
        $builder_actions = self::get_form_builder_actions();
        return ! $action || in_array( $action, $builder_actions );
    }

    public static function get_form_builder_actions() {
        return array( 'edit', 'settings', 'style' );
    }

    public static function start_field_array( $field ) {
        return array(
            'id' => $field->id,
            'default_value' => $field->default_value,
            'name' => $field->name,
            'description' => $field->description,
            'options' => $field->options,
            'required' => $field->required,
            'field_key' => $field->field_key,
            'field_order' => $field->field_order,
            'form_id' => $field->form_id,
        );
    }

    public static function show_search_box( $atts ) {
        $defaults = array(
            'placeholder' => '',
            'tosearch' => '',
            'text' => esc_html__( 'Search', 'admin-site-enhancements' ),
            'input_id' => '',
        );
        $atts = array_merge( $defaults, $atts );
        $class = 'fb-search-fields-input';
        $input_id = $atts['input_id'] . '-search-input';
        ?>
        <div class="fb-search-fields">
            <?php echo wp_kses( Form_Builder_Icons::get( 'search' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?>
            <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php echo esc_attr( $atts['placeholder'] ); ?>" class="<?php echo esc_attr( $class ); ?>" data-tosearch="<?php echo esc_attr( $atts['tosearch'] ); ?>" <?php if ( ! empty( $atts['tosearch'] ) ) { ?> autocomplete="off" <?php } ?> />
            <?php if ( empty( $atts['tosearch'] ) )
                submit_button( $atts['text'], 'button-secondary', '', false, array( 'id' => 'search-submit' ) ); ?>
        </div>
        <?php
    }

    public static function convert_date_format( $date ) {
        $timestamp = strtotime( $date );

        $new_date = date( 'Y/m/d', $timestamp );
        $new_time = date( 'H:i:s', $timestamp );

        return $new_date . ' ' . esc_html__( 'at', 'admin-site-enhancements' ) . ' ' . $new_time;
    }

    public static function parse_json_array( $array = array() ) {
        $array = json_decode( $array, true );
        $fields = array();
        foreach ( $array as $val ) {
            $name = $val['name'];
            $value = $val['value'];
            if (strpos( $name, '[]' ) !== false ) {
                $fields[str_replace( '[]', '', $name )][] = $value;
            } else if (strpos( $name, '[' ) !== false ) {
                $ids = explode( '[', str_replace( ']', '', $name ) );
                $count = count( $ids );

                switch ( $count ):
                    case 1:
                        $fields[$ids[0]] = $value;
                        break;
                    case 2:
                        $fields[$ids[0]][$ids[1]] = $value;
                        break;
                    case 3:
                        $fields[$ids[0]][$ids[1]][$ids[2]] = $value;
                        break;
                    case 4:
                        $fields[$ids[0]][$ids[1]][$ids[2]][$ids[3]] = $value;
                        break;
                    case 5:
                        $fields[$ids[0]][$ids[1]][$ids[2]][$ids[3]][$ids[4]] = $value;
                        break;
                endswitch;
            } else {
                $fields[$name] = $value;
            }
        }
        return $fields;
    }

    public static function process_form_array( $form ) {
        if ( ! $form ) {
            return;
        }

        $new_values = array(
            'id' => $form->id,
            'form_key' => $form->form_key,
            'name' => $form->name,
            'description' => $form->description,
            'status' => $form->status,
        );

        if ( is_array( $form->options ) ) {
            $form_options = wp_parse_args( $form->options, self::get_form_options_default() );

            foreach ( $form_options as $opt => $value ) {
                $new_values[$opt] = $value;
            }
        }

        return $new_values;
    }

    public static function recursive_parse_args( $args, $defaults ) {
        $new_args = (array) $defaults;
        foreach ( $args as $key => $value ) {
            if ( is_array( $value ) && isset( $new_args[$key] ) ) {
                $new_args[$key] = self::recursive_parse_args( $value, $new_args[$key] );
            } else {
                $new_args[$key] = $value;
            }
        }
        return $new_args;
    }

    public static function get_form_options_checkbox_settings() {
        return array(
            'show_title' => 'off',
            'show_description' => 'off',
        );
    }

    public static function get_form_settings_checkbox_settings() {
        return array(
            'enable_ar' => 'off',
        );
    }

    public static function get_form_options_default() {
        return array(
            'show_title' => 'on',
            'show_description' => 'off',
            'title' => '',
            'description' => '',
            'submit_value' => esc_html__( 'Submit', 'admin-site-enhancements' ),
            'form_css_class' => '',
            'submit_btn_css_class' => '',
            'submit_btn_alignment' => 'left',
        );
    }

    public static function get_form_settings_default( $name = '' ) {
        $return = array(
            'email_to'                  => '[admin_email]',
            'email_from'                => '[admin_email]',
            'reply_to_email'            => '',
            'email_from_name'           => '#site_name',
            'email_subject'             => esc_html__( 'New submission from: ', 'admin-site-enhancements' ) . '#form_title',
            'email_message'             => '#form_details',
            'footer_text'               => __( 'This email was sent from #linked_site_name.', 'admin-site-enhancements' ),
            'enable_ar'                 => 'off',
            'from_ar'                   => '[admin_email]',
            'from_ar_name'              => '#site_name',
            'send_to_ar'                => '',
            'email_subject_ar'          => esc_html__( 'Your submission for: ', 'admin-site-enhancements' ) . '#form_title',
            'email_message_ar'          => esc_html__( 'Thank you for your submission. We will get back to you as soon as possible.', 'admin-site-enhancements' ),
            'footer_text_ar'            => __( 'This email was sent from #linked_site_name.', 'admin-site-enhancements' ),
            'confirmation_type'         => 'show_message',
            'confirmation_message'      => esc_html__( 'Form was submitted successfully', 'admin-site-enhancements' ),
            'hide_form_after_submission' => 'off',
            'error_message'             => esc_html__( 'Sorry, an error occurred! Your form cannot be submitted.', 'admin-site-enhancements' ),
            'show_page_id'              => '',
            'redirect_url_page'         => '',
            'enable_db_entries'         => 'on',
            'entry_preview_field_id'    => '',
            'enable_webhooks'           => 'off',
            'webhook_urls'              => '',
        );
        return apply_filters( 'formbuilder_form_settings_default', $return );
    }

    public static function get_form_styles_default() {
        return array(
            'form_style' => '',
        );
    }

    public static function get_form_options_sanitize_rules() {
        return array(
            'show_title' => 'formbuilder_sanitize_checkbox',
            'show_description' => 'formbuilder_sanitize_checkbox',
            'title' => 'sanitize_text_field',
            'description' => 'sanitize_text_field',
            'submit_value' => 'sanitize_text_field',
            'form_css_class' => 'sanitize_text_field',
            'submit_btn_css_class' => 'sanitize_text_field',
            'submit_btn_alignment' => 'sanitize_text_field',
        );
    }

    public static function get_form_settings_sanitize_rules() {
        $return = array(
            'email_to'                  => 'sanitize_text_field',
            'email_from'                => 'sanitize_text_field',
            'reply_to_email'            => 'sanitize_text_field',
            'email_from_name'           => 'sanitize_text_field',
            'email_subject'             => 'sanitize_text_field',
            'email_message'             => 'sanitize_textarea_field',
            'footer_text'               => 'sanitize_text_field',
            'enable_ar'                 => 'formbuilder_sanitize_checkbox',
            'from_ar'                   => 'sanitize_text_field',
            'from_ar_name'              => 'sanitize_text_field',
            'send_to_ar'                => 'sanitize_text_field',
            'email_subject_ar'          => 'sanitize_text_field',
            'email_message_ar'          => 'sanitize_textarea_field',
            'footer_text_ar'            => 'sanitize_text_field',
            'confirmation_type'         => 'sanitize_text_field',
            'confirmation_message'      => 'sanitize_textarea_field',
            'hide_form_after_submission' => 'formbuilder_sanitize_checkbox',
            'error_message'             => 'sanitize_textarea_field',
            'show_page_id'              => 'sanitize_text_field',
            'redirect_url_page'         => 'sanitize_url',
            'enable_db_entries'         => 'formbuilder_sanitize_checkbox',
            'entry_preview_field_id'    => 'sanitize_text_field',
            'enable_webhooks'           => 'formbuilder_sanitize_checkbox',
            'webhook_urls'              => 'sanitize_url',
            'condition_action'          => array(
                'sanitize_text_field'
            ),
            'compare_from'              => array(
                'sanitize_text_field'
            ),
            'compare_to'                => array(
                'sanitize_text_field'
            ),
            'compare_condition'         => array(
                'sanitize_text_field'
            ),
            'compare_value'             => array(
                'sanitize_text_field'
            )
        );
        return apply_filters( 'formbuilder_settings_sanitize_rules', $return );
    }

    public static function get_form_styles_sanitize_rules() {
        return array(
            'form_style' => 'sanitize_text_field',
            'form_style_template' => 'absint'
        );
    }

    public static function get_form_fields_default() {
        return array(
            'field_order' => 0,
            'field_key' => '',
            'required' => false,
            'type' => '',
            'description' => '',
            'options' => '',
            'name' => '',
        );
    }

    public static function get_countries() {
        $countries = array(
            'Afghanistan',
            'Aland Islands',
            'Albania',
            'Algeria',
            'American Samoa',
            'Andorra',
            'Angola',
            'Anguilla',
            'Antarctica',
            'Antigua and Barbuda',
            'Argentina',
            'Armenia',
            'Aruba',
            'Australia',
            'Austria',
            'Azerbaijan',
            'Bahamas',
            'Bahrain',
            'Bangladesh',
            'Barbados',
            'Belarus',
            'Belgium',
            'Belize',
            'Benin',
            'Bermuda',
            'Bhutan',
            'Bolivia',
            'Bonaire, Sint Eustatius and Saba',
            'Bosnia and Herzegovina',
            'Botswana',
            'Bouvet Island',
            'Brazil',
            'British Indian Ocean Territory',
            'Brunei',
            'Bulgaria',
            'Burkina Faso',
            'Burundi',
            'Cambodia',
            'Cameroon',
            'Canada',
            'Cape Verde',
            'Cayman Islands',
            'Central African Republic',
            'Chad',
            'Chile',
            'China',
            'Christmas Island',
            'Cocos (Keeling ) Islands',
            'Colombia',
            'Comoros',
            'Congo',
            'Cook Islands',
            'Costa Rica',
            'C&ocirc;te d\'Ivoire',
            'Croatia',
            'Cuba',
            'Curacao',
            'Cyprus',
            'Czech Republic',
            'Denmark',
            'Djibouti',
            'Dominica',
            'Dominican Republic',
            'East Timor',
            'Ecuador',
            'Egypt',
            'El Salvador',
            'Equatorial Guinea',
            'Eritrea',
            'Estonia',
            'Ethiopia',
            'Falkland Islands (Malvinas )',
            'Faroe Islands',
            'Fiji',
            'Finland',
            'France',
            'French Guiana',
            'French Polynesia',
            'French Southern Territories',
            'Gabon',
            'Gambia',
            'Georgia',
            'Germany',
            'Ghana',
            'Gibraltar',
            'Greece',
            'Greenland',
            'Grenada',
            'Guadeloupe',
            'Guam',
            'Guatemala',
            'Guernsey',
            'Guinea',
            'Guinea-Bissau',
            'Guyana',
            'Haiti',
            'Heard Island and McDonald Islands',
            'Holy See',
            'Honduras',
            'Hong Kong',
            'Hungary',
            'Iceland',
            'India',
            'Indonesia',
            'Iran',
            'Iraq',
            'Ireland',
            'Israel',
            'Isle of Man',
            'Italy',
            'Jamaica',
            'Japan',
            'Jersey',
            'Jordan',
            'Kazakhstan',
            'Kenya',
            'Kiribati',
            'North Korea',
            'South Korea',
            'Kosovo',
            'Kuwait',
            'Kyrgyzstan',
            'Laos',
            'Latvia',
            'Lebanon',
            'Lesotho',
            'Liberia',
            'Libya',
            'Liechtenstein',
            'Lithuania',
            'Luxembourg',
            'Macao',
            'Macedonia',
            'Madagascar',
            'Malawi',
            'Malaysia',
            'Maldives',
            'Mali',
            'Malta',
            'Marshall Islands',
            'Martinique',
            'Mauritania',
            'Mauritius',
            'Mayotte',
            'Mexico',
            'Micronesia',
            'Moldova',
            'Monaco',
            'Mongolia',
            'Montenegro',
            'Montserrat',
            'Morocco',
            'Mozambique',
            'Myanmar',
            'Namibia',
            'Nauru',
            'Nepal',
            'Netherlands',
            'New Caledonia',
            'New Zealand',
            'Nicaragua',
            'Niger',
            'Nigeria',
            'Niue',
            'Norfolk Island',
            'Northern Mariana Islands',
            'Norway',
            'Oman',
            'Pakistan',
            'Palau',
            'Palestine',
            'Panama',
            'Papua New Guinea',
            'Paraguay',
            'Peru',
            'Philippines',
            'Pitcairn',
            'Poland',
            'Portugal',
            'Puerto Rico',
            'Qatar',
            'Reunion',
            'Romania',
            'Russia',
            'Rwanda',
            'Saint Barthelemy',
            'Saint Helena, Ascension and Tristan da Cunha',
            'Saint Kitts and Nevis',
            'Saint Lucia',
            'Saint Martin ( French part )',
            'Saint Pierre and Miquelon',
            'Saint Vincent and the Grenadines',
            'Samoa',
            'San Marino',
            'Sao Tome and Principe',
            'Saudi Arabia',
            'Senegal',
            'Serbia',
            'Seychelles',
            'Sierra Leone',
            'Singapore',
            'Sint Maarten (Dutch part )',
            'Slovakia',
            'Slovenia',
            'Solomon Islands',
            'Somalia',
            'South Africa',
            'South Georgia and the South Sandwich Islands',
            'South Sudan',
            'Spain',
            'Sri Lanka',
            'Sudan',
            'Suriname',
            'Svalbard and Jan Mayen',
            'Swaziland',
            'Sweden',
            'Switzerland',
            'Syria',
            'Taiwan',
            'Tajikistan',
            'Tanzania',
            'Thailand',
            'Timor-Leste',
            'Togo',
            'Tokelau',
            'Tonga',
            'Trinidad and Tobago',
            'Tunisia',
            'Turkey',
            'Turkmenistan',
            'Turks and Caicos Islands',
            'Tuvalu',
            'Uganda',
            'Ukraine',
            'United Arab Emirates',
            'United Kingdom',
            'United States',
            'United States Minor Outlying Islands',
            'Uruguay',
            'Uzbekistan',
            'Vanuatu',
            'Vatican City',
            'Venezuela',
            'Vietnam',
            'Virgin Islands, British',
            'Virgin Islands, U.S.',
            'Wallis and Futuna',
            'Western Sahara',
            'Yemen',
            'Zambia',
            'Zimbabwe',
        );

        sort( $countries, SORT_LOCALE_STRING);
        return $countries;
    }

    public static function get_us_states() {
        $us_states = array(
            'Alabama',
            'Alaska',
            'Arizona',
            'Arkansas',
            'California',
            'Colorado',
            'Connecticut',
            'Delaware',
            'District of Columbia',
            'Florida',
            'Georgia',
            'Hawaii',
            'Idaho',
            'Illinois',
            'Indiana',
            'Iowa',
            'Kansas',
            'Kentucky',
            'Louisiana',
            'Maine',
            'Maryland',
            'Massachusetts',
            'Michigan',
            'Minnesota',
            'Mississippi',
            'Missouri',
            'Montana',
            'Nebraska',
            'Nevada',
            'New Hampshire',
            'New Jersey',
            'New Mexico',
            'New York',
            'North Carolina',
            'North Dakota',
            'Ohio',
            'Oklahoma',
            'Oregon',
            'Pennsylvania',
            'Rhode Island',
            'South Carolina',
            'South Dakota',
            'Tennessee',
            'Texas',
            'Utah',
            'Vermont',
            'Virginia',
            'Washington',
            'West Virginia',
            'Wisconsin',
            'Wyoming',
            'Armed Forces Americas',
            'Armed Forces Europe',
            'Armed Forces Pacific',
        );

        return $us_states;
    }
    
    public static function get_us_state_abbreviations() {
        return array(
            'AK',
            'AL',
            'AR',
            'AZ',
            'CA',
            'CO',
            'CT',
            'DC',
            'DE',
            'FL',
            'GA',
            'HI',
            'IA',
            'ID',
            'IL',
            'IN',
            'KS',
            'KY',
            'LA',
            'MA',
            'MD',
            'ME',
            'MI',
            'MN',
            'MO',
            'MS',
            'MT',
            'NC',
            'ND',
            'NE',
            'NH',
            'NJ',
            'NM',
            'NV',
            'NY',
            'OH',
            'OK',
            'OR',
            'PA',
            'RI',
            'SC',
            'SD',
            'TN',
            'TX',
            'UT',
            'VA',
            'VT',
            'WA',
            'WI',
            'WV',
            'WY',        
        );
    }

    public static function get_canadian_provinces_territories() {
        $canadian_provinces_territories = array(
            'Alberta',
            'British Columbia',
            'Manitoba',
            'New Brunswick',
            'Newfoundland and Labrador',
            'Northwest Territories',
            'Nova Scotia',
            'Nunavut',
            'Ontario',
            'Prince Edward Island',
            'Quebec',
            'Saskatchewan',
            'Yukon',
        );

        return $canadian_provinces_territories;
    }

    public static function get_continents() {
        $continents = array(
            'Africa',
            'Antarctica',
            'Asia',
            'Australia',
            'Europe',
            'North America',
            'South America',
        );

        return $continents;
    }

    public static function get_gender() {
        return array(
            esc_html__( 'Male', 'admin-site-enhancements' ),
            esc_html__( 'Female', 'admin-site-enhancements' ),
            esc_html__( 'Non-binary', 'admin-site-enhancements' ),
            esc_html__( 'Other', 'admin-site-enhancements' ),
            esc_html__( 'Prefer not to answer', 'admin-site-enhancements' ),
        );
    }
    
    public static function get_ages() {
        return array(
            esc_html__( 'Under 18', 'admin-site-enhancements' ),
            esc_html__( '18-24', 'admin-site-enhancements' ),
            esc_html__( '25-34', 'admin-site-enhancements' ),
            esc_html__( '35-44', 'admin-site-enhancements' ),
            esc_html__( '45-54', 'admin-site-enhancements' ),
            esc_html__( '55-64', 'admin-site-enhancements' ),
            esc_html__( '65 or above', 'admin-site-enhancements' ),
            esc_html__( 'Prefer not to answer', 'admin-site-enhancements' ),
        );
    }

    public static function get_marital_status() {
        return array(
            esc_html__( 'Single', 'admin-site-enhancements' ),
            esc_html__( 'Married', 'admin-site-enhancements' ),
            esc_html__( 'Divorced', 'admin-site-enhancements' ),
            esc_html__( 'Widowed', 'admin-site-enhancements' ),
            esc_html__( 'Prefer not to answer', 'admin-site-enhancements' ),
        );
    }

    public static function get_employment() {
        return array(
            esc_html__( 'Employed full-time', 'admin-site-enhancements' ),
            esc_html__( 'Employed part-time', 'admin-site-enhancements' ),
            esc_html__( 'Self-employed', 'admin-site-enhancements' ),
            esc_html__( 'Not employed but looking for work', 'admin-site-enhancements' ),
            esc_html__( 'Not employed and not looking for work', 'admin-site-enhancements' ),
            esc_html__( 'Homemaker', 'admin-site-enhancements' ),
            esc_html__( 'Retired', 'admin-site-enhancements' ),
            esc_html__( 'Student', 'admin-site-enhancements' ),
            esc_html__( 'Prefer not to answer', 'admin-site-enhancements' ),
       );
    }

    public static function get_job_types() {
        return array(
            esc_html__( 'Full-time', 'admin-site-enhancements' ),
            esc_html__( 'Part-time', 'admin-site-enhancements' ),
            esc_html__( 'Per diem', 'admin-site-enhancements' ),
            esc_html__( 'Employee', 'admin-site-enhancements' ),
            esc_html__( 'Temporary', 'admin-site-enhancements' ),
            esc_html__( 'Contract', 'admin-site-enhancements' ),
            esc_html__( 'Intern', 'admin-site-enhancements' ),
            esc_html__( 'Seasonal', 'admin-site-enhancements' ),
            esc_html__( 'Prefer not to answer', 'admin-site-enhancements' ),
       );
    }

    public static function get_industries() {
        return array(
            esc_html__( 'Accounting / Finance', 'admin-site-enhancements' ),
            esc_html__( 'Advertising / Public Relations', 'admin-site-enhancements' ),
            esc_html__( 'Aerospace / Aviation', 'admin-site-enhancements' ),
            esc_html__( 'Arts / Entertainment / Publishing', 'admin-site-enhancements' ),
            esc_html__( 'Automotive', 'admin-site-enhancements' ),
            esc_html__( 'Banking / Mortgage', 'admin-site-enhancements' ),
            esc_html__( 'Business Development', 'admin-site-enhancements' ),
            esc_html__( 'Business Opportunity', 'admin-site-enhancements' ),
            esc_html__( 'Clerical / Administrative', 'admin-site-enhancements' ),
            esc_html__( 'Construction / Facilities', 'admin-site-enhancements' ),
            esc_html__( 'Consumer Goods', 'admin-site-enhancements' ),
            esc_html__( 'Customer Service', 'admin-site-enhancements' ),
            esc_html__( 'Education / Training', 'admin-site-enhancements' ),
            esc_html__( 'Energy / Utilities', 'admin-site-enhancements' ),
            esc_html__( 'Engineering', 'admin-site-enhancements' ),
            esc_html__( 'Government / Military', 'admin-site-enhancements' ),
            esc_html__( 'Green', 'admin-site-enhancements' ),
            esc_html__( 'Healthcare', 'admin-site-enhancements' ),
            esc_html__( 'Hospitality / Travel', 'admin-site-enhancements' ),
            esc_html__( 'Human Resources', 'admin-site-enhancements' ),
            esc_html__( 'Installation / Maintenance', 'admin-site-enhancements' ),
            esc_html__( 'Insurance', 'admin-site-enhancements' ),
            esc_html__( 'Internet', 'admin-site-enhancements' ),
            esc_html__( 'Job Search Aids', 'admin-site-enhancements' ),
            esc_html__( 'Law Enforcement / Security', 'admin-site-enhancements' ),
            esc_html__( 'Legal', 'admin-site-enhancements' ),
            esc_html__( 'Management / Executive', 'admin-site-enhancements' ),
            esc_html__( 'Manufacturing / Operations', 'admin-site-enhancements' ),
            esc_html__( 'Marketing', 'admin-site-enhancements' ),
            esc_html__( 'Non-Profit / Volunteer', 'admin-site-enhancements' ),
            esc_html__( 'Pharmaceutical / Biotech', 'admin-site-enhancements' ),
            esc_html__( 'Professional Services', 'admin-site-enhancements' ),
            esc_html__( 'QA/Quality Control', 'admin-site-enhancements' ),
            esc_html__( 'Real Estate', 'admin-site-enhancements' ),
            esc_html__( 'Restaurant / Food Service', 'admin-site-enhancements' ),
            esc_html__( 'Retail', 'admin-site-enhancements' ),
            esc_html__( 'Sales', 'admin-site-enhancements' ),
            esc_html__( 'Science / Research', 'admin-site-enhancements' ),
            esc_html__( 'Skilled Labor', 'admin-site-enhancements' ),
            esc_html__( 'Technology', 'admin-site-enhancements' ),
            esc_html__( 'Telecommunications', 'admin-site-enhancements' ),
            esc_html__( 'Transportation / Logistics', 'admin-site-enhancements' ),
            esc_html__( 'Other', 'admin-site-enhancements' ),
       );
    }
 
     public static function get_education() {
        return array(
            esc_html__( 'High school', 'admin-site-enhancements' ),
            esc_html__( 'Associate degree', 'admin-site-enhancements' ),
            esc_html__( "Bachelor's degree", 'admin-site-enhancements' ),
            esc_html__( 'Graduate or professional degree', 'admin-site-enhancements' ),
            esc_html__( 'Some college', 'admin-site-enhancements' ),
            esc_html__( 'Other', 'admin-site-enhancements' ),
            esc_html__( 'Prefer not to answer', 'admin-site-enhancements' ),
       );
    }

    public static function get_satisfaction() {
        return array(
            esc_html__( 'Highly dissatisfied', 'admin-site-enhancements' ),
            esc_html__( 'Dissatisfied', 'admin-site-enhancements' ),
            esc_html__( 'Neutral', 'admin-site-enhancements' ),
            esc_html__( 'Satisfied', 'admin-site-enhancements' ),
            esc_html__( 'Highly satisfied', 'admin-site-enhancements' ),
        );
    }

    public static function get_agreement() {
        return array(
            esc_html__( 'Strongly disagree', 'admin-site-enhancements' ),
            esc_html__( 'Disagree', 'admin-site-enhancements' ),
            esc_html__( 'Neutral', 'admin-site-enhancements' ),
            esc_html__( 'Agree', 'admin-site-enhancements' ),
            esc_html__( 'Strongly agree', 'admin-site-enhancements' ),
        );
    }

    public static function get_likely() {
        return array(
            esc_html__( 'Extremely unlikely', 'admin-site-enhancements' ),
            esc_html__( 'Unlikely', 'admin-site-enhancements' ),
            esc_html__( 'Neutral', 'admin-site-enhancements' ),
            esc_html__( 'Likely', 'admin-site-enhancements' ),
            esc_html__( 'Extremely likely', 'admin-site-enhancements' ),
        );
    }

    public static function get_would_you() {
        return array(
            esc_html__( 'Definitely not', 'admin-site-enhancements' ),
            esc_html__( 'Probably not', 'admin-site-enhancements' ),
            esc_html__( 'Not sure', 'admin-site-enhancements' ),
            esc_html__( 'Probably', 'admin-site-enhancements' ),
            esc_html__( 'Definitely', 'admin-site-enhancements' ),
        );
    }

    public static function get_frequency() {
        return array(
            esc_html__( 'Never', 'admin-site-enhancements' ),
            esc_html__( 'Once a year', 'admin-site-enhancements' ),
            esc_html__( 'Several times a year', 'admin-site-enhancements' ),
            esc_html__( 'Once a month', 'admin-site-enhancements' ),
            esc_html__( 'Several times a month', 'admin-site-enhancements' ),
            esc_html__( 'Once a week', 'admin-site-enhancements' ),
            esc_html__( 'Several times a week', 'admin-site-enhancements' ),
            esc_html__( 'Every day', 'admin-site-enhancements' ),
        );
    }

    public static function get_how_long() {
        return array(
            esc_html__( 'Never', 'admin-site-enhancements' ),
            esc_html__( 'Less than a month', 'admin-site-enhancements' ),
            esc_html__( '1-6 months', 'admin-site-enhancements' ),
            esc_html__( '6-12 months', 'admin-site-enhancements' ),
            esc_html__( '1-3 years', 'admin-site-enhancements' ),
            esc_html__( 'More than 3 years', 'admin-site-enhancements' ),
            esc_html__( 'More than 10 years', 'admin-site-enhancements' ),
        );
    }
        
    public static function get_importance() {
        return array(
            esc_html__( 'Not at all important', 'admin-site-enhancements' ),
            esc_html__( 'Not important', 'admin-site-enhancements' ),
            esc_html__( 'Neutral', 'admin-site-enhancements' ),
            esc_html__( 'Important', 'admin-site-enhancements' ),
            esc_html__( 'Very important', 'admin-site-enhancements' ),
        );
    }

    public static function get_comparison() {
        return array(
            esc_html__( 'Much worse', 'admin-site-enhancements' ),
            esc_html__( 'Somewhat worse', 'admin-site-enhancements' ),
            esc_html__( 'About the same', 'admin-site-enhancements' ),
            esc_html__( 'Somewhat better', 'admin-site-enhancements' ),
            esc_html__( 'Much better', 'admin-site-enhancements' ),
        );
    }

    public static function get_sizes_simple() {
        return array(
            esc_html__( 'XXS', 'admin-site-enhancements' ),
            esc_html__( 'XS', 'admin-site-enhancements' ),
            esc_html__( 'S', 'admin-site-enhancements' ),
            esc_html__( 'M', 'admin-site-enhancements' ),
            esc_html__( 'L', 'admin-site-enhancements' ),
            esc_html__( 'XL', 'admin-site-enhancements' ),
            esc_html__( 'XXL', 'admin-site-enhancements' ),
            esc_html__( 'XXXL', 'admin-site-enhancements' ),
        );
    }

    public static function get_sizes_full() {
        return array(
            esc_html__( 'XXS - Extra extra small', 'admin-site-enhancements' ),
            esc_html__( 'XS - Extra small', 'admin-site-enhancements' ),
            esc_html__( 'S - Small', 'admin-site-enhancements' ),
            esc_html__( 'M - Medium', 'admin-site-enhancements' ),
            esc_html__( 'L - Large', 'admin-site-enhancements' ),
            esc_html__( 'XL - Extra large', 'admin-site-enhancements' ),
            esc_html__( 'XXL - Extra extra large', 'admin-site-enhancements' ),
            esc_html__( 'XXXL - Extra extra extra large', 'admin-site-enhancements' ),
        );
    }

    public static function get_timezones() {
        return array(
            '(GMT -12-00) Eniwetok, Kwajalein',
            '(GMT -11-00) Midway Island, Samoa',
            '(GMT -10-00) Hawaii',
            '(GMT -9-00) Alaska',
            '(GMT -8-00) Pacific Time (US & Canada)',
            '(GMT -7-00) Mountain Time (US & Canada)',
            '(GMT -6-00) Central Time (US & Canada), Mexico City',
            '(GMT -5-00) Eastern Time (US & Canada), Bogota, Lima',
            '(GMT -4-00) Atlantic Time (Canada), Caracas, La Paz',
            '(GMT -3-30) Newfoundland',
            '(GMT -3-00) Brazil, Buenos Aires, Georgetown',
            '(GMT -2-00) Mid-Atlantic',
            '(GMT -1-00) Azores, Cape Verde Islands',
            '(GMT) Western Europe Time, London, Lisbon, Casablanca',
            '(GMT +1-00) Brussels, Copenhagen, Madrid, Paris',
            '(GMT +2-00) Kaliningrad, South Africa',
            '(GMT +3-00) Baghdad, Riyadh, Moscow, St. Petersburg',
            '(GMT +3-30) Tehran',
            '(GMT +4-00) Abu Dhabi, Muscat, Baku, Tbilisi',
            '(GMT +4-30) Kabul',
            '(GMT +5-00) Ekaterinburg, Islamabad, Karachi, Tashkent',
            '(GMT +5-30) Bombay, Calcutta, Madras, New Delhi',
            '(GMT +5-45) Kathmandu',
            '(GMT +6-00) Almaty, Dhaka, Colombo',
            '(GMT +7-00) Bangkok, Hanoi, Jakarta',
            '(GMT +8-00) Beijing, Perth, Singapore, Hong Kong',
            '(GMT +9-00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk',
            '(GMT +9-30) Adelaide, Darwin',
            '(GMT +10-00) Eastern Australia, Guam, Vladivostok',
            '(GMT +11-00) Magadan, Solomon Islands, New Caledonia',
            '(GMT +12-00) Auckland, Wellington, Fiji, Kamchatka',
        );
    }

    public static function get_day_names() {
        return array(
            esc_html__( 'Monday', 'admin-site-enhancements' ),
            esc_html__( 'Tuesday', 'admin-site-enhancements' ),
            esc_html__( 'Wednesday', 'admin-site-enhancements' ),
            esc_html__( 'Thursday', 'admin-site-enhancements' ),
            esc_html__( 'Friday', 'admin-site-enhancements' ),
            esc_html__( 'Saturday', 'admin-site-enhancements' ),
            esc_html__( 'Sunday', 'admin-site-enhancements' ),
        );
    }

    public static function get_month_names() {
        return array(
            esc_html__( 'January', 'admin-site-enhancements' ),
            esc_html__( 'February', 'admin-site-enhancements' ),
            esc_html__( 'March', 'admin-site-enhancements' ),
            esc_html__( 'April', 'admin-site-enhancements' ),
            esc_html__( 'May', 'admin-site-enhancements' ),
            esc_html__( 'June', 'admin-site-enhancements' ),
            esc_html__( 'July', 'admin-site-enhancements' ),
            esc_html__( 'August', 'admin-site-enhancements' ),
            esc_html__( 'September', 'admin-site-enhancements' ),
            esc_html__( 'October', 'admin-site-enhancements' ),
            esc_html__( 'November', 'admin-site-enhancements' ),
            esc_html__( 'December', 'admin-site-enhancements' ),
        );
    }

    public static function get_years( $period = 'm10') {
        if ( false !== strpos( $period, 'm' ) ) {
            $period_type = 'past';
            $period_length = intval( str_replace( 'm', '', $period ) );
        }
        if ( false !== strpos( $period, 'p' ) ) {
            $period_type = 'future';
            $period_length = intval( str_replace( 'p', '', $period ) );
        }

        $current_year = intval( wp_date( 'Y', time() ) );
        
        if ( 'past' === $period_type ) {
            $starting_year = $current_year - $period_length;
            $ending_year = $current_year;
        }
        
        if ( 'future' === $period_type ) {
            $starting_year = $current_year; 
            $ending_year = $current_year + $period_length;
        }

        $years_array = array();

        while ( $starting_year !== $ending_year + 1 ) {
            $years_array[] = $starting_year;
            $starting_year++;
        }            
        
        return $years_array;
    }
    
    public static function get_options_presets() {
        return array(
            'fb-continents-opts' => array(
                'label' => esc_html__( 'Continents', 'admin-site-enhancements' ),
                'options' => self::get_continents()
            ),
            'fb-countries-opts' => array(
                'label' => esc_html__( 'Countries', 'admin-site-enhancements' ),
                'options' => self::get_countries()
            ),
            'fb-us-states-opts' => array(
                'label' => esc_html__( 'U.S. States', 'admin-site-enhancements' ),
                'options' => self::get_us_states()
            ),
            'fb-us-states-abbreviations-opts' => array(
                'label' => esc_html__( 'U.S. States Abbreviations', 'admin-site-enhancements' ),
                'options' => self::get_us_state_abbreviations()
            ),
            'fb-canadian-provinces-opts' => array(
                'label' => esc_html__( 'Canadian Provinces / Territories', 'admin-site-enhancements' ),
                'options' => self::get_canadian_provinces_territories()
            ),
            'fb-gender-opts' => array(
                'label' => esc_html__( 'Gender', 'admin-site-enhancements' ),
                'options' => self::get_gender()
            ),
            'fb-age-opts' => array(
                'label' => esc_html__( 'Age Groups', 'admin-site-enhancements' ),
                'options' => self::get_ages()
            ),
            'fb-marital-status-opts' => array(
                'label' => esc_html__( 'Marital Status', 'admin-site-enhancements' ),
                'options' => self::get_marital_status()
            ),
            'fb-employment-opts' => array(
                'label' => esc_html__( 'Employment', 'admin-site-enhancements' ),
                'options' => self::get_employment()
            ),
            'fb-job-types-opts' => array(
                'label' => esc_html__( 'Job Type', 'admin-site-enhancements' ),
                'options' => self::get_job_types()
            ),
            'fb-industries-opts' => array(
                'label' => esc_html__( 'Industries', 'admin-site-enhancements' ),
                'options' => self::get_industries()
            ),
            'fb-education-opts' => array(
                'label' => esc_html__( 'Education', 'admin-site-enhancements' ),
                'options' => self::get_education()
            ),
            'fb-satisfaction-opts' => array(
                'label' => esc_html__( 'Satisfaction', 'admin-site-enhancements' ),
                'options' => self::get_satisfaction()
            ),
            'fb-importance-opts' => array(
                'label' => esc_html__( 'Importance', 'admin-site-enhancements' ),
                'options' => self::get_importance()
            ),
            'fb-agreement-opts' => array(
                'label' => esc_html__( 'Agreement', 'admin-site-enhancements' ),
                'options' => self::get_agreement()
            ),
            'fb-likely-opts' => array(
                'label' => esc_html__( 'Likely', 'admin-site-enhancements' ),
                'options' => self::get_likely()
            ),
            'fb-would-you-opts' => array(
                'label' => esc_html__( 'Would You / Probability', 'admin-site-enhancements' ),
                'options' => self::get_would_you()
            ),
            'fb-frequency-opts' => array(
                'label' => esc_html__( 'How Often / Frequency', 'admin-site-enhancements' ),
                'options' => self::get_frequency()
            ),
            'fb-how-long-opts' => array(
                'label' => esc_html__( 'How Long / Duration', 'admin-site-enhancements' ),
                'options' => self::get_how_long()
            ),
            'fb-comparison-opts' => array(
                'label' => esc_html__( 'Comparison', 'admin-site-enhancements' ),
                'options' => self::get_comparison()
            ),
            'fb-size-simple-opts' => array(
                'label' => esc_html__( 'Size (Simple)', 'admin-site-enhancements' ),
                'options' => self::get_sizes_simple()
            ),
            'fb-size-full-opts' => array(
                'label' => esc_html__( 'Size (Full)', 'admin-site-enhancements' ),
                'options' => self::get_sizes_full()
            ),
            'fb-timezone-opts' => array(
                'label' => esc_html__( 'Timezones', 'admin-site-enhancements' ),
                'options' => self::get_timezones()
            ),
            'fb-day-name-opts' => array(
                'label' => esc_html__( 'Day of the Week', 'admin-site-enhancements' ),
                'options' => self::get_day_names()
            ),
            'fb-month-name-opts' => array(
                'label' => esc_html__( 'Months of the Year', 'admin-site-enhancements' ),
                'options' => self::get_month_names()
            ),
            'fb-years-m10-opts' => array(
                'label' => esc_html__( 'Past 10 years', 'admin-site-enhancements' ),
                'options' => self::get_years( 'm10' )
            ),
            'fb-years-m25-opts' => array(
                'label' => esc_html__( 'Past 25 years', 'admin-site-enhancements' ),
                'options' => self::get_years( 'm25' )
            ),
            'fb-years-m50-pts' => array(
                'label' => esc_html__( 'Past 50 years', 'admin-site-enhancements' ),
                'options' => self::get_years( 'm50' )
            ),
            'fb-years-m75-opts' => array(
                'label' => esc_html__( 'Past 75 years', 'admin-site-enhancements' ),
                'options' => self::get_years( 'm75' )
            ),
            'fb-years-m100-opts' => array(
                'label' => esc_html__( 'Past 100 years', 'admin-site-enhancements' ),
                'options' => self::get_years( 'm100' )
            ),
            'fb-years-p10-opts' => array(
                'label' => esc_html__( '10 years ahead', 'admin-site-enhancements' ),
                'options' => self::get_years( 'p10' )
            ),
            'fb-years-p25-opts' => array(
                'label' => esc_html__( '25 years ahead', 'admin-site-enhancements' ),
                'options' => self::get_years( 'p25' )
            ),
            'fb-years-p50-pts' => array(
                'label' => esc_html__( '50 years ahead', 'admin-site-enhancements' ),
                'options' => self::get_years( 'p50' )
            ),
            'fb-years-p75-opts' => array(
                'label' => esc_html__( '75 years ahead', 'admin-site-enhancements' ),
                'options' => self::get_years( 'p75' )
            ),
            'fb-years-p100-opts' => array(
                'label' => esc_html__( '100 years ahead', 'admin-site-enhancements' ),
                'options' => self::get_years( 'p100' )
            ),
        );
    }

    public static function get_user_id_param( $user_id ) {
        if ( ! $user_id || is_numeric( $user_id ) ) {
            return $user_id;
        }
        $user_id = sanitize_text_field( $user_id );
        if ( $user_id == 'current' ) {
            $user_id = get_current_user_id();
        } else {
            if ( is_email( $user_id ) ) {
                $user = get_user_by( 'email', $user_id );
            } else {
                $user = get_user_by( 'login', $user_id );
            }
            if ( $user ) {
                $user_id = $user->ID;
            }
            unset( $user );
        }
        return $user_id;
    }

    public static function get_ip() {
        $ip = self::get_ip_address();
        return $ip;
    }

    public static function get_ip_address() {
        $ip_options = array( 'REMOTE_ADDR' );
        $ip = '';

        foreach ( $ip_options as $key ) {
            if ( ! isset( $_SERVER[$key] ) ) {
                continue;
            }
            $key = self::get_server_value( $key );
            foreach (explode( ',', $key ) as $ip ) {
                $ip = trim( $ip ); // Just to be safe.
                if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
                    return sanitize_text_field( $ip );
                }
            }
        }
        return sanitize_text_field( $ip );
    }

    public static function get_server_value( $value ) {
        return isset( $_SERVER[$value] ) ? sanitize_text_field( wp_strip_all_tags( wp_unslash( $_SERVER[$value] ) )) : '';
    }

    public static function count_decimals( $num ) {
        if ( ! is_numeric( $num ) ) {
            return false;
        }
        $num = (string ) $num;
        $parts = explode( '.', $num );
        if ( 1 === count( $parts ) ) {
            return 0;
        }
        return strlen( $parts[count( $parts ) - 1] );
    }

    public static function print_message() {
        if ( isset( $_SESSION['formbuilder_message'] ) ) {
            ?>
            <div class="fb-settings-updated">
                <?php
                echo esc_html(sanitize_text_field( $_SESSION['formbuilder_message'] ) );
                unset( $_SESSION['formbuilder_message'] );
                ?>
            </div>
            <?php
        }
    }

    public static function sanitize_array( $array = array(), $sanitize_rule = array() ) {
        $new_args = (array) $array;

        foreach ( $array as $key => $value ) {
            if ( is_array( $value ) ) {
                $new_args[$key] = self::sanitize_array( $value, isset( $sanitize_rule[$key] ) ? $sanitize_rule[$key] : 'sanitize_text_field' );
            } else {
                if ( isset( $sanitize_rule[$key] ) && ! empty( $sanitize_rule[$key] ) && function_exists( $sanitize_rule[$key] ) ) {
                    $sanitize_type = $sanitize_rule[$key];
                    $new_args[$key] = $sanitize_type( $value );
                } else {
                    $new_args[$key] = sanitize_text_field( $value );
                }
            }
        }

        return $new_args;
    }

    public static function get_field_options_sanitize_rules() {
        return array(
            'grid_id' => 'sanitize_text_field',
            'name' => 'sanitize_text_field',
            'label' => 'sanitize_text_field',
            'label_position' => 'sanitize_text_field',
            'label_alignment' => 'sanitize_text_field',
            'hide_label' => 'formbuilder_sanitize_checkbox_boolean',
            'heading_type' => 'sanitize_text_field',
            'text_alignment' => 'sanitize_text_field',
            'content' => 'sanitize_text_field',
            'select_option_type' => 'sanitize_text_field',
            'image_size' => 'sanitize_text_field',
            'image_id' => 'formbuilder_sanitize_number',
            'spacer_height' => 'formbuilder_sanitize_number',
            'step' => 'formbuilder_sanitize_float',
            'min_time' => 'sanitize_text_field',
            'max_time' => 'sanitize_text_field',
            'upload_label' => 'sanitize_text_field',
            'max_upload_size' => 'formbuilder_sanitize_number',
            'extensions' => 'formbuilder_sanitize_allowed_file_extensions',
            'extensions_error_message' => 'sanitize_text_field',
            'multiple_uploads' => 'sanitize_text_field',
            'multiple_uploads_limit' => 'formbuilder_sanitize_number',
            'multiple_uploads_error_message' => 'sanitize_text_field',
            'date_format' => 'sanitize_text_field',
            'border_style' => 'sanitize_text_field',
            'border_width' => 'formbuilder_sanitize_number',
            'minnum' => 'formbuilder_sanitize_float',
            'maxnum' => 'formbuilder_sanitize_float',
            'classes' => 'sanitize_text_field',
            'auto_width' => 'sanitize_text_field',
            'placeholder' => 'sanitize_text_field',
            'format' => 'sanitize_text_field',
            'required_indicator' => 'sanitize_text_field',
            'options_layout' => 'sanitize_text_field',
            'field_max_width' => 'formbuilder_sanitize_number',
            'field_max_width_unit' => 'sanitize_text_field',
            'image_max_width' => 'formbuilder_sanitize_number',
            'image_max_width_unit' => 'sanitize_text_field',
            'field_alignment' => 'sanitize_text_field',
            'blank' => 'sanitize_text_field',
            'invalid' => 'sanitize_text_field',
            'rows' => 'formbuilder_sanitize_number',
            'max' => 'formbuilder_sanitize_number',
            'disable' => array(
                'line1' => 'sanitize_text_field',
                'line2' => 'sanitize_text_field',
                'city' => 'sanitize_text_field',
                'state' => 'sanitize_text_field',
                // 'zip' => 'formbuilder_sanitize_number',
                'zip' => 'sanitize_text_field',
                'country' => 'sanitize_text_field'
            )
        );
    }

    public static function get_all_forms_list_options() {
        $all_forms = array();
        $forms = Form_Builder_Builder::get_all_forms();
        foreach ( $forms as $form ) {
            $all_forms[$form->id] = $form->name;
        }
        return $all_forms;
    }

    public static function getSalt() {
        $salt = get_option( '_formbuilder_security_salt' );
        if ( ! $salt ) {
            $salt = wp_generate_password();
            update_option( '_formbuilder_security_salt', $salt, 'no' );
        }
        return $salt;
    }

    public static function encrypt( $text ) {
        $key = static::getSalt();
        $cipher = 'AES-128-CBC';
        $ivlen = openssl_cipher_iv_length( $cipher );
        $iv = openssl_random_pseudo_bytes( $ivlen );
        $ciphertext_raw = openssl_encrypt( $text, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv );
        $hmac = hash_hmac( 'sha256', $ciphertext_raw, $key, $as_binary = true );
        return base64_encode( $iv . $hmac . $ciphertext_raw );
    }

    public static function decrypt( $text ) {
        $key = static::getSalt();
        $c = base64_decode( $text );
        $cipher = 'AES-128-CBC';
        $ivlen = openssl_cipher_iv_length( $cipher );
        $iv = substr( $c, 0, $ivlen );
        $hmac = substr( $c, $ivlen, $sha2len = 32 );
        $ciphertext_raw = substr( $c, $ivlen + $sha2len );
        $original_plaintext = openssl_decrypt( $ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv );
        $calcmac = hash_hmac( 'sha256', $ciphertext_raw, $key, $as_binary = true );

        if (hash_equals( $hmac, $calcmac ) ) {
            return $original_plaintext;
        }
    }

    public static function get_field_input_value( $value ) {
        $entry_val = '';
        $entry_value = maybe_unserialize( $value['value'] );
        $entry_type = maybe_unserialize( $value['type'] );
        if ( is_array( $entry_value ) ) {
            if ( $entry_type == 'name' ) {
                $entry_value = implode( ' ', array_filter( $entry_value ) );
            } elseif ( $entry_type == 'repeater_field' ) {
                $entry_val = '<table><thead><tr>';
                foreach ( array_keys( $entry_value ) as $key ) {
                    $entry_val .= '<th>' . $key . '</th>';
                }
                $entry_val .= '</tr></thead><tbody>';
                $out = array();
                foreach ( $entry_value as $rowkey => $row ) {
                    foreach ( $row as $colkey => $col ) {
                        $out[$colkey][$rowkey] = $col;
                    }
                }
                foreach ( $out as $key => $val ) {
                    foreach ( $val as $eval ) {
                        $entry_val .= '<td>' . $eval . '</td>';
                    }
                    $entry_val .= '</tr>';
                }
                $entry_val .= '</tbody></table>';
                $entry_value = $entry_val;
            } else {
                $entry_value = implode( ',', array_filter( $entry_value ) );
            }
        }
        return $entry_value;
    }

    public static function unserialize_or_decode( $value ) {
        if ( is_array( $value ) ) {
            return $value;
        }
        if ( is_serialized( $value ) ) {
            return self::maybe_unserialize_array( $value );
        } else {
            return self::maybe_json_decode( $value, false );
        }
    }

    public static function maybe_unserialize_array( $value ) {
        if ( ! is_string( $value ) ) {
            return $value;
        }

        if ( ! is_serialized( $value ) || 'a:' !== substr( $value, 0, 2 ) ) {
            return $value;
        }

        $parsed = Form_Builder_Serialized_Str_Parser::get()->parse( $value );
        if ( is_array( $parsed ) ) {
            $value = $parsed;
        }
        return $value;
    }

    public static function maybe_json_decode( $string, $single_to_array = true ) {
        if ( is_array( $string ) || is_null( $string ) ) {
            return $string;
        }

        $new_string = json_decode( $string, true );
        if ( function_exists( 'json_last_error' ) ) {
            $single_value = false;
            if ( ! $single_to_array ) {
                $single_value = is_array( $new_string ) && count( $new_string ) === 1 && isset( $new_string[0] );
            }
            if (json_last_error() == JSON_ERROR_NONE && is_array( $new_string ) && ! $single_value ) {
                $string = $new_string;
            }
        }
        return $string;
    }

}
