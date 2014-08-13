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

namespace Filicious\Plugin\Hash;

use Filicious\File;
use Filicious\Plugin\AbstractPlugin;
use Filicious\Plugin\Mime\MimeFilePlugin;

class MimePlugin extends AbstractPlugin
{
	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'mime';
	}

	/**
	 * {@inheritdoc}
	 */
	public function providesFilePlugin(File $file)
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilePlugin(File $file)
	{
		return new MimeFilePlugin($file);
	}
}
