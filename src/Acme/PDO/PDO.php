<?php
/**
 * PDO
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\PDO;

use Acme\PDO\PDOStatement;

/**
 * PDO
 *
 * @author k.holy74@gmail.com
 */
class PDO extends \PDO
{

    /**
     * @var string LIKE演算子のエスケープ文字
     */
    private $escapeCharacter = '\\';

    /**
     * prepare()
     *
     * @param string
     * @param int
     * @return Acme\PDO\PDOStatement
     * @override
     */
    public function prepare($statement, $options = null)
    {
        return new PDOStatement(parent::prepare($statement, $options ?: []));
    }

    /**
     * query()
     *
     * @param string
     * @param int
     * @param int
     * @param array
     * @return Acme\PDO\PDOStatement
     * @override
     */
    public function query($statement, $fetchMode = null, $fetchOption = null, array $arguments = null)
    {
        switch (func_num_args()) {
        case 4:
            return new PDOStatement(parent::query($statement, $fetchMode, $fetchOption, $arguments));
        case 3:
            return new PDOStatement(parent::query($statement, $fetchMode, $fetchOption));
        case 2:
            return new PDOStatement(parent::query($statement, $fetchMode));
        }
        return new PDOStatement(parent::query($statement));
    }

    /**
     * LIKE演算子のエスケープ文字をセットします。
     *
     * @param string エスケープに使用する文字
     */
    public function setEscapeCharacter($char)
    {
        $this->escapeCharacter = $char;
    }

    /**
     * LIKE演算子のパターンとして使用する文字列をエスケープして返します。
     *
     * @param string パターン文字列
     * @return string エスケープされたパターン文字列
     */
    public function escapeLikePattern($pattern)
    {
        return strtr($pattern, [
            '_' => $this->escapeCharacter . '_',
            '%' => $this->escapeCharacter . '%',
            $this->escapeCharacter => $this->escapeCharacter . $this->escapeCharacter,
        ]);
    }

}
