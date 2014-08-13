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

class CreateDirectoryEvent extends FileEvent
{

	/**
	 * @var bool
	 */
	protected $parents;

	public function __construct(Filesystem $filesystem, File $file, $parents)
	{
		parent::__construct($filesystem, $file);
		$this->parents = (bool) $parents;
	}

	/**
	 * @return bool
	 */
	public function getParents()
	{
		return $this->parents;
	}
}
