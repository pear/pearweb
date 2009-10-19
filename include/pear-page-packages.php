<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * The source code for the PEAR website
 *
 * PHP version 5
 *
 * +----------------------------------------------------------------------+
 * | PEAR Web site version 1.0                                            |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2001-2009 The PHP Group                                |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 2.02 of the PHP license,      |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available at through the world-wide-web at                           |
 * | http://www.php.net/license/2_02.txt.                                 |
 * | If you did not receive a copy of the PHP license and are unable to   |
 * | obtain it through the world-wide-web, please send a note to          |
 * | license@php.net so we can mail you a copy immediately.               |
 * +----------------------------------------------------------------------+
 * | Authors:  Michael Gauthier <mike@silverorange.com>                   |
 * +----------------------------------------------------------------------+
 *
 * @category  PEAR Website
 * @package   pearweb
 * @copyright The PHP Group
 * @license   PHP License http://www.php.net/license/2_02.txt
 * @version   $Id:$
 */

/**
 * Static license methods
 */
require_once 'pear-class-license.php';

/**
 * Class to display the package category browser
 *
 * @category  PEAR Website
 * @package   pearweb
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2009 silverorange
 * @license   PHP License http://www.php.net/license/3_01.txt
 */
class page_packages
{
    // {{{ constants

    /**
     * Maximum number of sub-items to display under each category in the
     * category list
     */
    const MAX_CATEGORY_SUB_LINKS = 4;

    // }}}
    // {{{ properties

    /**
     * The database handle
     *
     * @var DB_common
     *
     * @see page_packages::__construct()
     */
    protected $dbh = null;

    // }}}
    // {{{ __construct()

    /**
     * Creates a new category package browser
     *
     * @param DB_common $dbh database handler to use for page queries.
     */
    public function __construct(DB_common $dbh)
    {
        $this->dbh = $dbh;
    }

    // }}}
    // {{{ getTitle()

    /**
     * Gets the page title, used as the HTML page title
     *
     * @param integer $categoryId optional. The current category. If not
     *                            specified, the top-level category is assumed.
     * @param string  $php        optional. The PHP version on which to filter.
     *                            If not specified, defaults to 'all'.
     *
     * @return string the HTML page title.
     */
    public function getTitle($categoryId = null, $php = all)
    {
        $title = 'PEAR Packages';

        if (!empty($categoryId)) {
            $categoryName = $this->getCategoryName($categoryId);
            $title = htmlspecialchars($categoryName) . ' :: PEAR Packages';
        }

        return $title;
    }

    // }}}
    // {{{ getQueryString()

    /**
     * Gets a URI query string for the category package browser
     *
     * @param integer $categoryId   the category id.
     * @param string  $categoryName the category name.
     * @param string  $php          optional. The current PHP version. If not
     *                              specified, defaults to 'all'.
     *
     * @return string a query string suitable for appending to the current
     *                script name.
     */
    protected function getQueryString($categoryId, $categoryName, $php = 'all')
    {
        $query = '';

        $parts = array();

        if ($categoryId) {
            $parts[] = 'catpid=' . (int)$categoryId;
        }

        if ($categoryName) {
            $parts[] = 'catname=' . urlencode($categoryName);
        }

        if ($php != 'all') {
            $parts[] = 'php=' . urlencode($php);
        }

        if (count($parts) > 0) {
            $query = '?' . implode('&amp;', $parts);
        }

        return $query;
    }

    // }}}
    // {{{ getScriptName()

    /**
     * Gets a HTML-filtered version of the current script name
     *
     * @return string
     */
    protected function getScriptName()
    {
        return htmlspecialchars($_SERVER['SCRIPT_NAME']);
    }

    // }}}

    // sql building methods
    // {{{ getCategoryWhereClause()

    /**
     * Gets the SQL category where clause
     *
     * @param integer   $categoryId optional. The id of the parent category.
     *                              Defaults to null.
     *
     * @return string
     */
    protected function getCategoryWhereClause($categoryId = null)
    {
        $where = 'IS NULL';

        if (!empty($categoryId)) {
            $where = '= ' . $this->dbh->quote($categoryId, 'integer');
        }

        return $where;
    }

    // }}}
    // {{{ getPhpVersionWhereClause()

