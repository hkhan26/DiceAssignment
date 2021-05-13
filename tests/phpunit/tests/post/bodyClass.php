<?php

/**
 * @group post
 * @covers ::body_class
 */
class Tests_Post_BodyClass extends WP_UnitTestCase {
	protected $post_id;

	public function setUp() {
		parent::setUp();
		$this->post_id = self::factory()->post->create();
	}

	/**
	 * @covers ::get_body_class
	 */
	public function test_body_class() {
		$expected = 'class="' . implode( ' ', get_body_class( '', $this->post_id ) ) . '"';
		$this->expectOutputString( $expected );
		body_class( '', $this->post_id );
	}

	/**
	 * @covers ::get_body_class
	 */
	public function test_body_class_extra_esc_attr() {
		$classes              = get_body_class( '', $this->post_id );
		$escaped_again        = array_map( 'esc_attr', $classes );
		$escaped_another_time = 'class="' . esc_attr( implode( ' ', $escaped_again ) ) . '"';

		$this->expectOutputString( $escaped_another_time );
		body_class( '', $this->post_id );
	}
}
