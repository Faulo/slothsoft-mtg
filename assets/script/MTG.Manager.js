MTG.Manager = {
	drawParent : undefined,
	playerName : undefined,
	playerURI : undefined,
	playerDoc : undefined,
	repositoryDeck : undefined,
	unusedDeck : undefined,
	leftDeckNo : undefined,
	rightDeckNo : undefined,
	deckList : undefined,
	templateDoc : undefined,
	events : {
		legalityButton : function(eve) {
			this.ownerManager.setCurrentLegality(this.getAttribute("data-select-deck"), this.getAttribute("data-select-index"));
			this.ownerManager.draw();
		},
		typeButton : function(eve) {
			this.ownerManager.setCurrentType(this.getAttribute("data-select-deck"), this.getAttribute("data-select-index"));
			this.ownerManager.draw();
		},
		colorButton : function(eve) {
			this.ownerManager.setCurrentColor(this.getAttribute("data-select-deck"), this.getAttribute("data-select-index"));
			this.ownerManager.draw();
		},
		rarityButton : function(eve) {
			this.ownerManager.setCurrentRarity(this.getAttribute("data-select-deck"), this.getAttribute("data-select-index"));
			this.ownerManager.draw();
		},
		dragmousedown : function(eve) {
			this.setAttribute("data-status", "inuse");
		},
		dragmouseup : function(eve) {
			this.removeAttribute("data-status");
		},
		dragstart : function(eve) {
			var data;
			this.ownerManager.drawParent.setAttribute("data-status", "");
			
			data = {};
			data.cardName = this.getAttribute("data-card-name");
			data.deckNo = XPath.evaluate("string(ancestor::*[@data-deck-no]/@data-deck-no)", this);
			eve.dataTransfer.setData("text", JSON.stringify(data));
		},
		dragend : function(eve) {
			this.ownerManager.drawParent.removeAttribute("data-status");
			this.removeAttribute("data-status");
		},
		dragover : function(eve) {
			eve.preventDefault();
		},
		drop : function(eve) {
			var data, cardName, sourceNo, targetNo, manager;
			eve.preventDefault();
			
			try {
				manager = this.ownerManager;
				
				manager.drawParent.setAttribute("data-status", "busy");
				
				data = eve.dataTransfer.getData("text");
				data = JSON.parse(data);
				cardName = data.cardName;
				sourceNo = data.deckNo;
				targetNo = this.getAttribute("data-deck-no");
				
				manager.switchCard(cardName, sourceNo, targetNo);
				
				manager.drawParent.removeAttribute("data-status");
			} catch(e) {
				alert(e);
			}
		},
	},
	init : function(playerName, parentNode) {
		this.drawParent = parentNode;
		this.playerName = playerName;
		this.playerURI = "/getData.php/mtg/manager?player=" + this.playerName;
		this.switchURI = "/getData.php/mtg/manager?deck-switch=1&player=" + this.playerName;
		this.templateDoc = DOM.loadDocument("/getTemplate.php/mtg/manager-deck");
		this.initDecks(DOM.loadDocument(this.playerURI));
	},
	initDecks : function(playerDoc) {
		var nodeList, node, i, deck, serializeList;
		if (playerDoc) {
			this.playerDoc = playerDoc;
		}
		serializeList = [];
		if (this.deckList) {
			for (i in this.deckList) {
				serializeList.push(this.deckList[i].serialize());
			}
		}
		
		node = XPath.evaluate(".//deck[@type = 'repository']", this.playerDoc)[0];
		if (!node) {
			throw "no repository deck found! D:";
		}
		this.repositoryDeck = new MTG.Deck(this, node);
		this.repositoryDeck.backupStock();
		
		node = XPath.evaluate(".//deck[@type = 'unused']", this.playerDoc)[0];
		if (!node) {
			throw "no unused deck found! D:";
		}
		this.unusedDeck = new MTG.Deck(this, node);
		
		this.deckList = {};
		this.deckList[this.repositoryDeck.getNo()] = this.repositoryDeck;
		this.deckList[this.unusedDeck.getNo()] = this.unusedDeck;
		nodeList = XPath.evaluate(".//deck[@type = 'managed']", this.playerDoc);
		for (i = 0; i < nodeList.length; i++) {
			node = nodeList[i];
			deck = new MTG.Deck(this, node, false);
			this.repositoryDeck.subtractStock(deck);
			this.deckList[deck.getNo()] = deck;
			
			deck = new MTG.Deck(this, node, true);
			this.repositoryDeck.subtractStock(deck);
			this.deckList[deck.getNo()] = deck;
		}
		
		if (!this.leftDeckNo) {
			this.leftDeckNo = this.repositoryDeck.getNo();
		}
		
		for (i in this.deckList) {
			if (!this.leftDeckNo) {
				this.leftDeckNo = i;
			} else {
				if (!this.rightDeckNo) {
					this.rightDeckNo = i;
				} else {
					break;
				}
			}
		}
		
		if (serializeList) {
			for (i in this.deckList) {
				if (serializeList.length) {
					this.deckList[i].unserialize(serializeList.shift());
				}
			}
		}
		
		this.draw();
	},
	asNode : function(dataDoc) {
		var retNode, deck, i, parentNode, node;
		if (dataDoc) {
			retNode = dataDoc.createElement("manager");
		} else {
			dataDoc = document.implementation.createDocument(null, "manager", null);
			retNode = dataDoc.documentElement;
		}
		
		//node = this.repositoryDeck.asHTML(dataDoc);
		//parentNode = dataDoc.createElement("repository");
		//parentNode.appendChild(node);
		//parentNode = this.repositoryDeck.asNode(dataDoc);
		//parentNode.setAttribute("repository", "");
		//parentNode.setAttribute("no", this.repositoryDeck.getNo());
		
		//retNode.appendChild(parentNode);
		
		//retNode.appendChild(this.getTypeList(dataDoc));
		//retNode.appendChild(this.getColorList(dataDoc));
		
		for (i in this.deckList) {
			deck = this.deckList[i];
			parentNode = deck.asNode(dataDoc);
			parentNode.setAttribute("index", i);
			parentNode.setAttribute("no", deck.getNo());
			parentNode.setAttribute("name", deck.getName());
			parentNode.setAttribute("stock", deck.getStock());
			parentNode.setAttribute("uri", deck.getURI());
			if (i === this.leftDeckNo) {
				parentNode.setAttribute("manager", "left");
			}
			if (i === this.rightDeckNo) {
				parentNode.setAttribute("manager", "right");
			}
			retNode.appendChild(parentNode);
			//alert(deck.getName() + "\n" + retNode.getElementsByTagName("deck").length);
		}
		
		
		return retNode;
	},
	asHTML : function(targetDoc) {
		var dataNode, templateDoc;
		dataNode = this.asNode();
		return XSLT.transformToNode(dataNode, this.templateDoc, targetDoc);
	},
	setCurrentLegality : function(deckNo, index) {
		//index = parseInt(index);
		if (this.deckList[deckNo]) {
			this.deckList[deckNo].setCurrentLegality(index);
		}
	},
	setCurrentType : function(deckNo, index) {
		//index = parseInt(index);
		if (this.deckList[deckNo]) {
			this.deckList[deckNo].setCurrentType(index);
		}
	},
	setCurrentColor : function(deckNo, index) {
		//index = parseInt(index);
		if (this.deckList[deckNo]) {
			this.deckList[deckNo].setCurrentColor(index);
		}
	},
	setCurrentRarity : function(deckNo, index) {
		//index = parseInt(index);
		if (this.deckList[deckNo]) {
			this.deckList[deckNo].setCurrentRarity(index);
		}
	},
	draw : function() {
		var node, nodeList, i;
		while (this.drawParent.hasChildNodes()) {
			this.drawParent.removeChild(this.drawParent.lastChild);
		}
		if (node = this.asHTML(this.drawParent.ownerDocument)) {
			this.drawParent.appendChild(node);
		}
		
		nodeList = XPath.evaluate(".//*[@data-card-name]", this.drawParent);
		for (i = 0; i < nodeList.length; i++) {
			node = nodeList[i];
			node.ownerManager = this;
			node.setAttribute("draggable", "true");
			node.addEventListener(
				"dragstart",
				this.events.dragstart,
				false
			);
			node.addEventListener(
				"dragend",
				this.events.dragend,
				false
			);
			node.addEventListener(
				"touchstart",
				this.events.dragmousedown,
				false
			);
			node.addEventListener(
				"touchend",
				this.events.dragmouseup,
				false
			);
		}
		nodeList = XPath.evaluate(".//*[@data-deck-no]", this.drawParent);
		for (i = 0; i < nodeList.length; i++) {
			node = nodeList[i];
			node.ownerManager = this;
			node.setAttribute("data-status", "clear");
			node.addEventListener(
				"dragover",
				this.events.dragover,
				false
			);
			node.addEventListener(
				"drop",
				this.events.drop,
				false
			);
		}
		nodeList = XPath.evaluate(".//*[@data-select = 'legality']/*", this.drawParent);
		for (i = 0; i < nodeList.length; i++) {
			node = nodeList[i];
			node.ownerManager = this;
			node.addEventListener(
				"click",
				this.events.legalityButton,
				false
			);
		}
		nodeList = XPath.evaluate(".//*[@data-select = 'type']/*", this.drawParent);
		for (i = 0; i < nodeList.length; i++) {
			node = nodeList[i];
			node.ownerManager = this;
			node.addEventListener(
				"click",
				this.events.typeButton,
				false
			);
		}
		nodeList = XPath.evaluate(".//*[@data-select = 'color']/*", this.drawParent);
		for (i = 0; i < nodeList.length; i++) {
			node = nodeList[i];
			node.ownerManager = this;
			node.addEventListener(
				"click",
				this.events.colorButton,
				false
			);
		}
		nodeList = XPath.evaluate(".//*[@data-select = 'rarity']/*", this.drawParent);
		for (i = 0; i < nodeList.length; i++) {
			node = nodeList[i];
			node.ownerManager = this;
			node.addEventListener(
				"click",
				this.events.rarityButton,
				false
			);
		}
		
		window.dispatchEvent(new Event("DOMContentLoaded"));
	},
	setCurrentDeck : function(position, deckNo) {
		//deckNo = parseInt(deckNo);
		if (this.deckList[deckNo]) {
			switch (position) {
				case "left":
					this.leftDeckNo = deckNo;
					break;
				case "right":
					this.rightDeckNo = deckNo;
					break;
			} 
			this.draw();
		}
	},
	switchCard : function(cardName, sourceNo, targetNo) {
		var data, deck, saveStack = [], doc;
		//sourceNo = parseInt(sourceNo);
		//targetNo = parseInt(targetNo);
		//alert([sourceNo, targetNo, this.repositoryNo, sourceNo === this.repositoryNo, targetNo === this.repositoryNo]);
		if (cardName && sourceNo && targetNo) {
			if (sourceNo !== targetNo) {
				if (this.deckList[sourceNo] && this.deckList[targetNo]) {
					deck = this.deckList[sourceNo];
					if (deck !== this.repositoryDeck && deck !== this.unusedDeck) {
						data = {};
						data.cardName = cardName;
						data.deckNo = deck.getNo();
						data.stock = -1;
						
						saveStack.push(data);
					}
					
					deck = this.deckList[targetNo];
					if (deck !== this.repositoryDeck && deck !== this.unusedDeck) {
						data = {};
						data.cardName = cardName;
						data.deckNo = deck.getNo();
						data.stock = 1;
						
						saveStack.push(data);
					}
					
					if (saveStack) {
						this.initDecks(DOM.saveDocument(this.switchURI, JSON.stringify(saveStack)));
					}
				}
			}
		}
	},
};