<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

use Acme\JsonSerializer;

/**
 * Test for JsonSerializer
 *
 * @author k.holy74@gmail.com
 */
class JsonSerializerTest extends \PHPUnit_Framework_TestCase
{

	public function testJsonSerializeNull()
	{
		$serializer = new JsonSerializer(null);
		$this->assertNull($serializer->jsonSerialize());
	}

	public function testJsonSerializeString()
	{
		$serializer = new JsonSerializer('Foo');
		$this->assertEquals('Foo', $serializer->jsonSerialize());
	}

	public function testJsonSerializeInteger()
	{
		$serializer = new JsonSerializer(1);
		$this->assertEquals(1, $serializer->jsonSerialize());
	}

	public function testJsonSerializeBoolean()
	{
		$serializer = new JsonSerializer(false);
		$this->assertFalse($serializer->jsonSerialize());
	}

	public function testJsonSerializeArray()
	{
		$serializer = new JsonSerializer(['a' => 'A', 'b' => 'B', 'c' => 'C']);
		$data = $serializer->jsonSerialize();
		$this->assertArrayHasKey('a', $data);
		$this->assertArrayHasKey('b', $data);
		$this->assertArrayHasKey('c', $data);
		$this->assertEquals('A', $data['a']);
		$this->assertEquals('B', $data['b']);
		$this->assertEquals('C', $data['c']);
	}

	public function testJsonSerializeJsonSerializable()
	{
		$serializer = new JsonSerializer(new JsonSerializer(['a' => 'A', 'b' => 'B', 'c' => 'C']));
		$data = $serializer->jsonSerialize();
		$this->assertArrayHasKey('a', $data);
		$this->assertArrayHasKey('b', $data);
		$this->assertArrayHasKey('c', $data);
		$this->assertEquals('A', $data['a']);
		$this->assertEquals('B', $data['b']);
		$this->assertEquals('C', $data['c']);
	}

	public function testJsonSerializeDateTime()
	{
		$now = new \DateTime();
		$serializer = new JsonSerializer($now);
		$data = $serializer->jsonSerialize();
		$this->assertEquals($now->format(\DateTime::RFC3339), $data);
	}

	public function testJsonSerializeTraversable()
	{
		$serializer = new JsonSerializer(new \ArrayIterator(['a' => 'A', 'b' => 'B', 'c' => 'C']));
		$data = $serializer->jsonSerialize();
		$this->assertObjectHasAttribute('a', $data);
		$this->assertObjectHasAttribute('b', $data);
		$this->assertObjectHasAttribute('c', $data);
		$this->assertEquals('A', $data->a);
		$this->assertEquals('B', $data->b);
		$this->assertEquals('C', $data->c);
	}

	public function testJsonSerializeNestedTraversable()
	{
		$now = new \DateTime();
		$serializer = new JsonSerializer(new \ArrayIterator([
			'now' => $now,
			'traversable' => new \ArrayIterator([
				'now' => $now,
				'traversable' => new \ArrayIterator([
					'now' => $now,
					'traversable' => new \ArrayIterator([
						'now' => $now,
					]),
				]),
			]),
		]));
		$data = $serializer->jsonSerialize();
		$this->assertEquals($data->now, $data->traversable->now);
		$this->assertEquals($data->now, $data->traversable->traversable->now);
		$this->assertEquals($data->now, $data->traversable->traversable->traversable->now);
	}

	public function testJsonSerializeStdClass()
	{
		$object = new \stdClass();
		$object->a = 'A';
		$object->b = 'B';
		$object->c = 'C';
		$serializer = new JsonSerializer($object);
		$data = $serializer->jsonSerialize();
		$this->assertObjectHasAttribute('a', $data);
		$this->assertObjectHasAttribute('b', $data);
		$this->assertObjectHasAttribute('c', $data);
		$this->assertEquals('A', $data->a);
		$this->assertEquals('B', $data->b);
		$this->assertEquals('C', $data->c);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testJsonSerializeRaiseLogicExceptionInvalidType()
	{
		$pdo = new \PDO('sqlite::memory:');
		$serializer = new JsonSerializer($pdo);
		$object = $serializer->jsonSerialize();
	}

	public function testInvokable()
	{
		$iterator = new \ArrayIterator(['a' => 'A', 'b' => 'B', 'c' => 'C']);
		$serializer = new JsonSerializer();
		$data = $serializer($iterator);
		$this->assertObjectHasAttribute('a', $data);
		$this->assertObjectHasAttribute('b', $data);
		$this->assertObjectHasAttribute('c', $data);
		$this->assertEquals('A', $data->a);
		$this->assertEquals('B', $data->b);
		$this->assertEquals('C', $data->c);
	}

	public function testEncodeAndDecode()
	{
		$serializer = new JsonSerializer(['a' => 'A', 'b' => 'B', 'c' => 'C']);
		$data = json_decode(json_encode($serializer)); // json_decode() の戻り値はオブジェクトまたは連想配列のどちらかのみ
		$this->assertObjectHasAttribute('a', $data);
		$this->assertObjectHasAttribute('b', $data);
		$this->assertObjectHasAttribute('c', $data);
		$this->assertEquals('A', $data->a);
		$this->assertEquals('B', $data->b);
		$this->assertEquals('C', $data->c);
	}

}
