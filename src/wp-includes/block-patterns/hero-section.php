<?php
/**
 * Hero section
 *
 * @package WordPress
 */

return array(
	'title'       => __( 'Hero section' ),
	'categories'  => array( 'art' ),
	'content'     => '<!-- wp:cover {"url":"https://blockpatterndesigns.mystagingwebsite.com/wp-content/uploads/2021/03/504382ldsdl-scaled.jpg","id":613,"hasParallax":true,"dimRatio":40,"overlayColor":"black","minHeight":100,"minHeightUnit":"vh","contentPosition":"center center","align":"full"} -->
	<div class="wp-block-cover alignfull has-background-dim-40 has-black-background-color has-background-dim has-parallax" style="background-image:url(https://blockpatterndesigns.mystagingwebsite.com/wp-content/uploads/2021/03/504382ldsdl-scaled.jpg);min-height:100vh"><div class="wp-block-cover__inner-container"><!-- wp:heading {"level":1,"align":"wide","textColor":"white"} -->
	<h1 class="alignwide has-white-color has-text-color"><strong><em>'. __("Overseas: 1500 — 1960") .'</em></strong></h1>
	<!-- /wp:heading -->
	
	<!-- wp:columns {"align":"wide"} -->
	<div class="wp-block-columns alignwide"><!-- wp:column {"width":"60%"} -->
	<div class="wp-block-column" style="flex-basis:60%"><!-- wp:paragraph -->
	<p>'. __("An exhibition about the different representations of the ocean throughout time, between the sixteenth and the twentieth century. Taking place in our Open Room in <em>Floor 2</em>.") .'</p>
	<!-- /wp:paragraph -->
	
	<!-- wp:buttons -->
	<div class="wp-block-buttons"><!-- wp:button {"borderRadius":0,"backgroundColor":"black","textColor":"white","className":"is-style-outline"} -->
	<div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-white-color has-black-background-color has-text-color has-background no-border-radius">'. __("Visit") .'</a></div>
	<!-- /wp:button --></div>
	<!-- /wp:buttons --></div>
	<!-- /wp:column -->
	
	<!-- wp:column -->
	<div class="wp-block-column"></div>
	<!-- /wp:column --></div>
	<!-- /wp:columns --></div></div>
	<!-- /wp:cover -->',
	'description' => _x( 'Hero section with background image and text and button on top', 'Block pattern description' ),
);
