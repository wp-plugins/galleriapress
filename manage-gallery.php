<?php

/**
 * Gallery management functions
 */


/**
 * Enqueue needed scripts 
 */
function galleriapress_admin_gallery_enqueue_scripts()
{
  global $post;

  if(in_array($_GET['post_type'], array('gallery', 'gallery_profile')) ||
     in_array($post->post_type, array('gallery', 'gallery_profile')))
  {
    wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-droppable');
    wp_enqueue_script('jquery-ui-resizable');
    wp_enqueue_script('galleriapress-manage-gallery');
  }
}

add_action('admin_print_scripts-post.php', 'galleriapress_admin_gallery_enqueue_scripts');
add_action('admin_print_scripts-post-new.php', 'galleriapress_admin_gallery_enqueue_scripts');

/**
 * Enqueue needed styles for admin, only for gallery and gallery_profile post types
 *
 * @param $hook The admin page hook
 */
function galleriapress_admin_gallery_enqueue_styles($hook)
{
  global $post;

  if(in_array($_GET['post_type'], array('gallery', 'gallery_profile')) ||
     in_array($post->post_type, array('gallery', 'gallery_profile')))
  {
    wp_enqueue_style('galleriapress-manage-gallery');
  }
}

add_action('admin_print_styles-post.php', 'galleriapress_admin_gallery_enqueue_styles');
add_action('admin_print_styles-post-new.php', 'galleriapress_admin_gallery_enqueue_styles');


/**
 * Add the gallery and gallery_profile post types meta boxes
 */
function galleriapress_meta_boxes()
{
  global $post;

  $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : $post->post_type;

  // gallery box
  if($post_type == 'gallery')
    add_meta_box('gallery-box', 'Gallery', 'galleriapress_gallery_box', 'gallery', 'normal', 'high');

  // add common metaboxes
  add_meta_box('gallery-common-settings', 'Common Settings', 'galleriapress_common_settings_box', $post_type, 'normal', 'high');

  // add the gallery default profile box, or the apply profile box
  if($post_type == 'gallery_profile')
    add_meta_box('gallery-default-profile', 'Default Profile', 'galleriapress_default_profile', $post_type, 'side', 'default');
  else
    add_meta_box('gallery-profiles', 'Choose Profile', 'galleriapress_profiles_box', $post_type, 'side', 'default');
}

/**
 * Display meta box containing libraries and images box
 */
function galleriapress_gallery_box()
{
	global $post;

	$libraries = galleriapress_libraries();

  $items = galleriapress_gallery_items($post->ID);
?>
  <div class="libraries-menu"><a data-show="items" class="current">Items</a> | <a data-show="settings">Settings</a></div>

	<ul class="clearfix libraries-tabs">

		<?php foreach($libraries as $key => $library): ?>
    <li>
      <a href="#" data-library="<?php echo $key; ?>" class="<?php echo $key; ?>">
        <?php if($library->icon): ?>
        <img src="<?php echo $library->icon; ?>" title="<?php echo $library->title; ?>" />
        <?php else: ?>
        <span class="title"><?php echo $library->title; ?></span>
        <?php endif; ?>
      </a>
    </li>
		<?php endforeach; ?>

	</ul>

	<div id="galleriapress-libraries">
		<?php foreach($libraries as $key => $library): ?>

		<div class="library" id="<?php echo $key; ?>-library">
			<?php $library->library_items($items, "/"); ?>
		</div><!-- .library -->

    <div id="<?php echo $key; ?>-settings" class="library-settings">
      <?php $library->settings_form(); ?>
    </div><!-- .library-settings -->

		<?php endforeach; ?>

    <div class="loading">
      <div class="spinner"></div>
    </div>

	</div><!-- #galleriapress-libraries -->

  <a class="button-secondary clear-library-selected">Clear</a>
  <a class="button-primary add-to-gallery">Add to Gallery</a>

  <div class="item-info postbox">
    <div class="handlediv" title="Click to toggle"><br></div>
    <h3 class="hndle">Item Info</h3>
    <div class="info-panel inside">
      <p><strong>Title</strong> <span class="title"></span></p>
      <p><strong>Library</strong> <span class="library"></span></p>
    </div><!-- .info-panel -->
  </div><!-- .item-info -->

	<div class="clearfix galleriapress-items-container">

		<ul id="galleriapress-items" class="connected-sortable grid">

			<?php foreach($items as $item): ?>
      <li data-itemid="<?php echo $item->id; ?>" data-library="<?php echo $item->library; ?>" class="<?php echo $item->library; ?> item">
        <img src="<?php echo $item->thumb; ?>" <?php if($item->title): ?>title="<?php echo $item->title; ?>" <?php endif; ?>/>
        <span class="delete"></span>
      </li>
			<?php endforeach; ?>
		</ul><!-- #galleriapress-items -->

	</div><!-- .galleriapress-items-container -->

  <a class="button-secondary clear-gallery-selected">Clear</a>
  <a class="button-secondary remove-gallery-selected">Remove</a>
  <a class="remove-all button-primary" href="#">Remove All</a>

  <input type="hidden" value="<?php echo htmlspecialchars(json_encode($items)); ?>" name="galleriapress_items_data" id="galleriapress_items_data" />

<?php
}

