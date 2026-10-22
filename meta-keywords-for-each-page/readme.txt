=== Meta Keywords for Each Page ===
Contributors: hemantjangir
Plugin URI: 
Tags: meta keywords, seo, seo keywords, meta tags, seo plugin
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.9
License: GPL2
License URI: [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

Easily add SEO meta keywords to enhance your website's search engine optimization.

== Description ==

This WordPress plugin enables you to easily add Meta Keywords to enhance your website's SEO. With this plugin, you can quickly add SEO meta keywords to your pages, posts, products, taxonomies and archive pages on your WordPress site.

The plugin now includes a live preview of the generated meta tag HTML, visible while editing posts, pages, custom post types, and taxonomy terms.

While many SEO plugins offer features for adding meta titles and descriptions, most require a premium upgrade to include meta keywords.

The **Meta Keywords for Each Page** plugin gives you the ability to add SEO meta keywords without the need to upgrade to a paid version, making it an efficient and cost-effective solution for optimizing your website's SEO.

New in 1.0.3: CSV Export & Import — quickly export all saved meta keywords to a CSV file and import them back. The importer expects **numeric IDs** (post IDs for posts, term IDs for taxonomy terms) — useful for backups and bulk migration when IDs are preserved between sites.

== Plugin Features ==

* Add SEO Meta Keywords for Pages, Posts, and Products.
* Easy-to-use interface for adding meta keywords directly from the post, page, or taxonomy editing screen.
* Custom meta box for adding meta keywords.
* Taxonomy meta keywords support.
* Simple interface in the admin dashboard to manage meta keywords for all custom post types.
* Export / Import meta keywords via CSV (import expects numeric post / term IDs).

== Installation ==

1. Upload the `meta-keywords-for-each-page` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Once activated, the plugin will add a new "Meta Keywords" box on the post, page, or taxonomy edit screen.
4. You can also manage meta keywords for custom post types directly from the WordPress admin menu.

== Usage ==

After activating the plugin:

1. Edit any post, page, or taxonomy term.
2. You'll see a meta box called "Keywords Box" where you can add SEO meta keywords.
3. You can also manage SEO meta keywords for all custom post types through the "Meta Keywords" page under the "Settings" menu in the WordPress dashboard.
4. Once you've added the keywords, save the changes to apply the SEO meta keywords to the page.

== Changelog ==

= 1.0.9 =
* Compatibility check with latest version of wordpress 7.1
= 1.0.8 =
* Added live meta tag preview for post, page, custom post type, and taxonomy term editing screens.
* Admin assets now load only on the plugin settings page, post edit screens, and taxonomy term screens.
= 1.0.7 =
* Fixed backend UI and updated the screenshot. 
= 1.0.6 =
* Compatibility check with latest version of wordpress 7.0
= 1.0.5 =
* Compatibility check with latest version of wordpress 6.9
= 1.0.4 =
* Added plugin translation files (.pot, .po, .mo) for internationalization.
= 1.0.3 =
* Added Export/Import (CSV) tool for meta keywords. Import expects numeric post IDs and term IDs.
* Export format includes source, object_type, object_id, object_key, title and meta_keywords.
* Minor UI and code quality improvements.
= 1.0.2 =
* Compatibility checked for new version of WordPress.
= 1.0.1 =
* Added the mkephem_meta_keywords_saved action hook after saving meta keywords for custom post types.
* Resolved an issue with nonce verification during the meta keywords save process.
* Ensured proper handling of skipped post types using a filterable mkephem_skip_post_types array.
* Updated the code for better security and compatibility with WordPress coding standards.
= 1.0.0 =
* Initial release. Added functionality to manage and display SEO meta keywords for pages, posts, and taxonomy terms.
* Admin interface to manage meta keywords for custom post types.

== Frequently Asked Questions ==

= How can I add SEO meta keywords to a post or page? =
Once the plugin is installed and activated, you will see a "Keywords Box" meta box on the post or page edit screen where you can enter the meta keywords.

= Can I add meta keywords to custom post types? =
Yes, the plugin supports custom post types. You can manage meta keywords for all custom post types from the "Meta Keywords" page in the WordPress dashboard.

= Is this plugin free to use? =
Yes, the **Meta Keywords for Each Page** plugin is free to use, with no upgrade required to access the meta keywords functionality.

= Will this plugin work with other SEO plugins? =
Yes, this plugin adds meta keywords, which is an additional feature. It works independently of other SEO plugins.

== Support ==

If you encounter any issues, please visit the plugin’s support forum. If you found this plugin useful, please leave a review!

== Screenshots ==

1. Screenshot of the Meta Keywords box on the post editing screen.
2. Screenshot of the Meta Keywords box on the post category screen.
3. Screenshot of the Meta Keywords page in the WordPress dashboard for managing keywords for custom post types.