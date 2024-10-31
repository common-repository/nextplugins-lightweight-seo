=== NextPlugins Lightweight SEO ===
Contributors: nextplugins
Tags: seo, lightweight seo, NextPlugins seo, facebook, twitter card, keywords, meta keywords, meta description
Requires at least: 4.4
Tested up to: 4.8
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight SEO plugin for WordPress.

== Description ==

Lightweight SEO plugin.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Optionally add `define('NP_LIGHTWEIGHT_SEO_POSTS', 'post, page, my_custom_post_type');` in wp-config.php to enable SEO fields in Posts, Pages and custom post types
1. Optionally add `define('NP_LIGHTWEIGHT_SEO_TAXONOMY', 'category, my_custom_taxonomy');` in wp-config.php to enable SEO fields in Category and custom taxonomies

By default SEO fields will be added to Posts, Pages and Posts Category.

By default Post featured image is used for Facebook (og:image) and Twitter (twitter:image) images.
It is possible to add custom image using `next_plugins_lightweight_seo_post_image` and `next_plugins_lightweight_seo_term_image` filter.
In both filters you need to return array containing image url and sizes.
Returned array example:
`array('url' => 'http://www.my-full-url.com/image.jpg', 'width' => 300, 'height' => 200)`

== Installation ==

== Changelog ==

= 1.0.1 =
* Fix missing arguments for save_post hook

= 1.0.0 =
* First version.
