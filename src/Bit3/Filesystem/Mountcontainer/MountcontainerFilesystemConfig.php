<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @link    http://www.cyberspectrum.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem\Mountcontainer;

use Bit3\Filesystem\AbstractFilesystemConfig;

/**
 * A filesystem object
 *
 * @package php-filesystem
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MountcontainerFilesystemConfig
	extends AbstractFilesystemConfig
{
	public function __construct()
	{
		parent::__construct();
	}
}