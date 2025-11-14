<?php
global $post, $wpdb, $wp_roles;

$equals_text = __( 'equals', 'admin-site-enhancements' );
$not_equals_text = __( 'is not', 'admin-site-enhancements' );
$rules = (array) get_post_meta( $post->ID, 'cfgroup_rules', true );

// Populate rules if empty
$rule_types = [
    'placement',
    'post_types',
    'post_formats',
    'user_roles',
    'post_ids',
    'term_ids',
    'page_templates',
    'options_pages',
    'taxonomies',
];

foreach ( $rule_types as $type ) {
    if ( ! isset( $rules[ $type ] ) ) {
        $rules[ $type ] = [ 'operator' => [ '==' ], 'values' => [] ];
    }
}

// Placement location
$post_meta = get_post_custom( $post->ID );
$placement = isset( $rules['placement']['values'] ) && ! empty( $rules['placement']['values'] ) ? $rules['placement']['values'] : 'posts';

// Post types
$post_types = [];
$types = get_post_types();
$inapplicable_post_types = array( 
    'asenha_cfgroup', 
    'attachment', 
    'revision', 
    'nav_menu_item', 
    'customize_changeset',
    'oembed_cache',
    'custom_css',
    'user_request',
    'wp_block',
    'wp_template',
    'wp_template_part',
    'wp_global_styles',
    'wp_navigation',
    'wp_font_family',
    'wp_font_face',
    'product_variation',
    'shop_order',
    'shop_order_refund',
    'shop_coupon',
    'shop_order_placehold',
    'asenha_cpt',
    'asenha_ctax',
    'options_page_config',
    'asenha_options_page',
    'asenha_code_snippet',
    'acf-field',
    'acf-field-group',
    'acf-post-type',
    'acf-taxonomy',
    'acf-ui-options-page',
    'breakdance_form_res',
    'breakdance_block',
    'breakdance_acf_block',
    'breakdance_header',
    'breakdance_footer',
    'breakdance_popup',
    'breakdance_template',
    'ct_template', // Oxygen
    'bricks_fonts',
    'bricks_template',
    'e-landing-page',
    'elementor_font',
    'elementor_icons',
    'elementor_library',
    'elementor_snippet',
);
foreach ( $types as $post_type ) {
    if ( ! in_array( $post_type, $inapplicable_post_types ) ) {
        $post_types[ $post_type ] = $post_type;
    }
}
asort( $post_types );

// Post formats
$post_formats = [];
if ( current_theme_supports( 'post-formats' ) ) {
    $post_formats = [ 'standard' => 'Standard' ];
    $post_formats_slugs = get_theme_support( 'post-formats' );

    if ( is_array( $post_formats_slugs[0] ) ) {
        foreach ( $post_formats_slugs[0] as $post_format ) {
            $post_formats[ $post_format ] = get_post_format_string( $post_format );
        }
    }
}

// User roles
foreach ( $wp_roles->roles as $key => $role ) {
    $user_roles[ $key ] = $key;
}

// Post IDs
$post_ids = [];
$json_posts = [];

if ( ! empty( $rules['post_ids']['values'] ) ) {
    $post_in = implode( ',', $rules['post_ids']['values'] );

    $sql = "
    SELECT ID, post_type, post_title, post_parent
    FROM $wpdb->posts
    WHERE ID IN ($post_in)
    ORDER BY post_type, post_title";
    $results = $wpdb->get_results( $sql );

    foreach ( $results as $result ) {
        $parent = '';

        if (
            isset( $result->post_parent ) &&
            absint( $result->post_parent ) > 0 &&
            $parent = get_post( $result->post_parent )
        ) {
            $parent = "$parent->post_title >";
        }

        $json_posts[] = [ 'id' => $result->ID, 'text' => "($result->post_type) $parent $result->post_title (#$result->ID)" ];
        $post_ids[] = $result->ID;
    }
}

// Term IDs
$sql = "
SELECT t.term_id, t.name, tt.taxonomy
FROM $wpdb->terms t
INNER JOIN $wpdb->term_taxonomy tt ON tt.term_id = t.term_id AND tt.taxonomy != 'post_tag'
ORDER BY tt.parent, tt.taxonomy, t.name";
$results = $wpdb->get_results( $sql );

