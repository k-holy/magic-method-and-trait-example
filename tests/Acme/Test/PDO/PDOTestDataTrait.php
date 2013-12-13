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
     * 日付の出力用タイムゾーンをセットします。
     *
     * @param \DateTimeZone
     */
    private function setTimezone(\DateTimeZone $timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * 日付の出力用書式をセットします。
     *
     * @param \DateTimeZone
     */
    private function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat ?: 'Y-m-d H:i:s';
    }

    /**
     * createdAtの値をタイムスタンプと見なし、DateTimeImmutableオブジェクトに変換して返します。
     *
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        $createdAt = new \DateTimeImmutable(sprintf('@%d', $this->createdAt));
        if (isset($this->timezone)) {
            return $createdAt->setTimezone($this->timezone);
        }
        return $createdAt;
    }

    /**
     * createdAtの値を出力用の書式で文字列に変換して返します。
     *
     * @return string
     */
    public function getCreatedAtAsString()
    {
        return $this->getCreatedAt()->format($this->dateFormat);
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
        $object->createdAt = $this->getCreatedAtAsString();
        return $object;
    }

}
