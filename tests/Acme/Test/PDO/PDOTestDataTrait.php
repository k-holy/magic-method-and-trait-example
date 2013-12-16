<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test\PDO;

/**
 * PDOTestDataTrait
 *
 * @author k.holy74@gmail.com
 */
trait PDOTestDataTrait
{

    /**
     * __construct()
     *
     * @param array プロパティの配列
     */
    public function __construct(array $properties = null)
    {
        if (isset($properties)) {
            $this->initialize($properties);
        }
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
        if (isset($this->birthday)) {
            $birthday = new \DateTimeImmutable($this->birthday);
            if (isset($this->now)) {
                return $birthday->setTimezone($this->now->getTimezone());
            }
            return $birthday;
        }
        return null;
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
     * createdAtの値をタイムスタンプと見なし、DateTimeImmutableオブジェクトに変換して返します。
     *
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        if (isset($this->createdAt)) {
            $createdAt = new \DateTimeImmutable(sprintf('@%d', $this->createdAt));
            if (isset($this->now)) {
                return $createdAt->setTimezone($this->now->getTimezone());
            }
            return $createdAt;
        }
        return null;
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
        return $object;
    }

}
