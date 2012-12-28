<?php

/**
 * Utility function to gather info about image sizes used in wordpress
 */	
function galleriapress_image_sizes()
{
  global $_wp_additional_image_sizes;

  $base_sizes = array('thumbnail', 'medium', 'large');
  $image_sizes = array();

  foreach($base_sizes as $size)
  {
    $image_sizes[$size] = array('width' => intval(get_option($size . "_size_w")),
                                'height' => intval(get_option($size . "_size_h")));
  }

  if(isset($_wp_additional_image_sizes))
    $image_sizes = array_merge($_wp_additional_image_sizes, $image_sizes);

  return $image_sizes;
}

/**
 * Get installed theme names and url paths. The $type param
 * can take the following values:
 *   - all (or any other value): all themes, core or uploaded. The default if no type specified
 *   - core: only the themes included with Galleria
 *   - upload: the uploaded theme
 *
 * @param $type Optional Which theme to return
 * @return array An array of the the theme urls indexed by theme name
 */
function galleriapress_get_themes($type = 'all')
{
  $gp_upload_path = galleriapress_theme_upload_path();

  $core_themes_paths = glob(dirname(__FILE__) . "/galleria/themes/*", GLOB_ONLYDIR);
  $uploaded_themes_paths = glob($gp_upload_path . "/*", GLOB_ONLYDIR);

  $core_themes = array();
  $uploaded_themes = array();

  if(!empty($core_themes_paths))
  {
    foreach($core_themes_paths as $path)
    {
      $theme_name = basename($path);
      $theme_file = glob($path . "/galleria.*.js");

      if(!empty($theme_file))
        $core_themes[$theme_name] = plugins_url('/galleria/themes/' . $theme_name . '/galleria.' . $theme_name . '.js');
    }
  }

  if(!empty($uploaded_themes_paths))
  {
    $gp_upload_url = galleriapress_theme_upload_url();

    foreach($uploaded_themes_paths as $path)
    {
      $theme_name = basename($path);
      $theme_file = glob($path . "/galleria.*.js");

      if(!empty($theme_file))
        $uploaded_themes[$theme_name] = $gp_upload_url . $theme_name . "/" . basename($theme_file[0]);
    }
  }

  if($type == 'core')
  {
    $themes = $core_themes;
  }
  elseif($type == 'upload')
  {
    $themes = $uploaded_themes;
  }
  else
  {
    $themes = array_merge($core_themes, $uploaded_themes);
  }

	return $themes;
}

/**
 * Get profiles list
 */
function galleriapress_get_profiles_list()
{
	global $galleriapress_profiles;

	if(empty($galleriapress_profiles))
		galleriapress_get_profiles();

	foreach($galleriapress_profiles as $profile)
		$profiles[$profile->ID] = $profile->post_title;

	return $profiles;
}

/**
 * Get all profiles
 */
function galleriapress_get_profiles()
{
	global $galleriapress_profiles;

	if(empty($galleriapress_profiles))
		$galleriapress_profiles = get_posts(array('post_type' => 'gallery_profile',
																							'posts_per_page' => -1));

	return $galleriapress_profiles;
}

/**
 * Get the settings of a single profile
 *
 * @param int $id The post id
 * @return array An empty array if nothing found or an array with all the profile settings
 */
function galleriapress_get_profile_settings($id)
{
	global $galleriapress_profiles;

	return get_post_meta($id, 'galleriapress_gallery', true);
}


function galleriapress_process_options($gallery_options)
{
	// use custom size or one of the image sizes
	if($gallery_options['gallery_size'] == 'custom')
	{
		$gallery_options['width'] = (int)$gallery_options['custom_gallery_size_w'];
		$gallery_options['height'] = (int)$gallery_options['custom_gallery_size_h'];
	}
	else
	{
		$image_sizes = galleriapress_image_sizes();
		$size = $gallery_options['gallery_size'];

		$gallery_options['width'] = $image_sizes[$size]['width'];
		$gallery_options['height'] = $image_sizes[$size]['height'];
	}		

	return $gallery_options;
}


function galleriapress_get_default_profile()
{
	$default_profile = get_posts(array('post_type' => 'gallery_profile',
																		 'posts_per_page' => -1,
																		 'meta_key' => 'default_gallery_profile',
																		 'meta_value' => true,
																		 'meta_compare' => '='));

	if(!empty($default_profile))
		return $default_profile[0];

	return galleriapress_create_default_profile();

	return false;
}

function galleriapress_create_default_profile($options = array())
{
	if(empty($options))
		$options = galleriapress_get_default_profile_options();

	$new_profile_id = wp_insert_post(array('post_type' => 'gallery_profile',
																				 'post_name' => 'default',
																				 'post_status' => 'publish',
																				 'post_title' => 'Default Profile'));

	update_post_meta($new_profile_id, 'default_gallery_profile', true);
	update_post_meta($new_profile_id, 'galleriapress_common', $options);
}


function galleriapress_get_default_profile_options()
{
	return array('orderby' => 'menu_order',
							 'order' => 'ASC',
							 'use_featured_image' => false,
							 'captionOpen' => true,
							 'captionPosition' => 'top-left',
							 'size' => 'custom',
							 'gallery_size' => 'medium',
							 'custom_gallery_size_w' => false,
							 'custom_gallery_size_h' => false,
							 'custom_size_w' => 600,
							 'custom_size_h' => 400,
							 'theme' => 'classic');
}

function galleriapress_theme_upload_path()
{
  $wp_upload_dir = wp_upload_dir();

  try
  {
    $dirs_to_check = array('/galleria/', '/galleria/themes/');

    foreach($dirs_to_check as $dir)
    {
      if(!is_dir($wp_upload_dir['basedir'] . $dir))
        if(!mkdir($wp_upload_dir['basedir'] . $dir))
        {
          throw new Exception('Could not create ' . $wp_upload_dir . $dir);
        }
    }

    $upload_path = $wp_upload_dir['basedir'] . "/galleria/themes/";
  }
  catch(Exception $e)
  {
    return false;
  }

  return $upload_path;
}

function galleriapress_theme_upload_url()
{
  $wp_upload_dir = wp_upload_dir();
  $upload_url = $wp_upload_dir['baseurl'] . "/galleria/themes/";

  return $upload_url;
}


?>