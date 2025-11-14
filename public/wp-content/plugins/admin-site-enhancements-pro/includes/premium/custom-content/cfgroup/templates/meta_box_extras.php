<?php

global $wpdb;

// Post types
$post_types = [];
$types = get_post_types( [ 'public' => true ] );

foreach ( $types as $post_type ) {
    if ( ! in_array( $post_type, [ 'asenha_cfgroup', 'attachment' ] ) ) {
        $post_types[] = $post_type;
    }
}

$extras = (array) get_post_meta( $post->ID, 'cfgroup_extras', true );

if ( ! isset( $extras['order'] ) ) {
    $extras['order'] = 0;
}
if ( ! isset( $extras['context'] ) ) {
    $extras['context'] = 'normal';
}
if ( ! isset( $extras['hide_editor'] ) ) {
    $extras['hide_editor'] = '';
}

?>

<table>
    <tr>
        <td class="label">
            <label>
                <?php _e( 'Order', 'admin-site-enhancements' ); ?>
                <div class="cfgroup_tooltip">?
                    <div class="tooltip_inner"><?php _e( 'The field group with the lowest order will appear first.', 'admin-site-enhancements' ); ?></div>
                </div>
            </label>
        </td>
        <td style="vertical-align:top">
            <input type="text" name="cfgroup[extras][order]" value="<?php echo $extras['order']; ?>" style="width:80px" />
        </td>
    </tr>
    <tr>
        <td class="label">
            <label><?php _e( 'Position', 'admin-site-enhancements' ); ?></label>
        </td>
        <td style="vertical-align:top">
            <input type="radio" name="cfgroup[extras][context]" value="normal"<?php echo ( $extras['context'] == 'normal' ) ? ' checked' : ''; ?> /> <?php _e( 'Normal', 'admin-site-enhancements' ); ?> &nbsp; &nbsp;
            <input type="radio" name="cfgroup[extras][context]" value="side"<?php echo ( $extras['context'] == 'side' ) ? ' checked' : ''; ?> /> <?php _e( 'Side', 'admin-site-enhancements' ); ?>
        </td>
    </tr>
    <tr>
        <td class="label">
            <label><?php _e( 'Display Settings', 'admin-site-enhancements' ); ?></label>
        </td>
        <td style="vertical-align:top">
            <div>
                <?php
                    CFG()->create_field( [
                        'type'          => 'true_false',
                        'input_name'    => "cfgroup[extras][hide_editor]",
                        'input_class'   => 'true_false',
                        'value'         => $extras['hide_editor'],
                        'options'       => [ 'message' => __( 'Hide the content editor', 'admin-site-enhancements' ) ],
                    ] );
                ?>
            </div>
        </td>
    </tr>

</table>