<?xml version="1.0" encoding="UTF-8"?><xsl:stylesheet version="1.0"	xmlns="http://www.w3.org/1999/xhtml"	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">		<xsl:import href="/getTemplate.php/mtg/global"/>	<xsl:template match="/data">		<xsl:variable name="request" select="request"/>		<html>			<head>				<title>Magic Booster Prices</title>				<style><![CDATA[.booster {	display: table;	table-layout: fixed;	border-spacing: 0;	width: 8em;	height: 2em;	/*background-color: rgba(0, 63, 0, 0.1);*/	vertical-align: top;	opacity: 0.4;}.booster > span {	display: table-cell;	line-height: 1.5em;	vertical-align: middle;}.booster[data-price] {	opacity: 1.0;}.price {	width: 4em;	text-align: right;	font-family: monospace;}.input {	width: 2em;	text-align: center;}.name {	width: 2em;	text-align: left;}.name img {	max-height: 1.5em;    max-width: 2em;	display: block;	margin: auto;}table {	table-layout: fixed;	border-spacing: 0 1px;	border: 1px groove rgb(192, 192, 192);    border-radius: 0.25em;    margin: 0.5em 0 0;}td, th {	padding: 0;	border-width: 1px 0;	border-style: solid;	border-color: rgb(192, 192, 192);}.Shop {	width: 8em;}.Standard {	width: 8em;}.Modern {	width: 64em;}.Vintage {	width: 32em;}.shop {	text-align: center;	word-break: break-all;	padding: 0.25em;}.summary {	background-color: rgba(127, 255, 127, 0.1);}select {	height: 15em;	margin: 0 1em;}ul.columns {	/*	display: flex;	flex-direction: column;	flex-wrap: wrap;	height: 12em;	width: 136em;	column-count: 2;	column-gap: 0;	*/	list-style-type: none;	padding: 0;}ul.columns > li {	display: inline-block;}				]]></style>			</head>			<body>				<main>					<h1>Magic Booster Prices</h1>					<xsl:apply-templates select="data/shopping"/>				</main>			</body>		</html>	</xsl:template>		<xsl:template match="shopping">		<xsl:variable name="config" select="."/>		<div>			<form action="" method="GET">				<fieldset>					<legend>Filter</legend>					<select multiple="multiple" name="shopping[country][]" required="required">						<xsl:for-each select="country">								<option>								<xsl:if test="@active">									<xsl:attribute name="selected">selected</xsl:attribute>								</xsl:if>								<xsl:value-of select="@name"/>							</option>						</xsl:for-each>					</select>					<select size="15" multiple="multiple" name="shopping[language][]" required="required">						<xsl:for-each select="language">							<option>								<xsl:if test="@active">									<xsl:attribute name="selected">selected</xsl:attribute>								</xsl:if>								<xsl:value-of select="@name"/>							</option>						</xsl:for-each>					</select>					<select size="15" multiple="multiple" name="shopping[format][]" required="required">						<xsl:for-each select="format">								<option>								<xsl:if test="@active">									<xsl:attribute name="selected">selected</xsl:attribute>								</xsl:if>								<xsl:value-of select="@name"/>							</option>						</xsl:for-each>					</select>					<input type="submit"/>				</fieldset>			</form>			<!--			<form action="https://www.magiccardmarket.eu/" method="POST" target="_blank">				<input type="hidden" value="false" name="doSubmit"/>				<input type="hidden" value="processItemViewForm" name="mainPage"/>				<input type="hidden" value="mainPage=showShoppingCart" name="caller"/>				<input type="hidden" name="cb_idArticle_237832860" value="on"/>				<input type="hidden" name="modifAmount237832860" value="1"/>				<input type="image" name="putMultipleArticlesInCart" value="X" src="https://www.magiccardmarket.eu/img/shoppingbasket_add.png"/>			</form>			-->			<xsl:if test="shop">				<table>					<thead>						<tr>							<th class="Shop">Shop</th>							<th/>							<!--							<xsl:for-each select="format">								<th class="{@name}"><xsl:value-of select="@name"/></th>							</xsl:for-each>							-->						</tr>						<tr class="summary">							<th>(<xsl:value-of select="count($config/booster[@format = $config/format[@active]/@name])"/>)</th>							<td>								<ul class="columns">									<xsl:for-each select="$config/format[@active]">										<xsl:variable name="format" select="@name"/>										<xsl:for-each select="$config/booster[@format = current()/@name]">											<xsl:variable name="booster" select="@name"/>											<xsl:variable name="boosterList" select="$config/shop/booster[@name = $booster]"/>											<xsl:variable name="price" select="$boosterList[not(@price &gt; $boosterList/@price)]/@price"/>																						<li>												<span class="booster" title="{$booster}">													<xsl:if test="$price">														<xsl:attribute name="data-price"><xsl:value-of select="$price"/></xsl:attribute>													</xsl:if>													<span class="price">														<xsl:if test="$price">															<a href="{@uri}" target="_blank">																<xsl:value-of select="format-number($price, '0.00')"/>															</a>														</xsl:if>													</span>													<span class="input">														<input type="checkbox" disabled="disabled"/>													</span>													<span class="name">														<img src="/getData.php/mtg/image-rarity?expansion_name={@set}"/>													</span>												</span>											</li>										</xsl:for-each>									</xsl:for-each>								</ul>							</td>							<!--							<xsl:for-each select="$config/format">								<xsl:variable name="format" select="@name"/>								<td>									<xsl:for-each select="$config/booster[@format = current()/@name]">										<xsl:variable name="booster" select="@name"/>										<xsl:variable name="boosterList" select="$config/shop/booster[@name = $booster]"/>										<xsl:variable name="price" select="$boosterList[not(@price &gt; $boosterList/@price)]/@price"/>																				<span class="booster" title="{$booster}">											<xsl:if test="$price">												<xsl:attribute name="data-price"><xsl:value-of select="$price"/></xsl:attribute>											</xsl:if>											<span class="price">												<xsl:if test="$price">													<a href="{@uri}" target="_blank">														<xsl:value-of select="format-number($price, '#.00')"/>													</a>												</xsl:if>											</span>											<span class="input">												<input type="checkbox" disabled="disabled"/>											</span>											<span class="name">												<img src="/getData.php/mtg/image-rarity?expansion_name={@set}"/>											</span>										</span>									</xsl:for-each>								</td>							</xsl:for-each>							-->						</tr>					</thead>					<tbody>						<xsl:for-each select="shop[count(booster) &gt; 1]">							<xsl:sort select="count(booster)" order="descending" data-type="number"/>							<xsl:variable name="boosterList" select="booster"/>							<tr>								<td>									<div class="shop">										<a href="{@uri}"><xsl:value-of select="@name"/></a>										<br/>										<xsl:value-of select="concat('(', count(booster), ')')"/>									</div>								</td>								<td>									<ul class="columns">										<xsl:for-each select="$config/format[@active]">											<xsl:variable name="format" select="@name"/>											<xsl:for-each select="$config/booster[@format = current()/@name]">												<xsl:variable name="booster" select="@name"/>												<xsl:variable name="price" select="$boosterList[@name = $booster]/@price"/>																								<li>													<span class="booster" title="{$booster}">														<xsl:if test="$price">															<xsl:attribute name="data-price"><xsl:value-of select="$price"/></xsl:attribute>														</xsl:if>														<span class="price">															<xsl:if test="$price">																<a href="{@uri}" target="_blank">																	<xsl:value-of select="format-number($price, '#.00')"/>																</a>															</xsl:if>														</span>														<span class="input">															<input type="checkbox" disabled="disabled"/>														</span>														<span class="name">															<img src="/getData.php/mtg/image-rarity?expansion_name={@set}"/>														</span>													</span>												</li>											</xsl:for-each>										</xsl:for-each>									</ul>								</td>							</tr>						</xsl:for-each>					</tbody>				</table>			</xsl:if>		</div>	</xsl:template></xsl:stylesheet>