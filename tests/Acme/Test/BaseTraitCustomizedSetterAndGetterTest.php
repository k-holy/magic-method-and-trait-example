<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

/**
 * Test for BaseTrait with customized setter and getter
 *
 * @author k.holy74@gmail.com
 */
class BaseTraitCustomizedSetterAndGetterTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $now = new \DateTime();
        $test = new BaseTraitCustomizedSetterAndGetterTestData([
            'createdAt' => $now,
        ]);
        $this->assertEquals($now, $test->createdAt);
        $this->assertSame($now, $test->createdAt);
    }

    public function testSetCreatedAt()
    {
        $now = new \DateTime();
        $test = new BaseTraitCustomizedSetterAndGetterTestData([
            'createdAt' => $now,
        ]);
        $this->assertSame($now, $test->createdAt);
        $test->createdAt = new \DateTime();
        $this->assertNotSame($now, $test->createdAt);
    }

    public function testSetCreatedAtByTimestamp()
    {
        $now = new \DateTime();
        $test = new BaseTraitCustomizedSetterAndGetterTestData([
            'createdAt' => $now->getTimestamp(),
        ]);
        $this->assertInstanceOf('\DateTime', $test->createdAt);
        $this->assertEquals(
            $now->getTimestamp(),
            $test->createdAt->getTimestamp()
        );
    }

    public function testSetCreatedAtByString()
    {
        $now = new \DateTime();
        $test = new BaseTraitCustomizedSetterAndGetterTestData([
            'createdAt' => $now->format('Y-m-d H:i:s'),
        ]);
        $this->assertInstanceOf('\DateTime', $test->createdAt);
        $this->assertEquals(
            $now->format('Y-m-d H:i:s'),
            $test->createdAt->format('Y-m-d H:i:s')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetCreatedAtRaiseInvalidArgumentExceptionInvalidObject()
    {
        $test = new BaseTraitCustomizedSetterAndGetterTestData([
            'createdAt' => new \stdClass(),
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetCreatedAtRaiseInvalidArgumentExceptionInvalidType()
    {
        $test = new BaseTraitCustomizedSetterAndGetterTestData([
            'createdAt' => true,
        ]);
    }

    public function testGetCreatedAtAsString()
    {
        $now = new \DateTime();
        $test = new BaseTraitCustomizedSetterAndGetterTestData([
            'createdAt' => $now,
        ]);
        $this->assertEquals(
            $now->format('Y-m-d H:i:s'),
            $test->createdAtAsString
        );
    }

    public function testGetCreatedAtAsStringDateTimeFormat()
    {
        $now = new \DateTime();
        $test = new BaseTraitCustomizedSetterAndGetterTestData([
            'createdAt' => $now,
            'options' => [
                'dateTimeFormat' => 'Y/n/j H:i:s',
            ],
        ]);
        $this->assertEquals(
            $now->format('Y/n/j H:i:s'),
            $test->createdAtAsString
        );
    }

}
