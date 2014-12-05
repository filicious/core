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

namespace Filicious\Plugin\Mime;

use Filicious\Internals\Util;
use Filicious\Plugin\AbstractFilePlugin;

class MimeFilePlugin extends AbstractFilePlugin
{

	/**
	 * Get the mime name (e.g. "OpenDocument Text") of the file.
	 *
	 * @return string
	 */
	public function getMIMEName()
	{
		$adapter = $this->file->internalPathname()->localAdapter();

		if ($adapter instanceof MimeAwareAdapterInterface) {
			return $adapter->getMimeName($this->file->internalPathname());
		}

		return Util::executeFunction(
			function () {
				return finfo_buffer(
					Util::getFileInfo(),
					$this->file->getContents(),
					FILEINFO_NONE
				);
			},
			'Filicious\Exception\PluginException',
			0,
			'Could not determine mime name'
		);
	}

	/**
	 * Get the mime type (e.g. "application/vnd.oasis.opendocument.text") of the file.
	 *
	 * @return string
	 */
	public function getMIMEType()
	{
		$adapter = $this->file->internalPathname()->localAdapter();

		if ($adapter instanceof MimeAwareAdapterInterface) {
			return $adapter->getMimeType($this->file->internalPathname());
		}

		return Util::executeFunction(
			function () {
				return finfo_buffer(
					Util::getFileInfo(),
					$this->file->getContents(),
					FILEINFO_MIME_TYPE
				);
			},
			'Filicious\Exception\PluginException',
			0,
			'Could not determine mime type'
		);
	}

	/**
	 * Get the mime encoding (e.g. "binary" or "us-ascii" or "utf-8") of the file.
	 *
	 * @return string
	 */
	public function getMIMEEncoding()
	{
		$adapter = $this->file->internalPathname()->localAdapter();

		if ($adapter instanceof MimeAwareAdapterInterface) {
			return $adapter->getMimeEncoding($this->file->internalPathname());
		}

		return Util::executeFunction(
			function () {
				return finfo_buffer(
					Util::getFileInfo(),
					$this->file->getContents(),
					FILEINFO_MIME_ENCODING
				);
			},
			'Filicious\Exception\PluginException',
			0,
			'Could not determine mime encoding'
		);
	}

}
