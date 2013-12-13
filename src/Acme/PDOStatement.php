<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme;

use Acme\CallbackIterator;

/**
 * PDOStatement
 *
 * @author k.holy74@gmail.com
 */
class PDOStatement implements \IteratorAggregate
{

	/**
	 * @var PDOStatement
	 */
	private $statement;

	/**
	 * @param callable フェッチ後に実行するコールバック
	 */
	private $callback;

	/**
	 * コンストラクタ
	 *
	 * @param PDOStatement
	 */
	public function __construct(\PDOStatement $statement)
	{
		$this->statement = $statement;
		$this->callback = null;
	}

	/**
	 * __call
	 *
	 * @param string
	 * @param array
	 */
	public function __call($name, $args)
	{
		if (method_exists($this->statement, $name)) {
			return call_user_func_array(array($this->statement, $name), $args);
		}
		throw new \BadMethodCallException(
			sprintf('Undefined Method "%s" called.', $name)
		);
	}

	/**
	 * フェッチ後に実行するコールバックをセットします。
	 *
	 * @param callable コールバック
	 */
	public function setFetchCallback(callable $callback)
	{
		$this->callback = $callback;
	}

	/**
	 * プリペアドステートメントを実行します。
	 *
	 * @param array | Traversable パラメータ
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function execute($parameters = null)
	{
		if (isset($parameters)) {
			if (!is_array($parameters) && !($parameters instanceof \Traversable)) {
				throw new \InvalidArgumentException(
					sprintf('Parameters accepts an Array or Traversable, invalid type:%s',
						(is_object($parameters))
							? get_class($parameters)
							: gettype($parameters)
					)
				);
			}
			foreach ($parameters as $name => $value) {
				$type = \PDO::PARAM_STR;
				if (is_int($value)) {
					$type = \PDO::PARAM_INT;
				} elseif (is_bool($value)) {
					$type = \PDO::PARAM_BOOL;
				} elseif (is_null($value)) {
					$type = \PDO::PARAM_NULL;
				}
				$this->statement->bindValue(
					(strncmp(':', $name, 1) !== 0) ? sprintf(':%s', $name) : $name,
					$value,
					$type
				);
			}
		}

		try {
			return $this->statement->execute();
		} catch (\PDOException $e) {
			ob_start();
			$this->statement->debugDumpParams();
			$debug = ob_get_contents();
			ob_end_clean();
			throw new \RuntimeException(
				sprintf('execute prepared statement failed. "%s"', $debug)
			);
		}
	}

	/**
	 * 結果セットから次の行を取得して返します。
	 *
	 * @param int フェッチモード定数
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function fetch($how = null, $orientation = null, $offset = null)
	{
		$result = $this->statement->fetch($how, $orientation, $offset);
		if (!isset($this->callback) || $result === false) {
			return $result;
		}
		return call_user_func($this->callback, $result);
	}

	/**
	 * IteratorAggregate::getIterator()
	 *
	 * @return Traversable
	 */
	public function getIterator()
	{
		return (isset($this->callback))
			? new CallbackIterator($this->statement, $this->callback)
			: new \IteratorIterator($this->statement);
	}

}
