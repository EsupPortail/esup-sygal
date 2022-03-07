<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Soutenance\Validator;

use Traversable;
use Laminas\Form\Element\DateTime;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception\InvalidArgumentException;

class DateLesserThan extends AbstractValidator
{
    const NOT_LESSER           = 'notLesserThan';
    const NOT_LESSER_INCLUSIVE = 'notLesserThanInclusive';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_LESSER => "The input is not greater than '%min%'",
        self::NOT_LESSER_INCLUSIVE => "The input is not greater or equal than '%min%'"
    );

    /**
     * @var array
     */
    protected $messageVariables = array(
        'max' => 'max'
    );

    /**
     * Minimum value
     *
     * @var mixed
     */
    protected $max;

    /**
     * Whether to do inclusive comparisons, allowing equivalence to max
     *
     * If false, then strict comparisons are done, and the value may equal
     * the min option
     *
     * @var bool
     */
    protected $inclusive;

    /**
     * Sets validator options
     *
     * @param  array|Traversable $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (!is_array($options)) {
            $options = func_get_args();
            $temp['max'] = array_shift($options);

            if (!empty($options)) {
                $temp['inclusive'] = array_shift($options);
            }

            $options = $temp;
        }

        if (!array_key_exists('max', $options)) {
            throw new Exception\InvalidArgumentException("Missing option 'max'");
        }

        if (!array_key_exists('inclusive', $options)) {
            $options['inclusive'] = false;
        }

        $this->setMax($options['max'])
            ->setInclusive($options['inclusive']);

        parent::__construct($options);
    }

    /**
     * Returns the max option
     *
     * @return mixed
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Sets the min option
     *
     * @param  mixed $max
     * @return DateLesserThan Provides a fluent interface
     */
    public function setMax($max)
    {
        $this->max = $max;
        return $this;
    }

    /**
     * Returns the inclusive option
     *
     * @return bool
     */
    public function getInclusive()
    {
        return $this->inclusive;
    }

    /**
     * Sets the inclusive option
     *
     * @param  bool $inclusive
     * @return DateLesserThan Provides a fluent interface
     */
    public function setInclusive($inclusive)
    {
        $this->inclusive = $inclusive;
        return $this;
    }

    /**
     * Returns true if and only if $value is greater than min option
     *
     * @param  DateTime $value
     * @return bool
     */
    public function isValid($value)
    {
        $max = $this->max;

        if ($this->inclusive) {
            if (! ($value < $max)) {
                $this->error(self::NOT_LESSER_INCLUSIVE);
                return false;
            }
        } else {
            if (! ($value <= $max)) {
                $this->error(self::NOT_LESSER);
                return false;
            }
        }

        return true;
    }
}
