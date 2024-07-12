<?php
/**
 * Unit tests covering WP_HTML_Tag_Processor modifiable text functionality.
 *
 * @package WordPress
 * @subpackage HTML-API
 */

declare( strict_types = 1 );

/**
 * @group html-api
 *
 * @coversDefaultClass WP_HTML_Tag_Processor
 */
class Tests_HtmlApi_WpHtmlTagProcessorModifiableText extends WP_UnitTestCase {
	/**
	 * Ensures that modifiable text updates are not applied where they aren't supported.
	 *
	 * @ticket {TICKET_NUMBER}
	 *
	 * @dataProvider data_tokens_not_supporting_modifiable_text_updates
	 *
	 * @param string $html             Contains HTML with a token not supporting modifiable text updates.
	 * @param int    $advance_n_tokens Count of times to run `next_token()` before reaching target node.
	 */
	public function test_rejects_updates_on_unsupported_match_locations( string $html, int $advance_n_tokens ) {
		$processor = new WP_HTML_Tag_Processor( $html );
		while ( --$advance_n_tokens >= 0 ) {
			$processor->next_token();
		}

		$this->assertFalse(
			$processor->set_modifiable_text( 'Bazinga!' ),
			'Should have prevented modifying the text at the target node.'
		);

		$this->assertSame(
			$html,
			$processor->get_updated_html(),
			'Should not have modified the input document in any way.'
		);
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	private static function data_tokens_not_supporting_modifiable_text_updates() {
		return array(
			'Before parsing'               => array( 'nothing to see here', 0 ),
			'After parsing'                => array( 'nothing here either', 2 ),
			'Incomplete document'          => array( '<tag without="an end', 1 ),
			'Presumptuous closer'          => array( 'before</>after', 2 ),
			'Invalid (CDATA)'              => array( '<![CDATA[this is a comment]]>', 1 ),
			'Invalid (shortest comment)'   => array( '<!-->', 1 ),
			'Invalid (shorter comment)'    => array( '<!--->', 1 ),
			'Invalid (markup declaration)' => array( '<!run>', 1 ),
			'Invalid (PI-like node)'       => array( '<?xml is not html ?>', 1 ),
		);
	}

	/**
	 * Ensures that modifiable text updates are applied as expected to supported nodes.
	 *
	 * @ticket {TICKET_NUMBER}
	 *
	 * @dataProvider data_tokens_with_basic_modifiable_text_updates
	 *
	 * @param string $html             Contains HTML with a token supporting modifiable text updates.
	 * @param int    $advance_n_tokens Count of times to run `next_token()` before reaching target node.
	 * @param string $raw_replacement  This should be escaped properly when replaced as modifiable text.
	 * @param string $transformed      Expected output after updating modifiable text.
	 */
	public function test_updates_basic_modifiable_text_on_supported_nodes( string $html, int $advance_n_tokens, string $raw_replacement, string $transformed ) {
		$processor = new WP_HTML_Tag_Processor( $html );
		while ( --$advance_n_tokens >= 0 ) {
			$processor->next_token();
		}

		$this->assertTrue(
			$processor->set_modifiable_text( $raw_replacement ),
			'Should have modified the text at the target node.'
		);

		$this->assertSame(
			$transformed,
			$processor->get_updated_html(),
			"Should have transformed the HTML as expected why modifying the target node's modifiable text."
		);
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	private static function data_tokens_with_basic_modifiable_text_updates() {
		return array(
			'Text node (start)'       => array( 'Text', 1, 'Blubber', 'Blubber' ),
			'Text node (middle)'      => array( '<em>Bold move</em>', 2, 'yo', '<em>yo</em>' ),
			'Text node (end)'         => array( '<img>of a dog', 2, 'of a cat', '<img>of a cat' ),
			'Encoded text node'       => array( '<figcaption>birds and dogs</figcaption>', 2, '<birds> & <dogs>', '<figcaption>&lt;birds&gt; &amp; &lt;dogs&gt;</figcaption>' ),
			'SCRIPT tag'              => array( 'before<script></script>after', 2, 'const img = "<img> & <br>";', 'before<script>const img = "<img> & <br>";</script>after' ),
			'STYLE tag'               => array( '<style></style>', 1, 'p::before { content: "<img> & </style>"; }', '<style>p::before { content: "<img> & \3c\2fstyle>"; }</style>' ),
			'TEXTAREA tag'            => array( 'a<textarea>has no need to escape</textarea>b', 2, "so it <doesn't>", "a<textarea>so it <doesn't></textarea>b" ),
			'TEXTAREA (escape)'       => array( 'a<textarea>has no need to escape</textarea>b', 2, 'but it does for </textarea>', 'a<textarea>but it does for &lt;/textarea></textarea>b' ),
			'TEXTAREA (escape+attrs)' => array( 'a<textarea>has no need to escape</textarea>b', 2, 'but it does for </textarea not an="attribute">', 'a<textarea>but it does for &lt;/textarea not an="attribute"></textarea>b' ),
			'TITLE tag'               => array( 'a<title>has no need to escape</title>b', 2, "so it <doesn't>", "a<title>so it <doesn't></title>b" ),
			'TITLE (escape)'          => array( 'a<title>has no need to escape</title>b', 2, 'but it does for </title>', 'a<title>but it does for &lt;/title></title>b' ),
			'TITLE (escape+attrs)'    => array( 'a<title>has no need to escape</title>b', 2, 'but it does for </title not an="attribute">', 'a<title>but it does for &lt;/title not an="attribute"></title>b' ),
		);
	}
}
