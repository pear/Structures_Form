<?php
/**
 * An interface to enforce consistency among all group elements used in a
 * Structures_Form.
 *
 * In HTML_QuickForm, all form elements extend a common base class. In PHP-GTK
 * 2 this is not possible because the elements need to be added to containes. 
 * To be added to a container a class must extends GtkWidget. While it is not
 * possible to force all form elements to inherit from the same base class we
 * can use an interface to enforce some consistency.
 *
 * A group is a way to logically and visually group elements together. It must
 * be able to add, remove, reorder and return the elements in the group. It
 * must also be able to return the value for one or all of elements in the
 * group. When passed an array of values, the group object should set the value
 * for each element in the array.
 *
 * Groups must also implement the Structures_Form_ElementInterface.
 *
 * NOTE: This interface provides new meanings for some of the methods defined
 *       by Structures_Form_ElementInterface. Make sure that any group that
 *       implements this interface, implements the correct definition of the
 *       method! The methods are given new meanings but not new names to make
 *       it easier for Structures_Form to get and set values.
 *
 * A note contributors: I welcome contributions from others but I will not 
 * include any classes in the package that are not accompanied by a complete 
 * set of PHPUnit2 unit tests. Also, I am a stickler for documentation. Please
 * make sure that your contributions are fully documented. Docblocks are not 
 * enough. There must be inline documentation. Please see Structures/Form.php
 * for an example of what I mean by fully documented.
 *
 * @author    Scott Mattocks
 * @package   Structures_Form
 * @license   PHP License
 * @version   @version@
 * @copyright Copyright 2006 Scott Mattocks
 */
interface Structures_Form_GroupInterface {

    /**
     * Returns an element from a group.
     *
     * This method should return the element with the given name. If there is 
     * no element with the given name, this method should return false.
     *
     * @access public
     * @param  string $name The element name.
     * @return object The element object.
     */
    public function getElement($name);

    /**
     * Returns an array containing all elements in the group.
     *
     * The array should be of the form: array(<name> => <element>)
     *
     * @access public
     * @return array
     */
    public function getAllElements();

    /**
     * Returns whether or not an element with the given name exists in the
     * group.
     *
     * @access public
     * @param  string  $name The name of the element.
     * @return boolean true if the element is a member of the group.
     */
    public function elementExists($name);

    /**
     * Adds an element to the group.
     *
     * @access public
     * @param  object  $element An element object.
     * @return boolean true if the element was added.
     */
    public function addElement($element);

    /**
     * Removes an element from the group.
     *
     * This method should fail gracefully (no errors or notices) if the element
     * is not part of the group.
     *
     * @access public
     * @param  object $element The element object to remove.
     * @return void
     */
    public function removeElement($element);

    // NOTE: The meaning of these methods has been redefined for groups!!!

    /**
     * Sets the values of the group elements.
     *
     * This method should set the value of each element in the group. This
     * should be done by calling the element's setValue() method.
     *
     * @access public
     * @param  array   $values The values to set for the group elements.
     * @return boolean true if the value was changed.
     */
    //public function setValue($values);

    /**
     * Returns an array of the group elements' values.
     *
     * This method should return an array of the form: array(<name> => <value>)
     * The name and value should be obtained by calling getName() and
     * getValue() respectively for each member of the group.
     *
     * @access public
     * @return array
     */
    //public function getValue();

    /**
     * Clears the current values of the group elements.
     *
     * This method should clear the current value of each element in the group.
     * If not all values can be cleared, this method should return false. To
     * clear the elements' values, clearValue() should be called on each 
     * element.
     *
     * @access public
     * @return boolean true if the value was cleared.
     */
    //public function clearValue();

    /**
     * Freezes the elements in the group so that its value may not be changed.
     *
     * This method should call freeze() on every member of the group.
     *
     * To make life easier down the road this method should also call
     * set_data('frozen', true);
     *
     * @access public
     * @return void
     */
    //public function freeze();

    /**
     * Unfreezes the element so that its value may not be changed.
     *
     * This method should call unfreeze() on every member of the group.
     *
     * To make life easier down the road this method should also call
     * set_data('frozen', false);
     *
     * @access public
     * @return void
     */
    //public function unfreeze();

    /**
     * Returns whether or not the group is currently frozen.
     * 
     * This method should just return the value from get_data('frozen')
     *
     * @access public
     * @return boolean
     */
    //public function isFrozen();

    /**
     * Sets the label that identifies the group.
     *
     * @access public
     * @param  string $label A label identifying the group.
     * @return void
     */
    //public function setLabel($label);

    /**
     * Returns the label that identifies the group.
     *
     * @access public
     * @return string
     */
    //public function getLabel();

    /**
     * Sets whether or not the group is required. Requiring a group requires
     * all elements in the group.
     *
     * This method should call setRequired($required) on all members of the
     * group.
     *
     * @access public
     * @param  boolean $required
     * @return void
     */
    //public function setRequired($required);

    /**
     * Returns whether or not an element is required.
     * 
     * This method should simply return the value of get_data('required');
     *
     * @access public
     * @return boolean
     */
    //public function getRequired();
}
?>