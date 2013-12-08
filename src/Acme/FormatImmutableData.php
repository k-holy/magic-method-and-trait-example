<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme;

final class FormatImmutableData implements BaseInterface
{
	use BaseTrait, ImmutableTrait {
		ImmutableTrait::initialize insteadof BaseTrait;
		ImmutableTrait::__set insteadof BaseTrait;
		ImmutableTrait::__unset insteadof BaseTrait;
	}

	private $savedDate;
	private $options;

	final public function __construct(array $properties = array())
	{
		$this->initialize($properties);
	}

	final private function options($name)
	{
		return isset($this->options[$name]) ? $this->options[$name] : null;
	}

	final private function setSavedDate($savedDate)
	{
		if (is_int($savedDate)) {
			$savedDate = new \DateTime(sprintf('@%d', $savedDate));
		} elseif (is_string($savedDate)) {
			$savedDate = new \DateTime($savedDate);
		}
		if (false === ($savedDate instanceof \DateTime)) {
			throw new \InvalidArgumentException(
				sprintf('Invalid type:%s', (is_object($savedDate))
					? get_class($savedDate)
					: gettype($savedDate)
				)
			);
		}
		$timezone = $this->options('timezone');
		if (isset($timezone)) {
			$savedDate->setTimezone($timezone);
		}
		$this->savedDate = $savedDate;
	}

	final public function getSavedDateAsString()
	{
		$dateTimeFormat = $this->options('dateTimeFormat');
		return $this->savedDate->format($dateTimeFormat ?: 'Y-m-d H:i:s');
	}

}
