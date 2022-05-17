<?php
/**
 * Class WP_Autoload_Test.
 *
 * @package XWP\Io\Doc_Hooks
 */

namespace XWP\Io\WP_Autoload_Tests;

use PHPUnit\Framework\TestCase;
use XWP\Io\WP_Autoload\Autoload;

/**
 * Test the docblock parser.
 */
class Autoload_Test extends TestCase {

	/**
	 * Can resolve filenames by class names.
	 */
	public function test_autoload_class_resolve() {
		$autoload = new Autoload();
		$autoload->add( 'SomeClass', '/path/to/some-class' );

		$this->assertNull( $autoload->resolve( 'SomeClass' ), 'Skip root namespace' );
		$this->assertNull( $autoload->resolve( 'UnknownClass' ), 'Skip unknown classes' );
		$this->assertNull( $autoload->resolve( 'SomeClassNext' ), 'Skip classes that start with the same thing' );

		$this->assertEquals( '/path/to/some-class/class-root.php', $autoload->resolve( 'SomeClass\Root' ) );
		$this->assertEquals( '/path/to/some-class/beta-test/class-child.php', $autoload->resolve( 'SomeClass\Beta_Test\Child' ) );
	}

	/**
	 * Can resolve sub-classes of the same root namespace but
	 * with files in different directories.
	 */
	public function test_can_resolve_sub_classes() {
		$autoload = new Autoload();
		$autoload->add( 'RootClass', '/path/to/root-class' );
		$autoload->add( 'RootClass\Tests', '/path/to/root-class-tests' );

		$this->assertEquals( '/path/to/root-class/child/class-root-grand-child.php', $autoload->resolve( 'RootClass\Child\Root_Grand_Child' ) );
		$this->assertEquals( '/path/to/root-class-tests/child/class-tests-grand-child.php', $autoload->resolve( 'RootClass\Tests\Child\Tests_Grand_Child' ) );
	}

}
