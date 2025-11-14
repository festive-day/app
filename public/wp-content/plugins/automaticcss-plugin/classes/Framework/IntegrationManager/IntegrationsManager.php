<?php
/**
 * IntegrationsManager
 *
 * Manages the registration and retrieval of active integrations.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework\IntegrationManager;

use Automatic_CSS\Features\Contextual_Menus\Contextual_Menus;
use Automatic_CSS\Features\Color_Scheme_Switcher\Color_Scheme_Switcher;
use Automatic_CSS\Features\Builder_Input_Validation\Builder_Input_Validation;
use Automatic_CSS\Framework\IntegrationManager\IntegrationInterface;
use Automatic_CSS\Features\Keyboard_Nav_Hover_Preview\Keyboard_Nav_Hover_Preview;
use Automatic_CSS\Features\Hide_Deactivated_Classes\Hide_Deactivated_Classes;
use Automatic_CSS\Features\Bricks_Color_Swatches_Checkerboard\Bricks_Color_Swatches_Checkerboard;
use Automatic_CSS\Features\Move_Gutenberg_CSS_Input\Move_Gutenberg_CSS_Input;
use Automatic_CSS\Features\Bem_Class_Generator\Bem_Class_Generator;
use Automatic_CSS\Features\Fix_Bricks_Template_Ids\Fix_Bricks_Template_Ids;
use Automatic_CSS\Features\Buttons_Styles\Buttons_Styles;
use Automatic_CSS\Framework\Integrations\Breakdance;
use Automatic_CSS\Framework\Integrations\Bricks;
use Automatic_CSS\Framework\Integrations\Etch;
use Automatic_CSS\Framework\Integrations\Frames;
use Automatic_CSS\Framework\Integrations\Oxygen;
use Automatic_CSS\Helpers\Permissions;

/**
 * Class IntegrationsManager
 */
class IntegrationsManager {

	/**
	 * Available integrations.
	 *
	 * @var array<string, IntegrationInterface>
	 */
	private $available_integrations = array();

	/**
	 * Builders.
	 *
	 * @var array<string, IntegrationInterface>
	 */
	private $available_builders = array();

	/**
	 * Permissions.
	 *
	 * @var Permissions
	 */
	private $permissions;

	/**
	 * Constructor.
	 *
	 * @param Permissions|null $permissions Permissions.
	 * @param array|null       $builders Builders.
	 * @param array|null       $integrations Integrations.
	 */
	public function __construct( Permissions $permissions, $builders = null, $integrations = null ) {
		$this->permissions = $permissions;
		$this->available_builders = $builders ?? array(
			Etch::get_name() => new Etch(),
			Oxygen::get_name() => new Oxygen(),
			Breakdance::get_name() => new Breakdance(),
			Bricks::get_name() => new Bricks(),
		);
		$this->available_integrations = $integrations ?? array(
			// Frames::get_name() => new Frames(), // We no longer need to enqueue the Frames CSS file.
			Contextual_Menus::get_name() => new Contextual_Menus( $permissions ),
			Color_Scheme_Switcher::get_name() => new Color_Scheme_Switcher( $permissions ),
			Builder_Input_Validation::get_name() => new Builder_Input_Validation( $permissions ),
			Keyboard_Nav_Hover_Preview::get_name() => new Keyboard_Nav_Hover_Preview( $permissions ),
			Hide_Deactivated_Classes::get_name() => new Hide_Deactivated_Classes( $permissions ),
			Bricks_Color_Swatches_Checkerboard::get_name() => new Bricks_Color_Swatches_Checkerboard(),
			Move_Gutenberg_CSS_Input::get_name() => new Move_Gutenberg_CSS_Input( $permissions ),
			Bem_Class_Generator::get_name() => new Bem_Class_Generator( $permissions ),
			Buttons_Styles::get_name() => new Buttons_Styles(),
			Fix_Bricks_Template_Ids::get_name() => new Fix_Bricks_Template_Ids(),
		);
	}

	/**
	 * Initialize the integrations manager.
	 *
	 * @return void
	 */
	public function init() {
	}

	/**
	 * Get active integrations.
	 *
	 * @return array<string, IntegrationInterface>
	 */
	public function get_active_integrations() {
		$this->add_active_builder_to_integrations();
		return array_filter(
			$this->available_integrations,
			function ( $integration ) {
				return $integration->is_active();
			}
		);
	}

	/**
	 * Add the active builder to the list of integrations.
	 *
	 * @return void
	 */
	private function add_active_builder_to_integrations() {
		$this->sort_available_builders();
		foreach ( $this->available_builders as $builder_name => $builder ) {
			if ( ! $builder->is_active() ) {
				continue;
			}
			$this->available_integrations = array_merge( array( $builder_name => $builder ), $this->available_integrations );
			return;
		}
	}

	/**
	 * Sort the available builders.
	 *
	 * @return void
	 */
	private function sort_available_builders() {
		// Order matters here: Etch, Oxygen, Breakdance, Bricks.
		$priority_order = array(
			Etch::get_name() => 0,
			Oxygen::get_name() => 1,
			Breakdance::get_name() => 2,
			Bricks::get_name() => 3,
		);

		uasort(
			$this->available_builders,
			function ( $a, $b ) use ( $priority_order ) {
				$a_class = get_class( $a );
				$b_class = get_class( $b );
				$a_name = method_exists( $a_class, 'get_name' ) ? $a_class::get_name() : $a_class;
				$b_name = method_exists( $b_class, 'get_name' ) ? $b_class::get_name() : $b_class;

				// If either builder is not in our priority list, put it at the end.
				$a_priority = isset( $priority_order[ $a_name ] ) ? $priority_order[ $a_name ] : PHP_INT_MAX;
				$b_priority = isset( $priority_order[ $b_name ] ) ? $priority_order[ $b_name ] : PHP_INT_MAX;

				return $a_priority - $b_priority;
			}
		);
	}

	/**
	 * Get permissions.
	 *
	 * @return Permissions
	 */
	public function get_permissions() {
		return $this->permissions;
	}
}
