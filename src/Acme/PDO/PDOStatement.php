<?php
/**
 * PDO
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\PDO;

use Acme\PDO\CallbackIterator;

/**
 * PDOStatement
 *
 * @author k.holy74@gmail.com
 */
class PDOStatement implements \IteratorAggregate, \JsonSerializable
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
     * @throws \RuntimeException
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
                $this->statement->bindValue($name, $value, $type);
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
     * @param int PDO::FETCH_* 定数
     * @param int PDO::FETCH_ORI_* 定数
     * @param int カーソルの位置
     * @return mixed 失敗した場合は false
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

    /**
     * JsonSerializable::jsonSerialize()
     *
     * @return mixed 変換後の値
     * @throws \LogicException
     */
    public function jsonSerialize()
    {
        $values = [];
        foreach ($this->getIterator() as $i => $current) {
            $values[$i] = $this->convert($current);
        }
        return $values;
    }

    /**
     * 全てのプロパティをJSONで表現可能な値に変換して返します。
     *
     * NULL および スカラー値はそのまま返します。
     * 配列であれば イテレーションで取得した値を配列にセットして返します。
     * JsonSerializable であれば jsonSerialize() メソッドの実行結果を返します。
     * DateTime または DateTimeInterface であれば RFC3339 形式の文字列に変換して返します。
     * Traversable であればイテレーションで取得した値を配列にセットして返します。
     * stdClass であれば get_object_vars() で取得した値を無名オブジェクトにセットして返します。
     * 上記以外の値がああれば \LogicException をスローします。
     *
     * @return mixed 変換後の値
     * @throws \LogicException
     */
    public function convert($value)
    {
        if (null === $value || is_scalar($value)) {
            return $value;
        }
        if (is_array($value)) {
            $array = [];
            foreach ($value as $name => $val) {
                $array[$name] = $this->convert($val);
            }
            return $array;
        }
        if (is_object($value)) {
            if ($value instanceof \JsonSerializable) {
                return $value->jsonSerialize();
            }
            if ($value instanceof \DateTime || $value instanceof \DateTimeInterface) {
                return $value->format(\DateTime::RFC3339);
            }
            if ($value instanceof \Traversable) {
                $array = [];
                foreach ($value as $name => $val) {
                    $array[$name] = $this->convert($val);
                }
                return $array;
            }
            if ($value instanceof \stdClass) {
                $object = new \stdClass;
                foreach (get_object_vars($value) as $name => $val) {
                    $object->{$name} = $this->convert($val);
                }
                return $object;
            }
        }
        throw new \LogicException(
            sprintf('The value is invalid to convert JSON. type:%s',
                is_object($value) ? get_class($value) : gettype($value)
            )
        );
    }

}
