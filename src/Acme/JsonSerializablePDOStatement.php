<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme;

use Acme\JsonSerializer;

/**
 * JsonSerializablePDOStatement
 *
 * @author k.holy74@gmail.com
 */
class JsonSerializablePDOStatement extends \PDOStatement implements \JsonSerializable
{

	private $fetchStyleForJson;

	public function setFetchStyleForJson(array $fetchStyleForJson)
	{
		$this->fetchStyleForJson = $fetchStyleForJson;
	}

	public function jsonSerialize()
	{
		$jsonSerializer = new JsonSerializer();
		$values = [];
		if (isset($this->fetchStyleForJson[0]) && $this->fetchStyleForJson[0] === \PDO::FETCH_FUNC) {
			while ($item = $this->fetch(\PDO::FETCH_NUM)) {
				$values[] = $jsonSerializer(call_user_func_array($this->fetchStyleForJson[1], $item));
			}
			return $values;
		}
		if (isset($this->fetchStyleForJson)) {
			switch (count($this->fetchStyleForJson)) {
			case 1:
				$this->setFetchMode($this->fetchStyleForJson[0]);
				break;
			case 2:
				$this->setFetchMode($this->fetchStyleForJson[0], $this->fetchStyleForJson[1]);
				break;
			case 3:
				$this->setFetchMode($this->fetchStyleForJson[0], $this->fetchStyleForJson[1], $this->fetchStyleForJson[2]);
				break;
			}
		}
		foreach ($this as $i => $item) {
			$values[$i] = $jsonSerializer($item);
		}
		return $values;
	}

}
