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

class FiliciousEvents
{
	/**
	 * The TOUCH event occurs after File::touch() was called.
	 *
	 * This event allows you to act after touching a file.
	 * The event listener method receives a Filicious\Event\TouchEvent instance.
	 *
	 * @var string
	 *
	 * @api
	 */
	const TOUCH = 'filicious.touch';

	/**
	 * The SET_OWNER event occurs after File::setOwner() was called.
	 *
	 * This event allows you to act after changing the owner of a file.
	 * The event listener method receives a Filicious\Event\SetOwnerEvent instance.
	 *
	 * @var string
	 *
	 * @api
	 */
	const SET_OWNER = 'filicious.set-owner';

	/**
	 * The SET_GROUP event occurs after File::setGroup() was called.
	 *
	 * This event allows you to act after changing the group of a file.
	 * The event listener method receives a Filicious\Event\SetOwnerEvent instance.
	 *
	 * @var string
	 *
	 * @api
	 */
	const SET_GROUP = 'filicious.set-group';

	/**
	 * The SET_MODE event occurs after File::setMode() was called.
	 *
	 * This event allows you to act after changing the group of a file.
	 * The event listener method receives a Filicious\Event\SetModeEvent instance.
	 *
	 * @var string
	 *
	 * @api
	 */
	const SET_MODE = 'filicious.set-mode';

	/**
	 * The DELETE event occurs before File::delete() was called.
	 *
	 * This event allows you to act before deleting a file.
	 * The event listener method receives a Filicious\Event\DeleteEvent instance.
	 *
	 * @var string
	 *
	 * @api
	 */
	const BEFORE_DELETE = 'filicious.before-delete';

	/**
	 * The DELETE event occurs after File::delete() was called.
	 *
	 * This event allows you to act after deleting a file.
	 * The event listener method receives a Filicious\Event\DeleteEvent instance.
	 *
	 * @var string
	 *
	 * @api
	 */
	const DELETE = 'filicious.delete';

	/**
	 * The COPY event occurs after File::copy() was called.
	 *
	 * This event allows you to act after copying a file.
	 * The event listener method receives a Filicious\Event\CopyEvent instance.
	 *
	 * @var string
	 *
	 * @api
	 */
	const COPY = 'filicious.copy';

	/**
	 * The MOVE event occurs after File::move() was called.
	 *
	 * This event allows you to act after moving a file.
	 * The event listener method receives a Filicious\Event\MoveEvent instance.
	 *
	 * @var string
	 *
	 * @api
	 */
	const MOVE = 'filicious.move';

	/**
	 * The CREATE_DIRECTORY event occurs after File::createDirectory() was called.
	 *
	 * This event allows you to act after a directory was created.
	 * The event listener method receives a Filicious\Event\CreateDirectoryEvent instance.
	 *
	 * @var string
	 *
	 * @api
	 */
	const CREATE_DIRECTORY = 'filicious.create-directory';

	/**
	 * The CREATE_FILE event occurs after File::createFile() was called.
	 *
	 * This event allows you to act after an empty file was created.
	 * The event listener method receives a Filicious\Event\CreateFileEvent instance.
	 *
	 * @var string
	 *
	 * @api
	 */
	const CREATE_FILE = 'filicious.create-directory';

	/**
	 * The WRITE event occurs after File::setContents() was called.
	 *
	 * This event allows you to act after a file was overwritten.
	 * The event listener method receives a Filicious\Event\WriteEvent instance.
	 *
	 * @var string
	 *
	 * @api
	 */
	const WRITE = 'filicious.write';

	/**
	 * The APPEND event occurs after File::appendContents() was called.
	 *
	 * This event allows you to act after a file was appended.
	 * The event listener method receives a Filicious\Event\AppendEvent instance.
	 *
	 * @var string
	 *
	 * @api
	 */
	const APPEND = 'filicious.append';

	/**
	 * The TRUNCATE event occurs after File::truncate() was called.
	 *
	 * This event allows you to act after a file was truncated.
	 * The event listener method receives a Filicious\Event\TruncateEvent instance.
	 *
	 * @var string
	 *
	 * @api
	 */
	const TRUNCATE = 'filicious.truncate';

}
