=== Pantheon Advanced Page Cache ===
Contributors: (this should be a list of wordpress.org userid's)
Tags: pantheon
Requires at least: 4.4
Tested up to: 4.6.1
Stable tag: 0.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

[![Travis CI](https://travis-ci.org/pantheon-systems/pantheon-advanced-page-cache.svg?branch=master)](https://travis-ci.org/pantheon-systems/pantheon-advanced-page-cache) [![CircleCI](https://circleci.com/gh/pantheon-systems/pantheon-advanced-page-cache.svg?style=svg)](https://circleci.com/gh/pantheon-systems/pantheon-advanced-page-cache)

For sites wanting fine-grained control over how their responses are represented in their edge cache, Pantheon Advanced Page Cache is the golden ticket. Here's a high-level overview of how the plugin works:

1. When a response is generated, the plugin uses surrogate keys to "tag" the response with identifers for the data used in the response.
2. When WordPress data is modified, the plugin triggers a purge request for the data's corresponding surrogate keys.

Because of its surrogate key technology, Pantheon Advanced Page Cache empowers WordPress sites with a significantly more accurate cache purge mechanism, and generally higher cache hit rate.

Go forth and make awesome! And, once you've built something great, [send us feature requests (or bug reports)](https://github.com/pantheon-systems/pantheon-advanced-page-cache/issues).

== Installation ==

To install Pantheon Advanced Page Cache, follow these steps:

1. Install the plugin from WordPress.org using the WordPress dashboard.
2. Activate the plugin.

To install Pantheon Advanced Page Cache in one line with WP-CLI:

    wp plugin install pantheon-advanced-page-cache --activate

== Surrogate Keys ==

Surrogate keys enable responses to be "tagged" with identifiers that can then later be used in purge requests. For instance, a home page response might include the header:

    Surrogate-Key: front home post-43 user-4 post-41 post-9 post-7 post-1 user-1

Because cached responses include metadata describing the data therein, surrogate keys enable more flexible purging behavior like:

* When a post is updated, clear the cache for the post's URL, the homepage, and any index view the post appears on.
* When an author changes their name, clear the cache for the author's archive and any post they've authored.

There is a limit to the number of surrogate keys in a response, so we've optimized them based on a user's expectation of a normal WordPress site. See the "Emitted Keys" section for full details.

Use the `pantheon_wp_surrogate_keys` filter to customize surrogate keys in a response. For example, to include a surrogate key for a sidebar rendered on the homepage, you can filter the keys included in the response:

    /**
     * Add surrogate key for the featured content sidebar rendered on the homepage.
     */
    add_filter( 'pantheon_wp_surrogate_keys', function( $keys ){
	    if ( is_home() ) {
            $keys[] = 'sidebar-home-featured';
        }
        return $keys;
    });

Then, when sidebars are updated, you can use the `pantheon_wp_clear_edge_keys()` helper function to emit a purge event specific to the surrogate key:

    /**
     * Trigger a purge event for the featured content sidebar when widgets are updated.
     */
    add_action( 'update_option_sidebars_widgets', function() {
        pantheon_wp_clear_edge_keys( array( 'sidebar-home-featured' ) );
    });

Need a bit more power? Here are two additional helper functions you can use:

* `pantheon_wp_clear_edge_paths( $paths = array() )` - Purge cache for one or more paths.
* `pantheon_wp_clear_edge_all()` - Warning! With great power comes great responsibility. Purge the entire cache, but do so wisely.

= Emitted Keys =

**Home `/`**

* Emits surrogate keys: `home`, `front`, `post-<id>` (all posts in main query)

**Single post `/2016/10/14/surrogate-keys/`**

* Emits surrogate keys: `single`, `post-<id>`, `post-user-<id>`, `post-term-<id>` (all terms assigned to post)

**Author archive `/author/pantheon/`**

* Emits surrogate keys: `archive`, `user-<id>`, `post-<id>` (all posts in main query)

**Term archive `/tag/cdn/`**

* Emits surrogate keys: `archive`, `term-<id>`, `post-<id>` (all posts in main query)

**Day archive `/2016/10/14/`**

* Emits surrogate keys: `archive`, `date`, `post-<id>` (all posts in main query)

**Month archive `/2016/10/`**

* Emits surrogate keys: `archive`, `date`, `post-<id>` (all posts in main query)

**Year archive `/2016/`**

* Emits surrogate keys: `archive`, `date`, `post-<id>` (all posts in main query)

**Search `/?s=<search>`**

* Emits surrogate keys: `search`, either `search-results` or `search-no-results`, `post-<id>` (all posts in main query)

= Purge Events =

**wp_insert_post / before_delete_post / delete_attachment**

* Purges surrogate keys: `home`, `front`, `post-<id>`, `user-<id>`, `term-<id>`
* Affected views: homepage, single post, any archive where post displays, author archive, term archive

**clean_post_cache**

* Purges surrogate keys: `post-<id>`
* Affected views: single post

**created_term / edited_term / delete_term**

* Purges surrogate keys: `term-<id>`, `post-term-<id>`
* Affected views: term archive, any post where the term is assigned

**clean_term_cache**

* Purges surrogate keys: `term-<id>`
* Affected views: term archive

**clean_user_cache**

* Purges surrogate keys: `user-<id>`
* Affected views: author archive, any post where the user is the author

== Changelog ==

= 0.1.0 (??? ??, ????) =
* Initial release.
