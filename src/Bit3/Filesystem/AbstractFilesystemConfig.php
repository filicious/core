<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem;


/**
 * Skeleton for FilesystemConfig implementors.
 *
 * @package php-filesystem
 * @author  Oliver Hoff <oliver@hofff.com>
 */
abstract class AbstractFilesystemConfig
	implements FilesystemConfig
{

	/**
	 * Flag determining if the config is protected.
	 * Will get set from filesystem, when it initializes itself.
	 *
	 * @var bool
	 */
	protected $immutable;

	/**
	 * Base path to "chroot" or "sandbox" to.
	 *
	 * @var string
	 */
	protected $basePath;

	/* (non-PHPdoc)
	 * @see Bit3\Filesystem.FilesystemConfig::create()
	 */
	public static function create()
	{
		$args = func_get_args();
		$clazz = new \ReflectionClass(get_called_class());
		return $clazz->newInstanceArgs($args);
	}

	protected function __construct()
	{
	}

	/* (non-PHPdoc)
	 * @see Bit3\Filesystem.FilesystemConfig::immutable()
	 */
	public function makeImmutable()
	{
		$this->immutable = true;
		return $this;
	}

	public function __clone()
	{
		$this->immutable = null;
	}

	protected function checkImmutable()
	{
		if($this->immutable) {
			throw new Exception('Config is immutable'); // TODO
		}
		return $this;
	}

	/* (non-PHPdoc)
	 * @see Bit3\Filesystem.FilesystemConfig::getBasePath()
	 */
	public function getBasePath()
	{
		return $this->basePath;
	}

	/* (non-PHPdoc)
	 * @see Bit3\Filesystem.FilesystemConfig::setBasePath()
	 */
	public function setBasePath($basePath)
	{
		$this->checkImmutable()->basePath = (string) $basePath;
		return $this;
	}
}