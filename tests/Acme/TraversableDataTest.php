<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Tests;

use Acme\TraversableData;

/**
 * Test for TraversableData
 *
 * @author k.holy74@gmail.com
 */
class TraversableDataTest extends \PHPUnit_Framework_TestCase
{

	public function testTraversable()
	{
		$properties = [
			'string'   => 'Foo',
			'null'     => null,
			'boolean'  => true,
			'datetime' => new \DateTime(),
		];
		$test = new TraversableData($properties);
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
