<?php
/**
 * Class Autoload.
 *
 * @package XWP\Io\WP_Autoload
 */

namespace XWP\Io\WP_Autoload;

/**
 * Class Autoload.
 *
 * @package XWP\Io\WP_Autoload
 */
class Autoload {

	/**
	 * Namespace to directory mapping.
	 *
	 * @var array
	 */
	protected $namespace_dir_map = array();

	/**
	 * Setup the autoloader.
	 */
	public function __construct() {
		spl_autoload_register( array( $this, 'autoload' ) );
	}

	/**
	 * Add mapping for a directory to a namespace.
	 *
	 * @param string $namespace Namespace.
	 * @param string $dir       Absolute path to the directory.
	 */
	public function add( $namespace, $dir ) {
		$this->namespace_dir_map[ trim( $namespace, '/\\' ) ] = rtrim( $dir, '/\\' );

		krsort( $this->namespace_dir_map ); // Ensure the sub-namespaces are matched first.
	}

	/**
	 * Autoload the registered classes.
	 *
	 * @param string $class Fully qualified class name.
	 *
	 * @return void
	 */
	public function autoload( $class ) {
		$class_path = $this->resolve( $class );

		if ( ! empty( $class_path ) && is_readable( $class_path ) ) {
			require_once $class_path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		}
	}

	/**
	 * Resolve the requested classname to the file path
	 * of a registered namespace.
	 *
	 * @param string $class Fully qualified class name.
	 *
	 * @return string|null
	 */
	public function resolve( $class ) {
		foreach ( $this->namespace_dir_map as $namespace => $path ) {
			if ( 0 === strpos( $class, $namespace . '\\' ) ) { // Append the trailing slash to not match SomeClassName where SomeClass is defined.
				// Remove the known prefix from the class name.
				$class = substr( $class, strlen( $namespace ) + 1 );

				return sprintf(
					'%s/%s',
					$path,
					$this->class_to_file_path( $class )
				);
			}
		}
	}

	/**
	 * Map fully qualified class names to file path
	 * according to WP coding standard rules.
	 *
	 * @param string $class Fully qualified class name.
	 *
	 * @return string
	 */
	protected function class_to_file_path( $class ) {
		$class_parts = explode( '\\', $class );
		$class_name  = array_pop( $class_parts );

		// Map nested namespaces to sub-directories.
		if ( ! empty( $class_parts ) ) {
			$class_parts = array_map(
				array( $this, 'class_to_file_name' ),
				$class_parts
			);

			return sprintf( '%s/class-%s.php', implode( '/', $class_parts ), $this->class_to_file_name( $class_name ) );
		}

		return sprintf( 'class-%s.php', $this->class_to_file_name( $class_name ) );
	}

	/**
	 * Sanitize class name to filename according to WP coding standards.
	 *
	 * @param string $class Class name.
	 *
	 * @return string
	 */
	protected function class_to_file_name( $class ) {
		return strtolower( str_replace( '_', '-', $class ) );
	}
}
