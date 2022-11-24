/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		'templates/**/*.html.twig',
		'assets/js/**/*.js',
		'assets/js/**/*.jsx', // Si vous utilisez des fichiers React JSX
	],
	theme: {
		fontFamily: {
			'sans': ['Noto Sans', 'ui-sans-serif', 'system-ui']
		},
		extend: {
			colors: {
				'twitch': '#6441a5',
				'reddit': '#ff5700',
				'discord': '#7289da',
				'neutral': {
					750: '#333333'
				}
			}
		}
	},
	plugins: [
		require('@tailwindcss/forms')
	],
};
