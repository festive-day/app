<?php
global $post;
$meta = get_post_custom( $post->ID );

// MAIN
$options_page_title 				= isset( $meta['options_page_title'] ) ? $meta['options_page_title'][0] : '';
$options_page_menu_title 			= isset( $meta['options_page_menu_title'] ) ? $meta['options_page_menu_title'][0] : '';
$options_page_menu_slug 			= isset( $meta['options_page_menu_slug'] ) ? $meta['options_page_menu_slug'][0] : '';
$options_page_menu_icon 			= isset( $meta['options_page_menu_icon'] ) ? $meta['options_page_menu_icon'][0] : 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgdmlld0JveD0iMCAwIDIwIDIwIj48cGF0aCBmaWxsPSJjdXJyZW50Q29sb3IiIGQ9Ik0yLjU4NiAxMS40MTRhMiAyIDAgMCAxIDAtMi44MjhsNi4wMDItNmEyIDIgMCAwIDEgMi44MjggMGw2LjAwMiA2YTIgMiAwIDAgMSAwIDIuODI4bC02LjAwMiA2YTIgMiAwIDAgMS0yLjgyOCAwbC02LjAwMi02WiI+PC9wYXRoPjwvc3ZnPg==';
$options_page_parent_menu 			= isset( $meta['options_page_parent_menu'] ) ? $meta['options_page_parent_menu'][0] : 'none';
$options_page_capability 			= isset( $meta['options_page_capability'] ) ? $meta['options_page_capability'][0] : 'manage_options';
$options_page_capability_custom 	= isset( $meta['options_page_capability_custom'] ) ? $meta['options_page_capability_custom'][0] : '';