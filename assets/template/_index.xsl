<?xml version="1.0" encoding="UTF-8"?><xsl:stylesheet version="1.0"	xmlns="http://www.w3.org/1999/xhtml"	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">		<xsl:import href="farah://slothsoft@mtg/xsl/global"/>		<xsl:variable name="rootPage" select="/data/*[@data-cms-name='/core/sites']//page[@name='MTG']"/>		<xsl:template match="/data">		<xsl:variable name="chatNode" select="*[@data-cms-name='chat']"/>		<xsl:variable name="newsNode" select="*[@data-cms-name='news']"/>		<html><!-- manifest="{request/@url}?appcache"-->			<xsl:call-template name="sites.head"/>			<body>				<xsl:call-template name="sites.navi"/>				<main>					<!--					<h1>						<a href=".."><xsl:value-of select="$rootPage/../@title"/></a>						»						<a href="."><xsl:value-of select="$rootPage/@title"/></a>					</h1>					-->					<h1>Magic: The Gathering!! \o\ /o/</h1>					<xsl:if test="$chatNode">						<aside>							<xsl:copy-of select="$chatNode/node()"/>						</aside>					</xsl:if>					<xsl:if test="$newsNode">						<fieldset class="news">							<legend>News</legend>							<xsl:copy-of select="$newsNode/node()"/>						</fieldset>					</xsl:if>					<!--					<fieldset class="deck">						<legend>Decks</legend>						<dl class="index">							<xsl:for-each select="$rootPage/page">								<dt><a href="{@uri}"><xsl:value-of select="@title"/><xsl:text> - Drag'n'Drop Deck Manager</xsl:text></a></dt>								<dd>									<ul>										<xsl:for-each select="page">											<li>												<xsl:value-of select="@title"/>												<xsl:for-each select="page">													<a href="{@uri}"><xsl:value-of select="@title"/></a>												</xsl:for-each>											</li>										</xsl:for-each>									</ul>								</dd>							</xsl:for-each>						</dl>					</fieldset>					-->										<!--					<fieldset>						<legend>Sets</legend>						<xsl:apply-templates select="data[@data-cms-name = 'sets']"/>					</fieldset>					-->				</main>			</body>		</html>	</xsl:template>		<xsl:template match="data[@data-cms-name = 'sets']">		<dl class="sets">			<xsl:for-each select="block">				<dt><a href="{@uri}"><xsl:value-of select="@name"/></a></dt>				<dd>					<ul>						<xsl:for-each select="set">							<li style="list-style-image: url({@image})">								<a href="{@uri}"><xsl:value-of select="@name"/></a>							</li>						</xsl:for-each>					</ul>				</dd>			</xsl:for-each>		</dl>	</xsl:template></xsl:stylesheet>