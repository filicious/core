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

use Filicious\Internals\Util;
use Filicious\Plugin\AbstractFilePlugin;

class HashFilePlugin extends AbstractFilePlugin
{

	/**
	 * Calculate the hash.
	 *
	 * @param string $algorithm
	 *
	 * @return string
	 */
	public function getHash($algorithm, $binary = false)
	{
		$adapter = $this->file->internalPathname()->localAdapter();

		if ($adapter instanceof HashAwareAdapterInterface) {
			return $adapter->getHash($this->file->internalPathname(), $algorithm, $binary);
		}

		$file = $this->getFile();

		return Util::executeFunction(
			function() use ($algorithm, $file, $binary) {
				return hash(
					$algorithm,
					$file->getContents(),
					$binary
				);
			},
			'Filicious\Exception\PluginException',
			0,
			'Could not calculate %s hash of file %s',
			$algorithm,
			$file->getPathname()
		);
	}

}
