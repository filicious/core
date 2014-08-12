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

use Filicious\Internals\Util;
use Filicious\Plugin\AbstractFilePlugin;

class DiskSpaceFilePlugin extends AbstractFilePlugin
{

	/**
	 * Calculate the space.
	 *
	 * @param string $algorithm
	 *
	 * @return string
	 */
	public function getSpace($algorithm, $binary = false)
	{
		$adapter = $this->file->internalPathname()->localAdapter();

		if ($adapter instanceof SpaceAwareAdapterInterface) {
			return $adapter->getSpace($this->file->internalPathname(), $algorithm, $binary);
		}

		$file = $this->getFile();

		return Util::executeFunction(
			function () use ($algorithm, $file, $binary) {
				return space(
					$algorithm,
					$file->getContents(),
					$binary
				);
			},
			'Filicious\Exception\PluginException',
			0,
			'Could not calculate %s space of file %s',
			$algorithm,
			$file->getPathname()
		);
	}

	/**
	 * TODO PROPOSED TO BE REMOVED
	 *
	 * Checks if the file is a (symbolic) space.
	 *
	 * @return bool True if the file exists and is a space; otherwise false
	 */
	public function isSpace()
	{
		$adapter = $this->file->internalPathname()->localAdapter();

		if ($adapter instanceof SpaceAwareAdapterInterface) {
			return $adapter->isSpace($this->file->internalPathname());
		}

		return false;
	}

	/**
	 * TODO PROPOSED TO BE REMOVED
	 *
	 * @return mixed
	 */
	public function getSpaceTarget()
	{
		$adapter = $this->file->internalPathname()->localAdapter();

		if ($adapter instanceof SpaceAwareAdapterInterface) {
			return $adapter->getSpaceTarget($this->file->internalPathname());
		}

		return null;
	}

}
