<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" exclude-result-prefixes="fo">
 <xsl:output method="xml" encoding="UTF-8" version="1.0"/>
 <xsl:variable name="bundle"/>
 <xsl:template match="/">
  <xsl:text>
</xsl:text>
  <xsl:apply-templates select="/package"/>
 </xsl:template>
 <xsl:template match="package">
  <package version="2.0">
   <xsl:attribute name="xsi:schemaLocation">http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd</xsl:attribute>
   <xsl:text>
 </xsl:text>
   <name channel="pear">
    <xsl:value-of select="/package/name"/>
   </name>
   <xsl:text>
 </xsl:text>
   <summary>
    <xsl:value-of select="/package/summary"/>
   </summary>
   <xsl:text>
 </xsl:text>
   <description>
    <xsl:value-of select="/package/description"/>
   </description>
   <xsl:apply-templates select="maintainers"/>
   <xsl:text>
 </xsl:text>
   <date>
    <xsl:value-of select="release/date"/>T00:00:00Z</date>
   <xsl:text>
 </xsl:text>
   <xsl:element name="version">
    <xsl:attribute name="api">1.0</xsl:attribute>
    <xsl:attribute name="package"><xsl:value-of select="release/version"/></xsl:attribute>
   </xsl:element>
   <xsl:text>
 </xsl:text>
   <xsl:apply-templates select="release/license"/>
   <xsl:apply-templates select="/package/license"/>
   <xsl:text>
 </xsl:text>
   <xsl:element name="stability">
    <xsl:attribute name="api"><xsl:value-of select="release/state"/></xsl:attribute>
    <xsl:attribute name="package"><xsl:value-of select="release/state"/></xsl:attribute>
   </xsl:element>
   <xsl:text>
 </xsl:text>
   <notes>
    <xsl:value-of select="release/notes"/>
   </notes>
   <xsl:text>
 </xsl:text>
   <xsl:call-template name="bundle"/>
   <xsl:text>
 </xsl:text>
   <filelist>
    <xsl:text>
  </xsl:text>
    <xsl:element name="dir">
     <xsl:attribute name="name">/</xsl:attribute>
     <xsl:if test="release/filelist/dir[1]/@baseinstalldir">
      <xsl:attribute name="baseinstalldir"><xsl:value-of select="release/filelist/dir[1]/@baseinstalldir"/></xsl:attribute>
     </xsl:if>
     <xsl:choose>
      <xsl:when test="release/filelist/dir[1]/@name='/'">
       <xsl:call-template name="dircontents">
        <xsl:with-param name="contents" select="release/filelist/dir[1]"/>
        <xsl:with-param name="indent" select="concat(' ', '  ')"/>
       </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
       <xsl:call-template name="dircontents">
        <xsl:with-param name="contents" select="release/filelist"/>
        <xsl:with-param name="indent" select="concat(' ', '  ')"/>
       </xsl:call-template>
      </xsl:otherwise>
     </xsl:choose>
     <xsl:text>
  </xsl:text>
    </xsl:element>
    <xsl:text>
 </xsl:text>
   </filelist>
   <xsl:text>
 </xsl:text>
   <php>
    <xsl:text>
  </xsl:text>
    <dependencies>
     <xsl:if test="boolean(release/deps/dep[@type='php'])=false">
      <xsl:text>
   </xsl:text>
      <xsl:element name="php">
       <xsl:attribute name="min">4.0</xsl:attribute>
       <xsl:attribute name="recommended">4.3.9</xsl:attribute>
      </xsl:element>
     </xsl:if>
     <xsl:apply-templates select="release/deps"/>
     <xsl:text>
  </xsl:text>
    </dependencies>
    <xsl:text>
 </xsl:text>
   </php>
   <xsl:text>
