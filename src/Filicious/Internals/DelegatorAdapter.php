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

use Filicious\Filesystem;

/**
 * Local filesystem adapter.
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Oliver Hoff <oliver@hofff.com>
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
	 * @param string $basepath
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
	protected function selectDelegate(Pathname $pathname = null)
	{
		return $this->delegate;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setFilesystem(Filesystem $fs)
	{
		$this->fs = $fs;
		$this->delegate->setFilesystem($fs);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilesystem()
	{
		return $this->fs;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRootAdapter(Adapter $root)
	{
		$this->root = $root;
		$this->delegate->setRootAdapter($root);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRootAdapter()
	{
		return $this->root;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setParentAdapter(Adapter $parent)
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParentAdapter()
	{
		return $this->parent;
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolveLocal(Pathname $pathname, &$localAdapter, &$local)
	{
		$this->selectDelegate($pathname)->resolveLocal($pathname, $localAdapter, $local);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isFile(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->isFile($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isDirectory(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->isDirectory($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isLink(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->isLink($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAccessTime(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getAccessTime($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setAccessTime(Pathname $pathname, \DateTime $time)
	{
		$this->selectDelegate($pathname)->setAccessTime($pathname, $time);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCreationTime(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getCreationTime($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModifyTime(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getModifyTime($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setModifyTime(Pathname $pathname, \DateTime $time)
	{
		$this->selectDelegate($pathname)->setModifyTime($pathname, $time);
	}

	/**
	 * {@inheritdoc}
	 */
	public function touch(Pathname $pathname, \DateTime $time, \DateTime $atime, $create)
	{
		$this->selectDelegate($pathname)->touch($pathname, $time, $atime, $create);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSize(Pathname $pathname, $recursive)
	{
		return $this->selectDelegate($pathname)->getSize($pathname, $recursive);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOwner(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getOwner($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setOwner(Pathname $pathname, $user)
	{
		$this->selectDelegate($pathname)->setOwner($pathname, $user);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getGroup(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getGroup($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setGroup(Pathname $pathname, $group)
	{
		$this->selectDelegate($pathname)->setGroup($pathname, $group);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMode(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getMode($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setMode(Pathname $pathname, $mode)
	{
		$this->selectDelegate($pathname)->setMode($pathname, $mode);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isReadable(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->isReadable($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isWritable(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->isWritable($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isExecutable(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->isExecutable($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function exists(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->exists($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(Pathname $pathname, $recursive, $force)
	{
		$this->selectDelegate($pathname)->delete($pathname, $recursive, $force);
	}

	/**
	 * {@inheritdoc}
	 */
	public function copyTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags
	) {
		return $this->selectDelegate($srcPathname)->copyTo($srcPathname, $dstPathname, $flags);
	}

	/**
	 * {@inheritdoc}
	 */
	public function copyFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags
	) {
		return $this->selectDelegate($srcPathname)->copyFrom($dstPathname, $srcPathname, $flags);
	}

	/**
	 * {@inheritdoc}
	 */
	public function moveTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags
	) {
		return $this->selectDelegate($srcPathname)->moveTo($srcPathname, $dstPathname, $flags);
	}

	/**
	 * {@inheritdoc}
	 */
	public function moveFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags
	) {
		return $this->selectDelegate($srcPathname)->moveFrom($dstPathname, $srcPathname, $flags);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createDirectory(Pathname $pathname, $parents)
	{
		$this->selectDelegate($pathname)->createDirectory($pathname, $parents);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createFile(Pathname $pathname, $parents)
	{
		$this->selectDelegate($pathname)->createFile($pathname, $parents);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContents(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getContents($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setContents(Pathname $pathname, $content, $create)
	{
		$this->selectDelegate($pathname)->setContents($pathname, $content, $create);
	}

	/**
	 * {@inheritdoc}
	 */
	public function appendContents(Pathname $pathname, $content, $create)
	{
		return $this->selectDelegate($pathname)->appendContents($pathname, $content, $create);
	}

	/**
	 * {@inheritdoc}
	 */
	public function truncate(Pathname $pathname, $size)
	{
		return $this->selectDelegate($pathname)->truncate($pathname, $size);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getStream(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getStream($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getStreamURL(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getStreamURL($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMIMEName(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getMIMEName($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMIMEType(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getMIMEType($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMIMEEncoding(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getMIMEEncoding($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMD5(Pathname $pathname, $binary)
	{
		return $this->selectDelegate($pathname)->getMD5($pathname, $binary);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSHA1(Pathname $pathname, $binary)
	{
		return $this->selectDelegate($pathname)->getSHA1($pathname, $binary);
	}

	/**
	 * {@inheritdoc}
	 */
	public function ls(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->ls($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function count(Pathname $pathname, array $filter)
	{
		return $this->selectDelegate($pathname)->count($pathname, $filter);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator(Pathname $pathname, array $filter)
	{
		return $this->selectDelegate($pathname)->getIterator($pathname, $filter);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFreeSpace(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getFreeSpace($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTotalSpace(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->getTotalSpace($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function requireExists(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->requireExists($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkFile(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->checkFile($pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkDirectory(Pathname $pathname)
	{
		return $this->selectDelegate($pathname)->checkDirectory($pathname);
	}
}
