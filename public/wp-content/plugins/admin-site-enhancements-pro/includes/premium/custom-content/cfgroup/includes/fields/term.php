<?php

class cfgroup_term extends cfgroup_field
{

    function __construct() {
        $this->name = 'term';
        $this->label = __( 'Term', 'admin-site-enhancements' );
    }


    function html( $field ) {
        global $wpdb;

        $selected_posts = [];
        $available_posts = [];

        $taxonomies = [];
        if ( ! empty( $field->options['taxonomies'] ) ) {
            foreach ( $field->options['taxonomies'] as $taxonomy ) {
                $taxonomies[] = $taxonomy;
            }
        }
        else {
            $taxonomies = get_taxonomies( [ 'public' => true ] );
        }

        $args = [
            'taxonomy'   => $taxonomies,
            'hide_empty' => false,
            'fields'     => 'ids',
            'orderby'    => 'name',
            'order'      => 'ASC'
        ];

        $args = apply_filters( 'cfgroup_field_term_query_args', $args, [ 'field' => $field ] );

        // Use older `get_terms` function signature for older versions of WP
        if ( version_compare( get_bloginfo('version'), '4.5', '<' ) ) {
            $taxonomy = $args['taxonomy'];
            unset( $args['taxonomy'] );

            $query = get_terms( $taxonomy, $args );
        } else {
            $query = get_terms( $args );
        }

        foreach ( $query as $term_id ) {
            $term = get_term( $term_id );
            $term_taxonomy_obj = get_taxonomy( $term->taxonomy );
            $term_taxonomy_label = isset( $term_taxonomy_obj->singular_label ) ? $term_taxonomy_obj->singular_label : $term_taxonomy_obj->labels->singular_name;
            $available_posts[$term_taxonomy_label][] = (object) [
                'term_id'           => $term->term_id,
                'taxonomy'          => $term->taxonomy, // e.g. genre
                'taxonomy_label'    => $term_taxonomy_label, // e.g. Genre
                'name'              => $term->name, // e.g. Action
            ];
        }

        if ( ! empty( $field->value ) ) {
            $results = $wpdb->get_results( "SELECT term_id, name FROM $wpdb->terms WHERE term_id IN ($field->value) ORDER BY FIELD(term_id,$field->value)" );
            foreach ( $results as $result ) {
                $selected_posts[ $result->term_id ] = $result;
            }
        }
    ?>
        <div class="filter_posts">
            <input type="text" class="cfgroup_filter_input" autocomplete="off" placeholder="<?php _e( 'Search terms', 'admin-site-enhancements' ); ?>" />
        </div>

        <div class="available_posts post_list">
        <?php foreach ( $available_posts as $taxonomy_label => $terms ) : ?>
            <h4 class="taxonomy-label"><?php echo $taxonomy_label; ?></h4>
            <?php foreach ( $terms as $term ) : ?>
                <?php $class = ( isset( $selected_posts[ $term->term_id ] ) ) ? ' class="used"' : ''; ?>
                <div rel="<?php echo $term->term_id; ?>"<?php echo $class; ?>><span class="single-taxonomy-label"><?php echo $taxonomy_label; ?></span><?php echo apply_filters( 'cfgroup_term_display', $term->name, $term->term_id, $field ); ?></div>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </div>

        <div class="selected_posts post_list">
        <?php foreach ( $selected_posts as $term ) : ?>
        <?php
            $term_obj = get_term( $term->term_id );
            $term_taxonomy_obj = get_taxonomy( $term_obj->taxonomy );
            $taxonomy_label = isset( $term_taxonomy_obj->singular_label ) ? $term_taxonomy_obj->singular_label : $term_taxonomy_obj->labels->singular_name;            
        ?>
            <div rel="<?php echo $term->term_id; ?>"><span class="remove"></span><span class="single-taxonomy-label"><?php echo $taxonomy_label; ?></span><?php echo apply_filters( 'cfgroup_term_display', $term->name, $term->term_id, $field ); ?></div>
        <?php endforeach; ?>
        </div>
        <div class="clear"></div>
        <input type="hidden" name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" value="<?php echo $field->value; ?>" />
    <?php
    }


