<?php
/**
 * Etch CLI UiState Command
 *
 * @package Etch
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Etch\Cli\SubCommands;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_CLI;
use WP_CLI_Command;
use Etch\Cli\Traits\EtchCliHelper;

/**
 * Etch UiState command
 */
class UiState extends WP_CLI_Command {
	use EtchCliHelper;

	/**
	 * Deletes all Etch ui state.
	 *
	 * @param array<string, string> $assoc_args Associative arguments.
	 * @return void
	 */
	public function delete( array $assoc_args ): void {
		$ui_state_exists = get_option( 'etch_ui_state', null );

		if ( null === $ui_state_exists ) {
			WP_CLI::warning( 'There are no ui state to delete.' );
			return;
		}

		$force = $this->get_flag_args( $assoc_args, 'force', null );

		if ( null === $force ) {
			WP_CLI::confirm( 'Are you sure you want to delete all ui state?', array( 'y' ) );
		}

		delete_option( 'etch_ui_state' );
		WP_CLI::success( 'All ui state have been deleted.' );
	}
}
