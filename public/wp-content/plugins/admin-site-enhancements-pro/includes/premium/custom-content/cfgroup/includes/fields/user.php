<?php

class cfgroup_user extends cfgroup_field
{

    function __construct() {
        $this->name = 'user';
        $this->label = __( 'User', 'admin-site-enhancements' );
    }


    function html( $field ) {
        global $wpdb;

        $selected_users = [];
        $available_users = [];

        $results = $wpdb->get_results( "SELECT ID, user_login FROM $wpdb->users ORDER BY user_login" );
        foreach ( $results as $result ) {
            $available_users[] = $result;
        }

        if ( ! empty( $field->value ) ) {
            $results = $wpdb->get_results( "SELECT ID, user_login FROM $wpdb->users WHERE ID IN ($field->value) ORDER BY FIELD(ID,$field->value)" );
            foreach ( $results as $result ) {
                $selected_users[ $result->ID ] = $result;
            }
        }
    ?>
        <div class="filter_posts">
            <input type="text" class="cfgroup_filter_input" autocomplete="off" placeholder="<?php _e( 'Search users', 'admin-site-enhancements' ); ?>" />
        </div>

        <div class="available_posts post_list">
        <?php foreach ( $available_users as $user ) : ?>
            <?php
            $user_obj = get_user_by( 'id', $user->ID );
            ?>
            <?php $class = ( isset( $selected_users[ $user->ID ] ) ) ? ' class="used"' : ''; ?>
            <div rel="<?php echo $user->ID; ?>"<?php echo $class; ?>><?php echo $user_obj->display_name?><?php if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) { ?> <span class="user-info-faded">(<?php echo apply_filters( 'cfgroup_user_display', $user->user_login, $user->ID, $field ); ?> | <?php echo $user_obj->user_email?>)</span><?php } ?></div>
        <?php endforeach; ?>
        </div>

        <div class="selected_posts post_list">
        <?php foreach ( $selected_users as $user ) : ?>
            <?php
            $user_obj = get_user_by( 'id', $user->ID );
            ?>
            <div rel="<?php echo $user->ID; ?>"><span class="remove"></span><?php echo $user_obj->display_name?><?php if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) { ?> <span class="user-info-faded">(<?php echo apply_filters( 'cfgroup_user_display', $user->user_login, $user->ID, $field ); ?> | <?php echo $user_obj->user_email?>)</span><?php } ?></div>
        <?php endforeach; ?>
        </div>
        <div class="clear"></div>
        <input type="hidden" name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" value="<?php echo $field->value; ?>" />
    <?php
    }


    function options_html( $key, $field ) {
    ?>
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
            update_user_values = function(field) {
                var post_ids = [];
                field.find('.selected_posts div').each(function(idx) {
                    post_ids[idx] = $(this).attr('rel');
                });
                field.find('input.user').val(post_ids.join(','));
            }

            $(function() {
                $(document).on('cfgroup/ready', '.cfgroup_add_field', function() {
                    $('.cfgroup_user:not(.ready)').init_user();
                });
                $('.cfgroup_user').init_user();

                // add selected post
                $(document).on('click', '.cfgroup_user .available_posts div', function() {
                    var parent = $(this).closest('.field');
                    var post_id = $(this).attr('rel');
                    var html = $(this).html();
                    $(this).addClass('used');
                    parent.find('.selected_posts').append('<div rel="'+post_id+'"><span class="remove"></span>'+html+'</div>');
                    update_user_values(parent);
                });

                // remove selected post
                $(document).on('click', '.cfgroup_user .selected_posts .remove', function() {
                    var div = $(this).parent();
                    var parent = div.closest('.field');
                    var post_id = div.attr('rel');
                    parent.find('.available_posts div[rel='+post_id+']').removeClass('used');
                    div.remove();
                    update_user_values(parent);
                });

                // filter posts
                $(document).on('keyup', '.cfgroup_user .cfgroup_filter_input', function() {
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

            $.fn.init_user = function() {
                this.each(function() {
                    var $this = $(this);
                    $this.addClass('ready');

                    // sortable
                    $this.find('.selected_posts').sortable({
                        axis: 'y',
                        update: function(event, ui) {
                            var parent = $(this).closest('.field');
                            update_user_values(parent);
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
        return get_cf_users( explode( ',', $value[0] ), 'full_names' );
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
