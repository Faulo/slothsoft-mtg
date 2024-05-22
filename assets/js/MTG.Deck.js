MTG.Deck = function(ownerManager, deckNode, isSideboard) {
	var nodeList, i, node;
	this.ownerManager = ownerManager;
	this.isSideboard = !!isSideboard;
	this.node = this.isSideboard
		? deckNode.cloneNode(true)
		: deckNode;
	this.sideboardNode = XPath.evaluate("sideboard", this.node)[0];
	this.categoryNode = XPath.evaluate("categories", this.node)[0];
	
	if (isSideboard) {
		nodeList = XPath.evaluate("card", this.node);
		for (i = 0; i < nodeList.length; i++) {
			node = nodeList[i];
			node.parentNode.removeChild(node);
		}
		while (this.sideboardNode.hasChildNodes()) {
			this.node.appendChild(this.sideboardNode.firstChild);
		}
	}
	
	this.legalityList = {};
	this.typeList = {};
	this.colorList = {};
	this.rarityList = {};
	if (this.categoryNode) {
		nodeList = XPath.evaluate("legality", this.categoryNode);
		for (i = 0; i < nodeList.length; i++) {
			node = nodeList[i];
			this.legalityList[node.textContent] = node;
		}
		nodeList = XPath.evaluate("type", this.categoryNode);
		for (i = 0; i < nodeList.length; i++) {
			node = nodeList[i];
			this.typeList[node.textContent] = node;
		}
		if (!this.isSideboard) {
			if (this.typeList.Land) {
				this.typeList.Land.setAttribute("current", "");
			}
		}
		nodeList = XPath.evaluate("color", this.categoryNode);
		for (i = 0; i < nodeList.length; i++) {
			node = nodeList[i];
			this.colorList[node.textContent] = node;
		}
		nodeList = XPath.evaluate("rarity", this.categoryNode);
		for (i = 0; i < nodeList.length; i++) {
			node = nodeList[i];
			this.rarityList[node.textContent] = node;
		}
	}
};
MTG.Deck.prototype.ownerManager = undefined;
MTG.Deck.prototype.node = undefined;
MTG.Deck.prototype.categoryNode = undefined;
MTG.Deck.prototype.typeList = undefined;
MTG.Deck.prototype.colorList = undefined;
MTG.Deck.prototype.rarityList = undefined;

