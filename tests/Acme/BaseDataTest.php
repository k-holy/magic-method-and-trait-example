<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Tests;

use Acme\BaseData;

/**
 * Test for BaseData
 *
 * @author k.holy74@gmail.com
 */
class BaseDataTest extends \PHPUnit_Framework_TestCase
{

	public function testConstructor()
	{
		$now = new \DateTime();
		$test = new BaseData([
			'datetime' => $now,
		]);
		$this->assertEquals($now, $test->datetime);
		$this->assertSame($now, $test->datetime);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructorRaiseInvalidArgumentExceptionUndefinedProperty()
	{
		$test = new BaseData([
			'undefined_property' => 'Foo',
		]);
	}

	public function testIsset()
	{
		$test = new BaseData([
			'string' => 'Foo',
			'null'   => null,
		]);
		$this->assertTrue(isset($test->string));
		$this->assertFalse(isset($test->null));
		$this->assertFalse(isset($test->undefined_property));
	}

	public function testGet()
	{
		$test = new BaseData([
			'string' => 'Foo',
			'null'   => null,
		]);
		$this->assertEquals('Foo', $test->string);
		$this->assertNull($test->null);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetRaiseInvalidArgumentExceptionUndefinedProperty()
	{
		$test = new BaseData();
		$test->undefined_property;
	}

	public function testSet()
	{
		$test = new BaseData([
			'string'  => 'Foo',
			'boolean' => true,
		]);
		$test->string = 'Bar';
		$test->boolean = false;
		$this->assertEquals('Bar', $test->string);
		$this->assertFalse($test->boolean);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetRaiseInvalidArgumentExceptionUndefinedProperty()
	{
		$test = new BaseData();
		$test->undefined_property = 'Foo';
	}

	public function testSetObject()
	{
		$now = new \DateTime();
		$test = new BaseData([
			'datetime' => $now,
		]);
		$this->assertSame($now, $test->datetime);
		$test->datetime = new \DateTime();
		$this->assertNotSame($now, $test->datetime);
	}

	public function testUnset()
	{
		$test = new BaseData([
			'string' => 'Foo',
		]);
		$this->assertNotNull($test->string);
		unset($test->string);
		$this->assertNull($test->string);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testUnsetRaiseInvalidArgumentExceptionUndefinedProperty()
	{
		$test = new BaseData();
		unset($test->undefined_property);
	}

	public function testSerializable()
	{
		$test = new BaseData([
			'string'   => 'Foo',
			'null'     => null,
			'boolean'  => true,
			'datetime' => new \DateTime(),
		]);
		$serialized = serialize($test);
		$this->assertEquals($test, unserialize($serialized));
		$this->assertNotSame($test, unserialize($serialized));
	}

	public function testVarExport()
	{
		$test = new BaseData([
			'string'   => 'Foo',
			'null'     => null,
			'boolean'  => true,
			'datetime' => new \DateTime(),
		]);
		eval('$exported = ' . var_export($test, true) . ';');
		$this->assertEquals($test, $exported);
		$this->assertNotSame($test, $exported);
	}

	public function testClone()
	{
		$test = new BaseData([
			'string'   => 'Foo',
			'null'     => null,
			'boolean'  => true,
			'datetime' => new \DateTime(),
		]);
		$cloned = clone $test;
		$this->assertEquals($test->datetime, $cloned->datetime);
		$this->assertNotSame($test->datetime, $cloned->datetime);
	}

	public function testTraversable()
	{
		$properties = [
			'string'   => 'Foo',
			'null'     => null,
			'boolean'  => true,
			'datetime' => new \DateTime(),
		];
		$test = new BaseData($properties);
		foreach ($test as $name => $value) {
			if (array_key_exists($name, $properties)) {
				if (is_object($value)) {
					$this->assertSame($properties[$name], $value);
				} else {
					$this->assertEquals($properties[$name], $value);
				}
			}
		}
	}

}
