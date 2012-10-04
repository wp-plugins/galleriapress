<?php

class GalleriaPress_Picasa extends GalleriaPress_Library
{
	public function __construct()
	{
		parent::__construct();

		add_action('init', array(&$this, 'init'));
    add_action('admin_print_scripts-post.php', array(&$this, 'admin_print_scripts'));
    add_action('admin_print_scripts-post-new.php', array(&$this, 'admin_print_scripts'));

    add_action('wp_ajax_picasa_library_items', array(&$this, 'ajax_library_items'));
	}

	public function info()
	{
		return array('name' => 'picasa',
								 'title' => 'Picasa',
                 'icon' => plugins_url("picasa-icon.png", __FILE__),
								 'galleriapress_version' => '0.7.4');
	}

	public function init()
	{
    wp_register_script('galleriapress-picasa', plugins_url("picasa.js", __FILE__), array('galleriapress-manage-gallery'));
    wp_register_style('galleriapress-picasa', plugins_url("picasa.css", __FILE__), array('galleriapress-manage-gallery'));
	}

	public function admin_print_scripts()
	{
    global $post;

		if(in_array($_GET['post_type'], array('gallery', 'gallery_profile')) ||
			 in_array($post->post_type, array('gallery', 'gallery_profile')))
			{
				wp_enqueue_script('galleriapress-picasa');
        wp_enqueue_style('galleriapress-picasa');
			}
	}

	public function library_items()
	{
		global $post;

		$options = get_post_meta($post->ID, 'galleriapress_picasa', true);
		extract($options);

    if($picasa_username && $picasa_username != ''):
      $this->display_all_albums($post->ID);
    else:
    ?>
		<p>Please enter a username</p>
		<?php
		endif;
	}

	public function gallery_items($items)
	{
    foreach($items as $item)
    {
      // if we have stored the image already, skip
      if($item->image)
        continue;

      $photo = file_get_contents($item->id);
      $entry = new SimpleXMLElement($photo);

      $ns = $entry->getDocNamespaces();
      $gphoto = $entry->children($ns['gphoto']);
      $photo_width = $gphoto->width;

      // attribute for the source image
      $src_attr = $group->content[0]->attributes();

      $src_url = (string)$src_attr['url'];
      $src_end = basename($src_url);
      $src_url = str_replace($src_end, "s" . $photo_width . "/" . $src_end, $src_url);

      $item->image = $src_url;
    }

    return $items;
	}

  public function get_settings($post_id)
  {
    return array();
  }

	public function settings_form()
	{
		global $post;

		$options = get_post_meta($post->ID, 'galleriapress_picasa', true);

		if(!$options)
			$options = array();

		extract($options);

	?>
		<table class="form-table">
      <tr>
        <td>
          <label for="picasa_username">Picasa Username</label>
        </td>
        <td>
          <input type="text" name="picasa_username" value="<?php echo $picasa_username; ?>" />
        </td>
      </tr>
    </table>
	<?php
	}

	public function save_settings()
	{
		global $post;

		$options['picasa_username'] = $_POST['picasa_username'];
		update_post_meta($post->ID, 'galleriapress_picasa', $options);
	}

