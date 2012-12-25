<?php

class GalleriaPress_PicasaAPI
{
  private static $PICASA_USER_URL = 'https://picasaweb.google.com/data/feed/api/user/';

  public function user_albums($username)
  {
    $url = self::$PICASA_USER_URL . $username;

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
    $url = self::$PICASA_USER_URL .  $username . '/albumid/' . $album_id;
    $photos_xml = file_get_contents($url);

    if(!$photos_xml)
      return;

    $feed = new SimpleXMLElement($photos_xml);

    return $feed->entry;
  }

}