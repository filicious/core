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

namespace Filicious\Plugin\Link;

use Filicious\Plugin\AbstractFilePlugin;

class LinkFilePlugin extends AbstractFilePlugin
{

	/**
	 * Checks if the file is a (symbolic) link.
	 *
	 * @return bool True if the file exists and is a link; otherwise false
	 */
	public function isLink()
	{
		$adapter = $this->file->internalPathname()->localAdapter();

		if ($adapter instanceof LinkAwareAdapterInterface) {
			return $adapter->isLink($this->file->internalPathname());
		}

		return false;
	}

	/**
	 * Receive the link target from symbolic links.
	 *
	 * @return mixed
	 */
	public function getLinkTarget()
	{
		$adapter = $this->file->internalPathname()->localAdapter();

		if ($adapter instanceof LinkAwareAdapterInterface) {
			return $adapter->getLinkTarget($this->file->internalPathname());
		}

		return null;
	}

}
