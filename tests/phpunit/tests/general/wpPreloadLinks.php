<?php

/**
 * @group general
 * @group template
 * @ticket 42438
 * @covers ::wp_preload_links
 */
class Tests_General_wpPreloadLinks extends WP_UnitTestCase {

	public function test_basic_preload() {
		$expected = "<link rel='preload' href='https://example.com/style.css' as='style' />\n";

		add_filter( 'wp_preload_links', array( $this, 'add_url_basic_preload' ), 10 );
		$actual = get_echo( 'wp_preload_links' );
		remove_filter( 'wp_preload_links', array( $this, 'add_url_basic_preload' ) );

		$this->assertSame( $expected, $actual );
	}

	public function test_basic_preload_multiple_links() {
		$expected = "<link rel='preload' href='https://example.com/style.css' as='style' />\n" .
					"<link rel='preload' href='https://example.com/main.js' as='script' />\n";

		add_filter( 'wp_preload_links', array( $this, 'add_url_basic_preload_multiple_links' ), 10 );
		$actual = get_echo( 'wp_preload_links' );
		remove_filter( 'wp_preload_links', array( $this, 'add_url_basic_preload_multiple_links' ) );

		$this->assertSame( $expected, $actual );
	}

	public function test_preload_link_with_MIME_type() {
		$expected = "<link rel='preload' href='https://example.com/style.css' as='style' />\n" .
					"<link rel='preload' href='https://example.com/video.mp4' as='video' type='video/mp4' />\n" .
					"<link rel='preload' href='https://example.com/main.js' as='script' />\n";

		add_filter( 'wp_preload_links', array( $this, 'add_url_preload_link_with_MIME_type' ), 10 );
		$actual = get_echo( 'wp_preload_links' );
		remove_filter( 'wp_preload_links', array( $this, 'add_url_preload_link_with_MIME_type' ) );

		$this->assertSame( $expected, $actual );
	}

	public function test_preload_link_with_CORS() {
		$expected = "<link rel='preload' href='https://example.com/style.css' as='style' crossorigin='anonymous' />\n" .
					"<link rel='preload' href='https://example.com/video.mp4' as='video' type='video/mp4' />\n" .
					"<link rel='preload' href='https://example.com/main.js' as='script' />\n" .
					"<link rel='preload' href='https://example.com/font.woff2' as='font' type='font/woff2' crossorigin />\n";

		add_filter( 'wp_preload_links', array( $this, 'add_url_preload_link_with_CORS' ), 10 );
		$actual = get_echo( 'wp_preload_links' );
		remove_filter( 'wp_preload_links', array( $this, 'add_url_preload_link_with_CORS' ) );

		$this->assertSame( $expected, $actual );
	}

	public function test_preload_link_with_media() {
		$expected = "<link rel='preload' href='https://example.com/style.css' as='style' crossorigin='anonymous' />\n" .
					"<link rel='preload' href='https://example.com/video.mp4' as='video' type='video/mp4' />\n" .
					"<link rel='preload' href='https://example.com/main.js' as='script' />\n" .
					"<link rel='preload' href='https://example.com/font.woff2' as='font' type='font/woff2' crossorigin />\n" .
					"<link rel='preload' href='https://example.com/image-narrow.png' as='image' media='(max-width: 600px)' />\n" .
					"<link rel='preload' href='https://example.com/image-wide.png' as='image' media='(min-width: 601px)' />\n";

		add_filter( 'wp_preload_links', array( $this, 'add_url_preload_link_with_media' ), 10 );
		$actual = get_echo( 'wp_preload_links' );
		remove_filter( 'wp_preload_links', array( $this, 'add_url_preload_link_with_media' ) );

		$this->assertSame( $expected, $actual );
	}

	public function test_preload_link_with_media_extra_attributes() {
		$expected = "<link rel='preload' href='https://example.com/style.css' as='style' crossorigin='anonymous' />\n" .
					"<link rel='preload' href='https://example.com/video.mp4' as='video' type='video/mp4' />\n" .
					"<link rel='preload' href='https://example.com/main.js' as='script' />\n" .
					"<link rel='preload' href='https://example.com/font.woff2' as='font' type='font/woff2' crossorigin />\n" .
					"<link rel='preload' href='https://example.com/image-640.png' as='image' imagesrcset='640.png 640w, 800.png 800w, 1024.png 1024w' imagesizes='100vw' />\n" .
					"<link rel='preload' as='image' imagesrcset='640.png 640w, 800.png 800w, 1024.png 1024w' imagesizes='100vw' />\n" .
					"<link rel='preload' href='https://example.com/image-wide.png' as='image' media='(min-width: 601px)' />\n" .
					"<link rel='preload' href='https://example.com/image-800.png' as='image' imagesrcset='640.png 640w, 800.png 800w, 1024.png 1024w' />\n";

		add_filter( 'wp_preload_links', array( $this, 'add_url_preload_link_with_media_extra_attributes' ), 10 );
		$actual = get_echo( 'wp_preload_links' );
		remove_filter( 'wp_preload_links', array( $this, 'add_url_preload_link_with_media_extra_attributes' ) );

		$this->assertSame( $expected, $actual );
	}

	public function add_url_basic_preload( $urls ) {
		$urls[] = array(
			'href' => 'https://example.com/style.css',
			'as'   => 'style',
		);
		return $urls;
	}