foreach ( $results as $result ) {
    $term_ids[ $result->term_id ] = "($result->taxonomy) $result->name";
}

// Page templates
$page_templates = [];
$templates = get_page_templates();

foreach ( $templates as $template_name => $filename ) {
    $page_templates[ $filename ] = $template_name;
}

// Options Pages
$options_pages = array();

$args = array(
    'post_type'         => 'options_page_config',
    'post_status'       => 'publish',
    'numberposts'    => -1, // use this instead of posts_per_page
    'orderby'           => 'title',
    'order'             => 'ASC',
);

$options_page_configs = get_posts( $args );

if ( ! empty( $options_page_configs ) ) {
    foreach ( $options_page_configs as $options_page_config ) {
        $options_pages[get_post_meta( $options_page_config->ID, 'options_page_menu_slug', true )] = $options_page_config->post_title;
    }
}

// Taxonomies
$taxonomies = array();

$args = array(
    'public'        => true,
    'show_ui'       => true,
);

$all_taxonomies = get_taxonomies( $args, 'objects' );

foreach ( $all_taxonomies as $taxonomy_slug => $tax_obj ) {
    $taxonomies[$taxonomy_slug] = $tax_obj->labels->name . ' (' . $taxonomy_slug . ')';
}

asort( $taxonomies ); // sort by value, i.e. label->name
?>
<script>
(function($) {
    $(document).ready( function() {
        if ( $('#on-posts').is(':checked') ) {
            $('#posts-placement-options').show();
            $('#options-pages-placement-options').hide();
            $('#taxonomy-terms-placement-options').hide();
        }

        if ( $('#on-options-pages').is(':checked') ) {
            $('#posts-placement-options').hide();
            $('#options-pages-placement-options').show();
            $('#taxonomy-terms-placement-options').hide();
        }

        if ( $('#on-taxonomy-terms').is(':checked') ) {
            $('#posts-placement-options').hide();
            $('#options-pages-placement-options').hide();
            $('#taxonomy-terms-placement-options').show();
        }

        $("input[name='cfgroup[rules][placement]']").change(function(){
            if ( $(this).val() == 'posts' ) {
                $('#posts-placement-options').show();
                $('#options-pages-placement-options').hide();
                $('#taxonomy-terms-placement-options').hide();                
            }

            if ( $(this).val() == 'options-pages' ) {
                $('#posts-placement-options').hide();
                $('#options-pages-placement-options').show();
                $('#taxonomy-terms-placement-options').hide();
            }

            if ( $(this).val() == 'taxonomy-terms' ) {
                $('#posts-placement-options').hide();
                $('#options-pages-placement-options').hide();
                $('#taxonomy-terms-placement-options').show();
            }
        });
    });

    $(function() {
        var cfgroup_nonce = '<?php echo wp_create_nonce( 'cfgroup_admin_nonce' ); ?>';

        $('.select2').select2({
            placeholder: '<?php _e( 'Leave blank to skip this rule', 'admin-site-enhancements' ); ?>'
        });

        $('.select2-ajax').select2({
            multiple: true,
            placeholder: '<?php _e( 'Leave blank to skip this rule', 'admin-site-enhancements' ); ?>',
            minimumInputLength: 2,
            ajax: {
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: function(term, page) {
                    return {
                        q: term,
                        action: 'cfgroup_ajax_handler',
                        action_type: 'search_posts',
                        nonce: cfgroup_nonce
                    }
                },
                results: function(data, page) {
                    return { results: data };
                }
            },
            initSelection: function(element, callback) {
                var data = [];
                var post_ids = <?php echo json_encode( $json_posts ); ?>;
                $(post_ids).each(function(idx, val) {
                    data.push({ id: val.id, text: val.text });
                });
                callback(data);
            }
        });
    });
})(jQuery);
</script>