    /**
     * Gets the where clause to select against the package PHP version
     * requirement
     *
     * @param string $php the PHP version. Either '4', '5' or 'all'.
     *
     * @return string
     */
    protected function getPhpVersionWhereClause($php)
    {
        $where = '';

        if ($php === '5' || $php === '4') {
            if ($php === '5') {
                $phpVersion = ' >= 5 AND d.relation = "ge"';
            } else {
                $phpVersion = ' = 4';
            }

            $where = '
                AND
                d.release = (
                    SELECT id FROM releases
                    WHERE package = p.id
                    ORDER BY releasedate DESC
                    LIMIT 1
                ) AND
                d.optional = 0 AND
                d.type = "php" AND (
                       (SUBSTRING(d.version, 1, 1) ' . $phpVersion . ')
                    OR (SUBSTRING(d.name, 1, 1) ' . $phpVersion . ')
                )';
        }

        return $where;
    }

    // }}}
    // {{{ getPhpVersionJoinClause()

    /**
     * Gets the join clause to select against the package PHP version
     * requirement
     *
     * @param string $php the PHP version. Either '4', '5' or 'all'.
     *
     * @return string
     */
    protected function getPhpVersionJoinClause($php)
    {
        $join = '';

        if ($php === '4' || $php === '5') {
            $join = '
                LEFT JOIN releases r ON p.id = r.package
                LEFT JOIN deps d ON r.package = d.package';
        }

        return $join;
    }

    // }}}

    // query methods
    // {{{ getCategories()

    /**
     * Gets categories at the specified level
     *
     * @param integer   $categoryId optional. The id of the parent category.
     *                              Use null to get top-level categories.
     *                              Defaults to null.
     * @param string    $php        optional. PHP version on which to filter.
     *                              If not specified, defaults to 'all'.
     *
     * @return array
     */
    protected function getCategories($categoryId = null, $php = 'all')
    {
        $categoryWhere = $this->getCategoryWhereClause($categoryId);

        $sql = '
            SELECT
                c.*, COUNT(DISTINCT p.id) AS npackages
            FROM categories c
            LEFT JOIN packages p ON p.category = c.id';

        $sql .= $this->getPhpVersionJoinClause($php);

        $sql .='
            WHERE
                p.package_type = "' . SITE . '" AND
                p.approved = 1 AND
                c.parent ' . $categoryWhere;

        $sql .= $this->getPhpVersionWhereClause($php);

        $sql .= '
            GROUP BY c.id
            ORDER BY c.name';

        $this->dbh->setFetchmode(DB_FETCHMODE_ASSOC);
        return $this->dbh->getAll($sql);
    }

    // }}}
    // {{{ getPackages()

