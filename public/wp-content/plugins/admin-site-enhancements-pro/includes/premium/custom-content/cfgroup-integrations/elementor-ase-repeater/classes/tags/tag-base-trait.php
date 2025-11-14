<?php

namespace ElementorAseRepeater\DynamicTags;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once ELEMENTOR_ASE_REPEATER_PATH . 'classes/data/repeater-data-trait.php';

trait TagBaseTrait {
    use \ElementorAseRepeater\Data\RepeaterDataTrait;
    protected $configurator;
    protected $controls;

    public function __construct( $data = [] ) {
        parent::__construct( $data );
        $this->configurator = \ElementorAseRepeater\Configurator::instance();
        $this->controls = \ElementorAseRepeater\Controls\DynamicTagControls::instance();
    }
}

abstract class AseRepeaterTagBase extends \Elementor\Core\DynamicTags\Data_Tag {
    use TagBaseTrait;
}