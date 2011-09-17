<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output indent="yes" method="html" />
  <xsl:include href="../default/search/simple.xsl"/>

  <xsl:template match="/">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

            <title></title>

            <script type="text/javascript" src="{$root}js/jquery-1.4.2.min.js"></script>
            <script type="text/javascript" src="{$root}js/jquery-ui-1.8.2.custom.min.js"></script>
            <script type="text/javascript" src="{$root}js/jquery.cookie.js"></script>
            <script type="text/javascript" src="{$root}js/jquery.treeview.js"></script>

          <link rel="stylesheet" href="{$root}css/black-tie/jquery-ui-1.8.2.custom.css" type="text/css" />
          <link rel="stylesheet" href="{$root}css/theme.css" type="text/css"/>
          <link rel="shortcut icon" href="{$root}gifs/favicon.ico"/>
          <link rel="stylesheet" type="text/css" href="{$root}css/reset-fonts.css"/>
          <link rel="stylesheet" type="text/css" href="{$root}css/style.css"/>
          <!--[if IE 7]><link rel="stylesheet" type="text/css" href="{$root}css/IE7styles.css" /><![endif]-->
          <!--[if IE 6]><link rel="stylesheet" type="text/css" href="{$root}css/IE6styles.css" /><![endif]-->
          <link rel="stylesheet" type="text/css" href="{$root}css/print.css" media="print"/>
          <link rel="alternate" type="application/rss+xml" title="RSS feed" href="http://pear.php.net/feeds/latest.rss"/>
          <!-- compliance patch for microsoft browsers -->
       <!--[if lt IE 8]>
               <script type="text/javascript" src="http://pear.php.net/javascript/IE8.js"></script>
             <![endif]-->
        <script type="text/javascript" src="{$root}js/jquery-1.4.2.min.js"></script>
        <script type="text/javascript" src="{$root}js/jquery-ui-1.8.2.custom.min.js"></script>
        <script type="text/javascript" src="{$root}js/jquery.cookie.js"></script>
        <script type="text/javascript" src="{$root}js/jquery.treeview.js"></script>
      </head>
      <body>
        <table id="page">
          <tr><td colspan="2" id="db-header">
            <div id="header">
              <a href="/"><img src="{$root}gifs/pearsmall.gif" style="border: 0;" width="104" height="50" alt="PEAR"  /></a><br />
            </div>

            <div id="menubar">
                <ul id="menu">
                 <li class="menu-item"><a href="http://pear.php.net/index.php">Main</a></li>
                 <li class="menu-item"><a href="http://pear.php.net/support/">Support</a></li>
                 <li class="menu-item current"><a href="http://pear.php.net/manual/">Documentation</a></li>
                 <li class="menu-item"><a href="http://pear.php.net/packages.php">Packages</a></li>
                 <li class="menu-item"><a href="http://pear.php.net/pepr/">Package Proposals</a></li>
                 <li class="menu-item"><a href="http://pear.php.net/accounts.php">Developers</a></li>
                 <li class="menu-item menu-item-last"><a href="http://pear.php.net/bugs/">Bugs</a></li>
                </ul>
            </div>


        <ul id="submenu">
         <li class="menu-item"><a href="content.html" target="content">Package API</a></li>
         <li class="menu-item"><a href="markers.html" target="content">Markers</a></li>
         <li class="menu-item menu-item-last"><a href="graph.html" target="content">Inheritance Diagram</a></li>
        </ul>

          </td></tr>
          <tr>
            <td id="sidebar">
              <xsl:call-template name="search">
                <xsl:with-param name="root" select="$root" />
              </xsl:call-template>
              <iframe name="nav" id="nav" src="{$root}nav.html" />
            </td>
            <td id="contents">
              <iframe name="content" id="content" src="{$root}content.html" />
            </td>
          </tr>
          <tr><td colspan="2" id="db-footer"></td></tr>
        </table>
      </body>
    </html>
  </xsl:template>

</xsl:stylesheet>