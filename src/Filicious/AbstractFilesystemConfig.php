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
 * Skeleton for FilesystemConfig implementors.
 *
 * @package filicious-core
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
	 * @see Filicious.FilesystemConfig::create()
	 */
	public static function create()
	{
		$args  = func_get_args();
		$clazz = new \ReflectionClass(get_called_class());
		return $clazz->newInstanceArgs($args);
	}

	protected function __construct()
	{
	}

	/* (non-PHPdoc)
	 * @see Filicious.FilesystemConfig::immutable()
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
		if ($this->immutable) {
			throw new Exception('Config is immutable'); // TODO
		}
		return $this;
	}

	/* (non-PHPdoc)
	 * @see Filicious.FilesystemConfig::getBasePath()
	 */
	public function getBasePath()
	{
		return $this->basePath;
	}

	/* (non-PHPdoc)
	 * @see Filicious.FilesystemConfig::setBasePath()
	 */
	public function setBasePath($basePath)
	{
		$this->checkImmutable()->basePath = (string) $basePath;
		return $this;
	}
}