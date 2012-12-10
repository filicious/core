<?php

/**
 * High level object oriented filesystem abstraction.
 * 
 * @package	php-filesystem
 * @author	Tristan Lins <tristan.lins@bit3.de>
 * @author	Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author	Oliver Hoff <oliver@hofff.com>
 * @link	http://bit3.de
 * @license	http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem;

/**
 * Abstract base class for File implementation that are delegating a part of
 * their method calls to an underlying File object. 
 * 
 * @package	php-filesystem
 * @author	Oliver Hoff <oliver@hofff.com>
 */
abstract class AbstractFileDelegator
	implements File
{
	/**
	 * @var File The file object method calls are delegated to.
	 */
	private $delegate;

	public function __construct(File $delegate) {
		$this->setDelegate($delegate);
	}
	
	protected function setDelegate(File $delegate) {
		$this->delegate = $delegate;
	}
	
	public function getDelegate() {
		return $this->delegate;
	}
	
	public function __call($method, $args) {
		return call_user_func_array(array($this->delegate, $method), $args);
	}

	public function getFilesystem()				{ return $this->delegate->getFilesystem(); }
	public function isFile()					{ return $this->delegate->isFile(); }
	public function isLink()					{ return $this->delegate->isLink(); }
	public function isDirectory()				{ return $this->delegate->isDirectory(); }
	public function getType()					{ return $this->delegate->getType(); }
	public function getPathname()				{ return $this->delegate->getPathname(); }
	public function getLinkTarget()				{ return $this->delegate->getLinkTarget(); }
	public function getBasename($suffix = null)	{ return $this->delegate->getBasename($suffix); }
	public function getExtension()				{ return $this->delegate->getExtension(); }
	public function getParent()					{ return $this->delegate->getParent(); }
	public function getAccessTime()				{ return $this->delegate->getAccessTime(); }
	public function setAccessTime($time)		{ return $this->delegate->setAccessTime($time); }
	public function getCreationTime()			{ return $this->delegate->getCreationTime(); }
	public function getModifyTime()				{ return $this->delegate->getModifyTime(); }
	public function setModifyTime($time)		{ return $this->delegate->setModifyTime($time); }
	public function touch($time = null, $atime = null) { return $this->delegate->touch($time, $atime); }
	public function getSize()					{ return $this->delegate->getSize(); }
	public function getOwner()					{ return $this->delegate->getOwner(); }
	public function setOwner($user)				{ return $this->delegate->setOwner($user); }
	public function getGroup()					{ return $this->delegate->getGroup(); }
	public function setGroup($group)			{ return $this->delegate->setGroup($group); }
	public function getMode()					{ return $this->delegate->getMode(); }
	public function setMode($mode)				{ return $this->delegate->setMode($mode); }
	public function isReadable()				{ return $this->delegate->isReadable(); }
	public function isWritable()				{ return $this->delegate->isWritable(); }
	public function isExecutable()				{ return $this->delegate->isExecutable(); }
	public function exists()					{ return $this->delegate->exists(); }
	public function delete($recursive = false, $force = false) { return $this->delegate->delete($recursive, $force); }
	public function copyTo(File $destination, $parents = false) { return $this->delegate->copyTo($destination, $parents); }
	public function moveTo(File $destination)	{ return $this->delegate->moveTo($destination); }
	public function createDirectory($parents = false) { return $this->delegate->createDirectory($parents); }
	public function createFile($parents = false){ return $this->delegate->createFile($parents); }
	public function getContents()				{ return $this->delegate->getContents(); }
	public function setContents($content)		{ return $this->delegate->setContents($content); }
	public function appendContents($content)	{ return $this->delegate->appendContents($content); }
	public function truncate($size = 0)			{ return $this->delegate->truncate($size); }
	public function open($mode = 'rb')			{ return $this->delegate->open($mode); }
	public function getMIMEName()				{ return $this->delegate->getMIMEName(); }
	public function getMIMEType()				{ return $this->delegate->getMIMEType(); }
	public function getMIMEEncoding()			{ return $this->delegate->getMIMEEncoding(); }
	public function getMD5($raw = false)		{ return $this->delegate->getMD5($raw); }
	public function getSHA1($raw = false)		{ return $this->delegate->getSHA1($raw); }
	public function ls()						{ $args = func_get_args(); return call_user_func_array(array($this->delegate, __METHOD__), $args); }
	public function getRealURL()				{ return $this->delegate->getRealURL(); }
	public function getPublicURL()				{ return $this->delegate->getPublicURL(); }
	public function count()						{ $args = func_get_args(); return call_user_func_array(array($this->delegate, __METHOD__), $args); }
	public function getIterator()				{ $args = func_get_args(); return call_user_func_array(array($this->delegate, __METHOD__), $args); }
}
