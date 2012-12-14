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
 * A simple file system object.
 * This base class implements the simple file system interface by delegating
 * all calls to the given file object instance.
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
abstract class AbstractSimpleFilesystem
	extends AbstractFilesystem
	implements SimpleFilesystem
{
	/**
	 * @param FilesystemConfig $config
	 */
	public function __construct(FilesystemConfig $config, PublicURLProvider $provider = null)
	{
		parent::__construct($config, $provider);
	}

	/**
	 * Sets access and modification time of file.
	 *
	 * @param File $file the file to modify
	 * @param int  $time
	 * @param int  $atime
	 *
	 * @return bool
	 */
	public function touch($file, $time = null, $atime = null, $doNotCreate = false)
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
	public function isThisFile($file)
	{
		return (bool) ($file->getType() & File::TYPE_FILE);
	}

	/**
	 * Test whether this pathname is a link.
	 *
	 * @return bool
	 */
	public function isThisLink($file)
	{
		return (bool) ($file->getType() & File::TYPE_LINK);
	}

	/**
	 * Test whether this pathname is a directory.
	 *
	 * @return bool
	 */
	public function isThisDirectory($file)
	{
		return (bool) ($file->getType() & File::TYPE_DIRECTORY);
	}

	/**
	 * Returns the the path of this pathname's parent, or <em>null</em> if this pathname does not name a parent directory.
	 *
	 * @return File|null
	 */
	public function getParentOf($file)
	{
		return $file->getPathname() == '/' ? null : $this->getFile(dirname($file->getPathname()));
	}

	/**
	 * Test whether this pathname is readable.
	 *
	 * @return bool
	 */
	public function isThisReadable($file)
	{
		return ($mode = $file->getMode()) ? $mode & 0444 : false;
	}

	/**
	 * Test whether this pathname is writeable.
	 *
	 * @return bool
	 */
	public function isThisWritable($file)
	{
		return ($mode = $file->getMode()) ? $mode & 0222 : false;
	}

	/**
	 * Test whether this pathname is executeable.
	 *
	 * @return bool
	 */
	public function isThisExecutable($file)
	{
		return ($mode = $file->getMode()) ? $mode & 0111 : false;
	}

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMENameOf($file)
	{
		if (!($file->exists() && $file->isFile())) {
			return null;
		}

		return finfo_buffer(FS::getFileInfo(), $file->getContents(), FILEINFO_NONE);
	}

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMETypeOf($file)
	{
		if (!($file->exists() && $file->isFile())) {
			return null;
		}

		return finfo_buffer(FS::getFileInfo(), $file->getContents(), FILEINFO_MIME_TYPE);
	}

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMEEncodingOf($file)
	{
		if (!($file->exists() && $file->isFile())) {
			return null;
		}

		return finfo_buffer(FS::getFileInfo(), $file->getContents(), FILEINFO_MIME_ENCODING);
	}

	/**
	 * Calculate the md5 hash of this file.
	 * Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param bool $raw Return binary hash, instead of string hash.
	 *
	 * @return string|null
	 */
	public function getMD5Of($file, $raw = false)
	{
		if (!($file->exists() && $file->isFile())) {
			return null;
		}

		return md5($file->getContents());
	}

	/**
	 * Calculate the sha1 hash of this file.
	 * Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param bool $raw Return binary hash, instead of string hash.
	 *
	 * @return string|null
	 */
	public function getSHA1Of($file, $raw = false)
	{
		if (!($file->exists() && $file->isFile())) {
			return null;
		}

		return sha1($file->getContents());
	}

	/**
	 * Get a public url, e.g. http://www.example.com/path/to/public/file to the file.
	 *
	 * @return string
	 */
	public function getPublicURLOf($file)
	{
		$publicURLProvider = $this->getPublicURLProvider();

		return $publicURLProvider ? $publicURLProvider->getPublicURL($file) : false;
	}

	/**
	 * Get a public url, e.g. http://www.example.com/path/to/public/file to the file.
	 *
	 * @return string
	 */
	public function countFile()
	{
		$args = func_get_args();
		$file = array_shift($args);
		return count(call_user_func_array(array($file, 'ls'), $args));
	}

	/**
	 * iterator for file.
	 *
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 *
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 */
	public function getIteratorOf()
	{
		$args = func_get_args();
		$file = array_shift($args);
		return new ArrayIterator(call_user_func_array(array($file, 'ls'), $args));
	}
}
