<?php
/**
 * PDO
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\PDO;

/**
 * Callback イテレータ
 *
 * @author k.holy74@gmail.com
 */
class CallbackIterator extends \IteratorIterator
{

    /**
     * @var callable 要素を返す際に実行するコールバック関数
     */
    private $callback;

    /**
     * コンストラクタ
     *
     * @param Traversable
     * @param callable 要素を返す際に実行するコールバック関数
     */
    public function __construct(\Traversable $iterator, callable $callback)
    {
        $this->callback = $callback;
        parent::__construct($iterator);
    }

    /**
     * Iterator::current
     *
     * @return mixed
     */
    public function current()
    {
        return call_user_func($this->callback, parent::current());
    }

}
