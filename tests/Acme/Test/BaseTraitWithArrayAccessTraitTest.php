<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

/**
 * Test for BaseTrait with ArrayAccessTrait
 *
 * @author k.holy74@gmail.com
 */
class BaseTraitWithArrayAccessTraitTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $now = new \DateTime();
        $test = new BaseTraitWithArrayAccessTraitTestData([
            'datetime' => $now,
        ]);
        $this->assertEquals($now, $test['datetime']);
        $this->assertSame($now, $test['datetime']);
    }

    public function testIsset()
    {
        $test = new BaseTraitWithArrayAccessTraitTestData([
            'string' => 'Foo',
            'null'   => null,
        ]);
        $this->assertTrue(isset($test['string']));
        $this->assertFalse(isset($test['null']));
        $this->assertFalse(isset($test['undefined_property']));
    }

    public function testGet()
    {
        $test = new BaseTraitWithArrayAccessTraitTestData([
            'string' => 'Foo',
            'null'   => null,
        ]);
        $this->assertEquals('Foo', $test['string']);
        $this->assertNull($test['null']);
    }

    public function testSet()
    {
        $test = new BaseTraitWithArrayAccessTraitTestData([
            'string'  => 'Foo',
            'boolean' => true,
        ]);
        $test['string'] = 'Bar';
        $test['boolean'] = false;
        $this->assertEquals('Bar', $test['string']);
        $this->assertFalse($test['boolean']);
    }

    public function testSetObject()
    {
        $now = new \DateTime();
        $test = new BaseTraitWithArrayAccessTraitTestData([
            'datetime' => $now,
        ]);
        $this->assertSame($now, $test['datetime']);
        $test['datetime'] = new \DateTime();
        $this->assertNotSame($now, $test['datetime']);
    }

    public function testUnset()
    {
        $test = new BaseTraitWithArrayAccessTraitTestData([
            'string' => 'Foo',
        ]);
        $this->assertNotNull($test['string']);
        unset($test['string']);
        $this->assertNull($test['string']);
    }

}
