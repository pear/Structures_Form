<?php
/**
 * An interface to enforce consistency among all group elements used in a
 * Structures_Form.
 *
 * In HTML_QuickForm, all form rules extend a base rule class. While this is
 * technically possible for Structures_Form, it prevents any other class from
 * being extended to create a rule class. For example, an XML validation rule
 * may be needed. Instead of wrapping some other XML class, a Structures_Form
 * rule can extend that class and maintain the same API without the need for
 * lots of developer work. 
 *
 * The constructor for the rule should define all the needed arguments. It is
 * not possible to define the constructor here because the number of arguments
 * may vary depending on what the rule needs.
 *
 * This interface defines method needed to validate values.
 *
 * A note contributors: I welcome contributions from others but I will not 
 * include any classes in the package that are not accompanied by a complete 
 * set of PHPUnit2 unit tests. Also, I am a stickler for documentation. Please
 * make sure that your contributions are fully documented. Docblocks are not 
 * enough. There must be inline documentation. Please see Structures/Form.php
 * for an example of what I mean by fully documented. Plus as you can see the
 * interface is very simple.
 *
 * @author    Scott Mattocks
 * @package   Structures_Form
 * @license   PHP License
 * @version   @version@
 * @copyright Copyright 2006 Scott Mattocks
 */
interface Structures_Form_RuleInterface {

    /**
     * Validates a value against the rule.
     *
     * If the value is not valid according to the rule, this method should
     * return an error message. It is up to the rule to make sure the message
     * is meaningful.
     *
     * @access public
     * @param  object $element The form element.
     * @return mixed  true if the value is ok, an error message otherwise. 
     */
    public function validate(Structures_Form_ElementInterface $element);
}
?>