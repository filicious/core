<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem\Cache;

/**
 * Class ArrayCache
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class ArrayCache implements Cache
{
    protected $array = array();

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function fetch($key)
    {
        return isset($this->array[$key]) ? $this->array[$key] : null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function store($key, $value)
    {
        $this->array[$key] = $value;
    }
}