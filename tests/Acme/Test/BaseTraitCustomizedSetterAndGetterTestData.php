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
 * TestData for BaseTrait customized setter and getter
 *
 * @author k.holy74@gmail.com
 */
class BaseTraitCustomizedSetterAndGetterTestData implements BaseInterface
{
    use BaseTrait;

    private $createdAt;
    private $options;

    public function __construct(array $properties = array())
    {
        $this->initialize($properties);
    }

    private function options($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    public function setCreatedAt($createdAt)
    {
        if (is_int($createdAt)) {
            $createdAt = new \DateTime(sprintf('@%d', $createdAt));
        } elseif (is_string($createdAt)) {
            $createdAt = new \DateTime($createdAt);
        }
        if (false === ($createdAt instanceof \DateTime)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid type:%s', (is_object($createdAt))
                    ? get_class($createdAt)
                    : gettype($createdAt)
                )
            );
        }
        $this->createdAt = $createdAt;
    }

    public function getCreatedAtAsString()
    {
        $dateTimeFormat = $this->options('dateTimeFormat');
        return $this->createdAt->format($dateTimeFormat ?: 'Y-m-d H:i:s');
    }

}
