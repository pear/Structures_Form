<?php
/**
 * An interface to make loading element sets consistent.
 *
 * @author    Scott Mattocks
 * @package   Structures_Form
 * @license   PHP License
 * @version   @version@
 * @copyright Copyright 2006 Scott Mattocks
 */
interface Structures_Form_ElementSetInterface {

    /**
     * Returns an array of element names, classes and files.
     *
     * The return array should look like:
     *   // Create an array elements.
     *   $elements = array(
     *                     array('text',
     *                           'Structures_Form_Element_GtkText',
     *                           'Structures/Form/Element/GtkText.php'
     *                           ),
     *                     array('password',
     *                           'Structures_Form_Element_GtkPassword',
     *                           'Structures/Form/Element/GtkPassword.php'
     *                           ),
     *                     array('submit',
     *                           'Structures_Form_Element_GtkSubmit',
     *                           'Structures/Form/Element/GtkSubmit.php'
     *                                  ),
     *                     array('cancel',
     *                           'Structures_Form_Element_GtkCancel',
     *                           'Structures/Form/Element/GtkCancel.php'
     *                           )
     *                     );
     *
     * @static
     * @access public
     * @return array
     */
    public function getElementSet();

    /**
     * Returns an array of data defining the default renderer.
     *
     * The return array should look like:
     *        return  array('class' => 'Structures_Form_Renderer_Gtk2Table',
     *                      'path'  => 'Structures/Form/Renderer/Gtk2Table.php'
     *                      );
     *
     * @static
     * @public
     * @return array array(class => <classname>, path => <path>);
     */
    public function getDefaultRenderer();
}
?>