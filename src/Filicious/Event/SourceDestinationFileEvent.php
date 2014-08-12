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

class SourceDestinationFileEvent extends FilesystemEvent
{

	/**
	 * @var File
	 */
	protected $source;

	/**
	 * @var File
	 */
	protected $destination;

	public function __construct(Filesystem $filesystem, File $source, File $target)
	{
		parent::__construct($filesystem);
		$this->source      = $source;
		$this->destination = $target;
	}

	/**
	 * Return the source file.
	 *
	 * @return File
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * Return the target file.
	 *
	 * @return File
	 */
	public function getDestination()
	{
		return $this->destination;
	}

}
