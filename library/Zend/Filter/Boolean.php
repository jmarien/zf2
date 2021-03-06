<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zend\Filter;

use Traversable;
use Zend\Stdlib\ArrayUtils;


/**
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Boolean extends AbstractLocale
{
    const TYPE_BOOLEAN        = 1;
    const TYPE_INTEGER        = 2;
    const TYPE_FLOAT          = 4;
    const TYPE_STRING         = 8;
    const TYPE_ZERO_STRING    = 16;
    const TYPE_EMPTY_ARRAY    = 32;
    const TYPE_NULL           = 64;
    const TYPE_PHP            = 127;
    const TYPE_FALSE_STRING   = 128;
    const TYPE_LOCALIZED      = 256;
    const TYPE_ALL            = 511;

    /**
     * @var array
     */
    protected $constants = array(
        self::TYPE_BOOLEAN       => 'boolean',
        self::TYPE_INTEGER       => 'integer',
        self::TYPE_FLOAT         => 'float',
        self::TYPE_STRING        => 'string',
        self::TYPE_ZERO_STRING   => 'zero',
        self::TYPE_EMPTY_ARRAY   => 'array',
        self::TYPE_NULL          => 'null',
        self::TYPE_PHP           => 'php',
        self::TYPE_FALSE_STRING  => 'false',
        self::TYPE_LOCALIZED     => 'localized',
        self::TYPE_ALL           => 'all',
    );

    /**
     * @var array
     */
    protected $options = array(
        'type'         => self::TYPE_PHP,
        'casting'      => true,
        'locale'       => null,
        'translations' => array(),
    );

    /**
     * Constructor
     *
     * @param string|array|Traversable $options OPTIONAL
     */
    public function __construct($options = null)
    {
        if ($options !== null) {
            if ($options instanceof Traversable) {
                $options = ArrayUtils::iteratorToArray($options);
            }

            if (!is_array($options)) {
                $args = func_get_args();
                if (isset($args[0])) {
                    $this->setType($args[0]);
                }
                if (isset($args[1])) {
                    $this->setCasting($args[1]);
                }
                if (isset($args[2])) {
                    $this->setLocale($args[2]);
                }
            } else {
                $this->setOptions($options);
            }
        }
    }

    /**
     * Set boolean types
     *
     * @param  integer|array $type
     * @throws Exception\InvalidArgumentException
     * @return Boolean
     */
    public function setType($type = null)
    {
        if (is_array($type)) {
            $detected = 0;
            foreach ($type as $value) {
                if (is_int($value)) {
                    $detected += $value;
                } elseif (in_array($value, $this->constants)) {
                    $detected += array_search($value, $this->constants);
                }
            }

            $type = $detected;
        } elseif (is_string($type) && in_array($type, $this->constants)) {
            $type = array_search($type, $this->constants);
        }

        if (!is_int($type) || ($type < 0) || ($type > self::TYPE_ALL)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Unknown type value "%s" (%s)',
                $type,
                gettype($type)
            ));
        }

        $this->options['type'] = $type;
        return $this;
    }

    /**
     * Returns defined boolean types
     *
     * @return int
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * Set the working mode
     *
     * @param  boolean $flag When true this filter works like cast
     *                       When false it recognises only true and false
     *                       and all other values are returned as is
     * @return Boolean
     */
    public function setCasting($flag = true)
    {
        $this->options['casting'] = (boolean) $flag;
        return $this;
    }

    /**
     * Returns the casting option
     *
     * @return boolean
     */
    public function getCasting()
    {
        return $this->options['casting'];
    }

    /**
     * @param  array $translations
     * @return Boolean
     */
    public function setTranslations(array $translations)
    {
        foreach ($translations as $locale => $translation) {
            $this->addTranslation($locale, $translation);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getTranslations()
    {
        return $this->options['translations'];
    }

    /**
     * @param  string $locale
     * @param  array $translation
     * @return Boolean
     * @throws Exception\InvalidArgumentException
     */
    public function addTranslation($locale, array $translation)
    {
        foreach ($translation as $message => $flag) {
            $this->options['translations'][$locale][$message] = (bool) $flag;
        }

        return $this;
    }

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Returns a boolean representation of $value
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $type    = $this->getType();
        $casting = $this->getCasting();

        // LOCALIZED
        if ($type >= self::TYPE_LOCALIZED) {
            $type -= self::TYPE_LOCALIZED;
            if (is_string($value)) {
                $locale = $this->getLocale();
                if (isset($this->options['translations'][$locale][$value])) {
                    return (bool) $this->options['translations'][$locale][$value];
                }
            }
        }

        // FALSE_STRING ('false')
        if ($type >= self::TYPE_FALSE_STRING) {
            $type -= self::TYPE_FALSE_STRING;
            if (is_string($value) && (strtolower($value) == 'false')) {
                return false;
            }

            if (!$casting && is_string($value) && (strtolower($value) == 'true')) {
                return true;
            }
        }

        // NULL (null)
        if ($type >= self::TYPE_NULL) {
            $type -= self::TYPE_NULL;
            if ($value === null) {
                return false;
            }
        }

        // EMPTY_ARRAY (array())
        if ($type >= self::TYPE_EMPTY_ARRAY) {
            $type -= self::TYPE_EMPTY_ARRAY;
            if (is_array($value) && ($value == array())) {
                return false;
            }
        }

        // ZERO_STRING ('0')
        if ($type >= self::TYPE_ZERO_STRING) {
            $type -= self::TYPE_ZERO_STRING;
            if (is_string($value) && ($value == '0')) {
                return false;
            }

            if (!$casting && (is_string($value)) && ($value == '1')) {
                return true;
            }
        }

        // STRING ('')
        if ($type >= self::TYPE_STRING) {
            $type -= self::TYPE_STRING;
            if (is_string($value) && ($value == '')) {
                return false;
            }
        }

        // FLOAT (0.0)
        if ($type >= self::TYPE_FLOAT) {
            $type -= self::TYPE_FLOAT;
            if (is_float($value) && ($value == 0.0)) {
                return false;
            }

            if (!$casting && is_float($value) && ($value == 1.0)) {
                return true;
            }
        }

        // INTEGER (0)
        if ($type >= self::TYPE_INTEGER) {
            $type -= self::TYPE_INTEGER;
            if (is_int($value) && ($value == 0)) {
                return false;
            }

            if (!$casting && is_int($value) && ($value == 1)) {
                return true;
            }
        }

        // BOOLEAN (false)
        if ($type >= self::TYPE_BOOLEAN) {
            $type -= self::TYPE_BOOLEAN;
            if (is_bool($value)) {
                return $value;
            }
        }

        if ($casting) {
            return true;
        }

        return $value;
    }
}
