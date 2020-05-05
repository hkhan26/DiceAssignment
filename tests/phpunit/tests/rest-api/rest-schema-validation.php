<?php
/**
 * Unit tests covering schema validation and sanitization functionality.
 *
 * @package WordPress
 * @subpackage REST API
 */

/**
 * @group restapi
 */
class WP_Test_REST_Schema_Validation extends WP_UnitTestCase {

	public function test_type_number() {
		$schema = array(
			'type'    => 'number',
			'minimum' => 1,
			'maximum' => 2,
		);
		$this->assertTrue( rest_validate_value_from_schema( 1, $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( 2, $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( 0.9, $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( 3, $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( true, $schema ) );
	}

	public function test_type_integer() {
		$schema = array(
			'type'    => 'integer',
			'minimum' => 1,
			'maximum' => 2,
		);
		$this->assertTrue( rest_validate_value_from_schema( 1, $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( 2, $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( 0, $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( 3, $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( 1.1, $schema ) );
	}

	public function test_type_string() {
		$schema = array(
			'type' => 'string',
		);
		$this->assertTrue( rest_validate_value_from_schema( 'Hello :)', $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( '1', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( 1, $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( array(), $schema ) );
	}

	public function test_type_boolean() {
		$schema = array(
			'type' => 'boolean',
		);
		$this->assertTrue( rest_validate_value_from_schema( true, $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( false, $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( 1, $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( 0, $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( 'true', $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( 'false', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( 'no', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( 'yes', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( 1123, $schema ) );
	}

	public function test_format_email() {
		$schema = array(
			'type'   => 'string',
			'format' => 'email',
		);
		$this->assertTrue( rest_validate_value_from_schema( 'email@example.com', $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( 'a@b.co', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( 'email', $schema ) );
	}

	/**
	 * @ticket 49270
	 */
	public function test_format_hex_color() {
		$schema = array(
			'type'   => 'string',
			'format' => 'hex-color',
		);
		$this->assertTrue( rest_validate_value_from_schema( '#000000', $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( '#FFF', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( 'WordPress', $schema ) );
	}

	/**
	 * @ticket 50053
	 */
	public function test_format_uuid() {
		$schema = array(
			'type'   => 'string',
			'format' => 'uuid',
		);
		$this->assertTrue( rest_validate_value_from_schema( '123e4567-e89b-12d3-a456-426655440000', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( '123e4567-e89b-12d3-a456-426655440000X', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( '123e4567-e89b-?2d3-a456-426655440000', $schema ) );
	}

	public function test_format_date_time() {
		$schema = array(
			'type'   => 'string',
			'format' => 'date-time',
		);
		$this->assertTrue( rest_validate_value_from_schema( '2016-06-30T05:43:21', $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( '2016-06-30T05:43:21Z', $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( '2016-06-30T05:43:21+00:00', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( '20161027T163355Z', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( '2016', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( '2016-06-30', $schema ) );
	}

	public function test_format_ip() {
		$schema = array(
			'type'   => 'string',
			'format' => 'ip',
		);

		// IPv4.
		$this->assertTrue( rest_validate_value_from_schema( '127.0.0.1', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( '3333.3333.3333.3333', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( '1', $schema ) );

		// IPv6.
		$this->assertTrue( rest_validate_value_from_schema( '::1', $schema ) ); // Loopback, compressed, non-routable.
		$this->assertTrue( rest_validate_value_from_schema( '::', $schema ) ); // Unspecified, compressed, non-routable.
		$this->assertTrue( rest_validate_value_from_schema( '0:0:0:0:0:0:0:1', $schema ) ); // Loopback, full.
		$this->assertTrue( rest_validate_value_from_schema( '0:0:0:0:0:0:0:0', $schema ) ); // Unspecified, full.
		$this->assertTrue( rest_validate_value_from_schema( '2001:DB8:0:0:8:800:200C:417A', $schema ) ); // Unicast, full.
		$this->assertTrue( rest_validate_value_from_schema( 'FF01:0:0:0:0:0:0:101', $schema ) ); // Multicast, full.
		$this->assertTrue( rest_validate_value_from_schema( '2001:DB8::8:800:200C:417A', $schema ) ); // Unicast, compressed.
		$this->assertTrue( rest_validate_value_from_schema( 'FF01::101', $schema ) ); // Multicast, compressed.
		$this->assertTrue( rest_validate_value_from_schema( 'fe80::217:f2ff:fe07:ed62', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( '', $schema ) ); // Empty string.
		$this->assertWPError( rest_validate_value_from_schema( '2001:DB8:0:0:8:800:200C:417A:221', $schema ) ); // Unicast, full.
		$this->assertWPError( rest_validate_value_from_schema( 'FF01::101::2', $schema ) ); // Multicast, compressed.
	}

	public function test_type_array() {
		$schema = array(
			'type'  => 'array',
			'items' => array(
				'type' => 'number',
			),
		);
		$this->assertTrue( rest_validate_value_from_schema( array( 1 ), $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( array( true ), $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( null, $schema ) );
	}

	public function test_type_array_nested() {
		$schema = array(
			'type'  => 'array',
			'items' => array(
				'type'  => 'array',
				'items' => array(
					'type' => 'number',
				),
			),
		);
		$this->assertTrue( rest_validate_value_from_schema( array( array( 1 ), array( 2 ) ), $schema ) );
	}

	public function test_type_array_as_csv() {
		$schema = array(
			'type'  => 'array',
			'items' => array(
				'type' => 'number',
			),
		);
		$this->assertTrue( rest_validate_value_from_schema( '1', $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( '1,2,3', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( 'lol', $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( '1,,', $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( '', $schema ) );
	}

	public function test_type_array_with_enum() {
		$schema = array(
			'type'  => 'array',
			'items' => array(
				'enum' => array( 'chicken', 'ribs', 'brisket' ),
				'type' => 'string',
			),
		);
		$this->assertTrue( rest_validate_value_from_schema( array( 'ribs', 'brisket' ), $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( array( 'coleslaw' ), $schema ) );
	}

	public function test_type_array_with_enum_as_csv() {
		$schema = array(
			'type'  => 'array',
			'items' => array(
				'enum' => array( 'chicken', 'ribs', 'brisket' ),
				'type' => 'string',
			),
		);
		$this->assertTrue( rest_validate_value_from_schema( 'ribs,chicken', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( 'chicken,coleslaw', $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( 'ribs,chicken,', $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( '', $schema ) );
	}

	public function test_type_array_is_associative() {
		$schema = array(
			'type'  => 'array',
			'items' => array(
				'type' => 'string',
			),
		);
		$this->assertWPError(
			rest_validate_value_from_schema(
				array(
					'first'  => '1',
					'second' => '2',
				),
				$schema
			)
		);
	}

	public function test_type_object() {
		$schema = array(
			'type'       => 'object',
			'properties' => array(
				'a' => array(
					'type' => 'number',
				),
			),
		);
		$this->assertTrue( rest_validate_value_from_schema( array( 'a' => 1 ), $schema ) );
		$this->assertTrue(
			rest_validate_value_from_schema(
				array(
					'a' => 1,
					'b' => 2,
				),
				$schema
			)
		);
		$this->assertWPError( rest_validate_value_from_schema( array( 'a' => 'invalid' ), $schema ) );
	}

	public function test_type_object_additional_properties_false() {
		$schema = array(
			'type'                 => 'object',
			'properties'           => array(
				'a' => array(
					'type' => 'number',
				),
			),
			'additionalProperties' => false,
		);
		$this->assertTrue( rest_validate_value_from_schema( array( 'a' => 1 ), $schema ) );
		$this->assertWPError(
			rest_validate_value_from_schema(
				array(
					'a' => 1,
					'b' => 2,
				),
				$schema
			)
		);
	}

	public function test_type_object_nested() {
		$schema = array(
			'type'       => 'object',
			'properties' => array(
				'a' => array(
					'type'       => 'object',
					'properties' => array(
						'b' => array( 'type' => 'number' ),
						'c' => array( 'type' => 'number' ),
					),
				),
			),
		);
		$this->assertTrue(
			rest_validate_value_from_schema(
				array(
					'a' => array(
						'b' => '1',
						'c' => 3,
					),
				),
				$schema
			)
		);
		$this->assertWPError(
			rest_validate_value_from_schema(
				array(
					'a' => array(
						'b' => 1,
						'c' => 'invalid',
					),
				),
				$schema
			)
		);
		$this->assertWPError( rest_validate_value_from_schema( array( 'a' => 1 ), $schema ) );
	}

	public function test_type_object_stdclass() {
		$schema = array(
			'type'       => 'object',
			'properties' => array(
				'a' => array(
					'type' => 'number',
				),
			),
		);
		$this->assertTrue( rest_validate_value_from_schema( (object) array( 'a' => 1 ), $schema ) );
	}

	/**
	 * @ticket 42961
	 */
	public function test_type_object_allows_empty_string() {
		$this->assertTrue( rest_validate_value_from_schema( '', array( 'type' => 'object' ) ) );
	}

	public function test_type_unknown() {
		$schema = array(
			'type' => 'lalala',
		);
		$this->assertTrue( rest_validate_value_from_schema( 'Best lyrics', $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( 1, $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( array(), $schema ) );
	}

	public function test_type_null() {
		$this->assertTrue( rest_validate_value_from_schema( null, array( 'type' => 'null' ) ) );
		$this->assertWPError( rest_validate_value_from_schema( '', array( 'type' => 'null' ) ) );
		$this->assertWPError( rest_validate_value_from_schema( 'null', array( 'type' => 'null' ) ) );
	}

	public function test_nullable_date() {
		$schema = array(
			'type'   => array( 'string', 'null' ),
			'format' => 'date-time',
		);

		$this->assertTrue( rest_validate_value_from_schema( null, $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( '2019-09-19T18:00:00', $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( 'some random string', $schema ) );
	}

	public function test_object_or_string() {
		$schema = array(
			'type'       => array( 'object', 'string' ),
			'properties' => array(
				'raw' => array(
					'type' => 'string',
				),
			),
		);

		$this->assertTrue( rest_validate_value_from_schema( 'My Value', $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( array( 'raw' => 'My Value' ), $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( array( 'raw' => array( 'a list' ) ), $schema ) );
	}

	/**
	 * @ticket 48820
	 */
	public function test_string_min_length() {
		$schema = array(
			'type'      => 'string',
			'minLength' => 2,
		);

		// longer
		$this->assertTrue( rest_validate_value_from_schema( 'foo', $schema ) );
		// exact
		$this->assertTrue( rest_validate_value_from_schema( 'fo', $schema ) );
		// non-strings does not validate
		$this->assertWPError( rest_validate_value_from_schema( 1, $schema ) );
		// to short
		$this->assertWPError( rest_validate_value_from_schema( 'f', $schema ) );
		// one supplementary Unicode code point is not long enough
		$mb_char = mb_convert_encoding( '&#x1000;', 'UTF-8', 'HTML-ENTITIES' );
		$this->assertWPError( rest_validate_value_from_schema( $mb_char, $schema ) );
		// two supplementary Unicode code point is long enough
		$this->assertTrue( rest_validate_value_from_schema( $mb_char . $mb_char, $schema ) );
	}

	/**
	 * @ticket 48820
	 */
	public function test_string_max_length() {
		$schema = array(
			'type'      => 'string',
			'maxLength' => 2,
		);

		// shorter
		$this->assertTrue( rest_validate_value_from_schema( 'f', $schema ) );
		// exact
		$this->assertTrue( rest_validate_value_from_schema( 'fo', $schema ) );
		// to long
		$this->assertWPError( rest_validate_value_from_schema( 'foo', $schema ) );
		// non string
		$this->assertWPError( rest_validate_value_from_schema( 100, $schema ) );
		// two supplementary Unicode code point is long enough
		$mb_char = mb_convert_encoding( '&#x1000;', 'UTF-8', 'HTML-ENTITIES' );
		$this->assertTrue( rest_validate_value_from_schema( $mb_char, $schema ) );
		// three supplementary Unicode code point is to long
		$this->assertWPError( rest_validate_value_from_schema( $mb_char . $mb_char . $mb_char, $schema ) );
	}

	/**
	 * @ticket 48821
	 */
	public function test_array_min_items() {
		$schema = array(
			'type'     => 'array',
			'minItems' => 1,
			'items'    => array(
				'type' => 'number',
			),
		);

		$this->assertTrue( rest_validate_value_from_schema( array( 1, 2 ), $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( array( 1 ), $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( array(), $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( '', $schema ) );
	}

	/**
	 * @ticket 48821
	 */
	public function test_array_max_items() {
		$schema = array(
			'type'     => 'array',
			'maxItems' => 2,
			'items'    => array(
				'type' => 'number',
			),
		);

		$this->assertTrue( rest_validate_value_from_schema( array( 1 ), $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( array( 1, 2 ), $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( array( 1, 2, 3 ), $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( 'foobar', $schema ) );
	}

	/**
	 * @ticket 48821
	 */
	public function test_array_unique_numbers() {
		$schema = array(
			'type'        => 'array',
			'uniqueItems' => true,
			'items'       => array(
				'type' => 'number',
			),
		);

		$this->assertTrue( rest_validate_value_from_schema( array( 1, 2 ), $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( array( 1, 1 ), $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( array( 1.0, 1.00, 1 ), $schema ) );
		$this->assertTrue( rest_validate_value_from_schema( array(), $schema ) );
	}

	/**
	 * @ticket 48821
	 */
	public function test_array_unique_strings() {
		$schema = array(
			'type'        => 'array',
			'uniqueItems' => true,
			'items'       => array(
				'type' => 'string',
			),
		);

		$this->assertTrue( rest_validate_value_from_schema( array( 'a', 'b' ), $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( array( 'a', 'a' ), $schema ) );
	}

	/**
	 * @ticket 48821
	 */
	public function test_array_unique_objects() {
		$schema = array(
			'type'        => 'array',
			'uniqueItems' => true,
			'items'       => array(
				'type' => 'object',
			),
		);

		$this->assertTrue( rest_validate_value_from_schema( array( array( 'foo' => 'bar' ), array( 'foo' => 'baz' ) ), $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( array( array( 'foo' => 'bar' ), array( 'foo' => 'bar' ) ), $schema ) );

		$this->assertTrue(
			rest_validate_value_from_schema(
				array(
					array( 'foo' => array( 'bar' => array( 'baz' => true ) ) ),
					array( 'foo' => array( 'bar' => array( 'baz' => false ) ) ),
				),
				$schema
			)
		);
		$this->assertWPError(
			rest_validate_value_from_schema(
				array(
					array( 'foo' => array( 'bar' => array( 'baz' => true ) ) ),
					array( 'foo' => array( 'bar' => array( 'baz' => true ) ) ),
				),
				$schema
			)
		);
	}

	/**
	 * @ticket 48821
	 */
	public function test_array_unique_objects_with_additionalproperties() {
		$schema = array(
			'type'        => 'array',
			'uniqueItems' => true,
			'items'       => array(
				'type'                 => 'object',
				'properties'           => array(
					'foo' => array( 'type' => 'string' ),
					'bar' => array( 'type' => 'number' ),
				),
				'additionalProperties' => array( 'type' => 'string' ),
			),
		);

		$this->assertTrue(
			rest_validate_value_from_schema(
				array(
					array(
						'foo'  => 'x',
						'bar'  => 1,
						'xtra' => 'a',
					),
					array(
						'foo'  => 'y',
						'bar'  => 2,
						'xtra' => 'b',
					),
				),
				$schema
			)
		);

		$this->assertWPError(
			rest_validate_value_from_schema(
				array(
					array(
						'foo'  => 'x',
						'bar'  => 1,
						'xtra' => 'a',
					),
					array(
						'foo'  => 'x',
						'bar'  => 1,
						'xtra' => 'a',
					),
				),
				$schema
			)
		);
	}

	/**
	 * @ticket 48821
	 */
	public function test_array_unique_arrays() {
		$schema = array(
			'type'        => 'array',
			'uniqueItems' => true,
			'items'       => array(
				'type'  => 'array',
				'items' => array(
					'type' => 'string',
				),
			),
		);

		$this->assertTrue( rest_validate_value_from_schema( array( array( 'foo' ), array( 'bar' ) ), $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( array( array( 'foo' ), array( 'foo' ) ), $schema ) );
	}

	/**
	 * @ticket 48821
	 */
	public function test_array_unique_item_types() {
		$schema = array(
			'type'        => 'array',
			'uniqueItems' => true,
			'items'       => array(
				'type' => array( 'boolean', 'boolean' ),
			),
		);

		$this->assertTrue( rest_validate_value_from_schema( array( false, true ), $schema ) );
		$this->assertWPError( rest_validate_value_from_schema( array( false, false ), $schema ) );
	}
}
