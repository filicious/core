<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem\Mountcontainer;

use Bit3\Filesystem\SimpleFile;

/**
 * File from a mounted filesystem structure.
 *
 * @package php-filesystem
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
