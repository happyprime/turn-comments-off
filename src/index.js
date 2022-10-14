import { unregisterBlockType } from '@wordpress/blocks';
import { getBlockType } from '@wordpress/blocks';
import domReady from '@wordpress/dom-ready';

// Unregister comment-related blocks provided by WordPress core.
domReady( () => {
	const blocks = [
		// Blocks that will usually show as available on posts and pages.
		'core/latest-comments',
		'core/comments-query-loop',
		'core/post-comments-form',

		// Blocks used inside the comments query loop block.
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
		'core/post-comments-count',
		'core/post-comments-link',
	];

	blocks.forEach( function ( block ) {
		if ( getBlockType( block ) ) {
			unregisterBlockType( block );
		}
	} );
} );
