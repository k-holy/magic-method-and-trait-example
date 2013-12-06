<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme;

/**
 * ArrayAccessTrait
 *
 * @author k.holy74@gmail.com
 */
trait ArrayAccessTrait
{

	/**
	 * ArrayAccess::offsetExists()
	 *
	 * @param mixed
	 * @return bool
	 */
	public function offsetExists($name)
	{
		if (method_exists($this, '__isset')) {
			return $this->__isset($name);
		}
		return (property_exists($this, $name) && $this->{$name} !== null);
	}

	/**
	 * ArrayAccess::offsetGet()
	 *
	 * @param mixed
	 * @return mixed
	 */
	public function offsetGet($name)
	{
		if (method_exists($this, '__get')) {
			return $this->__get($name);
		}
		return $this->{$name};
	}

	/**
	 * ArrayAccess::offsetSet()
	 *
	 * @param mixed
	 * @param mixed
	 */
	public function offsetSet($name, $value)
	{
		if (method_exists($this, '__set')) {
			return $this->__set($name, $value);
		}
		$this->{$name} = $value;
	}

	/**
	 * ArrayAccess::offsetUnset()
	 *
	 * @param mixed
	 */
	public function offsetUnset($name)
	{
		if (method_exists($this, '__unset')) {
			return $this->__unset($name);
		}
		$this->{$name} = null;
	}

}
