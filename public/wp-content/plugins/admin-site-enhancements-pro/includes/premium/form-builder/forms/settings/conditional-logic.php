<?php
defined( 'ABSPATH' ) || die();
?>
<div class="fb-form-container fb-grid-container">
    <div class="fb-form-row">
        <div class="fb-condition-repeater-blocks">
            <?php
            $conditional_logics = Form_Builder_Builder::get_show_hide_conditions( $id );
            foreach ( $conditional_logics as $key => $row ) {
                ?>
                <div class="fb-condition-repeater-block">
                    <select name="condition_action[]" required>
                        <option value="show" <?php
                        if ( isset( $row['condition_action'] ) ) {
                            selected( $row['condition_action'], 'show' );
                        }
                        ?>><?php echo esc_html( 'Show', 'admin-site-enhancements' ); ?></option>
                        <option value="hide" <?php
                        if ( isset( $row['condition_action'] ) ) {
                            selected( $row['condition_action'], 'hide' );
                        }
                        ?>><?php echo esc_html( 'Hide', 'admin-site-enhancements' ); ?></option>
                    </select>
                    <select name="compare_from[]" required>
                        <option value=""><?php echo esc_html( 'Select field', 'admin-site-enhancements' ); ?></option>
                        <?php
                        foreach ( $fields as $field ) {
                            if ( ! ( $field->type == 'heading' || $field->type == 'paragraph' || $field->type == 'separator' || $field->type == 'spacer' || $field->type == 'image' || $field->type == 'altcha' || $field->type == 'captcha' || $field->type == 'turnstile' ) ) {
                                ?>
                                <option value="<?php echo esc_attr( $field->id ); ?>" <?php
                                   if ( isset( $row['compare_from'] ) ) {
                                       selected( $row['compare_from'], $field->id );
                                   }
                                   ?>><?php echo esc_html( $field->name ) . ' (ID: ' . esc_attr( $field->id ) . ' )'; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                    <span class="fb-condition-seperator"><?php echo esc_html( 'if', 'admin-site-enhancements' ); ?></span>
                    <select name="compare_to[]" required>
                        <option value=""><?php echo esc_html( 'Select field', 'admin-site-enhancements' ); ?></option>
                        <?php
                        foreach ( $fields as $field ) {
                            if ( ! ( $field->type == 'heading' || $field->type == 'paragraph' || $field->type == 'separator' || $field->type == 'spacer' || $field->type == 'image' || $field->type == 'altcha' || $field->type == 'captcha' || $field->type == 'turnstile' || $field->type == 'name' || $field->type == 'address' ) ) {
                                ?>
                                <option value="<?php echo esc_attr( $field->id ); ?>" <?php
                                   if ( isset( $row['compare_to'] ) ) {
                                       selected( $row['compare_to'], $field->id );
                                   }
                                   ?>><?php echo esc_html( $field->name ) . ' (ID: ' . esc_attr( $field->id ) . ' )'; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                    <select name="compare_condition[]" required>
                        <option value="equal" <?php
                        if ( isset( $row['compare_condition'] ) ) {
                            selected( $row['compare_condition'], 'equal' );
                        }
                        ?>><?php echo esc_html( 'is', 'admin-site-enhancements' ); ?></option>
                        <option value="not_equal" <?php
                        if ( isset( $row['compare_condition'] ) ) {
                            selected( $row['compare_condition'], 'not_equal' );
                        }
                        ?>><?php echo esc_html( 'is not', 'admin-site-enhancements' ); ?></option>
                        <option value="greater_than" <?php
                        if ( isset( $row['compare_condition'] ) ) {
                            selected( $row['compare_condition'], 'greater_than' );
                        }
                        ?>><?php echo esc_html( 'is greater than', 'admin-site-enhancements' ); ?></option>
                        <option value="greater_than_or_equal" <?php
                        if ( isset( $row['compare_condition'] ) ) {
                            selected( $row['compare_condition'], 'greater_than_or_equal' );
                        }
                        ?>><?php echo esc_html( 'is greater than or equal to', 'admin-site-enhancements' ); ?></option>
                        <option value="less_than" <?php
                        if ( isset( $row['compare_condition'] ) ) {
                            selected( $row['compare_condition'], 'less_than' );
                        }
                        ?>><?php echo esc_html( 'is less than', 'admin-site-enhancements' ); ?></option>
                        <option value="less_than_or_equal" <?php
                        if ( isset( $row['compare_condition'] ) ) {
                            selected( $row['compare_condition'], 'less_than_or_equal' );
                        }
                        ?>><?php echo esc_html( 'is less than or equal to', 'admin-site-enhancements' ); ?></option>
                        <option value="is_like" <?php
                        if ( isset( $row['compare_condition'] ) ) {
                            selected( $row['compare_condition'], 'is_like' );
                        }
                        ?>><?php echo esc_html( 'contains', 'admin-site-enhancements' ); ?></option>
                        <option value="is_not_like" <?php
                        if ( isset( $row['compare_condition'] ) ) {
                            selected( $row['compare_condition'], 'is_not_like' );
                        }
                        ?>><?php echo esc_html( 'does not contain', 'admin-site-enhancements' ); ?></option>
                    </select>
                    <input type="text" name="compare_value[]" value="<?php echo esc_attr( $row['compare_value'] ); ?>" required />
                    <span class="fb-condition-remove"><?php echo wp_kses( Form_Builder_Icons::get( 'delete' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>
                </div>
            <?php } ?>
        </div>
        <button class="fb-add-more-condition"><?php echo esc_html__( 'Add Condition', 'admin-site-enhancements' ); ?></button>
    </div>
</div>