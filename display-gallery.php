<?php

/**
 * Display Galleria Press stylesheets
 */
function galleriapress_print_styles()
{
	wp_enqueue_style('galleriapress');
}

add_action('wp_print_styles', 'galleriapress_print_styles');


/**
 * Display Galleria Press scripts in footer
 * galleriapress_options will be outpouted as a JSON object to be used when initialising each galleria
 */
function galleriapress_print_scripts()
{
	global $galleriapress_galleries_options;

	wp_print_scripts('galleria');
	$image_sizes = galleriapress_image_sizes();


  //  print_r($galleriapress_galleries_options);
?>
<script type="text/javascript">

  var galleriapress_options = <?php echo json_encode($galleriapress_galleries_options); ?>

  jQuery(document).ready(function()
												 {
                             Galleriapress.init_galleries();
												 });
</script>
<?php

  wp_enqueue_script('galleriapress-display');
}

add_action('wp_footer', 'galleriapress_print_scripts');


/**
 * Customise content for gallery post type
 */
function galleriapress_the_content($content)
{
  global $post;

  if($post->post_type == 'gallery')
	{
		echo do_shortcode('[galleria]');
	}

	return $content;
}

add_filter('the_content', 'galleriapress_the_content');


/**
 * operate the shortcode
 *
 * @param $attr The shortcode attributes
 */
function galleriapress_shortcode($attr)
{
  global $post;
  global $galleriapress_galleries_options;

	extract(shortcode_atts(array('gid' => $post->ID), $attr));

  if($gid)
    $the_post = get_post($gid);
  else
    $the_post = $post;

  // if current post is a gallery get the items
  if($the_post->post_type == 'gallery')
  {
    $gallery_items = get_post_meta($the_post->ID, 'galleriapress_items', true);
    if(!$gallery_items)
      $gallery_items = array();
  }
  // otherwise get the attachments
  else
  {
    $attachments = get_posts(array('posts_per_page' => -1,
                                   'post_type' => 'attachment',
                                   'post_parent' => $the_post->ID,
                                   'orderby' => 'menu_order',
                                   'order' => 'ASC'));

    foreach($attachments as $att)
      $gallery_items[] = (object)array('id' => $att->ID, 'library' => 'wp_media', 'title' => $att->title);

  }

  $common_options = get_post_meta($gid, 'galleriapress_common', true);
  if(!$common_options)
  {
    $default_profile = galleriapress_get_default_profile();
    $common_options = get_post_meta($default_profile->ID, 'galleriapress_common', true);
  }

  $common_options = galleriapress_process_options($common_options);

	$libraries = galleriapress_libraries();

  ob_start();
  ?>
	<div class="galleria galleriapress" id="galleria-<?php echo $gid; ?>" data-gallery_id="<?php echo $gid; ?>">
  </div>
  <?php

  $output = ob_get_clean();

  $library_items = array();
  $items_index = array();
  $library_options = array();

	// set up array for each library
  foreach($libraries as $library_name => $library)
  {
    $library_items[$library_name] = array();
    $library_indices[$library_name] = array();
  }

  // classify items by library, record the original index for each item
  foreach($gallery_items as $index => $item)
  {
    $library_items[$item->library][] = $item;
    $library_indices[$item->library][] = $index;
  }

  // process items in each library
  foreach($library_items as $library_name => $items)
  {
    $data = $libraries[$library_name]->gallery_items($items);
    if(!$data)
      $data = array();

    $library_options = array_merge($library_options, $libraries[$library_name]->get_settings($the_post->ID));

    $items_data[$library_name] = $data;
  }

  // recreate processed array
  $final_items = array();

  foreach($items_data as $library => $items)
  {
    foreach($items as $index => $item)
    {
      $original_index = $library_indices[$library][$index];
      $final_items[$original_index] = $item;
    }
  }

  ksort($final_items);

	$galleriapress_galleries_options[$gid] = array_merge($common_options, $library_options);
  $galleriapress_galleries_options[$gid]['dataSource'] = $final_items;

  return $output;
}


/**
 * Add [galleria] shortcode
 */	
function galleriapress_add_shortcode()
{
	add_shortcode('galleria', 'galleriapress_shortcode');
}


add_action('wp_head', 'galleriapress_add_shortcode');


?>