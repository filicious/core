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
 * Class Cache
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
interface Cache
{
	/**
	 * Check if key exists in the data source.
	 *
	 * @param string $key The key used to store the value.
	 *
	 * @return bool
	 */
	public function exists($key);

	/**
	 * Fetch a stored variable from the cache.
	 *
	 * @param string $key The key used to store the value.
	 *
	 * @return mixed
	 */
	public function fetch($key);

	/**
	 * Removes a stored variable from the cache.
	 *
	 * @param string $key The key used to store the value.
	 *
	 * @return bool
	 */
	public function delete($key);

	/**
	 * Cache a variable in the data store.
	 *
	 * @param string $key   Store the variable using this name. Keys are cache-unique,
	 *                      so storing a second value with the same key will overwrite the original value.
	 * @param mixed  $value The variable to store.
	 * @param int    $ttl   Time in seconds Time To Live; store var in the cache for ttl seconds.
	 *                      After the ttl has passed, the stored variable will be expunged from the cache.
	 *                      If no ttl is supplied (or if the ttl is 0),
	 *                      the value will persist until it is removed from the cache manually.
	 *
	 * @return bool
	 */
	public function store($key, $value, $ttl = 0);
}