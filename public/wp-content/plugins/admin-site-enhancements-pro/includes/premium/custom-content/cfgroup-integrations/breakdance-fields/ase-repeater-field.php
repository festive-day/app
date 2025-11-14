<?php

use Breakdance\DynamicData\DynamicDataController;
use Breakdance\DynamicData\LoopController;
use Breakdance\DynamicData\RepeaterField;
use Breakdance\DynamicData\RepeaterData;

class Ase_Repeater extends RepeaterField {

    public array $field;

	/**
	 * @var LoopController
	 */
	private LoopController $loop;

	/**
	 * @var int
	 */
	private $index;

    public function __construct( $field ) {
        $this->field = $field;
		$this->loop = \Breakdance\DynamicData\LoopController::getInstance( $field['id'] . '_' . $field['name'] );
		$this->index = 0;
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
        return 'ase_field_repeater_return_' . $this->field['name'];
    }

    /**
     * Seems like this is never called, so we return empty array.
     * @inheritDoc
     */
    public function handler( $attributes ): RepeaterData
    {
		return RepeaterData::fromArray( array() );
    }

    /**
     * @param $postId
     * @return bool
     */
    public function hasSubFields( $post_id = null ) {
    	$field_loop = $this->loop->get();

		if ( $post_id === null ) {
			$post_id = get_the_ID();
		}

		if ( $post_id === null ) {
			return false;
		}
		
		$nested_values = get_cf( $this->field['name'], 'raw', $post_id );

		if ( empty( $nested_values ) ) {
			return false;
		}
		
		$max_loops = count( $nested_values ) - 1;

		if ( isset( $field_loop['index'] ) && $max_loops <= $field_loop['index'] ) {
			$this->index = 0;
			$this->loop->reset();

			return false;
		}

		$this->loop->set([
			'field' => $this->field,
			'index' => $this->index,
		]);

		$this->index++;

		return true;
    }

    /**
     * @inheritDoc
     */
    public function setSubFieldIndex( $index, $post_id = null ) {
		$blockLoop = $this->loop->get();

		return $blockLoop['index'];
    }

    /**
     * @inheritDoc
     */
    public function parentField() {
    	if ( empty( $this->field['parent_name'] ) ) {
    		return null;
    	}
    	
        return DynamicDataController::getInstance()->getField( 'ase_field_repeater_return_' . $this->field['parent_name'] );
    }

}