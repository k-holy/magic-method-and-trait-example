<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme;

/**
 * ImmutableTrait
 *
 * @author k.holy74@gmail.com
 */
trait ImmutableTrait
{

	/**
	 * __set
	 *
	 * @param mixed
	 * @param mixed
	 */
	final public function __set($name, $value)
	{
		if (!property_exists($this, $name)) {
			throw new \InvalidArgumentException(
				sprintf('The property "%s" does not exists.', $name)
			);
		}
		throw new \LogicException(
			sprintf('The property "%s" could not set.', $name)
		);
	}

	/**
	 * __unset
	 *
	 * @param mixed
	 */
	final public function __unset($name)
	{
		if (!property_exists($this, $name)) {
			throw new \InvalidArgumentException(
				sprintf('The property "%s" does not exists.', $name)
			);
		}
		throw new \LogicException(
			sprintf('The property "%s" could not unset.', $name)
		);
	}

	/**
	 * プロパティを引数の配列からセットして自身を返します。
	 *
	 * @param array プロパティの配列
	 * @return self
	 */
	final private function initialize(array $properties = array())
	{
		foreach (array_keys(get_object_vars($this)) as $name) {
			if (array_key_exists($name, $properties)) {
				$this->{$name} = (is_object($properties[$name]))
					? clone $properties[$name]
					: $properties[$name];
				unset($properties[$name]);
			}
		}
		if (count($properties) !== 0) {
			throw new \InvalidArgumentException(
				sprintf('Not supported properties [%s]',
					implode(',', array_keys($properties))
				)
			);
		}
		return $this;
	}

}
