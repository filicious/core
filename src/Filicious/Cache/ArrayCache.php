<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 * @link    http://filicious.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Filicious\Cache;

/**
 * Class ArrayCache
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class ArrayCache implements Cache
{
	/**
	 * The array cache.
	 *
	 * @var array
	 */
	protected $array = array();

	/**
	 * Check if key exists in the data source.
	 *
	 * @param string $key The key used to store the value.
	 *
	 * @return bool
	 */
	public function exists($key)
	{
		return isset($this->array[$key]) &&
			($this->array[$key] === null || time() < $this->array[$key]);
	}

	/**
	 * Fetch a stored variable from the cache.
	 *
	 * @param string $key The key used to store the value.
	 *
	 * @return mixed
	 */
	public function fetch($key)
	{
		if ($this->exists($key)) {
			return $this->array[$key]['value'];
		}
		return null;
	}

	/**
	 * Removes a stored variable from the cache.
	 *
	 * @param string $key The key used to store the value.
	 *
	 * @return bool
	 */
	public function delete($key)
	{
		if ($this->exists($key)) {
			unset($this->array[$key]);
			return true;
		}
		return false;
	}

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
	public function store($key, $value, $ttl = 0)
	{
		$this->array[$key] = array(
			'value' => $value,
			'tll'   => $ttl > 0 ? time() + $ttl : null
		);
		return true;
	}
}