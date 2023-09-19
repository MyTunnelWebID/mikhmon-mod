self.addEventListener("load", () => {
	registerSW();
});

async function registerSW() {
	if ("serviceWorker" in navigator) {
		try {
			navigator.serviceWorker.register("/service-worker.js");
			// console.log(`SW registration Success`);
		} catch (e) {
			// console.log(`SW registration failed`);
		}
	}
}
