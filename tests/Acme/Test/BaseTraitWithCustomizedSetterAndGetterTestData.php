<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

use Acme\BaseInterface;
use Acme\BaseTrait;

/**
 * TestData for BaseTrait with customized setter and getter
 *
 * @author k.holy74@gmail.com
 */
class BaseTraitWithCustomizedSetterAndGetterTestData implements BaseInterface
{
	use BaseTrait;

	private $savedDate;
	private $options;

	public function __construct(array $properties = array())
	{
		$this->initialize($properties);
	}

	private function options($name)
	{
		return isset($this->options[$name]) ? $this->options[$name] : null;
	}

	public function setSavedDate($savedDate)
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
		$this->savedDate = $savedDate;
	}

	public function getSavedDateAsString()
	{
		$dateTimeFormat = $this->options('dateTimeFormat');
		return $this->savedDate->format($dateTimeFormat ?: 'Y-m-d H:i:s');
	}

}