<div class="field-group-placement-radio">
    <div>
        <input type="radio" id="on-posts" name="cfgroup[rules][placement]" value="posts" <?php checked( $placement, 'posts' ); ?> />
        <label for="on-posts"><?php echo __( 'On Posts', 'admin-site-enhancements' ); ?></label>
    </div>
    <div>
        <input type="radio" id="on-options-pages" name="cfgroup[rules][placement]" value="options-pages" <?php checked( $placement, 'options-pages' ); ?> />
        <label for="on-options-pages"><?php echo __( 'On Options Pages', 'admin-site-enhancements' ); ?></label>
    </div>
    <div>
        <input type="radio" id="on-taxonomy-terms" name="cfgroup[rules][placement]" value="taxonomy-terms" <?php checked( $placement, 'taxonomy-terms' ); ?> />
        <label for="on-taxonomy-terms"><?php echo __( 'On Taxonomy Terms', 'admin-site-enhancements' ); ?></label>
    </div>
    <input type="hidden" name="cfgroup[rules][operator][placement]" value="==" />
</div>

<table id="posts-placement-options">
    <tr>
        <td class="label">
            <label><?php _e( 'Post Types', 'admin-site-enhancements' ); ?></label>
        </td>
        <td style="width:80px; vertical-align:top">
            <?php
                CFG()->create_field( [
                    'type' => 'select',
                    'input_name' => "cfgroup[rules][operator][post_types]",
                    'options' => [
                        'choices' => [
                            '==' => $equals_text,
                            '!=' => $not_equals_text,
                        ],
                        'force_single' => true,
                    ],
                    'value' => $rules['post_types']['operator'],
                ] );
            ?>
        </td>
        <td>
            <?php
                CFG()->create_field( [
                    'type' => 'select',
                    'input_class' => 'select2',
                    'input_name' => "cfgroup[rules][post_types]",
                    'options' => [ 'multiple' => '1', 'choices' => $post_types ],
                    'value' => $rules['post_types']['values'],
                ] );
            ?>
        </td>
    </tr>
    <?php if ( current_theme_supports( 'post-formats' ) && count( $post_formats ) ) : ?>
        <tr>
            <td class="label">
                <label><?php _e( 'Post Formats', 'admin-site-enhancements' ); ?></label>
            </td>
            <td style="width:80px; vertical-align:top">
                <?php
                CFG()->create_field( [
                    'type' => 'select',
                    'input_name' => "cfgroup[rules][operator][post_formats]",
                    'options' => [
                        'choices' => [
                            '==' => $equals_text,
                            '!=' => $not_equals_text,
                        ],
                        'force_single' => true,
                    ],
                    'value' => $rules['post_formats']['operator'],
                ] );
                ?>
            </td>
            <td>
                <?php
                CFG()->create_field( [
                    'type' => 'select',
                    'input_class' => 'select2',
                    'input_name' => "cfgroup[rules][post_formats]",
                    'options' => [ 'multiple' => '1', 'choices' => $post_formats ],
                    'value' => $rules['post_formats']['values'],
                ] );
                ?>
            </td>
        </tr>
    <?php endif; ?>
    <tr>
        <td class="label">
            <label><?php _e( 'User Roles', 'admin-site-enhancements' ); ?></label>
        </td>
        <td style="width:80px; vertical-align:top">
            <?php
                CFG()->create_field( [
                    'type' => 'select',
                    'input_name' => "cfgroup[rules][operator][user_roles]",
                    'options' => [
                        'choices' => [
                            '==' => $equals_text,
                            '!=' => $not_equals_text,
                        ],
                        'force_single' => true,
                    ],
                    'value' => $rules['user_roles']['operator'],
                ] );
            ?>
        </td>
        <td>
            <?php
                CFG()->create_field( [
                    'type' => 'select',
                    'input_class' => 'select2',
                    'input_name' => "cfgroup[rules][user_roles]",
                    'options' => [ 'multiple' => '1', 'choices' => $user_roles ],
                    'value' => $rules['user_roles']['values'],
                ] );
            ?>
        </td>
    </tr>
    <tr>
        <td class="label">
            <label><?php _e('Posts', 'cfgroup'); ?></label>
        </td>
        <td style="width:80px; vertical-align:top">
            <?php
                CFG()->create_field( [
                    'type' => 'select',
                    'input_name' => "cfgroup[rules][operator][post_ids]",
                    'options' => [
                        'choices' => [
                            '==' => $equals_text,
                            '!=' => $not_equals_text,
                        ],
                        'force_single' => true,
                    ],
                    'value' => $rules['post_ids']['operator'],
                ] );
            ?>
        </td>
        <td>
            <input type="hidden" name="cfgroup[rules][post_ids]" class="select2-ajax" value="<?php echo implode( ',', $post_ids ); ?>" style="width:99.95%" />
        </td>
    </tr>
    <tr>
        <td class="label">
            <label><?php _e( 'Taxonomy Terms', 'admin-site-enhancements' ); ?></label>
        </td>
        <td style="width:80px; vertical-align:top">
            <?php
                CFG()->create_field( [
                    'type' => 'select',
                    'input_name' => "cfgroup[rules][operator][term_ids]",
                    'options' => [
                        'choices' => [
                            '==' => $equals_text,
                            '!=' => $not_equals_text,
                        ],
                        'force_single' => true,
                    ],
                    'value' => $rules['term_ids']['operator'],
                ] );
            ?>
        </td>
        <td>
            <?php
                CFG()->create_field( [
                    'type' => 'select',
                    'input_class' => 'select2',
                    'input_name' => "cfgroup[rules][term_ids]",
                    'options' => [ 'multiple' => '1', 'choices' => $term_ids ],
                    'value' => $rules['term_ids']['values'],
                ] );
            ?>
        </td>
    </tr>
    <tr>
        <td class="label">
            <label><?php _e( 'Page Templates', 'admin-site-enhancements' ); ?></label>
        </td>
        <td style="width:80px; vertical-align:top">
            <?php
                CFG()->create_field( [
                    'type' => 'select',
                    'input_name' => "cfgroup[rules][operator][page_templates]",
                    'options' => [
                        'choices' => [
                            '==' => $equals_text,
                            '!=' => $not_equals_text,
                        ],
                        'force_single' => true,
                    ],
                    'value' => $rules['page_templates']['operator'],
                ] );
            ?>
        </td>
        <td>
            <?php
                CFG()->create_field( [
                    'type' => 'select',
                    'input_class' => 'select2',
                    'input_name' => "cfgroup[rules][page_templates]",
                    'options' => [ 'multiple' => '1', 'choices' => $page_templates ],
                    'value' => $rules['page_templates']['values'],
                ] );
            ?>
        </td>
    </tr>
