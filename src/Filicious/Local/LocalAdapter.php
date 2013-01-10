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

use Filicious\Internals\Adapter;
use Filicious\Internals\AbstractAdapter;
use Filicious\Exception\FilesystemException;
use Filicious\Exception\FilesystemOperationException;
Use Filicious\Stream\BuildInStream;

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

	public function __construct(Filesystem $fs, Adapter $root, Adapter $parent)
	{
		parent::__construct($fs, $root, $parent);
	}

	/**
	 * @see Filicious\Internals\Adapter::isFile()
	 */
	public function isFile($pathname, $local)
	{
		if (!$this->exists($pathname, $local)) {
			return false;
		}

		return is_file($this->basepath . $local);
	}

	/**
	 * @see Filicious\Internals\Adapter::isDirectory()
	 */
	public function isDirectory($pathname, $local)
	{
		if (!$this->exists($pathname, $local)) {
			return false;
		}

		return is_dir($this->basepath . $local);
	}

	/**
	 * @see Filicious\Internals\Adapter::isLink()
	 */
	public function isLink($pathname, $local)
	{
		if (!$this->exists($pathname, $local)) {
			return false;
		}

		return is_link($this->basepath . $local);
	}

	/**
	 * @see Filicious\Internals\Adapter::getAccessTime()
	 */
	public function getAccessTime($pathname, $local)
	{
		$this->requireExists($pathname, $local);

		try {
			$atime = fileatime($this->basepath . $local);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($atime === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get access time of %s.', $pathname)
			);
		}

		return $atime;
	}

	/**
	 * @see Filicious\Internals\Adapter::setAccessTime()
	 */
	public function setAccessTime($pathname, $local, \DateTime $time)
	{
		$this->requireExists($pathname, $local);

		try {
			$result = touch(
				$this->basepath . $local,
				$this->getModifyTime($pathname, $local),
				$time->getTimestamp()
			);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not set access time for %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::getCreationTime()
	 */
	public function getCreationTime($pathname, $local)
	{
		$this->requireExists($pathname, $local);

		try {
			$ctime = filectime($this->basepath . $local);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($ctime === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get creation time of %s.', $pathname)
			);
		}

		return $ctime;
	}

	/**
	 * @see Filicious\Internals\Adapter::getCreationTime()
	 */
	public function getModifyTime($pathname, $local)
	{
		$this->requireExists($pathname, $local);

		try {
			$mtime = filemtime($this->basepath . $local);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($mtime === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get creation time of %s.', $pathname)
			);
		}

		return $mtime;
	}

	/**
	 * @see Filicious\Internals\Adapter::setModifyTime()
	 */
	public function setModifyTime($pathname, $local, \DateTime $time)
	{
		$this->requireExists($pathname, $local);

		try {
			$result = touch(
				$this->basepath . $local,
				$time->getTimestamp(),
				$this->getAccessTime($pathname, $local)
			);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not set modify time for %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::touch()
	 */
	public function touch($pathname, $local, \DateTime $time, \DateTime $atime, $create)
	{
		if (!$create) {
			$this->requireExists($pathname, $local);
		}

		try {
			$result = touch(
				$this->basepath . $local,
				$time->getTimestamp(),
				$atime->getTimestamp()
			);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not touch %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::getSize()
	 */
	public function getSize($pathname, $local, $recursive)
	{
		$this->requireExists($pathname, $local);

		// get directory size
		if ($this->isDirectory($pathname, $local)) {
			// calculate complete directory size
			if ($recursive) {
				$size = 0;

				$iterator = $this->getIterator($pathname, $local, array());

				foreach ($iterator as $pathname) {
					$size += $this->fs
						->getFile($pathname)
						->getSize(true);
				}

				return $size;
			}

			// a directory itself has no size (per definition)
			else {
				return 0;
			}
		}

		// get file size
		else {
			try {
				$result = filesize($this->basepath . $local);
			}
			catch (\ErrorException $e) {
				throw new FilesystemOperationException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			if ($result === false) {
				throw new FilesystemOperationException(
					sprintf('Could not get size of %s.', $pathname)
				);
			}

			return $result;
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::getSize()
	 */
	public function getOwner($pathname, $local)
	{
		$this->requireExists($pathname, $local);

		try {
			$owner = fileowner($this->basepath . $local);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($owner === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get owner of %s.', $pathname)
			);
		}

		return $owner;
	}

	/**
	 * @see Filicious\Internals\Adapter::setOwner()
	 */
	public function setOwner($pathname, $local, $user)
	{
		$this->requireExists($pathname, $local);

		try {
			$result = chown(
				$this->basepath . $local,
				$user
			);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not set owner for %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::getGroup()
	 */
	public function getGroup($pathname, $local)
	{
		$this->requireExists($pathname, $local);

		try {
			$owner = filegroup($this->basepath . $local);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($owner === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get group of %s.', $pathname)
			);
		}

		return $owner;
	}

	/**
	 * @see Filicious\Internals\Adapter::setGroup()
	 */
	public function setGroup($pathname, $local, $group)
	{
		$this->requireExists($pathname, $local);

		try {
			$result = chgrp(
				$this->basepath . $local,
				$group
			);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not set owner for %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::getMode()
	 */
	public function getMode($pathname, $local)
	{
		$this->requireExists($pathname, $local);

		try {
			$owner = fileperms($this->basepath . $local);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($owner === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get mode of %s.', $pathname)
			);
		}

		return $owner;
	}

	/**
	 * @see Filicious\Internals\Adapter::setMode()
	 */
	public function setMode($pathname, $local, $mode)
	{
		$this->requireExists($pathname, $local);

		try {
			$result = chmod(
				$this->basepath . $local,
				$mode
			);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not set owner for %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::isReadable()
	 */
	public function isReadable($pathname, $local)
	{
		$this->requireExists($pathname, $local);

		try {
			$readable = is_readable($this->basepath . $local);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($readable === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get readable state of %s.', $pathname)
			);
		}

		return $readable;
	}

	/**
	 * @see Filicious\Internals\Adapter::isWritable()
	 */
	public function isWritable($pathname, $local)
	{
		$this->requireExists($pathname, $local);

		try {
			$writeable = is_writable($this->basepath . $local);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($writeable === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get writeable state of %s.', $pathname)
			);
		}

		return $writeable;
	}

	/**
	 * @see Filicious\Internals\Adapter::isExecutable()
	 */
	public function isExecutable($pathname, $local)
	{
		$this->requireExists($pathname, $local);

		try {
			$executeable = is_executable($this->basepath . $local);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($executeable === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get executeable state of %s.', $pathname)
			);
		}

		return $executeable;
	}

	/**
	 * @see Filicious\Internals\Adapter::exists()
	 */
	public function exists($pathname, $local)
	{
		try {
			$exists = file_exists($this->basepath . $local);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		return $exists;
	}

	/**
	 * @see Filicious\Internals\Adapter::delete()
	 */
	public function delete($pathname, $local, $recursive, $force)
	{
		$this->requireExists($pathname, $local);

		if ($this->isDirectory($pathname, $local)) {
			// TODO Handling $force flag needed here!

			// recursive delete directories
			if ($recursive) {
				$iterator = $this->getIterator($pathname, $local, array());

				foreach ($iterator as $pathname) {
					$this->fs
						->getFile($pathname)
						->delete($recursive, $force);
				}
			}
			else if ($this->count($pathname, $local, array()) > 0) {
				return false;
			}

			try {
				$result = rmdir($this->basepath . $local);
			}
			catch (\ErrorException $e) {
				throw new FilesystemOperationException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			if ($result === false) {
				throw new FilesystemOperationException(
					sprintf('Could not delete directory %s.', $pathname)
				);
			}
		}
		else {
			// Handling $force flag
			if (!$this->isWritable($pathname, $local)) {
				if ($force) {
					$this->setMode($pathname, $local, 0666);
				}
				else {
					return false;
				}
			}

			try {
				$result = unlink($this->basepath . $local);
			}
			catch (\ErrorException $e) {
				throw new FilesystemOperationException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			if ($result === false) {
				throw new FilesystemOperationException(
					sprintf('Could not delete file %s.', $pathname)
				);
			}
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::copyTo()
	 */
	public function copyTo(
		$pathname,
		$local,
		Adapter $dstAdapter,
		$dstPathname,
		$flags
	) {
		$dstAdapter->copyFrom(
			$pathname, // TODO $dstPathname
			$local, // TODO $dstLocal missing!
			$this,
			$pathname,
			$local,
			$flags
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::copyFrom()
	 */
	public function copyFrom(
		$pathname,
		$local,
		Adapter $srcAdapter,
		$srcPathname,
		$srcLocal,
		$flags
	) {
		// TODO the Adapter interface is inconsistent here!
		throw new \Exception('TODO');

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
		// TODO: Implement copyFrom() method.
	}

	/**
	 * @see Filicious\Internals\Adapter::moveTo()
	 */
	public function moveTo(
		$pathname,
		$local,
		Adapter $dstAdapter,
		$dstPathname,
		$flags
	) {
		$dstAdapter->moveFrom(
			$pathname, // TODO $dstPathname
			$local, // TODO $dstLocal missing!
			$this,
			$pathname,
			$local,
			$flags
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::moveFrom()
	 */
	public function moveFrom(
		$pathname,
		$local,
		Adapter $srcAdapter,
		$srcPathname,
		$srcLocal,
		$flags
	) {
		// TODO the Adapter interface is inconsistent here!
		throw new \Exception('TODO');

		if ($destination instanceof LocalFile) {
			return rename($this->basepath . $pathname, $this->realPath($destination));
		}
		else {
			return (bool) stream_copy_to_stream($this->open($state, 'rb'), $dest->open('wb'));
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::createDirectory()
	 */
	public function createDirectory($pathname, $local, $parents)
	{
		// if exists, check if pathname is allready a directory
		if ($this->exists($pathname, $local)) {
			return $this->isDirectory($pathname, $local);
		}

		try {
			// create with parents
			if ($parents) {
				// TODO: apply umask.
				$result = mkdir($this->basepath . $local, 0777, true);
			}
			else {
				$this->requireExists(dirname($pathname), dirname($local));

				$result = mkdir($this->basepath . $local);
			}
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not create directory %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::createFile()
	 */
	public function createFile($pathname, $local, $parents)
	{
		$parent = dirname($pathname);
		if ($parents) {
			try {
				$this->root->createDirectory($parent, $parent, true);
			}
			catch (FilesystemException $e) {
				throw new FilesystemException(
					sprintf('Could not create parent path %s to create file %s!', $parent, $pathname),
					0,
					$e
				);
			}
		}
		else {
			try {
				$this->root->checkDirectory($parent, $parent);
			}
			catch (FilesystemException $e) {
				throw new FilesystemException(
					sprintf('Could not create file %s, parent directory %s does not exists!', $pathname, $parent),
					0,
					$e
				);
			}
		}

		try {
			$result = touch($this->basepath . $local);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not create file %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::getContents()
	 */
	public function getContents($pathname, $local)
	{
		$this->checkFile($pathname, $local);

		try {
			$result = file_get_contents($this->basepath . $local);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get contents of %s.', $pathname)
			);
		}

		return $result;
	}

	/**
	 * @see Filicious\Internals\Adapter::setContents()
	 */
	public function setContents($pathname, $local, $content, $create)
	{
		if (!$create) {
			$this->requireExists($pathname, $local);
		}

		if ($this->exists($pathname, $local)) {
			$this->checkFile($pathname, $local);
		}

		try {
			$result = file_put_contents($this->basepath . $local, $content);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not set contents to %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::appendContents()
	 */
	public function appendContents($pathname, $local, $content, $create)
	{
		if (!$create) {
			$this->requireExists($pathname, $local);
		}

		if ($this->exists($pathname, $local)) {
			$this->checkFile($pathname, $local);
		}

		$f = false;
		try {
			$result = false;

			if (false !== ($f = fopen($this->basepath . $local, 'ab'))) {
				$result = fwrite($f, $content);
				fclose($f);
			}
		}
		catch (\ErrorException $e) {
			if (is_resource($f)) {
				fclose($f);
			}
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not append contents to %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::truncate()
	 */
	public function truncate($pathname, $local, $size)
	{
		$this->checkFile($pathname, $local);

		$f = false;
		try {
			$result = false;

			if (false !== ($f = fopen($this->basepath . $local, 'ab'))) {
				$result = ftruncate($f, $size);
				fclose($f);
			}
		}
		catch (\ErrorException $e) {
			if (is_resource($f)) {
				fclose($f);
			}
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not truncate file %s to %s.', $pathname, $size)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::open()
	 */
	public function open($pathname, $local, $mode)
	{
		$this->checkFile($pathname, $local);

		return new BuildInStream('file://' . $this->basepath . $local, $mode);
	}

	/**
	 * @see Filicious\Internals\Adapter::getStreamURL()
	 */
	public function getStreamURL($pathname, $local)
	{
		// TODO get stream protocol from filesystem!
	}

	/**
	 * @see Filicious\Internals\Adapter::getMIMEName()
	 */
	public function getMIMEName($pathname, $local)
	{
		$this->checkFile($pathname, $local);

		try {
			$finfo = finfo_file(FS::getFileInfo(), $this->basepath . $local, FILEINFO_NONE);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($finfo === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get mime name of %s.', $pathname)
			);
		}

		return $finfo;
	}

	/**
	 * @see Filicious\Internals\Adapter::getMIMEType()
	 */
	public function getMIMEType($pathname, $local)
	{
		$this->checkFile($pathname, $local);

		try {
			$finfo = finfo_file(FS::getFileInfo(), $this->basepath . $local, FILEINFO_MIME_TYPE);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($finfo === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get mime name of %s.', $pathname)
			);
		}

		return $finfo;
	}

	/**
	 * @see Filicious\Internals\Adapter::getMIMEEncoding()
	 */
	public function getMIMEEncoding($pathname, $local)
	{
		$this->checkFile($pathname, $local);

		try {
			$finfo = finfo_file(FS::getFileInfo(), $this->basepath . $local, FILEINFO_MIME_ENCODING);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($finfo === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get mime name of %s.', $pathname)
			);
		}

		return $finfo;
	}

	/**
	 * @see Filicious\Internals\Adapter::getMD5()
	 */
	public function getMD5($pathname, $local, $binary)
	{
		$this->checkFile($pathname, $local);

		try {
			$md5 = md5_file($this->basepath . $local, $binary);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($md5 === false) {
			throw new FilesystemOperationException(
				sprintf('Could not calculate md5 sum of %s.', $pathname)
			);
		}

		return $md5;
	}

	/**
	 * @see Filicious\Internals\Adapter::getSHA1()
	 */
	public function getSHA1($pathname, $local, $binary)
	{
		$this->checkFile($pathname, $local);

		try {
			$md5 = sha1_file($this->basepath . $local, $binary);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($md5 === false) {
			throw new FilesystemOperationException(
				sprintf('Could not calculate md5 sum of %s.', $pathname)
			);
		}

		return $md5;
	}

	/**
	 * @see Filicious\Internals\Adapter::ls()
	 */
	public function ls($pathname, $local)
	{
		$this->checkDirectory($pathname, $local);

		try {
			$files = scandir($this->basepath . $local);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($files === false) {
			throw new FilesystemOperationException(
				sprintf('Could not list contents of %s.', $pathname)
			);
		}

		return array_values(
			array_filter(
				$files,
				function ($file) {
					return $file !== '.' && $file !== '..';
				}
			)
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::getFreeSpace()
	 */
	public function getFreeSpace($pathname, $local)
	{
		if (!$this->isDirectory($pathname, $local)) {
			$pathname = dirname($pathname);
			$local = dirname($local);
		}

		try {
			$diskFreeSpace = disk_free_space($this->basepath . $local);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($diskFreeSpace === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get free space for %s.', $pathname)
			);
		}

		return $diskFreeSpace;
	}

	/**
	 * @see Filicious\Internals\Adapter::getTotalSpace()
	 */
	public function getTotalSpace($pathname, $local)
	{
		if (!$this->isDirectory($pathname, $local)) {
			$pathname = dirname($pathname);
			$local = dirname($local);
		}

		try {
			$diskTotalSpace = disk_total_space($this->basepath . $local);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($diskTotalSpace === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get total space for %s.', $pathname)
			);
		}

		return $diskTotalSpace;
	}
}
