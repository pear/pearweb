<?php
class pearweb_Channel_REST_Generator
{
    protected $_restdir;
    protected $extra;
    protected $channel;
    private $db;

    public function __construct($base, $db, $channel = PEAR_CHANNELNAME)
    {
        $this->_restdir = $base;
        $this->extra    = '/rest/';
        $this->channel  = $channel;
        $this->db       = $db;
    }

    public function saveAllCategoriesREST()
    {
        include_once 'pear-database-category.php';
        $categories = category::listAll();
        if (PEAR::isError($categories)) {
            return $categories;
        }

        $info = '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allcategories"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allcategories
    http://pear.php.net/dtd/rest.allcategories.xsd">
<ch>' . $this->channel . '</ch>
';
        foreach ($categories as $category){
            $info .= ' <c xlink:href="' . $this->extra . 'c/' .
                urlencode(urlencode($category['name'])) .
                '/info.xml">' .
                htmlspecialchars(utf8_encode($category['name'])) . '</c>
';
        }
        $info .= '</a>';

        $cdir = $this->_restdir . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR;
        if (!is_dir($cdir)) {
            if (!mkdir($cdir, 0777, true)) {
                return PEAR::raiseError('Creating directory '. $cdir . ' failed - Check the permissions');
            }
            @chmod($cdir, 0777);
        }

        $file = $cdir . 'categories.xml';
        if (!file_put_contents($file, $info)) {
            return PEAR::raiseError('Writing to file ' . $file . ' failed - Check the permissions');
        }
        @chmod($file, 0666);
    }

    public function saveCategoryREST($category)
    {
        $cdir = $this->_restdir . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR;
        if (!is_dir($cdir)) {
            if (!mkdir($cdir, 0777, true)) {
                return PEAR::raiseError('Creating directory ' . $cdir . ' failed - Check the permissions');
            }
            @chmod($cdir, 0777);
        }

        $sql = 'SELECT name, description FROM categories WHERE name = ?';
        $category = $this->db->getRow($sql, array($category), DB_FETCHMODE_ASSOC);
        if (PEAR::isError($category)) {
            return $category;
        }

        $query = '
            SELECT p.name AS name
            FROM packages p, categories c
            WHERE p.package_type = ?
            AND p.category = c.id AND c.name = ? AND p.approved = 1';

        $sth = $this->db->getAll($query, array(SITE, $category['name']), DB_FETCHMODE_ASSOC);
        if (PEAR::isError($sth)) {
            return $sth;
        }

        $cndir = $cdir . urlencode($category['name']) . DIRECTORY_SEPARATOR;
        if (!is_dir($cndir)) {
            if (!mkdir($cndir, 0777, true)) {
                return PEAR::raiseError('Creating directory ' . $cndir . ' failed - Check the permissions');
            }
            @chmod($cndir, 0777);
        }

        $category['description'] = htmlspecialchars($category['description']);
        $info = '<?xml version="1.0" encoding="UTF-8" ?>
<c xmlns="http://pear.php.net/dtd/rest.category"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.category
    http://pear.php.net/dtd/rest.category.xsd">
 <n>' . htmlspecialchars($category['name']) . '</n>
 <c>' . $this->channel . '</c>
 <a>' . htmlspecialchars($category['name']) . '</a>
 <d>' . $category['description'] . '</d>
</c>';

        // category info
        $file = $cndir . 'info.xml';
        if (!file_put_contents($file, $info)) {
            return PEAR::raiseError('Writing file ' . $file . ' failed - Check the permissions');
        }
        @chmod($file, 0666);

        $list = '<?xml version="1.0" encoding="UTF-8" ?>
<l xmlns="http://pear.php.net/dtd/rest.categorypackages"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.categorypackages
    http://pear.php.net/dtd/rest.categorypackages.xsd">
';
        foreach ($sth as $package) {
            $list .= ' <p xlink:href="' . $this->extra . 'p/' . strtolower($package['name']) . '">' .
                $package['name'] . '</p>
';
        }
        $list .= '</l>';

        // list packages in a category
        $file = $cndir . 'packages.xml';
        if (!file_put_contents($file, $list)) {
            return PEAR::raiseError('Writing file ' . $file . ' failed - Check the permissions');
        }
        @chmod($file, 0666);
    }

