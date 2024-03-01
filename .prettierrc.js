const config = {
	useTabs: true,
	tabWidth: 4,
	printWidth: 80,
	singleQuote: true,
	trailingComma: 'es5',
	bracketSameLine: false,
	bracketSpacing: true,
	semi: true,
	arrowParens: 'always',
	parenSpacing: true,
	overrides: [
		{
			files: '*.{css,sass,scss}',
			options: {
				singleQuote: false,
			},
		},
	],
};

module.exports = config;
