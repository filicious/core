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

use Filicious\Internals\AbstractAdapter;

/**
 * Local filesystem adapter.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class LocalAdapter
	extends AbstractAdapter
{
	
	protected $basepath;
	
	public function __construct() {
		parent::__construct();
	}
	
	public function getStreamURL($pathname)
	{
		return 'file://' . $this->basepath . $pathname;
	}
	
	public function getType($pathname)
	{
		$type = 0;
		$pathname = $this->basepath . $pathname;
		is_file($pathname)	&& $type |= File::TYPE_FILE;
		is_link($pathname)	&& $type |= File::TYPE_LINK;
		is_dir($pathname)	&& $type |= File::TYPE_DIRECTORY;
		return $type;
	}
	
	public function getLinkTarget($pathname)
	{
		if($this->isLink($pathname)) { // TODO OH: is this check really needed?
			throw new \Exception(); // TODO
		}
		try {
			$target = readlink($this->basepath . $pathname);
		} catch(\ErrorException $e) {
			throw new \Exception(); // TODO
		}
		if($target === false) {
			throw new \Exception(); // TODO
		}
		// TODO alot here... what if the link target targets at path shadowed by a mount or union?
		return $this->getState($target);
	}
	
	public function getAccessTime($pathname)
	{
		if(!$this->exists($pathname)) { // TODO OH: is this check really needed?
			throw new \Exception(); // TODO
		}
		try {
			$atime = fileatime($this->basepath . $pathname);
		} catch(\ErrorException $e) {
			throw new \Exception(); // TODO
		}
		if($atime === false) {
			throw new \Exception(); // TODO
		}
		return $atime;
	}
	
	public function setAccessTime($pathname, $time)
	{
		if(!$this->exists($state)) {
			throw new \Exception(); // TODO
		}
		try {
			$result = touch($this->basepath . $pathname, $this->getModifyTime(), $time);
		} catch(\ErrorException $e) {
			throw new \Exception(); // TODO
		}
		if($result === false) {
			throw new \Exception(); // TODO
		}
	}
	
	public function getCreationTime($pathname)
	{
		return $file->exists() ? filectime($this->basepath . $pathname) : false;
	}
	
	public function getModifyTime($pathname)
	{
		return $file->exists() ? filemtime($this->basepath . $pathname) : false;
	}
	
	public function setModifyTime($pathname, $time)
	{
		if ($file->exists()) {
			return touch($this->basepath . $pathname, $time, $file->getAccessTime());
		}
		return false;
	}
	
	public function touch($pathname, $time, $atime, $create)
	{
		if($create || $this->exists($pathname)) {
			touch($this->basepath . $pathname, $time, $atime);
		}
	}
	
	public function getSize($pathname)
	{
		return filesize($this->basepath . $pathname);
	}
	
	public function getOwner($pathname)
	{
		return fileowner($this->basepath . $pathname);
	}
	
	public function setOwner($pathname, $user)
	{
		return chown($this->basepath . $pathname, $user);
	}
	
	public function getGroup($pathname)
	{
		return filegroup($this->basepath . $pathname);
	}
	
	public function setGroup($pathname, $group)
	{
		return chgrp($this->basepath . $pathname, $group);
	}
	
	public function getMode($pathname)
	{
		return fileperms($this->basepath . $pathname);
	}
	
	public function setMode($pathname, $mode)
	{
		return chmod($this->basepath . $pathname, $mode);
	}
	
	public function isReadable($pathname)
	{
		return is_readable($this->basepath . $pathname);
	}
	
	public function isWritable($pathname)
	{
		$pathname = $this->basepath . $pathname;
		while(!file_exists($pathname) && '' !== $pathname = dirname($pathname));
		return $pathname === '' ? false : is_writable($pathname);
	}
	
	public function isExecutable($pathname)
	{
		return is_executable($this->basepath . $pathname);
	}
	
	public function exists($pathname)
	{
		return file_exists($this->basepath . $pathname);
	}
	
	public function delete($pathname, $recursive = false, $force = false)
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
			return rmdir($this->basepath . $pathname);
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
			return unlink($this->basepath . $pathname);
		}
	}
	
	public function copyTo($pathname, File $destination, $parents = false)
	{
		if ($file->isDirectory()) {
			// TODO: recursive directory copy.
		}
		else if ($file->isFile()) {
			if (is_a($destination->getAdapter(), __CLASS__)) {
				return copy($this->stat->realpath, $this->realPath($destination));
			}
			else {
				return (bool) stream_copy_to_stream($this->open($state, 'rb'), $dest->open('wb'));
			}
		}
	}
	
	public function moveTo($pathname, File $destination)
	{
		if ($destination instanceof LocalFile) {
			return rename($this->basepath . $pathname, $this->realPath($destination));
		}
		else {
			return (bool) stream_copy_to_stream($this->open($state, 'rb'), $dest->open('wb'));
		}
	}
	
	public function createDirectory($pathname, $parents = false)
	{
		if ($file->exists()) {
			return $file->isDirectory();
		}
		else if ($parents) {
			// TODO: apply umask.
			return mkdir($this->basepath . $pathname, 0777, true);
		}
		else {
			return mkdir($this->basepath . $pathname);
		}
	}
	
	public function createFile($pathname, $parents = false)
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

		return touch($this->basepath . $pathname);
	}
	
	public function getContents($pathname)
	{
		if (!$file->exists()) {
			return null;
		}
		if (!$file->isFile()) {
			return false;
		}
		return file_get_contents($this->basepath . $pathname);
	}
	
	public function setContents($pathname, $content)
	{
		if ($file->exists() && !$file->isFile()) {
			return false;
		}
		return false !== file_put_contents($this->basepath . $pathname, $content);
	}
	
	public function appendContents($pathname, $content)
	{
		if (!$file->exists()) {
			return null;
		}
		if (!$file->isFile()) {
			return false;
		}
		if (false !== ($f = fopen($this->basepath . $pathname, 'ab'))) {
			if (false !== fwrite($f, $content)) {
				fclose($f);
				return true;
			}
			fclose($f);
		}
		return false;
	}
	
	public function truncate($pathname, $size = 0)
	{
		if (!$file->exists()) {
			return null;
		}
		if (!$file->isFile()) {
			return false;
		}
		if (false !== ($f = fopen($this->basepath . $pathname, 'ab'))) {
			if (false !== ftruncate($f, $size)) {
				fclose($f);
				return $file->getSize();
			}
			fclose($f);
		}
		return false;
	}
	
	public function open($pathname, $mode = 'rb')
	{
		return fopen($this->basepath . $pathname, $mode);
	}
	
	public function getMIMEName($pathname)
	{
		return finfo_file(FS::getFileInfo(), $this->basepath . $pathname, FILEINFO_NONE);
	}
	
	public function getMIMEType($pathname)
	{
		return finfo_file(FS::getFileInfo(), $this->basepath . $pathname, FILEINFO_MIME_TYPE);
	}
	
	public function getMIMEEncoding($pathname)
	{
		return finfo_file(FS::getFileInfo(), $this->basepath . $pathname, FILEINFO_MIME_ENCODING);
	}
	
	public function getMD5($pathname, $binary = false)
	{
		if (!$this->isFile($pathname)) {
			throw new Exception(); // TODO
		}
		return md5_file($this->basepath . $pathname, $binary);
	}
	
	public function getSHA1($pathname, $binary)
	{
		if (!$this->isFile($pathname)) {
			throw new Exception(); // TODO
		}
		return sha1_file($this->basepath . $pathname, $binary);
	}
	
	public function ls($pathname)
	{
		$args = func_get_args();
		$file = array_shift($args);

		list($recursive, $bitmask, $globs, $callables, $globSearchPatterns) = Util::buildFilters($file, $args);

		$pathname = $file->getPathname();

		$files = array();

		$currentFiles = scandir($this->basepath . $pathname);

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
	
	public function getFreeSpace($pathname)
	{
		$this->checkDirectory($pathname);
		return disk_free_space($this->basepath . $pathname);
	}

	public function getTotalSpace($pathname)
	{
		$this->checkDirectory($pathname);
		return disk_total_space($this->basepath . $pathname);
	}
	
}
