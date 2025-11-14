<?php

namespace ElementorAseRepeater\Controls;

use Elementor\Control_Switcher;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class AsenhaSwitcherControl extends Control_Switcher {

    public function get_type() {
        return 'asenha_switcher'; 
    }

    protected function get_default_settings() {
        return array_merge(
            parent::get_default_settings(),
            [
                'asenha_custom_switcher' => true,
                'disabled' => true,
                'default' => 'no',
            ]
        );
    }

    public function content_template() {
        $control_uid = $this->get_control_uid();
        ?>
        <# if ( data.asenha_custom_switcher ) { #>
        <div class="elementor-control-field">
            <label for="<?php echo esc_attr( $control_uid ); ?>" class="elementor-control-title">{{{ data.label }}}</label>
            <div class="elementor-control-input-wrapper">
                <label class="elementor-switch elementor-control-unit-2{{data.disabled ? ' elementor-disabled' : ''}}">
                    <input id="<?php echo esc_attr( $control_uid ); ?>" 
                           type="checkbox" 
                           data-setting="{{ data.name }}" 
                           class="elementor-switch-input" 
                           value="no"
                           {{data.disabled ? ' disabled' : ''}}
                           {{data.default === 'yes' ? ' checked="checked"' : ''}}>
                    <span class="elementor-switch-label" data-on="{{ data.label_on }}" data-off="{{ data.label_off }}"></span>
                    <span class="elementor-switch-handle"></span>
                </label>
            </div>
        </div>
        <# if ( data.description ) { #>
        <div class="elementor-control-field-description">{{{ data.description }}}</div>
        <# } #>
        <# } else { #>
        <?php parent::content_template(); ?>
        <# } #>
        <?php
    }

    public function get_default_value() {
        return 'yes';
    }
}