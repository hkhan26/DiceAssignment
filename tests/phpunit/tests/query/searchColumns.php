<?php
/**
 * Testing the search columns support in `WP_Query`.
 *
 * @package WordPress\UnitTests
 * @since 6.2.0
 */

/**
 * Test cases for the search columns feature.
 *
 * @group query
 * @group search
 *
 * @since 6.2.0
 */
class Tests_Query_SearchColumns extends WP_UnitTestCase {
	/**
	 * The post ID of the first fixture post.
	 *
	 * @since 6.2.0
	 * @var int $pid1
	 */
	protected static $pid1;

	/**
	 * The post ID of the second fixture post.
	 *
	 * @since 6.2.0
	 * @var int $pid2
	 */
	protected static $pid2;

	/**
	 * The post ID of the third fixture post.
	 *
	 * @since 6.2.0
	 * @var int $pid3
	 */
	protected static $pid3;

	/**
	 * Create posts fixtures.
	 *
	 * @param WP_UnitTest_Factory $factory The factory instance.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$pid1 = self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_title'   => 'foo title',
				'post_excerpt' => 'foo excerpt',
				'post_content' => 'foo content',
			)
		);
		self::$pid2 = self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_title'   => 'bar title',
				'post_excerpt' => 'foo bar excerpt',
				'post_content' => 'foo bar content',
			)
		);

		self::$pid3 = self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_title'   => 'baz title',
				'post_excerpt' => 'baz bar excerpt',
				'post_content' => 'baz bar foo content',
			)
		);
	}

	/**
	 * The search should use default search columns when search columns are empty.
	 */
	public function test_s_should_use_default_search_columns_when_empty_search_columns() {
		$q = new WP_Query(
			array(
				's'              => 'foo',
				'search_columns' => array(),
				'fields'         => 'ids',
			)
		);

		$this->assertStringContainsString( 'post_title', $q->request, 'SQL request should contain post_title string.' );
		$this->assertStringContainsString( 'post_excerpt', $q->request, 'SQL request should contain post_excerpt string.' );
		$this->assertStringContainsString( 'post_content', $q->request, 'SQL request should contain post_content string.' );
		$this->assertEqualSets( array( self::$pid1, self::$pid2, self::$pid3 ), $q->posts, 'Query results should be equal to the set.' );
	}

	/**
	 * The search should support the post_title search column.
	 */
	public function test_s_should_support_post_title_search_column() {
		$q = new WP_Query(
			array(
				's'              => 'foo',
				'search_columns' => array( 'post_title' ),
				'fields'         => 'ids',
			)
		);

		$this->assertEqualSets( array( self::$pid1 ), $q->posts );
	}

	/**
	 * The search should support the `post_excerpt` search column.
	 */
	public function test_s_should_support_post_excerpt_search_column() {
		$q = new WP_Query(
			array(
				's'              => 'foo',
				'search_columns' => array( 'post_excerpt' ),
				'fields'         => 'ids',
			)
		);

		$this->assertEqualSets( array( self::$pid1, self::$pid2 ), $q->posts );
	}

	/**
	 * The search should support the `post_content` search column.
	 */
	public function test_s_should_support_post_content_search_column() {
		$q = new WP_Query(
			array(
				's'              => 'foo',
				'search_columns' => array( 'post_content' ),
				'fields'         => 'ids',
			)
		);
		$this->assertEqualSets( array( self::$pid1, self::$pid2, self::$pid3 ), $q->posts );
	}

	/**
	 * The search should support the `post_title` and `post_excerpt` search columns together.
	 */
	public function test_s_should_support_post_title_and_post_excerpt_search_columns() {
		$q = new WP_Query(
			array(
				's'              => 'foo',
				'search_columns' => array( 'post_title', 'post_excerpt' ),
				'fields'         => 'ids',
			)
		);

		$this->assertEqualSets( array( self::$pid1, self::$pid2 ), $q->posts );
	}

	/**
	 * The search should support the `post_title` and `post_content` search columns together.
	 */
	public function test_s_should_support_post_title_and_post_content_search_columns() {
		$q = new WP_Query(
			array(
				's'              => 'foo',
				'search_columns' => array( 'post_title', 'post_content' ),
				'fields'         => 'ids',
			)
		);

		$this->assertEqualSets( array( self::$pid1, self::$pid2, self::$pid3 ), $q->posts );
	}

	/**
	 * The search should support the `post_excerpt` and `post_content` search columns together.
	 */
	public function test_s_should_support_post_excerpt_and_post_content_search_columns() {
		$q = new WP_Query(
			array(
				's'              => 'foo',
				'search_columns' => array( 'post_excerpt', 'post_content' ),
				'fields'         => 'ids',
			)
		);

		$this->assertEqualSets( array( self::$pid1, self::$pid2, self::$pid3 ), $q->posts );
	}

	/**
	 * The search should support the `post_title`, `post_excerpt` and `post_content` search columns together.
	 */
	public function test_s_should_support_post_title_and_post_excerpt_and_post_content_search_columns() {
		$q = new WP_Query(
			array(
				's'              => 'foo',
				'search_columns' => array( 'post_title', 'post_excerpt', 'post_content' ),
				'fields'         => 'ids',
			)
		);

		$this->assertEqualSets( array( self::$pid1, self::$pid2, self::$pid3 ), $q->posts );
	}

	/**
	 * The search should use default support columns when using a non-existing search column.
	 */
	public function test_s_should_use_default_search_columns_when_using_non_existing_search_column() {
		$q = new WP_Query(
			array(
				's'              => 'foo',
				'search_columns' => array( 'post_non_existing_column' ),
				'fields'         => 'ids',
			)
		);

		$this->assertStringContainsString( 'post_title', $q->request );
		$this->assertStringContainsString( 'post_excerpt', $q->request );
		$this->assertStringContainsString( 'post_content', $q->request );
		$this->assertEqualSets( array( self::$pid1, self::$pid2, self::$pid3 ), $q->posts );
	}

