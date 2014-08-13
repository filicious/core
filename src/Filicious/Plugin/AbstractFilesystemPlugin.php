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

namespace Filicious\Plugin;

use Filicious\Filesystem;

abstract class AbstractFilesystemPlugin implements FilesystemPluginInterface
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
	 * {@inheritdoc}
	 */
	public function getFilesystem()
	{
		return $this->filesystem;
	}
}
