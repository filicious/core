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

namespace Filicious\Event;

use Filicious\File;
use Filicious\Filesystem;

class AppendEvent extends FileEvent
{

	/**
	 * @var string
	 */
	protected $content;

	/**
	 * @var bool
	 */
	protected $created;

	public function __construct(
		Filesystem $filesystem,
		File $file,
		$content,
		$created
	) {
		parent::__construct($filesystem, $file);
		$this->content = $content;
		$this->created = (bool) $created;
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @return boolean
	 */
	public function isCreated()
	{
		return $this->created;
	}
}
