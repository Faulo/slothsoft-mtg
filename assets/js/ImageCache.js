
const MESSAGE_ERROR = 0;
const MESSAGE_IMAGE_FOUND = 1;
const MESSAGE_STATUS_QUEUE = 2;

import { NS } from "/slothsoft@farah/js/XMLNamespaces";

export default class ImageCache {
    document;
    worker;
    lookupCallbacks = {};
    lookupResults = {};
    infoElement;

    constructor(document) {
        try {
            this.document = document;
            this.infoElement = this.createInfoBox();

            const root = document.body ? document.body : document.documentElement;
            root.appendChild(this.infoElement);

            this.worker = this.createWorker();

            this.enqueueImageRequests();
        } catch (e) {
            this.worker = null;
            this.logError(e);
        }
    }

    createWorker() {
        const worker = new SharedWorker(
            "/slothsoft@mtg/js/ImageCache.Worker",
            { name: "ImageCache", type: "module" }
        );
        worker.port.addEventListener(
            "message",
            (eve) => {
                const message = eve.data;
                if (message) {
                    switch (message.type) {
                        case MESSAGE_STATUS_QUEUE:
                            for (let key in message.payload) {
                                const val = "" + message.payload[key];
                                const query = "*[data-imageCache-info = '" + key + "']";
                                const nodeList = this.document.querySelectorAll(query);
                                for (let i = 0; i < nodeList.length; i++) {
                                    if (nodeList[i].textContent !== val) {
                                        nodeList[i].textContent = val;
                                    }
                                }
                            }
                            break;
                        case MESSAGE_ERROR:
                            this.logError(message.payload);
                            break;
                        case MESSAGE_IMAGE_FOUND:
                            const result = message.payload;
                            this.lookupResults[result.url] = result;
                            if (this.lookupCallbacks[result.url]) {
                                while (this.lookupCallbacks[result.url].length) {
                                    this.lookupCallbacks[result.url].shift()(result);
                                }
                                delete this.lookupCallbacks[result.url];
                            }
                            break;
                    }
                }
            },
            false
        );

        worker.port.addEventListener(
            "error",
            (eve) => {
                this.logError(eve);
            },
            false
        );

        worker.port.start();

        return worker;
    }

    createInfoBox() {
        const infoTemplate = this.document.querySelector("template#imageCache-info");
        if (infoTemplate) {
            const fragment = this.document.importNode(infoTemplate.content, true);
            if (fragment && fragment.firstChild) {
                return fragment.firstChild;
            }
        }
        return this.document.createElementNS(NS.HTML, "pre");
    }

    enqueueImageRequests() {
        const imageNodeList = this.document.querySelectorAll("img[data-src]");
        const urlNodeMap = {};
        for (let i = 0; i < imageNodeList.length; i++) {
            const imageNode = imageNodeList[i];
            const url = imageNode.getAttribute("data-src");
            imageNode.removeAttribute("data-src");
            imageNode.setAttribute("title", window.location.protocol + "//" + window.location.hostname + url);
            if (!urlNodeMap[url]) {
                urlNodeMap[url] = [];
            }
            urlNodeMap[url].push(imageNode);
        }
        for (let url in urlNodeMap) {
            const urlNodeList = urlNodeMap[url];

            this.lookupImage(
                url,
                (res) => {
                    const url = res.blobURL
                        ? res.blobURL
                        : res.url;
                    for (let i = 0; i < urlNodeList.length; i++) {
                        urlNodeList[i].src = url;
                    }
                }
            );
        }
    }

    lookupImage(url, callback) {
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
            callback({ url: url });
        }
    }

    logError(message) {
        console.error(message);
        this.infoElement.appendChild(document.createTextNode(message + "\n"));
    }
}

window.imageCache = new ImageCache(window.document);