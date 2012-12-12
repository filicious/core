<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem;

/**
 * A simple file system object.
 * This base class implements the simple file system interface by delegating
 * all calls to the given file object instance.
 *
 * @package php-filesystem
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
abstract class AbstractSimpleFilesystem
implements SimpleFilesystem
{
	/**
	 * @var string The name of the config class used by instances of this
	 * 		filesystem implementation. Override in concrete classes to specify
	 * 		another config class.
	 */
	const CONFIG_CLASS = 'Bit3\Filesystem\Local\FilesystemConfig';

	/**
	 * @var FilesystemConfig
	 */
	protected $config;

	/**
	 * @var PublicURLProvider
	 */
	protected $provider;

	/* (non-PHPdoc)
	 * @see Bit3\Filesystem.Filesystem::create()
	*/
	public static function create(FilesystemConfig $config, PublicURLProvider $provider = null)
	{
		// the instanceof operator has lexer issues...
		if(!is_a($config, static::CONFIG_CLASS)) {
			throw new FilesystemException(sprintf(
				'%s requires a config of type %s, given %s',
				get_called_class(),
				static::CONFIG_CLASS,
				get_class($config)
			));
		}

		$args = func_get_args();
		$clazz = new \ReflectionClass(get_called_class());

		return $clazz->newInstanceArgs($args);
	}

	/**
	 * @param FilesystemConfig $config
	 */
	public function __construct(FilesystemConfig $config, PublicURLProvider $provider = null)
	{
		$this->config = clone $config;
		$this->provider = $provider;
		$this->prepareConfig();
		$this->config->makeImmutable();
	}

	/* (non-PHPdoc)
	 * @see Bit3\Filesystem.Filesystem::getConfig()
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Gets called before at construction time before the config is made
	 * immutable. Override in concrete classes to extend or alter behavior.
	 */
	protected function prepareConfig()
	{
		$this->config->setBasePath(Util::normalizePath('/' . $this->config->getBasePath()) . '/');
	}

	/* (non-PHPdoc)
	 * @see Bit3\Filesystem.FilesystemConfig::getPublicURLProvider()
	 */
	public function getPublicURLProvider()
	{
		return $this->provider;
	}

	/* (non-PHPdoc)
	 * @see Bit3\Filesystem.FilesystemConfig::setPublicURLProvider()
	 */
	public function setPublicURLProvider(PublicURLProvider $provider = null)
	{
		$this->provider = $provider;
	}

	/**
	 * Sets access and modification time of file.
	 *
	 * @param File $objFile the file to modify
	 * @param int  $time
	 * @param int  $atime
	 *
	 * @return bool
	 */
	public function touch($objFile, $time = null, $atime = null, $doNotCreate = false)
	{
		if (!$this->exists() && $doNotCreate && (!$this->createFile())) {
			return false;
		}

		if ($time) {
			$this->setModifyTime($time);
		}

		if ($atime) {
			$this->setAccessTime($atime);
		}
	}

	/**
	 * Test whether this pathname is a file.
	 *
	 * @return bool
	 */
	public function isThisFile($objFile)
	{
		return (bool) ($objFile->getType() & File::TYPE_FILE);
	}

	/**
	 * Test whether this pathname is a link.
	 *
	 * @return bool
	 */
	public function isThisLink($objFile)
	{
		return (bool) ($objFile->getType() & File::TYPE_LINK);
	}

	/**
	 * Test whether this pathname is a directory.
	 *
	 * @return bool
	 */
	public function isThisDirectory($objFile)
	{
		return (bool) ($objFile->getType() & File::TYPE_DIRECTORY);
	}

	/**
	 * Returns the the path of this pathname's parent, or <em>null</em> if this pathname does not name a parent directory.
	 *
	 * @return File|null
	 */
	public function getParentOf($objFile)
	{
		return $objFile->getPathname() == '/' ? null : $this->getFile(dirname($objFile->getPathname()));
	}

	/**
	 * Test whether this pathname is readable.
	 *
	 * @return bool
	 */
	public function isThisReadable($objFile)
	{
		return ($mode = $objFile->getMode()) ? $mode & 0444 : false;
	}

	/**
	 * Test whether this pathname is writeable.
	 *
	 * @return bool
	 */
	public function isThisWritable($objFile)
	{
		return ($mode = $objFile->getMode()) ? $mode & 0222 : false;
	}

	/**
	 * Test whether this pathname is executeable.
	 *
	 * @return bool
	 */
	public function isThisExecutable($objFile)
	{
		return ($mode = $objFile->getMode()) ? $mode & 0111 : false;
	}

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMENameOf($objFile)
	{
		if (!($objFile->exists() && $objFile->isFile())) {
			return null;
		}

		return finfo_buffer(FS::getFileInfo(), $objFile->getContents(), FILEINFO_NONE);
	}

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMETypeOf($objFile)
	{
		if (!($objFile->exists() && $objFile->isFile())) {
			return null;
		}

		return finfo_buffer(FS::getFileInfo(), $objFile->getContents(), FILEINFO_MIME_TYPE);
	}

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMEEncodingOf($objFile)
	{
		if (!($objFile->exists() && $objFile->isFile())) {
			return null;
		}

		return finfo_buffer(FS::getFileInfo(), $objFile->getContents(), FILEINFO_MIME_ENCODING);
	}

	/**
	 * Calculate the md5 hash of this file.
	 * Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param bool $raw Return binary hash, instead of string hash.
	 *
	 * @return string|null
	 */
	public function getMD5Of($objFile, $raw = false)
	{
		if (!($objFile->exists() && $objFile->isFile())) {
			return null;
		}

		return md5($objFile->getContents());
	}

	/**
	 * Calculate the sha1 hash of this file.
	 * Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param bool $raw Return binary hash, instead of string hash.
	 *
	 * @return string|null
	 */
	public function getSHA1Of($objFile, $raw = false)
	{
		if (!($objFile->exists() && $objFile->isFile())) {
			return null;
		}

		return sha1($objFile->getContents());
	}

	/**
	 * Get a public url, e.g. http://www.example.com/path/to/public/file to the file.
	 *
	 * @return string
	 */
	public function getPublicURLOf($objFile)
	{
		$publicURLProvider = $this->getPublicURLProvider();

		return $publicURLProvider ? $publicURLProvider->getPublicURL($objFile) : false;
	}

	/**
	 * Get a public url, e.g. http://www.example.com/path/to/public/file to the file.
	 *
	 * @return string
	 */
	public function countFile()
	{
		$args = func_get_args();
		$objFile = array_shift($args);
		return count(call_user_func_array(array($objFile, 'ls'), $args));
	}

	/**
	 * iterator for file.
	 *
	 * Retrieve an external iterator
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 *
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 */
	public function getIteratorOf()
	{
		$args = func_get_args();
		$objFile = array_shift($args);
		return new ArrayIterator(call_user_func_array(array($objFile, 'ls'), $args));
	}
}
