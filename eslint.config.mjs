import happyprimeConfig from '@happyprime/eslint-config';
import wordpressPlugin from '@wordpress/eslint-plugin';

export default [
	...happyprimeConfig,
	// Only the i18n rules. The rest of the WordPress config overlaps the
	// happyprime one.
	...wordpressPlugin.configs.i18n,
	{
		rules: {
			'@wordpress/i18n-text-domain': [
				'error',
				{ allowedTextDomain: 'turn-comments-off' },
			],
		},
	},
];
