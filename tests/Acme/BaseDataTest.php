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

}
