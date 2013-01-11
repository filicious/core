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

use Filicious\File;
use Filicious\Filesystem;
use Filicious\FilesystemConfig;
use Filicious\Internals\Adapter;
use Filicious\Internals\Pathname;

/**
 * Local filesystem adapter.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class DelegatorAdapter
	implements Adapter
{
	protected $fs;

	protected $root;

	protected $parent;

	/**
	 * @var Adapter
	 */
	protected $delegate;

	/**
	 * @see Filicious\Internals\Adapter::setFilesystem()
	 */
	public function setFilesystem(Filesystem $fs)
	{
		$this->fs = $fs;
		$this->delegate->setFilesystem($fs);
		return $this;
	}

	/**
	 * @see Filicious\Internals\Adapter::getFilesystem()
	 */
	public function getFilesystem()
	{
		return $this->fs;
	}

	/**
	 * @see Filicious\Internals\Adapter::setRootAdapter()
	 */
	public function setRootAdapter(Adapter $root)
	{
		$this->root = $root;
		$this->delegate->setRootAdapter($root);
		return $this;
	}

	/**
	 * @see Filicious\Internals\Adapter::getRootAdapter()
	 */
	public function getRootAdapter()
	{
		return $this->root;
	}

	/**
	 * @see Filicious\Internals\Adapter::setParentAdapter()
	 */
	public function setParentAdapter(Adapter $parent)
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 * @see Filicious\Internals\Adapter::getParentAdapter()
	 */
	public function getParentAdapter()
	{
		return $this->parent;
	}

	/**
	 * @param string|FilesystemConfig $basepath
	 */
	public function __construct(Adapter $delegate)
	{
		$this->delegate = $delegate;
		$this->delegate->setParentAdapter($this);
	}

	/**
	 * @see Filicious\Internals\Adapter::resolveLocal()
	 */
	public function resolveLocal(Pathname $pathname, &$localAdapter, &$local)
	{
		$this->delegate->resolveLocal($pathname, $localAdapter, $local);
	}

	/**
	 * @see Filicious\Internals\Adapter::isFile()
	 */
	public function isFile(Pathname $pathname)
	{
		return $this->delegate->isFile($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::isDirectory()
	 */
	public function isDirectory(Pathname $pathname)
	{
		return $this->delegate->isDirectory($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::isLink()
	 */
	public function isLink(Pathname $pathname)
	{
		return $this->delegate->isLink($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getAccessTime()
	 */
	public function getAccessTime(Pathname $pathname)
	{
		return $this->delegate->getAccessTime($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::setAccessTime()
	 */
	public function setAccessTime(Pathname $pathname, \DateTime $time)
	{
		$this->delegate->setAccessTime($pathname, $time);
	}

	/**
	 * @see Filicious\Internals\Adapter::getCreationTime()
	 */
	public function getCreationTime(Pathname $pathname)
	{
		return $this->delegate->getCreationTime($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getCreationTime()
	 */
	public function getModifyTime(Pathname $pathname)
	{
		return $this->delegate->getModifyTime($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::setModifyTime()
	 */
	public function setModifyTime(Pathname $pathname, \DateTime $time)
	{
		$this->delegate->setModifyTime($pathname, $time);
	}

	/**
	 * @see Filicious\Internals\Adapter::touch()
	 */
	public function touch(Pathname $pathname, \DateTime $time, \DateTime $atime, $create)
	{
		$this->delegate->touch($pathname, $time, $atime, $create);
	}

	/**
	 * @see Filicious\Internals\Adapter::getSize()
	 */
	public function getSize(Pathname $pathname, $recursive)
	{
		return $this->delegate->getSize($pathname, $recursive);
	}

	/**
	 * @see Filicious\Internals\Adapter::getSize()
	 */
	public function getOwner(Pathname $pathname)
	{
		return $this->delegate->getOwner($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::setOwner()
	 */
	public function setOwner(Pathname $pathname, $user)
	{
		$this->delegate->setOwner($pathname, $user);
	}

	/**
	 * @see Filicious\Internals\Adapter::getGroup()
	 */
	public function getGroup(Pathname $pathname)
	{
		return $this->delegate->getGroup($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::setGroup()
	 */
	public function setGroup(Pathname $pathname, $group)
	{
		$this->delegate->setGroup($pathname, $group);
	}

	/**
	 * @see Filicious\Internals\Adapter::getMode()
	 */
	public function getMode(Pathname $pathname)
	{
		return $this->delegate->getMode($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::setMode()
	 */
	public function setMode(Pathname $pathname, $mode)
	{
		$this->delegate->setModifyTime($pathname, $mode);
	}

	/**
	 * @see Filicious\Internals\Adapter::isReadable()
	 */
	public function isReadable(Pathname $pathname)
	{
		return $this->delegate->isReadable($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::isWritable()
	 */
	public function isWritable(Pathname $pathname)
	{
		return $this->delegate->isWritable($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::isExecutable()
	 */
	public function isExecutable(Pathname $pathname)
	{
		return $this->delegate->isExecutable($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::exists()
	 */
	public function exists(Pathname $pathname)
	{
		return $this->delegate->exists($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::delete()
	 */
	public function delete(Pathname $pathname, $recursive, $force)
	{
		$this->delegate->delete($pathname, $recursive, $force);
	}

	/**
	 * @see Filicious\Internals\Adapter::copyTo()
	 */
	public function copyTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags
	) {
		return $this->delegate->copyTo($srcPathname, $dstPathname, $flags);
	}

	/**
	 * @see Filicious\Internals\Adapter::copyFrom()
	 */
	public function copyFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags
	) {
		return $this->delegate->copyFrom($dstPathname, $srcPathname, $flags);
	}

	/**
	 * @see Filicious\Internals\Adapter::moveTo()
	 */
	public function moveTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags
	) {
		return $this->delegate->moveTo($srcPathname, $dstPathname, $flags);
	}

	/**
	 * @see Filicious\Internals\Adapter::moveFrom()
	 */
	public function moveFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags
	) {
		return $this->delegate->moveFrom($dstPathname, $srcPathname, $flags);
	}

	/**
	 * @see Filicious\Internals\Adapter::createDirectory()
	 */
	public function createDirectory(Pathname $pathname, $parents)
	{
		$this->delegate->createDirectory($pathname, $parents);
	}

	/**
	 * @see Filicious\Internals\Adapter::createFile()
	 */
	public function createFile(Pathname $pathname, $parents)
	{
		$this->delegate->createFile($pathname, $parents);
	}

	/**
	 * @see Filicious\Internals\Adapter::getContents()
	 */
	public function getContents(Pathname $pathname)
	{
		return $this->delegate->getContents($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::setContents()
	 */
	public function setContents(Pathname $pathname, $content, $create)
	{
		$this->delegate->setContents($pathname, $content, $create);
	}

	/**
	 * @see Filicious\Internals\Adapter::appendContents()
	 */
	public function appendContents(Pathname $pathname, $content, $create)
	{
		return $this->delegate->appendContents($pathname, $content, $create);
	}

	/**
	 * @see Filicious\Internals\Adapter::truncate()
	 */
	public function truncate(Pathname $pathname, $size)
	{
		return $this->delegate->truncate($pathname, $size);
	}

	/**
	 * @see Filicious\Internals\Adapter::open()
	 */
	public function open(Pathname $pathname, $mode)
	{
		return $this->delegate->open($pathname, $mode);
	}

	/**
	 * @see Filicious\Internals\Adapter::getStreamURL()
	 */
	public function getStreamURL(Pathname $pathname)
	{
		return $this->delegate->getStreamURL($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getMIMEName()
	 */
	public function getMIMEName(Pathname $pathname)
	{
		return $this->delegate->getMIMEName($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getMIMEType()
	 */
	public function getMIMEType(Pathname $pathname)
	{
		return $this->delegate->getMIMEType($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getMIMEEncoding()
	 */
	public function getMIMEEncoding(Pathname $pathname)
	{
		return $this->delegate->getMIMEEncoding($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getMD5()
	 */
	public function getMD5(Pathname $pathname, $binary)
	{
		return $this->delegate->getMD5($pathname, $binary);
	}

	/**
	 * @see Filicious\Internals\Adapter::getSHA1()
	 */
	public function getSHA1(Pathname $pathname, $binary)
	{
		return $this->delegate->getSHA1($pathname, $binary);
	}

	/**
	 * @see Filicious\Internals\Adapter::ls()
	 */
	public function ls(Pathname $pathname)
	{
		return $this->delegate->ls($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::count()
	 */
	public function count(Pathname $pathname, array $filter)
	{
		return $this->delegate->count($pathname, $filter);
	}

	/**
	 * @see Filicious\Internals\Adapter::getIterator()
	 */
	public function getIterator(Pathname $pathname, array $filter)
	{
		return $this->delegate->getIterator($pathname, $filter);
	}

	/**
	 * @see Filicious\Internals\Adapter::getFreeSpace()
	 */
	public function getFreeSpace(Pathname $pathname)
	{
		return $this->delegate->getFreeSpace($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getTotalSpace()
	 */
	public function getTotalSpace(Pathname $pathname)
	{
		return $this->delegate->getTotalSpace($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::requireExists()
	 */
	public function requireExists(Pathname $pathname)
	{
		return $this->delegate->requireExists($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::checkFile()
	 */
	public function checkFile(Pathname $pathname)
	{
		return $this->delegate->checkFile($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::checkDirectory()
	 */
	public function checkDirectory(Pathname $pathname)
	{
		return $this->delegate->checkDirectory($pathname);
	}
}
