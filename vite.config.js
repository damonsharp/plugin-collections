import { defineConfig } from 'vite';

export default defineConfig({
	build: {
		manifest: true,
		assetsDir: '.',
		outDir: `dist`,
		emptyOutDir: true,
		sourcemap: true,
		rollupOptions: {
			input: [
				'assets/js/admin-scripts.js',
				'assets/css/admin.css',
			],
			output: {
				entryFileNames: 'js/[name].js',
				assetFileNames: 'css/[name].[ext]',
			},
		},
	},
	plugins: [
		{
			name: 'php',
			handleHotUpdate({ file, server }) {
				if (file.endsWith('.php')) {
					server.ws.send({ type: 'full-reload' });
				}
			},
		},
	],
});
