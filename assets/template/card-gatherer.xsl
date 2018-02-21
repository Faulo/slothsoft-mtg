<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://schema.slothsoft.net/mtg"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:func="http://exslt.org/functions"
	xmlns:str="http://exslt.org/strings"
	xmlns:php="http://php.net/xsl"
	xmlns:mtg="http://slothsoft.net/MTG/"
	extension-element-prefixes="func str php mtg">
	
	<xsl:variable name="EOL" select="'&#10;'"/>
	<xsl:variable name="TAB" select="'&#9;'"/>
	
	<xsl:variable name="LABEL_NAME" select="'Card Name:'"/>
	<xsl:variable name="LABEL_TYPE" select="'Types:'"/>
	<xsl:variable name="LABEL_RARITY" select="'Rarity:'"/>
	<xsl:variable name="LABEL_COST" select="'Mana Cost:'"/>
	<xsl:variable name="LABEL_CMC" select="'Converted Mana Cost:'"/>
	<xsl:variable name="LABEL_DESCRIPTION" select="'Card Text:'"/>
	<xsl:variable name="LABEL_FLAVOR" select="'Flavor Text:'"/>
	<xsl:variable name="LABEL_SET_NAME" select="'Expansion:'"/>
	<xsl:variable name="LABEL_SET_NUMBER" select="'Card Number:'"/>
	
	<xsl:key name="cardValue" match="*[@class = 'value']" use="normalize-space(preceding-sibling::*[@class = 'label'])"/>
	
	
	<func:function name="mtg:getCardValue">
		<xsl:param name="valueName"/>
		<xsl:param name="cardName" select="''"/>
		<xsl:param name="countLimit" select="10"/>
		
		<func:result select="php:function('trim', mtg:getNodeText(mtg:getCardNode($valueName, $cardName, $countLimit)/node()))"/>
	</func:function>
	
	<func:function name="mtg:getCardSetAbbr">
		<xsl:param name="cardName" select="''"/>
		
		<func:result select="php:function(
			'strtolower',
			mtg:getURLParam(mtg:getCardNode($LABEL_SET_NAME, $cardName)//@src, 'set')
		)"/>
	</func:function>
	
	<func:function name="mtg:getCardNode">
		<xsl:param name="valueName"/>
		<xsl:param name="cardName" select="''"/>
		<xsl:param name="countLimit" select="10"/>
		
		<xsl:choose>
			<xsl:when test="$cardName = ''">
				<func:result select="key('cardValue', $valueName)"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:variable name="ownerCard" select="key('cardValue', $LABEL_NAME)[contains($cardName, normalize-space(.))]"/>
				<xsl:variable name="valueNode" select="key('cardValue', $valueName)[ancestor::html:td/@id = $ownerCard/ancestor::html:td/@id]"/>
				<func:result select="$valueNode[position() &lt;= $countLimit]"/>
			</xsl:otherwise>
		</xsl:choose>
	</func:function>
	
	<func:function name="mtg:getNodeText">
		<xsl:param name="node"/>
		<func:result>
			<xsl:for-each select="$node">
				<xsl:choose>
					<xsl:when test="self::*[@alt][not(@title)]">
						<xsl:choose>
							<xsl:when test="number(@alt) = @alt">
								<xsl:value-of select="concat('{', @alt, '}')"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="concat('{', mtg:getURLParam(@src, 'name'), '}')"/>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:when test="self::*[@class = 'cardtextbox']">
						<xsl:variable name="text" select="mtg:getNodeText(node())"/>
						<xsl:if test="$text != ''">
							<xsl:value-of select="concat($text, $EOL)"/>
						</xsl:if>
					</xsl:when>
					<xsl:when test="self::*">
						<xsl:variable name="text" select="normalize-space(mtg:getNodeText(node()))"/>
						<xsl:if test="$text != ''">
							<xsl:value-of select="concat($text, $EOL)"/>
						</xsl:if>
					</xsl:when>
					<xsl:when test="self::node()[ancestor::*/@class = 'cardtextbox']">
						<xsl:value-of select="."/>
					</xsl:when>
					<xsl:when test="self::node()">
						<xsl:variable name="text" select="normalize-space(.)"/>
						<xsl:if test="$text != ''">
							<xsl:value-of select="concat($text, $EOL)"/>
						</xsl:if>
					</xsl:when>
				</xsl:choose>
			</xsl:for-each>
		</func:result>
	</func:function>
	
	
	<func:function name="mtg:getURLParam">
		<xsl:param name="url"/>
		<xsl:param name="param"/>
		<func:result select="substring-before(
			substring-after(
				$url,
				concat($param, '=')
			),
			'&amp;'
		)"/>
	</func:function>
	
	<xsl:template match="/">
		<xsl:for-each select="//*[@class = 'contentTitle']">
			<xsl:call-template name="createCard">
				<xsl:with-param name="cardName" select="normalize-space(.)"/>
			</xsl:call-template>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template name="createCard">
		<xsl:param name="cardName"/>
		
		<card>
			<name><xsl:value-of select="$cardName"/></name>
			<image><xsl:value-of select="//*[@alt = $cardName]/@src"/></image>
			<type>
				<xsl:value-of select="mtg:getCardValue($LABEL_TYPE, $cardName)"/>
			</type>
			<rarity>
				<xsl:value-of select="mtg:getCardValue($LABEL_RARITY, $cardName, 1)"/>
			</rarity>
			<cost>
				<xsl:value-of select="mtg:getCardValue($LABEL_COST, $cardName)"/>
			</cost>
			<!--
			<cmc>
				<xsl:value-of select="mtg:getCardValue($LABEL_CMC, $cardName)"/>
			</cmc>
			-->
			<description>
				<xsl:value-of select="mtg:getCardValue($LABEL_DESCRIPTION, $cardName)"/>
			</description>
			<flavor>
				<xsl:value-of select="mtg:getCardValue($LABEL_FLAVOR, $cardName)"/>
			</flavor>
			<expansion_name>
				<xsl:value-of select="mtg:getCardValue($LABEL_SET_NAME, $cardName, 1)"/>
			</expansion_name>
			<expansion_abbr>
				<xsl:value-of select="mtg:getCardSetAbbr($cardName)"/>
			</expansion_abbr>
			<expansion_number>
				<xsl:value-of select="mtg:getCardValue($LABEL_SET_NUMBER, $cardName, 1)"/>
			</expansion_number>
		</card>
	</xsl:template>
	
	<xsl:template match="*[@alt]" mode="text" priority="3">
		<xsl:apply-templates select="@alt" mode="text"/>
	</xsl:template>
	
	<xsl:template match="*" mode="text" priority="2">
		<xsl:for-each select="node()">
			<xsl:if test="position() &gt; 1">
				<xsl:value-of select="$EOL"/>
			</xsl:if>
			<xsl:apply-templates select="." mode="text"/>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template match="node()" mode="text" priority="1">
		<xsl:if test="normalize-space(.) != ''">
			<xsl:value-of select="."/>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="@alt" mode="text" priority="1">
		<xsl:choose>
			<xsl:when test="number(.) = .">
				<xsl:value-of select="concat('{', ., '}')"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="concat('{', substring(., 1, 1), '}')"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>