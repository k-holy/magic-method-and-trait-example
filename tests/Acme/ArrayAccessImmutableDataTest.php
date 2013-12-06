<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Tests;

use Acme\ArrayAccessImmutableData;

/**
 * Test for ArrayAccessImmutableData
 *
 * @author k.holy74@gmail.com
 */
class ArrayAccessImmutableDataTest extends \PHPUnit_Framework_TestCase
{

	public function testConstructorDefensiveCopy()
	{
		$now = new \DateTime();
		$test = new ArrayAccessImmutableData([
			'datetime' => $now,
		]);
		$this->assertEquals($now, $test['datetime']);
		$this->assertNotSame($now, $test['datetime']);
	}

	public function testIsset()
	{
		$test = new ArrayAccessImmutableData([
			'string' => 'Foo',
			'null'   => null,
		]);
		$this->assertTrue(isset($test['string']));
		$this->assertFalse(isset($test['null']));
		$this->assertFalse(isset($test['not_defined_property']));
	}

	public function testGet()
	{
		$test = new ArrayAccessImmutableData([
			'string' => 'Foo',
			'null'   => null,
		]);
		$this->assertEquals('Foo', $test['string']);
		$this->assertNull($test['null']);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testSetRaiseLogicException()
	{
		$test = new ArrayAccessImmutableData([
			'string'  => 'Foo',
			'boolean' => true,
		]);
		$test['string'] = 'Bar';
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testUnsetRaiseLogicException()
	{
		$test = new ArrayAccessImmutableData([
			'string' => 'Foo',
		]);
		unset($test['string']);
	}

}
