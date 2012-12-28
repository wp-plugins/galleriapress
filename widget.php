<?php

class Galleriapress_Widget extends WP_Widget 
{
	public function __construct()
  {
    parent::__construct('galleriapress_widget',
                        'Galleriapress Widget',
                        array('description' => 'Galleriapress Widget'));
	}

 	public function form($instance)
  {
    if(isset($instance['gid']))
      $gid = $instance['gid'];
    else
      $gid = -1;

    $galleries = get_posts(array('post_type' => 'gallery',
                                 'posts_per_page' => '-1',
                                 'post_status' => 'publish'));
    ?>
		<p>
		  <label for="<?php echo $this->get_field_id('gid' ); ?>">Gallery</label> 
		  <select class="widefat" id="<?php echo $this->get_field_id('gid'); ?>" name="<?php echo $this->get_field_name('gid' ); ?>">
        <?php foreach($galleries as $gallery): ?>
        <option value="<?php echo $gallery->ID; ?>"><?php echo $gallery->post_title; ?></option>
        <?php endforeach; ?>
      </select>
		</p>
		<?php 
	}

	public function update($new_instance, $old_instance)
  {
    $instance = array();
		$instance['gid'] = (int)$new_instance['gid'];

		return $instance;
	}

	public function widget($args, $instance)
  {
    extract($args);
		$gid = $instance['gid'];

		echo $before_widget;

    echo do_shortcode('[galleria gid=' . $gid . ']');

		echo $after_widget;
	}

}

add_action('widgets_init', create_function('', 'register_widget("Galleriapress_Widget");'));

?>
