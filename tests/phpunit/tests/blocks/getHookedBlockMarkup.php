<?php
/**
 * Tests for the get_hooked_block_markup function.
 *
 * @package WordPress
 * @subpackage Blocks
 *
 * @since 6.5.0
 *
 * @group blocks
 * @group block-hooks
 */
class Tests_Blocks_GetHookedBlockMarkup extends WP_UnitTestCase {
	/**
	 * @ticket 60008
	 *
	 * @covers ::get_hooked_block_markup
	 */
	public function test_get_hooked_block_markup_adds_metadata() {
		$hooked_block = array(
			'blockName' => 'tests/hooked-block',
		);

		$anchor_block = array(
			'blockName' => 'tests/anchor-block',
		);

		$actual = get_hooked_block_markup( $hooked_block, $anchor_block );
		$this->assertSame( array( 'tests/hooked-block' ), $anchor_block['attrs']['metadata']['ignoredHookedBlocks'] );
		$this->assertSame( '<!-- wp:tests/hooked-block /-->', $actual );
	}

	/**
	 * @ticket 60008
	 *
	 * @covers ::get_hooked_block_markup
	 */
	public function test_get_hooked_block_markup_if_block_is_already_hooked() {
		$hooked_block = array(
			'blockName' => 'tests/hooked-block',
		);

		$anchor_block = array(
			'blockName' => 'tests/anchor-block',
			'attrs'     => array(
				'metadata' => array(
					'ignoredHookedBlocks' => array( 'tests/hooked-block' ),
				),
			),
		);

		$actual = get_hooked_block_markup( $hooked_block, $anchor_block );
		$this->assertSame( array( 'tests/hooked-block' ), $anchor_block['attrs']['metadata']['ignoredHookedBlocks'] );
		$this->assertSame( '', $actual );
	}

	/**
	 * @ticket 60008
	 *
	 * @covers ::get_hooked_block_markup
	 */
	public function test_get_hooked_block_markup_adds_to_ignored_hooked_blocks() {
		$other_hooked_block = array(
			'blockName' => 'tests/other-hooked-block',
		);

		$anchor_block = array(
			'blockName' => 'tests/anchor-block',
			'attrs'     => array(
				'metadata' => array(
					'ignoredHookedBlocks' => array( 'tests/hooked-block' ),
				),
			),
		);

		$actual = get_hooked_block_markup( $other_hooked_block, $anchor_block );
		$this->assertSame( array( 'tests/hooked-block', 'tests/other-hooked-block' ), $anchor_block['attrs']['metadata']['ignoredHookedBlocks'] );
		$this->assertSame( '<!-- wp:tests/other-hooked-block /-->', $actual );
	}
}
