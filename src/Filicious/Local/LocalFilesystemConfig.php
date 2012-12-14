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

namespace Filicious\Local;

use Filicious\AbstractFilesystemConfig;

/**
 * A filesystem object
 *
 * @package filicious-core
 * @author  Oliver Hoff <oliver@hofff.com>
 */
class LocalFilesystemConfig
	extends AbstractFilesystemConfig
{

	public function __construct($path)
	{
		parent::__construct();
		$this->setBasePath($path);
	}
}