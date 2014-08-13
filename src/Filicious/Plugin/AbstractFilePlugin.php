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

use Filicious\File;

abstract class AbstractFilePlugin implements FilePluginInterface
{

	/**
	 * @var File
	 */
	protected $file;

	public function __construct(File $file)
	{
		$this->file = $file;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFile()
	{
		return $this->file;
	}
}
