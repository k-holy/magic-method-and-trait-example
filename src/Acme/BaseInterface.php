<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme;

/**
 * BaseInterface
 *
 * @author k.holy74@gmail.com
 */
interface BaseInterface extends \IteratorAggregate
{

    /**
     * __isset
     *
     * @param mixed
     * @return bool
     */
    public function __isset($name);

    /**
     * __get
     *
     * @param mixed
     */
    public function __get($name);

    /**
     * __set
     *
     * @param mixed
     * @param mixed
     */
    public function __set($name, $value);

    /**
     * __unset
     *
     * @param mixed
     */
    public function __unset($name);

    /**
     * __clone for clone
     */
    public function __clone();

    /**
     * __sleep for serialize()
     *
     * @return array
     */
    public function __sleep();

    /**
     * __set_state for var_export()
     *
     * @param array
     * @return object
     */
    public static function __set_state($properties);

    /**
     * IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     */
    public function getIterator();

}
