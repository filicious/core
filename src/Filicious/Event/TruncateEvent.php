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

class TruncateEvent extends FileEvent
{

	/**
	 * @var int
	 */
	protected $size;

	public function __construct(Filesystem $filesystem, File $file, $size)
	{
		parent::__construct($filesystem, $file);
		$this->size = (int) $size;
	}

	/**
	 * @return int
	 */
	public function getSize()
	{
		return $this->size;
	}
}
