
importScripts(
	"/getScript.php/core/IndexedDatabase",
	"/getScript.php/core/ImageDatabase"
);

const MESSAGE_ERROR = 0;
const MESSAGE_IMAGE_FOUND = 1;
const MESSAGE_STATUS_QUEUE = 2;

self.imageCache = new ImageDatabase("mtg", 7);
self.imageCacheResults = {};
self.portList = [];

self.sendMessage = function(port, messageType, messagePayload) {
	let message = {
		type : messageType,
		payload : messagePayload,
	};
	try {
		port.postMessage(message);
	} catch(e) {
		console.log(e);
	}
};
self.logError = function(errorMessage) {
	for (let i = 0; i < self.portList.length; i++) {
		self.sendMessage(self.portList[i], MESSAGE_ERROR, errorMessage);
	}
};
self.updateInfo = function() {
	try {
		let status = self.imageCache.getInfo();
		for (let i = 0; i < self.portList.length; i++) {
			self.sendMessage(self.portList[i], MESSAGE_STATUS_QUEUE, status);
		}
	} catch(e) {
		console.log(e);
	}
};

self.addEventListener(
	"connect",
	(eve) => {
		for (let i = 0; i < eve.ports.length; i++) {
			self.portList.push(eve.ports[i]);
			eve.ports[i].addEventListener(
				"message",
				(eve) => {
					let url = eve.data;
					let port = eve.target;
					if (self.imageCacheResults[url]) {
						self.sendMessage(port, MESSAGE_IMAGE_FOUND, self.imageCacheResults[url]);
					} else {
						self.imageCache.lookupImage(
							url,
							(row) => {
								let result = {};
								result.url = row.url
									? row.url
									: url;
								result.blobURL = row.blob
									? self.URL.createObjectURL(row.blob)
									: null;
									
								self.imageCacheResults[url] = result;
								
								self.sendMessage(port, MESSAGE_IMAGE_FOUND, self.imageCacheResults[url]);
							}
						);
					}
				},
				false
			);
			eve.ports[i].start();
		}
		self.updateInfo();
	},
	false
);

self.setInterval(
	self.updateInfo,
	1000
);