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

class SetModeEvent extends FileEvent
{

	/**
	 * @var int
	 */
	protected $mode;

	public function __construct(Filesystem $filesystem, File $file, $mode)
	{
		parent::__construct($filesystem, $file);
		$this->mode = (int) $mode;
	}

	/**
	 * @return int
	 */
	public function getMode()
	{
		return $this->mode;
	}
}
