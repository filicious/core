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

namespace Filicious\Stream;

use Filicious\Stream;

/**
 * An observable file stream object.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
abstract class ObservableStream implements Stream
{
	protected $observers = array();

	/**
	 * Add an observer to the stream.
	 *
	 * @param StreamObserver $observer
	 */
	public function addObserver(StreamObserver $observer)
	{
		foreach ($this->observers as $registered) {
			if ($registered == $observer) {
				return;
			}
		}

		$this->observers[] = $observer;
	}

	/**
	 * Remove an observer from the stream.
	 *
	 * @param StreamObserver $observer
	 */
	public function removeObserver(StreamObserver $observer)
	{
		foreach ($this->observers as $index => $registered) {
			if ($registered == $observer) {
				unset($this->observers[$index]);
				return;
			}
		}
	}

	/**
	 * Notify observers that the stream is opened.
	 *
	 * @param StreamMode $mode
	 */
	protected function notifyOpened(StreamMode $mode)
	{
		/** @var StreamObserver $observer */
		foreach ($this->observers as $observer) {
			$observer->opened($mode);
		}
	}

	/**
	 * Notify observers that the stream is closed.
	 */
	protected function notifyClosed()
	{
		/** @var StreamObserver $observer */
		foreach ($this->observers as $observer) {
			$observer->closed();
		}
	}

	/**
	 * Notify observers that the stream is locked.
	 *
	 * @param mode $operation
	 */
	protected function notifyLocked($operation)
	{
		/** @var StreamObserver $observer */
		foreach ($this->observers as $observer) {
			$observer->locked($operation);
		}
	}

	/**
	 * Notify observers that the stream position changed.
	 *
	 * @param mode $operation
	 */
	protected function notifyPositionChanged($offset, $whence)
	{
		/** @var StreamObserver $observer */
		foreach ($this->observers as $observer) {
			$observer->positionChanged($offset, $whence);
		}
	}

	/**
	 * Notify observers that the stream is truncated.
	 *
	 * @param mode $operation
	 */
	protected function notifyTruncated($size)
	{
		/** @var StreamObserver $observer */
		foreach ($this->observers as $observer) {
			$observer->truncated($size);
		}
	}

	/**
	 * Notify observers that the stream is read.
	 *
	 * @param mode $operation
	 */
	protected function notifyRead($count, $data)
	{
		/** @var StreamObserver $observer */
		foreach ($this->observers as $observer) {
			$observer->read($count, $data);
		}
	}

	/**
	 * Notify observers that the stream is written.
	 *
	 * @param mode $operation
	 */
	protected function notifyWritten($data)
	{
		/** @var StreamObserver $observer */
		foreach ($this->observers as $observer) {
			$observer->written($data);
		}
	}

	/**
	 * Notify observers that the stream is flushed.
	 *
	 * @param mode $operation
	 */
	protected function notifyFlushed()
	{
		/** @var StreamObserver $observer */
		foreach ($this->observers as $observer) {
			$observer->flushed();
		}
	}
}
