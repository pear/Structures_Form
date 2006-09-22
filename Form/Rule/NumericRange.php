<?php
/**
 * A rule designed to be used with Structures_Form. This rule makes sure that
 * an element has a numeric value between two other values (inclusive).
 *
 * One of a max or min must be supplied. If the max is NULL, then there will be
 * no max. If the min is NULL, then there will be no min. But both cannot be
 * NULL.
 *
 * If the element's value is NULL, this rule will pass. If you don't want a 
 * NULL value to pass, make the element required first.
 *
 * This class extends Structures_Form_Rule_Base.
 * This class implement Structures_Form_RuleInterface.
 *
 * @author    Scott Mattocks
 * @package   Structures_Form
 * @license   PHP License
 * @version   @version@
 * @copyright Copyright 2006 Scott Mattocks
 */
require_once 'Structures/Form/Rule/Numeric.php';
class Structures_Form_Rule_NumericRange extends Structures_Form_Rule_Numeric {

    /**
     * The error message to be returned if the element does not validate.
     *
     * The values in {curly braces} will be substituted for values from the
     * substitiution array.
     *
     * @access protected
     * @var    string
     */
    protected $errorMessage = '{elementName} must be a number {min}{and}{max}.';
    
    /**
     * The minimum value for the element.
     *
     * @access protected
     * @var    float
     */
    protected $min;

    /**
     * The maximum value for the element.
     *
     * @access protected
     * @var    float
     */
    protected $max;
    
    /**
     * Constructor. Calls the parent constructor then sets up the min and max
     * values.
     *
     * This rule does not require any additional arguments, but may accept two
     * optional arguments.
     *
     * @access public
     * @param  float  $min           The lower limit.
     * @param  float  $max           The upper limit.
     * @param  string $errorMessage  Optional error message.
     * @param  array  $substitutions Optional substitution array.
     * @return void
     * @throws Structures_Form_Exception
     */
    public function __construct($min, $max, $errorMessage = null,
                                $substitutions = null
                                )
    {
        // Call parent constructor.
        parent::__construct($errorMessage, $substitutions);

        // Check the min value.
        if (!is_null($min) && !is_numeric($min)) {
            // No good. Throw an exception!
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Excpetion('Invalid value for minimum numeric range: ' . $min);
        } else {
            // Set the min.
            $this->min = $min;
        }
        
        // Check the max value.
        if (!is_null && !is_numeric($max)) {
            // No good. Throw an exception!
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Excpetion('Invalid value for maximum numeric range: ' . $max);
        } else {
            // Set the max.
            $this->max = $max;
        }

        // Make sure that both the min and max are not null.
        if (is_null($this->min) && is_null($this->max)) {
            // No good. Throw an exception!
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Excpetion('At least one of min or max must be supplied.');
        }

        // Add the min to the substitutions array.
        if (is_null($this->min)) {
            // If there is no lower limit replace the tag with nothing
            $this->addSubstitution('{min}', '');
        } else {
            $this->addSubstitution('{min}', 'greater than or equal to ' . $this->min);
        }

        // Add the max to the substitutions array.
        if (is_null($this->max)) {
            // If there is no upper limit replace the tag with nothing
            $this->addSubstitution('{max}', '');
        } else {
            $this->addSubstitution('{max}', 'less than or equal to ' . $this->max);
        }

        // If there is both a lower and upper limit, add the word and.
        if (!is_null($this->min) && !is_null($this->max)) {
            $this->addSubstitution('{and}', ' and ');
        } else {
            $this->addSubstitution('{and}', '');
        }
    }

    /**
     * Validates the given element.
     *
     * @access public
     * @param  object $element A Structures_Form element.
     * @return mixed  true if the value is ok, an error message otherwise. 
     */
    public function validate(Structures_Form_ElementInterface $element)
    {
        // First check that the value is a number by calling the parent 
        // validate method.
        if (($retVal = parent::validate($element)) !== true) {
            return $retVal;
        }       

        // Grab the value (if it is null or an empty string return true).
        if (strlen($value = $element->getValue()) == 0) {
            return true;
        }

        // Make sure the value is greater than or equal to the lower limit.
        if (!is_null($this->min) && $value < $this->min) {
            // The validation failed. Create an error message.
            return $this->generateErrorString($element);
        }

        // Make sure the value is less than or equal to the upper limit.
        if (!is_null($this->max) && $value > $this->max) {
            // The validation failed. Create an error message.
            return $this->generateErrorString($element);
        }
        
        // All is good.
        return true;
    }
}
?>