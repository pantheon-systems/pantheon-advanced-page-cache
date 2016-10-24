# Pantheon Integrated CDN #
**Contributors:** (this should be a list of wordpress.org userid's)  
**Tags:** pantheon  
**Requires at least:** 4.4  
**Tested up to:** 4.6.1  
**Stable tag:** 0.0.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

## Description ##

[![Travis CI](https://travis-ci.org/pantheon-systems/pantheon-integrated-cdn.svg?branch=master)](https://travis-ci.org/pantheon-systems/pantheon-integrated-cdn) [![CircleCI](https://circleci.com/gh/pantheon-systems/pantheon-integrated-cdn.svg?style=svg)](https://circleci.com/gh/pantheon-systems/pantheon-integrated-cdn)

For sites wanting fine-grained control over how their responses are represented in their edge cache, Pantheon Integrated CDN is the golden ticket. Here's a high-level overview of how the plugin works:

1. When a response is generated, the plugin uses surrogate keys to "tag" the response with identifers for the data used in the response.
2. When WordPress data is modified, the plugin triggers a purge request for the data's corresponding surrogate keys.

Because of its surrogate key technology, Pantheon Integrated CDN empowers WordPress sites with a significantly more accurate cache purge mechanism, and generally higher cache hit rate.

Go forth and make awesome! And, once you've built something great, [send us feature requests (or bug reports)](https://github.com/pantheon-systems/pantheon-integrated-cdn/issues).

## Installation ##

To install Pantheon Integrated CDN, follow these steps:

1. Install the plugin from WordPress.org using the WordPress dashboard.
2. Activate the plugin.

To install Pantheon Integrated CDN in one line with WP-CLI:

    wp plugin install pantheon-integrated-cdn --activate

## Surrogate Keys ##

Surrogate keys enable responses to be "tagged" with identifiers that can then later be used in purge requests. This flexibility enables behaviors like:

* When a post is updated, clear the cache for the post's URL, the homepage, and any index view the post appears on.
* When an author changes their name, clear the cache for the author's archive and any post they've authored.

Keys emitted are optimized based on a user's expectation of a normal WordPress site because there's a limit on the total number of keys that can be included in a response. For instance, it doesn't make sense to include a `term-<id>` surrogate key for every category or tag on an archive view.

### Emitted Keys ###

**Home `/`**

* Emits surrogate keys: `home`, `front`, `post-<id>` (all posts in main query)

**Single post `/2016/10/14/surrogate-keys/`**

* Emits surrogate keys: `single`, `post-<id>`, `user-<id>`, `term-<id>` (all terms assigned to post)

**Author archive `/author/pantheon/`**

* Emits surrogate keys: `archive`, `user-<id>`, `post-<id>` (all posts in main query)

**Term archive `/tag/cdn/`**

* Emits surrogate keys: `archive`, `term-<id>`, `post-<id>` (all posts in main query)

**Search `/?s=<search>`**

* Emits surrogate keys: `search`, `post-<id>` (all posts in main query)

### Purge Events ###

**clean_post_cache**

* Purges surrogate keys: `home`, `front`, `post-<id>`, `user-<id>`
* Affected views: homepage, single post, any archive where post displays, author archive

**clean_term_cache**

* Purges surrogate keys: `term-<id>`
* Affected views: term archive, any post where the term is assigned

**clean_user_cache**

* Purges surrogate keys: `user-<id>`
* Affected views: author archive, any post where the user is the author

## Changelog ##

### 0.1.0 (??? ??, ????) ###
* Initial release.