/**
 * Settings meta box
 */
function galleriapress_gallery_settings_box()
{
  global $post;

	$libraries = galleriapress_libraries();

?>
  <div id="galleriapress-libraries-settings">

   <?php foreach($libraries as $key => $library): ?>

    <div id="<?php echo $key; ?>-settings" class="library-settings">
      <?php $library->settings_form(); ?>
    </div><!-- .library-settings -->

    <?php endforeach; ?>

  </div><!-- .libraries-settings -->
<?php
}

/**
 * Display the common settings
 */
function galleriapress_common_settings_box()
{
  global $post;

	$image_sizes = galleriapress_image_sizes();
  $themes = galleriapress_get_themes();

  $common_options = get_post_meta($post->ID, 'galleriapress_common', true);

  extract($common_options);

  $transitions = array('fade', 'flash', 'pulse', 'slide', 'fadeslide');
  if(!$transition) $transition = 'slide';

?>
<input type="hidden" name="galleriapress_noncename" id="galleriapress_noncename" value="<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>" />

<table class="form-table">
	<tr class="gallery-size">
		<th><label for="gallery_size">Gallery Size</label></th>
		<td>
			<select id="gallery_size" name="gallery_size">
				<?php foreach($image_sizes as $name => $image_size): ?>
				<option value="<?php echo $name; ?>" <?php echo selected($name, $gallery_size); ?>>
					<?php echo $name . " (" . $image_size['width'] . " x " . $image_size['height'] . ")"; ?>
				</option>
				<?php endforeach; ?>
				<option value="custom" <?php echo selected('custom', $gallery_size); ?>>Custom</option>
			</select>
		</td>
	</tr>

	<tr class="custom-size">
		<th>
			<label>Custom Dimensions</label>
		</th>
		<td>
			<input type="text" name="custom_gallery_size_w" value="<?php echo $custom_gallery_size_w; ?>" />
      <select name="custom_gallery_size_w_unit">
        <option value="px" <?php echo selected($custom_gallery_size_w_unit, 'px'); ?>>px</option>
        <option value="%" <?php echo selected($custom_gallery_size_w_unit, '%'); ?>>%</option>
      </select>
			<span>by</span>
			<input type="text" name="custom_gallery_size_h" value="<?php echo $custom_gallery_size_h; ?>" />
      <select name="custom_gallery_size_h_unit">
        <option value="px" <?php echo selected($custom_gallery_size_h_unit, 'px'); ?>>px</option>
        <option value="%" <?php echo selected($custom_gallery_size_h_unit, '%'); ?>>%</option>
      </select>
		</td>
	</tr>

  <tr>
    <th><label for="autoplay">Autoplay<label></th>
    <td>
      <input type="text" name="autoplay" value="<?php echo $autoplay; ?>" />
      <span>(ms) Leave blank to disable</span>
    </td>
  </tr>

  <tr>
    <th><label for="carousel">Carousel</label></th>
    <td><input type="checkbox" name="carousel" value="1" <?php echo checked($carousel, 1); ?> /></td>
  </tr>

  <tr>
    <th><label for="transition">Transition</label></th>
    <td>
      <select name="transition">
        <?php foreach($transitions as $t): ?>
        <option value="<?php echo $t; ?>" <?php echo selected($transition, $t); ?>><?php echo $t; ?></option>
        <?php endforeach; ?>
      </select>
    </td>
  </select>

	<tr>
		<th><label for="captionOpen">Caption Open</label></th>
		<td><input type="checkbox" name="captionOpen" value="1" <?php echo checked($captionOpen, 1); ?> /></td>
	</tr>
  <tr>
		<th><label for="captionPosition">Caption Position</label></th>
		<td>
			<select name="captionPosition">
				<option value="top-left" <?php echo selected($captionPosition, 'top-left'); ?>>Top Left</option>
				<option value="top-right" <?php echo selected($captionPosition, 'top-right'); ?>>Top Right</option>
				<option value="bottom-left" <?php echo selected($captionPosition, 'bottom-left'); ?>>Bottom Left</option>
				<option value="bottom-right" <?php echo selected($captionPosition, 'bottom-right'); ?>>Bottom Right</option>
			</select>	
		</td>
	</tr>

	<tr>
    <th><label for="theme">Theme</label></th>
		<td class="themes">
      <select name="theme">
			<?php foreach($themes as $theme_name => $url): ?>
      <option <?php echo selected($theme, $url); ?> value="<?php echo $url; ?>"><?php echo $theme_name; ?></option>
			<?php endforeach; ?>
      </select>
		</td>
	</tr>
</table>

<input type="hidden" name="galleriapress_noncename" id="galleriapress_noncename" value="<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>" />

<?php
}

