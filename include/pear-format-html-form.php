<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2004 The PEAR Group                                    |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Author: Martin Jansen <mj@php.net>                                   |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once "HTML/Form.php";

/**
 * Subclass of HTML_Form that provides methods to create special form 
 * fields for the PEAR website.
 *
 * This class is derived from HTML_Form and provides a number of methods
 * that are only useful for the PEAR website.
 *
 * @author  Martin Jansen <mj@php.net>
 * @extends HTML_Form
 * @version $Revision$
 */
class PEAR_Web_Form extends HTML_Form {

    /**
     * Adds a select field containing all available PEAR packages to
     * the form set
     *
     * @param string Name of the select field
     * @param string Title of the select field
     * @param string Default value for the select field (optional)
     * @return void
     */
    function addPackageSelect($name, $title, $default = "") {
        static $values = null;

        if (!is_array($values)) {
            $list = package::listAll(false);

            foreach ($list as $p_name => $package) {
                $values[$p_name] = $p_name;
            }
        }

        $this->addSelect($name, $title, $values, $default, 1);
    }

    /**
     * Adds a select field containing all available PEAR developers to
     * the form set
     *
     * @param string Name of the select field
     * @param string Title of the select field
     * @param string Default value for the select field (optional)
     * @return void
     */
    function addUserSelect($name, $title, $default = "") {
        static $values = null;

        if (!is_array($values)) {
            $list = user::listAll(true);

            foreach ($list as $user) {
                $values[$user['handle']] = $user['handle'] . " (" . $user['name'] . ")";
            }
        }

        $this->addSelect($name, $title, $values, $default, 1);
    }
}
?>
