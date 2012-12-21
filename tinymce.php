<?php

function galleriapress_tinymce_add_buttons()
{
  if(!current_user_can('edit_posts') && !current_user_can('edit_pages'))
    return;

  if(get_user_option('rich_editing') == 'true')
  {
    add_filter('mce_external_plugins', 'galleriapress_tinymce_plugin');
    add_filter('mce_buttons', 'galleriapress_tinymce_buttons');
  }
}

add_action('init', 'galleriapress_tinymce_add_buttons');


function galleriapress_tinymce_buttons($buttons)
{
  array_push($buttons, "separator", "galleriapress");
  return $buttons;
}

function galleriapress_tinymce_plugin($plugin_array)
{
  $plugin_array['galleriapress'] = plugins_url('tinymce/editor-plugin.js', __FILE__);
  return $plugin_array;
}

/**
 * Display the TinyMCE dialog
 */
function galleriapress_ajax_tinymce_dialog()
{
  $pid = (int)$_GET['pid'];
  $post = get_post($pid);

  wp_print_scripts('jquery');

  // get all galleries
  $galleries = get_posts(array('post_type' => 'gallery',
                               'post_status' => 'publish',
                               'posts_per_page' => -1));

  // check if the post has some associated images
  $post_gallery = true;

  if($post->post_type !== 'gallery')
  {
    $attachments = get_posts(array('posts_per_page' => -1,
                                   'post_type' => 'attachment',
                                   'post_parent' => $pid));

    if(count($attachments) == 0)
      $post_gallery = false;
  }

  ?>

  <form id="galleriapress-tinymce">

    <select name="galleriapress_gallery">
      <?php if($post_gallery): ?><option value="-1">Post Gallery</option><?php endif; ?>
      <?php foreach($galleries as $gallery): ?>
      <option value="<?php echo $gallery->ID; ?>"><?php echo $gallery->post_title; ?></option>
      <?php endforeach; ?>
    </select>

    <input type="submit" value="Insert" />
  </form>

  <script type="text/javascript">
    $ = jQuery.noConflict();

    $('#galleriapress-tinymce').submit(function(e)
                                       {
                                           e.preventDefault();

                                           var id = $('select[name=galleriapress_gallery]', $(this)).val();
                                           var w = window.dialogArguments || opener || parent || top;
                                           var insert_str = '[galleria]';
                                           var ed = w.tinymce.EditorManager.activeEditor, g, el;

                                           if(id != -1)
                                               insert_str = '[galleria gid=' + id + ']';

			                                     if((g = ed.dom.select('img.wpGallery')) && g[0])
                                           {
                                               el = g[0];
                                               insert_str = insert_str.replace(/\[(.*)\]/, '$1');
                                               ed.dom.setAttrib(el, 'title', insert_str);
                                           }
                                           else
                                           {
                                               w.send_to_editor(insert_str);
                                           }
                                       });
  </script>

  <?php
  exit;
}

add_action('wp_ajax_galleriapress_tiny_mce_dialog', 'galleriapress_ajax_tinymce_dialog');

?>