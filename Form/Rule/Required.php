<?php
/**
 * A rule designed to be used with Structures_Form. This rule makes sure that an
 * element has some sort of value.
 *
 * The only validation done is to make sure that the value of the element is 
 * not empty.
 *
 * This class implement Structures_Form_RuleInterface.
 *
 * @author    Scott Mattocks
 * @package   Structures_Form
 * @license   PHP License
 * @version   @version@
 * @copyright Copyright 2006 Scott Mattocks
 */
require_once 'Structures/Form/RuleInterface.php';
class Structures_Form_Rule_Required implements Structures_Form_RuleInterface {

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
     * The array of substitutions to be made in the error message.
     *
     * The array should be of the form: array(<replace> => <withMethod>). The
     * <withMethod> should be an element method such as getName or getValue.
     *
     * @access protected
     * @var    array
     */
    protected $substitutions = array('{elementName}' => 'getLabel');

    /**
     * Constructor.
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
        // If a new error message was given set it.
        if (!is_null($errorMessage)) {
            $this->setErrorMessage($errorMessage);
        }

        // If a new substitution array was given set it.
        if (is_array($substitutions)) {
            $this->setSubstitutions($substitutions);
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
        // Check to see if the elements value is null.
        $value = $element->getValue();
        if (!empty($value)) {
            return true;
        }

        // The validation failed. Create an error message.
        // Get values for the substitution.
        $values = array();
        foreach (array_values($this->substitutions) as $method) {
            if (method_exists($element, $method)) {
                $values[] = $element->$method();
            } else {
                $values[] = $method;
            }
        }
   
        return str_replace(array_keys($this->substitutions),
                           $values,
                           $this->errorMessage
                           );
    }

    /**
     * Sets the error message to be used when a value fails to validate.
     *
     * The values in {curly braces} will be substituted for values from the
     * substitiution array.
     * 
     * @access public
     * @param  string $message The error message.
     * @return void
     */
    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;
    }

    /**
     * Sets the array of error message substitutions.
     *
     * The array should be of the form: array(<replace> => <withMethod>). The
     * <withMethod> should be an element method such as getName or getValue.
     * 
     * @access public
     * @param  array  $subs The array of substitutions.
     * @return void
     */
    public function setSubstitutions($subs)
    {
        if (is_array($subs)) {
            $this->substitutions = $subs;
        }
    }
}
?>