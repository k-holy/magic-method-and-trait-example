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

    public function prepare($statement, $options = null)
    {
        return new PDOStatement(parent::prepare($statement, $options ?: []));
    }

    public function query($statement, $fetchMode = null, $fetchOption = null, array $arguments = null)
    {
        switch (func_num_args()) {
        case 1:
            return new PDOStatement(parent::query($statement));
        case 2:
            return new PDOStatement(parent::query($statement, $fetchMode));
        case 3:
            return new PDOStatement(parent::query($statement, $fetchMode, $fetchOption));
        case 4:
            return new PDOStatement(parent::query($statement, $fetchMode, $fetchOption, $arguments));
        }
    }

}
