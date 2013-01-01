<?php

class GalleriaPress_PicasaAPI
{
  private static $API_FEED = 'https://picasaweb.google.com/data/feed/api/';
  private static $API_USER_FEED = 'https://picasaweb.google.com/data/feed/api/user/';

  public function user_albums($username)
  {
    $url = self::$API_USER_FEED . $username;

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

  public function user_album($username, $album_id)
  {
    $url = self::$API_USER_FEED . $username . '/albumid/' . $album_id;
    $photos_xml = file_get_contents($url);

    if(!$photos_xml)
      return;

    $feed = new SimpleXMLElement($photos_xml);

    return $feed->entry;
  }

  public function user_uploads($username, $max_results = 10)
  {
    $url = self::$API_USER_FEED . $username . '?kind=photo&max-results=' . $max_results;
    $photos_xml = file_get_contents($url);

    if(!$photos_xml)
      return array();

    $feed = new SimpleXMLElement($photos_xml);

    return $feed->entry;    
  }

  public function search($query, $max_results = 10)
  {
    $url = self::$API_FEED . "all?q=" . $query . "&max-results=" . $max_results;
    $photos_xml = file_get_contents($url);

    if(!$photos_xml)
      return array();

    $feed = new SimpleXMLElement($photos_xml);

    return $feed->entry;    
  }

}