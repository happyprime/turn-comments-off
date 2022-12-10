import { unregisterBlockType } from '@wordpress/blocks';
import domReady from '@wordpress/dom-ready';

// Unregister comment-related blocks provided by WordPress core.
domReady( () => {
	// Blocks that will usually show as available on posts and pages.
	unregisterBlockType( 'core/latest-comments' );
	unregisterBlockType( 'core/comments-query-loop' );
	unregisterBlockType( 'core/post-comments-form' );

	// Blocks used inside the comments query loop block.
	unregisterBlockType( 'core/comment-author-name' );
	unregisterBlockType( 'core/comment-content' );
	unregisterBlockType( 'core/comment-date' );
	unregisterBlockType( 'core/comment-edit-link' );
	unregisterBlockType( 'core/comment-reply-link' );
	unregisterBlockType( 'core/comment-template' );
	unregisterBlockType( 'core/comments-pagination' );
	unregisterBlockType( 'core/comments-pagination-next' );
	unregisterBlockType( 'core/comments-pagination-numbers' );
	unregisterBlockType( 'core/comments-pagination-previous' );
	unregisterBlockType( 'core/comments-title' );
	unregisterBlockType( 'core/post-comments-count' );
	unregisterBlockType( 'core/post-comments-link' );
} );
