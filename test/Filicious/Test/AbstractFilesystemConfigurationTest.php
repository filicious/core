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

namespace Filicious\Test;

use Filicious\FilesystemConfig;
use Filicious\Internals\Adapter;
use PHPUnit_Framework_TestCase;

/**
 * @outputBuffering disabled
 */
abstract class AbstractFilesystemConfigurationTest extends PHPUnit_Framework_TestCase
{
	abstract protected function getAdapterClassName();

	/**
	 * Return a set of configuration parameters, after empty initialisation.
	 *
	 * @return array
	 */
	abstract protected function getBlankConfigurationValues();

	/**
	 * Return a set of object properties, after empty initialisation.
	 *
	 * @return array
	 */
	abstract protected function getBlankObjectPropertyValues();

	/**
	 * Set of constructor parameters.
	 *
	 * @var array
	 */
	abstract protected function getConstructorArgumentValues();

	/**
	 * Set of configuration parameters, after initialisation with $constructorArguments.
	 *
	 * @var array
	 */
	abstract protected function getConstructorConfigurationValues();

	/**
	 * Set of object properties, after empty initialisation.
	 *
	 * @var array
	 */
	abstract protected function getConstructorObjectPropertyValues();

	/**
	 * Set of configuration parameters, for preconfigurated initialisation.
	 *
	 * @var array
	 */
	abstract protected function getInitialSetupValues();

	/**
	 * Set of configuration parameters, after preconfigurated initialisation.
	 *
	 * @var array
	 */
	abstract protected function getInitialConfigurationValues();

	/**
	 * Set of object properties, after preconfigurated initialisation.
	 *
	 * @var array
	 */
	abstract protected function getInitialObjectPropertyValues();

	/**
	 * Set of configuration parameters, for live reconfiguration.
	 *
	 * @var array
	 */
	abstract protected function getUpdateSetupValues();

	/**
	 * Set of configuration parameters, after live reconfiguration.
	 *
	 * @var array
	 */
	abstract protected function getUpdateConfigurationValues();

	/**
	 * Set of object properties, after live reconfiguration.
	 *
	 * @var array
	 */
	abstract protected function getUpdatePropertyValues();

	protected function validateConfiguration(Adapter $adapter, array $expectedValues)
	{
		$config = $adapter->getConfig();

		$this->assertAttributeEquals(
			$expectedValues,
			'data',
			$config
		);
	}

	protected function validateProperties(Adapter $adapter, array $expectedValues)
	{
		foreach ($expectedValues as $key => $value) {
			$this->assertObjectHasAttribute($key, $adapter);
			$this->assertAttributeEquals($value, $key, $adapter);
		}
	}

	/**
	 * @covers Filicious\Local\LocalAdapter::__construct()
	 */
	public function testBlankConfiguration()
	{
		/** @var \Filicious\Internals\Adapter $adapter */
		$class = new \ReflectionClass($this->getAdapterClassName());
		$adapter = $class->newInstance();

		$this->validateConfiguration($adapter, $this->getBlankConfigurationValues());
		$this->validateProperties($adapter, $this->getBlankObjectPropertyValues());
		$this->postTestUpdate($adapter);
	}

	/**
	 * @covers Filicious\Local\LocalAdapter::__construct(mixed $args, mixed $_ = null)
	 */
	public function testConstructorConfiguration()
	{
		/** @var \Filicious\Internals\Adapter $adapter */
		$class = new \ReflectionClass($this->getAdapterClassName());
		$adapter = $class->newInstanceArgs($this->getConstructorArgumentValues());

		$this->validateConfiguration($adapter, $this->getConstructorConfigurationValues());
		$this->validateProperties($adapter, $this->getConstructorObjectPropertyValues());
		$this->postTestUpdate($adapter);
	}

	/**
	 * @covers Filicious\Local\LocalAdapter::__construct(FilesystemConfig $config)
	 */
	public function testConfigurationInitialisation()
	{
		$config = new FilesystemConfig($this->getInitialSetupValues());

		/** @var \Filicious\Internals\Adapter $adapter */
		$class = new \ReflectionClass($this->getAdapterClassName());
		$adapter = $class->newInstance($config);

		$this->validateConfiguration($adapter, $this->getInitialConfigurationValues());
		$this->validateProperties($adapter, $this->getInitialObjectPropertyValues());
		$this->postTestUpdate($adapter);
	}

	public function postTestUpdate(Adapter $adapter)
	{
		$config  = $adapter->getConfig();

		try {
			$config->open();
			foreach ($this->getUpdateSetupValues() as $path => $values) {
				foreach ($values as $key => $value) {
					$config->set($key, $value, $path);
				}
			}
			$config->commit();

			$this->validateConfiguration($adapter, $this->getUpdateConfigurationValues());
			$this->validateProperties($adapter, $this->getUpdatePropertyValues());
		} catch (\Filicious\Exception\ConfigurationException $e) {
			$this->fail($e->getMessage() . "\n" . $e->getTraceAsString());
			throw $e;
		}
	}
}
