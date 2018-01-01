<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl"
	xmlns:func="http://exslt.org/functions"
	xmlns:str="http://exslt.org/strings"
	xmlns:mtg="http://slothsoft.net/MTG/"
	extension-element-prefixes="php func str mtg">
	
	<xsl:variable name="rootPage" select="(/data/*[@data-cms-name='/core/sites']//*[@current])[1]"/>
	
	<func:function name="mtg:card-has-colors">
		<xsl:param name="card"/>
		<xsl:param name="colors" select="'00000'"/>
		
		<xsl:variable name="c" select="str:tokenize($card/@colors, '')"/><!--cardColors-->
		<xsl:variable name="t" select="str:tokenize($colors, '')"/><!--testColors-->
		
		<func:result select="$t[1]+$c[1]=2 or $t[2]+$c[2]=2 or $t[3]+$c[3]=2 or $t[4]+$c[4]=2 or $t[5]+$c[5]=2"/>
	</func:function>
	
	<func:function name="mtg:card-has-all-colors">
		<xsl:param name="card"/>
		<xsl:param name="colors" select="'00000'"/>
		
		<xsl:variable name="c" select="str:tokenize($card/@colors, '')"/><!--cardColors-->
		<xsl:variable name="t" select="str:tokenize($colors, '')"/><!--testColors-->
		
		<func:result select="($t[1]='0' or $c[1]='1') and ($t[2]='0' or $c[2]='1') and ($t[3]='0' or $c[3]='1') and ($t[4]='0' or $c[4]='1') and ($t[5]='0' or $c[5]='1')"/>
	</func:function>
	
	<func:function name="mtg:card-is-colors">
		<xsl:param name="card"/>
		<xsl:param name="colors" select="'00000'"/>
		
		<xsl:variable name="c" select="str:tokenize($card/@colors, '')"/><!--cardColors-->
		<xsl:variable name="t" select="str:tokenize($colors, '')"/><!--testColors-->
		
		<func:result select="$t[1]=$c[1] and $t[2]=$c[2] and $t[3]=$c[3] and $t[4]=$c[4] and $t[5]=$c[5]"/>
	</func:function>
	
	<func:function name="mtg:encode-uri">
		<xsl:param name="text"/>
		
		<func:result select="$text"/>
	</func:function>
	
	<func:function name="mtg:cards-by-expansion">
		<xsl:param name="expansion"/>
		
		<func:result select="php:functionString('MTG\OracleXSLT::cardsByExpansion', $expansion)"/>
	</func:function>
	
	<xsl:template name="search.internal">
		<xsl:param name="expansion" select="false()"/>
		<xsl:text>http://localhost/getData.php/mtg/oracle?dnt</xsl:text>
		<xsl:if test="$expansion">
			<xsl:text></xsl:text>
			<xsl:value-of select="concat(
				'&amp;',
				mtg:encode-uri('search-query[expansion_name]'),
				'=',
				mtg:encode-uri($expansion)
			)"/>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="search.external">
		<xsl:param name="expansion" select="false()"/>
		<xsl:text>http://mtg.slothsoft.net/</xsl:text>
		<xsl:if test="$expansion">
			<xsl:text></xsl:text>
			<xsl:value-of select="concat(
				'?',
				mtg:encode-uri('search-query[expansion_name]'),
				'=',
				mtg:encode-uri($expansion)
			)"/>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="sites.head">
		<head>
			<title>
				<xsl:for-each select="$rootPage/ancestor-or-self::*[@title]">
					<xsl:sort select="position()" order="descending"/>
					<xsl:if test="position() &gt; 1">
						<xsl:text> - </xsl:text>
					</xsl:if>
					<span data-dict="."><xsl:value-of select="@title"/></span>
				</xsl:for-each>
			</title>
			<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes"/>
			<template id="imageCache-info">
				<div class="imageCache-info">
					<u>Image Cache</u>
					<div>
						<code><span data-imageCache-info="finishedRequests">-</span>/<span data-imageCache-info="totalRequests">-</span></code>
					</div>
					<!--<div>Image lookups: <code data-imageCache-info="imageLookups">-</code></div>-->
					<div>
						<small>
							<xsl:text>new: </xsl:text>
							<code data-imageCache-info="newImages">-</code>
						</small>
					</div>
					<div>
						<small>
							<xsl:text>unchanged: </xsl:text>
							<code data-imageCache-info="unchangedImages">-</code>
						</small>
					</div>
					<div>
						<small>
							<xsl:text>deleted: </xsl:text>
							<code data-imageCache-info="deletedImages">-</code>
						</small>
					</div>
					<div>
						<small>
							<xsl:text>errors: </xsl:text>
							<code data-imageCache-info="errors">-</code>
						</small>
					</div>
				</div>
			</template>
		</head>
	</xsl:template>
	
	<xsl:template name="sites.navi">
		<!--
			<xsl:apply-templates select="$rootPage/ancestor::page[last()]" mode="navi"/>
		-->
		<header style="display: none;">
			<xsl:for-each select="$rootPage/ancestor-or-self::page[page]">
				<nav>
					<details>
						<summary data-dict=""><xsl:value-of select=".//*[descendant-or-self::*[@current]]/@title"/></summary>
						<div>
							<ul>
								<xsl:for-each select="page[@status-public]">
									<li>
										<xsl:if test="descendant-or-self::*[@current]">
											<xsl:attribute name="class">active</xsl:attribute>
										</xsl:if>
										<a href="{@uri}" data-dict="">
											<xsl:value-of select="@title"/>
										</a>
									</li>
								</xsl:for-each>
							</ul>
						</div>
					</details>
					<!--
					<div class="details" tabindex="0">
						<div class="summary" data-dict=""><xsl:value-of select=".//*[descendant-or-self::*[@current]]/@title"/></div>
						<ul>
							<xsl:for-each select="page">
								<li>
									<xsl:if test="descendant-or-self::*[@current]">
										<xsl:attribute name="class">active</xsl:attribute>
									</xsl:if>
									<a href="{@uri}" data-dict="">
										<xsl:value-of select="@title"/>
									</a>
								</li>
							</xsl:for-each>
						</ul>
					</div>
					-->
				</nav>
			</xsl:for-each>
		</header>
		<!--
		<div class="header">
			<xsl:for-each select="$rootPage/ancestor-or-self::page[page]">
				<nav>
					<xsl:for-each select="page">
						<a href="{@uri}" data-dict="">
							<xsl:if test="descendant-or-self::*[@current]">
								<xsl:attribute name="class">active</xsl:attribute>
							</xsl:if>
							<xsl:value-of select="@title"/>
						</a>
					</xsl:for-each>
				</nav>
			</xsl:for-each>
		</div>
		-->
	</xsl:template>
	
	<xsl:template match="*[page]" mode="navi">
		<header>
			<nav>
				<xsl:for-each select="page">
					<a href="{@uri}">
						<xsl:if test="descendant-or-self::*[@current]">
							<xsl:attribute name="class">active</xsl:attribute>
						</xsl:if>
						<xsl:value-of select="@title"/>
					</a>
				</xsl:for-each>
			</nav>
			<xsl:apply-templates select="*[descendant-or-self::*[@current]]" mode="navi"/>
		</header>
	</xsl:template>
	
	<xsl:template name="deck">
		<xsl:param name="deck" select="deck"/>
		<xsl:param name="mode" select="'default'"/>
		<xsl:for-each select="$deck">
			<div class="deck" data-mode="{$mode}">
				<xsl:choose>
					<xsl:when test="$mode = 'default'">
						<xsl:call-template name="deck.mana"/>
						<xsl:call-template name="categoryList">
							<xsl:with-param name="sortList" select="card/@type"/>
							<xsl:with-param name="categoryList" select="categories/type"/>
							<xsl:with-param name="mode" select="$mode"/>
							<xsl:with-param name="open" select="sum(.//card/@stock) &lt;= 100"/>
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="$mode = 'color'">
						<xsl:call-template name="categoryList">
							<xsl:with-param name="sortList" select="card/color"/>
							<xsl:with-param name="categoryList" select="categories/color"/>
							<xsl:with-param name="mode" select="$mode"/>
							<xsl:with-param name="open" select="sum(.//card/@stock) &lt;= 100"/>
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="$mode = 'rarity'">
						<xsl:call-template name="categoryList">
							<xsl:with-param name="sortList" select="card/@rarity"/>
							<xsl:with-param name="categoryList" select="categories/rarity"/>
							<xsl:with-param name="mode" select="$mode"/>
							<xsl:with-param name="open" select="sum(.//card/@stock) &lt;= 100"/>
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="$mode = 'expansion'">
						<xsl:call-template name="categoryList">
							<xsl:with-param name="sortList" select=".//card/set"/>
							<xsl:with-param name="categoryList" select="categories/set"/>
							<xsl:with-param name="mode" select="$mode"/>
							<xsl:with-param name="open" select="sum(.//card/@stock) &lt;= 100"/>
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="$mode = 'legality'">
						<xsl:call-template name="categoryList">
							<xsl:with-param name="sortList" select="card/legality"/>
							<xsl:with-param name="categoryList" select="categories/legality"/>
							<xsl:with-param name="mode" select="$mode"/>
							<xsl:with-param name="open" select="sum(.//card/@stock) &lt;= 100"/>
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="$mode = 'edit'">
						<xsl:call-template name="categoryList">
							<xsl:with-param name="sortList" select="card/@type"/>
							<xsl:with-param name="categoryList" select="categories/type"/>
							<xsl:with-param name="mode" select="$mode"/>
							<xsl:with-param name="open" select="sum(.//card/@stock) &lt;= 100"/>
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="$mode = 'search'">
						<xsl:call-template name="categoryList">
							<xsl:with-param name="sortList" select="card/@type"/>
							<xsl:with-param name="categoryList" select="../deck/categories/type"/>
							<xsl:with-param name="mode" select="$mode"/>
							<xsl:with-param name="open" select="count(.//card) &lt;= 500"/>
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="$mode = 'filter'">
						<xsl:call-template name="categoryList">
							<xsl:with-param name="sortList" select="card/@type"/>
							<xsl:with-param name="categoryList" select="../deck/categories/type"/>
							<xsl:with-param name="mode" select="$mode"/>
							<xsl:with-param name="open" select="count(.//card) &lt;= 500"/>
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="$mode = 'oracle'">
						<xsl:call-template name="categoryList">
							<xsl:with-param name="sortList" select="card/@type"/>
							<xsl:with-param name="categoryList" select="../categories/type"/>
							<xsl:with-param name="mode" select="'view'"/>
							<xsl:with-param name="open" select="count(.//card) &lt;= 500"/>
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="$mode = 'booster'">
						<xsl:call-template name="categoryList">
							<xsl:with-param name="sortList" select="card/@rarity"/>
							<xsl:with-param name="categoryList" select="../categories/rarity"/>
							<xsl:with-param name="mode" select="'view'"/>
							<xsl:with-param name="open" select="true()"/>
						</xsl:call-template>
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template name="categoryList">
		<xsl:param name="categoryList"/>
		<xsl:param name="sortList"/>
		<xsl:param name="mode"/>
		<xsl:param name="open" select="false()"/>
		<xsl:for-each select="$categoryList">
			<xsl:variable name="name" select="."/>
			<xsl:choose>
				<xsl:when test="$name = 'Colorless'">
					<xsl:variable name="cardList" select="$sortList[. = $name]/parent::card[count(color) = 1]"/>
					<xsl:if test="count($cardList)">
						<details class="category" data-category="{$name}">
							<summary>
								<xsl:call-template name="cardList.title">
									<xsl:with-param name="title" select="$name"/>
									<xsl:with-param name="cardList" select="$cardList"/>
								</xsl:call-template>
							</summary>
							<xsl:call-template name="cardList">
								<xsl:with-param name="cardList" select="$cardList"/>
								<xsl:with-param name="mode" select="$mode"/>
								<xsl:with-param name="open" select="$open"/>
							</xsl:call-template>
						</details>
					</xsl:if>
				</xsl:when>
				<xsl:otherwise>
					<xsl:variable name="cardList" select="$sortList[. = $name]/.."/>
					<xsl:if test="count($cardList)">
						<details class="category" data-category="{$name}">
							<xsl:if test="$open">
								<xsl:attribute name="open">open</xsl:attribute>
							</xsl:if>
							<summary>
								<xsl:call-template name="cardList.title">
									<xsl:with-param name="title" select="$name"/>
									<xsl:with-param name="cardList" select="$cardList"/>
								</xsl:call-template>
							</summary>
							<xsl:call-template name="cardList">
								<xsl:with-param name="cardList" select="$cardList"/>
								<xsl:with-param name="mode" select="$mode"/>
								<xsl:with-param name="open" select="$open"/>
							</xsl:call-template>
						</details>
					</xsl:if>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
		
		<xsl:if test="count(sideboard/card)">
			<xsl:variable name="name" select="'Sideboard'"/>
			<xsl:variable name="cardList" select="sideboard/card"/>
			<details class="category" data-category="{$name}">
				<summary>
					<xsl:call-template name="cardList.title">
						<xsl:with-param name="title" select="$name"/>
						<xsl:with-param name="cardList" select="$cardList"/>
					</xsl:call-template>
				</summary>
				<xsl:call-template name="cardList">
					<xsl:with-param name="cardList" select="$cardList"/>
					<xsl:with-param name="mode" select="$mode"/>
					<xsl:with-param name="open" select="$open"/>
				</xsl:call-template>
			</details>
		</xsl:if>
		
		<xsl:if test="count(.//card)">
			<xsl:variable name="cardList" select=".//card"/>
			<details class="category" data-category="Export">
				<summary>
					<xsl:call-template name="cardList.title">
						<xsl:with-param name="title" select="'Export'"/>
						<xsl:with-param name="cardList" select="$cardList"/>
					</xsl:call-template>
				</summary>
				<xsl:if test="$cardList[not(parent::sideboard)]">
					<xsl:call-template name="cardList">
						<xsl:with-param name="cardList" select="$cardList[not(parent::sideboard)]"/>
						<xsl:with-param name="mode" select="'export'"/>
						<xsl:with-param name="open" select="$open"/>
					</xsl:call-template>
				</xsl:if>
				<xsl:if test="$cardList[parent::sideboard]">
					<xsl:call-template name="cardList">
						<xsl:with-param name="cardList" select="$cardList[parent::sideboard]"/>
						<xsl:with-param name="mode" select="'export'"/>
						<xsl:with-param name="open" select="$open"/>
					</xsl:call-template>
				</xsl:if>
			</details>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="cardList.title">
		<xsl:param name="title"/>
		<xsl:param name="cardList"/>
		
		<xsl:value-of select="$title"/>
		<xsl:choose>
			<xsl:when test="$cardList/@stock != 0">
				<xsl:value-of select="concat(' (', count($cardList), ' cards, ', sum($cardList/@stock), ' copies)')"/>
			</xsl:when>
			<xsl:when test="count($cardList) > 0">
				<xsl:value-of select="concat(' (', count($cardList), ' cards)')"/>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="cardList">
		<xsl:param name="cardList"/>
		<xsl:param name="mode"/>
		<xsl:param name="open" select="false()"/>
		<xsl:choose>
			<xsl:when test="$mode = 'export'">
				<textarea cols="80" rows="{count($cardList) + 1}">
					<xsl:for-each select="$cardList">
						<xsl:variable name="name">
							<xsl:choose>
								<xsl:when test="substring-after(@name, ' &amp; ')">
									<xsl:value-of select="substring-before(@name, ' &amp; ')"/>
									<xsl:text> // </xsl:text>
									<xsl:value-of select="substring-after(@name, ' &amp; ')"/>
									<!--
									<xsl:text> (</xsl:text>
									<xsl:value-of select="normalize-space(substring-before(@name, '&amp;'))"/>
									<xsl:text>)</xsl:text>
									-->
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="@name"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
						
						<xsl:choose>
							<xsl:when test="@stock">
								<xsl:value-of select="concat(format-number(@stock, '00'), ' ', $name)"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="concat(format-number(1, '00'), ' ', $name)"/>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:text>
