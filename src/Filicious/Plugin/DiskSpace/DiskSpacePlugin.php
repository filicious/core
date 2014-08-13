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

namespace Filicious\Plugin\DiskSpace;

use Filicious\File;
use Filicious\Plugin\AbstractPlugin;

class DiskSpacePlugin extends AbstractPlugin
{
	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'disk-space';
	}

	/**
	 * {@inheritdoc}
	 */
	public function providesFilePlugin(File $file)
	{
		return $file->internalPathname()->localAdapter() instanceof DiskSpaceAwareAdapterInterface;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilePlugin(File $file)
	{
		if ($file->internalPathname()->localAdapter() instanceof DiskSpaceAwareAdapterInterface) {
			return new DiskSpaceFilePlugin($file);
		}

		return null;
	}
}
