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
    wp_register_style('galleriapress-wp_media', plugins_url("wp-media.css", __FILE__), array('galleriapress-manage-gallery'));
  }

  public function admin_print_scripts()
  {
    global $post;

    if(in_array($_GET['post_type'], array('gallery', 'gallery_profile')) ||
       in_array($post->post_type, array('gallery', 'gallery_profile')))
      {
        wp_enqueue_script('galleriapress-wp_media');
        wp_enqueue_style('galleriapress-wp_media');
      }    
  }

  /**
   * return the library info
   */
  public function info() 
	{
		return array('name' => 'wp_media',
								 'title' => 'Media Library',
                 'icon' => plugins_url("wp_media-icon.png", __FILE__),
								 'galleriapress_version' => '0.8');
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


  public function library_items($gallery_items = array(), $path = "/")
  {
    global $wpdb;

    $path_elements = explode("/", $path);

    $this->display_toolbar($path);

    foreach($gallery_items as $item)
    {
      if($item->library == 'wp_media')
        $gallery_ids[] = $item->id;
    }

    $images_query_args = array('posts_per_page' => 20,
                               'post_type' => 'attachment',
                               'post_status' => 'inherit',
                               'orderby' => 'date',
                               'post__not_in' => $gallery_ids,
                               'order' => 'DESC');

    switch($path_elements[0])
    {
    case 'search':
      $images_query_args['s'] = $path_elements[1];
      break;

    case 'library':
    default:
      if(isset($path_elements[1]))
      {
        $year = substr($path_elements[1], 0, 4);
        $month = substr($path_elements[1], 4, 2);

        $images_query_args['year'] = $year;
        $images_query_args['monthnum'] = $month;
      }
    }

		$images_query = new WP_Query;
    $images = $images_query->query($images_query_args);

    ?>
		<ul class="clearfix grid scrollable">

			<?php
				foreach($images as $image):
					$image_src = wp_get_attachment_image_src($image->ID, 'thumbnail');
          $title = htmlspecialchars($image->post_title, ENT_QUOTES);
			?>

			<li class="ui-state-default item" data-itemid="<?php echo $image->ID; ?>" data-library="wp_media">
				<img src="<?php echo $image_src[0]; ?>" title="<?php echo $title; ?>"/>
			</li>

			<?php endforeach; ?>

		</ul>

    <?php
  }

  public function display_toolbar($path)
  {
    $path_elements = explode("/", $path);

    if($path_elements[0] == 'search')
      $search_value = $path_elements[1];
    else
      $month = $path_elements[1];
    

    ?>
    <div class="wp_media-toolbar">
      <a href="#" class="insert-media button add_media" title="Add Media">Add Media</a>

      <?php $this->months_dropdown($month); ?>

      <form class="wp_media-search">
        <input type="input" placeholder="Search Library" id="wp_media_query" <?php if($search_value): ?> value="<?php echo $search_value; ?>" <?php endif; ?> />
        <input type="submit" value="Search" class="library-path button-primary" data-library="wp_media" data-path="search/{wp_media_query}" />
      </form>

    </div>
    <?php
  }

  public function months_dropdown($selected)
  {
		global $wpdb, $wp_locale;

		$months = $wpdb->get_results( $wpdb->prepare( "
			SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
			FROM $wpdb->posts
			WHERE post_type = 'attachment'
			ORDER BY post_date DESC
		", $post_type));

		$month_count = count( $months );

		if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
			return;

?>
		<select id="wp_media-months" onchange="Galleriapress.load_library_path('wp_media', 'library/' + $(this).val())">
			<option<?php selected( $selected, 0 ); ?> value='0'><?php _e( 'Show all dates' ); ?></option>
<?php
		foreach ( $months as $arc_row ) {
			if ( 0 == $arc_row->year )
				continue;

			$month = zeroise( $arc_row->month, 2 );
			$year = $arc_row->year;

			printf( "<option %s value='%s'>%s</option>\n",
				selected( $selected, $year . $month, false ),
				esc_attr( $arc_row->year . $month ),
				/* translators: 1: month name, 2: 4-digit year */
				sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
			);
		}
?>
		</select>
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
