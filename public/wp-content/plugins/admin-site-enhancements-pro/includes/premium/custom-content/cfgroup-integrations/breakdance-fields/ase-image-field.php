<?php

use Ase\Integrations\Breakdance\Breakdance_Ase_Field;
use Breakdance\DynamicData\ImageField;
use Breakdance\DynamicData\ImageData;

class Ase_Image extends ImageField {

    public array $field;

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
    public function slug() {
        return 'ase_field_image_return_' . $this->field['name'];
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
    public function handler( $attributes ): ImageData {
        $post_id = get_the_ID();

        $breakdance_ase_field = new Breakdance_Ase_Field( $post_id, $this->field, 'image' );
        $attachment_id = (int) $breakdance_ase_field->get_field_value();

    	return ImageData::fromAttachmentId( $attachment_id );
    }
}