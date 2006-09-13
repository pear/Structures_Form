<?php
/**
 * An interface to enforce consistency among all elements used in a
 * Structures_Form.
 *
 * In HTML_QuickForm, all form elements extend a common base class. In PHP-GTK
 * 2 this is not possible because the elements need to be added to containes. 
 * To be added to a container a class must extends GtkWidget. While it is not
 * possible to force all form elements to inherit from the same base class we
 * can use an interface to enforce some consistency.
 *
 * This interface defines methods to get and set values as well as retrieve
 * information about an element. Individual element classes must implement at 
 * least the methods here but are free to implement any other methods needed.
 *
 * The element constructor (which cannot be defined here) must always expect 
 * the first argument to be the form that created the element. It does not 
 * necessarily have to use that value but it should expect it.
 *
 * Some of the comments here are specific to PHP-GTK 2 applications.
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
interface Structures_Form_ElementInterface {

    /**
     * Sets an element's value.
     *
     * This method should set the value of the widget not just set some data
     * that is retrieved later. If the widget is a GtkEntry, this method should
     * call set_text(). If the widget is a GtkComboBox, this method should set
     * the active row.
     *
     * @access public
     * @param  mixed   $value The value to set for the form element.
     * @return boolean true if the value was changed.
     */
    public function setValue($value);

    /**
     * Returns element's value.
     *
     * This method should return the widget's value not just some data from the
     * widget (i.e. set with set_data()). For example if the widget is a 
     * GtkEntry, this method should call get_text(). If the widget is a
     * GtkComboBox, this method should return the value of the column
     * identified when the element was constructed for the given row.
     *
     * @access public
     * @return mixed
     */
    public function getValue();

    /**
     * Clears the current value of the element.
     *
     * This method should clear the current value if possible. For example, if
     * the widget is a GtkEntry, this method should pass null to set_text(). If
     * the value could not be cleared for some reason (the item is frozen or it
     * is not possible to clear the value (selection type = browse)) this
     * method should return false.
     *
     * @access public
     * @return boolean true if the value was cleared.
     */
    public function clearValue();

    /**
     * Returns the element type.
     * 
     * This method must return a string identifying the element type, such as 
     * text, password, submit, etc.
     *
     * @access public
     * @return string The element type.
     */
    public function getType();

    /**
     * Sets the element name.
     *
     * This method exists to maintain consistency in the interface. It should
     * simply call set_name which is a GtkWidget method and should be avialable
     * to all elements.
     *
     * @access public
     * @param  string $name
     * @return void
     */
    public function setName($name);

    /**
     * Returns the element's name.
     *
     * This method exists to maintain consistency in the interface. It should
     * simply call get_name which is a GtkWidget method and should be available
     * to all elements.
     *
     * @access public
     * @return string
     */
    public function getName();

    /**
     * Freezes the element so that its value may not be changed.
     *
     * Again this method exists only to maintain consistency in the interface.
     * It should just pass false to set_sensitive().
     *
     * To make life easier down the road this method should also call
     * set_data('frozen', true);
     *
     * @access public
     * @return void
     */
    public function freeze();

    /**
     * Unfreezes the element so that its value may not be changed.
     *
     * Again this method exists only to maintain consistency in the interface.
     * It should just pass true to set_sensitive().
     *
     * To make life easier down the road this method should also call
     * set_data('frozen', false);
     *
     * @access public
     * @return void
     */
    public function unfreeze();

    /**
     * Returns whether or not the element is currently frozen.
     * 
     * This method should just return the value from get_data('frozen')
     *
     * @access public
     * @return boolean
     */
    public function isFrozen();

    /**
     * Sets the label that identifies the element.
     *
     * @access public
     * @param  string $label
     * @return void
     */
    public function setLabel($label);

    /**
     * Returns the GtkLabel that identifies the element.
     *
     * @access public
     * @return string
     */
    public function getLabel();

    /**
     * Adds an event handler for the element.
     *
     * @access public
     * @param  string  $eventName The name of the event.
     * @param  mixed   $callback  The callback to call when the event occurs.
     * @return integer An identifier for the callback.
     */
    public function addEventHandler($eventName, $callback);
}
?>