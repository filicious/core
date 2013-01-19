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

use Filicious\Internals\Adapter;
use Filicious\Internals\BoundFilesystemConfig;
use Filicious\Internals\RootAdapter;
use Filicious\Internals\Pathname;

/**
 * Virtual filesystem structure.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Filesystem
{
	/**
	 * @var BoundFilesystemConfig
	 */
	protected $config;
	
	protected $adapter;

	/**
	 * @param FilesystemConfig|Adapter $root
	 */
	public function __construct($root, $config = null)
	{
		$this->adapter = new RootAdapter($this);
		$this->config = new BoundFilesystemConfig($this->adapter);
		$this->config->open();

		if (is_traversable($config)) {
			$this->config->merge($config);
		}

		if ($root instanceof FilesystemConfig) {
			$this->config->merge($root);
		}
		else if ($root instanceof Adapter) {
			$this->adapter->setDelegate($root);

			$this->config->linkConfig('/', $root->getConfig());
		}
		else {
			throw new \InvalidArgumentException(); // TODO
		}

		$this->config->commit();
	}

	public function getConfig()
	{
		return $this->config;
	}
	
	/**
	 * Get the root (/) file node.
	 *
	 * @return File
	 */
	public function getRoot()
	{
		return $this->getFile(); // same as ->getFile('/') and ->getFile('')
	}

	/**
	 * Get a file object for the specific file.
	 *
	 * @param string $path
	 *
	 * @return File
	 */
	public function getFile($path = null)
	{
		// cheap recreate of File object
		if ($path instanceof Pathname && $path->rootAdapter() == $this->adapter) {
			return new File($this, $path);
		}

		$pathname = implode('/', static::getPathnameParts($path));
		strlen($pathname) && $pathname = '/' . $pathname;
		return new File($this, new Pathname($this->adapter, $pathname));
	}
	
	public static function getPathnameParts($path)
	{
		$path = strval($path);
		if(!strlen($path)) {
			return array();
		}
		$path = str_replace('\\', '/', $path);
		$path = preg_replace('@^(?>[a-zA-Z]:)?[/\s]+|[/\s]+$@', '', $path); // TODO how to handle win pathnames?
		$parts = array();
		
		foreach (explode('/', $path) as $part) {
			if($part === '..') {
				array_pop($parts);
			}
			elseif($part !== '.' && strlen($part)) {
				$parts[] = $part;
			}
		}
		
		return $parts;
	}

// 	/**
// 	 * Create a temporary file and return the file object.
// 	 *
// 	 * @param string $prefix
// 	 *
// 	 * @return File
// 	 */
// 	public static function createTempFile($prefix) {
// // 		// create a temporary file
// // 		$pathname = tempnam($this->getBasePath(), $prefix);

// // 		// remove the base path from pathname
// // 		$file = substr($pathname, strlen($this->getBasePath()));

// // 		// create new local file object
// // 		$file = $this->getFile($file);

// 		return $file;
// 	}

// 	/**
// 	 * Create a temporary directory and return the file object.
// 	 *
// 	 * @param string $prefix
// 	 *
// 	 * @return File
// 	 */
// 	public static function createTempDirectory($prefix) {
// // 		// create a temporary file
// // 		$file = $this->createTempFile($prefix);

// // 		// delete the file and...
// // 		$file->delete();

// // 		// finally create a directory
// // 		$file->createDirectory();

// 		// return the local file object
// 		return $file;
// 	}

	
// 	/**
// 	 * @var string|null
// 	 */
// 	protected static $tempFilesystemConfig = null;
	
// 	/**
// 	 * Get the default temporary directory.
// 	 *
// 	 * @return string
// 	 */
// 	public static function getTempFilesystemConfig()
// 	{
// 		if(!static::$tempFilesystemConfig) {
// 			$config = FilesystemConfig::create();
// 			$config->addAdapterConfig(array(
// // 				'impl' => 'Filicious\Local\LocalAdapter',
// 				'base' => sys_get_temp_dir()
// 			));
// 			$this->setTempFilesystemConfig($config);
// 		}
// 		return static::$tempFilesystemConfig;
// 	}
	
// 	/**
// 	 * Set the default temporary directory.
// 	 * Warning: Changing this pass will only new initialized TemporaryFilesystem's!
// 	 *
// 	 * @param string $tempPath
// 	 */
// 	public static function setTempFilesystemConfig(FilesystemConfig $config = null)
// 	{
// 		static::$tempFilesystemConfig = $config;
// 	}
	
// 	/**
// 	 * @return TemporaryFilesystem
// 	 */
// 	public static function getTempFilesystem()
// 	{
// 		return static::getTempFilesystemConfig()->getFilesystem();
// 	}
	
}
