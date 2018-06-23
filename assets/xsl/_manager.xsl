<?xml version="1.0" encoding="UTF-8"?><xsl:stylesheet version="1.0" xmlns="http://www.w3.org/1999/xhtml"	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">	<xsl:import href="farah://slothsoft@mtg/xsl/global" />	<xsl:template match="/data">		<html>			<xsl:call-template name="sites.head" />			<body onload="MTG.Manager.init('{data/request/page[2]/@name}', document.getElementById('MTG-Manager'))">				<xsl:call-template name="sites.navi" />				<main>					<h1>						<xsl:for-each select="$rootPage/ancestor-or-self::page[position() != last()]">							<xsl:if test="position() &gt; 1">								<xsl:text> » </xsl:text>							</xsl:if>							<span data-dict=".">								<xsl:value-of select="@title" />							</span>						</xsl:for-each>					</h1>					<div id="MTG-Manager" />				</main>			</body>		</html>	</xsl:template></xsl:stylesheet>