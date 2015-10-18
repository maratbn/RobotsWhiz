=== RobotsWhiz ===
Contributors: maratbn
Tags: robots, robots.txt, crawlers, spiders, search engines, discourage search engines, robots policy, page specific robots
Requires at least: 3.8.1
Tested up to: 4.3.1
Stable tag: 0.2.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Easy way to discourage search engines from indexing only specific pages / posts.


== Description ==

Overview:

  At the time of this writing, the latest version of WordPress, version 4.3.1,
  has functionality to discourage search engines from indexing the whole site,
  but not functionality to discourage search engines from indexing only certain
  specific pages and posts of the site.

  RobotsWhiz is a WordPress plugin that allows site administrators to
  discourage search engines from indexing, following links from, caching,
  indexing images from, and / or have additional custom restrictions on only
  for certain specifically-designated pages and posts.

  It is an easy way to discourage search engines from indexing only specific
  pages / posts.

  This only applies to search engine robots, and will not effect the site's
  local search functionality.

Technical summary:

  Plugin works by hooking-in special logic into the action 'wp_head' to inject
  the tag `<meta name='robots' content='...'>` with restriction directives for
  the specified pages and posts.

Official project URLs:

  https://github.com/maratbn/RobotsWhiz


== Installation ==

1. Unzip contents of `robotswhiz.zip` into the directory `/wp-content/plugins/robotswhiz/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.


== Frequently Asked Questions ==

= Where can I post issues / bugs / feature requests? =

Post issues / bugs / feature requests at: https://github.com/maratbn/RobotsWhiz/issues

= Where can I post pull requests? =

Post pull requests at: https://github.com/maratbn/RobotsWhiz/pulls


== Changelog ==

= 0.1.0 =
* Initial release.

= 0.1.1 =
* Minor code refactoring involving empty PHP echo short tags.
* Corrected REQUIREMENTS in that the supported PHP version is >= 5.4 not >= 5.3

= 0.2.0 =
* Updated description wording.
* Added activation check for PHP version to not be < 5.4

= 0.2.1 =
* Fixed bug:  Restored the php echo short tags for rendering <table> column
  header captions that were incorrectly removed earlier.
* Fixed bug:  Corrected the logic for processing previously saved values.
