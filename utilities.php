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
 * Get installed theme names and url paths
 */
function galleriapress_get_themes()
{
	$theme_dirs = glob(dirname(__FILE__) . '/galleria/themes/*', GLOB_ONLYDIR);
	$themes = array();

	foreach($theme_dirs as $theme)
	{
		$theme_name = basename($theme);
		if(file_exists($theme . '/galleria.' . $theme_name . '.js'))
			$themes[$theme_name] = plugins_url('/galleria/themes/' . $theme_name . '/galleria.' . $theme_name . '.js');
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

?>