<?php
/**
 * A rule designed to be used with Structures_Form. This rule makes sure that
 * an element has some sort of value.
 *
 * The only validation done is to make sure that the value of the element is 
 * not empty.
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
require_once 'Structures/Form/Rule/Base.php';
class Structures_Form_Rule_Required extends Structures_Form_Rule_Base {

    /**
     * The error message to be returned if the element does not validate.
     *
     * The values in {curly braces} will be substituted for values from the
     * substitiution array.
     *
     * @access protected
     * @var    string
     */
    protected $errorMessage = '{elementName} is required.';
    
    /**
     * Constructor. Just calls the parent constructor.
     *
     * This rule does not require any additional arguments, but may accept two
     * optional arguments.
     *
     * @access public
     * @param  string $errorMessage  Optional error message.
     * @param  array  $substitutions Optional substitution array.
     * @return void
     */
    public function __construct($errorMessage = null, $substitutions = null)
    {
        // Call parent constructor.
        parent::__construct($errorMessage, $substitutions);
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
        // Check to see if the elements value is null.
        $value = $element->getValue();
        if (!empty($value)) {
            return true;
        }

        // The validation failed. Create an error message.
        return $this->generateErrorString($element);
    }
}
?>