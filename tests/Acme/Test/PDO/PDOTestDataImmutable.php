<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test\PDO;

use Acme\BaseInterface;
use Acme\BaseTrait;
use Acme\ImmutableTrait;

/**
 * PDOTestDataImmutable
 *
 * @author k.holy74@gmail.com
 */
class PDOTestDataImmutable implements BaseInterface, \JsonSerializable
{
    use BaseTrait, ImmutableTrait, PDOTestDataTrait {
        ImmutableTrait::initialize insteadof BaseTrait;
        ImmutableTrait::__set insteadof BaseTrait;
        ImmutableTrait::__unset insteadof BaseTrait;
    }

    /**
     * @var string
     */
    private $userId;

    /**
     * @var string
     */
    private $userName;

    /**
     * @var string Y-m-d
     */
    private $birthday;

    /**
     * @var string タイムスタンプ値
     */
    private $createdAt;

    /**
     * @var \DateTimeImmutable 現在日時
     */
    private $now;

    /**
     * @var string 日付の出力用書式
     */
    private $dateFormat;

    /**
     * @var string 日時の出力用書式
     */
    private $dateTimeFormat;

}
