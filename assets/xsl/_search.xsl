<?xml version="1.0" encoding="UTF-8"?><xsl:stylesheet version="1.0" xmlns="http://www.w3.org/1999/xhtml"	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">	<xsl:import href="farah://slothsoft@mtg/xsl/global" />	<xsl:template match="/*">		<div>			<xsl:call-template name="sites.head" />			<xsl:for-each select=".//oracle">				<main>					<xsl:for-each select="result">						<h3>							<xsl:value-of select="@message" />						</h3>					</xsl:for-each>					<h1>						MTG Card Search					</h1>					<xsl:apply-templates select="." />				</main>			</xsl:for-each>		</div>	</xsl:template>	<xsl:template match="oracle">		<xsl:variable name="search" select="search" />		<form method="GET" action=".">			<fieldset class="search">				<legend>					Card Search Thing					<xsl:if test="$search">						(Result:						<xsl:value-of select="count($search//card)" />						cards)					</xsl:if>				</legend>				<div>					<xsl:call-template name="search.input">						<xsl:with-param name="search" select="$search" />						<xsl:with-param name="key" select="'search-query'" />					</xsl:call-template>					<button type="submit">Search!! °o°</button>				</div>			</fieldset>			<xsl:call-template name="deck">				<xsl:with-param name="deck" select="$search" />				<xsl:with-param name="mode" select="'oracle'" />			</xsl:call-template>		</form>	</xsl:template></xsl:stylesheet>