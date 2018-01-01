<?xml version="1.0" encoding="UTF-8"?><xsl:stylesheet version="1.0"	xmlns="http://www.w3.org/1999/xhtml"	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">		<xsl:import href="/getTemplate.php/mtg/global"/>		<xsl:template match="/data">		<html>			<xsl:call-template name="sites.head"/>			<body>				<main>					<h1>Slothsoft's MagicCardMarket Search</h1>					<form action="." method="POST">						<fieldset>							<legend>Search</legend>							<label><input name="search" type="search" value="{request/param[@name='search']}"/></label>							<button type="submit">Search!</button>						</fieldset>					</form>					<xsl:apply-templates select="*[@data-cms-name='search']"/>				</main>			</body>		</html>	</xsl:template>		<xsl:template match="search/category">		<details>			<summary><xsl:value-of select="@name"/></summary>			<table border="1">				<thead>					<tr>						<th>Set</th>						<th>Set Size</th>						<th>Name</th>						<th>Price</th>					</tr>				</thead>				<tbody>					<xsl:for-each select="../article[@category = current()/@name]">						<xsl:sort select="@price-float" data-type="number"/>						<tr>							<td><xsl:value-of select="@set"/></td>							<td class="number"><xsl:value-of select="@set-size"/></td>							<td><xsl:value-of select="@name"/></td>							<td class="number"><a href="{@href}"><xsl:value-of select="@price"/></a></td>						</tr>					</xsl:for-each>				</tbody>			</table>		</details>	</xsl:template></xsl:stylesheet>