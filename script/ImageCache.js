
const MESSAGE_ERROR = 0;
const MESSAGE_IMAGE_FOUND = 1;
const MESSAGE_STATUS_QUEUE = 2;

function ImageCache(name) {
	try {
		this.lookupCallbacks = {};
		this.lookupResults = {};
		
		this.worker = new SharedWorker(
			"/getScript.php/mtg/ImageCache.Worker",
			"ImageCache",
			{ type : "classic" }
		);
		this.worker.port.addEventListener(
			"message",
			(eve) => {
				let message = eve.data;
				if (message) {
					switch (message.type) {
						case MESSAGE_STATUS_QUEUE:
							//document.querySelector("title").textContent = JSON.stringify(message.payload);
							for (let key in message.payload) {
								let val = ""+message.payload[key];
								let query = "*[data-imageCache-info = '" + key + "']";
								let nodeList = document.querySelectorAll(query);
								for (let i = 0; i < nodeList.length; i++) {
									if (nodeList[i].textContent !== val) {
										nodeList[i].textContent = val;
									}
								}
							}
							break;
						case MESSAGE_ERROR:
							if (this.infoElement) {
								this.infoElement.appendChild(document.createTextNode(message.payload + "\n"));
							} else {
								document.querySelector("title").textContent = message.payload;
							}
							break;
						case MESSAGE_IMAGE_FOUND:
							let res = message.payload;
							this.lookupResults[res.url] = res;
							if (this.lookupCallbacks[res.url]) {
								while (this.lookupCallbacks[res.url].length) {
									this.lookupCallbacks[res.url].shift()(res);
								}
								delete this.lookupCallbacks[res.url];
							}
							break;
					}
				}
			},
			false
		);
		this.worker.port.addEventListener(
			"error",
			(eve) => {
				alert(eve);
			},
			false
		);
		this.worker.port.start();
	} catch(e) {
		this.worker = null;
		alert(e);
		console.log(e);
	}
}
ImageCache.prototype = Object.create(
	Object.prototype, {
		worker : { writable : true },
		lookupCallbacks : { writable : true },
		lookupResults : { writable : true },
		infoTemplate : { writable : true },
		infoElement : { writable : true },
		init : {
			value : function() {
				this.infoTemplate = document.querySelector("template#imageCache-info");
				if (this.infoTemplate) {
					let fragment = document.importNode(this.infoTemplate.content, true);
					if (fragment) {
						this.infoElement = fragment.firstChild;
						if (this.infoElement) {
							document.body.appendChild(this.infoElement);
						}
					}
				}
				
				let imageNodeList = window.document.querySelectorAll("img[data-src]");
				let urlNodeMap = {};
				for (let i = 0; i < imageNodeList.length; i++) {
					let imageNode = imageNodeList[i];
					let url = imageNode.getAttribute("data-src");
					imageNode.removeAttribute("data-src");
					imageNode.setAttribute("title", window.location.protocol + "//" + window.location.hostname + url);
					if (!urlNodeMap[url]) {
						urlNodeMap[url] = [];
					}
					urlNodeMap[url].push(imageNode);
				}
				for (let url in urlNodeMap) {
					let urlNodeList = urlNodeMap[url];
					
					this.lookupImage(
						url,
						(res) => {
							let url = res.blobURL
								? res.blobURL
								: res.url;
							for (let i = 0; i < urlNodeList.length; i++) {
								urlNodeList[i].src = url;
							}
						}
					);
				}
			},
		},
		lookupImage : {
			value : function(url, callback) {
				if (this.worker) {
					if (this.lookupResults[url]) {
						callback(this.lookupResults[url]);
					} else {
						if (this.lookupCallbacks[url]) {
							this.lookupCallbacks[url].push(callback);
						} else {
							this.lookupCallbacks[url] = [callback];
							this.worker.port.postMessage(url);
						}
					}
				} else {
					callback({url : url});
				}
			},
		},
		/*
		postImage : {
			value : function(eve) {
				let res = eve.data;
				if (res) {
					this.lookupResults[res.url] = res;
					if (this.lookupCallbacks[res.url]) {
						while (this.lookupCallbacks[res.url].length) {
							this.lookupCallbacks[res.url].shift()(res);
						}
						delete this.lookupCallbacks[res.url];
					}
				}
			},
		},
		//*/
	}
);

window.imageCache = new ImageCache("mtg");

window.addEventListener(
	"DOMContentLoaded",
	function(eve) {
		window.imageCache.init();
	},
	false
);