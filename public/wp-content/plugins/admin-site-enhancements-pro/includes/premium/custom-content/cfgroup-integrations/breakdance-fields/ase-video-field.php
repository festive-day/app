<?php

use Ase\Integrations\Breakdance\Breakdance_Ase_Field;
use Breakdance\DynamicData\OembedField;
use Breakdance\DynamicData\OembedData;

class Ase_Video extends OembedField {

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
        return 'ase_field_video_return_' . $this->field['name'];
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

    public function handler( $attributes ): OembedData {
        $post_id = get_the_ID();

        $breakdance_ase_field = new Breakdance_Ase_Field( $post_id, $this->field, 'video' );
		$url = (string) $breakdance_ase_field->get_field_value();

		if ( ! empty( $url ) ) {
			return OembedData::fromOembedUrl( $url );
		}

		return OembedData::emptyOembed();
    }

}