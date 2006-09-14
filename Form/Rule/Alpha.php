<?php
/**
 * A rule designed to be used with Structures_Form. This rule makes sure that
 * an element has a value that consists of only letters or nothing at all.
 *
 * Empty values will pass validation of this rule. If you don't want the value
 * to be empty make sure to also apply the Required rule.
 *
 * This class uses Perl compatible regular expressions.
 *
 * This class extends Structures_Form_Rule_Regex.
 * This class implement Structures_Form_RuleInterface.
 *
 * @author    Scott Mattocks
 * @package   Structures_Form
 * @license   PHP License
 * @version   @version@
 * @copyright Copyright 2006 Scott Mattocks
 */
require_once 'Structures/Form/Rule/Regex.php';
class Structures_Form_Rule_Alpha extends Structures_Form_Rule_Regex {

    /**
     * The error message to be returned if the element does not validate.
     *
     * The values in {curly braces} will be substituted for values from the
     * substitiution array.
     *
     * @access protected
     * @var    string
     */
    protected $errorMessage = '{elementName} may only contain letters.';
    
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
        // Create a numeric regex.
        $regex = '/^[a-zA-Z]*$/';

        // Call the parent constructor.
        parent::__construct($regex, $errorMessage, $substitutions);
    }
}
?>