</xsl:text>
  </package>
 </xsl:template>
 <xsl:template match="maintainers">
  <xsl:for-each select="maintainer">
   <xsl:sort select="user"/>
   <xsl:if test="role='lead'">
    <xsl:text>
 </xsl:text>
    <xsl:element name="lead">
     <xsl:attribute name="user"><xsl:value-of select="user"/></xsl:attribute>
     <xsl:attribute name="name"><xsl:value-of select="name"/></xsl:attribute>
     <xsl:attribute name="email"><xsl:value-of select="email"/></xsl:attribute>
     <xsl:attribute name="active">yes</xsl:attribute>
    </xsl:element>
   </xsl:if>
  </xsl:for-each>
  <xsl:for-each select="maintainer">
   <xsl:sort select="user"/>
   <xsl:if test="role!='lead'">
    <xsl:text>
 </xsl:text>
    <xsl:element name="maintainer">
     <xsl:attribute name="role"><xsl:value-of select="role"/></xsl:attribute>
     <xsl:attribute name="user"><xsl:value-of select="user"/></xsl:attribute>
     <xsl:attribute name="name"><xsl:value-of select="name"/></xsl:attribute>
     <xsl:attribute name="email"><xsl:value-of select="email"/></xsl:attribute>
     <xsl:attribute name="active">yes</xsl:attribute>
    </xsl:element>
   </xsl:if>
  </xsl:for-each>
 </xsl:template>
 <xsl:template match="license">
  <xsl:element name="license">
   <xsl:choose>
    <xsl:when test=".='PHP License'">
     <xsl:attribute name="uri">http://www.php.net/license/3_00.txt</xsl:attribute>
    </xsl:when>
    <xsl:when test=".='lgpl'">
     <xsl:attribute name="uri">http://www.gnu.org/copyleft/lesser.html</xsl:attribute>
    </xsl:when>
    <xsl:when test=".='LGPL'">
     <xsl:attribute name="uri">http://www.gnu.org/copyleft/lesser.html</xsl:attribute>
    </xsl:when>
    <xsl:when test=".='BSD'">
     <xsl:attribute name="uri">http://www.opensource.org/licenses/bsd-license.php</xsl:attribute>
    </xsl:when>
    <xsl:when test=".='MIT'">
     <xsl:attribute name="uri">http://www.opensource.org/licenses/mit-license.php</xsl:attribute>
    </xsl:when>
    <xsl:when test=".='GPL'">
     <xsl:attribute name="uri">http://www.gnu.org/copyleft/gpl.html</xsl:attribute>
    </xsl:when>
    <xsl:when test=".='Apache'">
     <xsl:attribute name="uri">http://www.opensource.org/licenses/apache2.0.php</xsl:attribute>
    </xsl:when>
    <xsl:otherwise>
     <xsl:attribute name="uri">http://www.example.com</xsl:attribute>
    </xsl:otherwise>
   </xsl:choose>
   <xsl:value-of select="."/>
  </xsl:element>
 </xsl:template>
 <xsl:template name="bundle">
  <xsl:if test="release/deps/dep[@type='pkg' and (@optional='no' or boolean(@optional)=false)]">
   <bundle name="default">
    <xsl:for-each select="release/deps/dep[@type='pkg' and (@optional='no' or boolean(@optional)=false)]">
     <xsl:text>
  </xsl:text>
     <xsl:element name="package">
      <xsl:attribute name="channel">pear</xsl:attribute>
      <xsl:attribute name="name"><xsl:value-of select="."/></xsl:attribute>
      <xsl:if test="@rel='ge' or @rel='le'">
       <xsl:attribute name="recommended"><xsl:value-of select="@version"/></xsl:attribute>
      </xsl:if>
     </xsl:element>
    </xsl:for-each>
    <xsl:text>
 </xsl:text>
   </bundle>
  </xsl:if>
 </xsl:template>
 <xsl:template name="filelist">
  <xsl:param name="dir"/>
  <xsl:param name="indent"/>
  <xsl:for-each select="$dir">
   <xsl:sort select="@name"/>
   <xsl:choose>
    <xsl:when test="@name!='/'">
     <xsl:text>
</xsl:text>
     <xsl:value-of select="$indent"/>
     <xsl:element name="dir">
      <xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
      <xsl:if test="@baseinstalldir">
       <xsl:attribute name="baseinstalldir"><xsl:value-of select="@baseinstalldir"/></xsl:attribute>
      </xsl:if>
      <xsl:call-template name="dircontents">
       <xsl:with-param name="contents" select="."/>
       <xsl:with-param name="indent" select="concat($indent, ' ')"/>
      </xsl:call-template>
      <xsl:text>
</xsl:text>
      <xsl:value-of select="$indent"/>
     </xsl:element>
    </xsl:when>
    <xsl:otherwise>
     <xsl:call-template name="dircontents">
      <xsl:with-param name="contents" select="."/>
      <xsl:with-param name="indent" select="concat($indent, ' ')"/>
     </xsl:call-template>
    </xsl:otherwise>
   </xsl:choose>
  </xsl:for-each>
 </xsl:template>
 <xsl:template name="files">
  <xsl:param name="files"/>
  <xsl:param name="indent"/>
  <xsl:for-each select="$files">
   <xsl:sort select="@name"/>
   <xsl:text>
</xsl:text>
   <xsl:value-of select="$indent"/>
   <xsl:element name="file">
    <xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
    <xsl:attribute name="role"><xsl:value-of select="@role"/></xsl:attribute>
    <xsl:if test="@install-as">
     <xsl:attribute name="install-as"><xsl:value-of select="@install-as"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="@imd5sum">
     <xsl:attribute name="md5sum"><xsl:value-of select="@md5sum"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="@platform">
     <xsl:attribute name="platform"><xsl:value-of select="@platform"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="replace">
     <xsl:for-each select="replace">
      <xsl:text>
