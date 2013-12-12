<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme;

use Acme\CallbackIterator;
use Acme\JsonSerializer;

/**
 * PDOStatement
 *
 * @author k.holy74@gmail.com
 */
class PDOStatement implements \IteratorAggregate, \JsonSerializable
{

	const FETCH_ASSOC   = 'assoc';
	const FETCH_NUM     = 'num';
	const FETCH_INTO    = 'into';
	const FETCH_CLASS   = 'class';
	const FETCH_CLOSURE = 'closure';

	/**
	 * @var PDOStatement
	 */
	private $statement;

	/**
	 * @var string フェッチモード [FETCH_ASSOC | FETCH_NUM | FETCH_INTO | FETCH_CLASS | FETCH_CLOSURE]
	 *
	 * このクラスでは複数のモードを組み合わせた指定を許可しません。
	 * FETCH_ASSOC, FETCH_NUM, FETCH_INTO, FETCH_CLASS は標準の \PDO::FETCH_** と同じ動作を行います。
	 * FETCH_CLOSURE のみ特殊なモードで、フェッチした結果を引数として指定のクロージャを実行し、その結果を返します。
	 */
	private $fetchMode;

	/**
	 * @var Closure フェッチモードが FETCH_CLOSURE の場合に実行する関数
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
		$this->fetchMode = null;
		$this->callback = null;
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
	 * このステートメントのデフォルトのフェッチモードを設定します。
	 *
	 * @param int フェッチモード定数
	 * @param mixed フェッチモードのオプション引数
	 * @param array FETCH_CLASS の場合のコンストラクタ引数の配列
	 * @return self
	 * @throws \InvalidArgumentException
	 */
	public function setFetchMode($mode, $option = null, array $arguments = array())
	{
		switch ($mode) {
		case self::FETCH_ASSOC:
			$this->fetchMode = $mode;
			$this->statement->setFetchMode(\PDO::FETCH_ASSOC);
			break;
		case self::FETCH_NUM:
			$this->fetchMode = $mode;
			$this->statement->setFetchMode(\PDO::FETCH_NUM);
			break;
		case self::FETCH_INTO:
			$this->fetchMode = $mode;
			$this->statement->setFetchMode(\PDO::FETCH_INTO, $option);
			break;
		case self::FETCH_CLASS:
			$this->fetchMode = $mode;
			if (!class_exists($option, true)) {
				throw new \InvalidArgumentException(
					sprintf('PDOStatement::FETCH_CLASS accepts only className, unknown className:%s',
						$option
					)
				);
			}
			$this->statement->setFetchMode(\PDO::FETCH_CLASS, $option, $arguments);
			break;
		case self::FETCH_CLOSURE:
			$this->fetchMode = $mode;
			if (false === ($option instanceof \Closure)) {
				throw new \InvalidArgumentException(
					sprintf('PDOStatement::FETCH_CLOSURE accepts only Closure, invalid type:%s',
						(is_object($option))
							? get_class($option)
							: gettype($option)
					)
				);
			}
			$this->callback = $option;
			break;
		default:
			throw new \InvalidArgumentException(
				sprintf('Unsupported fetchMode:%s', $mode)
			);
			break;
		}

		return $this;
	}

	/**
	 * 結果セットから次の行を取得して返します。
	 *
	 * @param int フェッチモード定数
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function fetch($mode = null)
	{
		if ($mode === null) {
			$mode = $this->fetchMode;
		}
		if ($mode === null) {
			return $this->statement->fetch();
		}

		switch ($mode) {
		case self::FETCH_ASSOC:
			return $this->statement->fetch(\PDO::FETCH_ASSOC);
		case self::FETCH_NUM:
			return $this->statement->fetch(\PDO::FETCH_NUM);
		case self::FETCH_INTO:
			return $this->statement->fetch(\PDO::FETCH_INTO);
		case self::FETCH_CLASS:
			return $this->statement->fetch(\PDO::FETCH_CLASS);
		case self::FETCH_CLOSURE:
			$result = $this->statement->fetch();
			if (!is_array($result)) {
				return false;
			}
			return call_user_func($this->callback, $result);
		}

		throw new \InvalidArgumentException(
			sprintf('Unsupported fetchMode:%s', $mode)
		);
	}

	/**
	 * IteratorAggregate::getIterator()
	 *
	 * @return Traversable
	 */
	public function getIterator()
	{
		if ($this->fetchMode === self::FETCH_CLOSURE) {
			return new CallbackIterator($this->statement, $this->callback);
		}

		return $this->statement;
	}

	/**
	 * JsonSerializable::jsonSerialize()
	 *
	 * @return array
	 */
	public function jsonSerialize()
	{
		$values = [];
		$jsonSerializer = new JsonSerializer();
		foreach ($this->getIterator() as $i => $item) {
			$values[$i] = $jsonSerializer($item);
		}

		return $values;
	}

}
