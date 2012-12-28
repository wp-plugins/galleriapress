<?php

/**
 * Display the settings page
 */
function galleriapress_settings_page()
{
?>
<div class="wrap">
  <h2>Galleriapress Settings</h2>

  <h3>Themes</h3>

  <h4>Installed themes</h4>

  <?php
    $themes = galleriapress_get_themes();

    foreach($themes as $theme_name => $dir)
      echo "<p>" . $theme_name . "</p>";

  ?>

  <h4>Upload theme</h4>

  <?php 
    $form_results = $_SESSION['galleriapress_theme_upload_form'];

    if(!empty($form_results['errors']))
      foreach($form_results['errors'] as $error)
        echo "<p>" . $error . "</p>";

    // check theme upload path
    if(($upload_path = galleriapress_theme_upload_path()) === false):
      echo "<p>uploads folder is not writable, please check permissions</p>";
    else:

  ?>

  <form enctype="multipart/form-data" method="post" action="">
    <input type="file" name="theme_file" />
    <input type="hidden" name="redirect_to" value="<?php echo admin_url('edit.php?post_type=gallery&page=galleriapress-settings'); ?>" />
    <input type="hidden" name="action" value="galleriapress_theme_upload" />
    <input type="submit" value="Upload" class="button-primary" />
  </form>

  <?php endif; ?>

</div>

<?php

  unset($_SESSION['galleriapress_theme_upload_form']);
}

/**
 * Handle Galleria theme upload
 */
function galleriapress_theme_upload()
{
  if($_POST['action'] == 'galleriapress_theme_upload')
  {
    $form_results = array('errors' => array());

    if($_FILES['theme_file']['type'] !== 'application/zip')
      $form_results['errors'][] = 'Not a zip file';
      
    $tmp_filename = $_FILES['theme_file']['tmp_name'];
    $filename = $_FILES['theme_file']['name'];
    $filename = preg_replace("/\\.[^.\\s]{3,4}$/", "", $filename);

    $zip = new ZipArchive;

    try
    {
      if(!$zip->open($tmp_filename) === TRUE)
        throw new Exception('Could not open zip file');

      if($zip->numFiles == 0)
        throw new Exception('Empty zip file');

      // check if we have a file with galleria.*.js pattern
      for($i = 0; $i < $zip->numFiles; $i++)
      {
        $file_info = $zip->statIndex($i);
        $file_stats[] = $file_info;
        if(preg_match('/galleria\.(.*)\.js/', $file_info['name']))
        {
          // store the path to the main js file relative to the zip archive
          $path_to_theme_js = dirname($file_info['name']);
          $valid = true;
        }
      }

      if(!$valid)
        throw new Exception('No valid theme JS file');

      $upload_path = galleriapress_theme_upload_path();
      $extract_path = $upload_path . $filename . "/";

      if(!mkdir($extract_path))
        throw new Exception('Unable to create directory' . basename($extract_path));

      // extract only the files in same directory as main js file (in case it is in a subdirectory)
      foreach($file_stats as $file)
      {
        if(strstr($file['name'], $path_to_theme_js) != FALSE)
           if(!$zip->extractTo($extract_path, $file['name']))
             throw new Exception('Could not extract file to destination path. Permission denied');
      }

      // if files where nested, put files under the theme directory and
      // remove directory where they were stored
      if($path_to_theme_js != "")
      {
        $glob = glob($extract_path . $path_to_theme_js . "/*");
        foreach($glob as $file)
          if($file != '.' && $file != '..')
            rename($file, $extract_path . basename($file));

        // remove directories where files were formely stored
        $dirs_to_remove = explode("/", $path_to_theme_js);
        while(!empty($dirs_to_remove))
        {
          $dir = $extract_path . implode("/", $dirs_to_remove);

          // check that it's not empty
          if(count(scandir($dir)) == 2)
          {
            rmdir($dir);
            array_shift($dirs_to_remove);
          }
          else
          {
            break;
          }
        }
      }

      $form_results['success'] = 'Theme successfully installed';
    }
    catch(Exception $e)
    {
      $form_results['errors'][] = $e->getMessage();
    }

    $_SESSION['galleriapress_theme_upload_form'] = $form_results;

    wp_redirect($_POST['redirect_to']);
    exit;
  }
}

add_action('init', 'galleriapress_theme_upload');

?>