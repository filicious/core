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

namespace Filicious\Test;

use Filicious\Filesystem;
use Filicious\Internals\Adapter;
use Filicious\Internals\Pathname;
use Filicious\Internals\RootAdapter;

class DummyAdapter implements Adapter
{
	public function setFilesystem(Filesystem $fs = null)
	{
	}

	public function getFilesystem()
	{
	}

	public function getRootAdapter()
	{
	}

	public function setParentAdapter(Adapter $parent = null)
	{
	}

	public function getParentAdapter()
	{
	}

	public function resolveLocal(Pathname $pathname, &$localAdapter, &$local)
	{
	}

	public function isFile(Pathname $pathname)
	{
	}

	public function isDirectory(Pathname $pathname)
	{
	}

	public function isLink(Pathname $pathname)
	{
	}

	public function getAccessTime(Pathname $pathname)
	{
	}

	public function setAccessTime(Pathname $pathname, \DateTime $atime)
	{
	}

	public function getCreationTime(Pathname $pathname)
	{
	}

	public function getModifyTime(Pathname $pathname)
	{
	}

	public function setModifyTime(Pathname $pathname, \DateTime $mtime)
	{
	}

	public function touch(Pathname $pathname, \DateTime $time, \DateTime $atime, $create)
	{
	}

	public function getSize(Pathname $pathname, $recursive)
	{
	}

	public function getOwner(Pathname $pathname)
	{
	}

	public function setOwner(Pathname $pathname, $user)
	{
	}

	public function getGroup(Pathname $pathname)
	{
	}

	public function setGroup(Pathname $pathname, $group)
	{
	}

	public function getMode(Pathname $pathname)
	{
	}

	public function setMode(Pathname $pathname, $mode)
	{
	}

	public function isReadable(Pathname $pathname)
	{
	}

	public function isWritable(Pathname $pathname)
	{
	}

	public function isExecutable(Pathname $pathname)
	{
	}

	public function exists(Pathname $pathname)
	{
	}

	public function delete(Pathname $pathname, $recursive, $force)
	{
	}

	public function copyTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags
	) {
	}

	public function copyFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags
	) {
	}

	public function moveTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags
	) {
	}

	public function moveFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags
	) {
	}

	public function createDirectory(Pathname $pathname, $parents)
	{
	}

	public function createFile(Pathname $pathname, $parents)
	{
	}

	public function getContents(Pathname $pathname)
	{
	}

	public function setContents(Pathname $pathname, $content, $create)
	{
	}

	public function appendContents(Pathname $pathname, $content, $create)
	{
	}

	public function truncate(Pathname $pathname, $size)
	{
	}

	public function getStream(Pathname $pathname)
	{
	}

	public function getStreamURL(Pathname $pathname)
	{
	}

	public function ls(Pathname $pathname)
	{
	}

	public function count(Pathname $pathname, array $filter)
	{
	}

	public function getIterator(Pathname $pathname, array $filter)
	{
	}
}
