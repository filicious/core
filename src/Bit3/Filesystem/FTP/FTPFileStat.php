<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem\FTP;

/**
 * FTP file status information.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class FTPFileStat
{
	protected $perms;

	protected $mode;

	protected $type;

	protected $isDirectory;

	protected $isFile;

	protected $isLink;

	protected $user;

	protected $group;

	protected $size;

	protected $modified;

	protected $name;

	protected $target;

	public function setPerms($perms)
	{
		$this->perms = $perms;
		return $this;
	}

	public function getPerms()
	{
		return $this->perms;
	}

	public function setMode($mode)
	{
		$this->mode = $mode;
		return $this;
	}

	public function getMode()
	{
		return $this->mode;
	}

	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setIsDirectory($isDirectory)
	{
		$this->isDirectory = $isDirectory;
		return $this;
	}

	public function getIsDirectory()
	{
		return $this->isDirectory;
	}

	public function setIsFile($isFile)
	{
		$this->isFile = $isFile;
		return $this;
	}

	public function getIsFile()
	{
		return $this->isFile;
	}

	public function setIsLink($isLink)
	{
		$this->isLink = $isLink;
		return $this;
	}

	public function getIsLink()
	{
		return $this->isLink;
	}

	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function setGroup($group)
	{
		$this->group = $group;
		return $this;
	}

	public function getGroup()
	{
		return $this->group;
	}

	public function setSize($size)
	{
		$this->size = $size;
		return $this;
	}

	public function getSize()
	{
		return $this->size;
	}

	public function setModified($modified)
	{
		$this->modified = $modified;
		return $this;
	}

	public function getModified()
	{
		return $this->modified;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setTarget($target)
	{
		$this->target = $target;
		return $this;
	}

	public function getTarget()
	{
		return $this->target;
	}
}
