<?php
/**
 * ドメインデータ
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Domain\Data;

/**
 * DataInterface
 *
 * @author k.holy74@gmail.com
 */
interface DataInterface
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

    /**
     * JsonSerializable::jsonSerialize
     *
     * @return \stdClass for json_encode()
     */
    public function jsonSerialize();

}
