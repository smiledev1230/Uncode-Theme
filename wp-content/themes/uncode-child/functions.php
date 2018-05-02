<?php
add_action('after_setup_theme', 'uncode_language_setup');
function uncode_language_setup()
{
	load_child_theme_textdomain('uncode', get_stylesheet_directory() . '/languages');
}

function theme_enqueue_styles()
{
	$production_mode = ot_get_option('_uncode_production');
	$resources_version = ($production_mode === 'on') ? null : rand();
	$parent_style = 'uncode-style';
	$child_style = array('uncode-custom-style');
	wp_enqueue_style($parent_style, get_template_directory_uri() . '/library/css/style.css', array(), $resources_version);
	wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', $child_style, $resources_version);
	// wp_enqueue_script('child-main-js', get_stylesheet_directory_uri() . '/assets/js/main.js', 'child-main-js', $resources_version);
	wp_enqueue_script('jquery-balloon', get_stylesheet_directory_uri().'/assets/js/main.js', array('jquery'), false, false);
}
add_action('wp_enqueue_scripts', 'theme_enqueue_styles');