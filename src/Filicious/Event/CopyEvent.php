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

class CopyEvent extends SourceDestinationFileEvent
{

	/**
	 * @var bool
	 */
	protected $recursive;

	/**
	 * @var int
	 */
	protected $overwrite;

	/**
	 * @var bool
	 */
	protected $parents;

	public function __construct(Filesystem $filesystem, File $source, File $destination, $recursive, $overwrite, $parents)
	{
		parent::__construct($filesystem, $source, $destination);
		$this->recursive = (bool) $recursive;
		$this->overwrite = (int) $overwrite;
		$this->parents = (bool) $parents;
	}

	/**
	 * @return boolean
	 */
	public function isRecursive()
	{
		return $this->recursive;
	}

	/**
	 * @return int
	 */
	public function getOverwrite()
	{
		return $this->overwrite;
	}

	/**
	 * @return boolean
	 */
	public function isParents()
	{
		return $this->parents;
	}

}
