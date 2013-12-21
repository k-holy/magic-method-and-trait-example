<?php
/**
 * ドメインデータ
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Domain\Data;

/**
 * DataTrait
 *
 * @author k.holy74@gmail.com
 */
trait DataTrait
{

    /**
     * __construct()
     *
     * @param array プロパティの配列
     */
    public function __construct(array $properties = [])
    {
        foreach (array_keys(get_object_vars($this)) as $name) {
            $this->{$name} = null;
            if (array_key_exists($name, $properties)) {
                $value = (is_object($properties[$name]))
                    ? clone $properties[$name]
                    : $properties[$name];
                $camelize = $this->camelize($name);
                if (method_exists($this, 'set' . $camelize)) {
                    $this->{'set' . $camelize}($value);
                } else {
                    $this->{$name} = $value;
                }
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
     * @throws \LogicException
     */
    final public function __set($name, $value)
    {
        throw new \LogicException(
            sprintf('The property "%s" could not set.', $name)
        );
    }

    /**
     * __unset
     *
     * @param mixed
     * @throws \LogicException
     */
    final public function __unset($name)
    {
        throw new \LogicException(
            sprintf('The property "%s" could not unset.', $name)
        );
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
        return new self($properties);
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
