<?xml version="1.0" encoding="UTF-8"?><xsl:stylesheet version="1.0" xmlns="http://www.w3.org/1999/xhtml"	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">	<!-- <xsl:variable name="uri" select="'http://magiccards.info/extras/token/return-to-ravnica/knight.jpg'"/> <xsl:variable 		name="uri" select="'http://magiccards.info/extras/token/avacyn-restored/human-1.jpg'"/> -->	<xsl:template match="/data/data/print">		<html>			<head>				<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />				<style type="text/css"><![CDATA[		* {	padding: 0;	margin: 0;}img {	display: block;}body {	vertical-align: middle;	height: 100%;}table {	border-spacing: 0;	margin: auto;	background-color: black;}td, img {	width: 2.5in;	height: 3.5in;}td.white {	background-color: white;}div.wrapper {	width: 7.5in;	margin: 0 auto;	padding: 0.5cm 0;	page-break-after: always;}			]]></style>			</head>			<body>				<xsl:for-each select="page">					<div class="wrapper">						<table>							<tr>								<xsl:call-template name="table.cell">									<xsl:with-param name="card" select="card[1]" />								</xsl:call-template>								<xsl:call-template name="table.cell">									<xsl:with-param name="card" select="card[2]" />								</xsl:call-template>								<xsl:call-template name="table.cell">									<xsl:with-param name="card" select="card[3]" />								</xsl:call-template>							</tr>							<tr>								<xsl:call-template name="table.cell">									<xsl:with-param name="card" select="card[4]" />								</xsl:call-template>								<xsl:call-template name="table.cell">									<xsl:with-param name="card" select="card[5]" />								</xsl:call-template>								<xsl:call-template name="table.cell">									<xsl:with-param name="card" select="card[6]" />								</xsl:call-template>							</tr>							<tr>								<xsl:call-template name="table.cell">									<xsl:with-param name="card" select="card[7]" />								</xsl:call-template>								<xsl:call-template name="table.cell">									<xsl:with-param name="card" select="card[8]" />								</xsl:call-template>								<xsl:call-template name="table.cell">									<xsl:with-param name="card" select="card[9]" />								</xsl:call-template>							</tr>						</table>					</div>				</xsl:for-each>			</body>		</html>	</xsl:template>	<xsl:template name="table.cell">		<xsl:param name="card" select="/.." />		<td>			<xsl:choose>				<xsl:when test="string-length($card/@uri)">					<img src="{$card/@uri}" />				</xsl:when>				<xsl:otherwise>					<xsl:attribute name="class">white</xsl:attribute>				</xsl:otherwise>			</xsl:choose>		</td>	</xsl:template></xsl:stylesheet>