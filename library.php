<?php

abstract class GalleriaPress_Library
{
  /**
   * Constructor
   *
   * All subclasses must call parent::__construct for registration to occur unless
   * they want to register themselves.
   *
   * @param int $priority A lower number means a higher priority in the ordering of the library tabs
   */
  public function __construct($priority = 10) 
  {
		add_action('galleriapress_libraries', array(&$this, 'galleriapress_libraries'), $priority, 2);
  }

  /**
   * Magic method to access info fields
   *
   * @param string $name The name of the field
   * @return mixed The value specified by $name
   */
	public function __get($name)
	{
		$info = static::info();

		return $info[$name];
	}

  /**
   * return the library info
   */
  public function info() { }

  /**
   * Display the library items
   */
  public function library_items($gallery_items = array(), $options) { }

  /**
   * Display the gallery items for this library
   */
  public function gallery_items(array $items) { }

  /**
   * Get the settings for the gallery
   */
  public function get_settings($post_id) { }

  /**
   * Display the library settings
   */
  public function settings_form() { }

  /**
   * Save the library settings
   */
  public function save_settings() { }

  /**
   * Register the library
   *
   * @param array $libraries Array to add the library object
   */
	public function galleriapress_libraries($libraries)
	{
    if(method_exists($this, 'info'))
    {
      $info = $this->info();

      $libraries[$info['name']] = $this;
    }

    return $libraries;
	}

}

?>