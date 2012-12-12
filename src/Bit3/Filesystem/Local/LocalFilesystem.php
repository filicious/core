<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem\Local;

use Bit3\Filesystem\AbstractSimpleFilesystem;
use Bit3\Filesystem\File;
use Bit3\Filesystem\SimpleFile;
use Bit3\Filesystem\SimpleFilesystem;
use Bit3\Filesystem\PublicURLProvider;
use Bit3\Filesystem\Util;

/**
 * Local filesystem adapter.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class LocalFilesystem
	extends AbstractSimpleFilesystem
{
	const CONFIG_CLASS = 'Bit3\Filesystem\Local\LocalFilesystemConfig';

	/**
	 * Prepend a file path with the baseroot and normalize it.
	 *
	 * @param $file the file that shall get rebased
	 *
	 * @return string
	 */
	protected function realPath(File $file)
	{
		return Util::normalizePath($this->getConfig()->getBasePath() . '/' . $file->getPathname());
	}

	/**************************************************************************
	 * Interface Filesystem
	 *************************************************************************/

	/**
	 * Get the root (/) file node.
	 *
	 * @return File
	 */
	public function getRoot()
	{
		return new SimpleFile('/', $this);
	}

	/**
	 * Get a file object for the specific file.
	 *
	 * @param string $path
	 *
	 * @return File
	 */
	public function getFile($path)
	{
		// TODO: avoid directory traversal.
		return new SimpleFile($path, $this);
	}

	/**
	 * Returns available space on filesystem or disk partition.
	 *
	 * @param File $path
	 *
	 * @return int
	 */
	public function getFreeSpace(File $path = null)
	{
		if (!$path) {
			$path = $this->getRoot();
		}

		return disk_free_space($path->getRealPath());
	}

	/**
	 * Returns the total size of a filesystem or disk partition.
	 *
	 * @param File $path
	 *
	 * @return int
	 */
	public function getTotalSpace(File $path = null)
	{
		if (!$path) {
			$path = $this->getRoot();
		}

		return disk_total_space($this->realPath($path));
	}

	/**************************************************************************
	 * Interface SimpleFilesystem
	 *************************************************************************/

	/**
	 * Get the type of this file.
	 *
	 * @return int Type bitmask
	 */
	public function getTypeOf($objFile)
	{
		$type = 0;
		if($objFile->exists()) {
			$filePath = $this->realPath($objFile);
			is_file($filePath) && $type |= File::TYPE_FILE;
			is_link($filePath) && $type |= File::TYPE_LINK;
			is_dir($filePath) && $type |= File::TYPE_DIRECTORY;
		}
		return $type;
	}

	/**
	 * Get the link target of the link.
	 *
	 * @return string
	 */
	public function getLinkTargetOf($objFile)
	{
		return $objFile->isLink() && readlink($this->realPath($objFile));
	}

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getAccessTimeOf($objFile)
	{
		return $objFile->exists() ? fileatime($this->realPath($objFile)) : false;
	}

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setAccessTimeOf($objFile, $time)
	{
		if ($objFile->exists()) {
			return touch($this->realPath($objFile), $this->getModifyTime(), time());
		}
		return false;
	}

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getCreationTimeOf($objFile)
	{
		return $objFile->exists() ? filectime($this->realPath($objFile)) : false;
	}

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getModifyTimeOf($objFile)
	{
		return $objFile->exists() ? filemtime($this->realPath($objFile)) : false;
	}

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setModifyTimeOf($objFile, $time)
	{
		if ($objFile->exists()) {
			return touch($this->realPath($objFile), time(), $objFile->getAccessTime());
		}
		return false;
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
		if ($doNotCreate && !$objFile->exists())
		{
			return false;
		}
		return touch($this->realPath($objFile), $time, $atime);
	}

	/**
	 * Get the size of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getSizeOf($objFile)
	{
		return $objFile->exists() ? filesize($this->realPath($objFile)) : false;
	}

	/**
	 * Get the owner of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getOwnerOf($objFile)
	{
		return $objFile->exists() ? fileowner($this->realPath($objFile)) : false;
	}

	/**
	 * Set the owner of the file denoted by this pathname.
	 *
	 * @param string|int $user
	 *
	 * @return bool
	 */
	public function setOwnerOf($objFile, $user)
	{
		return $objFile->exists() ? chown($this->realPath($objFile), $user) : false;
	}

	/**
	 * Get the group of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getGroupOf($objFile)
	{
		return $objFile->exists() ? filegroup($this->realPath($objFile)) : false;
	}

	/**
	 * Change the group of the file denoted by this pathname.
	 *
	 * @param mixed $group
	 *
	 * @return bool
	 */
	public function setGroupOf($objFile, $group)
	{
		return $objFile->exists() ? chgrp($this->realPath($objFile), $group) : false;
	}

	/**
	 * Get the mode of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getModeOf($objFile)
	{
		return $objFile->exists() ? fileperms($this->realPath($objFile)) : false;
	}

	/**
	 * Set the mode of the file denoted by this pathname.
	 *
	 * @param int  $mode
	 *
	 * @return bool
	 */
	public function setModeOf($objFile, $mode)
	{
		return $objFile->exists() ? chmod($this->realPath($objFile), $mode) : false;
	}

	/**
	 * Test whether this pathname is readable.
	 *
	 * @return bool
	 */
	public function isThisReadable($objFile)
	{
		return $objFile->exists() && is_readable($this->realPath($objFile));
	}

	/**
	 * Test whether this pathname is writeable.
	 *
	 * @return bool
	 */
	public function isThisWritable($objFile)
	{
		// either exist -> direct lookup
		if ($objFile->exists()) {
			return is_writable($this->realPath($objFile));
		}
		// or non existant -> check if we can create it.
		$parent = $objFile->getParent();
		if ($parent) {
			return $parent->isWritable();
		}
		return false;
	}

	/**
	 * Test whether this pathname is executeable.
	 *
	 * @return bool
	 */
	public function isThisExecutable($objFile)
	{
		return $objFile->exists() && is_executable($this->realPath($objFile));
	}

	/**
	 * Checks whether a file or directory exists.
	 *
	 * @return bool
	 */
	public function exists($objFile)
	{
		return file_exists($this->realPath($objFile));
	}

	/**
	 * Delete a file or directory.
	 *
	 * @param File $objFile the file
	 *
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function delete($objFile, $recursive = false, $force = false)
	{
		if ($objFile->isDirectory()) {
			if ($recursive) {
				/** @var File $file */
				foreach ($objFile->ls() as $file) {
					if (!$objFile->delete(true, $force)) {
						return false;
					}
				}
			}
			else if ($objFile->count() > 0) {
				return false;
			}
			return rmdir($this->realPath($objFile));
		}
		else {
			if (!$objFile->isWritable()) {
				if ($force) {
					$objFile->setMode(0666);
				}
				else {
					return false;
				}
			}
			return unlink($this->realPath($objFile));
		}
	}

	/**
	 * Copies file
	 *
	 * @param File $destination
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function copyTo($objFile, File $destination, $parents = false)
	{
		if ($objFile->isDirectory()) {
			// TODO: recursive directory copy.
		}
		else if ($objFile->isFile()) {
			if ($destination instanceof LocalFile) {
				return copy($this->realPath($objFile), $this->realPath($destination));
			}
			else {
				return Util::streamCopy($objFile, $destination);
			}
		}
	}

	/**
	 * Renames a file or directory
	 *
	 * @param File $destination
	 *
	 * @return bool
	 */
	public function moveTo($objFile, File $destination)
	{
		if ($destination instanceof LocalFile) {
			return rename($this->realPath($objFile), $this->realPath($destination));
		}
		else {
			return Util::streamCopy($objFile, $destination) && $objFile->delete();
		}
	}

	/**
	 * Makes directory
	 *
	 * @return bool
	 */
	public function createDirectory($objFile, $parents = false)
	{
		if ($objFile->exists()) {
			return $objFile->isDirectory();
		}
		else if ($parents) {
			// TODO: apply umask.
			return mkdir($this->realPath($objFile), 0777, true);
		}
		else {
			return mkdir($this->realPath($objFile));
		}
	}

	/**
	 * Create new empty file.
	 *
	 * @return bool
	 */
	public function createFile($objFile, $parents = false)
	{
		$parent = $objFile->getParent();
		if ($parents) {
			if (!($parent && $parent->createDirectory(true))) {
				return false;
			}
		}
		else if (!($parent && $parent->isDirectory())) {
			return false;
		}

		return touch($this->realPath($objFile));
	}

	/**
	 * Get contents of the file. Returns <em>null</em> if file does not exists
	 * and <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @return string|null|bool
	 */
	public function getContentsOf($objFile)
	{
		if (!$objFile->exists()) {
			return null;
		}
		if (!$objFile->isFile()) {
			return false;
		}
		return file_get_contents($this->realPath($objFile));
	}

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function setContentsOf($objFile, $content)
	{
		if ($objFile->exists() && !$objFile->isFile()) {
			return false;
		}
		return false !== file_put_contents($this->realPath($objFile), $content);
	}

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function appendContentsTo($objFile, $content)
	{
		if (!$objFile->exists()) {
			return null;
		}
		if (!$objFile->isFile()) {
			return false;
		}
		if (false !== ($f = fopen($this->realPath($objFile), 'ab'))) {
			if (false !== fwrite($f, $content)) {
				fclose($f);
				return true;
			}
			fclose($f);
		}
		return false;
	}

	/**
	 * Truncate a file to a given length. Returns the new length or
	 * <em>false</em> on error (e.a. if file is a directory).
	 * @param int $size
	 *
	 * @return int|bool
	 */
	public function truncate($objFile, $size = 0)
	{
		if (!$objFile->exists()) {
			return null;
		}
		if (!$objFile->isFile()) {
			return false;
		}
		if (false !== ($f = fopen($this->realPath($objFile), 'ab'))) {
			if (false !== ftruncate($f, $size)) {
				fclose($f);
				return $objFile->getSize();
			}
			fclose($f);
		}
		return false;
	}

	/**
	 * Gets an stream for the file. May return <em>null</em> if streaming is not supported.
	 *
	 * @param string $mode
	 *
	 * @return resource|null
	 */
	public function open($objFile, $mode = 'rb')
	{
		return fopen($this->realPath($objFile), $mode);
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
		return finfo_file(FS::getFileInfo(), $this->realPath($objFile), FILEINFO_NONE);
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
		return finfo_file(FS::getFileInfo(), $this->realPath($objFile), FILEINFO_MIME_TYPE);
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
		return finfo_file(FS::getFileInfo(), $this->realPath($objFile), FILEINFO_MIME_ENCODING);
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
		if (!$objFile->exists()) {
			return null;
		}
		if (!$objFile->isFile()) {
			return false;
		}

		return md5_file($this->realPath($objFile), $raw);
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
		if (!$objFile->exists()) {
			return null;
		}
		if (!$objFile->isFile()) {
			return false;
		}

		return sha1_file($this->realPath($objFile), $raw);
	}

	/**
	 * List files.
	 *
	 * @param int|string|callable Multiple list of LIST_* bitmask, glob pattern and callables to filter the list.
	 *
	 * @return array<File>
	 */
	public function lsFile()
	{
		$args = func_get_args();
		$objFile = array_shift($args);

		list($recursive, $bitmask, $globs, $callables, $globSearchPatterns) = Util::buildFilters($objFile, $args);

		$pathname = $objFile->getPathname();

		$files = array();

		$currentFiles = scandir($this->realPath($objFile));

		foreach ($currentFiles as $path) {
			$file = new SimpleFile($pathname . '/' . $path, $this);

			$files[] = $file;

			if ($recursive &&
				$path != '.' &&
				$path != '..' &&
				$file->isDirectory() ||
				count($globSearchPatterns) &&
				Util::applyGlobFilters($file, $globSearchPatterns)
			) {
				$recursiveFiles = $file->ls();

				$files = array_merge(
					$files,
					$recursiveFiles
				);
			}
		}

		$files = Util::applyFilters($files, $bitmask, $globs, $callables);

		return $files;
	}

	/**
	 * Get the real url, e.g. file:/real/path/to/file to the pathname.
	 *
	 * @return string
	 */
	public function getRealURLOf($objFile)
	{
		return 'file:' . $this->realPath($objFile);
	}
}
