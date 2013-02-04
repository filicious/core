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

namespace Filicious;

function is_traversable($thingee)
{
	return is_array($thingee) || $thingee instanceof \Traversable;
}

/**
 * A filesystem configuration
 *
 * @package filicious-core
 * @author  Oliver Hoff <oliver@hofff.com>
 */
class FilesystemConfig
	implements \Serializable, \IteratorAggregate
{
	/**
	 * @var string Configuration parameter for Filesystem implementation to be used
	 */
	const IMPLEMENTATION = 'IMPL';

	/**
	 * @var string Configuration parameter for base path to use
	 */
	const BASEPATH = 'BASEPATH';

	/**
	 * @var string Configuration parameter for base path to use
	 */
	const CREATE_BASEPATH = 'CREATE_BASEPATH';

	/**
	 * @var string Configuration parameter for default mode to use when creating
	 *         new files
	 */
	const DEFAULTMODE = 'DEFAULTMODE';

	/**
	 * @var string Configuration parameter for hostname to use
	 */
	const HOST = 'HOST';

	/**
	 * @var string Configuration parameter for port to use
	 */
	const PORT = 'PORT';

	/**
	 * @var string Configuration parameter for username to use
	 */
	const USERNAME = 'USERNAME';

	/**
	 * @var string Configuration parameter for password to use
	 */
	const PASSWORD = 'PASSWORD';

	/**
	 * @var string
	 */
	const STREAM_SUPPORTED = 'STREAM_SUPPORTED';

	/**
	 * @var string
	 */
	const STREAM_SCHEME = 'STREAM_SCHEME';

	/**
	 * @var string
	 */
	const STREAM_HOST = 'STREAM_HOST';

	/**
	 * Create a new filesystem config.
	 *
	 * @param \Traversable|array $base
	 * @param callable           $handler
	 *
	 * @return FilesystemConfig
	 */
	public static function newConfig($data = null)
	{
		// cheap forking
		if (is_array($data)) {
			// do not use static here!
			$config       = new self();
			$config->data = $data;
			return $config;
		}
		// do not use static here!
		return new self($data);
	}

	/**
	 * @var array The configuration data
	 */
	protected $data = array();

	/**
	 * Create a new filesytem config.
	 *
	 * @param \Traversable|array $data Initial parameters to use
	 * @param Filesystem         $fs   The filesystem this config will be bound to
	 */
	public function __construct($data = null)
	{
		$this->merge($data);
	}

	/**
	 * Check if this config is mutable.
	 *
	 * @return bool
	 */
	public function isMutable()
	{
		return true;
	}

	/**
	 * Check if this config is immutable.
	 *
	 * @return bool
	 */
	public function isImmutable()
	{
		return false;
	}

	/**
	 * Open this config for changes.
	 *
	 * @return FilesystemConfig
	 */
	public function open() {
		return $this;
	}

	/**
	 * Commit changes to the bound filesystem/adapter.
	 *
	 * @return bool
	 */
	public function commit() {
		return false;
	}

	/**
	 * Set a configuration parameter.
	 *
	 * @param string $param The parameter to change
	 * @param mixed  $value The parameter value to set
	 *
	 * @return FilesystemConfig This config object
	 * @throws InvalidStateException When the bound filesystem denies the update
	 *         of the given parameter
	 * @throws ConfigurationException When the bound filesystem decides that the
	 *         parameter value set is not a valid setting
	 */
	public function set($param, $value = null, $path = 'global')
	{
		$this->data[$path][$param] = $value;
		return $this;
	}

	/**
	 * Get a configuration parameter, if it exists, otherwise return the passed
	 * default value.
	 *
	 * @param string $param
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get($param, $default = null, $path = 'global')
	{
		if ($this->has($param, $path)) {
			return $this->data[$path][$param];
		}
		if ($path != 'global' && $this->has($param, 'global')) {
			return $this->data['global'][$param];
		}
		return $default;
	}

	/**
	 * Test whether a configuration parameter exists.
	 *
	 * @param string $param
	 *
	 * @return bool
	 */
	public function has($param, $path = 'global')
	{
		return array_key_exists($path, $this->data) && array_key_exists($param, $this->data[$path]);
	}

	/**
	 * Add all configuration parameter from given traversable.
	 *
	 * @param \Traversable|array $data The parameters to merge
	 *
	 * @return FilesystemConfig This config object
	 * @throws InvalidStateException When the bound filesystem denies the update
	 *         of the given parameter
	 * @throws ConfigurationException When the bound filesystem decides that the
	 *         parameter value set is not a valid setting
	 */
	public function merge($data = null, $path = null)
	{
		if ($data !== null) {
			if (!is_traversable($data)) {
				throw new \InvalidArgumentException();
			}
			foreach ($data as $param => $values) {
				if (is_traversable($values)) {
					$path = $path ?: $param;
					foreach ($values as $param => $value) {
						$this->set($param, $value, $path);
					}
				}
				else {
					$this->set($param, $values, $path ?: 'global');
				}
			}
		}
		return $this;
	}

	/**
	 * Fork this configuration and merge in the given parameters.
	 *
	 * @param \Traversable|array $data      The config to merge in
	 * @param                    Filesystem The filesystem this config is bound to
	 *
	 * @return FilesystemConfig The forked config instance
	 */
	public function fork($data = null)
	{
		// do not use static here!
		return self::newConfig($this->data)
			->merge($data);
	}

	/**
	 * Get the bound filesystem.
	 *
	 * @return Filesystem
	 */
	public function getFilesystem()
	{
		return null;
	}

	/**
	 * Test whether this config is bound to a filesystem.
	 *
	 * @return bool True if this config is bound to a filesystem; otherwise false
	 */
	public function isBound()
	{
		return false;
	}

	/**
	 * Create a new filesystem with this configuration.
	 *
	 * @param string $impl The filesystem implementation to use
	 *
	 * @return Filesystem The created filesystem
	 * @throws ConfigurationException If the implementation is missing or the
	 *         creation of the filesystem fails due to misconfiguration
	 */
	/*
	public function create($impl = null)
	{
		if ($impl === null && $this->has(static::IMPLEMENTATION)) {
			$impl = $this->get(static::IMPLEMENTATION);
		}

		try {
			$clazz = new \ReflectionClass(strval($impl));
			return $clazz->newInstance($this->fork());
		}
		catch (Exception $e) {
			throw new \Exception('', 0, $e); //ConfigurationException(); // TODO
		}
	}
	*/

	/**
	 * @return \ArrayIterator The iterator over this configs params
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}

	/**
	 * @return string The serialized config data
	 */
	public function serialize()
	{
		return json_encode($this->data);
	}

	/**
	 * @param string $str The serialized config data
	 *
	 * @return void
	 */
	public function unserialize($str)
	{
		$this->data = json_decode($str);
	}

	/**
	 * You should use <em>FilesystemConfig::fork()</em>.
	 *
	 * @return void
	 */
	public function __clone()
	{
		return $this->fork();
	}

	/**
	 * Return serialized string representation of this configuration.
	 *
	 * @return string Serialized representation
	 */
	public function __toString()
	{
		return $this->serialize();
	}
}
