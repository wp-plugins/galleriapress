<?php

function galleriapress_media_tab($tabs)
{
  global $post;

  if($post->post_type != 'gallery')
  {
    $newtab = array('galleriapress' => 'Galleriapress' );
    return array_merge($tabs, $newtab);
  }

  return $tabs;
}

//add_filter('media_upload_tabs', 'galleriapress_media_tab');


function galleriapress_media_tab_content()
{
  $galleries = get_posts(array('post_type' => 'gallery',
                               'posts_per_page' => '-1',
                               'post_status' => 'publish'));

  foreach($galleries as $gallery)
  {
    echo $gallery->post_title;
  }

}

//add_action('media_upload_galleriapress', 'galleriapress_media_tab_content');


function galleriapress_media_template()
{
$galleries = get_posts(array('post_type' => 'gallery',
                             'posts_per_page' => -1));

?>
	<script type="text/html" id="tmpl-galleriapress">
     <?php foreach($galleries as $gallery): ?>
     <?php echo get_the_title($gallery->ID); ?>
     <?php endforeach; ?>
  </script>
<?php
}

add_action('print_media_templates', 'galleriapress_media_template');

?>