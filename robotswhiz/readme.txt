=== Robots Meta Whiz ===
Contributors: maratbn
Tags: robots, robots.txt, crawlers, spiders, search engines, discourage search engines, robots policy, page specific robots, meta, privacy
Requires at least: 3.8.1
Tested up to: 4.7
Stable tag: 1.1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Easy way to discourage search engines from indexing only specific pages / posts with custom meta tags.


== Description ==

Overview:

  At the time of this writing, the latest version of WordPress, version 4.7,
  has functionality to discourage search engines from indexing the whole site,
  but not functionality to discourage search engines from indexing only certain
  specific pages and posts of the site.

  Robots Meta Whiz is a WordPress plugin that allows site administrators to
  deploy custom meta robots tags to discourage search engines from indexing,
  following links from, caching, indexing images from, and / or have
  additional custom restrictions on only for certain specifically-designated
  pages and posts.

  It is an easy way to discourage search engines from indexing only specific
  pages / posts.

  This only applies to search engine robots, and will not effect the site's
  local search functionality.

Technical summary:

  Plugin works by hooking-in special logic into the action 'wp_head' to inject
  the tag `<meta name='robots' content='...'>` with restriction directives for
  the specified pages and posts.

Official project URLs:

  https://wordpress.org/plugins/robotswhiz
  https://github.com/maratbn/RobotsWhiz


== Installation ==

1. Unzip contents of `robotswhiz.zip` into the directory `/wp-content/plugins/robotswhiz/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.


== Frequently Asked Questions ==

= Where can I ask a question about Robots Meta Whiz? =

Ask your questions at: https://wordpress.org/support/plugin/robotswhiz

= Where can I post issues / bugs / feature requests? =

Post issues / bugs / feature requests at: https://github.com/maratbn/RobotsWhiz/issues

= Where can I post pull requests? =

Post pull requests at: https://github.com/maratbn/RobotsWhiz/pulls


== Screenshots ==

1. Robots Meta Whiz configuration screen.


== Changelog ==

= 1.2.0 =
* Inserted thin dashed line between each post attribute row and the tokens row for better
  readability.
* Fixed incorrect sorting of the page template column occuring under WordPress version < 4.7

= 1.1.0 =
* Implemented user sorting of the posts table rows according to column values and in ascending or
  descending order.
* Having the robots meta tokens be sorted alphabetically.

= 1.0.0 =
* Renamed the plugin from RobotsWhiz to Robots Meta Whiz.
* Additional minor changes to description and keywords.

= 0.3.0 =
* Added screenshot of configuration screen.
* Miscellaneous documentation improvements.

= 0.2.1 =
* Fixed bug:  Restored the php echo short tags for rendering `<table>` column
  header captions that were incorrectly removed earlier.
* Fixed bug:  Corrected the logic for processing previously saved values.

= 0.2.0 =
* Updated description wording.
* Added activation check for PHP version to not be < 5.4

= 0.1.1 =
* Minor code refactoring involving empty PHP echo short tags.
* Corrected REQUIREMENTS in that the supported PHP version is >= 5.4 not >= 5.3

= 0.1.0 =
* Initial release.
