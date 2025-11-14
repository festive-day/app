<?php
/**
 * CSS_File class.
 *
 * @package Automatic_CSS\Helpers
 */

namespace Automatic_CSS\Helpers;

/**
 * CSS_File class.
 */
class CSS_File {

	/**
	 * The handle of the CSS file.
	 *
	 * @var string
	 */
	private $handle;

	/**
	 * The URL of the CSS file.
	 *
	 * @var string
	 */
	private $file_url;

	/**
	 * The path of the CSS file.
	 *
	 * @var string
	 */
	private $file_path;

	/**
	 * The dependencies of the CSS file.
	 *
	 * @var array
	 */
	private $deps;

	/**
	 * The media of the CSS file.
	 *
	 * @var string
	 */
	private $media;

	/**
	 * The style queue.
	 *
	 * @var \WP_Styles
	 */
	private $style_queue;

	/**
	 * Is this stylesheet registered?
	 *
	 * @var bool
	 */
	private $is_registered;

	/**
	 * Is this stylesheet enqueued?
	 *
	 * @var bool
	 */
	private $is_enqueued;

	/**
	 * Constructor.
	 *
	 * @param string     $handle The handle of the CSS file.
	 * @param string     $url The URL of the CSS file.
	 * @param string     $path The path of the CSS file.
	 * @param array      $deps The dependencies of the CSS file.
	 * @param string     $media The media of the CSS file.
	 * @param \WP_Styles $style_queue The style queue.
	 */
	public function __construct( $handle, $url, $path, $deps = array(), $media = 'all', $style_queue = null ) {
		$this->handle = $handle;
		$this->file_url = $url;
		$this->file_path = $path;
		$this->deps = $deps;
		$this->media = $media;
		$this->style_queue = $style_queue ?? $this->set_default_queue();
	}

	/**
	 * Enqueue this CSS file
	 *
	 * @return void
	 * @see https://developer.wordpress.org/reference/classes/wp_dependencies/enqueue/
	 */
	public function enqueue_stylesheet() {
		if ( $this->is_file_empty() ) {
			return;
		}
		if ( ! $this->is_registered ) {
			$this->register_stylesheet();
		}
		Logger::log( sprintf( '%s: enqueuing stylesheet %s', __METHOD__, $this->handle ) );
		Logger::now( 'CSS FILE: enqueuing ' . $this->handle ); // TODO: remove.
		$this->style_queue->enqueue( $this->handle );
		$this->is_enqueued = $this->style_queue->query( $this->handle, 'enqueued' );
	}

	/**
	 * Register this CSS file as a stylesheet in $style_queue
	 *
	 * @return bool
	 * @see https://developer.wordpress.org/reference/classes/wp_dependencies/add/
	 */
	public function register_stylesheet() {
		Logger::log( sprintf( '%s: registering stylesheet %s', __METHOD__, $this->handle ) );
		if ( ! $this->file_exists() ) {
			Logger::log( sprintf( '%s: CSS file %s does not exist and cannot be registered', __METHOD__, $this->file_path ), Logger::LOG_LEVEL_ERROR );
			return false;
		}
		$ret = $this->style_queue->add(
			$this->handle,
			$this->file_url,
			$this->deps,
			filemtime( $this->file_path ),
			$this->media
		);
		$this->is_registered = $this->style_queue->query( $this->handle, 'registered' );
		return $ret;
	}

	/**
	 * Change the stylesheet's queue, if it was not registered yet.
	 *
	 * @param \WP_Styles $queue The new queue.
	 *
	 * @return WP_Styles|null
	 */
	public function set_queue( \WP_Styles $queue ) {
		Logger::log( sprintf( '%s: setting new queue for stylesheet %s', __METHOD__, $this->handle ) );
		// TODO: deregister the stylesheet if it was registered?
		$this->style_queue = $queue;
		return $this->style_queue;
	}

	/**
	 * Set the default queue if it was not set yet.
	 *
	 * @return WP_Styles|null
	 */
	private function set_default_queue() {
		if ( ! Flag::is_on( 'ENABLE_NEW_ASSET_MANAGEMENT' ) ) {
			Logger::log( sprintf( '%s: setting default queue for stylesheet %s', __METHOD__, $this->handle ) );
			return $this->set_queue( wp_styles() );
		}
		$container = Container::get_instance();
		$style_queue = $container->has( 'style_queue' )
			? $container->get( 'style_queue' )
			: Style_Queue_Factory::get_instance()->get_default_queue();

		Logger::log( sprintf( '%s: setting default queue for stylesheet %s', __METHOD__, $this->handle ) );
		return $this->set_queue( $style_queue->get_queue() );
	}

	/**
	 * Check if CSS file exists.
	 *
	 * @return bool
	 */
	public function file_exists() {
		return file_exists( $this->file_path );
	}

	/**
	 * Check if the file is empty
	 *
	 * @return bool
	 */
	public function is_file_empty() {
		if ( ! $this->file_exists() ) {
			return true;
		}

		if ( '' == file_get_contents( $this->file_path ) ) {
			return true;
		}

		return false;
	}
}
