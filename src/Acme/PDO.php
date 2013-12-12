<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme;

use Acme\PDOStatement;

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

	public function query($statement)
	{
		$args = func_get_args();
		switch (func_num_args()) {
		case 1:
			return new PDOStatement(parent::query($statement));
		case 2:
			return new PDOStatement(parent::query($statement, $args[1]));
		case 3:
			return new PDOStatement(parent::query($statement, $args[1], $args[2]));
		case 4:
			return new PDOStatement(parent::query($statement, $args[1], $args[2], $args[3]));
		}
		throw new \InvalidArgumentException('Invalid argument count.');
	}

}
