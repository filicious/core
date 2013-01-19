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

namespace Filicious\Internals;

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
	/**
	 * @var Filesystem
	 */
	protected $fs;

	/**
	 * @var RootAdapter
	 */
	protected $root;

	/**
	 * @var Adapter
	 */
	protected $parent;

	/**
	 * @var Adapter
	 */
	protected $delegate;

	/**
	 * @param string|FilesystemConfig $basepath
	 */
	public function __construct(Adapter $delegate)
	{
		$this->delegate = $delegate;
		$this->delegate->setParentAdapter($this);
	}

	/**
	 * Select the delegate.
	 *
	 * @param \Filicious\Internals\Pathname $pathname
	 *
	 * @return \Filicious\Internals\Adapter
	 */
	protected function selectDelegate(Pathname $pathname = null) {
		return $this->delegate;
	}

	/**
	 * @see Filicious\Internals\Adapter::getConfig()
	 */
	public function getConfig()
	{
		return $this->selectDelegate()->getConfig();
	}

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
	 * @see Filicious\Internals\Adapter::resolveLocal()
	 */
	public function resolveLocal(Pathname $pathname, &$localAdapter, &$local)
	{
		$this->selectDelegate($pathname)->resolveLocal($pathname, $localAdapter, $local);
	}

	/**
	 * @see Filicious\Internals\Adapter::isFile()
	 */
	public function isFile(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->isFile($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::isDirectory()
	 */
	public function isDirectory(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->isDirectory($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::isLink()
	 */
	public function isLink(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->isLink($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getAccessTime()
	 */
	public function getAccessTime(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getAccessTime($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::setAccessTime()
	 */
	public function setAccessTime(Pathname $pathname, \DateTime $time)
	{
		$this->selectDelegate($pathname)->setAccessTime($pathname, $time);
	}

	/**
	 * @see Filicious\Internals\Adapter::getCreationTime()
	 */
	public function getCreationTime(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getCreationTime($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getCreationTime()
	 */
	public function getModifyTime(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getModifyTime($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::setModifyTime()
	 */
	public function setModifyTime(Pathname $pathname, \DateTime $time)
	{
		$this->selectDelegate($pathname)->setModifyTime($pathname, $time);
	}

	/**
	 * @see Filicious\Internals\Adapter::touch()
	 */
	public function touch(Pathname $pathname, \DateTime $time, \DateTime $atime, $create)
	{
		$this->selectDelegate($pathname)->touch($pathname, $time, $atime, $create);
	}

	/**
	 * @see Filicious\Internals\Adapter::getSize()
	 */
	public function getSize(Pathname $pathname, $recursive)
	{
		return $this->selectDelegate($pathname)->getSize($pathname, $recursive);
	}

	/**
	 * @see Filicious\Internals\Adapter::getSize()
	 */
	public function getOwner(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getOwner($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::setOwner()
	 */
	public function setOwner(Pathname $pathname, $user)
	{
		$this->selectDelegate($pathname)->setOwner($pathname, $user);
	}

	/**
	 * @see Filicious\Internals\Adapter::getGroup()
	 */
	public function getGroup(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getGroup($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::setGroup()
	 */
	public function setGroup(Pathname $pathname, $group)
	{
		$this->selectDelegate($pathname)->setGroup($pathname, $group);
	}

	/**
	 * @see Filicious\Internals\Adapter::getMode()
	 */
	public function getMode(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getMode($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::setMode()
	 */
	public function setMode(Pathname $pathname, $mode)
	{
		$this->selectDelegate($pathname)->setMode($pathname, $mode);
	}

	/**
	 * @see Filicious\Internals\Adapter::isReadable()
	 */
	public function isReadable(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->isReadable($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::isWritable()
	 */
	public function isWritable(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->isWritable($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::isExecutable()
	 */
	public function isExecutable(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->isExecutable($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::exists()
	 */
	public function exists(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->exists($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::delete()
	 */
	public function delete(Pathname $pathname, $recursive, $force)
	{
		$this->selectDelegate($pathname)->delete($pathname, $recursive, $force);
	}

	/**
	 * @see Filicious\Internals\Adapter::copyTo()
	 */
	public function copyTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags
	) {
		return $this->selectDelegate($srcPathname)->copyTo($srcPathname, $dstPathname, $flags);
	}

	/**
	 * @see Filicious\Internals\Adapter::copyFrom()
	 */
	public function copyFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags
	) {
		return $this->selectDelegate($srcPathname)->copyFrom($dstPathname, $srcPathname, $flags);
	}

	/**
	 * @see Filicious\Internals\Adapter::moveTo()
	 */
	public function moveTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags
	) {
		return $this->selectDelegate($srcPathname)->moveTo($srcPathname, $dstPathname, $flags);
	}

	/**
	 * @see Filicious\Internals\Adapter::moveFrom()
	 */
	public function moveFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags
	) {
		return $this->selectDelegate($srcPathname)->moveFrom($dstPathname, $srcPathname, $flags);
	}

	/**
	 * @see Filicious\Internals\Adapter::createDirectory()
	 */
	public function createDirectory(Pathname $pathname, $parents)
	{
		$this->selectDelegate($pathname)->createDirectory($pathname, $parents);
	}

	/**
	 * @see Filicious\Internals\Adapter::createFile()
	 */
	public function createFile(Pathname $pathname, $parents)
	{
		$this->selectDelegate($pathname)->createFile($pathname, $parents);
	}

	/**
	 * @see Filicious\Internals\Adapter::getContents()
	 */
	public function getContents(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getContents($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::setContents()
	 */
	public function setContents(Pathname $pathname, $content, $create)
	{
		$this->selectDelegate($pathname)->setContents($pathname, $content, $create);
	}

	/**
	 * @see Filicious\Internals\Adapter::appendContents()
	 */
	public function appendContents(Pathname $pathname, $content, $create)
	{
		return $this->selectDelegate($pathname)->appendContents($pathname, $content, $create);
	}

	/**
	 * @see Filicious\Internals\Adapter::truncate()
	 */
	public function truncate(Pathname $pathname, $size)
	{
		return $this->selectDelegate($pathname)->truncate($pathname, $size);
	}

	/**
	 * @see Filicious\Internals\Adapter::getStream()
	 */
	public function getStream(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getStream($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getStreamURL()
	 */
	public function getStreamURL(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getStreamURL($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getMIMEName()
	 */
	public function getMIMEName(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getMIMEName($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getMIMEType()
	 */
	public function getMIMEType(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getMIMEType($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getMIMEEncoding()
	 */
	public function getMIMEEncoding(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getMIMEEncoding($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getMD5()
	 */
	public function getMD5(Pathname $pathname, $binary)
	{
		return $this->selectDelegate($pathname)->getMD5($pathname, $binary);
	}

	/**
	 * @see Filicious\Internals\Adapter::getSHA1()
	 */
	public function getSHA1(Pathname $pathname, $binary)
	{
		return $this->selectDelegate($pathname)->getSHA1($pathname, $binary);
	}

	/**
	 * @see Filicious\Internals\Adapter::ls()
	 */
	public function ls(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->ls($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::count()
	 */
	public function count(Pathname $pathname, array $filter)
	{
		return $this->selectDelegate($pathname)->count($pathname, $filter);
	}

	/**
	 * @see Filicious\Internals\Adapter::getIterator()
	 */
	public function getIterator(Pathname $pathname, array $filter)
	{
		return $this->selectDelegate($pathname)->getIterator($pathname, $filter);
	}

	/**
	 * @see Filicious\Internals\Adapter::getFreeSpace()
	 */
	public function getFreeSpace(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getFreeSpace($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getTotalSpace()
	 */
	public function getTotalSpace(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getTotalSpace($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::requireExists()
	 */
	public function requireExists(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->requireExists($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::checkFile()
	 */
	public function checkFile(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->checkFile($pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::checkDirectory()
	 */
	public function checkDirectory(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->checkDirectory($pathname);
	}

	/**
	 * Notify about config changes.
	 */
	public function notifyConfigChange()
	{
		return $this->selectDelegate()->notifyConfigChange();
	}
}