/**
 * Display profiles selection meta box
 */
function galleriapress_profiles_box()
{
	global $post;
  $profiles = galleriapress_get_profiles_list();

  $profiles = array('0' => 'None') + $profiles;

  $current_profile = get_post_meta(get_the_id(), 'galleriapress_profile', true);
  $link_profile = get_post_meta(get_the_id(), 'galleriapress_link_profile', true);

  ?>
  <div id="profiles-box">

    <p>
      <label for="choose_profile">Choose a gallery profile</label>
      <select name="choose_profile" class="choose-profile">
        <?php foreach($profiles as $profile_key => $profile_name): ?>
        <option value="<?php echo $profile_key; ?>" <?php selected($current_profile, $profile_key); ?>><?php echo $profile_name; ?></option>
        <?php endforeach; ?>
      </select>
    </p>

    <p>
      <input type="checkbox" name="link_profile" <?php echo checked($link_profile, 1); ?> value="1" />
      <label for="link_profile">Link Profile</label>
    </p>

  </div>

  <?php
}


/**
 * This function filters the get_media_item_args hook so that an image
 * can be inserted into a non existent editor
 */
function galleriapress_get_media_item_args($args)
{
  $args['send'] = true;

  return $args;
}

add_filter('get_media_item_args', 'galleriapress_get_media_item_args');


/**
 * Save the gallery data
 */
function galleriapress_save_gallery($post_id)
{
  global $post;

  if(!wp_verify_nonce($_POST['galleriapress_noncename'], plugin_basename(__FILE__)))
    return;

  if(!current_user_can('edit_post', $post->ID))
    return;

  if(($post->post_type != 'gallery') && ($post->post_type != 'gallery_profile'))
    return;

  $new_profile = (int)$_POST['choose_profile'];
  $new_link_profile = (int)$_POST['link_profile'];

  // general options
  $option_names = array('captionOpen',
                        'captionPosition',
                        'gallery_size',
                        'custom_gallery_size_w',
                        'custom_gallery_size_w_unit',
                        'custom_gallery_size_h',
                        'custom_gallery_size_h_unit',
                        'autoplay',
                        'carousel',
                        'transition',
                        'theme');
  
  foreach($option_names as $op)
    $new_options[$op] = $_POST[$op];

  update_post_meta($post->ID, 'galleriapress_profile', $new_profile);
  update_post_meta($post->ID, 'galleriapress_link_profile', $new_link_profile);
  update_post_meta($post->ID, 'galleriapress_common', $new_options);

  $items = json_decode(stripcslashes($_POST['galleriapress_items_data']));
  update_post_meta($post->ID, 'galleriapress_items', $items);

	$libraries = galleriapress_libraries();

  foreach($libraries as $library)
    $library->save_settings();
}

