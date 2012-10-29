=== GalleriaPress ===
Contributors: erezodier
Donate Link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QXLXPBCX2FQVG
Tags: gallery, galleria
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: 0.7.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin integrates the Galleria jQuery plugin with Wordpress.


== Description ==

Galleriapress allows you to create galleries using the excellent galleria jQuery plugin. Simply drag and drop images from your Media Library
or from Picasa or videos from that are publicly available on a Youtube account.

You can link galleries to a Gallery Profile. Change the profile settings and it is updated throughout all the linked Galleries.

This plugin is still under development and still needs testing. I will be continually improving the plugin over time.


== Upgrade Notice ==

Upgrading to version 0.7.5:
If you where using the [gallery] shortcode in your own (non gallery)
posts, you will need to change it to the new [galleria] shortcode.



== Installation ==

This plugin requries PHP Version 5.3 or greater

1. Upload zip file contents to your plugins/ folder
2. Activate plugin


== Changelog ==

= 0.7.5 =

* Increased size of thumbnails to 150 by 150 pixels
* Upgraded galleria.js to version 1.2.8
* Changed to [galleria] shortcode
* Items that are already in the gallery are now not appearing in the library
* Fixed paging bug in the WP Media Library
* A message now appears on top of the gallery items telling user where to drag after drag start
* Minor styling improvements to the interface
* Gallery container expands by an extra row when full
* Fixed picasa library bug where the thumbnail was fetched instead of the actual image
* Fixed warning that would appear in debug mode

= 0.7.4.2 =

* Fixed error when activation with PHP 5.2

= 0.7.4.1 =

* Added link to go back to main Picasa libary menu when in search
* Add some padding for Picasa and Youtube settings panel
* Removed requirement for PHP 5.3 (should now work with PHP 5.2.4)
