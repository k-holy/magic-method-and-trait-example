<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

use Acme\CallbackIterator;

/**
 * Test for CallbackIterator
 *
 * @author k.holy74@gmail.com
 */
class CallbackIteratorTest extends \PHPUnit_Framework_TestCase
{

	public function testCurrent()
	{
		$values = array();
		$values[] = array('num' => 0);
		$values[] = array('num' => 1);
		$values[] = array('num' => 2);
		$iterator = new CallbackIterator(new \ArrayIterator($values), function($value) {
			$object = new \StdClass();
			$object->num = $value['num'];
			$object->pow = pow($value['num'], 2);
			return $object;
		});
		foreach ($iterator as $current) {
			$this->assertEquals(pow($current->num, 2), $current->pow);
		}
	}

}