add_action('save_post', 'galleriapress_save_gallery', 1);


/**
 * Meta box to make profile the default
 *
 * @parma $post The current post
 */
function galleriapress_default_profile($post)
{
  $is_default = get_post_meta($post->ID, 'default_gallery_profile', true);

  if($is_default):
  ?>

  <p>This profile is currently the default profile for galleries</p>

  <?php else: ?>

  <label for="default_gallery_profile">Make this profile the default gallery profile</label>
  <input type="checkbox" name="default_gallery_profile" />

  <?php
  endif;
}

/**
 * Save the profile. Find out if any galleries are linked and update their common ooptions
 */

function galleriapress_save_profile($post_id)
{
  global $post;

  if(!wp_verify_nonce($_POST['galleriapress_noncename'], plugin_basename(__FILE__)))
    return $post_id;

  if(!current_user_can('edit_post', $post_id))
    return;

  if($post->post_type != 'gallery_profile')
    return;

  // find out linked galleries
  $linked_galleries = get_posts(array('post_type' => 'gallery',
                                      'fields' => 'ids',
                                      'meta_query' => array(array('key' => 'galleriapress_link_profile',
                                                                  'value' => 1,
                                                                  'compare' => '='),
                                                            array('key' => 'galleriapress_profile',
                                                                  'value' => $post_id,
                                                                  'compare' => '='))));



  // update the linked galleries options
  $common_options = get_post_meta($post_id, 'galleriapress_common', true);

  foreach($linked_galleries as $gallery_id)
  {
    update_post_meta($gallery_id, 'galleriapress_common', $common_options);
  }

  if($_POST['default_gallery_profile'] == 'on')
  {
    $default_profile = galleriapress_get_default_profile();

    // remove default_gallery_profile setting from the current default profile
    if($default_profile)
      update_post_meta($default_profile->ID, 'default_gallery_profile', false);

    // assign it to this one
    update_post_meta($post_id, 'default_gallery_profile', true);
  }
}

add_action('save_post', 'galleriapress_save_profile', 30);


/**
 * override for gallery settings in non gallery posts
 */
function galleriapress_wordpress_gallery_options()
{
  global $post;

  ?>
  <div class="title"><?php _e('Gallery Settings'); ?></div>
  <?php
  $galleries = get_posts(array('post_type' => 'gallery',
                               'post_status' => 'publish',
                               'posts_per_page' => -1));

  ?>
  <table class="describe">
    <tr>
      <th class="label" scope="row">
        <label for="gid">Gallery</label>
      </th>
      <td class="field">
        <select name="gid">
          <option value="0">Post Gallery</option>
          <?php
            foreach($galleries as $post):
              setup_postdata($post);
          ?>
          <option value="<?php the_id(); ?>"><?php the_title(); ?></option>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>
  </table>

  <input type="button" value="Update gallery settings" id="update-gallery" name="update-gallery" style="" class="button"> 

  <?php
  exit;
}

add_action('wp_ajax_galleriapress_wordpress_gallery_options', 'galleriapress_wordpress_gallery_options');


/**
 * Get all the registered libraries
 *
 * @return Array of library info items
 */
function galleriapress_libraries()
{
	global $galleriapress_libraries;

	if(!$galleriapress_libraries)
		$galleriapress_libraries = apply_filters('galleriapress_libraries', array());

	return $galleriapress_libraries;
}


/**
 * Get gallery items
 *
 * @param int $post_id ID of the gallery
 * @return array An array of gallery items
 */
function galleriapress_gallery_items($post_id)
{
  $items = get_post_meta($post_id, 'galleriapress_items', true);
  if(!$items)
    $items = array();

  return $items;
}

 
?>