	/**
	 * The search should support ignore a non-existing search column when used together with a supported one.
	 */
	public function test_s_should_ignore_non_existing_search_column_when_used_with_supported_one() {
		$q = new WP_Query(
			array(
				's'              => 'foo',
				'search_columns' => array( 'post_title', 'post_non_existing_column' ),
				'fields'         => 'ids',
			)
		);

		$this->assertEqualSets( array( self::$pid1 ), $q->posts );
	}

	/**
	 * The search should support search columns when searching multiple terms.
	 */
	public function test_s_should_support_search_columns_when_searching_multiple_terms() {
		$q = new WP_Query(
			array(
				's'              => 'foo bar',
				'search_columns' => array( 'post_content' ),
				'fields'         => 'ids',
			)
		);

		$this->assertEqualSets( array( self::$pid2, self::$pid3 ), $q->posts );
	}

	/**
	 * The search should support search columns when searching for a sentence.
	 */
	public function test_s_should_support_search_columns_when_sentence_true() {
		$q = new WP_Query(
			array(
				's'              => 'bar foo',
				'search_columns' => array( 'post_content' ),
				'sentence'       => true,
				'fields'         => 'ids',
			)
		);

		$this->assertEqualSets( array( self::$pid3 ), $q->posts );
	}

	/**
	 * The search should support search columns when searching for a sentence.
	 */
	public function test_s_should_support_search_columns_when_sentence_false() {
		$q = new WP_Query(
			array(
				's'              => 'bar foo',
				'search_columns' => array( 'post_content' ),
				'sentence'       => false,
				'fields'         => 'ids',
			)
		);

		$this->assertEqualSets( array( self::$pid2, self::$pid3 ), $q->posts );
	}

	/**
	 * The search should support search columns when searched with a term exclusion.
	 */
	public function test_s_should_support_search_columns_when_searched_with_term_exclusion() {
		$q = new WP_Query(
			array(
				's'              => 'bar -baz',
				'search_columns' => array( 'post_excerpt', 'post_content' ),
				'fields'         => 'ids',
			)
		);

		$this->assertEqualSets( array( self::$pid2 ), $q->posts );
	}

	/**
	 * The search columns should be filterable with the `post_search_columns` filter.
	 */
	public function test_search_columns_should_be_filterable() {
		add_filter( 'post_search_columns', array( $this, 'post_supported_search_column' ), 10, 3 );
		$q = new WP_Query(
			array(
				's'      => 'foo',
				'fields' => 'ids',
			)
		);
		remove_filter( 'post_search_columns', array( $this, 'post_supported_search_column' ) );

		$this->assertEqualSets( array( self::$pid1 ), $q->posts );
	}

	/**
	 * Filter callback that sets a supported search column.
	 *
	 * @param  string[] $search_columns Array of column names to be searched.
	 * @param  string   $search         Text being searched.
	 * @param  WP_Query $wp_query       The current WP_Query instance.
	 * @return string[] $search_columns Array of column names to be searched.
	 */
	public function post_supported_search_column( $search_columns, $search, $wp_query ) {
		$search_columns = array( 'post_title' );
		return $search_columns;
	}

	/**
	 * The search columns should not be filterable when using non-supported search columns.
	 */
	public function test_search_columns_should_not_filterable_when_non_supported_search_columns() {
		add_filter( 'post_search_columns', array( $this, 'post_non_supported_search_column' ), 10, 3 );
		$q = new WP_Query(
			array(
				's'      => 'foo',
				'fields' => 'ids',
			)
		);
		remove_filter( 'post_search_columns', array( $this, 'post_non_supported_search_column' ) );

		$this->assertStringNotContainsString( 'post_name', $q->request );
		$this->assertEqualSets( array( self::$pid1, self::$pid2, self::$pid3 ), $q->posts );
	}

	/**
	 * Filter callback that sets an existing but non-supported search column.
	 *
	 * @param  string[] $search_columns Array of column names to be searched.
	 * @param  string   $search         Text being searched.
	 * @param  WP_Query $wp_query       The current WP_Query instance.
	 * @return string[] $search_columns Array of column names to be searched.
	 */
	public function post_non_supported_search_column( $search_columns, $search, $wp_query ) {
		$search_columns = array( 'post_name' );
		return $search_columns;
	}

	/**
	 * The search columns should not be filterable with non-existing search columns.
	 */
	public function xtest_search_columns_should_not_filterable_non_existing_search_column() {
		add_filter( 'post_search_columns', array( $this, 'post_non_existing_search_column' ), 10, 3 );
		$q = new WP_Query(
			array(
				's'      => 'foo',
				'fields' => 'ids',
			)
		);
		remove_filter( 'post_search_columns', array( $this, 'post_non_existing_search_column' ) );

		$this->assertNotContains( 'post_non_existing_column', $q->request );
		$this->assertEqualSets( array( self::$pid1, self::$pid2, self::$pid3 ), $q->posts );
	}

	/**
	 * Filter callback that sets a non-existing search column.
	 *
	 * @param  string[] $search_columns Array of column names to be searched.
	 * @param  string   $search         Text being searched.
	 * @param  WP_Query $wp_query       The current WP_Query instance.
	 * @return string[] $search_columns Array of column names to be searched.
	 */
	public function post_non_existing_search_column( $search_columns, $search, $wp_query ) {
		$search_columns = array( 'post_non_existing_column' );
		return $search_columns;
	}

}
