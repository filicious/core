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
	const IMPLEMENTATION = 'impl';
	
	/**
	 * Create a new filesystem config.
	 *
	 * @param \Traversable $base
	 * @param callable $handler
	 * @return FilesystemConfig
	 */
	public static function newConfig(\Traversable $data = null, Filesystem $fs = null) {
		if(is_array($data) && $fs === null) { // cheap forking
			$config = new static();
			$config->data = $data;
			return $config;
		}
		return new static($data, $fs);
	}
	
	/**
	 * @var array The configuration data
	 */
	protected $data = array();
	
	/**
	 * @var Filesystem|null The filesystem this config is bound to
	 */
	protected $fs;
	
	/**
	 * Create a new filesytem config.
	 * 
	 * @param \Traversable $config Initial parameters to use
	 * @param Filesystem $fs The filesystem this config will be bound to
	 */
	protected function __construct(\Traversable $data = null, Filesystem $fs = null) {
		$this->merge($data);
		if($fs) {
			$this->set(static::IMPLEMENTATION, get_class($fs));
			$this->fs = $fs;
		}
	}
	
	/**
	 * Add all configuration parameter from given traversable.
	 *
	 * @param \Traversable $config The parameters to merge
	 * @return FilesystemConfig This config object
	 * @throws InvalidStateException
	 */
	public function merge(\Traversable $data) {
		if($data !== null) foreach($data as $param => $value) {
			$this->set($param, $value);
		}
		return $this;
	}
	
	/**
	 * Fork this configuration and merge in the given parameters.
	 *
	 * @param \Traversable $config The config to merge in
	 * @param Filesystem The filesystem this config is bound to
	 * @return FilesystemConfig The forked config instance
	 */
	public function fork(\Traversable $config = null) {
		return static::create($this)->merge($config);
	}
	
	/**
	 * Create a new configuration instance with the exact same settings of this
	 * config and bind it to the given Filesystem.
	 *
	 * @param Filesystem The filesystem the new config will be bound to
	 * @return FilesystemConfig The new bound config instance
	 */
	public function bind(Filesystem $fs) {
		return static::create($this, $fs);
	}
	
	/**
	 * Test whether this config is bound to a filesystem.
	 * 
	 * @return bool True if this config is bound to a filesystem; otherwise false
	 */
	public function isBound() {
		return isset($this->fs);
	}
	
	/**
	 * Get the filesystem this config is bound to.
	 * 
	 * @throws InvalidStateException When this config is not bound to filesystem
	 */
	public function getFilesystem() {
		if(!$this->isBound()) {
			throw new \Exception(); // TODO
		}
		return $this->fs;
	}
	
	/**
	 * Set a configuration parameter.
	 *
	 * @param string $param The parameter to change
	 * @param mixed $value The parameter value to set
	 * @return FilesystemConfig This config object
	 * @throws InvalidStateException When the bound filesystem denies the update
	 * 		of the given parameter
	 * @throws ConfigurationException When the bound filesystem decides that the
	 * 		parameter value set is not a valid setting
	 */
	public function set($param, $value) {
		if(!$this->isBound()) {
			$this->data[$param] = $value;
		}
		elseif($param == static::IMPLEMENTATION) {
			throw new Exception(); //InvalidStateException(); // TODO
		}
		else {
			$this->getFilesystem()->notify($this->data, $param, $value);
		} 
		return $this;
	}
	
	/**
	 * Get a configuration parameter, if it exists, otherwise return the passed
	 * default value.
	 *
	 * @param string $param
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($param, $default = null) {
		return $this->has($param) ? $this->data[$param] : $default;
	}
	
	/**
	 * Test whether a configuration parameter exists.
	 *
	 * @param string $param
	 * @return bool
	 */
	public function has($param) {
		return isset($this->data[$param]);
	}
	
	/**
	 * Create a new filesystem with this configuration.
	 * 
	 * @param string $impl The filesystem implementation to use 
	 * @return Filesystem The created filesystem
	 * @throws ConfigurationException If the implementation is missing or the
	 * 		creation of the filesystem fails due to misconfiguration
	 */
	public function create($impl = null) {
		if($impl === null && $this->has(static::IMPLEMENTATION)) {
			$impl = $this->get(static::IMPLEMENTATION);
		}
		
		$impl = strval($impl);
		if(!strlen($impl)) {
			throw new Exception(); //ConfigurationException(); // TODO
		}
		
		return new $impl($this->fork());
	}
	
	/**
	 * @return \ArrayIterator The iterator over this configs params
	 */
	public function getIterator() {
		return new \ArrayIterator($this->data);
	}
	
	/**
	 * @return string The serialized config data
	 */
	public function serialize() {
		return serialize($this->data);
	}
	
	/**
	 * @param string $str The serialized config data
	 * @return void
	 */
	public function unserialize($str) {
		$this->data = unserialize($str);
	}
	
}
