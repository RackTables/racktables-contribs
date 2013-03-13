<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xlink="http://www.w3.org/1999/xlink">

<xsl:output method="html" />

<xsl:template match="whois-resources/objects">
	<table class="widetable" border="0" cellpadding="5" cellspacing="0" style="font-family: monospace;" >
		<tbody>
			<xsl:apply-templates select="object"/>
		</tbody>
	</table>
</xsl:template>

<xsl:template match="object">
	<tr><th colspan="2" class="centered"><h3>
	<xsl:for-each select="primary-key/attribute">
		<xsl:choose>
			<xsl:when test="../../link/@xlink:href">
				<xsl:element name="a">
					<xsl:attribute name="target">
						<xsl:text>_blank</xsl:text>
					</xsl:attribute>				
					<xsl:attribute name="href">
						<xsl:value-of select="../../link/@xlink:href"/>
					</xsl:attribute>				
					<xsl:value-of select="@value" />
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="@value" />
			</xsl:otherwise>
		</xsl:choose>
		<xsl:if test="following-sibling::*">
			<br />
		</xsl:if>
	</xsl:for-each>
	</h3></th></tr>
	<xsl:for-each select="attributes/attribute">
		<tr>
		<td width="20%"><xsl:value-of select="@name" /><xsl:text>:</xsl:text></td>
		<td><xsl:choose>
			<xsl:when test="link/@xlink:href">
				<xsl:element name="a">
					<xsl:attribute name="target">
						<xsl:text>_blank</xsl:text>
					</xsl:attribute>				
					<xsl:attribute name="href">
						<xsl:value-of select="link/@xlink:href"/>
					</xsl:attribute>				
					<xsl:value-of select="@value" />
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="@value" />
			</xsl:otherwise>
		</xsl:choose></td>
		</tr>
	</xsl:for-each>
</xsl:template>

</xsl:stylesheet>