    function options_html( $key, $field ) {
        $args = [ 'public' => true ];
        $choices = apply_filters( 'cfgroup_field_term_taxonomies', get_taxonomies( $args ) );

    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Taxonomies', 'cfgroup'); ?></label>
                <p class="description"><?php _e('Limit terms to the following taxonomies', 'cfgroup'); ?></p>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type'          => 'select',
                        'input_name'    => "cfgroup[fields][$key][options][taxonomies]",
                        'options'       => [ 'multiple' => '1', 'choices' => $choices ],
                        'value'         => $this->get_option( $field, 'taxonomies' ),
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Limits', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <input type="text" name="cfgroup[fields][<?php echo $key; ?>][options][limit_min]" value="<?php echo $this->get_option( $field, 'limit_min' ); ?>" placeholder="min" style="width:60px" />
                <input type="text" name="cfgroup[fields][<?php echo $key; ?>][options][limit_max]" value="<?php echo $this->get_option( $field, 'limit_max' ); ?>" placeholder="max" style="width:60px" />
            </td>
        </tr>
    <?php
    }


    function input_head( $field = null ) {
    ?>
        <script>
        (function($) {
            update_term_values = function(field) {
                var term_ids = [];
                field.find('.selected_posts div').each(function(idx) {
                    term_ids[idx] = $(this).attr('rel');
                });
                field.find('input.term').val(term_ids.join(','));
            }

            $(function() {
                $(document).on('cfgroup/ready', '.cfgroup_add_field', function() {
                    $('.cfgroup_term:not(.ready)').init_term();
                });
                $('.cfgroup_term').init_term();

                // add selected post
                $(document).on('click', '.cfgroup_term .available_posts div', function() {
                    var parent = $(this).closest('.field');
                    var term_id = $(this).attr('rel');
                    var html = $(this).html();
                    $(this).addClass('used');
                    parent.find('.selected_posts').append('<div rel="'+term_id+'"><span class="remove"></span>'+html+'</div>');
                    update_term_values(parent);
                });

                // remove selected post
                $(document).on('click', '.cfgroup_term .selected_posts .remove', function() {
                    var div = $(this).parent();
                    var parent = div.closest('.field');
                    var term_id = div.attr('rel');
                    parent.find('.available_posts div[rel='+term_id+']').removeClass('used');
                    div.remove();
                    update_term_values(parent);
                });

                // filter posts
                $(document).on('keyup', '.cfgroup_term .cfgroup_filter_input', function() {
                    var input = $(this).val();
                    var parent = $(this).closest('.field');
                    var regex = new RegExp(input, 'i');
                    parent.find('.available_posts div:not(.used)').each(function() {
                        if (-1 < $(this).html().search(regex)) {
                            $(this).removeClass('hidden');
                        }
                        else {
                            $(this).addClass('hidden');
                        }
                    });
                });
            });

            $.fn.init_term = function() {
                this.each(function() {
                    var $this = $(this);
                    $this.addClass('ready');

                    // sortable
                    $this.find('.selected_posts').sortable({
                        axis: 'y',
                        update: function(event, ui) {
                            var parent = $(this).closest('.field');
                            update_term_values(parent);
                        }
                    });
                });
            }
        })(jQuery);
        </script>
    <?php
    }


    function prepare_value( $value, $field = null ) {
        return $value;
    }


    function format_value_for_input( $value, $field = null ) {
        return empty( $value ) ? '' : implode( ',', $value );
    }


    function format_value_for_display( $value, $field = null ) {
        return get_cf_terms( explode( ',', $value[0] ), 'names_archive_links' );            
    }


    function pre_save( $value, $field = null ) {
        if ( ! empty( $value ) ) {
            // The raw input from a post edit screen's custom field meta box will save a comma-separated string
            // This is in $field_data for /includes/premium/custom-content/cfgroup/includes/form.php >> init() >> CFG()->save()
            // Let's convert it to an array
            if ( is_string( $value ) ) {
                if ( false !== strpos( $value, ',' ) ) {
                    return explode( ',', $value ); // Turns '123,456' into array( 0 => '123', 1 => '234' )
                } else {
                    return (array) $value; // Turns '1234' into array( 0 => '1234' );
                }
            } else {
                // $value is not a string, i.e. an array
                return $value;                
            }
        }

        return array(); // Return empty array
    }
}
