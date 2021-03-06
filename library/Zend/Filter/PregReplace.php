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
class PregReplace extends AbstractFilter
{
    protected $options = array(
        'pattern'     => null,
        'replacement' => '',
    );

    /**
     * Constructor
     * Supported options are
     *     'pattern'     => matching pattern
     *     'replacement' => replace with this
     *
     * @param  array|Traversable|string|null $options
     * @return void
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = iterator_to_array($options);
        }

        if (!is_array($options)
            || (!isset($options['pattern']) && !isset($options['replacement'])))
        {
            $args = func_get_args();
            if (isset($args[0])) {
                $this->setPattern($args[0]);
            }
            if (isset($args[1])) {
                $this->setReplacement($args[1]);
            }
        } else {
            $this->setOptions($options);
        }
    }

    /**
     * Set the regex pattern to search for
     * @see preg_replace()
     *
     * @param  string|array $pattern - same as the first argument of preg_replace
     * @return PregReplace
     * @throws Exception\InvalidArgumentException
     */
    public function setPattern($pattern)
    {
        if (!is_array($pattern) && !is_string($pattern)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects pattern to be array or string; received "%s"',
                __METHOD__,
                (is_object($pattern) ? get_class($pattern) : gettype($pattern))
            ));
        }
        $this->options['pattern'] = $pattern;
        return $this;
    }

    /**
     * Get currently set match pattern
     *
     * @return string|array
     */
    public function getPattern()
    {
        return $this->options['pattern'];
    }

    /**
     * Set the replacement array/string
     * @see preg_replace()
     *
     * @param  array|string $replacement - same as the second argument of preg_replace
     * @return PregReplace
     * @throws Exception\InvalidArgumentException
     */
    public function setReplacement($replacement)
    {
        if (!is_array($replacement) && !is_string($replacement)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects replacement to be array or string; received "%s"',
                __METHOD__,
                (is_object($replacement) ? get_class($replacement) : gettype($replacement))
            ));
        }
        $this->options['replacement'] = $replacement;
        return $this;
    }

    /**
     * Get currently set replacement value
     *
     * @return string|array
     */
    public function getReplacement()
    {
        return $this->options['replacement'];
    }

    /**
     * Perform regexp replacement as filter
     *
     * @param  mixed $value
     * @return mixed
     * @throws Exception\RuntimeException
     */
    public function filter($value)
    {
        if ($this->options['pattern'] === null) {
            throw new Exception\RuntimeException(sprintf(
                'Filter %s does not have a valid pattern set',
                get_called_class()
            ));
        }

        return preg_replace($this->options['pattern'], $this->options['replacement'], $value);
    }
}