    public function savePackagesCategoryREST($category)
    {
        $cdir = $this->_restdir . DIRECTORY_SEPARATOR . 'c';
        if (!is_dir($cdir)) {
            return;
        }

        // list packages in a category
        $dir = $cdir . DIRECTORY_SEPARATOR . urlencode($category) . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return PEAR::raiseError('Creating directory ' . $dir . ' failed - Check the permissions');
            }
        }

        $pdir = $this->_restdir . DIRECTORY_SEPARATOR . 'p';
        $rdir = $this->_restdir . DIRECTORY_SEPARATOR . 'r';

        include_once 'pear-database-category.php';
        $packages = category::listPackages($category);

        $fullpackageinfo = '<?xml version="1.0" encoding="UTF-8" ?>
<f xmlns="http://pear.php.net/dtd/rest.categorypackageinfo"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.categorypackageinfo
    http://pear.php.net/dtd/rest.categorypackageinfo.xsd">
';
        clearstatcache();
        foreach ($packages as $package) {
            $pmdir = $pdir . DIRECTORY_SEPARATOR . strtolower($package['name']);
            if (!file_exists($pmdir . DIRECTORY_SEPARATOR . 'info.xml')) {
                continue;
            }

            $fullpackageinfo .= '<pi>
';
            $fullpackageinfo .= str_replace($this->_getPackageRESTProlog(), '<p>',
            file_get_contents($pmdir . DIRECTORY_SEPARATOR . 'info.xml'));

            $rmdir = $rdir . DIRECTORY_SEPARATOR . strtolower($package['name']);
            if (file_exists($rmdir . DIRECTORY_SEPARATOR . 'allreleases.xml')) {
                $fullpackageinfo .= str_replace(
                    $this->_getAllReleasesRESTProlog($package['name']), '
<a>
',
                file_get_contents($rmdir . DIRECTORY_SEPARATOR .'allreleases.xml'));
                $files = scandir($rmdir);
                foreach ($files as $entry) {
                    if (strpos($entry, 'deps.') === 0) {
                        $version = str_replace(array('deps.', '.txt'), array('', ''), $entry);
                        $fullpackageinfo .= '
<deps>
 <v>' . $version . '</v>
 <d>' . htmlspecialchars(utf8_encode(file_get_contents($rmdir . DIRECTORY_SEPARATOR . $entry))) . '</d>
</deps>
';
                    }
                }
            }
            $fullpackageinfo .= '</pi>
';
        }
        $fullpackageinfo .= '</f>';

        $file = $dir . 'packagesinfo.xml';
        if (!file_put_contents($file, $fullpackageinfo)) {
            return PEAR::raiseError('Writing file ' . $file . ' failed - Check the permissions');
        }
        @chmod($file, 0666);
    }

    public function deleteCategoryREST($category)
    {
        $dir = $this->_restdir . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . urlencode($category);
        if (!is_dir($dir)) {
            return;
        }

        // remove all category info
        require_once 'System.php';
        System::rm(array('-r', $dir));
    }

    public function saveAllPackagesREST()
    {
        $info = '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>' . $this->channel . '</c>
';
        include_once 'pear-database-package.php';
        foreach (package::listAllNames() as $package) {
            $info .= ' <p>' . $package . '</p>
';
        }
        $info .= '</a>';

        $dir = $this->_restdir . DIRECTORY_SEPARATOR . 'p' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return PEAR::raiseError('Creating directory ' . $dir . ' failed - Check ther permissions');
            }
            @chmod($dir, 0777);
        }

        $file = $dir . 'packages.xml';
        if (!file_put_contents($file, $info)) {
            return PEAR::raiseError('Writing file ' . $file . ' failed - Check the permissions');
        }
        @chmod($file, 0666);
    }

    private function _getPackageRESTProlog()
    {
        return "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n" .
"<p xmlns=\"http://pear.php.net/dtd/rest.package\"" .
'    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"' .
"    xsi:schemaLocation=\"http://pear.php.net/dtd/rest.package" .
'    http://pear.php.net/dtd/rest.package.xsd">';
    }

    public function savePackageREST($package)
    {
        include_once 'pear-database-package.php';
        $package = package::info($package);

        $parent = '';
        $catinfo = $package['category'];
        if (isset($package['parent']) && $package['parent']) {
            $parent = '
 <pa xlink:href="' . $this->extra . 'p/' . $package['parent'] . '">' .
                $package['parent'] . '</pa>';
        }

        $deprecated = '';
        if ($package['new_package']) {
            $dpackage = $package['new_package'];
            $deprecated = '
 <dc>' . $package['new_channel'] . '</dc>
 <dp> ' .
            $dpackage . '</dp>';
        }

        $package['summary']     = htmlspecialchars($package['summary']);
        $package['description'] = htmlspecialchars($package['description']);
        $info = $this->_getPackageRESTProlog() . '
 <n>' . $package['name'] . '</n>
 <c>' . $this->channel . '</c>
 <ca xlink:href="' . $this->extra . 'c/' . htmlspecialchars(urlencode($catinfo)) . '">' .
        htmlspecialchars($catinfo) . '</ca>
 <l>' . $package['license'] . '</l>
 <s>' . $package['summary'] . '</s>
 <d>' . $package['description'] . '</d>
 <r xlink:href="' . $this->extra . 'r/' . strtolower($package['name']) . '"/>' . $parent . $deprecated . '
</p>';

        $dir = $this->_restdir . DIRECTORY_SEPARATOR . 'p' . DIRECTORY_SEPARATOR
                . strtolower($package['name']) . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return PEAR::raiseError('Creating directory ' . $dir . ' failed - Check the permissions');
            }
            @chmod($dir, 0777);
        }

        // package information
        $file = $dir . 'info.xml';
        if (!file_put_contents($file, $info)) {
            return PEAR::raiseError('Writing file ' . $file . ' failed - Check the permissions');
        }
        @chmod($file, 0666);
    }

    public function deletePackageREST($package)
    {
        if (!$package) {
            // don't delete the entire package/release info
            return;
        }

        require_once 'System.php';
        $pdir = $this->_restdir . DIRECTORY_SEPARATOR . 'p' . DIRECTORY_SEPARATOR;
        $rdir = $this->_restdir . DIRECTORY_SEPARATOR . 'r' . DIRECTORY_SEPARATOR;
        // remove all package/release info for this package
        System::rm(array('-r', $pdir . strtolower($package)));
        System::rm(array('-r', $rdir . strtolower($package)));
    }

    private function _getAllReleasesRESTProlog($package)
    {
        return '<?xml version="1.0" encoding="UTF-8" ?>' . "\n" .
'<a xmlns="http://pear.php.net/dtd/rest.allreleases"' . "\n" .
'    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink" ' .
'    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases' . "\n" .
'    http://pear.php.net/dtd/rest.allreleases.xsd">' . "\n" .
' <p>' . $package . '</p>' . "\n" .
' <c>' . $this->channel . '</c>' . "\n";
    }

    private function _getAllReleases2RESTProlog($package)
    {
        return '<?xml version="1.0" encoding="UTF-8" ?>' . "\n" .
'<a xmlns="http://pear.php.net/dtd/rest.allreleases2"' . "\n" .
'    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink" ' .
'    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases2' . "\n" .
'    http://pear.php.net/dtd/rest.allreleases2.xsd">' . "\n" .
' <p>' . $package . '</p>' . "\n" .
' <c>' . $this->channel . '</c>' . "\n";
    }

    public function saveAllReleasesREST($package)
    {
        require_once 'PEAR/PackageFile/Parser/v2.php';
        require_once 'PEAR/Config.php';

        include_once 'pear-database-package.php';
        $pid = package::info($package, 'id');
        $releases = $this->db->getAll('SELECT * FROM releases WHERE package = ? ORDER BY releasedate DESC',
            array($pid), DB_FETCHMODE_ASSOC);
        if (PEAR::isError($releases)) {
            return $releases;
        }

        $deps = $this->db->getAssoc('SELECT `release`, `version` FROM deps WHERE package = ? AND type="php" and relation="ge"', false,
            array($pid));
        if (PEAR::isError($deps)) {
            return $releases;
        }

        $rdir = $this->_restdir . DIRECTORY_SEPARATOR . 'r' . DIRECTORY_SEPARATOR;
        if (!is_dir($rdir)) {
            if (!mkdir($rdir, 0777, true)) {
                return PEAR::raiseError('Creating directory ' . $rdir . ' failed - Check the permissions');
            }
            @chmod($rdir, 0777);
        }

        if (!$releases || !count($releases)) {
            // start from scratch, so that any pulled releases have their REST deleted
            require_once 'System.php';
            System::rm(array('-r', $rdir. strtolower($package)));
            return;
        }

        $info  = $this->_getAllReleasesRESTProlog($package);
        $info2 = $this->_getAllReleases2RESTProlog($package);
        foreach ($releases as $release) {
            $packagexml = $this->db->getOne('SELECT packagexml FROM files WHERE package = ? AND
                `release` = ?', array($pid, $release['id']));
            if (PEAR::isError($packagexml)) {
                return $packagexml;
            }

            $extra = '';
            if (strpos($packagexml, ' version="2.0"')) {
                // little quick hack to determine package.xml version
                $pkg = new PEAR_PackageFile_Parser_v2;
                $config = &PEAR_Config::singleton();
                $pkg->setConfig($config); // configuration is unused for this quick parse
                $pf = $pkg->parse($packagexml, '');
                if ($compat = $pf->getCompatible()) {
                    if (!isset($compat[0])) {
                        $compat = array($compat);
                    }
                    foreach ($compat as $entry) {
                        $extra .= '<co><c>' . $entry['channel'] . '</c>' .
                            '<p>' . $entry['name'] . '</p>' .
                            '<min>' . $entry['min'] . '</min>' .
                            '<max>' . $entry['max'] . '</max>';
                        if (isset($entry['exclude'])) {
                            if (!is_array($entry['exclude'])) {
                                $entry['exclude'] = array($entry['exclude']);
                            }

                            foreach ($entry['exclude'] as $exclude) {
                                $extra .= '<x>' . $exclude . '</x>';
                            }
                        }

                        $extra .= '</co>
';
                    }
                }
            }
            if (!isset($latest)) {
                $latest = $release['version'];
            }

            if ($release['state'] == 'stable' && !isset($stable)) {
                $stable = $release['version'];
            }

            if ($release['state'] == 'beta' && !isset($beta)) {
                $beta = $release['version'];
            }

            if ($release['state'] == 'alpha' && !isset($alpha)) {
                $alpha = $release['version'];
            }

            $info .= ' <r><v>' . $release['version'] . '</v><s>' . $release['state'] . '</s>'
                 . $extra . '</r>
';
            $phpdep = isset($deps[$release['id']]) ? $deps[$release['id']] : '4.0.0';
            $info2 .= ' <r><v>' . $release['version'] . '</v><s>' . $release['state'] . '</s>'
                 . '<m>' . $phpdep . '</m>' . $extra . '</r>
';
        }

        $info .= '</a>';
        $info2 .= '</a>';

        $dir = $rdir . strtolower($package) . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return PEAR::raiseError('Creating directory ' . $dir . ' failed - Check the permissions');
            }
            @chmod($dir, 0777);
        }

        if (!file_put_contents($dir . 'allreleases.xml', $info)) {
            return PEAR::raiseError('Writing file ' . $dir . 'allreleases.xml failed - Check the permissions');
        }
        @chmod($dir . 'allreleases.xml', 0666);

        if (!file_put_contents($dir . 'allreleases2.xml', $info2)) {
            return PEAR::raiseError('Writing file ' . $dir . 'allreleases2.xml failed - Check the permissions');
        }
        @chmod($dir . 'allreleases2.xml', 0666);

        if (!file_put_contents($dir . 'latest.txt', $latest)) {
            return PEAR::raiseError('Writing file ' . $dir . 'latest.txt failed - Check the permissions');
        }
        @chmod($dir . 'latest.txt', 0666);

        // remove .txt in case all releases of this stability were deleted
        @unlink($dir . 'stable.txt');
        @unlink($dir . 'beta.txt');
        @unlink($dir . 'alpha.txt');
        if (isset($stable)) {
            if (!file_put_contents($dir . 'stable.txt', $stable)) {
                return PEAR::raiseError('Writing file ' . $dir . 'stable.txt failed - Check the permissions');
            }
            @chmod($dir . 'stable.txt', 0666);
        }

        if (isset($beta)) {
            if (!file_put_contents($dir . 'beta.txt', $beta)) {
                return PEAR::raiseError('Writing file ' . $dir . 'beta.txt failed - Check the permissions');
            }
            @chmod($dir . 'beta.txt', 0666);
        }

        if (isset($alpha)) {
            if (!file_put_contents($dir . 'alpha.txt', $alpha)) {
                return PEAR::raiseError('Writing file ' . $dir . 'alpha.txt failed - Check the permissions');
            }
            @chmod($dir . 'alpha.txt', 0666);
        }
    }

    public function deleteReleaseREST($package, $version)
    {
        $dir  = $this->_restdir . DIRECTORY_SEPARATOR . 'r'
                . DIRECTORY_SEPARATOR . strtolower($package) . DIRECTORY_SEPARATOR;
        if (@is_dir($dir)) {
            @unlink($dir . $version . '.xml');
            @unlink($dir . 'v2.' . $version . '.xml');
            @unlink($dir . 'package.' . $version . '.xml');
            @unlink($dir . 'deps.' . $version . '.txt');
        }
    }

    public function saveReleaseREST($filepath, $packagexml, $pkgobj, $releasedby, $id)
    {
        $package = $pkgobj->getPackage();
        $version = $pkgobj->getVersion();

        $releasedate = $this->db->getOne('SELECT releasedate FROM releases WHERE id = ?', array($id));

        $info = '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="' . $this->extra . 'p/' . strtolower($package) . '">' . $package . '</p>
 <c>' . $this->channel . '</c>
 <v>' . $version . '</v>
 <st>' . $pkgobj->getState() . '</st>
 <l>' . $pkgobj->getLicense() . '</l>
 <m>' . $releasedby . '</m>
 <s>' . htmlspecialchars($pkgobj->getSummary()) . '</s>
 <d>' .  htmlspecialchars($pkgobj->getDescription()) . '</d>
 <da>' . $releasedate . '</da>
 <n>' . htmlspecialchars($pkgobj->getNotes()) . '</n>
 <f>' . filesize($filepath) . '</f>
 <g>http://' . $this->channel . '/get/' . $package . '-' . $version . '</g>
 <x xlink:href="package.' . $version . '.xml"/>
</r>';
        $d = $pkgobj->getDeps(true);
        $minphp = isset($d['required']) ? $d['required']['php']['min'] : '4.3.0';
        $info2 = '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release2"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release2
    http://pear.php.net/dtd/rest.release2.xsd">
 <p xlink:href="' . $this->extra . 'p/' . strtolower($package) . '">' . $package . '</p>
 <c>' .  $this->channel . '</c>
 <v>' .  $version . '</v>
 <a>' .  $pkgobj->getVersion('api') . '</a>
 <mp>' . $minphp . '</mp>
 <st>' . $pkgobj->getState() . '</st>
 <l>' .  $pkgobj->getLicense() . '</l>
 <m>' .  $releasedby . '</m>
 <s>' .  htmlspecialchars($pkgobj->getSummary()) . '</s>
 <d>' .  htmlspecialchars($pkgobj->getDescription()) . '</d>
 <da>' . $releasedate . '</da>
 <n>' .  htmlspecialchars($pkgobj->getNotes()) . '</n>
 <f>' .  filesize($filepath) . '</f>
 <g>http://' . $this->channel . '/get/' . $package . '-' . $version . '</g>
 <x xlink:href="package.' . $version . '.xml"/>
</r>';

        $dir = $this->_restdir . DIRECTORY_SEPARATOR . 'r'
                . DIRECTORY_SEPARATOR . strtolower($package) . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return PEAR::raiseError('Creating directory ' . $dir . ' failed - Check the permissions');
            }
            @chmod($dir, 0777);
        }

        if (!file_put_contents($dir . $version . '.xml', $info)) {
            return PEAR::raiseError('Creating directory ' . $dir . $version .'.xml failed - Check the permissions');
        }
        @chmod($dir . $version . '.xml', 0666);

        if (!file_put_contents($dir . 'v2.' . $version . '.xml', $info2)) {
            return PEAR::raiseError('Writing file ' . $dir . 'v2.' . $version . '.xml failed - Check the permissions');
        }
        @chmod($dir . 'v2.' . $version . '.xml', 0666);

        if (!file_put_contents($dir . 'package.' . $version . '.xml', $packagexml)) {
            return PEAR::raiseError('Writing file ' . $dir . 'package.' . $version . '.xml failed - Check the permissions');
        }
        @chmod($dir . 'package.' . $version . '.xml', 0666);

        if (!file_put_contents($dir . 'deps.' . $version . '.txt', serialize($pkgobj->getDeps(true)))) {
            return PEAR::raiseError('Writing file ' . $dir . 'deps.' . $version . '.txt failed - Check the permissions');
        }
        @chmod($dir . 'deps.' . $version . '.txt', 0666);
    }

    public function deleteMaintainerREST($handle)
    {
        require_once 'System.php';
        $dir = $this->_restdir . DIRECTORY_SEPARATOR . 'm' . DIRECTORY_SEPARATOR . $handle;
        if (is_dir($dir)) {
            System::rm(array('-r', $dir));
        }
    }

    public function savePackageMaintainerREST($package)
    {
        include_once 'pear-database-package.php';
        $pid = package::info($package, 'id');
        $maintainers = $this->db->getAll('SELECT handle, active, role FROM maintains WHERE package = ?', array($pid),
            DB_FETCHMODE_ASSOC);

        $dir = $this->_restdir . DIRECTORY_SEPARATOR . 'p'
                . DIRECTORY_SEPARATOR . strtolower($package) . DIRECTORY_SEPARATOR;
        if (count($maintainers)) {
            $info2 = '<?xml version="1.0" encoding="UTF-8" ?>
<m xmlns="http://pear.php.net/dtd/rest.packagemaintainers2"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.packagemaintainers2
    http://pear.php.net/dtd/rest.packagemaintainers2.xsd">
';
            $info = '<?xml version="1.0" encoding="UTF-8" ?>
<m xmlns="http://pear.php.net/dtd/rest.packagemaintainers"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.packagemaintainers
    http://pear.php.net/dtd/rest.packagemaintainers.xsd">
';
            $info .= ' <p>' . $package . '</p>
 <c>' . $this->channel . '</c>
';
            $info2 .= ' <p>' . $package . '</p>
 <c>' . $this->channel . '</c>
';
            foreach ($maintainers as $maintainer) {
                $info .= ' <m><h>' . $maintainer['handle'] . '</h><a>' . $maintainer['active'] .
                    '</a></m>' . "\n";
                $info2 .= ' <m><h>' . $maintainer['handle'] . '</h><a>' . $maintainer['active'] .
                    '</a><r>' . $maintainer['role'] . '</r></m>' . "\n";
            }
            $info  .= '</m>';
            $info2 .= '</m>';

            if (!is_dir($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    return PEAR::raiseError('Creating directory ' . $dir . ' failed - Check the permissions');
                }
                @chmod($dir, 0777);
            }

            if (!file_put_contents($dir . 'maintainers.xml', $info)) {
                return PEAR::raiseError('Writing file ' . $dir . 'maintainers.xml failed - Check the permissions');
            }
            @chmod($dir . 'maintainers.xml', 0666);

            if (!file_put_contents($dir . 'maintainers2.xml', $info2)) {
                return PEAR::raiseError('Writing file ' . $dir . 'maintainers2.xml failed - Check the permissions');
            }
            @chmod($dir . 'maintainers2.xml', 0666);
        } else {
            @unlink($dir . 'maintainers.xml');
            @unlink($dir . 'maintainers2.xml');
        }
    }

    public function saveMaintainerREST($maintainer)
    {
        $maintainer = $this->db->getRow('SELECT handle, name, homepage FROM users WHERE handle = ?',
            array($maintainer), DB_FETCHMODE_ASSOC);

        $uri = '';
        if ($maintainer['homepage']) {
            $uri = ' <u>' . htmlspecialchars($maintainer['homepage']) . '</u>' . "\n";
        }

        $info = '<?xml version="1.0" encoding="UTF-8" ?>
<m xmlns="http://pear.php.net/dtd/rest.maintainer"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.maintainer
    http://pear.php.net/dtd/rest.maintainer.xsd">
 <h>' . $maintainer['handle'] . '</h>
 <n>' .  htmlspecialchars($maintainer['name']) . '</n>
' . $uri . '</m>';

        $dir = $this->_restdir . DIRECTORY_SEPARATOR . 'm'
                . DIRECTORY_SEPARATOR . $maintainer['handle'] . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return PEAR::raiseError('Creating directory ' . $dir . ' failed - Check the permissions');
            }
            @chmod($dir, 0777);
        }

        // package information
        $file = $dir .'info.xml';
        if (!file_put_contents($file, $info)) {
            return PEAR::raiseError('Writing file ' . $file . ' failed - Check the permissions');
        }
        @chmod($file, 0666);
    }

    public function saveAllMaintainersREST()
    {
        $info = '<?xml version="1.0" encoding="UTF-8" ?>
<m xmlns="http://pear.php.net/dtd/rest.allmaintainers"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allmaintainers
    http://pear.php.net/dtd/rest.allmaintainers.xsd">' . "\n";

        // package information
        require_once 'Damblan/Karma.php';
        $karma = &new Damblan_Karma($this->db);

        include_once 'pear-database-user.php';
        $maintainers = user::listAllHandles();
        foreach ($maintainers as $maintainer) {
            if (!$karma->has($maintainer['handle'], 'pear.dev')) {
                continue;
            }
            $info .= ' <h xlink:href="/rest/m/' . $maintainer['handle'] . '">' .
                $maintainer['handle'] . '</h>' . "\n";
        }
        $info .= '</m>';

        $dir = $this->_restdir . DIRECTORY_SEPARATOR . 'm' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return PEAR::raiseError('Creating directory ' . $dir . ' failed - Check the permissions');
            }
            @chmod($dir, 0777);
        }

        $file = $dir . 'allmaintainers.xml';
        if (!file_put_contents($file, $info)) {
            return PEAR::raiseError('Writing file ' . $file . ' failed - Check the permissions');
        }
        @chmod($file, 0666);
    }
}