</xsl:text>
					</xsl:for-each>
				</textarea>
			</xsl:when>
			<xsl:otherwise>
				<ul class="cardList">
					<xsl:for-each select="$cardList">
						<xsl:sort select="@sort"/>
						<xsl:sort select="@name"/>
						<li>
							<xsl:call-template name="card">
								<xsl:with-param name="card" select="."/>
								<xsl:with-param name="mode" select="$mode"/>
								<xsl:with-param name="open" select="$open"/>
							</xsl:call-template>
						</li>
					</xsl:for-each>
				</ul>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="card">
		<xsl:param name="card"/>
		<xsl:param name="mode"/>
		<xsl:param name="open" select="false()"/>
		<xsl:for-each select="$card">
			<article data-card-rarity="{@rarity}" data-card-name="{@name}" data-card-stock="{@stock}">
				<!--
				<xsl:variable name="colorList" select="color[. != 'Colorless']"/>
				<xsl:if test="count($colorList)">
					<xsl:attribute name="style">
						<xsl:text>background: linear-gradient(to right</xsl:text>
						<xsl:for-each select="$colorList">
							<xsl:value-of select="concat(', var(', ., ')')"/>
						</xsl:for-each>
						<xsl:if test="count($colorList) = 1">
							<xsl:value-of select="concat(', var(', $colorList[1], ')')"/>
						</xsl:if>
						<xsl:text>)</xsl:text>
					</xsl:attribute>
				</xsl:if>
				-->
				<xsl:call-template name="card.button">
					<xsl:with-param name="open" select="$open"/>
				</xsl:call-template>
				<xsl:call-template name="card.name"/>
				<xsl:call-template name="card.actions">
					<xsl:with-param name="mode" select="$mode"/>
				</xsl:call-template>
			</article>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template name="card.button">
		<xsl:param name="card" select="."/>
		<xsl:param name="open" select="false()"/>
		<xsl:for-each select="$card">
			<img alt="{@name}&#10;{@type}&#10;{@description}" data-src="{@href-image}">
				<!--
				data-src="/getData.php/mtg/image-card?name={mtg:encode-uri(@name)}"
				<xsl:if test="$open or true()">
					<xsl:attribute name="src"><xsl:value-of select="@href-image"/></xsl:attribute>
				</xsl:if>
				-->
				<!--
				<xsl:attribute name="data-src"><xsl:value-of select="@href-image"/></xsl:attribute>
				<xsl:attribute name="onmouseenter">if (!this.src) this.src = this.getAttribute('data-src');</xsl:attribute>
				-->
				<xsl:attribute name="onclick">if (this.parentNode.hasAttribute('data-clicked')) this.parentNode.removeAttribute('data-clicked'); else this.parentNode.setAttribute('data-clicked', '');</xsl:attribute>
			</img>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template name="card.name">
		<xsl:param name="card" select="."/>
		<xsl:for-each select="$card">
			<h3>
				<xsl:call-template name="card.href"/>
			</h3>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template name="card.href">
		<xsl:choose>
			<xsl:when test="@href-oracle">
				<a href="{@href-oracle}" target="_blank"><xsl:value-of select="@name"/></a>
			</xsl:when>
			<xsl:otherwise>
				<span><xsl:value-of select="@name"/></span>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="card.price">
		<xsl:choose>
			<xsl:when test="@href-price">
				<a class="button" href="{@href-price}" target="_blank" title="{@price}">💰</a>
			</xsl:when>
			<xsl:otherwise>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="card.actions">
		<xsl:param name="card" select="."/>
		<xsl:param name="mode"/>
		<xsl:for-each select="$card">
			<nav class="clicked-show">
				<span class="image">
					<img data-src="{@href-rarity}" alt="{substring(@rarity, 1, 1)}" title="{set} - {@rarity}"/>
				</span>
				<xsl:choose>
					<xsl:when test="@href-set">
						<a href="{@href-set}" class="clicked-show" rel="external">
							<xsl:value-of select="set"/>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<span class="clicked-show">
							<xsl:value-of select="set"/>
						</span>
					</xsl:otherwise>
				</xsl:choose>
				<!--
				<span class="clicked-show">
					<a href="/getFragment.php/mtg/print?uri={@href-image}">⎙</a>
				</span>
				-->
				<xsl:choose>
					<xsl:when test="$mode = 'manager'">
						<code class="button input">
							<xsl:value-of select="@stock"/>
							<xsl:if test="@whole-stock">
								<xsl:text>/</xsl:text>
								<xsl:value-of select="@whole-stock"/>
							</xsl:if>
						</code>
					</xsl:when>
					<xsl:when test="@stock">
						<code class="button">
							<input type="text" pattern="-?\d+" required="required" value="{@stock}" name="stock[{name(..)}][{@name}]" tabindex="1" title="number of these cards in the deck">
								<xsl:if test="$mode != 'edit'">
									<xsl:attribute name="disabled">disabled</xsl:attribute>
								</xsl:if>
							</input>
						</code>
					</xsl:when>
				</xsl:choose>
				<xsl:if test="$mode = 'edit'">
					<span class="button">
						<button type="submit" name="card-del" value="{@name}" title="remove this card from deck">✖</button>
					</span>
				</xsl:if>
				<xsl:if test="$mode = 'search'">
					<span class="button">
						<button type="submit" name="card-add" value="{@name}" title="add this card to deck">✔</button>
					</span>
				</xsl:if>
				<xsl:call-template name="card.price"/>
			</nav>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template name="search.input">
		<xsl:param name="search"/>
		<xsl:param name="key"/>
		<!--<input type="search" name="search-query" value="{$search/@query}" required="required"/>-->
		<input type="search" name="{$key}[name]" value="{$search/@query-name}" title="Name" placeholder="Name..."  autofocus="autofocus"/>
		<input type="search" name="{$key}[type]" value="{$search/@query-type}" title="Type" placeholder="Type..."/>
		<input type="search" name="{$key}[rarity]" value="{$search/@query-rarity}" title="Rarity" placeholder="Rarity..."/>
		<input type="search" name="{$key}[expansion_name]" value="{$search/@query-expansion_name}" title="Expansion" placeholder="Expansion..."/>
		<input type="search" name="{$key}[description]" value="{$search/@query-description}" title="Rules Text" placeholder="Rules Text..."/>
		<input type="search" name="{$key}[flavor]" value="{$search/@query-flavor}" title="Flavor Text" placeholder="Flavor Text..."/>
		<input type="search" name="{$key}[legality]" value="{$search/@query-legality}" title="Format" placeholder="Format..."/>
		<input type="search" name="{$key}[cmc]" value="{$search/@query-cmc}" title="Converted Mana Cost" placeholder="CMC..."/>
		<input type="search" name="{$key}[colors]" value="{$search/@query-colors}" title="Number of Colors" placeholder="Number of Colors..."/>
		<input type="search" name="{$key}[price_gt]" value="{$search/@query-price_gt}" title="Market Price (€)" placeholder="Market Price..."/>
		<!--<input type="search" name="{$key}[cost][]" value="{$search/@query-cost}" title="Mana Cost" placeholder="Mana Cost..."/>-->
		<!--
		<input type="search" name="{$key}[cost]" value="{$search/@query-cost}" placeholder="Color..."/>
		-->
		
		<span class="searchColor">
			<xsl:call-template name="search.input.color">
				<xsl:with-param name="search" select="$search"/>
				<xsl:with-param name="key" select="$key"/>
				<xsl:with-param name="color" select="'W'"/>
			</xsl:call-template>
			<xsl:call-template name="search.input.color">
				<xsl:with-param name="search" select="$search"/>
				<xsl:with-param name="key" select="$key"/>
				<xsl:with-param name="color" select="'U'"/>
			</xsl:call-template>
			<xsl:call-template name="search.input.color">
				<xsl:with-param name="search" select="$search"/>
				<xsl:with-param name="key" select="$key"/>
				<xsl:with-param name="color" select="'B'"/>
			</xsl:call-template>
			<xsl:call-template name="search.input.color">
				<xsl:with-param name="search" select="$search"/>
				<xsl:with-param name="key" select="$key"/>
				<xsl:with-param name="color" select="'R'"/>
			</xsl:call-template>
			<xsl:call-template name="search.input.color">
				<xsl:with-param name="search" select="$search"/>
				<xsl:with-param name="key" select="$key"/>
				<xsl:with-param name="color" select="'G'"/>
			</xsl:call-template>
			<xsl:call-template name="search.input.color">
				<xsl:with-param name="search" select="$search"/>
				<xsl:with-param name="key" select="$key"/>
				<xsl:with-param name="color" select="'C'"/>
			</xsl:call-template>
		</span>
	</xsl:template>
	
	<xsl:template name="search.input.color">
		<xsl:param name="search"/>
		<xsl:param name="key"/>
		<xsl:param name="color"/>
		<label>
			<img data-src="/getData.php/mtg/image?color={$color}" alt="{$color}"/>
			<input type="checkbox" name="{$key}[cost][]" value="{$color}">
				<xsl:if test="contains($search/@query-cost, $color)">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
			</input>
		</label>
	</xsl:template>
	
	<xsl:template name="deck.mana">
		<xsl:param name="cardList" select=".//card"/>
		<xsl:variable name="filterList" select="$cardList[@type != 'Land']"/>
		<xsl:variable name="colorList" select="categories/color"/>

		<!--
		<fieldset class="colors">
			<legend>Color Distribution</legend>
			<table>
				<caption></caption>
				<thead>
					<tr>
						<th/>
						<th data-category="Common">Total</th>
						<xsl:for-each select="$colorList">
							<th data-category="{.}"><xsl:value-of select="."/></th>
						</xsl:for-each>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>Cards</th>
						<td data-category="Common"><xsl:value-of select="sum($filterList/@stock)"/></td>
						<xsl:for-each select="$colorList">
							<td data-category="{.}">
								<xsl:value-of select="sum($filterList[color[. = current()]]/@stock)"/>
							</td>
						</xsl:for-each>
					</tr>
					<tr>
						<th>Mana</th>
						<td data-category="Common"><xsl:value-of select="sum($filterList/color/@val)"/></td>
						<xsl:for-each select="$colorList">
							<td data-category="{.}">
								<xsl:value-of select="
								  1 * sum($filterList[@stock = 1]/color[. = current()]/@val)
								+ 2 * sum($filterList[@stock = 2]/color[. = current()]/@val)
								+ 3 * sum($filterList[@stock = 3]/color[. = current()]/@val)
								+ 4 * sum($filterList[@stock = 4]/color[. = current()]/@val)
								+ 5 * sum($filterList[@stock = 5]/color[. = current()]/@val)
								+ 6 * sum($filterList[@stock = 6]/color[. = current()]/@val)
								+ 7 * sum($filterList[@stock = 6]/color[. = current()]/@val)
								+ 8 * sum($filterList[@stock = 6]/color[. = current()]/@val)"/>
							</td>
						</xsl:for-each>
					</tr>
				</tbody>
			</table>
		</fieldset>
		-->
		<!--
		<table border="1">
			<caption>Color Distribution</caption>
			<thead>
				<tr>
					<th/>
					<th>Total</th>
					<th>Colorless</th>
					<xsl:for-each select="$colorList">
						<th><xsl:value-of select="@name"/></th>
					</xsl:for-each>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th>Cards</th>
					<th><xsl:value-of select="sum($cardList/@stock)"/></th>
					<th><xsl:value-of select="sum($cardList[mana[not(@color)]]/@stock)"/></th>
					<xsl:for-each select="$colorList">
						<th><xsl:value-of select="sum($cardList[mana[@color = current()/@name]]/@stock)"/></th>
					</xsl:for-each>
				</tr>
				<tr>
					<th>Mana</th>
					<th><xsl:value-of select="sum($cardList/mana/@val)"/></th>
					<th>
						<xsl:value-of select="
							  1 * sum($cardList[@stock = 1]/mana[not(@color)]/@val)
							+ 2 * sum($cardList[@stock = 2]/mana[not(@color)]/@val)
							+ 3 * sum($cardList[@stock = 3]/mana[not(@color)]/@val)
							+ 4 * sum($cardList[@stock = 4]/mana[not(@color)]/@val)
							+ 5 * sum($cardList[@stock = 5]/mana[not(@color)]/@val)
							+ 6 * sum($cardList[@stock = 6]/mana[not(@color)]/@val)
							+ 7 * sum($cardList[@stock = 7]/mana[not(@color)]/@val)
							+ 8 * sum($cardList[@stock = 8]/mana[not(@color)]/@val)
							+ 9 * sum($cardList[@stock = 9]/mana[not(@color)]/@val)"/>
					</th>
					<xsl:for-each select="$colorList">
						<th>
							<xsl:value-of select="
							  1 * sum($cardList[@stock = 1]/mana[@color = current()/@name]/@val)
							+ 2 * sum($cardList[@stock = 2]/mana[@color = current()/@name]/@val)
							+ 3 * sum($cardList[@stock = 3]/mana[@color = current()/@name]/@val)
							+ 4 * sum($cardList[@stock = 4]/mana[@color = current()/@name]/@val)
							+ 5 * sum($cardList[@stock = 5]/mana[@color = current()/@name]/@val)
							+ 6 * sum($cardList[@stock = 6]/mana[@color = current()/@name]/@val)"/>
						</th>
					</xsl:for-each>
				</tr>
			</tbody>
		</table>
		-->
	</xsl:template>
</xsl:stylesheet>