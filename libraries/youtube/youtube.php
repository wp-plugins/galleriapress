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
								 'galleriapress_version' => '0.8');
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

	public function library_items($gallery_items, $path)
  {
    $this->display_toolbar($path);

    $path_elements = explode("/", $path);
    switch($path_elements[0])
    {
    case 'search':
    default:
      return $this->search($path_elements[1]);
      break;
    }
  }

  public function display_toolbar($path)
  {
    ob_start();

    $path_elements = explode("/", $path);

    switch($path_elements[0])
    {
    case 'search':
    default:
      if(isset($path_elements[1]))
        $search_value=$path_elements[1];
    ?>
      <form class="youtube-search">
        <input type="text" id="youtube_query" placeholder="Search Youtube" value="<?php echo $path_elements[1]; ?>" />
        <input type="submit" value="Search" class="button-primary library-path" data-path="search/{youtube_query}" data-library="youtube"  />
      </form>
    <?php
    break;
    }

    $output = ob_get_clean();
    ?>

    <div class="youtube-toolbar">
      <?php echo $output; ?>
    </div>

    <?php
  }

  public function search($search_term = '')
  {
    $url = 'http://gdata.youtube.com/feeds/api/videos?q=' . urlencode($search_term) . '&v=2';

    $feed_xml = file_get_contents($url);
    if($feed_xml):
      $feed = new SimpleXMLElement($feed_xml);
    ?>
		<ul class="clearfix grid connected-sortable">
			<?php
				foreach($feed->entry as $entry):

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

}