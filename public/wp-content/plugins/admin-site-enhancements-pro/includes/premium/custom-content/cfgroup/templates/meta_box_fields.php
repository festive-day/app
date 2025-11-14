<input type="hidden" name="cfgroup[save]" value="<?php echo wp_create_nonce('cfgroup_save_fields'); ?>" />

<div class="fields-header">
    <!-- Icon: https://icon-sets.iconify.design/ooui/draggable/ -->
    <div class="field-header field-order"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path fill="currentColor" d="M2 11h16v2H2zm0-4h16v2H2zm11 8H7l3 3zM7 5h6l-3-3z"/></svg></div>
    <div class="field-header field-label"><?php echo __( 'Label', 'admin-site-enhancements' ); ?></div>
    <div class="field-header field-key"><?php echo __( 'Name', 'admin-site-enhancements' ); ?></div>
    <div class="field-header field-type"><?php echo __( 'Type', 'admin-site-enhancements' ); ?></div>
    <div class="field-header field-width"><?php echo __( 'Width', 'admin-site-enhancements' ); ?></div>
    <!-- Icon: https://icon-sets.iconify.design/ph/caret-double-down-bold/ -->
    <div class="field-header field-toggle"><a class="cfgroup_toggle_fields"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 256 256"><path fill="currentColor" d="M216.49 119.51a12 12 0 0 1 0 17l-80 80a12 12 0 0 1-17 0l-80-80a12 12 0 1 1 17-17L128 191l71.51-71.52a12 12 0 0 1 16.98.03Zm-97 17a12 12 0 0 0 17 0l80-80a12 12 0 0 0-17-17L128 111L56.49 39.51a12 12 0 0 0-17 17Z"/></svg></a></div>
</div>

<ul class="fields connected-sortable">
<?php

global $post;

$results = CFG()->api->get_input_fields( [ 'group_id' => $post->ID ] );
// vi( $results );

/*---------------------------------------------------------------------------------------------
    Create <ul> based on field structure
---------------------------------------------------------------------------------------------*/

$level = 0;
$levels = [];
$last_level = $diff = 0;

foreach ( $results as $field ) {

    // Skip missing field types
    if ( ! isset( CFG()->fields[ $field->type ] ) ) {
        continue;
    }

    $level = 0;
    if ( 0 < (int) $field->parent_id ) {
        $level = isset( $levels[ $field->parent_id ] ) ? $levels[ $field->parent_id ] + 1 : 1;
        $levels[ $field->id ] = (int) $level;
    }
    $diff = ( $level - $last_level );
    $last_level = $level;

    if ( 0 < $diff ) {
        for ( $i = 0; $i < ( $diff - 1 ); $i++ ) {
            echo '<div class="connected-sortable-wrapper"><ul class="connected-sortable"><li>';
        }
        echo '<div class="connected-sortable-wrapper"><ul class="connected-sortable">';
    }
    elseif ( 0 > $diff ) {
        for ( $i = 0; $i < abs( $diff ); $i++ ) {
            echo '</li></ul></div>';
        }
    }

    // echo ( 'repeater' == $field->type ) ? '<li class="repeater sortable-item">' : '<li class="sortable-item">';
    echo '<li class="' . esc_attr( $field->type ) . ' sortable-item">';

    CFG()->field_html( $field );
}

for ( $i = 0; $i < abs($level); $i++ ) {
    echo '</li></ul>';
}

echo '</li>';

?>
</ul>

<div class="table_footer">
    <input type="button" class="button-primary cfgroup_add_field" value="<?php _e('Add New Field', 'admin-site-enhancements'); ?>" />
</div>