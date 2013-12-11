<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

/**
 * Test for BaseTrait with ImmutableTrait customized setter
 *
 * @author k.holy74@gmail.com
 */
class BaseTraitWithImmutableTraitCustomizedSetterTest extends \PHPUnit_Framework_TestCase
{

	public function testConstructorDefensiveCopy()
	{
		$now = new \DateTime();
		$test = new BaseTraitWithImmutableTraitCustomizedSetterTestData([
			'createdAt' => $now,
		]);
		$this->assertEquals($now, $test->createdAt);
		$this->assertNotSame($now, $test->createdAt);
	}

	public function testSetCreatedAtByTimestamp()
	{
		$now = new \DateTime();
		$test = new BaseTraitWithImmutableTraitCustomizedSetterTestData([
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
		$test = new BaseTraitWithImmutableTraitCustomizedSetterTestData([
			'createdAt' => $now->format('Y-m-d H:i:s'),
		]);
		$this->assertInstanceOf('\DateTime', $test->createdAt);
		$this->assertEquals(
			$now->format('Y-m-d H:i:s'),
			$test->createdAt->format('Y-m-d H:i:s')
		);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testSetRaiseLogicException()
	{
		$test = new BaseTraitWithImmutableTraitCustomizedSetterTestData();
		$test->createdAt = new \DateTime();
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testUnsetRaiseLogicException()
	{
		$test = new BaseTraitWithImmutableTraitCustomizedSetterTestData([
			'createdAt' => new \DateTime(),
		]);
		unset($test->createdAt);
	}

}