	public function add_url_basic_preload_multiple_links( $urls ) {
		$urls[] = array(
			'href' => 'https://example.com/style.css',
			'as'   => 'style',
		);
		$urls[] = array(
			'href' => 'https://example.com/main.js',
			'as'   => 'script',
		);
		return $urls;
	}

	public function add_url_preload_link_with_MIME_type( $urls ) {
		$urls[] = array(
			//Should ignore not valid attributes
			'not'  => 'valid',
			'href' => 'https://example.com/style.css',
			'as'   => 'style',
		);
		$urls[] = array(
			'href' => 'https://example.com/video.mp4',
			'as'   => 'video',
			'type' => 'video/mp4',
		);
		$urls[] = array(
			'href' => 'https://example.com/main.js',
			'as'   => 'script',
		);
		return $urls;
	}

	public function add_url_preload_link_with_CORS( $urls ) {
		$urls[] = array(
			'href'        => 'https://example.com/style.css',
			'as'          => 'style',
			'crossorigin' => 'anonymous',
		);
		$urls[] = array(
			'href' => 'https://example.com/video.mp4',
			'as'   => 'video',
			'type' => 'video/mp4',
		);
		$urls[] = array(
			'href' => 'https://example.com/main.js',
			'as'   => 'script',
		);
		$urls[] = array(
			//Should ignore not valid attributes.
			'ignore' => 'ignore',
			'href'   => 'https://example.com/font.woff2',
			'as'     => 'font',
			'type'   => 'font/woff2',
			'crossorigin',
		);
		return $urls;
	}

	public function add_url_preload_link_with_media( $urls ) {
		$urls[] = array(
			'href'        => 'https://example.com/style.css',
			'as'          => 'style',
			'crossorigin' => 'anonymous',
		);
		$urls[] = array(
			'href' => 'https://example.com/video.mp4',
			'as'   => 'video',
			'type' => 'video/mp4',
		);
		// Duplicated href should be ignored.
		$urls[] = array(
			'href' => 'https://example.com/video.mp4',
			'as'   => 'video',
			'type' => 'video/mp4',
		);
		$urls[] = array(
			'href' => 'https://example.com/main.js',
			'as'   => 'script',
		);
		$urls[] = array(
			'href' => 'https://example.com/font.woff2',
			'as'   => 'font',
			'type' => 'font/woff2',
			'crossorigin',
		);
		$urls[] = array(
			'href'  => 'https://example.com/image-narrow.png',
			'as'    => 'image',
			'media' => '(max-width: 600px)',
		);
		$urls[] = array(
			'href'  => 'https://example.com/image-wide.png',
			'as'    => 'image',
			'media' => '(min-width: 601px)',
		);
		return $urls;
	}

	public function add_url_preload_link_with_media_extra_attributes( $urls ) {
		$urls[] = array(
			'href'        => 'https://example.com/style.css',
			'as'          => 'style',
			'crossorigin' => 'anonymous',
		);
		$urls[] = array(
			'href' => 'https://example.com/video.mp4',
			'as'   => 'video',
			'type' => 'video/mp4',
		);
		$urls[] = array(
			'href' => 'https://example.com/main.js',
			'as'   => 'script',
		);
		$urls[] = array(
			'href' => 'https://example.com/font.woff2',
			'as'   => 'font',
			'type' => 'font/woff2',
			'crossorigin',
		);
		// imagesrcset only possible when using image, ignore.
		$urls[] = array(
			'href'        => 'https://example.com/font.woff2',
			'as'          => 'font',
			'type'        => 'font/woff2',
			'imagesrcset' => '640.png 640w, 800.png 800w, 1024.png 1024w',
		);
		// imagesizes only possible when using image, ignore.
		$urls[] = array(
			'href'        => 'https://example.com/font.woff2',
			'as'          => 'font',
			'type'        => 'font/woff2',
			'imagesizes'  => '100vw',
		);
		// Duplicated href should be ignored.
		$urls[] = array(
			'href' => 'https://example.com/font.woff2',
			'as'   => 'font',
			'type' => 'font/woff2',
			'crossorigin',
		);
		$urls[] = array(
			'href'        => 'https://example.com/image-640.png',
			'as'          => 'image',
			'imagesrcset' => '640.png 640w, 800.png 800w, 1024.png 1024w',
			'imagesizes'  => '100vw',
		);
		// Omit href so that unsupporting browsers won't request a useless image.
		$urls[] = array(
			'as'          => 'image',
			'imagesrcset' => '640.png 640w, 800.png 800w, 1024.png 1024w',
			'imagesizes'  => '100vw',
		);
		// Duplicated imagesrcset should be ignored.
		$urls[] = array(
			'as'          => 'image',
			'imagesrcset' => '640.png 640w, 800.png 800w, 1024.png 1024w',
			'imagesizes'  => '100vw',
		);
		$urls[] = array(
			'href'  => 'https://example.com/image-wide.png',
			'as'    => 'image',
			'media' => '(min-width: 601px)',
		);
		// No href but not imagesrcset, should be ignored
		$urls[] = array(
			'as'    => 'image',
			'media' => '(min-width: 601px)',
		);
		// imagesizes is optional
		$urls[] = array(
			'href'        => 'https://example.com/image-800.png',
			'as'          => 'image',
			'imagesrcset' => '640.png 640w, 800.png 800w, 1024.png 1024w',
		);
		// imagesizes should be ignored since imagesrcset not present.
		$urls[] = array(
			'href'        => 'https://example.com/image-640.png',
			'as'          => 'image',
			'imagesizes'  => '100vw',
		);
		return $urls;
	}

}
