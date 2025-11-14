<?php

namespace ElementorAseRepeater\Data;

use ElementorAseRepeater\Configurator;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * This trait is used to get the repeater field value and each sub-field's value for a given post ID
 */
trait RepeaterDataTrait {
    /**
     * Get the value of the repeater sub-field
     */
    public function get_repeater_value( $subfield_name, $output_format = 'raw' ) {
        $post_id = get_the_ID();

        // Get document once and reuse it
        $document = \Elementor\Plugin::$instance->documents->get_current();
        
        // Default index (matching the original get_current_item_index behavior)
        $current_index = ( $document instanceof \ElementorPro\Modules\LoopBuilder\Documents\Loop ) 
            ? ( $document->get_settings('loop')['index'] ?? 0 ) 
            : 0;

        // Handle virtual post IDs. Let's find the actual post ID.
        if ( $post_id < 0 ) {
            // $post_id can for example, be -1023399909990
            // where 10233 is the actual post ID, 9990999 is the VIRTUAL_POST_ID_SEPARATOR, and 0 is the current index
            $abs_id = abs( $post_id ); // e.g. -1023399909990 => 102339999990 (integer)
            $id_str = (string) $abs_id; // e.g. '1023399099990' (string)
            
            // Let's replace separator with double underscore and create an array of post ID and current index
            // e.g. '1023399909990' => '10233__0'
            $id_str = str_replace( Configurator::VIRTUAL_POST_ID_SEPARATOR, '__', $id_str );
            $id_str_array = explode( '__', $id_str );
            
            $post_id = (int) $id_str_array[0];
            $current_index = (int) $id_str_array[1];
        }

        // $repeater_field = get_parent_repeater_cf( $subfield_name );
        // vi( $repeater_field );

        // Determine repeater field to use
        if ( $document instanceof \ElementorPro\Modules\LoopBuilder\Documents\Loop ) {
            $document_id = $document->get_main_id();
            $saved_repeater_field = get_post_meta( $document_id, 'asenha_loop_repeater_field', true );
            
            $repeater_field = ! empty( $saved_repeater_field ) 
                ? $saved_repeater_field // String of repeater field name
                : get_parent_repeater_cf( $subfield_name ); // Array of repeater field info
        } else {
            $repeater_field = get_parent_repeater_cf( $subfield_name ); // Array of repeater field info
        }
            
        if ( ! $repeater_field ) {
            return null;
        }
        
        if ( is_string( $repeater_field ) && ! empty( $repeater_field ) ) {
            $repeater_field_name = $repeater_field;
        } else if ( is_array( $repeater_field ) && isset( $repeater_field['name'] ) ) {
            $repeater_field_name = $repeater_field['name'];
        }
        
        switch ( $output_format ) {
            case 'raw':
                $repeater_data = get_cf( $repeater_field_name, 'raw', $post_id );
                // if ( 'some_repeater_field_name' == $repeater_field['name'] ) {
                //     vi( $repeater_data, '', $repeater_field['name'] . ' - raw - ' . $post_id );
                // }
                break;
                
            case 'display':
                $repeater_data = get_cf( $repeater_field_name, 'display', $post_id );
                // if ( 'some_repeater_field_name' == $repeater_field['name'] && '10233' == $post_id ) {
                //     vi( $repeater_data, '', $repeater_field['name'] . ' - display - ' . $post_id );
                // }            
                break;
        }

        if ( ! $repeater_data || ! is_array( $repeater_data ) ) {
            return null;
        }

        if ( ! isset( $repeater_data[$current_index][$subfield_name] ) ) {
            return null;
        }
        
        $subfield_value = $repeater_data[$current_index][$subfield_name];

        return $subfield_value;
    }
}