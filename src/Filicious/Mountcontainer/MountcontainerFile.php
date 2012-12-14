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

namespace Filicious\Mountcontainer;

use Filicious\SimpleFile;

/**
 * File from a mounted filesystem structure.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MountcontainerFile
	extends SimpleFile
{
	/**
	 * @var File
	 */
	protected $subFile;

	/**
	 * @param string
	 *
	 * @param File|null
	 *
	 * @param MountcontainerFilesystem
	 */
	public function __construct($mappedPath, $file, MountcontainerFilesystem $fs)
	{
		parent::__construct($mappedPath, $fs);
		$this->subFile = $file;
	}

	public function getSubFile()
	{
		return $this->subFile;
	}
}
