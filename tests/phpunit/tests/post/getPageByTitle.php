<?php

/**
 * @group post
 * @covers ::get_page_by_title
 */
class Tests_Post_GetPageByTitle extends WP_UnitTestCase {
	/**
	 * @ticket 36905
	 */
	public function test_get_page_by_title_priority() {
		$attachment = self::factory()->post->create_and_get(
			array(
				'post_title' => 'some-other-page',
				'post_type'  => 'attachment',
			)
		);
		$page       = self::factory()->post->create_and_get(
			array(
				'post_title' => 'some-page',
				'post_type'  => 'page',
			)
		);

		// get_page_by_title() should return a post of the requested type before returning an attachment.
		$this->assertEquals( $page, get_page_by_title( 'some-page' ) );

		// Make sure get_page_by_title() will still select an attachment when a post of the requested type doesn't exist.
		$this->assertEquals( $attachment, get_page_by_title( 'some-other-page', OBJECT, 'attachment' ) );
	}

	/**
	 * @ticket 36905
	 */
	public function test_should_match_top_level_page() {
		$page = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'foo',
			)
		);

		$found = get_page_by_title( 'foo' );

		$this->assertSame( $page, $found->ID );
	}

	/**
	 * @ticket 36905
	 */
	public function test_inherit() {
		$page = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'foo',
				'post_status' => 'inherit',
			)
		);

		$found = get_page_by_title( 'foo' );

		$this->assertSame( $page, $found->ID );
	}

	/**
	 * @ticket 36905
	 */
	public function test_should_obey_post_type() {
		register_post_type( 'wptests_pt' );

		$page = self::factory()->post->create(
			array(
				'post_type'  => 'wptests_pt',
				'post_title' => 'foo',
			)
		);

		$found = get_page_by_title( 'foo' );
		$this->assertNull( $found );

		$found = get_page_by_title( 'foo', OBJECT, 'wptests_pt' );
		$this->assertSame( $page, $found->ID );
	}


	/**
	 * @ticket 36905
	 */
	public function test_should_hit_cache() {
		global $wpdb;

		$page = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'foo',
			)
		);

		// Prime cache.
		$found = get_page_by_title( 'foo' );
		$this->assertSame( $page, $found->ID );

		$num_queries = $wpdb->num_queries;

		$found = get_page_by_title( 'foo' );
		$this->assertSame( $page, $found->ID );
		$this->assertSame( $num_queries, $wpdb->num_queries );
	}

	/**
	 * @ticket 36905
	 */
	public function test_bad_path_should_be_cached() {
		global $wpdb;

		// Prime cache.
		$found = get_page_by_title( 'foo' );
		$this->assertNull( $found );

		$num_queries = $wpdb->num_queries;

		$found = get_page_by_title( 'foo' );
		$this->assertNull( $found );
		$this->assertSame( $num_queries, $wpdb->num_queries );
	}

	/**
	 * @ticket 36905
	 */
	public function test_bad_path_served_from_cache_should_not_fall_back_on_current_post() {
		global $wpdb, $post;

		// Fake the global.
		$post = self::factory()->post->create_and_get();

		// Prime cache.
		$found = get_page_by_title( 'foo' );
		$this->assertNull( $found );

		$num_queries = $wpdb->num_queries;

		$found = get_page_by_title( 'foo' );
		$this->assertNull( $found );
		$this->assertSame( $num_queries, $wpdb->num_queries );

		unset( $post );
	}

	/**
	 * @ticket 36905
	 */
	public function test_cache_should_not_match_post_in_different_post_type_with_same_path() {
		global $wpdb;

		register_post_type( 'wptests_pt' );

		$p1 = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'foo',
			)
		);

		$p2 = self::factory()->post->create(
			array(
				'post_type'  => 'wptests_pt',
				'post_title' => 'foo',
			)
		);

		// Prime cache for the page.
		$found = get_page_by_title( 'foo' );
		$this->assertSame( $p1, $found->ID );

		$num_queries = $wpdb->num_queries;

		$found = get_page_by_title( 'foo', OBJECT, 'wptests_pt' );
		$this->assertSame( $p2, $found->ID );
		$num_queries ++;
		$this->assertSame( $num_queries, $wpdb->num_queries );
	}

	/**
	 * @ticket 36905
	 */
	public function test_cache_should_be_invalidated_when_post_name_is_edited() {
		global $wpdb;

		$page = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'foo',
			)
		);

		// Prime cache.
		$found = get_page_by_title( 'foo' );
		$this->assertSame( $page, $found->ID );

		wp_update_post(
			array(
				'ID'         => $page,
				'post_title' => 'bar',
			)
		);

		$num_queries = $wpdb->num_queries;

		$found = get_page_by_title( 'bar' );
		$this->assertSame( $page, $found->ID );
		$num_queries ++;
		$this->assertSame( $num_queries, $wpdb->num_queries );
	}

	/**
	 * @ticket 36905
	 */
	public function test_output_param_should_be_obeyed_for_cached_value() {
		$page = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'foo',
			)
		);

		// Prime cache.
		$found = get_page_by_title( 'foo' );
		$this->assertSame( $page, $found->ID );

		$object = get_page_by_title( 'foo', OBJECT );
		$this->assertIsObject( $object );
		$this->assertSame( $page, $object->ID );

		$array_n = get_page_by_title( 'foo', ARRAY_N );
		$this->assertIsArray( $array_n );
		$this->assertSame( $page, $array_n[0] );

		$array_a = get_page_by_title( 'foo', ARRAY_A );
		$this->assertIsArray( $array_a );
		$this->assertSame( $page, $array_a['ID'] );
	}
}
