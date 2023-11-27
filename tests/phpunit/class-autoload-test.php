<?php
/**
 * Class WP_Autoload_Test.
 *
 * @package XWP\IO\Doc_Hooks
 */

namespace XWP\IO\WP_Autoload_Tests;

use PHPUnit\Framework\TestCase;
use XWP\IO\WP_Autoload\Autoload;

/**
 * Test the docblock parser.
 */
class Autoload_Test extends TestCase {

	/**
	 * Resolves classes, traits, interfaces.
	 */
	public function test_can_resolve_traits_interfaces_classes() {
		$autoload = new Autoload();
		$autoload->add( 'MaybeClassTraitInterface', '/path/to/some-class' );

		$this->assertEquals(
			array(
				'/path/to/some-class/class-root.php',
				'/path/to/some-class/interface-root.php',
				'/path/to/some-class/trait-root.php',
			),
			$autoload->resolve( 'MaybeClassTraitInterface\Root' )
		);
	}

	/**
	 * Can resolve filenames by class names.
	 */
	public function test_autoload_class_resolve() {
		$autoload = new Autoload();
		$autoload->add( 'SomeClass', '/path/to/some-class' );

		$this->assertEmpty( $autoload->resolve( 'SomeClass' ), 'Skip root namespace' );
		$this->assertEmpty( $autoload->resolve( 'UnknownClass' ), 'Skip unknown classes' );
		$this->assertEmpty( $autoload->resolve( 'SomeClassNext' ), 'Skip classes that start with the same thing' );

		$this->assertContains( '/path/to/some-class/class-root.php', $autoload->resolve( 'SomeClass\Root' ) );
		$this->assertContains( '/path/to/some-class/beta-test/class-child.php', $autoload->resolve( 'SomeClass\Beta_Test\Child' ) );
	}

	/**
	 * Can resolve sub-classes of the same root namespace but
	 * with files in different directories.
	 */
	public function test_can_resolve_sub_classes() {
		$autoload = new Autoload();
		$autoload->add( 'RootClass', '/path/to/root-class' );
		$autoload->add( 'RootClass\Tests', '/path/to/root-class-tests' );

		$this->assertContains( '/path/to/root-class/child/class-root-grand-child.php', $autoload->resolve( 'RootClass\Child\Root_Grand_Child' ) );
		$this->assertContains( '/path/to/root-class-tests/child/class-tests-grand-child.php', $autoload->resolve( 'RootClass\Tests\Child\Tests_Grand_Child' ) );
	}
}
