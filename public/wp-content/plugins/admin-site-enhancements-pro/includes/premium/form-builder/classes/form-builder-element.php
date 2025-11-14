<?php
defined( 'ABSPATH' ) || die();

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;

class Form_Builder_Element extends Widget_Base {

    public function get_name() {
        return 'Form Builder';
    }

    public function get_title() {
        return esc_html__( 'Form Builder', 'admin-site-enhancements' );
    }

    public function get_icon() {
        return 'hfi hfi-form';
    }

    public function get_categories() {
        return array( 'basic' );
    }

    public function get_keywords() {
        return array( 'Form', 'Form Builder', 'Form' );
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_title', [
                'label' => esc_html__( 'Form', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'hf_form_id', [
                'label' => esc_html__( 'Select Form', 'admin-site-enhancements' ),
                'type' => Controls_Manager::SELECT2,
                'options' => Form_Builder_Helper::get_all_forms_list_options(),
                'multiple' => false,
                'label_block' => true,
                'separator' => 'after'
            ]
        );

        $this->add_control(
            'new_form', [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => sprintf(
                    wp_kses(esc_html__( 'To Create New Form', 'admin-site-enhancements' ) . ' <a href="%s" target="_blank">' . esc_html__( 'Click Here', 'admin-site-enhancements' ) . '</a>', [
                        'b' => [],
                        'br' => [],
                        'a' => [
                            'href' => [],
                            'target' => [],
                        ],
                    ] ), esc_url(add_query_arg( 'page', 'formbuilder', admin_url( 'admin.php' ) ))
                )
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section(
            'enable_style', [
                'label' => esc_html__( 'Custom Style', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'enable_custom_style', [
                'label' => __( 'Enable Custom Style', 'totalplus' ),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section(
            'form_style', [
                'label' => esc_html__( 'Form', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'column_gap', [
                'label' => __( 'Column Gap', 'admin-site-enhancements' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 80,
                        'step' => 1,
                    ]
                ],
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-form-column-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'row_gap', [
                'label' => __( 'Row Gap', 'admin-site-enhancements' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 80,
                        'step' => 1,
                    ]
                ],
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-form-row-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'label_style', [
                'label' => esc_html__( 'Label', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(), [
                'name' => 'label_typography',
                'label' => esc_html__( 'Typography', 'admin-site-enhancements' ),
                'selector' => '{{WRAPPER}} .fb-container .fb-field-label',
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'label_color', [
                'label' => esc_html__( 'Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-label-typo-font-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'required_color', [
                'label' => esc_html__( 'Required Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-label-required-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'label_spacing', [
                'label' => esc_html__( 'Spacing', 'admin-site-enhancements' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-label-spacing-top: {{TOP}}{{UNIT}};--fb-label-spacing: {{BOTTOM}}{{UNIT}};--fb-label-spacing-left: {{LEFT}}{{UNIT}};--fb-label-spacing-right: {{RIGHT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'description_style', [
                'label' => esc_html__( 'Description', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(), [
                'name' => 'description_typography',
                'label' => esc_html__( 'Typography', 'admin-site-enhancements' ),
                'selector' => '{{WRAPPER}} .fb-container .fb-field-desc',
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'description_color', [
                'label' => esc_html__( 'Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-desc-typo-font-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'description_spacing', [
                'label' => esc_html__( 'Spacing', 'admin-site-enhancements' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-desc-spacing: {{TOP}}{{UNIT}};--fb-desc-spacing-bottom: {{BOTTOM}}{{UNIT}};--fb-desc-spacing-left: {{LEFT}}{{UNIT}};--fb-desc-spacing-right: {{RIGHT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'fields_style', [
                'label' => esc_html__( 'Fields', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(), [
                'name' => 'fields_typography',
                'label' => esc_html__( 'Typography', 'admin-site-enhancements' ),
                'selector' => '{{WRAPPER}} .fb-container input[type="text"], {{WRAPPER}} .fb-container input[type="email"], {{WRAPPER}} .fb-container input[type="url"], {{WRAPPER}} .fb-container input[type="password"], {{WRAPPER}} .fb-container input[type="search"], {{WRAPPER}} .fb-container input[type="number"], {{WRAPPER}} .fb-container input[type="tel"], {{WRAPPER}} .fb-container input[type="range"], {{WRAPPER}} .fb-container input[type="date"], {{WRAPPER}} .fb-container input[type="month"], {{WRAPPER}} .fb-container input[type="week"], {{WRAPPER}} .fb-container input[type="time"], {{WRAPPER}} .fb-container input[type="datetime"], {{WRAPPER}} .fb-container input[type="datetime-local"], {{WRAPPER}} .fb-container input[type="color"], {{WRAPPER}} .fb-container textarea, {{WRAPPER}} .fb-container select',
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->start_controls_tabs(
            'fields_style_tabs'
        );

        $this->start_controls_tab(
            'fields_normal_tab', [
                'label' => esc_html__( 'Normal', 'textdomain' ),
            ]
        );

        $this->add_control(
            'fields_color', [
                'label' => esc_html__( 'Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-field-color-normal: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'fields_bg_color', [
                'label' => esc_html__( 'Background Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-field-bg-color-normal: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(), [
                'name' => 'fields_box_shadow',
                'selector' => '{{WRAPPER}} .fb-container input[type="text"], {{WRAPPER}} .fb-container input[type="email"], {{WRAPPER}} .fb-container input[type="url"], {{WRAPPER}} .fb-container input[type="password"], {{WRAPPER}} .fb-container input[type="search"], {{WRAPPER}} .fb-container input[type="number"], {{WRAPPER}} .fb-container input[type="tel"], {{WRAPPER}} .fb-container input[type="range"], {{WRAPPER}} .fb-container input[type="date"], {{WRAPPER}} .fb-container input[type="month"], {{WRAPPER}} .fb-container input[type="week"], {{WRAPPER}} .fb-container input[type="time"], {{WRAPPER}} .fb-container input[type="datetime"], {{WRAPPER}} .fb-container input[type="datetime-local"], {{WRAPPER}} .fb-container input[type="color"], {{WRAPPER}} .fb-container textarea, {{WRAPPER}} .fb-container select'
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(), [
                'name' => 'fields_border',
                'selector' => '{{WRAPPER}} .fb-container input[type="text"], {{WRAPPER}} .fb-container input[type="email"], {{WRAPPER}} .fb-container input[type="url"], {{WRAPPER}} .fb-container input[type="password"], {{WRAPPER}} .fb-container input[type="search"], {{WRAPPER}} .fb-container input[type="number"], {{WRAPPER}} .fb-container input[type="tel"], {{WRAPPER}} .fb-container input[type="range"], {{WRAPPER}} .fb-container input[type="date"], {{WRAPPER}} .fb-container input[type="month"], {{WRAPPER}} .fb-container input[type="week"], {{WRAPPER}} .fb-container input[type="time"], {{WRAPPER}} .fb-container input[type="datetime"], {{WRAPPER}} .fb-container input[type="datetime-local"], {{WRAPPER}} .fb-container input[type="color"], {{WRAPPER}} .fb-container textarea, {{WRAPPER}} .fb-container select'
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'fields_hover_tab', [
                'label' => esc_html__( 'Focus', 'textdomain' ),
            ]
        );

        $this->add_control(
            'fields_color_focus', [
                'label' => esc_html__( 'Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-field-color-focus: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'fields_bg_color_focus', [
                'label' => esc_html__( 'Background Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-field-bg-color-focus: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(), [
                'name' => 'fields_box_shadow_focus',
                'selector' => '{{WRAPPER}} .fb-container input[type="text"]:focus, {{WRAPPER}} .fb-container input[type="email"]:focus, {{WRAPPER}} .fb-container input[type="url"]:focus, {{WRAPPER}} .fb-container input[type="password"]:focus, {{WRAPPER}} .fb-container input[type="search"]:focus, {{WRAPPER}} .fb-container input[type="number"]:focus, {{WRAPPER}} .fb-container input[type="tel"]:focus, {{WRAPPER}} .fb-container input[type="range"]:focus, {{WRAPPER}} .fb-container input[type="date"]:focus, {{WRAPPER}} .fb-container input[type="month"]:focus, {{WRAPPER}} .fb-container input[type="week"]:focus, {{WRAPPER}} .fb-container input[type="time"]:focus, {{WRAPPER}} .fb-container input[type="datetime"]:focus, {{WRAPPER}} .fb-container input[type="datetime-local"]:focus, {{WRAPPER}} .fb-container input[type="color"]:focus, {{WRAPPER}} .fb-container textarea:focus, {{WRAPPER}} .fb-container select'
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(), [
                'name' => 'fields_border_focus',
                'selector' => '{{WRAPPER}} .fb-container input[type="text"]:focus, {{WRAPPER}} .fb-container input[type="email"]:focus, {{WRAPPER}} .fb-container input[type="url"]:focus, {{WRAPPER}} .fb-container input[type="password"]:focus, {{WRAPPER}} .fb-container input[type="search"]:focus, {{WRAPPER}} .fb-container input[type="number"]:focus, {{WRAPPER}} .fb-container input[type="tel"]:focus, {{WRAPPER}} .fb-container input[type="range"]:focus, {{WRAPPER}} .fb-container input[type="date"]:focus, {{WRAPPER}} .fb-container input[type="month"]:focus, {{WRAPPER}} .fb-container input[type="week"]:focus, {{WRAPPER}} .fb-container input[type="time"]:focus, {{WRAPPER}} .fb-container input[type="datetime"]:focus, {{WRAPPER}} .fb-container input[type="datetime-local"]:focus, {{WRAPPER}} .fb-container input[type="color"]:focus, {{WRAPPER}} .fb-container textarea:focus, {{WRAPPER}} .fb-container select'
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'fields_border_radius', [
                'label' => esc_html__( 'Border Radius', 'admin-site-enhancements' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-field-border-radius-top: {{TOP}}{{UNIT}};--fb-field-border-radius-bottom: {{BOTTOM}}{{UNIT}};--fb-field-border-radius-left: {{LEFT}}{{UNIT}};--fb-field-border-radius-right: {{RIGHT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'fields_padding', [
                'label' => esc_html__( 'Padding', 'admin-site-enhancements' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-field-padding-top: {{TOP}}{{UNIT}};--fb-field-padding-bottom: {{BOTTOM}}{{UNIT}};--fb-field-padding-left: {{LEFT}}{{UNIT}};--fb-field-padding-right: {{RIGHT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'upload_style', [
                'label' => esc_html__( 'Upload Button', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(), [
                'name' => 'upload_typography',
                'label' => esc_html__( 'Typography', 'admin-site-enhancements' ),
                'selector' => '{{WRAPPER}} .fb-file-uploader .qq-upload-button',
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->start_controls_tabs(
            'upload_style_tabs'
        );

        $this->start_controls_tab(
            'upload_normal_tab', [
                'label' => esc_html__( 'Normal', 'textdomain' ),
            ]
        );

        $this->add_control(
            'upload_color', [
                'label' => esc_html__( 'Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-upload-color-normal: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'upload_bg_color', [
                'label' => esc_html__( 'Background Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-upload-bg-color-normal: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(), [
                'name' => 'upload_box_shadow',
                'selector' => '{{WRAPPER}} .fb-file-uploader .qq-upload-button',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(), [
                'name' => 'upload_border',
                'selector' => '{{WRAPPER}} .fb-file-uploader .qq-upload-button',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'upload_hover_tab', [
                'label' => esc_html__( 'Hover', 'textdomain' ),
            ]
        );

        $this->add_control(
            'upload_color_hover', [
                'label' => esc_html__( 'Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-upload-color-hover: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'upload_bg_color_hover', [
                'label' => esc_html__( 'Background Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-upload-bg-color-hover: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(), [
                'name' => 'upload_box_shadow_hover',
                'selector' => '{{WRAPPER}} .fb-file-uploader .qq-upload-button-hover',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(), [
                'name' => 'upload_border_hover',
                'selector' => '{{WRAPPER}} .fb-file-uploader .qq-upload-button-hover',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'upload_border_radius', [
                'label' => esc_html__( 'Border Radius', 'admin-site-enhancements' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-upload-border-radius-top: {{TOP}}{{UNIT}};--fb-upload-border-radius-bottom: {{BOTTOM}}{{UNIT}};--fb-upload-border-radius-left: {{LEFT}}{{UNIT}};--fb-upload-border-radius-right: {{RIGHT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'upload_padding', [
                'label' => esc_html__( 'Padding', 'admin-site-enhancements' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-upload-border-top: {{TOP}}{{UNIT}};--fb-upload-border-bottom: {{BOTTOM}}{{UNIT}};--fb-upload-border-left: {{LEFT}}{{UNIT}};--fb-upload-border-right: {{RIGHT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'button_style', [
                'label' => esc_html__( 'Submit Button', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(), [
                'name' => 'button_typography',
                'label' => esc_html__( 'Typography', 'admin-site-enhancements' ),
                'selector' => '{{WRAPPER}} .fb-container button, .fb-container input[type="button"], .fb-container input[type="reset"], .fb-container input[type="submit"]',
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->start_controls_tabs(
            'button_style_tabs'
        );

        $this->start_controls_tab(
            'button_normal_tab', [
                'label' => esc_html__( 'Normal', 'textdomain' ),
            ]
        );

        $this->add_control(
            'button_color', [
                'label' => esc_html__( 'Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-button-color-normal: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_bg_color', [
                'label' => esc_html__( 'Background Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-button-bg-color-normal: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(), [
                'name' => 'button_box_shadow',
                'selector' => '{{WRAPPER}} .fb-container button, .fb-container input[type="button"], .fb-container input[type="reset"], .fb-container input[type="submit"]',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(), [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .fb-container button, .fb-container input[type="button"], .fb-container input[type="reset"], .fb-container input[type="submit"]',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'button_hover_tab', [
                'label' => esc_html__( 'Hover', 'textdomain' ),
            ]
        );

        $this->add_control(
            'button_color_hover', [
                'label' => esc_html__( 'Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-button-color-hover: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_bg_color_hover', [
                'label' => esc_html__( 'Background Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-button-bg-color-hover: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(), [
                'name' => 'button_box_shadow_hover',
                'selector' => '{{WRAPPER}} .fb-container button:hover, .fb-container input[type="button"]:hover, .fb-container input[type="reset"]:hover, .fb-container input[type="submit"]:hover',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(), [
                'name' => 'button_border_hover',
                'selector' => '{{WRAPPER}} .fb-container button:hover, .fb-container input[type="button"]:hover, .fb-container input[type="reset"]:hover, .fb-container input[type="submit"]:hover',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'button_border_radius', [
                'label' => esc_html__( 'Border Radius', 'admin-site-enhancements' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-button-border-radius-top: {{TOP}}{{UNIT}};--fb-button-border-radius-bottom: {{BOTTOM}}{{UNIT}};--fb-button-border-radius-left: {{LEFT}}{{UNIT}};--fb-button-border-radius-right: {{RIGHT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_padding', [
                'label' => esc_html__( 'Padding', 'admin-site-enhancements' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-button-padding-top: {{TOP}}{{UNIT}};--fb-button-padding-bottom: {{BOTTOM}}{{UNIT}};--fb-button-padding-left: {{LEFT}}{{UNIT}};--fb-button-padding-right: {{RIGHT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'Validation_style', [
                'label' => esc_html__( 'Validation Text', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(), [
                'name' => 'validation_typography',
                'label' => esc_html__( 'Typography', 'admin-site-enhancements' ),
                'selector' => '{{WRAPPER}} .fb-container .fb-error-msg',
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'validation_color', [
                'label' => esc_html__( 'Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-validation-typo-font-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'validation_text_alignment', [
                'label' => __( 'Text Alignment', 'totalplus' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __( 'Left', 'totalplus' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __( 'Center', 'totalplus' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __( 'Right', 'totalplus' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .fb-container .fb-error-msg{' => '--fb-validation-textalign: {{VALUE}}',
                ],
                'toggle' => true,
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'form_title_style', [
                'label' => esc_html__( 'Form Title', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(), [
                'name' => 'form_title_typography',
                'label' => esc_html__( 'Typography', 'admin-site-enhancements' ),
                'selector' => '{{WRAPPER}} .fb-form-title',
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'form_title_color', [
                'label' => esc_html__( 'Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-form-title-typo-font-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'form_title_spacing', [
                'label' => esc_html__( 'Spacing', 'admin-site-enhancements' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-form-title-spacing-top: {{TOP}}{{UNIT}};--fb-form-title-spacing: {{BOTTOM}}{{UNIT}};--fb-form-title-spacing-left: {{LEFT}}{{UNIT}};--fb-form-title-spacing-right: {{RIGHT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'form_desc_style', [
                'label' => esc_html__( 'Form Description', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(), [
                'name' => 'form_desc_typography',
                'label' => esc_html__( 'Typography', 'admin-site-enhancements' ),
                'selector' => '{{WRAPPER}} .fb-form-description p',
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'form_desc_color', [
                'label' => esc_html__( 'Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-form-desc-typo-font-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'form_desc_spacing', [
                'label' => esc_html__( 'Spacing', 'admin-site-enhancements' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-form-desc-spacing-top: {{TOP}}{{UNIT}};--fb-form-desc-spacing: {{BOTTOM}}{{UNIT}};--fb-form-desc-spacing-left: {{LEFT}}{{UNIT}};--fb-form-desc-spacing-right: {{RIGHT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'heading_style', [
                'label' => esc_html__( 'Heading', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(), [
                'name' => 'heading_typography',
                'label' => esc_html__( 'Typography', 'admin-site-enhancements' ),
                'selector' => '{{WRAPPER}} .fb-form-field.formbuilder-field-type-heading h1, {{WRAPPER}} .fb-form-field.formbuilder-field-type-heading h2, {{WRAPPER}} .fb-form-field.formbuilder-field-type-heading h3, {{WRAPPER}} .fb-form-field.formbuilder-field-type-heading h4, {{WRAPPER}} .fb-form-field.formbuilder-field-type-heading h5, {{WRAPPER}} .fb-form-field.formbuilder-field-type-heading h6',
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'heading_color', [
                'label' => esc_html__( 'Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-heading-typo-font-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'paragraph_style', [
                'label' => esc_html__( 'Paragraph / Shortcode', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(), [
                'name' => 'paragraph_typography',
                'label' => esc_html__( 'Typography', 'admin-site-enhancements' ),
                'selector' => '{{WRAPPER}} .fb-form-field.formbuilder-field-type-paragraph p',
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'paragraph_color', [
                'label' => esc_html__( 'Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-paragraph-typo-font-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'divider_style', [
                'label' => esc_html__( 'Divider', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'divider_color', [
                'label' => esc_html__( 'Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-divider-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'star_style', [
                'label' => esc_html__( 'Star', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'star_size', [
                'label' => __( 'Size', 'admin-site-enhancements' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 80,
                        'step' => 1,
                    ]
                ],
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-star-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'star_color', [
                'label' => esc_html__( 'Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-star-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'star_color_active', [
                'label' => esc_html__( 'Color (Active )', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-star-color-active: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'range_slider_style', [
                'label' => esc_html__( 'Range Slider', 'admin-site-enhancements' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'range_slider_height', [
                'label' => __( 'Height', 'admin-site-enhancements' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 80,
                        'step' => 1,
                    ]
                ],
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-range-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'range_slider_handle_size', [
                'label' => __( 'Handle Size', 'admin-site-enhancements' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 80,
                        'step' => 1,
                    ]
                ],
                'condition' => [
                    'enable_custom_style' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-range-handle-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'range_slider_bar_color', [
                'label' => esc_html__( 'Bar Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-range-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'rangle_slider_bar_color_active', [
                'label' => esc_html__( 'Color (Active )', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-range-color-active: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'range_handle_color', [
                'label' => esc_html__( 'Handle Color', 'admin-site-enhancements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--fb-range-handle-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();
    }

    public function render() {
        $settings = $this->get_settings_for_display();
        $enable_custom_style = isset( $settings['enable_custom_style'] ) ? $settings['enable_custom_style'] : 'no';

        if ( $enable_custom_style == 'yes' ) {
            add_filter( 'formbuilder_form_classes', array( $this, 'modify_class' ) );
            add_filter( 'formbuilder_enable_style', '__return_false' );
        }

        if ( isset( $settings['hf_form_id'] ) && ! empty( $settings['hf_form_id'] ) && ( Form_Builder_Listing::get_status( $settings['hf_form_id'] ) == 'published' ) ) {
            echo do_shortcode( '[formbuilder id="' . $settings['hf_form_id'] . '"]' );
        } elseif ( $this->elementor()->editor->is_edit_mode() ) {
            ?>
            <p><?php echo esc_html__( 'Please select a Form', 'admin-site-enhancements' ); ?></p>
            <?php
        }

        if ( $enable_custom_style == 'yes' ) {
            remove_filter( 'formbuilder_form_classes', array( $this, 'modify_class' ) );
            remove_filter( 'formbuilder_enable_style', '__return_false' );
        }
    }

    public function modify_class( $classes ) {
        $remove_classes = array( 'fb-form-default-style', 'fb-form-no-style' );
        $classes = array_diff( $classes, $remove_classes );
        $classes[] = 'fb-elementor-form';
        $classes[] = 'fb-form-custom-style';

        return $classes;
    }

    protected function elementor() {
        return Plugin::$instance;
    }

}
