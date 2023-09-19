module.exports = {
	globDirectory: "./",
	globPatterns: ["**/*.{php,json,js,css,woff}"],
	swDest: "/service-worker.js",
	runtimeCaching: [
		{
			urlPattern: /\.(?:png|jpg|jpeg|svg)$/,
			handler: "NetworkFirst",
			options: {
				cacheName: "images",
				expiration: {
					maxEntries: 10,
				},
			},
		},
	],
};
