<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Tests;

use Acme\ArrayAccessTrait;

/**
 * Test for ArrayAccessTrait
 *
 * @author k.holy74@gmail.com
 */
class ArrayAccessTraitTest extends \PHPUnit_Framework_TestCase
{

	public function testConstructor()
	{
		$now = new \DateTime();
		$test = new ArrayAccessTest();
		$test['datetime'] = $now;
		$this->assertEquals($now, $test['datetime']);
		$this->assertSame($now, $test['datetime']);
	}

	public function testIsset()
	{
		$test = new ArrayAccessTest();
		$test['string'] = 'Foo';
		$test['null'] = null;
		$this->assertTrue(isset($test['string']));
		$this->assertFalse(isset($test['null']));
		$this->assertFalse(isset($test['not_defined_property']));
	}

	public function testGet()
	{
		$test = new ArrayAccessTest();
		$test['string'] = 'Foo';
		$test['null'] = null;
		$this->assertEquals('Foo', $test['string']);
		$this->assertNull($test['null']);
	}

	public function testSet()
	{
		$test = new ArrayAccessTest();
		$test['string'] = 'Bar';
		$test['boolean'] = false;
		$this->assertEquals('Bar', $test['string']);
		$this->assertFalse($test['boolean']);
	}

	public function testSetObject()
	{
		$now = new \DateTime();
		$test = new ArrayAccessTest();
		$test['datetime'] = $now;
		$this->assertSame($now, $test['datetime']);
		$test['datetime'] = new \DateTime();
		$this->assertNotSame($now, $test['datetime']);
	}

	public function testUnset()
	{
		$test = new ArrayAccessTest();
		$test['string'] = 'Foo';
		$this->assertNotNull($test['string']);
		unset($test['string']);
		$this->assertNull($test['string']);
	}

}

class ArrayAccessTest implements \ArrayAccess
{
	use ArrayAccessTrait;

	private $string;
	private $null;
	private $boolean;
	private $datetime;

}