    /**
     * Gets detailed information about packages in a category.
     *
     * @param integer $categoryId the category to for which to get package
     *                            information.
     * @param string  $php        optional. The PHP version on which to filter.
     *                            If not specified, defaults to 'all'.
     *
     * @return array an array of packages with each array element being an array
     *               containing information about a specific package.
     */
    protected function getPackages($categoryId, $php = 'all')
    {
        $sql = '
            SELECT
                p.id, p.name, p.summary, p.license, p.unmaintained, p.newpk_id,
                (SELECT COUNT(package) FROM releases WHERE package = p.id) AS numreleases,
                (SELECT state FROM releases WHERE package = p.id ORDER BY id DESC LIMIT 1) AS status,
                (SELECT version FROM releases WHERE package = p.id ORDER BY id DESC LIMIT 1) AS version,
                (SELECT releasedate FROM releases WHERE package = p.id ORDER BY id DESC LIMIT 1) AS releasedate,
                (SELECT COUNT(1)
                    FROM bugdb
                    WHERE package_name = p.name AND status IN
                        (\'Open\', \'Verified\',  \'Assigned\')
                ) as numbugs
            FROM packages p';

        $sql .= $this->getPhpVersionJoinClause($php);

        $sql .= '
            WHERE
                p.package_type = ? AND p.approved = 1 AND p.category = ?';

        $sql .= $this->getPhpVersionWhereClause($php);
        if ($php != 'all') {
            $sql .= '
                GROUP BY p.id';
        }

        $sql .='
            ORDER BY p.name ASC';

        $this->dbh->setFetchmode(DB_FETCHMODE_ASSOC);
        return $this->dbh->getAll($sql, array(SITE, $categoryId));
    }

    // }}}
    // {{{ getSubCategories()

    /**
     * Gets the sub-categories of sub-categories of a category
     *
     * @param integer $categoryId optional. The id of the parent category. If
     *                            null, the top-level category is used. Defaults
     *                            to null.
     * @param string  $php        optional. PHP version on which to filter. If
     *                            not specified, defaults to 'all'.
     *
     * @return array an associative array with the sub-category id as the key
     *               and and array of sub-categories as the value.
     */
    protected function getSubCategories($categoryId = null, $php = 'all')
    {
        $categoryWhere = $this->getCategoryWhereClause($categoryId);

        $sql = '
            SELECT p.id AS pid, c.id AS id, c.name AS name, c.summary AS summary
            FROM categories c, categories p
            WHERE p.parent ' . $categoryWhere . ' AND c.parent = p.id
            ORDER BY c.name';

        return $this->dbh->getAssoc(
            $sql,
            false,
            null,
            DB_FETCHMODE_ASSOC,
            true
        );
    }

    // }}}
    // {{{ getSubPackages()

    /**
     * Gets the packages of sub-categories of a category indexed by the
     * sub-category id
     *
     * @param integer $categoryId optional. The id of the category. If null,
     *                            the top-level category is used. Defaults
     *                            to null.
     * @param string  $php        optional. PHP version on which to filter. If
     *                            not specified, defaults to 'all'.
     *
     * @return array an associative array with the sub-category id as the key
     *               and an array of packages as the value.
     */
    protected function getSubPackages($categoryId = null, $php = 'all')
    {
        $categoryWhere = $this->getCategoryWhereClause($categoryId);

        $sql = '
            SELECT
                p.category, p.id AS id, p.name AS name, p.summary AS summary
            FROM categories c
            LEFT JOIN packages p ON p.category = c.id';

        $sql .= $this->getPhpVersionJoinClause($php);

        $sql .= '
            WHERE
                c.parent ' . $categoryWhere . '
                AND p.approved = 1
                AND p.package_type = "' . SITE . '"
                AND (p.newpk_id IS NULL OR p.newpk_id = 0)
                AND p.category = c.id';

        $sql .= $this->getPhpVersionWhereClause($php);
        if ($php != 'all') {
            $sql .= '
                GROUP BY p.id';
        }

        $sql .= '
            ORDER BY p.name';

        return $this->dbh->getAssoc(
            $sql,
            false,
            null,
            DB_FETCHMODE_ASSOC,
            true
        );
    }

    // }}}
    // {{{ getCategoryName()

    /**
     * Gets the name of a category
     *
     * @param integer $categoryId the category for which to get the name.
     *
     * @return string the category name or null if no such category exists.
     */
    protected function getCategoryName($categoryId)
    {
        static $names = array();

        if (!isset($names[$categoryId])) {
            $sql = 'SELECT name FROM categories WHERE id = '
                . $this->dbh->quote($categoryId, 'integer');

            $names[$categoryId] = $this->dbh->getOne($sql);
        }

        return $names[$categoryId];
    }

    // }}}
    // {{{ getTotalPackageCount()

    /**
     * Gets the total number of packages
     *
     * @param integer $categoryId the category.
     * @param string  $php        optional. The PHP version on which to filter.
     *                            If not specified, defaults to 'all'.
     *
     * @return integer the number of packages.
     */
    protected function getTotalPackageCount($php = 'all')
    {
        $sql = '
            SELECT
                COUNT(DISTINCT p.id) AS count
            FROM packages p';

        $sql .= $this->getPhpVersionJoinClause($php);

        $sql .='
            WHERE
                p.package_type = "' . SITE . '" AND
                p.approved = 1';

        $sql .= $this->getPhpVersionWhereClause($php);

        return (int)$this->dbh->getOne($sql);
    }

    // }}}
    // {{{ getCategoryPackageCount()

    /**
     * Gets the number of packages in a category
     *
     * @param integer $categoryId the category.
     * @param string  $php        optional. The PHP version on which to filter.
     *                            If not specified, defaults to 'all'.
     *
     * @return integer the number of packages in the category.
     */
    protected function getCategoryPackageCount($categoryId, $php = 'all')
    {
        $categoryWhere = $this->getCategoryWhereClause($categoryId);
        $categoryWhere = '
            AND p.category ' . $categoryWhere;

        $sql = '
            SELECT
                COUNT(DISTINCT p.id) AS count
            FROM packages p';

        $sql .= $this->getPhpVersionJoinClause($php);

        $sql .='
            WHERE
                p.package_type = "' . SITE . '" AND
                p.approved = 1 ' . $categoryWhere;

        $sql .= $this->getPhpVersionWhereClause($php);

        return (int)$this->dbh->getOne($sql);
    }

    // }}}

    // display methods
    // {{{ display()

    /**
     * Displays the category package browser
     *
     * @param integer $categoryId optional. The current category. If not
     *                            specified, the top-level category is assumed.
     * @param string  $php        optional. The PHP version on which to filter.
     *                            If not specified, defaults to 'all'.
     *
     * @return void
     */
    public function display($categoryId = null, $php = 'all')
    {
        $this->displayHeader($categoryId, $php);
        $this->displayBody($categoryId, $php);
        $this->displayChannelTip();
    }

    // }}}
    // {{{ displayChannelTip()

    /**
     * Displays the link to view alternative PEAR channels
     *
     * @return void
     */
    protected function displayChannelTip()
    {
        echo "<blockquote class=\"channel-tip\">\n";
        echo " <p class=\"para\">\n";
        echo "  Several other PHP projects provide packages of their \n"
            . "  software that are installable using the PEAR \n"
            . "  infrastructure. A list of these projects can be found in \n"
            . "  the <a href=\"/channels/\">channels section</a>.\n";

        echo " </p>\n";
        echo "</blockquote>\n";
    }

    // }}}

    // header display methods
    // {{{ displayTitle()

    /**
     * Displays the category path and title in the header
     *
     * @param integer $categoryId optional. The category being displayed. If not
     *                            specified, the top level category is assumed.
     * @param string  $php        optional. The PHP version on which to filter.
     *                            If not specified, defaults to 'all'.
     *
     * @return void
     */
    protected function displayTitle($categoryId = null, $php = 'all')
    {
        if (empty($categoryId)) {
            $packageCount = $this->getTotalPackageCount($php);
        } else {
            $packageCount = $this->getCategoryPackageCount($categoryId, $php);
        }

        echo " <h2 class=\"category-title\">";

        if (empty($categoryId)) {
            echo "Packages <span class=\"category-package-count\">"
                . "({$packageCount})"
                . "</span>";
        } else {

            // display root
            $link = $this->getScriptName()
                . $this->getQueryString(null, null, $php);

            echo "<span><a href=\"{$link}\">Packages</a> :: </span>";

            // display parent categories
            $parentCategoryId = $categoryId;
            while (
                $parentCategoryId = $this->dbh->getOne(
                    'SELECT parent FROM categories where id = '
                    . (int)$parentCategoryId
                )
            ) {
                $categoryName = $this->getCategoryName($parentCategoryId);

                $link = $this->getScriptName()
                    . $this->getQueryString($parentCategoryId, null, $php);

                echo "<span><a href=\"{$link}\">{$categoryName}</a> :: </span>";
            }

            // display this category
            $categoryName = $this->getCategoryName($categoryId);
            echo htmlspecialchars($categoryName) . " ";

            // display package count
            echo "<span class=\"category-package-count\">"
                . "({$packageCount})"
                . "</span>";
        }

        echo "</h2>\n";
    }

    // }}}
    // {{{ displayStatisticsLink()

    /**
     * Displays the category statistics link
     *
     * @param integer $categoryId optional. The category being displayed. If not
     *                            specified, the top level category is assumed.
     * @param string  $php        optional. The PHP version on which to filter.
     *                            If not specified, defaults to 'all'.
     *
     * @return void
     */
    protected function displayStatisticsLink($categoryId = null, $php = 'all')
    {
        if ($categoryId) {
            $link         = '/package-stats.php?cid=' . (int)$categoryId;
            $categoryName = $this->getCategoryName($categoryId);
            $categoryName = htmlspecialchars($categoryName);
            echo " <a class=\"category-statistics\" href=\"{$link}\">";
            echo "View statistics for <strong>{$categoryName}</strong>";
            echo "</a>\n";
        }
    }

    // }}}
    // {{{ displayPhpVersionFilter()

    /**
     * Displays the PHP version filter selector
     *
     * @param integer $categoryId optional. The id of the current category. If
     *                            not specified, the top-level category is
     *                            assumed.
     * @param string  $php        optional. The current PHP version. If not
     *                            specified, 'all' is used.
     *
     * @return void
     */
    protected function displayPhpVersionFilter($categoryId = null, $php = 'all')
    {
        echo " <div class=\"php-version\">\n";
        echo "  Filter by\n";

        $options = array(
            '4'   => 'PHP 4',
            '5'   => 'PHP 5+',
            'all' => 'All PHP Versions',
        );

        foreach ($options as $key => $value) {
            if ($php == $key) {
                echo "  <span class=\"php-version-selected\">{$value}</span>\n";
            } else {
                $categoryName = $this->getCategoryName($categoryId);
                $link = $this->getScriptName() . $this->getQueryString(
                    $categoryId,
                    $categoryName,
                    $key
                );
                echo "  <a href=\"{$link}\">{$value}</a>\n";
            }
        }

        echo " </div>\n";
    }

    // }}}
    // {{{ displayHeader()

    /**
     * Displays the header for this page
     *
     * @param integer $categoryId optional. The category being displayed. If not
     *                            specified, the top level category is assumed.
     * @param string  $php        optional. The PHP version on which to filter.
     *                            If not specified, defaults to 'all'.
     *
     * @return void
     */
    protected function displayHeader($categoryId = null, $php = 'all')
    {
        echo "<div class=\"packages-header\">\n";
        $this->displayTitle($categoryId, $php);
        $this->displayPhpVersionFilter($categoryId, $php);
        $this->displayStatisticsLink($categoryId, $php);
        echo " <div style=\"clear: both;\"></div>\n";
        echo "</div>\n";
    }

    // }}}

    // body display methods
    // {{{ displayCategories()

    /**
     * Displays categories
     *
     * @param array  $categories    categories to display. Array of associative
     *                              arrays describing categories to display.
     * @param array  $subCategories sub-categories of the categories to display.
     *                              Associative array of arrays indexed by
     *                              category id.
     * @param array  $subPackages   sub-packages of the categories to display.
     *                              Associative array of arrays indexed by
     *                              category id.
     * @param string $php           optional. The PHP version on which to
     *                              filter. If not specified, defaults to 'all'.
     *
     * @return void
     */
    protected function displayCategories(
        array $categories,
        array $subCategories,
        array $subPackages,
        $php = 'all'
    ) {
        echo "<ul class=\"categories\">\n";

        $totalPackages = 0;
        $count = 0;
        foreach ($categories as $category) {

            if (isset($subCategories[$category['id']])) {
                $categorySubCategories = $subCategories[$category['id']];
            } else {
                $categorySubCategories = array();
            }

            if (isset($subPackages[$category['id']])) {
                $categorySubPackages = $subPackages[$category['id']];
            } else {
                $categorySubPackages = array();
            }

            $this->displayCategory(
                $category,
                $categorySubCategories,
                $categorySubPackages,
                $php,
                $count
            );

            $totalPackages += $category['npackages'];
            $count++;
        }

        echo "</ul>\n";
        echo "<div class=\"categories-clear\"></div>\n";
    }

    // }}}
    // {{{ displayCategory()

    /**
     * Displays a single category in a list of categories
     *
     * @param array $category      an associative array containing information
     *                             about the category to display.
     * @param array $subCategories an array containing associative arrays
     *                             describing the sub-categories of this
     *                             category. Empty array if there are none.
     * @param array $subPackages   an array containing associative arrays
     *                             describing the sub-packages of this
     *                             category. Empty array if there are none.
     *
     * @param string  $php         optional. The PHP version on which to filter.
     *                             If not specified, defaults to 'all'.
     * @param integer $count       optional. The ordinal index of the category
     *                             being displayed.
     *
     * @return void
     */
    protected function displayCategory(
        array $category,
        array $subCategories,
        array $subPackages,
        $php = 'all',
        $count = 0
    ) {
        $subLinks = array();

        // sub-category links
        foreach ($subCategories as $subCategory) {
            $link = $this->getScriptName() . $this->getQueryString(
                $subCategory['id'],
                $subCategory['name'],
                $php
            );

            $subLinks[] = '<a href="' . $link . '"'
                . ' class="category-sub-category"'
                . ' title="' . htmlspecialchars($subCategory['summary']) . '"'
                . '>' . htmlspecialchars($subCategory['name']) . '</a>';

            if (count($subLinks) >= self::MAX_CATEGORY_SUB_LINKS) {
                break;
            }
        }

        // sub-package links
        if (count($subLinks) < self::MAX_CATEGORY_SUB_LINKS) {
            foreach ($subPackages as $subPackage) {
                $subLinks[] = '<a href="/package/'
                    . urlencode($subPackage['name']) . '"'
                    . ' class="category-package"'
                    . ' title="'
                    . htmlspecialchars($subPackage['summary']) . '"'
                    . '>' . htmlspecialchars($subPackage['name']) . '</a>';

                if (count($subLinks) >= self::MAX_CATEGORY_SUB_LINKS) {
                    break;
                }
            }
        }

        if (count($subLinks) >= self::MAX_CATEGORY_SUB_LINKS) {
            // UTF-8 ellipsis
            $subLinks[] = "\xe2\x80\xa6";
        }
        $subLinks = "   " . implode(",\n   ", $subLinks);

        // wrap categories
        if ($count % 3 === 0) {
            $class = 'category category-clear';
        } else {
            $class = 'category';
        }

        echo " <li id=\"category-{$category['id']}\" class=\"{$class}\">\n";
        echo "  <h3>\n";

        $link = $this->getScriptname() . $this->getQueryString(
            $category['id'],
            $category['name'],
            $php
        );

        $categoryName         = htmlspecialchars($category['name']);
        $categoryPackageCount = htmlspecialchars($category['npackages']);

        echo "   <a href=\"{$link}\">";
        echo "<span class=\"category-title\">{$categoryName}</span> ";
        echo "<span class=\"category-count\">{$categoryPackageCount}</span>";
        echo "</a>\n";
        echo "  </h3>\n";
        echo "  <div>\n";

        echo $subLinks . "\n";

        echo "  </div>\n";
        echo " </li>\n";
    }

    // }}}
    // {{{ displayPackages()

    /**
     * Displays a list of packages
     *
     * @param array  $packages the packages to display.
     * @param string $php      optional. The PHP version on which to filter.
     *                         If not specified, defaults to 'all'.
     *
     * @return void
     */
    protected function displayPackages(array $packages, $php = 'all')
    {
        $class = (count($packages) === 0) ?
            'packages packages-none' :
            'packages';

        echo "<div class=\"{$class}\">\n";

        // display no packages message
        if (count($packages) === 0) {
            echo " <h3>Packages</h3>\n";
            echo " No packages found in this category.\n";
        }

        // display packages
        $count = 0;
        foreach ($packages as $package) {
            $this->displayPackage($package, $php, $count);
            $count++;
        }

        echo "</div>\n";
    }

    // }}}
    // {{{ displayPackage()

    /**
     * Displays package information for the specified package
     *
     * @param array   $package associative array containing package information.
     * @param string  $php     optional. The PHP version on which to filter.
     *                         If not specified, defaults to 'all'.
     * @param integer $count   optional. The ordinal index of the package
     *                         being displayed.
     *
     * @return null
     */
    protected function displayPackage(array $package, $php = 'all', $count = 0)
    {
        $packageClass = ($count === 0) ? 'package package-first' : 'package';
        echo " <div class=\"{$packageClass}\">\n";

        echo "  <div class=\"package-info\">\n";

        // name
        $packageName = htmlspecialchars($package['name']);
        echo "   <div class=\"package-title\">\n";
        echo "    <a href=\"package/{$packageName}\">{$packageName}</a>\n";
        echo "   </div>\n";

        // summary
        $packageSummary = trim(htmlspecialchars($package['summary']));
        echo "   <div class=\"package-description\">\n";
        echo "    <p>{$packageSummary}</p>\n";
        echo "   </div>\n";

        // deprecated note
        if (!empty($package['newpk_id'])) {
            echo "   <div class=\"package-notes\">\n";

            $newPackageName = $this->dbh->getOne(
                'SELECT name FROM packages WHERE id = '
                . $this->dbh->quote($package['newpk_id'], 'text')
                . ' ORDER BY id DESC LIMIT 1'
            );

            $newPackageName = htmlspecialchars($newPackageName);

            echo "    <p>This package has been deprecated in favor of "
                . "<a href=\"/package/{$newPackageName}\">"
                . $newPackageName
                . "</a>.</p>";

            echo "   </div>\n";
        }

        echo "  </div>\n";

        echo "  <div class=\"package-more-info\">\n";
        echo "   <table class=\"package-data\">\n";
        echo "    <tbody>\n";

        // status
        switch ($package['status']) {
        case 'stable':
            $state = '<span class="package-stable">stable</span>';
            break;
        case 'beta':
            $state = '<span class="package-beta">beta</span>';
            break;
        case 'alpha':
        default:
            $state = '<span class="package-alpha">alpha</span>';
            break;
        }

        $timestamp      = strtotime($package['releasedate']);
        $packageVersion = htmlspecialchars($package['version']);
        $packageDateIso = htmlspecialchars(date('c', $timestamp));
        $packageDate    = htmlspecialchars(date('Y-m-d', $timestamp));
        echo "     <tr>\n";
        echo "      <th>Status:</th>\n";
        echo "      <td>"
            . $packageVersion . " "
            . "(" . $state . ") "
            . "released on <abbr class=\"date\" title=\"{$packageDateIso}\">"
            . $packageDate . "</abbr>"
            . "</td>\n";

        echo "     </tr>\n";

        // license
        $packageLicense       = htmlspecialchars($package['license']);
        $packageLicenseStatus = license::isGood($package['license']);
        $packageLicenseLink   = license::getLink($package['license']);
        $packageLicenseClass  = ($packageLicenseStatus) ?
            'package-license-good' :
            'package-license-bad';

        echo "     <tr>\n";
        echo "      <th>License:</th>\n";
        echo "      <td>";
        echo "<span class=\"{$packageLicenseClass}\">";
        if ($packageLicenseLink === null) {
            echo $packageLicense;
        } else {
            echo "<a href=\"{$packageLicenseLink}\">{$packageLicense}</a>";
        }
        echo "</span>";
        echo "</td>\n";
        echo "     </tr>\n";

        // maintained
        echo "     <tr>\n";
        echo "      <th>Maintained:</th>\n";
        echo "      <td>";
        if ($package['unmaintained'] == 0) {
            echo "Yes";
        } else {
            echo "<span class=\"package-unmaintained\">No</span>";
        }
        echo "</td>\n";
        echo "     </tr>\n";

        // bugs
        if ($package['numbugs'] == 0) {
            $packageBugs = 'none';
        } else {
            $packageBugs = $package['numbugs'];
        }
        $packageBugsLink = 'bugs/search.php?cmd=display&amp;package_name[]='
            . urlencode($package['name']);

        echo "     <tr>\n";
        echo "      <th>Open Bugs:</th>\n";
        echo "      <td>";
        echo "<a href=\"{$packageBugsLink}\">{$packageBugs}</a>";
        echo "</td>\n";
        echo "     </tr>\n";

        echo "    </tbody>\n";
        echo "   </table>\n";
        echo "  </div>\n";

        echo " </div>\n";
    }

    // }}}
    // {{{ displayBody()

    /**
     * Displays the body of the category package browser
     *
     * @param integer $categoryId optional. The current category id. Null if
     *                            displaying the top-level category.
     * @param string  $php        optional. The PHP version to filter no. If
     *                            not specified, defaults to 'all'.
     *
     * @return void
     */
    protected function displayBody($categoryId = null, $php = 'all')
    {
        $categories    = $this->getCategories($categoryId, $php);
        $subCategories = $this->getSubCategories($categoryId, $php);
        $subPackages   = $this->getSubPackages($categoryId, $php);

        if (empty($categoryId)) {
            $this->displayCategories(
                $categories,
                $subCategories,
                $subPackages,
                $php
            );
        } else {
            $packages = $this->getPackages($categoryId, $php);
            if (count($categories) === 0) {
                $this->displayPackages($packages, $php);
            } else {
                echo "<h3 class=\"packages-sub-header\">\n";
                echo " <span>Sub-Categories</span>\n";
                echo "</h3>\n";

                $this->displayCategories(
                    $categories,
                    $subCategories,
                    $subPackages,
                    $php
                );

                echo "<h3 class=\"packages-sub-header\">\n";
                echo " <span>Packages</span>\n";
                echo "</h3>\n";
                $this->displayPackages($packages, $php);
            }
        }
    }

    // }}}
}

?>