MTG.Deck.prototype.getNo = function() {
	//return parseInt(this.node.getAttribute("no"));
	return this.isSideboard
		? "-" + this.node.getAttribute("no")
		: this.node.getAttribute("no");
};
MTG.Deck.prototype.getName = function() {
	var ret = "";
	if (parseInt(this.node.getAttribute("no"))) {
		ret += "[" + ("00" + this.node.getAttribute("no")).slice(-2) + "] ";
	}
	ret += this.node.getAttribute("name");
	if (this.isSideboard) {
		ret += " - Sideboard";
	}
	return ret;
};
MTG.Deck.prototype.getURI = function() {
	return this.isSideboard
		? "../" + this.node.getAttribute("no") + "/view/"
		: "../" + this.node.getAttribute("no") + "/view/";
};
MTG.Deck.prototype.getStock = function() {
	return this.isSideboard
		? XPath.evaluate("sum(card/@stock)", this.node)
		: XPath.evaluate("sum(card/@stock)", this.node);
};
MTG.Deck.prototype.asNode = function(dataDoc) {
	var retNode, nodeList, node, i, query;
	node = this.node;
		
	if (dataDoc) {
		retNode = dataDoc.importNode(node, true);
	} else {
		dataDoc = node.ownerDocument;
		retNode = node;
	}
	
	query = "card";
	
	for (i in this.legalityList) {
		node = this.legalityList[i];
		if (node.hasAttribute("current")) {
			query += "['" + i + "' = legality]";
		}
	}
	for (i in this.typeList) {
		node = this.typeList[i];
		if (node.hasAttribute("current")) {
			query += "['" + i + "' = @type]";
		}
	}
	for (i in this.colorList) {
		node = this.colorList[i];
		if (node.hasAttribute("current")) {
			query += "['" + i + "' = color]";
			if (i === "Colorless") {
				query += "[count(color) = 1]";
			}
		}
	}
	for (i in this.rarityList) {
		node = this.rarityList[i];
		if (node.hasAttribute("current")) {
			query += "['" + i + "' = @rarity]";
		}
	}
	
	nodeList = XPath.evaluate(query, retNode);
	for (i = 0; i < nodeList.length; i++) {
		node = nodeList[i];
		node.setAttribute("active", "");
	}
	return retNode;
};
MTG.Deck.prototype.asHTML = function(targetDoc) {
	var retNode, dataNode, templateDoc;
	dataNode = this.asNode();
	templateDoc = this.ownerManager.deckTemplateDoc;
	retNode = XSLT.transformToNode(dataNode, templateDoc, targetDoc);
	
	return retNode;
};
MTG.Deck.prototype.getCardList = function() {
	var ret, nodeList, i;
	ret = {};
	nodeList = XPath.evaluate("card", this.node);
	for (i = 0; i < nodeList.length; i++) {
		ret[nodeList[i].getAttribute("name")] = nodeList[i];
	}
	return ret;
};
MTG.Deck.prototype.setCurrentLegality = function(index) {
	var nodeList, node, i;
	nodeList = this.legalityList;
	for (i in nodeList) {
		node = nodeList[i];
		if (i === index) {
			node.setAttribute("current", "");
		} else {
			node.removeAttribute("current");
		}
	}
};
MTG.Deck.prototype.getCurrentLegality = function() {
	var nodeList, node, i;
	nodeList = this.legalityList;
	for (i in nodeList) {
		node = nodeList[i];
		if (node.hasAttribute("current")) {
			return i;
		}
	}
	return "";
};
MTG.Deck.prototype.setCurrentType = function(index) {
	var nodeList, node, i;
	nodeList = this.typeList;
	for (i in nodeList) {
		node = nodeList[i];
		if (i === index) {
			node.setAttribute("current", "");
		} else {
			node.removeAttribute("current");
		}
	}
};
MTG.Deck.prototype.getCurrentType = function() {
	var nodeList, node, i;
	nodeList = this.typeList;
	for (i in nodeList) {
		node = nodeList[i];
		if (node.hasAttribute("current")) {
			return i;
		}
	}
	return "";
};
MTG.Deck.prototype.setCurrentColor = function(index) {
	var nodeList, node, i;
	nodeList = this.colorList;
	for (i in nodeList) {
		node = nodeList[i];
		if (i === index) {
			node.setAttribute("current", "");
		} else {
			node.removeAttribute("current");
		}
	}
};
MTG.Deck.prototype.getCurrentColor = function() {
	var nodeList, node, i;
	nodeList = this.colorList;
	for (i in nodeList) {
		node = nodeList[i];
		if (node.hasAttribute("current")) {
			return i;
		}
	}
	return "";
};
MTG.Deck.prototype.setCurrentRarity = function(index) {
	var nodeList, node, i;
	nodeList = this.rarityList;
	for (i in nodeList) {
		node = nodeList[i];
		if (i === index) {
			node.setAttribute("current", "");
		} else {
			node.removeAttribute("current");
		}
	}
};
MTG.Deck.prototype.getCurrentRarity = function() {
	var nodeList, node, i;
	nodeList = this.rarityList;
	for (i in nodeList) {
		node = nodeList[i];
		if (node.hasAttribute("current")) {
			return i;
		}
	}
	return "";
};
MTG.Deck.prototype.subtractStock = function(deck) {
	var thisCardList, otherCardList, name, stock;
	thisCardList = this.getCardList();
	otherCardList = deck.getCardList();
	for (name in thisCardList) {
		if (otherCardList[name]) {
			stock = parseInt(thisCardList[name].getAttribute("stock"));
			stock -= parseInt(otherCardList[name].getAttribute("stock"));
			thisCardList[name].setAttribute("stock", stock);
		}
	}
};
MTG.Deck.prototype.backupStock = function() {
	var thisCardList, name;
	thisCardList = this.getCardList();
	for (name in thisCardList) {
		thisCardList[name].setAttribute("whole-stock", thisCardList[name].getAttribute("stock"));
	}
};
MTG.Deck.prototype.serialize = function() {
	return [this.getCurrentType(), this.getCurrentRarity(), this.getCurrentColor(), this.getCurrentLegality()];
};
MTG.Deck.prototype.unserialize = function(data) {
	this.setCurrentType(data[0]);
	this.setCurrentRarity(data[1]);
	this.setCurrentColor(data[2]);
	this.setCurrentLegality(data[3]);
};