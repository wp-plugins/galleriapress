<?php

/**
 * Plugin Name: GalleriaPress
 * Plugin URI: http://secondvariety.com
 * Description: Plugin for Galleria.js integration
 * Version: 0.8
 * Author: Erez Odier
 * Author URI: http://secondvariety.com
 * License: GPLv2
 */


/* define version of galleria press, make it global
	 so that it can be used during plugin activation */
global $galleriapress_version;
$galleriapress_version = '0.8';

/* define version of galleria plugin */
$galleria_version = '1.2.8';

/* options for each gallery, to be outputed as JSON data */
$galleriapress_galleries_options = array();

$galleriapress_urls = array('plugin' => plugins_url('', __FILE__),
                            'libraries' => plugins_url("/libraries", __FILE__) ,
                            'themes' => plugins_url("/galleria/themes", __FILE__),
                            'plugins' => plugins_url("/galleria/plugins", __FILE__));

/* include files */
require_once(dirname(__FILE__) . '/utilities.php');
require_once(dirname(__FILE__) . '/library.php');
require_once(dirname(__FILE__) . '/display-gallery.php');
require_once(dirname(__FILE__) . '/manage-gallery.php');

if(version_compare($wp_version, '3.5', '<'))
  require_once(dirname(__FILE__) . '/tinymce.php');



/**
 * Plugin activation. Set up the plugin default options
 */
function galleriapress_activate()
{
	galleriapress_check_options();
}

register_activation_hook(__FILE__, 'galleriapress_activate');


/**
 * Galleria Press default gallery options
 */
function galleriapress_default_options()
{
	global $galleriapress_version;

	return array('version' => $galleriapress_version);
}


/**
 * Check that options are set
 */
function galleriapress_check_options()
{
	if(get_option('galleriapress') === false)
		update_option('galleriapress', galleriapress_default_options());

	galleriapress_get_default_profile();
}


/**
 * Initialise galleriapress plugin
 */
function galleriapress_init()
{
  global $galleriapress_version;
	global $galleria_version;

	// check options
	if(is_admin())
	{
		galleriapress_check_options();
	}

	// register scripts
	wp_register_script('galleria', plugins_url('/galleria/galleria-1.2.8.min.js', __FILE__), array('jquery'), $galleria_version, true);
	wp_register_script('galleriapress-manage-gallery', plugins_url('/js/manage-gallery.js', __FILE__), array('jquery', 'jquery-ui-sortable'), $galleriapress_version, true);
  //	wp_register_script('galleriapress-wpgallery', plugins_url('/js/wpgallery.js', __FILE__), array('jquery'), $galleriapress_version, true);

	// register styles
	wp_register_style('galleriapress-manage-gallery', plugins_url('/css/manage-gallery.css', __FILE__), array(), $galleriapress_version);
	wp_register_style('galleriapress', plugins_url('/css/galleriapress.css', __FILE__), array(), $galleriapress_version);

	// register gallery post type
	register_post_type('gallery',
										 array('labels' => array('name' => 'Galleries',
																						 'singular_name' => 'Gallery',
																						 'add_new' => 'New Gallery',
																						 'all_items' => 'Galleries',
																						 'add_new_text' => 'New Gallery',
																						 'edit_item' => 'Edit Gallery',
																						 'new_item' => 'New Gallery',
																						 'view_item' => 'View Gallery',
																						 'search_item' => 'Search Galleries',
																						 'not_found' => 'Gallery not found',
																						 'not_found_in_trash' => 'Gallery not found in trash',
																						 'menu_name' => 'Galleries'),
                           'menu_icon' => plugins_url('/images/icon.png', __FILE__),
													 'public' => true,
													 'menu_position' => 30,
													 'hierarchical' => false,
													 'supports' => array('title',
																							 'author',
																							 'slug',
																							 'thumbnail'),
													 'register_meta_box_cb' => 'galleriapress_meta_boxes',
													 'has_archive' => true,
													 'rewrite' => array('slug' => 'galleries')));


	register_post_type('gallery_profile',
										 array('labels' => array('name' => 'Gallery Profiles',
																						 'singular_name' => 'Gallery Profile',
																						 'add_new' => 'New Gallery Profile',
																						 'all_items' => 'Gallery Profiles',
																						 'add_new_text' => 'New Gallery Profile',
																						 'edit_item' => 'Edit Gallery Profile',
																						 'new_item' => 'New Gallery Profile',
																						 'view_item' => 'View Gallery Profile',
																						 'search_item' => 'Search Gallery Profiles',
																						 'not_found' => 'Gallery Profile not found',
																						 'not_found_in_trash' => 'Gallery Profile not found in trash',
																						 'menu_name' => 'Gallery Profiles'),
													 'public' => false,
													 'show_ui' => true,
													 'show_in_menu' => 'edit.php?post_type=gallery',
													 'hierarchical' => false,
													 'supports' => array('title',
																							 'author',
																							 'slug',
																							 'thumbnail'),
													 'register_meta_box_cb' => 'galleriapress_meta_boxes'));
}
add_action('init', 'galleriapress_init');


/**
 * Load galleriapress libraries in the libraries/ folder
 */
function galleriapress_load_libraries()
{
  global $galleriapress_libraries;

  if(!empty($galleriapress_libraries))
    return $galleriapress_libraries;

	$libraries = glob(dirname(__FILE__) . "/libraries/*", GLOB_ONLYDIR | GLOB_MARK);

	foreach($libraries as $directory)
  {
    $files = glob($directory . "*.php");
    if(!empty($files))
    {
      $library_name = basename($directory);
      $plugin_file = $directory . $library_name . ".php";
      if(file_exists($plugin_file))
        require_once($plugin_file);
    }
  }

	// instantiate each library
	foreach(get_declared_classes() as $class_name)
	{
		if(is_subclass_of($class_name, 'GalleriaPress_Library'))
    {
			$library_object = new $class_name();
    }
	}
}

galleriapress_load_libraries();


/**
 * Admin initialisation
 */
function galleriapress_admin_init()
{
	register_setting('galleriapress', 'galleriapress_default_gallery');
}

add_action('admin_init', 'galleriapress_admin_init');


function galleriapress_ajax_library_form()
{
  $libraries = galleriapress_libraries();

  $library_action = $_POST['library_action'];
  $library_name = $_POST['library'];

  unset($_POST['library_action']);
  unset($_POST['library']);

  $library = $libraries[$library_name];
  $method_name = 'form_' . $library_action;

  if($library)
  {
    if(method_exists($library, $method_name))
    {
      $library->method_name($_POST);
    }
  }

  exit;
}

add_action('wp_ajax_galleriapress_library_form', 'galleriapress_ajax_library_form');

function galleriapress_ajax_library_path()
{
  $libraries = galleriapress_libraries();

  $library_name = $_POST['library'];
  $library = $libraries[$library_name];
  $post_id = $_POST['post_id'];
  $path = $_POST['path'];

  if($library)
  {
    $items = galleriapress_gallery_items($post_id);
    $library->library_items($items, $path);
  }

  exit;  
}

add_action('wp_ajax_galleriapress_library_path', 'galleriapress_ajax_library_path');

?>
