<?php
/**
 * Demo content for the local wp-env site
 *
 * Creates posts that already have comments, a post and a page built from
 * core comment blocks, and an editor note, so each thing the plugin hides has
 * something to hide. Safe to run more than once.
 *
 * Run with `npm run env:seed`.
 *
 * @package turn-comments-off
 */

namespace TurnCommentsOff\Dev;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates a post with the given slug unless one already exists.
 *
 * @param string $slug      Post slug.
 * @param string $title     Post title.
 * @param string $content   Block markup.
 * @param string $post_type Post type.
 * @return int Post ID, or 0 on failure.
 */
function upsert_post( string $slug, string $title, string $content, string $post_type = 'post' ): int {
	$existing = get_page_by_path( $slug, OBJECT, $post_type );

	if ( $existing instanceof \WP_Post ) {
		return $existing->ID;
	}

	$admin = get_user_by( 'login', 'admin' );

	// wp_insert_post() unslashes its input, which breaks quoted block attributes.
	$post_id = wp_insert_post(
		wp_slash(
			[
				'post_author'  => $admin ? $admin->ID : 1,
				'post_type'    => $post_type,
				'post_status'  => 'publish',
				'post_name'    => $slug,
				'post_title'   => $title,
				'post_content' => $content,
			]
		)
	);

	return is_wp_error( $post_id ) ? 0 : (int) $post_id;
}

/**
 * Counts every comment row on a post, of any type and status.
 *
 * WP_Comment_Query returns nothing while the plugin is active, so this reads
 * the table directly.
 *
 * @param int $post_id Post ID.
 * @return int Number of comment rows.
 */
function count_comment_rows( int $post_id ): int {
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Dev seed; the plugin short-circuits WP_Comment_Query.
	return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_post_ID = %d", $post_id ) );
}

/**
 * Adds comments to a post that has none.
 *
 * @param int                               $post_id  Post ID.
 * @param array<int, array<string, string>> $comments Comment fields.
 */
function seed_comments( int $post_id, array $comments ): void {
	if ( 0 === $post_id || count_comment_rows( $post_id ) > 0 ) {
		return;
	}

	foreach ( $comments as $comment ) {
		wp_insert_comment(
			array_merge(
				[
					'comment_post_ID'  => $post_id,
					'comment_approved' => '1',
					'comment_type'     => 'comment',
				],
				$comment
			)
		);
	}
}

/**
 * Seeds the site.
 */
function run(): void {
	global $wp_rewrite;

	// Updating the option alone leaves $wp_rewrite on the old structure for this flush.
	$wp_rewrite->set_permalink_structure( '/%postname%/' );
	flush_rewrite_rules();

	if ( 'twentytwentyfive' !== get_stylesheet() ) {
		switch_theme( 'twentytwentyfive' );
	}

	// Comments left before the plugin was activated.
	$commented_post = upsert_post(
		'post-with-old-comments',
		'A post with old comments',
		"<!-- wp:paragraph -->\n<p>This post has three approved comments, one waiting for moderation, and one spam comment. None of them should appear anywhere: not below the post, not in a comment count, not in a feed, and not in the REST API.</p>\n<!-- /wp:paragraph -->"
	);

	seed_comments(
		$commented_post,
		[
			[
				'comment_author'       => 'Ada',
				'comment_author_email' => 'ada@example.test',
				'comment_content'      => 'Approved comment one. If you can read this on the front end, a feed, or the REST API, comments are not off.',
			],
			[
				'comment_author'       => 'Grace',
				'comment_author_email' => 'grace@example.test',
				'comment_content'      => 'Approved comment two.',
			],
			[
				'comment_author'       => 'Linus',
				'comment_author_email' => 'linus@example.test',
				'comment_content'      => 'Approved comment three.',
			],
			[
				'comment_author'       => 'Pending Pat',
				'comment_author_email' => 'pat@example.test',
				'comment_content'      => 'A comment waiting for moderation.',
				'comment_approved'     => '0',
			],
			[
				'comment_author'       => 'Spammy Sam',
				'comment_author_email' => 'sam@example.test',
				'comment_content'      => 'A spam comment.',
				'comment_approved'     => 'spam',
			],
		]
	);

	// Comment blocks saved before the plugin was activated. The plugin unregisters them.
	$blocks_content = <<<'HTML'
<!-- wp:paragraph -->
<p>Below this paragraph are a Latest Comments block, a Comments Count block, a Comments Link block, and a full Comments block. With the plugin active they render nothing on the front end and show as unsupported in the editor.</p>
<!-- /wp:paragraph -->

<!-- wp:latest-comments {"commentsToShow":3} /-->

<!-- wp:post-comments-count /-->

<!-- wp:post-comments-link /-->

<!-- wp:comments -->
<div class="wp-block-comments"><!-- wp:comments-title /-->

<!-- wp:comment-template -->
<!-- wp:comment-author-name /-->

<!-- wp:comment-date /-->

<!-- wp:comment-content /-->

<!-- wp:comment-reply-link /-->
<!-- /wp:comment-template -->

<!-- wp:comments-pagination -->
<!-- wp:comments-pagination-previous /-->

<!-- wp:comments-pagination-numbers /-->

<!-- wp:comments-pagination-next /-->
<!-- /wp:comments-pagination -->

<!-- wp:post-comments-form /--></div>
<!-- /wp:comments -->
HTML;

	$blocks_post = upsert_post( 'post-with-comment-blocks', 'A post with comment blocks', $blocks_content );

	seed_comments(
		$blocks_post,
		[
			[
				'comment_author'       => 'Margaret',
				'comment_author_email' => 'margaret@example.test',
				'comment_content'      => 'An approved comment on the comment blocks post.',
			],
		]
	);

	upsert_post( 'latest-comments', 'Latest comments', $blocks_content, 'page' );

	// An editor note, which WordPress stores as a comment of type "note".
	$notes_post = upsert_post(
		'post-with-a-note',
		'A post with an editor note',
		"<!-- wp:paragraph -->\n<p>This paragraph had an editor note attached before the plugin was activated. Open it in the editor to see how notes behave.</p>\n<!-- /wp:paragraph -->"
	);

	if ( $notes_post > 0 && 0 === count_comment_rows( $notes_post ) ) {
		$admin   = get_user_by( 'login', 'admin' );
		$note_id = wp_insert_comment(
			[
				'comment_post_ID'  => $notes_post,
				'comment_type'     => 'note',
				'comment_approved' => '1',
				'comment_content'  => 'Can we tighten this sentence?',
				'user_id'          => $admin ? $admin->ID : 1,
				'comment_author'   => $admin ? $admin->display_name : 'admin',
			]
		);

		if ( $note_id ) {
			$content = '<!-- wp:paragraph {"metadata":{"noteId":' . (int) $note_id . "}} -->\n<p>This paragraph had an editor note attached before the plugin was activated. Open it in the editor to see how notes behave.</p>\n<!-- /wp:paragraph -->";

			wp_update_post(
				wp_slash(
					[
						'ID'           => $notes_post,
						'post_content' => $content,
					]
				)
			);
		}
	}

	\WP_CLI::success( 'Seeded demo content.' );
}

run();