</xsl:text>
      <xsl:value-of select="concat($indent, ' ')"/>
      <xsl:element name="tasks:replace">
       <xsl:attribute name="from"><xsl:value-of select="@from"/></xsl:attribute>
       <xsl:attribute name="to"><xsl:value-of select="@to"/></xsl:attribute>
       <xsl:attribute name="type"><xsl:value-of select="@type"/></xsl:attribute>
      </xsl:element>
     </xsl:for-each>
     <xsl:text>
</xsl:text>
     <xsl:value-of select="$indent"/>
    </xsl:if>
   </xsl:element>
  </xsl:for-each>
 </xsl:template>
 <xsl:template name="dircontents">
  <xsl:param name="contents"/>
  <xsl:param name="indent"/>
  <xsl:call-template name="filelist">
   <xsl:with-param name="dir" select="$contents/dir"/>
   <xsl:with-param name="indent" select="$indent"/>
  </xsl:call-template>
  <xsl:call-template name="files">
   <xsl:with-param name="files" select="$contents/file"/>
   <xsl:with-param name="indent" select="$indent"/>
  </xsl:call-template>
 </xsl:template>
 <xsl:template match="deps">
  <xsl:apply-templates select="dep[@type='php']"/>
  <xsl:apply-templates select="dep[@type='pkg']"/>
  <xsl:apply-templates select="dep[@type='ext']"/>
  <xsl:apply-templates select="dep[@type='sapi']"/>
  <xsl:apply-templates select="dep[@type='os']"/>
 </xsl:template>
 <xsl:template match="dep[@type='php']">
  <xsl:for-each select=".">
   <xsl:if test="@rel='ge'">
    <xsl:text>
   </xsl:text>
    <xsl:element name="php">
     <xsl:attribute name="min"><xsl:value-of select="@version"/></xsl:attribute>
     <xsl:attribute name="recommended"><xsl:value-of select="@version"/></xsl:attribute>
    </xsl:element>
   </xsl:if>
  </xsl:for-each>
 </xsl:template>
 <xsl:template match="dep[@type='pkg']">
  <xsl:for-each select=".">
   <xsl:text>
   </xsl:text>
   <xsl:element name="package">
    <xsl:attribute name="channel">pear</xsl:attribute>
    <xsl:attribute name="name"><xsl:value-of select="."/></xsl:attribute>
    <xsl:if test="@rel='ge'">
     <xsl:attribute name="min"><xsl:value-of select="@version"/></xsl:attribute>
     <xsl:attribute name="recommended"><xsl:value-of select="@version"/></xsl:attribute>
    </xsl:if>
   </xsl:element>
  </xsl:for-each>
 </xsl:template>
 <xsl:template match="dep[@type='ext']">
  <xsl:for-each select=".">
   <xsl:text>
   </xsl:text>
   <xsl:element name="extension">
    <xsl:attribute name="name"><xsl:value-of select="."/></xsl:attribute>
    <xsl:if test="@rel='ge'">
     <xsl:attribute name="min"><xsl:value-of select="@version"/></xsl:attribute>
     <xsl:attribute name="recommended"><xsl:value-of select="@version"/></xsl:attribute>
    </xsl:if>
   </xsl:element>
  </xsl:for-each>
 </xsl:template>
 <xsl:template match="dep[@type='sapi']">
  <xsl:for-each select=".">
   <xsl:text>
   </xsl:text>
   <xsl:element name="sapi">
    <xsl:attribute name="name"><xsl:value-of select="."/></xsl:attribute>
    <xsl:if test="@rel='ge'">
     <xsl:attribute name="min"><xsl:value-of select="@version"/></xsl:attribute>
     <xsl:attribute name="recommended"><xsl:value-of select="@version"/></xsl:attribute>
    </xsl:if>
   </xsl:element>
  </xsl:for-each>
 </xsl:template>
 <xsl:template match="dep[@type='os']">
  <xsl:for-each select=".">
   <xsl:text>
   </xsl:text>
   <xsl:element name="os">
    <xsl:attribute name="name"><xsl:value-of select="."/></xsl:attribute>
    <xsl:if test="@rel='ge'">
     <xsl:attribute name="min"><xsl:value-of select="@version"/></xsl:attribute>
     <xsl:attribute name="recommended"><xsl:value-of select="@version"/></xsl:attribute>
    </xsl:if>
   </xsl:element>
  </xsl:for-each>
 </xsl:template>
</xsl:stylesheet>
