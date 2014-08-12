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

namespace Filicious\Event;

use Filicious\File;
use Filicious\Filesystem;

class TouchEvent extends FileEvent
{

	/**
	 * @var \DateTime
	 */
	protected $modifyTime;

	/**
	 * @var \DateTime
	 */
	protected $accessTime;

	/**
	 * @var bool
	 */
	protected $created;

	public function __construct(
		Filesystem $filesystem,
		File $file,
		\DateTime $modifyTime,
		\DateTime $accessTime,
		$created
	) {
		parent::__construct($filesystem, $file);
		$this->modifyTime = $modifyTime;
		$this->accessTime = $accessTime;
		$this->created    = (bool) $created;
	}

	/**
	 * @return \DateTime
	 */
	public function getModifyTime()
	{
		return $this->modifyTime;
	}

	/**
	 * @return \DateTime
	 */
	public function getAccessTime()
	{
		return $this->accessTime;
	}

	/**
	 * @return boolean
	 */
	public function isCreated()
	{
		return $this->created;
	}
}
