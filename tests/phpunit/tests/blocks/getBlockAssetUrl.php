<?php
/**
 * Tests for block asset urls.
 *
 * @package WordPress
 * @subpackage Blocks
 *
 * @since 6.4.0
 *
 * @group blocks
 * @covers ::get_block_asset_url
 */
class Tests_Get_Block_Asset_Url extends WP_UnitTestCase {

	/**
	 * Original theme directory.
	 *
	 * @var string[]
	 */
	private $orig_theme_dir;

	public function set_up() {
		global $wp_theme_directories;

		parent::set_up();

		// Sets up the `wp-content/themes/` directory to ensure consistency when running tests.
		$this->orig_theme_dir = $wp_theme_directories;
		$wp_theme_directories = array( WP_CONTENT_DIR . '/themes', realpath( DIR_TESTDATA . '/themedir1' ) );

		wp_clean_themes_cache();
		unset( $GLOBALS['wp_themes'] );
	}

	public function tear_down() {
		global $wp_theme_directories;

		$wp_theme_directories = $this->orig_theme_dir;

		wp_clean_themes_cache();
		unset( $GLOBALS['wp_themes'] );

		parent::tear_down();
	}

	/**
	 * @ticket 58525
	 */
	public function test_core_block() {
		$path = ABSPATH . WPINC . '/blocks/file/view.min.js';
		$url  = get_block_asset_url( $path );

		$this->assertStringNotContainsString( ABSPATH . WPINC, $url );
		$this->assertSame( includes_url( '/blocks/file/view.min.js' ), $url );
	}

	/**
	 * @ticket 58525
	 */
	public function test_parent_theme() {
		switch_theme( 'block-theme' );

		$path = get_template_directory() . '/blocks/example-block/view.js';
		$url  = get_block_asset_url( $path );

		//$this->assertStringNotContainsString( get_template_directory(), $url );
		$this->assertSame( get_template_directory_uri() . '/blocks/example-block/view.js', $url );

	}

	/**
	 * @ticket 58525
	 */
	public function test_child_theme() {
		switch_theme( 'block-theme-child' );

		$path = get_stylesheet_directory() . '/blocks/example-block/view.js';
		$url  = get_block_asset_url( $path );

		//$this->assertStringNotContainsString( get_stylesheet_directory(), $url );
		$this->assertSame( get_stylesheet_directory_uri() . '/blocks/example-block/view.js', $url );

	}

	/**
	 * @ticket 58525
	 */
	public function test_plugin() {
		$path = WP_PLUGIN_DIR . '/test-plugin/blocks/example-block/view.js';
		$url  = get_block_asset_url( $path );

		$this->assertStringNotContainsString( WP_PLUGIN_DIR, $url );
		$this->assertSame( plugins_url( 'view.js', $path ), $url );
		$this->assertStringStartsWith( WP_PLUGIN_URL, $url );

	}

	/**
	 * @ticket 58525
	 */
	public function test_muplugin() {
		$path = WPMU_PLUGIN_DIR . '/test-plugin/example-block/view.js';
		$url  = get_block_asset_url( $path );

		$this->assertStringNotContainsString( WPMU_PLUGIN_DIR, $url );
		$this->assertSame( plugins_url( 'view.js', $path ), $url );
		$this->assertStringStartsWith( WPMU_PLUGIN_URL, $url );
	}


	public function fix_url( $current, $template ) {
		return content_url( 'themes' ) . '/' . $template;
	}
}
