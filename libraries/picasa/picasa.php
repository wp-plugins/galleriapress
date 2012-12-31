<?php

require_once('picasa-api.php');

class GalleriaPress_Picasa extends GalleriaPress_Library
{
  private $api;

	public function __construct()
	{
		parent::__construct();

		add_action('init', array(&$this, 'init'));
    add_action('admin_print_scripts-post.php', array(&$this, 'admin_print_scripts'));
    add_action('admin_print_scripts-post-new.php', array(&$this, 'admin_print_scripts'));

    $this->api = new GalleriaPress_PicasaAPI();
	}

	public function info()
	{
		return array('name' => 'picasa',
								 'title' => 'Picasa',
                 'icon' => plugins_url("picasa-icon.png", __FILE__),
								 'galleriapress_version' => '0.8');
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

	public function library_items($gallery_items, $path)
	{
		global $post;

    $path_elements = explode("/", $path);
    $mode = array_shift($path_elements);

    $gallery_ids = array();
    foreach($gallery_items as $item)
    {
      if($item->library == 'picasa')
        $gallery_ids[] = $item->id;
    }

    $this->display_toolbar($path);


    switch($mode)
    {
    case "user":
      $username = array_shift($path_elements);
      if($username)
      {
        preg_match("/^albums\/?(.*)?\/?$/", implode("/", $path_elements), $matches);

        if(!empty($matches))
        {
          if($matches[1])
          {
            $items = $this->api->user_album($username, $matches[1]);
            $this->display_items($items);
          }
          else
          {
            $albums = $this->api->user_albums($username);

            foreach($albums as $album):
              $attr = $album->media->group->thumbnail->attributes();
          ?>

          <div class="picasa-album library-path" data-library="picasa" data-path="user/<?php echo $username; ?>/albums/<?php echo $album->gphoto->id; ?>">
            <img src="<?php echo $attr['url']; ?>" width="<?php echo $attr['width']; ?>" height="<?php echo $attr['height']; ?>" />
            <span class="title"><?php echo $album->gphoto->name; ?></span>
          </div>

          <?php
            endforeach;
          }
        }
      }

     break;
     }
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
      $src_attr = $entry->content[0]->attributes();

      $src_url = (string)$src_attr['src'];
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

  protected function display_items($entries)
  {
    ?>
    <ul class="clearfix grid scrollable">
      <?php
        foreach($entries as $entry):
          $ns = $entry->getDocNamespaces();
          $group = $entry->children($ns['media'])->group;
          $attr = $group->thumbnail[1]->attributes();
      ?>
      <li class="ui-state-default item" data-itemid="<?php echo $entry->id; ?>" data-library="picasa">
        <img src="<?php echo $attr['url']; ?>" title="<?php echo $group->title; ?>" />
      </li>
      <?php endforeach; ?>
    </ul>
    <?php
  }

  protected function display_toolbar($path)
  {
    $path_elements = explode("/", $path);
    $mode = array_shift($path_elements);

  ?>
  <div class="picasa-menu">
  <?php
    switch($mode)
    {
      case "user":
        $username = array_shift($path_elements);
  ?>
      <a href="#" class="library-path nav-item" data-library="picasa" data-path="/">Back</a>
      <form class="form-picasa-user nav-item">
        <input id="picasa_user" placeholder="Enter username" <?php if($username): ?>value="<?php echo $username; ?>" <?php endif; ?>/>
        <input type="submit" class="library-path button-primary" data-library="picasa" data-path="user/{picasa_user}" value="Update" />
      </form>
  <?php if($username): ?>
      <a href="#" class="library-path nav-item" data-library="picasa" data-path="user/<?php echo $username; ?>/albums">Albums</a>
  <?php endif;
        break;

      case "search":
        $search_term = array_shift($path_elements);
  ?>
      <a href="#" class="library-path nav-item" data-library="picasa" data-path="/">Back</a>
      <form class="form-picasa-search nav-item">
        <input id="picasa_search" placeholder="Search Picasa" <?php if($search_term): ?>value="<?php echo $search_term; ?>" <?php endif; ?>/>
        <input type="submit" class="library-path button-primary" data-library="picasa" data-path="search/{picasa_search}" value="Update" />
      </form>

      <?php
      break;
    default:
      ?>
      <a href="#" class="library-path nav-item" data-library="picasa" data-path="user">User</a>
      <a href="#" class="library-path nav-item" data-library="picasa" data-path="search">Search</a>
      <?php
      break;
    }
      ?>
    </div><!-- .picasa-menu -->
  <?php
  }
}

?>
