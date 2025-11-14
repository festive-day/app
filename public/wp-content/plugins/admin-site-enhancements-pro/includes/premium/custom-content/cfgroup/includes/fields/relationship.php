<?php

class cfgroup_relationship extends cfgroup_field
{

    function __construct() {
        $this->name = 'relationship';
        $this->label = __( 'Relationship', 'admin-site-enhancements' );
    }


    function html( $field ) {
        global $wpdb;

        $selected_posts = [];
        $available_posts = [];

        $post_types = [];
        if ( ! empty( $field->options['post_types'] ) ) {
            foreach ( $field->options['post_types'] as $type ) {
                $post_types[] = $type;
            }
        }
        else {
            $post_types = get_post_types( [ 'exclude_from_search' => true ] );
        }

        $args = [
            'post_type'         => $post_types,
            'post_status'       => [ 'publish', 'private' ],
            'posts_per_page'    => -1,
            'orderby'           => 'title',
            'order'             => 'ASC'
        ];

        $args = apply_filters( 'cfgroup_field_relationship_query_args', $args, [ 'field' => $field ] );
        $query = new WP_Query( $args );

        foreach ( $query->posts as $post_obj ) {
            $post_title = ( 'private' == $post_obj->post_status ) ? '(Private) ' . $post_obj->post_title : $post_obj->post_title;
            $post_type_obj = get_post_type_object( $post_obj->post_type );
            $post_type_label = $post_type_obj->labels->singular_name;
            $available_posts[$post_type_label][] = (object) [
                'ID'                => $post_obj->ID,
                'post_type'         => $post_obj->post_type,
                'post_type_label'   => $post_type_label,
                'post_status'       => $post_obj->post_status,
                'post_title'        => $post_title,
            ];
        }

        if ( ! empty( $field->value ) ) {
            $results = $wpdb->get_results( "SELECT ID, post_status, post_title FROM $wpdb->posts WHERE ID IN ($field->value) ORDER BY FIELD(ID,$field->value)" );
            foreach ( $results as $result ) {
                $result->post_title = ( 'private' == $result->post_status ) ? '(Private) ' . $result->post_title : $result->post_title;
                $selected_posts[ $result->ID ] = $result;
            }
        }
    ?>
        <div class="filter_posts">
            <input type="text" class="cfgroup_filter_input" autocomplete="off" placeholder="<?php _e( 'Search...', 'admin-site-enhancements' ); ?>" />
        </div>

        <div class="available_posts post_list">
        <?php foreach ( $available_posts as $post_type_label => $posts ) : ?>
            <h4 class="post-type-label"><?php echo $post_type_label; ?></h4>
            <?php foreach ( $posts as $post ) : ?>
                <?php $class = ( isset( $selected_posts[ $post->ID ] ) ) ? ' class="used"' : ''; ?>
                <div rel="<?php echo $post->ID; ?>"<?php echo $class; ?>><span class="single-post-post-type-label"><?php echo $post_type_label; ?></span><?php echo apply_filters( 'cfgroup_relationship_display', $post->post_title, $post->ID, $field ); ?></div>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </div>

        <div class="selected_posts post_list">
        <?php foreach ( $selected_posts as $post ) : ?>
            <?php
            $post_obj = get_post( $post->ID );
            $post_type_obj = get_post_type_object( $post_obj->post_type );
            $post_type_label = $post_type_obj->labels->singular_name;
            ?>
            <div rel="<?php echo $post->ID; ?>"><span class="remove"></span><span class="single-post-post-type-label"><?php echo $post_type_label; ?></span><?php echo apply_filters( 'cfgroup_relationship_display', $post->post_title, $post->ID, $field ); ?></div>
        <?php endforeach; ?>
        </div>
        <div class="clear"></div>
        <input type="hidden" name="<?php echo esc_attr( $field->input_name ); ?>" class="<?php echo esc_attr( $field->input_class ); ?>" value="<?php echo $field->value; ?>" />
    <?php
    }


    function options_html( $key, $field ) {
        $args = [ 'exclude_from_search' => false ];
        $choices = apply_filters( 'cfgroup_field_relationship_post_types', get_post_types( $args ) );

    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Post Types', 'cfgroup'); ?></label>
                <p class="description"><?php _e('Limit posts to the following types', 'cfgroup'); ?></p>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type'          => 'select',
                        'input_name'    => "cfgroup[fields][$key][options][post_types]",
                        'options'       => [ 'multiple' => '1', 'choices' => $choices ],
                        'value'         => $this->get_option( $field, 'post_types' ),
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
            update_relationship_values = function(field) {
                var post_ids = [];
                field.find('.selected_posts div').each(function(idx) {
                    post_ids[idx] = $(this).attr('rel');
                });
                field.find('input.relationship').val(post_ids.join(','));
            }

            $(function() {
                $(document).on('cfgroup/ready', '.cfgroup_add_field', function() {
                    $('.cfgroup_relationship:not(.ready)').init_relationship();
                });
                $('.cfgroup_relationship').init_relationship();

                // add selected post
                $(document).on('click', '.cfgroup_relationship .available_posts div', function() {
                    var parent = $(this).closest('.field');
                    var post_id = $(this).attr('rel');
                    var html = $(this).html();
                    $(this).addClass('used');
                    parent.find('.selected_posts').append('<div rel="'+post_id+'"><span class="remove"></span>'+html+'</div>');
                    update_relationship_values(parent);
                });

                // remove selected post
                $(document).on('click', '.cfgroup_relationship .selected_posts .remove', function() {
                    var div = $(this).parent();
                    var parent = div.closest('.field');
                    var post_id = div.attr('rel');
                    parent.find('.available_posts div[rel='+post_id+']').removeClass('used');
                    div.remove();
                    update_relationship_values(parent);
                });

                // filter posts
                $(document).on('keyup', '.cfgroup_relationship .cfgroup_filter_input', function() {
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

            $.fn.init_relationship = function() {
                this.each(function() {
                    var $this = $(this);
                    $this.addClass('ready');

                    // sortable
                    $this.find('.selected_posts').sortable({
                        axis: 'y',
                        update: function(event, ui) {
                            var parent = $(this).closest('.field');
                            update_relationship_values(parent);
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
        return cf_titles_only_v( $field->name, 'titles_only_v__ul', explode( ',', $value[0] ) );
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
