<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme;

/**
 * Callback イテレータ
 *
 * @author k.holy74@gmail.com
 */
class CallbackIterator extends \IteratorIterator
{

	/**
	 * @var Closure 要素を返す際に実行するコールバック関数
	 */
	private $callback;

	/**
	 * コンストラクタ
	 *
	 * @param Traversable
	 * @param Closure 要素を返す際に実行するコールバック関数
	 */
	public function __construct(\Traversable $iterator, \Closure $callback)
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
