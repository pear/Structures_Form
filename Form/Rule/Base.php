<?php
/**
 * A base class for Structures_Form rules.
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
abstract class Structures_Form_Rule_Base implements Structures_Form_RuleInterface {

    /**
     * The error message to be returned if the element does not validate.
     *
     * The values in {curly braces} will be substituted for values from the
     * substitiution array.
     *
     * @access protected
     * @var    string
     */
    protected $errorMessage = '{elementName} is not valid.';
    
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

    /**
     * Adds a new subsitution to the substitution array.
     * 
     * @access public
     * @param  string $key   The substituion key.
     * @param  string $value The substitution value.
     * @return void
     */
    public function addSubstitution($key, $value)
    {
        $this->substitutions[$key] = $value;
    }

    /**
     * Removes a subsitution from the substitution array.
     * 
     * @access public
     * @param  string $key   The substituion key.
     * @return void
     */
    public function removeSubstitution($key)
    {
        if (array_key_exists($key, $this->substitutions)) {
            unset($this->substitutions[$key]);
        }
    }

    /**
     * Generates an error string based on the class' error string and the
     * class' substitution array.
     *
     * @access protected
     * @param  object    $element A Structures_Form element.
     * @return string
     */
    protected function generateErrorString(Structures_Form_ElementInterface $element)
    {
        // Get values for the substitution.
        $values = array();
        foreach (array_values($this->substitutions) as $method) {
            if (method_exists($element, $method)) {
                $values[] = $element->$method();
            } else {
                $values[] = $method;
            }
        }
   
        // Return the error string with the substitutions made.
        return str_replace(array_keys($this->substitutions),
                           $values,
                           $this->errorMessage
                           );
    }
}
?>