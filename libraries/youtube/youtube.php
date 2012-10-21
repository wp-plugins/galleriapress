<?php

class GalleriaPress_Youtube extends GalleriaPress_Library
{
	public function __construct()
	{
		parent::__construct();

    add_action('init', array(&$this, 'init'));
    add_action('admin_print_scripts-post.php', array(&$this, 'admin_print_scripts'));
    add_action('admin_print_scripts-post-new.php', array(&$this, 'admin_print_scripts'));
	}

	public function info()
	{
		return array('name' => 'youtube',
								 'title' => 'Youtube',
                 'icon' => plugins_url('youtube-icon.png', __FILE__),
								 'galleriapress_version' => '0.7.5');
	}

  public function init()
  {
    wp_register_script('galleriapress-youtube', plugins_url("youtube.js", __FILE__), array('galleriapress-manage-gallery'));
    wp_register_style('galleriapress-youtube', plugins_url("youtube.css", __FILE__), array('galleriapress-manage-gallery'));
  }

	public function admin_print_scripts()
	{
    global $post;

		if(in_array($_GET['post_type'], array('gallery', 'gallery_profile')) ||
			 in_array($post->post_type, array('gallery', 'gallery_profile')))
			{
				wp_enqueue_script('galleriapress-youtube');
				wp_enqueue_style('galleriapress-youtube');
			}
	}

	public function library_items($gallery_items)
	{
		global $post;

		$options = get_post_meta($post->ID, 'galleriapress_youtube', true);
		if(!$options)
			$options = array('options' => array());

		extract($options['options']);

    $gallery_ids = array();
    foreach($gallery_items as $item)
    {
      if($item->library == 'picasa')
        $gallery_ids[] = $item->id;
    }
    
		if(!$youtube_username):
		?>
			<p>Please enter a Youtube username</p>
		<?php
		else:
			$url = 'http://gdata.youtube.com/feeds/api/users/' . $youtube_username . '/uploads?v=2';

			$feed_xml = file_get_contents($url);
			if($feed_xml):
				$feed = new SimpleXMLElement($feed_xml);

		?>
		<ul class="clearfix grid connected-sortable">
			<?php
				foreach($feed->entry as $entry):

          if(in_array($entry->id, $gallery_ids))
            continue;

					$ns = $entry->getDocNamespaces();
					$group = $entry->children($ns['media'])->group;
					$video_id = $group->children($ns['yt'])->videoid;
					$attr = $group->thumbnail[0]->attributes();
          $video_url = "http://www.youtube.com/watch?v=" . $video_id;
			?>
				<li data-itemid="<?php echo $video_id; ?>" data-library="youtube">
					<img src="<?php echo $attr['url']; ?>" style="width:<?php echo $attr['width']; ?>px; height: <?php echo $attr['height']; ?>px" title="<?php echo $entry->title;?>" data-video="<?php echo $video_url; ?>" />
				</li>
			<?php endforeach; ?>
		</ul>

		<?php
			endif;

		endif;
	}
	

	public function gallery_items($items)
	{
    $data = array();
    foreach($items as $item)
    {
      if($item->video)
        continue;

      $item->video = "http://www.youtube.com/watch?v=" . $item->id;
    }

    return $items;
	}

  public function get_settings($post_id)
  {
    return array();
    //    return get_post_meta($post_id, 'galleriapress_wp_media', true);
  }


	function settings_form()
	{
		global $post;

		$options = get_post_meta($post->ID, 'galleriapress_youtube', true);

		if(!$options)
			$options = array();

		extract($options['options']);

	?>
		<table class="form-table">
      <tr>
        <td>
          <label for="youtube_username">Youtube Username</label>
        </td>
        <td>
          <input type="text" name="youtube_username" value="<?php echo $youtube_username; ?>" />
        </td>
      </tr>
    </table>
	<?php
	}

	public function save_settings()
	{
		global $post;

		$videos = $_POST['videos'];

		$options = array('videos' => $videos,
										 'options' => array());

		$options['options']['youtube_username'] = $_POST['youtube_username'];

		update_post_meta($post->ID, 'galleriapress_youtube', $options);
	}
}