</table>

<table id="options-pages-placement-options">
    <tr>
        <td class="label">
            <label><?php _e( 'Option Pages', 'admin-site-enhancements' ); ?></label>
        </td>
        <td style="width:80px; vertical-align:top">
            <?php
                CFG()->create_field( [
                    'type' => 'select',
                    'input_name' => "cfgroup[rules][operator][options_pages]",
                    'options' => [
                        'choices' => [
                            '==' => $equals_text,
                            '!=' => $not_equals_text,
                        ],
                        'force_single' => true,
                    ],
                    'value' => $rules['options_pages']['operator'],
                ] );
            ?>
        </td>
        <td>
            <?php
                CFG()->create_field( [
                    'type' => 'select',
                    'input_class' => 'select2',
                    'input_name' => "cfgroup[rules][options_pages]",
                    'options' => [ 'multiple' => '1', 'choices' => $options_pages ],
                    'value' => $rules['options_pages']['values'],
                ] );
            ?>
        </td>
    </tr>
</table>

<table id="taxonomy-terms-placement-options">
    <tr>
        <td class="label">
            <label><?php _e( 'Taxonomies', 'admin-site-enhancements' ); ?></label>
        </td>
        <td style="width:80px; vertical-align:top">
            <?php
                CFG()->create_field( [
                    'type' => 'select',
                    'input_name' => "cfgroup[rules][operator][taxonomies]",
                    'options' => [
                        'choices' => [
                            '==' => $equals_text,
                            '!=' => $not_equals_text,
                        ],
                        'force_single' => true,
                    ],
                    'value' => $rules['taxonomies']['operator'],
                ] );
            ?>
        </td>
        <td>
            <?php
                CFG()->create_field( [
                    'type' => 'select',
                    'input_class' => 'select2',
                    'input_name' => "cfgroup[rules][taxonomies]",
                    'options' => [ 'multiple' => '1', 'choices' => $taxonomies ],
                    'value' => $rules['taxonomies']['values'],
                ] );
            ?>
        </td>
    </tr>
</table>