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

use Filicious\SimpleFilesystem;

/**
 * A file object
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class SimpleFile
	implements File
{
	/**
	 * @var string
	 */
	protected $pathname;

	/**
	 * @var SimpleFilesystem
	 */
	protected $fs;

	public function __construct($pathname, SimpleFilesystem $fs)
	{
		$this->pathname = Util::normalizePath('/' . $pathname);

		$this->fs = $fs;
	}

	/**
	 * Get the underlaying filesystem for this pathname.
	 *
	 * @return SimpleFilesystem
	 */
	public function getFilesystem()
	{
		return $this->fs;
	}

	public function isFile()
	{
		return $this
			->getFilesystem()
			->isThisFile($this);
	}

	public function isLink()
	{
		return $this
			->getFilesystem()
			->isThisLink($this);
	}

	public function isDirectory()
	{
		return $this
			->getFilesystem()
			->isThisDirectory($this);
	}

	public function getType()
	{
		return $this
			->getFilesystem()
			->getTypeOf($this);
	}

	/**
	 * Returns the absolute pathname.
	 *
	 * @return string
	 */
	public function getPathname()
	{
		return $this->pathname;
	}

	public function getLinkTarget()
	{
		return $this
			->getFilesystem()
			->getLinkTargetOf($this);
	}

	public function getBasename($suffix = null)
	{
		return basename($this->getPathname(), $suffix);
	}

	public function getExtension()
	{
		$basename = $this->getBasename();
		$pos      = strrpos($basename, '.');

		if ($pos !== false) {
			return substr($basename, $pos + 1);
		}

		return null;
	}

	public function getParent()
	{
		return $this
			->getFilesystem()
			->getParentOf($this);
	}

	public function getAccessTime()
	{
		return $this
			->getFilesystem()
			->getAccessTimeOf($this);
	}

	public function setAccessTime($time)
	{
		return $this
			->getFilesystem()
			->setAccessTimeOf($this, $time);
	}

	public function getCreationTime()
	{
		return $this
			->getFilesystem()
			->getCreationTimeOf($this);
	}

	public function getModifyTime()
	{
		return $this
			->getFilesystem()
			->getModifyTimeOf($this);
	}

	public function setModifyTime($time)
	{
		return $this
			->getFilesystem()
			->setModifyTimeOf($this, $time);
	}

	public function touch($time = null, $atime = null, $doNotCreate = false)
	{
		return $this
			->getFilesystem()
			->touch($this, $time, $atime, $doNotCreate);
	}

	public function getSize()
	{
		return $this
			->getFilesystem()
			->getSizeOf($this);
	}

	public function getOwner()
	{
		return $this
			->getFilesystem()
			->getOwnerOf($this);
	}

	public function setOwner($user)
	{
		return $this
			->getFilesystem()
			->setOwnerOf($this, $user);
	}

	public function getGroup()
	{
		return $this
			->getFilesystem()
			->getGroupOf($this);
	}

	public function setGroup($group)
	{
		return $this
			->getFilesystem()
			->setGroupOf($this, $group);
	}

	public function getMode()
	{
		return $this
			->getFilesystem()
			->getModeOf($this);
	}

	public function setMode($mode)
	{
		return $this
			->getFilesystem()
			->setModeOf($this, $mode);
	}

	public function isReadable()
	{
		return $this
			->getFilesystem()
			->isThisReadable($this);
	}

	public function isWritable()
	{
		return $this
			->getFilesystem()
			->isThisWritable($this);
	}

	public function isExecutable()
	{
		return $this
			->getFilesystem()
			->isThisExecutable($this);
	}

	public function exists()
	{
		return $this
			->getFilesystem()
			->exists($this);
	}

	public function delete($recursive = false, $force = false)
	{
		return $this
			->getFilesystem()
			->delete($this, $recursive, $force);
	}

	public function copyTo(File $destination, $parents = false)
	{
		return $this
			->getFilesystem()
			->copyTo($this, $destination, $parents);
	}

	public function moveTo(File $destination)
	{
		return $this
			->getFilesystem()
			->moveTo($this, $destination);
	}

	public function createDirectory($parents = false)
	{
		return $this
			->getFilesystem()
			->createDirectory($this, $parents);
	}

	public function createFile($parents = false)
	{
		return $this
			->getFilesystem()
			->createFile($this, $parents);
	}

	public function getContents()
	{
		return $this
			->getFilesystem()
			->getContentsOf($this);
	}

	public function setContents($content)
	{
		return $this
			->getFilesystem()
			->setContentsOf($this, $content);
	}

	public function appendContents($content)
	{
		return $this
			->getFilesystem()
			->appendContentsTo($this, $content);
	}

	public function truncate($size = 0)
	{
		return $this
			->getFilesystem()
			->truncate($this, $size);
	}

	public function open($mode = 'rb')
	{
		return $this
			->getFilesystem()
			->open($this, $mode);
	}

	public function getMIMEName()
	{
		return $this
			->getFilesystem()
			->getMIMENameOf($this);
	}

	public function getMIMEType()
	{
		return $this
			->getFilesystem()
			->getMIMETypeOf($this);
	}

	public function getMIMEEncoding()
	{
		return $this
			->getFilesystem()
			->getMIMEEncodingOf($this);
	}

	public function getMD5($raw = false)
	{
		return $this
			->getFilesystem()
			->getMD5Of($this, $raw);
	}

	public function getSHA1($raw = false)
	{
		return $this
			->getFilesystem()
			->getSHA1Of($this, $raw);
	}

	public function ls()
	{
		return call_user_func_array(
			array($this->getFilesystem(), 'lsFile'),
			array_merge(array($this), func_get_args())
		);
	}

	public function getRealURL()
	{
		return $this
			->getFilesystem()
			->getRealURLOf($this);
	}

	public function getPublicURL()
	{
		return $this
			->getFilesystem()
			->getPublicURLOf($this);
	}

	public function count()
	{
		return call_user_func_array(
			array($this->getFilesystem(), 'countFile'),
			array_merge(array($this), func_get_args())
		);
	}

	public function getIterator()
	{
		return call_user_func_array(
			array($this->getFilesystem(), 'getIteratorOf'),
			array_merge(array($this), func_get_args())
		);
	}

	public function __toString()
	{
		return $this->getPathname();
	}
}
