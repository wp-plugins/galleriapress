<?php

class GalleriaPress_WP_Media extends GalleriaPress_Library
{
  /**
   * Constructor
   */
  public function __construct() 
	{
		parent::__construct(1);

    add_action('init', array(&$this, 'init'));
    add_action('wp_ajax_galleriapress_wp_media_library_items', array(&$this, 'ajax_library_items'));
    add_action('admin_print_scripts-post.php', array(&$this, 'admin_print_scripts'));
    add_action('admin_print_scripts-post-new.php', array(&$this, 'admin_print_scripts'));

	}

  public function init()
  {
    wp_register_script('galleriapress-wp_media', plugins_url("wp-media.js", __FILE__), array('galleriapress-manage-gallery'));
  }

  public function admin_print_scripts()
  {
    global $post;

    if(in_array($_GET['post_type'], array('gallery', 'gallery_profile')) ||
       in_array($post->post_type, array('gallery', 'gallery_profile')))
      {
        wp_enqueue_script('galleriapress-wp_media');
      }    
  }

  /**
   * return the library info
   */
  public static function info() 
	{
		return array('name' => 'wp_media',
								 'title' => 'Media Library',
                 'icon' => plugins_url("wp_media-icon.png", __FILE__),
								 'galleriapress_version' => '0.7.4');
	}

	/**
	 * return library items for an ajax call
	 */
  public function ajax_library_items()
  {
    $page = (int)$_POST['page'];

    ob_start();
    $this->library_items(array('page' => $page));
    $output = ob_get_clean();

    echo json_encode($output);
    exit;
  }

  /**
   * Display the library items
   */
  public function library_items($options = array())
	{ 
		global $post;

		$options = array_merge(array('page' => 1), $options);

		// get all images in library
		$images_query = new WP_Query;
    $images = $images_query->query(array('posts_per_page' => 20,
                                         'post_type' => 'attachment',
                                         'post_status' => 'inherit',
                                         'orderby' => 'date',
                                         'paged' => $options['page'],
                                         'order' => 'ASC'));

		?>
    <div class="tablenav">

      <div class="media-buttons"><?php do_action('media_buttons'); ?></div>

      <div class="page-nav">
        <?php if($page > 1): ?>
        <a class="first-page" title="Go to the first page" data-page="1">&laquo;</a>
        <a class="prev-page" title="Go to the previous page" data-page="<?php echo $page - 1; ?>">&lsaquo;</a>
        <?php
        endif;

        if($images_query->max_num_pages > 1)
          echo $page;

        if($page < $images_query->max_num_pages):
        ?>
        <a class="next-page" title="Go to the next page" data-page="<?php echo $page + 1; ?>">&rsaquo;</a>
        <a class="last-page" title="Go to the last page" data-page="<?php echo $images_query->max_num_pages; ?>">&raquo;</a>
        <?php endif; ?>
      </div>

    </div>

		<ul class="clearfix grid">

			<?php
				foreach($images as $image):
					$image_src = wp_get_attachment_image_src($image->ID, 'thumbnail');
          $title = htmlspecialchars($image->post_title, ENT_QUOTES);
			?>

			<li class="ui-state-default" data-itemid="<?php echo $image->ID; ?>" data-library="wp_media">
				<img src="<?php echo $image_src[0]; ?>" title="<?php echo $title; ?>"/>
			</li>

			<?php endforeach; ?>

		</ul>

		<script type="text/javascript">
			jQuery(document).ready(function()
														 {
																 Galleriapress.wp_media.init();
														 });
		</script>

	<?php
	}

  public function gallery_items($items)
  {
		$options = get_post_meta($post_id, 'galleriapress_wp_media', true);
    if(!$options)
      $options = array();

    extract($options);

    $data = array();

    foreach($items as $item)
    {
      $image_id = $item->id;
      $item_data['id'] = $image_id;

      $image_src = wp_get_attachment_image_src($image_id, $size);
			$thumb_src = wp_get_attachment_image_src($image_id, 'thumbnail');

      $item_data['image'] = $image_src[0];
			$item_data['thumb'] = $thumb_src[0];

			$image = get_post($image_id);

			// use post_excerpt for caption, but if empty use post title
      $item_data['title'] = htmlspecialchars($image->post_excerpt ? $image->post_excerpt : $image->post_title, ENT_QUOTES);

      // get description either from post content or image alt
			$description = htmlspecialchars($image->post_content, ENT_QUOTES);
      if(!$description)
        $description = htmlspecialchars(get_post_meta($image_id, '_wp_attachment_image_alt', true));
      $item_data['description'] = $description;

      $data[] = (object)$item_data;
    }

		wp_reset_postdata();

    return $data;
  }

  public function get_settings($post_id)
  {
    $options = get_post_meta($post_id, 'galleriapress_wp_media', true);
    if($options)
      return array_intersect_key($options, array('captionPosition', 'captionOpen'));

    return array();
  }

  /**
   * Display the library settings
   */
  public function settings_form() 
	{ 
		global $post;

		$options = get_post_meta($post->ID, 'galleriapress_wp_media', true);

    if(!$options)
      $options = array();

    extract($options);

		$image_sizes = galleriapress_image_sizes();

	?>
		<table class="form-table">
			<tr class="size">
				<th><label for="size">Image Size</label></th>
				<td>
					<select id="size" name="size">
						<?php foreach($image_sizes as $name => $image_size): ?>
						<option value="<?php echo $name; ?>" <?php echo selected($name, $size); ?>>
							<?php echo $name . " (" . $image_size['width'] . " x " . $image_size['height'] . ")"; ?>
						</option>
						<?php endforeach; ?>
						<option value="custom"  <?php echo selected('custom', $size); ?>>Custom</option>
					</select>
				</td>
			</tr>
			<tr class="custom-size">
				<th>
					<label for="custome_size_w">Custom Dimensions</label>
				</th>
				<td>
					<input type="text" name="custom_size_w" value="<?php echo $custom_size_w; ?>" /><span>px</span>
					<span>x</span>
					<input type="text" name="custom_size_h" value="<?php echo $custom_size_h; ?>" /><span>px</span>
				</td>
			</tr>
		</table>

	<?php
	}

  /**
   * Save the library settings
   */
  public function save_settings()
	{
		global $post;

		// save images
		$options = get_post_meta($post->ID, 'galleriapress_wp_media', true);
		if(!$options)
      $options = array();

		$option_names = array('orderby',
													'order',
													'size',
													'custom_size_w',
													'custom_size_h');

		foreach($option_names as $op)
			$options[$op] = $_POST[$op];

		update_post_meta($post->ID, 'galleriapress_wp_media', $options);
	}
}

?>