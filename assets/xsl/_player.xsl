<?xml version="1.0" encoding="UTF-8"?><xsl:stylesheet version="1.0" xmlns="http://www.w3.org/1999/xhtml"	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">	<xsl:import href="farah://slothsoft@mtg/xsl/global" />	<xsl:template match="/data">		<html>			<xsl:call-template name="sites.head" />			<body>				<xsl:call-template name="sites.navi" />				<main>					<h1>						<xsl:value-of select="$rootPage/ancestor-or-self::page[1]/@title" />					</h1>				</main>			</body>		</html>	</xsl:template></xsl:stylesheet>