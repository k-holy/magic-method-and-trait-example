<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

/**
 * Test for BaseTrait with JsonSerializableTrait
 *
 * @author k.holy74@gmail.com
 */
class BaseTraitWithJsonSerializableTraitTest extends \PHPUnit_Framework_TestCase
{

	public function testJsonSerialize()
	{
		$data = new BaseTraitWithJsonSerializableTraitTestData([
			'string'  => 'Foo',
			'null'    => null,
			'boolean' => true,
		]);
		$object = $data->jsonSerialize();
		$this->assertEquals('Foo', $object->string);
		$this->assertNull($object->null);
		$this->assertTrue($object->boolean);
	}

	public function testJsonSerializeDateTimeToStringRFC3339()
	{
		$now = new \DateTime();
		$data = new BaseTraitWithJsonSerializableTraitTestData([
			'datetime' => $now,
		]);
		$object = $data->jsonSerialize();
		$this->assertEquals($now->format(\DateTime::RFC3339), $object->datetime);
	}

	public function testJsonSerializeArray()
	{
		$data = new BaseTraitWithJsonSerializableTraitTestData([
			'array' => [
				'a' => 'A',
				'b' => 'B',
				'c' => 'C',
			]
		]);
		$object = $data->jsonSerialize();
		$this->assertArrayHasKey('a', $object->array);
		$this->assertArrayHasKey('b', $object->array);
		$this->assertArrayHasKey('c', $object->array);
		$this->assertEquals('A', $object->array['a']);
		$this->assertEquals('B', $object->array['b']);
		$this->assertEquals('C', $object->array['c']);
	}

	public function testJsonSerializeDateTimeInArray()
	{
		$now = new \DateTime();
		$data = new BaseTraitWithJsonSerializableTraitTestData([
			'datetime' => $now,
			'array' => [
				'datetime' => $now,
			],
		]);
		$object = $data->jsonSerialize();
		$this->assertEquals($object->datetime, $object->array['datetime']);
	}

	public function testJsonSerializeDateTimeInObject()
	{
		$now = new \DateTime();
		$object = new \stdClass();
		$object->datetime = $now;
		$data = new BaseTraitWithJsonSerializableTraitTestData([
			'datetime' => $now,
			'object' => $object,
		]);
		$object = $data->jsonSerialize();
		$this->assertEquals($object->datetime, $object->object->datetime);
	}

	public function testJsonSerializeDateTimeInTraversable()
	{
		$now = new \DateTime();
		$data = new BaseTraitWithJsonSerializableTraitTestData([
			'datetime' => $now,
			'traversable' => new \ArrayIterator([
				'datetime' => $now,
			]),
		]);
		$object = $data->jsonSerialize();
		$this->assertEquals($object->datetime, $object->traversable->datetime);
	}

	public function testJsonSerializeNestedJsonserializable()
	{
		$now = new \DateTime();
		$data = new BaseTraitWithJsonSerializableTraitTestData([
			'datetime' => $now,
			'serializable' => new BaseTraitWithJsonSerializableTraitTestData([
				'datetime' => $now,
				'serializable' => new BaseTraitWithJsonSerializableTraitTestData([
					'datetime' => $now,
					'serializable' => new BaseTraitWithJsonSerializableTraitTestData([
						'datetime' => $now,
					]),
				]),
			]),
		]);
		$object = $data->jsonSerialize();
		$this->assertEquals($object->datetime, $object->serializable->datetime);
		$this->assertEquals($object->datetime, $object->serializable->serializable->datetime);
		$this->assertEquals($object->datetime, $object->serializable->serializable->serializable->datetime);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testJsonSerializeRaiseLogicExceptionInvalidType()
	{
		$data = new BaseTraitWithJsonSerializableTraitTestData([
			'any' => new \PDO('sqlite::memory:'),
		]);
		$object = $data->jsonSerialize();
	}

	public function testEncodeAndDecode()
	{
		$now = new \DateTime();
		$data = new BaseTraitWithJsonSerializableTraitTestData([
			'datetime' => $now,
			'array' => [
				'datetime' => $now,
			],
			'traversable' => new \ArrayIterator([
				'datetime' => $now,
			]),
		]);
		$object = json_decode(json_encode($data)); // json_decode() の戻り値はオブジェクトまたは連想配列のどちらかのみ
		$this->assertEquals($now->format(\DateTime::RFC3339), $object->datetime);
		$this->assertEquals($now->format(\DateTime::RFC3339), $object->array->datetime);
		$this->assertEquals($now->format(\DateTime::RFC3339), $object->traversable->datetime);
	}

}
