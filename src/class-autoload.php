<?php
/**
 * Class Autoload.
 *
 * @package XWP\IO\WP_Autoload
 */

namespace XWP\IO\WP_Autoload;

/**
 * Class Autoload.
 *
 * @package XWP\IO\WP_Autoload
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
		$paths = $this->resolve( $class );

		foreach ( $paths as $path ) {
			if ( is_readable( $path ) ) {
				require_once $path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

				return; // Return as soon as we've resolved the first one.
			}
		}
	}

	/**
	 * Resolve the requested classname to the possible file path
	 * of a registered namespace by type (class, interface, trait).
	 *
	 * @param string $class Fully qualified class name.
	 *
	 * @return array List of mapped file paths.
	 */
	public function resolve( $class ) {
		$prefixes = array( 'class', 'interface', 'trait' );

		foreach ( $this->namespace_dir_map as $namespace => $path ) {
			if ( 0 === strpos( $class, $namespace . '\\' ) ) { // Append the trailing slash to not match SomeClassName where SomeClass is defined.
				$class = substr( $class, strlen( $namespace ) + 1 );

				$file_path_template = $this->file_path_from_parts(
					array(
						$path,
						$this->class_to_file_path_template( $class, '{prefix}' ),
					)
				);

				return array_map(
					function ( $prefix ) use ( $file_path_template ) {
						return str_replace( '{prefix}', $prefix, $file_path_template );
					},
					$prefixes
				);
			}
		}

		return array();
	}

	/**
	 * Map fully qualified class names to file path
	 * according to WP coding standard rules.
	 *
	 * @param string $class       Fully qualified class name.
	 * @param string $placeholder Placeholder string to use for the file type designation.
	 *
	 * @return string
	 */
	protected function class_to_file_path_template( $class, $placeholder = '{prefix}' ) {
		$class_parts = explode( '\\', $class );
		$class_name  = array_pop( $class_parts );

		// Map nested namespaces to sub-directories.
		if ( ! empty( $class_parts ) ) {
			$class_parts = array_map(
				array( $this, 'class_to_file_name' ),
				$class_parts
			);
		}

		// Add filename at the end.
		$class_parts[] = sprintf(
			'%s-%s.php',
			$placeholder,
			$this->class_to_file_name( $class_name )
		);

		return $this->file_path_from_parts( $class_parts );
	}

	/**
	 * Generate file path based on components and the system
	 * directory separator.
	 *
	 * @param array $parts File path parts.
	 *
	 * @return string
	 */
	protected function file_path_from_parts( $parts ) {
		return implode( DIRECTORY_SEPARATOR, $parts );
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
