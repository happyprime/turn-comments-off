module.exports = {
	extends: [
		'plugin:import/recommended',
		'plugin:jsdoc/recommended',
		'plugin:jsx-a11y/recommended',
		'plugin:prettier/recommended',
		'plugin:react/recommended',
		'plugin:react-hooks/recommended',
	],
	env: {
		browser: true,
	},
	ignorePatterns: ['build/**/*.js', 'vendor/**/*.js'],
	rules: {
		'no-console': ['warn', { allow: ['error'] }],
		'import/no-unresolved': [2, { ignore: ['^@wordpress/'] }],
	},
	settings: {
		react: {
			version: '18',
		},
	},
};
