<?php

use Ase\Integrations\Breakdance\Breakdance_Ase_Field;
use Breakdance\DynamicData\StringField;
use Breakdance\DynamicData\StringData;

class Ase_String extends StringField {

    /**
     * @var ASE Field
     */
    public array $field;

    /**
     * @param AseString $field
     */
    public function __construct( $field ) {
        $this->field = $field;
    }

    /**
     * @inheritDoc
     */
    public function label() {
        return $this->field['label'];
    }

    /**
     * @inheritDoc
     */
    public function category() {
        return 'ASE';
    }

    /**
     * @inheritDoc
     */
    public function subcategory() {
    	if ( $this->field['is_repeater_sub_field'] ) {
	        return $this->field['parent_repeater']; // Repeater name		
    	} else {
	        return $this->field['field_group']; // Field group name		    		
    	}
    }

    /**
     * @inheritDoc
     */
    public function returnTypes() {
        // if ($this->field['type'] === 'hyperlink') {
        //     return ['string', 'url'];
        // }
        return ['string'];
    }

    /**
     * @inheritDoc
     */
    public function slug() {
        return 'ase_field_string_return_' . $this->field['name'];
    }

    /**
     * @param string $postType
     * @return bool
     */
    public function availableForPostType( $post_type ) {
    	if ( isset( $this->field['for_post_types'] ) && in_array( $post_type, $this->field['for_post_types'] ) ) {
	        return true;		
    	}
        
        if ( isset( $this->field['is_for_an_option_page'] ) && $this->field['is_for_an_option_page'] ) {
            return true;
        }
    	
    	return false;
    }

    /**
     * @inheritDoc
     */
    public function handler( $attributes ): StringData {
        $post_id = get_the_ID();
        
        $breakdance_ase_field = new Breakdance_Ase_Field( $post_id, $this->field, 'string' );
        $value = $breakdance_ase_field->get_field_value();

        // Make $value is a string even when it is null. This prevents error in Breakdance editor.
        if ( null === $value ) {
            $value = (string) '';
        }

        return StringData::fromString( $value );
    }

}