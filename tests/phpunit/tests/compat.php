<?php

/**
 * @group compat
 */
class Tests_Compat extends WP_UnitTestCase {

	/**
	 * Test that is_countable() is always available (either from PHP or WP).
	 *
	 * @ticket 43583
	 */
	function test_is_countable_availability() {
		$this->assertTrue( function_exists( 'is_countable' ) );
	}

	/**
	 * Test is_countable() polyfill.
	 *
	 * @ticket 43583
	 *
	 * @dataProvider countable_variable_test_data
	 *
	 * @param mixed $variable     Variable to check.
	 * @param bool  $is_countable The expected return value of PHP 7.3 is_countable() function.
	 */
	function test_is_countable_functionality( $variable, $is_countable ) {
		$this->assertSame( is_countable( $variable ), $is_countable );
	}

	/**
	 * Data provider for test_is_countable_functionality().
	 *
	 * @ticket 43583
	 *
	 * @return array {
	 *     @type array {
	 *         @type mixed $variable     Variable to check.
	 *         @type bool  $is_countable The expected return value of PHP 7.3 is_countable() function.
	 *     }
	 * }
	 */
	public function countable_variable_test_data() {
		return array(
			array( true, false ),
			array( new stdClass(), false ),
			array( new ArrayIteratorFake(), true ),
			array( new CountableFake(), true ),
			array( 16, false ),
			array( null, false ),
			array( array( 1, 2, 3 ), true ),
			array( (array) 1, true ),
			array( (object) array( 'foo', 'bar', 'baz' ), false ),
		);
	}

	/**
	 * Test is_countable() polyfill for ResourceBundle.
	 *
	 * @ticket 43583
	 *
	 * @requires extension intl
	 */
	function test_is_countable_ResourceBundle() {
		$this->assertTrue( is_countable( new ResourceBundle( 'en', null ) ) );
	}

	/**
	 * Test is_countable() polyfill for SimpleXMLElement.
	 *
	 * @ticket 43583
	 *
	 * @requires extension simplexml
	 */
	function test_is_countable_SimpleXMLElement() {
		$this->assertTrue( is_countable( new SimpleXMLElement( '<xml><tag>1</tag><tag>2</tag></xml>' ) ) );
	}

	/**
	 * Test that is_iterable() is always available (either from PHP or WP).
	 *
	 * @ticket 43619
	 */
	function test_is_iterable_availability() {
		$this->assertTrue( function_exists( 'is_iterable' ) );
	}

	/**
	 * Test is_iterable() polyfill.
	 *
	 * @ticket 43619
	 *
	 * @dataProvider iterable_variable_test_data
	 *
	 * @param mixed $variable    Variable to check.
	 * @param bool  $is_iterable The expected return value of PHP 7.1 is_iterable() function.
	 */
	function test_is_iterable_functionality( $variable, $is_iterable ) {
		$this->assertSame( is_iterable( $variable ), $is_iterable );
	}

	/**
	 * Data provider for test_is_iterable_functionality().
	 *
	 * @ticket 43619
	 *
	 * @return array {
	 *     @type array {
	 *         @type mixed $variable    Variable to check.
	 *         @type bool  $is_iterable The expected return value of PHP 7.1 is_iterable() function.
	 *     }
	 * }
	 */
	public function iterable_variable_test_data() {
		return array(
			array( array(), true ),
			array( array( 1, 2, 3 ), true ),
			array( new ArrayIterator( array( 1, 2, 3 ) ), true ),
			array( 1, false ),
			array( 3.14, false ),
			array( new stdClass(), false ),
		);
	}
}

class ArrayIteratorFake extends ArrayIterator {
}

class CountableFake implements Countable {
	#[ReturnTypeWillChange]
	public function count() {
		return 16;
	}
}
