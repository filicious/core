<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace bit3\filesystem\cache;

/**
 * Class Cache
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
interface Cache
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    public function fetch($key);

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function store($key, $value);
}