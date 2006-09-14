<?php
/**
 * A rule designed to be used with Structures_Form. This rule makes sure that
 * an element has a value that matches the given regular expression.
 *
 * This class uses Perl compatible regular expressions.
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
class Structures_Form_Rule_Regex extends Structures_Form_Rule_Base {

    /**
     * The error message to be returned if the element does not validate.
     *
     * The values in {curly braces} will be substituted for values from the
     * substitiution array.
     *
     * @access protected
     * @var    string
     */
    protected $errorMessage = '{elementName} must match {regex}.';
    
    /**
     * The regular expression to validate against.
     * 
     * Must be a PCRE.
     *
     * @access protected
     * @var    string
     */
    protected $regex;

    /**
     * Constructor.
     *
     * This rule does not require any additional arguments, but may accept two
     * optional arguments.
     *
     * @access public
     * @param  string $regex         A regular expression to validate against.
     * @param  string $errorMessage  Optional error message.
     * @param  array  $substitutions Optional substitution array.
     * @return void
     */
    public function __construct($regex, $errorMessage = null,
                                $substitutions = null
                                )
    {
        // Call the parent constructor.
        parent::__construct($errorMessage, $substitutions);

        // Set the regex.
        $this->setRegex($regex);

        // Add the regex to the substitutions array.
        $this->addSubstitution('{regex}', $this->regex);
    }

    /**
     * Sets the regular expression to be used for validation.
     *
     * Must be a PCRE.
     *
     * @access public
     * @param  string $regex The regex for validation.
     * @return void
     */
    public function setRegex($regex)
    {
        // Set the regex.
        $this->regex = $regex;
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
        if (preg_match($this->regex, $value)) {
            return true;
        }

        // The validation failed. Create an error message.
        return $this->generateErrorString($element);
    }
}
?>