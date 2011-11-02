<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

/**
 * @ignore
 */
require_once 'Services/ProjectHoneyPot.php';

/**
 * Simple service class to wrap creation.
 *
 * @category Helper
 * @package  pearweb
 * @author   Till Klampaeckel <till@php.net>
 */
class Pearweb_Service_HoneyPot
{
    /**
     * @param string $key API key
     */
    protected $key;

    /**
     * @param Services_ProjectHoneyPot $sphp
     */
    protected $sphp;

    /**
     * __construct
     *
     * @param string $key API key
     *
     * @return $this
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Convenience to create a resolver, or to swap it out.
     *
     * @return mixed
     */
    public function getResolver()
    {
        //For testing on windows!
        //$resolver = new Net_DNS2_Resolver;
        //$resolver->nameservers = array('8.8.8.8');

        return null;
    }

    /**
     * Return/create {@link Services_ProjectHoneyPot}.
     *
     * @return Services_ProjectHoneyPot
     */
    public function getHoneyPot()
    {
        if ($this->sphp instanceof Services_ProjectHoneyPot) {
            return $this->sphp;
        }
        return new Services_ProjectHoneyPot($this->key, $this->getResolver());
    }

    /**
     * Inject.
     *
     * @param Services_ProjectHoneyPot $sphp
     *
     * @return $this
     */
    public function setHoneyPot(Services_ProjectHoneyPot $sphp)
    {
        $this->sphp = $sphp;
        return $this;
    }

    /**
     * Check the IP against HoneyPot.
     *
     * @return void
     * @throws RuntimeException
     */
    public function check($ip)
    {
        $sphp    = $this->getHoneyPot();
        $results = $sphp->query($ip);

        foreach ($results as $status) {
            foreach ($status as $ip => $item) {
                if (empty($item) || $item == false) {
                   continue;
                }

                foreach ($status as $ip => $item) {
                    if (empty($item)) {
                        continue;
                    }

                    if ($status->getLastActivity() < 30
                        && (
                            $status->isCommentSpammer()
                            || $status->isHarvester()
                            || $status->isSearchEngine()
                        )) {

                        // Check about the last 30 days
                        $errors = 'We can not allow you to continue since your IP has been marked suspicious within the past 30 days
                            by the http://projecthoneypot.org/, if that was done in error then please contact ' .
                            PEAR_DEV_EMAIL . ' as well as the projecthoneypot people to resolve the issue.';

                        throw new RuntimeException($errors);
                    }
                }
            }
        }
    }
}
