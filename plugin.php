<?php
/**
 * Plugin Name:  Turn Comments Off
 * Description:  Turn comments off everywhere in WordPress.
 * Version:      2.0.1
 * Plugin URI:   https://github.com/happyprime/turn-comments-off/
 * Author:       Happy Prime
 * Author URI:   https://happyprime.co
 * License:      GPL v2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  turn-comments-off
 * Requires PHP: 7.4
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package turn-comments-off
 */

namespace TurnCommentsOff;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Comments are never open.
add_filter( 'comments_open', '__return_false' );

// And pings are a form of comments.
add_filter( 'pings_open', '__return_false' );

// No content has an existing comment.
add_filter( 'get_comments_number', '__return_zero' );

// So return an empty set or count of comments for all comment queries.
add_filter( 'comments_pre_query', __NAMESPACE__ . '\filter_comments_pre_query', 10, 2 );

// And disable the comments feed.
add_filter( 'feed_links_show_comments_feed', '__return_false' );

// And remove comment rewrite rules.
add_filter( 'comments_rewrite_rules', '__return_empty_array' );

// Then remove comment support from everything.
add_action( 'init', __NAMESPACE__ . '\remove_comment_support', 99 );
add_action( 'init', __NAMESPACE__ . '\remove_trackback_support', 99 );

// Remove comment blocks from the editor. (Twice to be sure!).
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\unregister_comment_blocks_javascript' );
add_action( 'init', __NAMESPACE__ . '\unregister_comment_blocks', 99 );

// And disable all comment related views in the admin.
add_filter( 'wp_count_comments', __NAMESPACE__ . '\filter_wp_count_comments' );
add_action( 'add_admin_bar_menus', __NAMESPACE__ . '\remove_admin_bar_comments_menu' );
add_action( 'admin_bar_menu', __NAMESPACE__ . '\remove_my_sites_comments_menu', 21 );
add_action( 'admin_menu', __NAMESPACE__ . '\remove_comments_menu_page' );
add_action( 'load-options-discussion.php', __NAMESPACE__ . '\block_comments_admin_screen' );
add_action( 'load-edit-comments.php', __NAMESPACE__ . '\block_comments_admin_screen' );

/**
 * Filter the comments pre query.
 *
 * @param array<int,\WP_Comment>|int|null $comments The comments to filter.
 * @param \WP_Comment_Query               $query The query object.
 * @return array<int,\WP_Comment>|int The filtered comments.
 */
function filter_comments_pre_query( $comments, \WP_Comment_Query $query ) {
	if ( $query->query_vars['count'] ) {
		return 0;
	}

	return [];
}

/**
 * Remove comments support from all post types that have registered
 * it by priority 99 on init.
 */
function remove_comment_support(): void {
	$post_types = get_post_types_by_support( 'comments' );

	foreach ( $post_types as $post_type ) {
		remove_post_type_support( $post_type, 'comments' );
	}
}

/**
 * Remove trackbacks support from all post types that have registered
 * it by priority 99 on init.
 */
function remove_trackback_support(): void {
	$post_types = get_post_types_by_support( 'trackbacks' );

	foreach ( $post_types as $post_type ) {
		remove_post_type_support( $post_type, 'trackbacks' );
	}
}

/**
 * Enqueue a script to remove any client-side registration of WordPress
 * core comment blocks.
 *
 * @since 1.1.0
 */
function unregister_comment_blocks_javascript(): void {
	$asset_data = include_once __DIR__ . '/build/index.asset.php';

	wp_enqueue_script(
		'turn-comments-off',
		plugin_dir_url( __FILE__ ) . '/build/index.js',
		$asset_data['dependencies'],
		$asset_data['version'],
		true
	);
}

/**
 * Remove any server-side registration of WordPress core comment blocks.
 *
 * @see unregister_comment_blocks_javascript() for client-side removal.
 *
 * @since 1.1.0
 */
function unregister_comment_blocks(): void {

	// Retrieve all registered blocks.
	$registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();

	$blocks = [
		'core/comments',
		'core/comments-query-loop', // Replaced by core/comments in Gutenberg 13.7.

		'core/comment-author-avatar',
		'core/comment-author-name',
		'core/comment-content',
		'core/comment-date',
		'core/comment-edit-link',
		'core/comment-reply-link',
		'core/comment-template',

		'core/comments-pagination',
		'core/comments-pagination-next',
		'core/comments-pagination-numbers',
		'core/comments-pagination-previous',
		'core/comments-title',

		'core/latest-comments',

		'core/post-comment',
		'core/post-comments-count',
		'core/post-comments-form',
		'core/post-comments-link',
	];

	foreach ( $blocks as $block ) {
		if ( isset( $registered_blocks[ $block ] ) ) {
			unregister_block_type( $block );
		}
	}
}

/**
 * Remove the "Comments" and Settings -> Discussion menus from the
 * side menu in the dashboard.
 */
function remove_comments_menu_page(): void {
	remove_menu_page( 'edit-comments.php' );
	remove_submenu_page( 'options-general.php', 'options-discussion.php' );
}

/**
 * Remove the comments menu from the admin bar.
 */
function remove_admin_bar_comments_menu(): void {
	remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
}

/**
 * Remove the "Manage Comments" node from each site's menu under My Sites.
 *
 * @since 1.3.0
 */
function remove_my_sites_comments_menu(): void {
	global $wp_admin_bar;

	// Only parse for the menu if it's going to be there, part 1.
	if ( ! is_multisite() || ! is_user_logged_in() ) {
		return;
	}

	// Only parse for the menu if it's going to be there, part 2.
	if ( count( $wp_admin_bar->user->blogs ) < 1 ) {
		return;
	}

	// The plugin API is not always available on the front-end.
	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	// We can't accurately remove the menu item if the plugin is not network activated.
	if ( ! is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
		return;
	}

	foreach ( $wp_admin_bar->user->blogs as $blog ) {
		$wp_admin_bar->remove_menu( 'blog-' . $blog->userblog_id . '-c' );
	}
}

/**
 * Filter wp_count_comments() so that it always returns 0.
 *
 * This hides Recent Comments from the dashboard activity widget.
 *
 * @return \stdClass An object with expected count properties.
 */
function filter_wp_count_comments(): \stdClass {
	return (object) array(
		'approved'       => 0,
		'moderated'      => 0,
		'spam'           => 0,
		'trash'          => 0,
		'post-trashed'   => 0,
		'total_comments' => 0,
		'all'            => 0,
	);
}

/**
 * Block access to the Settings -> Discussion and Edit Comments views
 * in the admin.
 */
function block_comments_admin_screen(): void {
	wp_die( esc_html__( 'This screen is disabled by the Turn Comments Off plugin.', 'turn-comments-off' ) );
}
