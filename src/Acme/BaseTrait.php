<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme;

/**
 * BaseTrait
 *
 * @author k.holy74@gmail.com
 */
trait BaseTrait
{

	/**
	 * プロパティを引数の配列からセットして自身を返します。
	 *
	 * @param array プロパティの配列
	 * @return self
	 * @throws \InvalidArgumentException
	 */
	public function initialize(array $properties = array())
	{
		foreach (array_keys(get_object_vars($this)) as $name) {
			$this->{$name} = null;
			if (array_key_exists($name, $properties)) {
				$this->__set($name, $properties[$name]);
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

	/**
	 * __isset
	 *
	 * @param mixed
	 * @return bool
	 */
	public function __isset($name)
	{
		return (property_exists($this, $name) && $this->{$name} !== null);
	}

	/**
	 * __get
	 *
	 * @param mixed
	 * @throws \InvalidArgumentException
	 */
	public function __get($name)
	{
		$camelize = $this->camelize($name);
		if (method_exists($this, 'get' . $camelize)) {
			return $this->{'get' . $camelize}();
		}
		if (!property_exists($this, $name)) {
			throw new \InvalidArgumentException(
				sprintf('The property "%s" does not exists.', $name)
			);
		}
		return $this->{$name};
	}

	/**
	 * __set
	 *
	 * @param mixed
	 * @param mixed
	 * @throws \InvalidArgumentException
	 */
	public function __set($name, $value)
	{
		$camelize = $this->camelize($name);
		if (method_exists($this, 'set' . $camelize)) {
			return $this->{'set' . $camelize}($value);
		}
		if (!property_exists($this, $name)) {
			throw new \InvalidArgumentException(
				sprintf('The property "%s" does not exists.', $name)
			);
		}
		$this->{$name} = $value;
	}

	/**
	 * __unset
	 *
	 * @param mixed
	 * @throws \InvalidArgumentException
	 */
	public function __unset($name)
	{
		if (!property_exists($this, $name)) {
			throw new \InvalidArgumentException(
				sprintf('The property "%s" does not exists.', $name)
			);
		}
		$this->{$name} = null;
	}

	/**
	 * __clone for clone
	 */
	public function __clone()
	{
		foreach (get_object_vars($this) as $name => $value) {
			if (is_object($value)) {
				$this->{$name} = clone $value;
			}
		}
	}

	/**
	 * __sleep for serialize()
	 *
	 * @return array
	 */
	public function __sleep()
	{
		return array_keys(get_object_vars($this));
	}

	/**
	 * __set_state for var_export()
	 *
	 * @param array
	 * @return object
	 */
	public static function __set_state($properties)
	{
		return new static($properties);
	}

	/**
	 * IteratorAggregate::getIterator()
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator(get_object_vars($this));
	}

	/**
	 * @param string  $string
	 * @return string
	 */
	private function camelize($string)
	{
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
	}

}
