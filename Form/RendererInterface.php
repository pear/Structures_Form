<?php
/**
 * An interface to enforce consistency among all group elements used in a
 * Structures_Form.
 *
 * In HTML_QuickForm, all form renderers can extend one base class. In PHP-GTK
 * 2 this is not possible. To maintain consistency, all renderers must
 * implement this interface. 
 *
 * This interface defines methods needed to collect and display form 
 * information including errors. 
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
interface Structures_Form_RendererInterface {

    /**
     * Sets the form.
     *
     * @access public
     * @param  object $form The form.
     * @return void
     */
    public function setForm(Structures_Form $form);

    /**
     * Sets the elements that make up the form.
     *
     * The array passed in will be of the form: array(<name> => <element>)
     *
     * @access public
     * @param  array  $elements The elements that make up the form.
     * @return void
     */
    public function setElements($elements);

    /**
     * Sets the errors to be displayed.
     *
     * @access public
     * @param  array  $errors An array of error strings.
     * @return void
     */
    public function setErrors($errors);

    /**
     * Returns the rendered form.
     *
     * This method should return something inline with the intent of the
     * renderer even if there are no elements in the form. For example, if the
     * renderer is a GtkTable renderer, this method should return a GtkTable
     * no matter what. Of course, it may throw an exception if needed but 
     * should not return anything other than a GtkTable. Not even null or void.
     *
     * @access public
     * @return mixed  The rendered form.
     */
    public function render();

    /**
     * Sets the string to be used as the note indicating what the required 
     * symbol means.
     *
     * The required note does not include the required symbol. It is up to the
     * renderer to append or prepend the required symbol in a way that makes
     * sense for the rendered output.
     *
     * The required note is controlled by the form to maintain consistency when
     * a single form is rendered in different ways.
     * 
     * @access public
     * @param  string $note The required note.
     * @return void
     */
    public function setRequiredNote($note);

    /**
     * Sets the string to be used as the note indicating what the required 
     * symbol means.
     *
     * The required note does not include the required symbol. It is up to the
     * renderer to append or prepend the required symbol in a way that makes
     * sense for the rendered output.
     *
     * The required symbol is controlled by the form to maintain consistency
     * when a single form is rendered in different ways.
     * 
     * @access public
     * @param  string $symbol The required symbol.
     * @return void
     */
    public function setRequiredSymbol($symbol);

}
?>