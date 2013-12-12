<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Domain\Data;

/**
 * UserTrait
 *
 * @author k.holy74@gmail.com
 */
trait UserTrait
{
	public function getCreatedAt()
	{
		$createdAt = new \DateTime(sprintf('@%d', $this->createdAt));
		if (isset($this->timezone)) {
			$createdAt->setTimezone($this->timezone);
		}
		return $createdAt->format($this->dateFormat);
	}

	/**
	 * JsonSerializable::jsonSerialize
	 *
	 * @return object
	 * @throws \LogicException
	 */
	public function jsonSerialize()
	{
		$object = new \stdClass;
		$object->userId = $this->userId;
		$object->userName = $this->userName;
		$object->createdAt = $this->getCreatedAt();
		return $object;
	}

}
