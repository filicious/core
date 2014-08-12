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

use Filicious\Filesystem;
use Symfony\Component\EventDispatcher\Event;

class FilesystemEvent extends Event
{

	/**
	 * @var Filesystem
	 */
	protected $filesystem;

	public function __construct(Filesystem $filesystem)
	{
		$this->filesystem = $filesystem;
	}

	/**
	 * Return the filesystem.
	 *
	 * @return Filesystem
	 */
	public function getFilesystem()
	{
		return $this->filesystem;
	}

}
