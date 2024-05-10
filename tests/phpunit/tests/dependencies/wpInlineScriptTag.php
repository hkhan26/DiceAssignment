<?php

/**
 * Test wp_get_inline_script_tag() and wp_print_inline_script_tag().
 *
 * @group dependencies
 * @group scripts
 * @covers ::wp_get_inline_script_tag
 * @covers ::wp_print_inline_script_tag
 */
class Tests_Functions_wpInlineScriptTag extends WP_UnitTestCase {

	private $original_theme_features = array();

	public function set_up() {
		global $_wp_theme_features;
		parent::set_up();
		$this->original_theme_features = $_wp_theme_features;
	}

	public function tear_down() {
		global $_wp_theme_features;
		$_wp_theme_features = $this->original_theme_features;
		parent::tear_down();
	}

	private $event_handler = <<<'JS'
document.addEventListener( 'DOMContentLoaded', function () {
	document.getElementById( 'elementID' )
			.addEventListener( 'click', function( event ) {
				event.preventDefault();
			});
});
JS;

	public function get_inline_script_tag_type_set() {
		$this->assertSame(
			'<script type="application/javascript" nomodule>' . "\n{$this->event_handler}\n</script>\n",
			wp_get_inline_script_tag(
				$this->event_handler,
				array(
					'type'     => 'application/javascript',
					'async'    => false,
					'nomodule' => true,
				)
			)
		);

		$this->assertSame(
			'<script type="application/javascript" nomodule>' . "\n{$this->event_handler}\n</script>\n",
			wp_get_inline_script_tag(
				$this->event_handler,
				array(
					'type'     => 'application/javascript',
					'async'    => false,
					'nomodule' => true,
				)
			)
		);
	}

	public function test_get_inline_script_tag_type_not_set() {
		$this->assertSame(
			"<script nomodule>\n{$this->event_handler}\n</script>\n",
			wp_get_inline_script_tag(
				$this->event_handler,
				array(
					'async'    => false,
					'nomodule' => true,
				)
			)
		);
	}

	public function test_get_inline_script_tag_unescaped_src() {
		$this->assertSame(
			"<script>\n{$this->event_handler}\n</script>\n",
			wp_get_inline_script_tag( $this->event_handler )
		);
	}

	public function test_print_script_tag_prints_get_inline_script_tag() {
		add_filter(
			'wp_inline_script_attributes',
			static function ( $attributes ) {
				if ( isset( $attributes['id'] ) && 'utils-js-extra' === $attributes['id'] ) {
					$attributes['async'] = true;
				}
				return $attributes;
			}
		);

		$attributes = array(
			'id'       => 'utils-js-before',
			'nomodule' => true,
		);

		$this->assertSame(
			wp_get_inline_script_tag( $this->event_handler, $attributes ),
			get_echo(
				'wp_print_inline_script_tag',
				array(
					$this->event_handler,
					$attributes,
				)
			)
		);

		$this->assertSame(
			wp_get_inline_script_tag( $this->event_handler, $attributes ),
			get_echo(
				'wp_print_inline_script_tag',
				array(
					$this->event_handler,
					$attributes,
				)
			)
		);
	}

	/**
	 * Tests that CDATA wrapper duplication is handled.
	 *
	 * @ticket 58664
	 */
	public function test_get_inline_script_tag_with_duplicated_cdata_wrappers() {
		$this->assertSame(
			"<script type=\"text/javascript\">\n/* <![CDATA[ */\n/* <![CDATA[ */ console.log( 'Hello World!' ); /* ]]]]><![CDATA[> */\n/* ]]> */\n</script>\n",
			wp_get_inline_script_tag( "/* <![CDATA[ */ console.log( 'Hello World!' ); /* ]]> */" )
		);
	}

}
