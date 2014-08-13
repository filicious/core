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
use Filicious\Plugin\DiskSpace\DiskSpaceAwareAdapterInterface;
use Filicious\Plugin\Hash\HashAwareAdapterInterface;
use Filicious\Plugin\Link\LinkAwareAdapterInterface;
use Filicious\Plugin\Mime\MimeAwareAdapterInterface;

/**
 * Local filesystem adapter.
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 */
abstract class AbstractDelegatorAdapter
	extends AbstractAdapter
{

	/**
	 * Select the delegate.
	 *
	 * @param \Filicious\Internals\Pathname $pathname
	 *
	 * @return \Filicious\Internals\Adapter
	 */
	abstract protected function selectDelegate(Pathname $pathname = null);

	/**
	 * {@inheritdoc}
	 */
	public function resolveLocal(Pathname $pathname, &$localAdapter, &$local)
	{
		return $this->selectDelegate($pathname)->resolveLocal($pathname, $localAdapter, $local);
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
		return $this;
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
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function touch(Pathname $pathname, \DateTime $time, \DateTime $atime, $create)
	{
		$this->selectDelegate($pathname)->touch($pathname, $time, $atime, $create);
		return $this;
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
		return $this;
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
		return $this;
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
		return $this;
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
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function copyTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags
	) {
		$this->selectDelegate($srcPathname)->copyTo($srcPathname, $dstPathname, $flags);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function copyFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags
	) {
		$this->selectDelegate($srcPathname)->copyFrom($dstPathname, $srcPathname, $flags);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function moveTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags
	) {
		$this->selectDelegate($srcPathname)->moveTo($srcPathname, $dstPathname, $flags);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function moveFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags
	) {
		$this->selectDelegate($srcPathname)->moveFrom($dstPathname, $srcPathname, $flags);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function createDirectory(Pathname $pathname, $parents)
	{
		$this->selectDelegate($pathname)->createDirectory($pathname, $parents);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function createFile(Pathname $pathname, $parents)
	{
		$this->selectDelegate($pathname)->createFile($pathname, $parents);
		return $this;
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
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function appendContents(Pathname $pathname, $content, $create)
	{
		$this->selectDelegate($pathname)->appendContents($pathname, $content, $create);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function truncate(Pathname $pathname, $size)
	{
		$this->selectDelegate($pathname)->truncate($pathname, $size);
		return $this;
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
}
