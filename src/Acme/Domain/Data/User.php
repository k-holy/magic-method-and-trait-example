<?php
/**
 * ドメインデータ
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Domain\Data;

use Acme\Domain\Data\DataInterface;
use Acme\Domain\Data\DataTrait;

/**
 * User
 *
 * @author k.holy74@gmail.com
 */
class User implements DataInterface, \IteratorAggregate, \JsonSerializable
{

    use DataTrait;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $userName;

    /**
     * @var \DateTimeImmutable
     */
    private $birthday;

    /**
     * @var \DateTimeImmutable
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

    /**
     * birthdayの値をセットします。
     *
     * @param \DateTimeImmutable
     */
    private function setBirthday(\DateTimeImmutable $birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * createdAtの値をセットします。
     *
     * @param \DateTimeImmutable
     */
    private function setCreatedAt(\DateTimeImmutable $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * 現在日時をセットします。
     *
     * @param \DateTimeImmutable
     */
    private function setNow(\DateTimeImmutable $now)
    {
        $this->now = $now;
    }

    /**
     * 日付の出力用書式をセットします。
     *
     * @param string
     */
    private function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat ?: 'Y-m-d';
    }

    /**
     * 日時の出力用書式をセットします。
     *
     * @param string
     */
    private function setDateTimeFormat($dateTimeFormat)
    {
        $this->dateTimeFormat = $dateTimeFormat ?: 'Y-m-d H:i:s';
    }

    /**
     * birthdayの値に出力用のTimezoneをセットして返します。
     *
     * @return \DateTimeImmutable
     */
    public function getBirthday()
    {
        return (isset($this->birthday) && isset($this->now))
            ? $this->birthday->setTimezone($this->now->getTimezone())
            : $this->birthday;
    }

    /**
     * birthdayの値を出力用の書式で文字列に変換して返します。
     *
     * @return string
     */
    public function getBirthdayAsString()
    {
        $birthday = $this->getBirthday();
        if (isset($birthday)) {
            return $birthday->format($this->dateFormat);
        }
        return null;
    }

    /**
     * 年齢を返します。
     *
     * @return int
     */
    public function getAge()
    {
        $birthday = $this->getBirthday();
        if (isset($birthday)) {
            return (int)(((int)$this->now->format('Ymd') - (int)$birthday->format('Ymd')) / 10000);
        }
        return null;
    }

    /**
     * createdAtの値に出力用のTimezoneをセットして返します。
     *
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return (isset($this->createdAt) && isset($this->now))
            ? $this->createdAt->setTimezone($this->now->getTimezone())
            : $this->createdAt;
    }

    /**
     * createdAtの値を出力用の書式で文字列に変換して返します。
     *
     * @return string
     */
    public function getCreatedAtAsString()
    {
        $createdAt = $this->getCreatedAt();
        if (isset($createdAt)) {
            return $createdAt->format($this->dateTimeFormat);
        }
        return null;
    }

    /**
     * JsonSerializable::jsonSerialize
     *
     * @return \stdClass for json_encode()
     */
    public function jsonSerialize()
    {
        $object = new \stdClass;
        $object->userId = $this->userId;
        $object->userName = $this->userName;
        $object->birthday = $this->getBirthdayAsString();
        $object->createdAt = $this->getCreatedAtAsString();
        $object->age = $this->getAge();
        return $object;
    }

}
