<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package    php-filesystem
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @link       http://bit3.de
 * @license    http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem;

/**
 * Abstract base class for File implementation that are delegating a part of
 * their method calls to an underlying File object.
 *
 * @package    php-filesystem
 * @author     Oliver Hoff <oliver@hofff.com>
 */
abstract class AbstractFileDelegator
	implements File
{
	/**
	 * @var File The file object method calls are delegated to.
	 */
	private $delegate;
	
	private $fs;

	public function __construct(File $delegate, Filesystem $fs = null)
	{
		$this->setDelegate($delegate);
		$this->setFilesystem($fs);
	}

	protected function setDelegate(File $delegate)
	{
		$this->delegate = $delegate;
	}
	
	protected function setFilesystem(Filesystem $fs = null)
	{
		$this->fs = $fs;
	}

	public function getDelegate()
	{
		return $this->delegate;
	}

	public function __call($method, $args)
	{
		return call_user_func_array(array($this->getDelegate(), $method), $args);
	}

	public function getFilesystem()
	{
		return $this->fs ?: $this->getDelegate()->getFilesystem();
	}

	public function isFile()
	{
		return $this->getDelegate()->isFile();
	}

	public function isLink()
	{
		return $this->getDelegate()->isLink();
	}

	public function isDirectory()
	{
		return $this->getDelegate()->isDirectory();
	}

	public function getType()
	{
		return $this->getDelegate()->getType();
	}

	public function getPathname()
	{
		return $this->getDelegate()->getPathname();
	}

	public function getLinkTarget()
	{
		return $this->getDelegate()->getLinkTarget();
	}

	public function getBasename($suffix = null)
	{
		return $this->getDelegate()->getBasename($suffix);
	}

	public function getExtension()
	{
		return $this->getDelegate()->getExtension();
	}

	public function getParent()
	{
		return $this->getDelegate()->getParent();
	}

	public function getAccessTime()
	{
		return $this->getDelegate()->getAccessTime();
	}

	public function setAccessTime($time)
	{
		return $this->getDelegate()->setAccessTime($time);
	}

	public function getCreationTime()
	{
		return $this->getDelegate()->getCreationTime();
	}

	public function getModifyTime()
	{
		return $this->getDelegate()->getModifyTime();
	}

	public function setModifyTime($time)
	{
		return $this->getDelegate()->setModifyTime($time);
	}

	public function touch($time = null, $atime = null)
	{
		return $this->getDelegate()->touch($time, $atime);
	}

	public function getSize()
	{
		return $this->getDelegate()->getSize();
	}

	public function getOwner()
	{
		return $this->getDelegate()->getOwner();
	}

	public function setOwner($user)
	{
		return $this->getDelegate()->setOwner($user);
	}

	public function getGroup()
	{
		return $this->getDelegate()->getGroup();
	}

	public function setGroup($group)
	{
		return $this->getDelegate()->setGroup($group);
	}

	public function getMode()
	{
		return $this->getDelegate()->getMode();
	}

	public function setMode($mode)
	{
		return $this->getDelegate()->setMode($mode);
	}

	public function isReadable()
	{
		return $this->getDelegate()->isReadable();
	}

	public function isWritable()
	{
		return $this->getDelegate()->isWritable();
	}

	public function isExecutable()
	{
		return $this->getDelegate()->isExecutable();
	}

	public function exists()
	{
		return $this->getDelegate()->exists();
	}

	public function delete($recursive = false, $force = false)
	{
		return $this->getDelegate()->delete($recursive, $force);
	}

	public function copyTo(File $destination, $parents = false)
	{
		return $this->getDelegate()->copyTo($destination, $parents);
	}

	public function moveTo(File $destination)
	{
		return $this->getDelegate()->moveTo($destination);
	}

	public function createDirectory($parents = false)
	{
		return $this->getDelegate()->createDirectory($parents);
	}

	public function createFile($parents = false)
	{
		return $this->getDelegate()->createFile($parents);
	}

	public function getContents()
	{
		return $this->getDelegate()->getContents();
	}

	public function setContents($content)
	{
		return $this->getDelegate()->setContents($content);
	}

	public function appendContents($content)
	{
		return $this->getDelegate()->appendContents($content);
	}

	public function truncate($size = 0)
	{
		return $this->getDelegate()->truncate($size);
	}

	public function open($mode = 'rb')
	{
		return $this->getDelegate()->open($mode);
	}

	public function getMIMEName()
	{
		return $this->getDelegate()->getMIMEName();
	}

	public function getMIMEType()
	{
		return $this->getDelegate()->getMIMEType();
	}

	public function getMIMEEncoding()
	{
		return $this->getDelegate()->getMIMEEncoding();
	}

	public function getMD5($raw = false)
	{
		return $this->getDelegate()->getMD5($raw);
	}

	public function getSHA1($raw = false)
	{
		return $this->getDelegate()->getSHA1($raw);
	}

	public function ls()
	{
		$args = func_get_args();
		return call_user_func_array(array($this->getDelegate(), __METHOD__), $args);
	}

	public function getRealURL()
	{
		return $this->getDelegate()->getRealURL();
	}

	public function getPublicURL()
	{
		return $this->getDelegate()->getPublicURL();
	}

	public function count()
	{
		$args = func_get_args();
		return call_user_func_array(array($this->getDelegate(), __METHOD__), $args);
	}

	public function getIterator()
	{
		$args = func_get_args();
		return call_user_func_array(array($this->getDelegate(), __METHOD__), $args);
	}
}