  /**
   * AJAX handler for browsing picasa library
   */
  public function ajax_library_items()
  {
    // find out which gallery and path
    $post_id = (int)$_POST['post_id'];
    $path = htmlentities($_POST['path']);

		$options = get_post_meta($post_id, 'galleriapress_picasa', true);
		extract($options);

    // must have a username
    if(!$picasa_username)
      exit;

    // parse according to supplied path
    $elements = explode("/", $path);

    ob_start();

    $this->display_loading();

    if($elements[0] == 'albums')
    {
      // specific album
      if(count($elements) == 2)
      {
        $url = 'https://picasaweb.google.com/data/feed/api/user/' . $picasa_username . '/albumid/' . $elements[1];
        $photos_xml = file_get_contents($url);

        if(!$photos_xml)
          return;

        $feed = new SimpleXMLElement($photos_xml);
        ?>

        <ul class="picasa-menu">
          <li class="browse" data-path="albums">Back</li>
          <?php
            $gphoto = $feed->children($ns['gphoto']);
            echo $gphoto->name;
          ?>
        </ul>

        <ul class="clearfix grid">
          <?php
          foreach($feed->entry as $entry):
            $ns = $entry->getDocNamespaces();
            $group = $entry->children($ns['media'])->group;

            $gphoto = $entry->children($ns['gphoto']);
            $photo_width = $gphoto->width;

            // attribute for the source image
            $src_attr = $group->content[0]->attributes();

            $src_url = (string)$src_attr['url'];
            $src_end = basename($src_url);
            $src_url = str_replace($src_end, "s" . $photo_width . "/" . $src_end, $src_url);

            // attribute for the thumbnail
            $attr = $group->thumbnail[1]->attributes();
          ?>
           <li class="ui-state-default" data-itemid="<?php echo $entry->id; ?>" data-library="picasa">
             <img src="<?php echo $attr['url']; ?>" title="<?php echo $group->title; ?>" data-image="<?php echo $src_url; ?>" />
           </li>
          <?php endforeach; ?>
        </ul>

        <?php
      }
      // all albums
      else
      {
        $this->display_all_albums($post_id);
      }
    }
    elseif($elements[0] == 'search')
    {
      if(isset($_POST['picasa_search']))
        $search_query = htmlentities($_POST['picasa_search']);
      ?>
      <ul class="picasa-menu">
        <li>
          <input type="text" name="picasa_search" class="picasa-search" placeholder="Search..."/>
          <input type="button"  class="button browse" data-path="search" value="Search" />
        </li>
        <li class="browse" data-path="albums">Back</li>
      </ul>
      <?php
      if($search_query)
      {
        $url = 'https://picasaweb.google.com/data/feed/api/all?q=' . $search_query . '?&max-results=10';        

        $photos_xml = file_get_contents($url);

        if(!$photos_xml)
          return;

        $feed = new SimpleXMLElement($photos_xml);
        $this->display_items($feed->entry);
      }
    }

    ?>
      <script type="text/javascript">
        Galleriapress.picasa.init_draggable();
      </script>
    <?php

    $output = ob_get_clean();

    echo json_encode(array('html' => $output));

    exit;
  }

  protected function display_items($entries)
  {
    ?>
    <ul class="clearfix grid">
      <?php
        foreach($entries as $entry):
          $ns = $entry->getDocNamespaces();
          $group = $entry->children($ns['media'])->group;
          $attr = $group->thumbnail[1]->attributes();
      ?>
      <li class="ui-state-default" data-itemid="<?php echo $entry->id; ?>" data-library="picasa">
        <img src="<?php echo $attr['url']; ?>" title="<?php echo $group->title; ?>" />
      </li>
      <?php endforeach; ?>
    </ul>
    <?php
  }

  protected function display_all_albums($post_id)
  {
		$options = get_post_meta($post_id, 'galleriapress_picasa', true);
    $albums = $this->get_albums($options['picasa_username']);

    $this->display_default_menu();

    foreach($albums as $album):
      $attr = $album->media->group->thumbnail->attributes();
        ?>

        <div class="picasa-album browse" data-path="albums/<?php echo $album->gphoto->id; ?>">
          <img src="<?php echo $attr['url']; ?>" width="<?php echo $attr['width']; ?>" height="<?php echo $attr['height']; ?>" />
          <span class="title"><?php echo $album->gphoto->name; ?></span>
        </div>

        <?php
    endforeach;
  }

  protected function display_loading()
  {
    ?>
    <div class="picasa-loading">
      Loading...
    </div>
    <?php
  }

  protected function display_default_menu()
  {
    ?>
    <ul class="picasa-menu">
      <li class="browse" data-path="albums">Albums</li>
      <li class="browse" data-path="search">Search</li>
    </ul>
    <?php
  }

  protected function get_albums($username)
  {
    $url = 'https://picasaweb.google.com/data/feed/api/user/' . $username;
    $feed_xml = file_get_contents($url);

    if($feed_xml)
      $feed = new SimpleXMLElement($feed_xml);

    $albums = array();

    foreach($feed->entry as $entry)
    {
      $ns = $entry->getDocNamespaces();
      $gphoto = $entry->children($ns['gphoto']);
      $media = $entry->children($ns['media']);

      $albums[] = (object)array('gphoto' => $gphoto, 'media' => $media);
    }

    return $albums;
  }
}

?>