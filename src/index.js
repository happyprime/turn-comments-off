import { getBlockType, unregisterBlockType } from '@wordpress/blocks';
import domReady from '@wordpress/dom-ready';

// Unregister comment-related blocks provided by WordPress core.
domReady(() => {
	const blocks = [
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

	blocks.forEach((block) => {
		if (undefined !== getBlockType(block)) {
			unregisterBlockType(block);
		}
	});
});
