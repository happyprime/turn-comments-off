# Turn Comments Off
Contributors: happyprime, jeremyfelt, slocker, philcable
Tags: comments
Requires at least: 5.9
Tested up to: 6.9
Stable tag: 2.0.1
License: GPLv2 or later
Requires PHP: 7.4

Turn comments off everywhere in WordPress.

## Description

Activate this plugin to immediately remove all comment functionality from WordPress.

* Stop comments and pingbacks on all existing and future content.
* Remove "Discussion" from Settings in the admin menu.
* Remove "Comments" from the admin menu.
* Remove the comments bubble from the admin bar.
* Remove "Recent Comments" from the site activity dashboard widget.
* Remove the comments feed link.
* Unregister all core comment blocks in the editor.
* Remove "Manage Comments" from My Sites on multisite. (If network activated.)

No configuration necessary (or available).

If you find something we missed, [please let us know](https://github.com/happyprime/turn-comments-off)!

## Changelog

### 2.0.1

* No functional changes.
* Update development dependencies.
* Confirm WordPress 6.9 support.

### 2.0.0

* Fix a fatal PHP error when a comment query requests a count.
* Note: If you had manually removed our `comments_pre_query` filter for any reason,
  (you likely weren't), you'll need to adjust your code to account for the new
  namespaced function.
* Update development dependencies.

### 1.3.2

* No functional changes.
* Update development dependencies.
* Confirm WordPress 6.6 support.

### 1.3.1

* No functional changes.
* Replace `@wordpress/scripts` dependency with leaner configuration.
* Confirm WordPress 6.5 support.

### 1.3.0

* Remove "Manage Comments" from site menus under My Sites on multisite.
* Confirm WordPress 6.4 support.
* Update `wordpress/scripts` dependency to 26.15.0.
* Improve linting configuration.

### 1.2.1

* No functional changes, only custodial.
* Confirm WordPress 6.2 support.
* Update `wordpress/scripts` dependency to 26.1.0.
* Improve linting configuration.

### 1.2.0

* Update the list of blocks unregistered to include latest from Gutenberg and WordPress.
* Update `wordpress/scripts` to 24.6.0.
* Refactor how the list of blocks is managed to make future maintenance easier. Props [@huubl](https://profiles.wordpress.org/huubl/).
* Only unregister blocks that are registered. Props [@huubl](https://profiles.wordpress.org/huubl/).

### 1.1.1

* Unregister core comment blocks in the editor.

### 1.0.2

* Add automated GitHub deployments to wp.org.

### 1.0.1

* Add PHP namespace.

### 1.0.0

* Initial release.
