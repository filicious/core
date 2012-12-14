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

namespace Filicious\Local;

use Filicious\AbstractSimpleFilesystem;
use Filicious\File;
use Filicious\SimpleFile;
use Filicious\SimpleFilesystem;
use Filicious\PublicURLProvider;
use Filicious\Util;

/**
 * Local filesystem adapter.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class LocalFilesystem
	extends AbstractSimpleFilesystem
{
	const CONFIG_CLASS = 'Filicious\Local\LocalFilesystemConfig';

	/**
	 * Prepend a file path with the baseroot and normalize it.
	 *
	 * @param $file the file that shall get rebased
	 *
	 * @return string
	 */
	protected function realPath(File $file)
	{
		return Util::normalizePath(
			$this
				->getConfig()
				->getBasePath() . '/' . $file->getPathname()
		);
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
	public function getTypeOf($file)
	{
		$type = 0;
		if ($file->exists()) {
			$filePath = $this->realPath($file);
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
	public function getLinkTargetOf($file)
	{
		return $file->isLink() && readlink($this->realPath($file));
	}

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getAccessTimeOf($file)
	{
		return $file->exists() ? fileatime($this->realPath($file)) : false;
	}

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setAccessTimeOf($file, $time)
	{
		if ($file->exists()) {
			return touch($this->realPath($file), $this->getModifyTime(), time());
		}
		return false;
	}

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getCreationTimeOf($file)
	{
		return $file->exists() ? filectime($this->realPath($file)) : false;
	}

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getModifyTimeOf($file)
	{
		return $file->exists() ? filemtime($this->realPath($file)) : false;
	}

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setModifyTimeOf($file, $time)
	{
		if ($file->exists()) {
			return touch($this->realPath($file), time(), $file->getAccessTime());
		}
		return false;
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
		if ($doNotCreate && !$file->exists()) {
			return false;
		}
		return touch($this->realPath($file), $time, $atime);
	}

	/**
	 * Get the size of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getSizeOf($file)
	{
		return $file->exists() ? filesize($this->realPath($file)) : false;
	}

	/**
	 * Get the owner of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getOwnerOf($file)
	{
		return $file->exists() ? fileowner($this->realPath($file)) : false;
	}

	/**
	 * Set the owner of the file denoted by this pathname.
	 *
	 * @param string|int $user
	 *
	 * @return bool
	 */
	public function setOwnerOf($file, $user)
	{
		return $file->exists() ? chown($this->realPath($file), $user) : false;
	}

	/**
	 * Get the group of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getGroupOf($file)
	{
		return $file->exists() ? filegroup($this->realPath($file)) : false;
	}

	/**
	 * Change the group of the file denoted by this pathname.
	 *
	 * @param mixed $group
	 *
	 * @return bool
	 */
	public function setGroupOf($file, $group)
	{
		return $file->exists() ? chgrp($this->realPath($file), $group) : false;
	}

	/**
	 * Get the mode of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getModeOf($file)
	{
		return $file->exists() ? fileperms($this->realPath($file)) : false;
	}

	/**
	 * Set the mode of the file denoted by this pathname.
	 *
	 * @param int  $mode
	 *
	 * @return bool
	 */
	public function setModeOf($file, $mode)
	{
		return $file->exists() ? chmod($this->realPath($file), $mode) : false;
	}

	/**
	 * Test whether this pathname is readable.
	 *
	 * @return bool
	 */
	public function isThisReadable($file)
	{
		return $file->exists() && is_readable($this->realPath($file));
	}

	/**
	 * Test whether this pathname is writeable.
	 *
	 * @return bool
	 */
	public function isThisWritable($file)
	{
		// either exist -> direct lookup
		if ($file->exists()) {
			return is_writable($this->realPath($file));
		}
		// or non existant -> check if we can create it.
		$parent = $file->getParent();
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
	public function isThisExecutable($file)
	{
		return $file->exists() && is_executable($this->realPath($file));
	}

	/**
	 * Checks whether a file or directory exists.
	 *
	 * @return bool
	 */
	public function exists($file)
	{
		return file_exists($this->realPath($file));
	}

	/**
	 * Delete a file or directory.
	 *
	 * @param File $file the file
	 *
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function delete($file, $recursive = false, $force = false)
	{
		if ($file->isDirectory()) {
			if ($recursive) {
				/** @var File $file */
				foreach ($file->ls() as $file) {
					if (!$file->delete(true, $force)) {
						return false;
					}
				}
			}
			else if ($file->count() > 0) {
				return false;
			}
			return rmdir($this->realPath($file));
		}
		else {
			if (!$file->isWritable()) {
				if ($force) {
					$file->setMode(0666);
				}
				else {
					return false;
				}
			}
			return unlink($this->realPath($file));
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
	public function copyTo($file, File $destination, $parents = false)
	{
		if ($file->isDirectory()) {
			// TODO: recursive directory copy.
		}
		else if ($file->isFile()) {
			if ($destination instanceof LocalFile) {
				return copy($this->realPath($file), $this->realPath($destination));
			}
			else {
				return Util::streamCopy($file, $destination);
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
	public function moveTo($file, File $destination)
	{
		if ($destination instanceof LocalFile) {
			return rename($this->realPath($file), $this->realPath($destination));
		}
		else {
			return Util::streamCopy($file, $destination) && $file->delete();
		}
	}

	/**
	 * Makes directory
	 *
	 * @return bool
	 */
	public function createDirectory($file, $parents = false)
	{
		if ($file->exists()) {
			return $file->isDirectory();
		}
		else if ($parents) {
			// TODO: apply umask.
			return mkdir($this->realPath($file), 0777, true);
		}
		else {
			return mkdir($this->realPath($file));
		}
	}

	/**
	 * Create new empty file.
	 *
	 * @return bool
	 */
	public function createFile($file, $parents = false)
	{
		$parent = $file->getParent();
		if ($parents) {
			if (!($parent && $parent->createDirectory(true))) {
				return false;
			}
		}
		else if (!($parent && $parent->isDirectory())) {
			return false;
		}

		return touch($this->realPath($file));
	}

	/**
	 * Get contents of the file. Returns <em>null</em> if file does not exists
	 * and <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @return string|null|bool
	 */
	public function getContentsOf($file)
	{
		if (!$file->exists()) {
			return null;
		}
		if (!$file->isFile()) {
			return false;
		}
		return file_get_contents($this->realPath($file));
	}

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function setContentsOf($file, $content)
	{
		if ($file->exists() && !$file->isFile()) {
			return false;
		}
		return false !== file_put_contents($this->realPath($file), $content);
	}

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function appendContentsTo($file, $content)
	{
		if (!$file->exists()) {
			return null;
		}
		if (!$file->isFile()) {
			return false;
		}
		if (false !== ($f = fopen($this->realPath($file), 'ab'))) {
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
	 *
	 * @param int $size
	 *
	 * @return int|bool
	 */
	public function truncate($file, $size = 0)
	{
		if (!$file->exists()) {
			return null;
		}
		if (!$file->isFile()) {
			return false;
		}
		if (false !== ($f = fopen($this->realPath($file), 'ab'))) {
			if (false !== ftruncate($f, $size)) {
				fclose($f);
				return $file->getSize();
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
	public function open($file, $mode = 'rb')
	{
		return fopen($this->realPath($file), $mode);
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
		return finfo_file(FS::getFileInfo(), $this->realPath($file), FILEINFO_NONE);
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
		return finfo_file(FS::getFileInfo(), $this->realPath($file), FILEINFO_MIME_TYPE);
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
		return finfo_file(FS::getFileInfo(), $this->realPath($file), FILEINFO_MIME_ENCODING);
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
		if (!$file->exists()) {
			return null;
		}
		if (!$file->isFile()) {
			return false;
		}

		return md5_file($this->realPath($file), $raw);
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
		if (!$file->exists()) {
			return null;
		}
		if (!$file->isFile()) {
			return false;
		}

		return sha1_file($this->realPath($file), $raw);
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
		$file = array_shift($args);

		list($recursive, $bitmask, $globs, $callables, $globSearchPatterns) = Util::buildFilters($file, $args);

		$pathname = $file->getPathname();

		$files = array();

		$currentFiles = scandir($this->realPath($file));

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
	public function getRealURLOf($file)
	{
		return 'file:' . $this->realPath($file);
	}
}
