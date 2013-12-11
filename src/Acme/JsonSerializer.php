<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme;

/**
 * JsonSerializer
 *
 * @author k.holy74@gmail.com
 */
class JsonSerializer implements \JsonSerializable
{

	private $value;

	/**
	 * __construct()
	 *
	 * @param mixed 変換する値
	 */
	public function __construct($value = null)
	{
		$this->value = $value;
	}

	/**
	 * __invoke()
	 *
	 * @return mixed 変換後の値
	 * @throws \LogicException
	 */
	public function __invoke($value)
	{
		return $this->convert($value);
	}

	/**
	 * JsonSerializable::jsonSerialize()
	 *
	 * @return mixed 変換後の値
	 * @throws \LogicException
	 */
	public function jsonSerialize()
	{
		return $this->convert($this->value);
	}

	/**
	 * 全てのプロパティをJSONで表現可能な値に変換して返します。
	 *
	 * NULL および スカラー値はそのまま返します。
	 * 配列であれば イテレーションで取得した値を配列にセットして返します。
	 * JsonSerializable であれば jsonSerialize() メソッドの実行結果を返します。
	 * DateTime または DateTimeInterface であれば RFC3339 形式の文字列に変換して返します。
	 * Traversable であればイテレーションで取得した値を無名オブジェクトにセットして返します。
	 * stdClass であれば get_object_vars() で取得した値を無名オブジェクトにセットして返します。
	 * 上記以外の値がああれば \LogicException をスローします。
	 *
	 * @return mixed 変換後の値
	 * @throws \LogicException
	 */
	public function convert($value)
	{
		if (null === $value || is_scalar($value)) {
			return $value;
		}
		if (is_array($value)) {
			$array = [];
			foreach ($value as $name => $val) {
				$array[$name] = $this->convert($val);
			}
			return $array;
		}
		if (is_object($value)) {
			if ($value instanceof \JsonSerializable) {
				return $value->jsonSerialize();
			}
			if ($value instanceof \DateTime || $value instanceof \DateTimeInterface) {
				return $value->format(\DateTime::RFC3339);
			}
			if ($value instanceof \Traversable) {
				$object = new \stdClass;
				foreach ($value as $name => $val) {
					$object->{$name} = $this->convert($val);
				}
				return $object;
			}
			if ($value instanceof \stdClass) {
				$object = new \stdClass;
				foreach (get_object_vars($value) as $name => $val) {
					$object->{$name} = $this->convert($val);
				}
				return $object;
			}
		}
		throw new \LogicException(
			sprintf('The value is invalid to convert JSON. type:%s',
				is_object($value) ? get_class($value) : gettype($value)
			)
		);
	}

}
