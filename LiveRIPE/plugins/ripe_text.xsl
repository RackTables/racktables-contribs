<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text" />

<xsl:variable name="newline" select="'&#10;'" />
<xsl:variable name="maxWidth1">
  <xsl:for-each select="whois-resources/objects/object/attributes/attribute">
    <xsl:sort select="string-length(@name)" order="descending" data-type="number"/>
    <xsl:if test="position() = 1">
      <xsl:value-of select="string-length(@name) + 3" />
    </xsl:if>
  </xsl:for-each> 
</xsl:variable>

<xsl:template match="whois-resources/objects">  
  <xsl:apply-templates select="object"/>  
</xsl:template>

<xsl:template match="object">
  <xsl:apply-templates select="attributes"/>
  <xsl:value-of select="$newline"/>
</xsl:template>

<xsl:template match="object/primary-key/attribute">
  <xsl:value-of select="@value" />  
</xsl:template>

<xsl:template match="object/attributes">
  <xsl:apply-templates select="attribute"/>
</xsl:template>

<xsl:template match="object/attributes/attribute">
  <xsl:variable name="cell1"><xsl:value-of select="@name" /><xsl:text>:</xsl:text></xsl:variable>
  <xsl:variable name="cell2"><xsl:value-of select="@value" /></xsl:variable>
  
  <xsl:variable name="pad1"><xsl:value-of select="$maxWidth1 - string-length($cell1)" /></xsl:variable>   
  <xsl:variable name="pad2"><xsl:value-of select="$maxWidth1 - string-length($cell2)" /></xsl:variable>

  <xsl:value-of select="$cell1" />
  <xsl:call-template name="append-pad"><xsl:with-param name="length" select="$pad1" /></xsl:call-template>
  <xsl:value-of select="$cell2" />
  <xsl:call-template name="append-pad"><xsl:with-param name="length" select="$pad1" /></xsl:call-template>
  <xsl:value-of select="$newline"/>
</xsl:template>

<xsl:template name="append-pad">
  <xsl:param name="length" select="0" />
  <xsl:if test="$length > 0">
    <xsl:value-of select="'&#32;'"/>
    <xsl:call-template name="append-pad">
      <xsl:with-param name="length" select="$length - 1"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>