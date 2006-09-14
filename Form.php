<?php
/**
 * A package designed to make creating input forms easy for PHP-GTK 2 packages
 * and applications.
 *
 * This package was designed to make it easy to add forms to PHP-GTK 2 packages
 * and applications. The package is modeled after HTML_QuickForm but cannot
 * extend HTML_QuickForm because HTML_QuickForm is tied too tightly to HTML.
 *
 * PHP-GTK 2 forms are very similar to HTML forms. They consist of a handful of
 * different input types and the data is submitted all together when a user 
 * clicks the submit button. The difference is how the user values are
 * submitted and stored. HTML forms submit data in an HTTP request using either
 * GET or POST. PHP-GTK 2 forms call a callback which is then responsible for
 * retrieving the values. 
 *
 * In HTML_QuickForm, all form elements extend a common base class. In PHP-GTK
 * 2 this is not possible because the elements need to be added to containes. 
 * To be added to a container a class must extends GtkWidget. While it is not
 * possible to force all form elements to inherit from the same base class we
 * can use an interface to enforce some consistency.
 *
 * In order to make it possible to create elements on the fly without
 * restricting the number of constructor arguments this package must use the
 * Reflection API. This means that the user must have reflection enabled.
 *
 * @todo At least one group class.
 * @todo Element classes (Done: Text, Password, Submit, Cancel)
 * @todo Rule classes    (Done: Required)
 *
 * @todo Unit tests
 *
 * @author    Scott Mattocks
 * @package   Structures_Form
 * @license   PHP License
 * @version   @version@
 * @copyright Copyright 2006 Scott Mattocks
 */
class Structures_Form {

    // {{{ Constants
    
    /**
     * Error constants.
     *
     * @const
     */
    const ERROR_ADD_ELEMENT_DOUBLEADD          = -1;
    const ERROR_ADD_RULE_DOUBLEADD             = -2;

    const ERROR_UNREGISTERED_ELEMENT           = -3;
    const ERROR_UNREGISTERED_RULE              = -4;
    const ERROR_REGISTRATION_ELEMENT           = -5;
    const ERROR_REGISTRATION_ELEMENT_DOUBLEREG = -6;
    const ERROR_REGISTRATION_RULE              = -7;
    const ERROR_REGISTRATION_RULE_DOUBLEREG    = -8;

    const ERROR_UNREGISTRATION_ELEMENTINUSE    = -9;
    const ERROR_UNREGISTRATION_ELEMENT         = -10;
    const ERROR_UNREGISTRATION_RULEINUSE       = -11;
    const ERROR_UNREGISTRATION_RULE            = -12;

    const ERROR_LACKSINTERFACE_ELEMENT         = -13;
    const ERROR_LACKSINTERFACE_ELEMENTSET      = -14;
    const ERROR_LACKSINTERFACE_RULE            = -15;
    const ERROR_LACKSINTERFACE_GROUP           = -16;
    const ERROR_LACKSINTERFACE_RENDERER        = -17;

    const ERROR_NONEXISTENT_CLASS              = -18;
    const ERROR_NONEXISTENT_FILE               = -19;
    const ERROR_NONEXISTENT_ELEMENT            = -20;
    const ERROR_NONEXISTENT_CALLBACK           = -21;
    const ERROR_RENDER_FAILURE                 = -22;

    /**
     * ElementSet constants.
     *
     * @const
     */
    const ELEMENTSET_PATH   = 'Structures/Form/Element/';
    const ELEMENTSET_PREFIX = 'Structures_Form_Element_';

    /**
     * Defaults
     *
     * @const
     */
    const DEFAULT_ERRORCALLBACK = 'defaultErrorCallback';

    /**
     * The name of the rule defining required elements.
     *
     * @const
     */
    const REQUIRED_RULE = 'required';

    // }}}
    // {{{ Member Variables

    /**
     * The form elements.
     *
     * @access protected
     * @var    array
     */
    protected $elements = array();

    /**
     * The rules to be applied to each element.
     *
     * @access protected
     * @var    array
     */
    protected $rules = array();

    /**
     * The registered element types.
     *
     * @access protected
     * @var    array
     */
    protected $registeredElements = array();

    /**
     * The registered rules.
     *
     * @access protected
     * @var    array
     */
    protected $registeredRules = array();

    /**
     * The method to call after values are collected from a submitted form.
     *
     * @access protected
     * @var    mixed     string or array
     */
    protected $submitCallback;

    /**
     * The callback for error handling.
     *
     * @access protected
     * @var    mixed     string or array
     */
    protected $errorCallback;

    /**
     * Send the widgets to the callback instead of the values.
     *
     * @access protected
     * @var    boolean
     */
    protected $submitObjects;

    /**
     * The default values for the form elements.
     * 
     * @access protected
     * @var    array
     */
    protected $defaults = array();

    /**
     * A note identifying the required field marker.
     * The renderer should prepend the required symbol to the note.
     *
     * @access protected
     * @var    string
     */
    protected $requiredNote = ' denotes required field';

    /**
     * A symbol identifying the required fields.
     * The renderer should provide some sort of formatting to make the symbol
     * standout.
     *
     * @access protected
     * @var    string
     */
    protected $requiredSymbol = '*';

    /**
     * The default renderer class defined by the element set.
     *
     * @access protected
     * @var    string
     */
    protected $defaultRenderer;

    /**
     * The path to the default renderer defined by the element set.
     *
     * @access protected
     * @var    string
     */
    protected $defaultRendererPath;

    /**
     * The object responsible for displaying the form.
     *
     * @access public
     * @var    object 
     */
    public $renderer;

    /**
     * The rendered form.
     * 
     * @access protected
     * @var    mixed
     */
    protected $renderedForm;

    /**
     * An array relating error codes to error messages.
     *
     * @static
     * @access protected
     * @var    array
     */
    protected static $errorMsgs =
        array(
              Structures_Form::ERROR_ADD_ELEMENT_DOUBLEADD =>
              'Cannot add the element. The element or element name already exists in the form.',
              Structures_Form::ERROR_ADD_RULE_DOUBLEADD =>
              'Cannot create the rule. A rule of this type already exists in the form',
              Structures_Form::ERROR_UNREGISTERED_ELEMENT =>
              'Cannot add the element. The type is not registered.',
              Structures_Form::ERROR_UNREGISTERED_RULE =>
              'Cannot add the rule. The type is not registered.',
              Structures_Form::ERROR_REGISTRATION_ELEMENT =>
              'An error occured while trying to register the element.',
              Structures_Form::ERROR_REGISTRATION_ELEMENT_DOUBLEREG =>
              'The element could not be registered. An element of this type or with the same name is already registered.',
              Structures_Form::ERROR_REGISTRATION_RULE =>
              'An error occured while trying to register the rule.',
              Structures_Form::ERROR_REGISTRATION_RULE_DOUBLEREG =>
              'The rule could not be registered. A rule of this type or with the same name is already registered.',
              Structures_Form::ERROR_UNREGISTRATION_ELEMENTINUSE =>
              'The element could not be unregistered. It is currently in use.',
              Structures_Form::ERROR_UNREGISTRATION_ELEMENT =>
              'An error occured while trying to unregister the element.',
              Structures_Form::ERROR_UNREGISTRATION_RULEINUSE =>
              'The rule could not be unregistered. It is currently in use.',
              Structures_Form::ERROR_UNREGISTRATION_RULE =>
              'An error occured while trying to unregister the rule.',
              Structures_Form::ERROR_LACKSINTERFACE_ELEMENT =>
              'The element could not be added to the form. It does not implement the needed interface.',
              Structures_Form::ERROR_LACKSINTERFACE_ELEMENTSET =>
              'The element set could not be registered. It does not implement the needed interface.',
              Structures_Form::ERROR_LACKSINTERFACE_RULE =>
              'The rule could not be added to the form. It does not implement the needed interface.',
              Structures_Form::ERROR_LACKSINTERFACE_GROUP =>
              'The element group could not be added. It does not implement the needed interface.',
              Structures_Form::ERROR_LACKSINTERFACE_RENDERER =>
              'The renderer could not be set. It does not implement the needed interface.',
              Structures_Form::ERROR_NONEXISTENT_CLASS =>
              'The class does not exist.',
              Structures_Form::ERROR_NONEXISTENT_FILE => 
              'The file could not be read or does not exist.',
              Structures_Form::ERROR_NONEXISTENT_ELEMENT =>
              'The element does not exist.',
              Structures_Form::ERROR_NONEXISTENT_CALLBACK =>
              'The callback does not exist.',
              Structures_Form::ERROR_RENDER_FAILURE =>
              'An error occurred while trying to render the form.'
              );
    
    // }}}
    // {{{ Base 

    /**
     * Constructor. Sets the callbacks then registers any default element or
     * rule types.
     *
     * A callback must be passed in so that the form knows what to do with the
     * values that are collected by the form. Optionally, the user may tell the
     * form to pass the widgets themselves, not the values, to the callback.
     * The user may also define an error callback, which will be called if any
     * rules are viloated. If no error callback is defined, the default 
     * callback ($this->defaultErrorCallback()) will be used. The default just
     * passes the errors on to the renderer.
     *
     * @access public
     * @param  mixed   $callback      The method to call when the form is
     *                                submitted.
     * @param  boolean $submitObjects Send the widgets to the callback not the
     *                                values.
     * @param  mixed   $errorCallback A callback for handling errors.
     * @return void
     * @throws Structures_Form_Exception
     */
    public function __construct($callback,
                                $submitObjects = false,
                                $errorCallback = null
                                )
    {
        // Make sure the callback exists.
        if (!is_callable($callback)) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_NONEXISTENT_CALLBACK),
                                                self::ERROR_NONEXISTENT_CALLBACK
                                                );
        }

        // Set the submit callback.
        $this->submitCallback = $callback;

        // Set the error handling callback.
        if (!is_callable($errorCallback)) {
            $errorCallback = array($this, self::DEFAULT_ERRORCALLBACK);
        }
        $this->setErrorCallback($errorCallback);

        // Set whether or not we want to send the objects.
        $this->submitObjects = $submitObjects;

        // Register default rules.
        $this->registerDefaultRules();
    }

    /**
     * Sets the error callback.
     *
     * @access public
     * @param  mixed  $callback A string or array.
     * @return void
     */
    public function setErrorCallback($callback)
    {
        $this->errorCallback = $callback;
    }

    /**
     * Returns the submit callback.
     *
     * @access public
     * @return mixed  A string or array.
     */
    public function getSubmitCallback()
    {
        return $this->submitCallback;
    }

    /**
     * Returns the error callback.
     *
     * @access public
     * @return mixed  A string or array.
     */
    public function getErrorCallback()
    {
        return $this->errorCallback;
    }

    /**
     * Registers a set of elements.
     *
     * Only elements types that are registered with Structures_Form may be
     * added to the form. This helps to ensure that the elements implement the
     * proper interface. 
     *
     * Element sets must also define a default renderer.
     *
     * To avoid overhead, type registration is not checked until the type is
     * added to the form.
     *
     * registerElement may throw an exception that will be passed along to the
     * calling code.
     *
     * @access public
     * @param  string  $elementSet The name of an element set.
     * @return boolean true if the element set was registered.
     * @throws Structures_Form_Exception
     */
    public function registerElementSet($elementSet)
    {
        // Try to get the element set.
        if (!$this->isIncludable(self::ELEMENTSET_PATH . $elementSet . '.php')) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_NONEXISTENT_FILE),
                                                self::ERROR_NONEXISTENT_FILE
                                                );
        }

        // Include the element set file.
        require_once self::ELEMENTSET_PATH . $elementSet . '.php';

        // Get the correct element set classname.
        $class = self::ELEMENTSET_PREFIX . $elementSet;

        // Instantiate the calss. The class must be instantiated to use
        // instanceof. If there was only one method it might not be worth 
        // instantiating the class but since we have two methods we might as
        // well. I know the methods can be called statically but this is
        // easier.
        $eSet = new $class();

        // Check to see if the correct interface is implemented
        require_once 'Structures/Form/ElementSetInterface.php';
        if (!($eSet instanceof Structures_Form_ElementSetInterface)) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_LACKSINTERFACE_ELEMENTSET),
                                                self::ERROR_LACKSINTERFACE_ELEMENTSET
                                                );
        }

        // Register the element set.
        foreach ($eSet->getElementSet() as $element) {
            $this->registerElement($element[0], $element[1], $element[2]);
        }
        
        // Set the default renderer.
        $renderer = $eSet->getDefaultRenderer();
        $this->defaultRenderer     = $renderer['class'];
        $this->defaultRendererPath = $renderer['path'];

        return true;
    }

    /**
     * Registers the default rule types.
     *
     * Rule objects are used to validate the values of the form elements before
     * they are submitted to the callback. If an elements value violates a rule
     * a rule callback will be called which allows the user to handle errors in
     * their own way. All violations are collected on each submit and then
     * passed off to the rule callback.
     *
     * Only registered rules may be applied to a form element.
     * 
     * All registered rules must implement the Structures_Form_RuleInterface.
     *
     * To avoid overhead, a rule is not checked until it is applied to a form
     * element.
     *
     * @access protected
     * @return void
     */
    protected function registerDefaultRules()
    {
        // Create an array of the default rules.
        $defaultRules = array(
                              array(self::REQUIRED_RULE,
                                    'Structures_Form_Rule_Required',
                                    'Structures/Form/Rule/Required.php'
                                    ),
                              array('regex',
                                    'Structures_Form_Rule_Regex',
                                    'Structures/Form/Rule/Regex.php'
                                    ),                              
                              array('alpha',
                                    'Structures_Form_Rule_Alpha',
                                    'Structures/Form/Rule/Alpha.php'
                                    ),                              
                              array('numeric',
                                    'Structures_Form_Rule_Numeric',
                                    'Structures/Form/Rule/Numeric.php'
                                    ),                              
                              array('numericrange',
                                    'Structures_Form_Rule_NumericRange',
                                    'Structures/Form/Rule/NumericRange.php'
                                    ),                              
                              array('alphanumeric',
                                    'Structures_Form_Rule_AlphaNumeric',
                                    'Structures/Form/Rule/AlphaNumeric.php'
                                    )
                              );

        // Register each rule.
        foreach ($defaultRules as $rule) {
            $this->registerRule($rule[0], $rule[1], $rule[2]);
        }
    }

    /**
     * Returns an error message for the given error code.
     *
     * @static
     * @access public
     * @param  integer $errorCode The error code to get a message for.
     * @return string  An error message.
     */
    public static function getErrorMessage($errorCode)
    {
        return self::$errorMsgs[$errorCode];
    }

    // }}}
    // {{{ Elements

    /**
     * Creates and adds an element to the form.
     *
     * Only elements types that are registered with Structures_Form may be added
     * to the form. This helps to ensure that the elements implement the proper
     * interface. 
     *
     * To avoid overhead, type registration is not checked until the type is
     * added to the form.
     *
     * This method passes any extra arguments on to the element constructor.
     * This allows one method to be used for creating many types of elements.
     * The exact number and type of arguments that is passed to this method
     * varies depending on the type of element to be created. If the wrong
     * number or type of arguments is passed, the element constructor should
     * throw an exception.
     *
     * An exception may be thrown by createElement or addElementObject. If so
     * it will be bubble up through this method.
     *
     * @access public
     * @param  string $type The element type.
     * @param  string $name The element name.
     * @return object A Structures_Form element
     * @throws Structures_Form_Exception
     */
    public function addElement($type, $name)
    {
        // Freak out if the element name is already in use.
        if ($this->elementExists($name)) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_ADD_ELEMENT_DOUBLEADD),
                                                self::ERROR_ADD_ELEMENT_DOUBLEADD
                                                );
        }

        // Create the element.
        $args    = func_get_args();
        $element = call_user_func_array(array($this, 'createElement'), $args);

        // Add the element to the form.
        return $this->addElementObject($element);
    }
    
    /**
     * Inserts an element object at the given location.
     *
     * Only elements types that are registered with Structures_Form may be added
     * to the form. This helps to ensure that the elements implement the proper
     * interface. 
     *
     * To avoid overhead, type registration is not checked until the type is
     * added to the form.
     *
     * @access public
     * @param  object $element The element object.
     * @return object The inserted object.
     */
    public function insertElement($element, $position = -1)
    {
        // Make sure the type is registered. 
        if (!$this->isElementRegistered($element->getType())) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_UNREGISTERED_ELEMENT),
                                                self::ERROR_UNREGISTERED_ELEMENT
                                                );
        }
        
        // Make sure the element implements the needed interface.
        require_once 'Structures/Form/ElementInterface.php';
        if (!$element instanceof Structures_Form_ElementInterface) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_LACKSINTERFACE_ELEMENT),
                                                self::ERROR_LACKSINTERFACE_ELEMENT
                                                );
        }

        // Make sure an element with this name isn't already in the form.
        if ($this->elementExists($element->getName())) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_ADD_ELEMENT_DOUBLEADD),
                                                self::ERROR_ADD_ELEMENT_DOUBLEADD
                                                );
        }

        // Make sure this specific element was not already added.
        foreach ($this->getAllElements() as $name => $elem) {
            if ($elem === $element) {
                require_once 'Structures/Form/Exception.php';
                throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_ADD_ELEMENT_DOUBLEADD),
                                                    self::ERROR_ADD_ELEMENT_DOUBLEADD
                                                    );
            }
        }

        // Add the element to the elements array.
        // Go through the current elements until we find the right position.
        $elements = array();
        $i        = 0;
        foreach ($this->getAllElements() as $name => $elem) {
            if ($i++ == $position) {
                $elements[$element->getName()] = $element;
            }
            $elements[$name] = $elem;
        }

        // If the element was not inserted, add the element to the end.
        if (!$this->elementExists($element->getName())) {
            $elements[$element->getName()] = $element;
        }

        // Update the elements array.
        $this->elements = $elements;

        // Return the object.
        return $element;
    }

    /**
     * Moves an element object from its current position to the given position.
     *
     * If the position is greater than the number of elements, the element will
     * be added to the end of the form.
     *
     * @access public
     * @param  object  $element  The element to move.
     * @param  integer $position The new position.
     * @return object  The object that was moved.
     */
    public function moveElement($element, $position)
    {
        // First remove the element from the form.
        $this->removeElementObject($element);

        // Then insert it in the given position.
        return $this->insertElement($element, $position);
    }

    /**
     * Creates an element but does not add it to the form.
     * 
     * Only elements types that are registered with Structures_Form may be added
     * to the form. This helps to ensure that the elements implement the proper
     * interface. 
     *
     * To avoid overhead, type registration is not checked until the type is
     * added to the form.
     *
     * This method passes any extra arguments on to the element constructor.
     * This allows one method to be used for creating many types of elements.
     * The exact number and type of arguments that is passed to this method
     * varies depending on the type of element to be created. If the wrong
     * number or type of arguments is passed, the element constructor should
     * throw an exception.
     *
     * @access public
     * @param  string $type The element type.
     * @param  string $name The element name.
     * @return object A Structures_Form element
     * @throws Structures_Form_Exception
     */
    public function createElement($type, $name)
    {
        // Make sure the type is registered.
        if (!$this->isElementRegistered($type)) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_UNREGISTERED_ELEMENT),
                                                self::ERROR_UNREGISTERED_ELEMENT
                                                );
        }

        // Make sure an element with this name isn't already in the form.
        if ($this->elementExists($name)) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_ADD_ELEMENT_DOUBLEADD),
                                                self::ERROR_ADD_ELEMENT_DOUBLEADD
                                                );
        }

        // Include the element class file.
        require_once $this->registeredElements[$type]['path'];

        // Get the class name.
        $class = $this->registeredElements[$type]['class'];
        
        // Make sure the class exists.
        if (!class_exists($class)) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_REGISTRATION_ELEMENT),
                                                self::ERROR_NONEXISTENT_CLASS
                                                );
        }

        // Try to instantiate the class.
        // First add the form as the first argument.
        $args    = func_get_args();
        unset($args[0]);
        unset($args[1]);
        array_unshift($args, $this);

        // Instantiate the class.
        $obj =  call_user_func_array(array(new ReflectionClass($class),
                                           'newInstance'
                                           ),
                                     $args
                                     );

        // Set the name of the object.
        $obj->setName($name);
        
        // Return the object.
        return $obj;
    }

    /**
     * Removes an element from the form by name.
     *
     * @access public
     * @param  string $name
     * @return void
     */
    public function removeElement($name)
    {
        if ($this->elementExists($name)) {
            unset($this->elements[$name]);
        }
    }

    /**
     * Adds an element to the form.
     *
     * Only elements types that are registered with Structures_Form may be added
     * to the form. This helps to ensure that the elements implement the proper
     * interface. 
     *
     * To avoid overhead, type registration is not checked until the type is
     * added to the form.
     *
     * @access public
     * @param  object $element
     * @return object The added element.
     */
    public function addElementObject($element)
    {
        return $this->insertElement($element);
    }

    /**
     * Removes an element object from the form.
     *
     * This method is useful if an element has been added with the wrong name.
     * Trying to remove it by the name returned by getName() will not work so
     * you must remove it by the object.
     *
     * @access public
     * @param  object $element
     * @return void
     */
    public function removeElementObject($element)
    {
        // Loop through the current elements and see if the given element is
        // part of the form.
        foreach ($this->getAllElements() as $name => $elem) {
            if ($elem === $element) {
                // Remove by the name the form thinks the element has.
                $this->removeElement($name);
                return;
            }
        }
    }

    /**
     * Registers an element type with this form.
     *
     * Before an element type can be used in a form, it must be registered. 
     * Registering an element type helps to make sure that type names are
     * unique and that element classes implement the needed interface.
     *
     * To avoid overhead, registered classes are not checked until the type is
     * instantiated.
     *
     * @access public
     * @param  string  $name  The name to identify the element type.
     * @param  string  $class The name of the element class.
     * @param  string  $path  The path to the class definition.
     * @return boolean true if the class was registered
     * @throws Structures_Form_Exception
     */
    public function registerElement($name, $class, $path)
    {
        // Check to see if the element is already registered.
        if ($this->isElementRegistered($name, $class)) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_REGISTRATION_ELEMENT),
                                                self::ERROR_REGISTRATION_ELEMENT_DOUBLEREG
                                                );
        }
        
        // If the class is not already declared, make sure the path is
        // readable.
        if (!class_exists($class) && !$this->isIncludable($path)) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_REGISTRATION_ELEMENT),
                                                self::ERROR_NONEXISTENT_FILE
                                                );
        }        

        // Add the information to the registered elements array.
        $this->registeredElements[$name] = array('class' => $class,
                                                 'path'  => $path
                                                 );

        return $this->isElementRegistered($name);
    }

    /**
     * Returns whether or not a path is in the include path.
     *
     * @access public
     * @param  string  $path
     * @return boolean true if the path is in the include path.
     */
    public function isIncludable($path)
    {
        // Break up the include path and check to see if the path is readable.
        foreach (explode(PATH_SEPARATOR, get_include_path()) as $ip) {
            if (file_exists($ip . DIRECTORY_SEPARATOR . $path) &&
                is_readable($ip . DIRECTORY_SEPARATOR . $path)
                ) {
                return true;
            }
        }
        
        // If we got down here, the path is not readable from the include path.
        return false;
    }

    /**
     * Unregisters an element type with a form.
     *
     * An element type may not be unregistered if the form contains elements of
     * the given type.
     *
     * You may want to unregister a type to prevent a certain type from being
     * used in the form. For example if your app creates forms on the fly from
     * user supplied data, you can unregister the text type to prevent users
     * from creating a form with free form text entries.
     *
     * @access public
     * @param  string  $type The element type name.
     * @return boolean true if the type was unregistered
     * @throws Structures_Form_Exception
     */
    public function unRegisterElement($type)
    {
        // Check to make sure the element isn't in use.
        if (count($this->getElementsByType($type))) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_UNREGISTRATION_ELEMENT),
                                                self::ERROR_UNREGISTRATION_ELEMENTINUSE
                                                );
        }

        // Just unset the element in the registered elements array.
        unset($this->registeredElements[$type]);
        
        return !$this->isElementRegistered($type);
    }

    /**
     * Returns whether or not an element type is registered.
     * 
     * @access public
     * @param  string  $type  The name of the element type.
     * @param  string  $class Optional class name to check.
     * @return boolean true if the element type is registered.
     */
    public function isElementRegistered($type, $class = null)
    {
        // Check by name first. 
        if (isset($this->registeredElements[$type]) &&
            is_array($this->registeredElements[$type])
            ) {
            return true;
        } elseif (!is_null($class)) {
            // Check to see if the element is registered under a different
            // name.
            foreach ($this->registeredElements as $element) {
                // Use a case insensitive comparison.
                if (strcmp($element['class'], $class) === 0) {
                    return true;
                }
            }
        }
            
        // If we made it here, the class is not registered.
        return false;
    }

    /**
     * Returns all of the elements in the form of the given type.
     * 
     * @access public
     * @param  string $type The element type name.
     * @return array
     */
    public function getElementsByType($type)
    {
        // Loop through the elements and check their type.
        $elements = array();
        foreach ($this->getAllElements() as $name => $element) {
            if ($element->getType() == $type) {
                $elements[$name] = $element;
            }
        }

        return $elements;
    }

    /**
     * Returns the form element with the given name.
     *
     * @access public
     * @param  string $name The element name.
     * @return mixed  Either the object or false if the object was not found.
     */
    public function getElement($name)
    {
        if ($this->elementExists($name)) {
            return $this->elements[$name];
        } else {
            // Check the element groups.
            foreach ($this->getGroups() as $group) {
                if ($group->elementExists($name)) {
                    return $group->getElement($name);
                }
            }
            
            return false;
        }
    }

    /**
     * Returns an array of all elements.
     *
     * @access public
     * @return array  An array of elements array(<name> => <element>)
     */
    public function getAllElements()
    {
        return $this->elements;
    }

    /**
     * Returns whether or not the given element is associated with this form.
     *
     * @access public
     * @param  string  $name The name of the element to check.
     * @return boolean true if the element is part of this form.
     */
    public function elementExists($name)
    {
        return (isset($this->elements[$name]) && 
                is_object($this->elements[$name])
                );
    }

    /**
     * Returns whether or not the element object is associated with this form.
     *
     * @access public
     * @param  object  $element The element object.
     * @return boolean true if the element is part of the form.
     */
    public function elementObjectExists($element)
    {
        // Go through all of the elements and check to see if the given element
        // is in the array.
        foreach ($this->getAllElements() as $elem) {
            if ($elem === $element) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the current position of the element.
     *
     * @access public
     * @param  string  $name The element name.
     * @return integer The current position.
     */
    public function getPosition($name)
    {
        return array_search($name, array_keys($this->elements));
    }

    // }}}
    // {{{ Groups

    /**
     * Disbands a group. 
     *
     * This method disbands a group. It does not destroy the elements in the
     * group or even remove them from the form. It simply disbands the group
     * and makes the elements free floating individuals. The elements are 
     * inserted where the group used to be.
     *
     * This method returns an array containing the names of any elements that
     * were in the group before it was disbanded.
     *
     * If the group does not exist, this method will return an empty array.
     *
     * @access public
     * @param  string $group The name of the group to disband.
     * @return array
     */
    public function disbandGroup($group)
    {
        $elements = array();
        // Make sure the group exists.
        if ($this->groupExists($group)) {
            // Grab the group elements.
            $elements = $this->getGroupElements($group);

            // Grab the current group position.
            $pos = $this->getPosition($group);

            // Remove the elements from the group.
            foreach ($elements as $element) {
                $this->removeElementObjectFromGroup($element);
            }

            // Remove the group.
            $this->removeElement($group);

            // Reverse the elements and insert them into the position the group
            // used to occupy.
            $elements = array_reverse($elements);
            $this->insertElementObject($element, $pos);
        }

        return $elements;
    }

    /**
     * Returns whether or not the given group exists in this form.
     *
     * @access public
     * @param  string  $name The name of the group.
     * @return boolean true if the group exists.
     */
    public function groupExists($group)
    {
        // Check to see if an element with the group name exists.
        if (!$this->elementExists($group)) {
            return false;
        }

        // See if the element implements the group interface.
        $element = $this->getElement($group);
        
        return $this->isGroup($element);
    }

    /**
     * Returns whether or not the element is a group.
     *
     * @access public
     * @param  object  $element The element to check.
     * @return boolean true if the element is an group.
     */
    public function isGroup($element)
    {
        require_once 'Structures/Form/GroupInterface.php';
        return ($element instanceof Structures_Form_GroupInterface);
    }

    /**
     * Returns the group element.
     *
     * If the group does not exists, this method returns false.
     *
     * @access public
     * @param  string $group The name of the group.
     * @return array
     */
    public function getGroup($group)
    {
        if ($this->groupExists($group)) {
            return $this->getElement($group);
        } else {
            return false;
        }
    }

    /**
     * Returns an array containing all groups.
     *
     * @access public
     * @return array
     */
    public function getGroups()
    {
        // Loop through all the elements and figure out which ones are groups.
        $groups = array();
        foreach ($this->getAllElements() as $name => $element) {
            if ($this->isGroup($element)) {
                $groups[$name] = $element;
            }
        }

        return $groups;
    }

    /**
     * Adds an element to a group.
     * 
     * A group is just a way to logically and visually group form elements.
     * Elements can be added to or removed from a group without removing them
     * from the form all together. However, obviously if the element is removed
     * from the form, it will be removed from the group. If you readd the same
     * element object, it will NOT be readded to the group.
     *
     * Elements may only be part of one group. If the element is already part
     * of another group, it will not be added to this group.
     *
     * @access public
     * @param  string  $name  The name of the element.
     * @param  string  $group The name of the group.
     * @return boolean true if the element has been added to the group.
     */
    public function addElementToGroup($name, $group)
    {
        // Check to make sure the element exists.
        if (!$this->elementExists($name)) {
            return false;
        }

        // Make sure the group exists.
        if (!$this->groupExists($group)) {
            return false;
        }

        // Grab the element.
        $element = $this->getElement($name);

        // Add it by object.
        return $this->addElementObjectToGroup($element, $group);
    }

    /**
     * Removes an element from a group.
     *
     * A group is just a way to logically and visually group form elements.
     * Elements can be added to or removed from a group without removing them
     * from the form all together. However, obviously if the element is removed
     * from the form, it will be removed from the group. If you readd the same
     * element object, it will NOT be readded to the group.
     *
     * @access public
     * @param  string $element The name of the element.
     * @param  string $group   The name of the group.
     * @return object The object that was removed or false
     */
    public function removeElementFromGroup($element, $group)
    {
        // Make sure the group exists.
        if (!$this->groupExists($group)) {
            return false;
        }

        // Grab the group.
        $grp = $this->getGroup($group);

        // Check to make sure the element is part of the group.
        if (!$grp->elementExists($element)) {
            return false;
        }

        // Remove the element from the group.
        return $this->removeElementObjectFromGroup($grp->getElement($element),
                                                   $group
                                                   );
    }

    /**
     * Adds an element object to a group.
     * 
     * A group is just a way to logically and visually group form elements.
     * Elements can be added to or removed from a group without removing them
     * from the form all together. However, obviously if the element is removed
     * from the form, it will be removed from the group. If you readd the same
     * element object, it will NOT be readded to the group.
     *
     * This method returns true if the element has been added to the group and
     * false in all other cases including when the element is not part of the 
     * form or the group does not exist.
     *
     * Elements may only be part of one group. If the element is already part
     * of another group, it will not be added to this group.
     *
     * An element must be part of the form before it can be added to the group.
     * This is normally not a problem as elements should be created using 
     * addElement or createElement.
     *
     * @access public
     * @param  string  $element The element.
     * @param  string  $group   The name of the group.
     * @return boolean true if the element has been added to the group.
     */
    public function addElementObjectToGroup($element, $group)
    {
        // Check to make sure the element exists.
        if (!$this->elementExists($element->getName())) {
            return false;
        }

        // Make sure the group exists.
        if (!$this->groupExists($group)) {
            return false;
        }

        // Remove the element from the form.
        $this->removeElementObject($element);
        
        // Add the element to the group.
        $grp = $this->getGroup($group);

        return $grp->addElement($element);
    }

    /**
     * Removes an element object from a group.
     *
     * A group is just a way to logically and visually group form elements.
     * Elements can be added to or removed from a group without removing them
     * from the form all together. However, obviously if the element is removed
     * from the form, it will be removed from the group. If you readd the same
     * element object, it will NOT be readded to the group.
     *
     * This method returns true if the element has been removed from the group
     * and false in all other cases including when the element is not part of
     * the form or the group does not exist.
     *
     * @access public
     * @param  string $element The element.
     * @param  string $group   The name of the group.
     * @return object The object that was removed or false.
     */
    public function removeElementObjectFromGroup($element, $group)
    {
        // Make sure the group exists.
        if (!$this->groupExists($group)) {
            return false;
        }
        
        // Grab the group.
        $grp = $this->getGroup($group);

        // Check to make sure the element is part of the group.
        if (!$grp->elementExists($element->getName())) {
            return false;
        }

        // Remove the element from the group.
        $grp->removeElement($element);

        return $element;
    }

    /**
     * Returns an array containing the names of the elements in the given
     * group.
     *
     * If the group does not exist, this method returns an empty array.
     *
     * @access public
     * @param  string $name The name of the group
     * @return array 
     */
    public function getGroupElements($name)
    {
        // Make sure the group exists.
        if (!$this->groupExists($group)) {
            return false;
        }
        
        // Grab the group.
        $grp = $this->getGroup($group);

        return $grp->getAllElements();
    }

    // }}}
    // {{{ Values

    /**
     * Returns the current value for all elements in an associative array.
     * 
     * The array will be of the form: array(<name> => <value>);
     *
     * @access public
     * @return array
     */
    public function getValues()
    {
        // Loop through all the elements and collect their values.
        $values = array();
        foreach ($this->getAllElements() as $name => $element) {
            // Check to see if the element is a group.
            if ($this->isGroup($element)) {
                // Merge the values from the group with the other values.
                $values = array_merge($values, $element->getValue());
            } else {
                // Just add the element's value to the array.
                $values[$name] = $element->getValue();
            }
        }

        return $values;
    }

    /**
     * Returns the current value for the given element.
     *
     * @access public
     * @param  string $name The element name.
     * @return mixed  The value of the element or group of elements.
     * @throws Structures_Form_Exception
     */
    public function getValue($name)
    {
        // Check to see if the element exists.
        if (!$this->elementExists($name)) {
            // Throw an exception because returning any value could be 
            // misleading.
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_NONEXISTENT_ELEMENT),
                                                self::ERROR_NONEXISTENT_ELEMENT
                                                );
        }

        // Get the element.
        $element = $this->getElement($name);

        // Return the element's value. A groups return value will be an array.
        return $element->getValue();
    }

    /**
     * Sets the values for the form elements.
     * 
     * The array passed in should be of the form: array(<name> => <value>)
     *
     * @uses setValue()
     *
     * @access public
     * @param  array  $values An array of values.
     * @return array  An array containing names of elements whose value has
     *                chagned
     */
    public function setValues($values)
    {
        // Loop through the array and try to set the values of each element.
        $success = array();
        foreach ($values as $element => $value) {
            // We want to prevent throwing an exception here so check for the 
            // element first.
            if ($this->elementExists($element)) {
                // Try to set the value for the element.
                if ($this->setValue($element, $value)) {
                    // Value was set.
                    $success[] = $element;
                }
            }
        }

        return $success;
    }

    /**
     * Sets the value for the given element.
     *
     * This method returns true if the value appears to have been changed. If
     * false is returned, it may indicated that the element does not exist, 
     * that the new value was the same as the old value, or that it was frozen.
     *
     * @access public
     * @param  string  $name  The name of the element
     * @param  mixed   $value The element value.
     * @return boolean true if the element's value was set.
     */
    public function setValue($name, $value)
    {
        // Check to see if the element exists.
        if (!$this->elementExists($name)) {
            // Throw an exception because returning any value could be 
            // misleading.
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_NONEXISTENT_ELEMENT),
                                                self::ERROR_NONEXISTENT_ELEMENT
                                                );
        }

        // Get the element.
        $element = $this->getElement($name);

        // Set the value.
        return $element->setValue($value);        
    }

    /**
     * Clears the values of all form elements (if possible).
     *
     * @access public
     * @return array  An array of elements whose value has been cleared.
     */
    public function clearValues()
    {
        // Loop through all elements and call the clearValue method.
        $success = array();
        foreach ($this->getAllElements() as $name => $element) {
            if ($element->clearValue()) {
                // Value was cleared successfully.
                $success[] = $name;
            }
        }

        return $success;
    }

    /**
     * Sets the values of the elements and make the given values the defautls.
     *
     * This method over writes any values currently set!
     *
     * @access public
     * @param  array  $defaults An array of default values.
     * @return array  An array containing names of elements whose value has
     *                changed.
     */
    public function setDefaults($defaults)
    {
        // Set the array as the defaults.
        $this->defaults = $defaults;

        // Set the values.
        return $this->setValues($defaults);
    }

    /**
     * Restores the elements to their default values.
     *
     * This method over writes any values currently set!
     *
     * If no defaults were set, this method will return an empty array.
     *
     * @access public
     * @return array  An array containing names of elements whose value has
     *                changed.
     */
    public function restoreDefaults()
    {
        return $this->setValues($this->defaults);
    }

    /**
     * Returns the array of default values.
     *
     * @access public
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Collects submitted values and calls the callback or error callback.
     *
     * @access public
     * @return boolean false to continue processing callbacks.
     */
    public function submit()
    {
        // Validate the form.
        $failures = $this->validate();

        // Process the errors. An empty array will clear any existing errors.
        call_user_func_array($this->getErrorCallback(), array($failures));

        if (!count($failures)) {
            // Check to see if the form widgets should be submitted instead of
            // the values.
            if ($this->submitObjects) {
                // Send the widgets!
                $values = $this->getAllElements();
            } else {
                // Send the values.
                $values = $this->getValues();
            }
            
            // Call the user's callback.
            call_user_func_array($this->getSubmitCallback(), array($values));
            
            // Continue calling callbacks.
            return false;
        } else {
            // Stop processing callbacks.
            return true;
        }
    }
    
    /**
     * Simply passes the error array on to the renderer.
     *
     * It might be a good idea to re-render the form after errors are added but
     * that is the responsibility of the developer and the renderer. It cannot
     * be determined here if the user wants to re-render. Nor can the form
     * reliably be changed form here.
     *
     * If errors need to be handled in a different way, you should set a custom
     * error callback either when the form is constructed or by using
     * setErrorCallback().
     *
     * @access public
     * @param  array  $errors An array of error messages.
     * @return void
     */
    public function defaultErrorCallback($errors)
    {
        // Check to see if the renderer is set. (It almost has to be)
        if (empty($this->renderer)) {
            $this->setDefaultRenderer();
        }

        // Pass off the errrors.
        $this->renderer->setErrors($errors);
    }

    /**
     * Validates the values of the form against the applied rules.
     *
     * This method returns an array even if there are no errors. If the array
     * is empty, then no errors occurred or no rules were applied.
     *
     * @access public
     * @return array  An array of error messages
     */
    public function validate()
    {
        // Call each rule and check for errors.
        $errors = array();
        foreach ($this->rules as $name => $rule) {
            $rObj = $rule['object'];
            // Validate the elements.
            foreach ($rule['elements'] as $element) {
                $result = $rObj->validate($this->getElement($element));
                if ($result !== true) {
                    // Errors were found.
                    $errors[] = $result;
                }
            }
        }

        return $errors;
    }

    // }}}
    // {{{ Rules

    /**
     * Registers a rule class with the form.
     *
     * Rule objects are used to validate the values of the form elements before
     * they are submitted to the callback. If an elements value violates a rule
     * a rule callback will be called which allows the user to handle errors in
     * their own way. All violations are collected on each submit and then
     * passed off to the rule callback.
     *
     * Only registered rules may be applied to a form element.
     * 
     * All registered rules must implement the Structures_Form_RuleInterface.
     *
     * To avoid overhead, a rule is not checked until it is applied to a form
     * element.
     *
     * @access public
     * @param  string  $name  A name to identify the rule.
     * @param  string  $class The name of the rule class.
     * @param  string  $path  The path to the class definition.
     * @return boolean true if the rule was registered.
     * @throws Structures_Form_Exception
     */
    public function registerRule($name, $class, $path)
    {
        // Check to see if the element is already registered.
        if ($this->isRuleRegistered($name, $class)) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_REGISTRATION_RULE),
                                                self::ERROR_REGISTRATION_RULE_DOUBLEREG
                                                );
        }
        
        // If the class is not already declared, make sure the path is
        // readable.
        if (!class_exists($class) && !$this->isIncludable($path)) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_REGISTRATION_RULE),
                                                self::ERROR_NONEXISTENT_FILE
                                                );
        }        

        // Add the information to the registered elements array.
        $this->registeredRules[$name] = array('class' => $class,
                                              'path'  => $path
                                              );

        return $this->isElementRegistered($name);
    }

    /**
     * Unregisters a rule with the form.
     *
     * Only registered rules may be applied to a form element.
     *
     * A rule may not be unregistered if it is currently being applied to one
     * or more form elements.
     *
     * You may want to unregister a rule to prevent it from being applied to a
     * form. For example, if your app creates forms on the fly, you may want to
     * unregister a rule to prevent the user from applying a rule that their
     * PHP installation cannot support (regular expressions may not be
     * available).
     * 
     * @access public
     * @param  string  $name The name of the rule class.
     * @return boolean true if the rule was unregistered.
     * @throws Structures_Form_Exception
     */
    public function unRegisterRule($name)
    {
        // Make sure the rule is not applied.
        if ($this->isRuleApplied($name)) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_UNREGISTRATION_RULE),
                                                self::ERROR_UNREGISTRATION_RULEINUSE
                                                );
        }

        // Just unset the registered rule element for this rule.
        unset($this->registeredRules[$name]);

        return $this->isRuleRegistered($name);
    }

    /**
     * Returns whether or not a rule is registered.
     *
     * @access public
     * @param  string  $rule  The name of the rule type.
     * @param  string  $class Optional class name to check.
     * @return boolean true if the rule is registered.
     */
    public function isRuleRegistered($rule, $class = null)
    {
        // Check by name first. 
        if (isset($this->registeredRules[$rule]) &&
            is_array($this->registeredRules[$rule])
            ) {
            return true;
        } elseif (!is_null($class)) {
            // Check to see if the rule is registered under a different name.
            foreach ($this->registeredRules as $rule) {
                // Use a case insensitive comparison.
                if (strcasecmp($rule['class'], $class) === 0) {
                    return true;
                }
            }
        }
            
        // If we made it here, the class is not registered.
        return false;
    }

    /**
     * Associates a rule with a form element.
     *
     * When a form is submitted, the values will be checked against any rules
     * that have been associated with the elements. A rule of a give type can
     * only be added to an individual element once. Attempting to add a rule
     * to the same element twice will have no extra effect.
     *
     * This method passes any extra arguments on to the rule constructor.
     * This allows one method to be used for creating many types of rules.
     * The exact number and type of arguments that is passed to this method
     * varies depending on the type of rule to be created. If the wrong
     * number or type of arguments is passed, the rule constructor should
     * throw an exception.
     *
     * @access public
     * @param  string  $name  The name of the element.
     * @param  string  $rule  The name of the rule.
     * @return boolean true if the rule was applied.
     */
    public function addRule($name, $rule)
    {
        // Check to make sure the element exists.
        if (!$this->elementExists($name)) {
            return false;
        }

        // Make sure the rule is registered.
        if (!$this->isRuleRegistered($rule)) {
            return false;
        }

        // See if we need to create the rule object.
        if (!isset($this->rules[$rule]['object']) ||
            !is_object($this->rules[$rule]['object'])
            ) {
            // Create the rule object.
            $args = func_get_args();
            unset($args[0]);
         
            // Create the rule object.
            $rObj = call_user_func_array(array($this, 'createRule'),
                                         $args
                                         );
            
            // Set up the rules array.
            $this->rules[$rule] = array('object'   => $rObj,
                                        'elements' => array());
        }

        // Associate the rule with the element.
        $this->rules[$rule]['elements'][] = $name;
        
        return true;
    }

    /**
     * Creates a rule object.
     *
     * When a form is submitted, the values will be checked against any rules
     * that have been associated with the elements. A rule of a give type can
     * only be added to an individual element once. Attempting to add a rule
     * to the same element twice will have no extra effect.
     *
     * This method passes any extra arguments on to the rule constructor.
     * This allows one method to be used for creating many types of rules.
     * The exact number and type of arguments that is passed to this method
     * varies depending on the type of rule to be created. If the wrong
     * number or type of arguments is passed, the rule constructor should
     * throw an exception.
     *
     * @access public
     * @param  string  $rule  The name of the rule.
     * @return boolean true if the rule was applied.
     */
    public function createRule($rule)
    {
        // Make sure the rule is registered.
        if (!$this->isRuleRegistered($rule)) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_UNREGISTERED_RULE),
                                                self::ERROR_UNREGISTERED_RULE
                                                );
        }

        // Make sure an element with this name isn't already in the form.
        if ($this->ruleExists($rule)) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_ADD_RULE_DOUBLEADD),
                                                self::ERROR_ADD_RULE_DOUBLEADD
                                                );
        }

        // Include the element class file.
        require_once $this->registeredRules[$rule]['path'];

        // Get the class name.
        $class = $this->registeredRules[$rule]['class'];
        
        // Make sure the class exists.
        if (!class_exists($class)) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_REGISTRATION_RULE),
                                                self::ERROR_NONEXISTENT_CLASS
                                                );
        }

        // Try to instantiate the class.
        // First add the form as the first argument. <-- Not needed.
        $args    = func_get_args();
        unset($args[0]);
        //$args[0] = $this;

        // Next instantiate the class.
        $rObj = call_user_func_array(array(new ReflectionClass($class),
                                           'newInstance'
                                           ),
                                     $args
                                     );

        // Make sure the object implements the correct interface.
        require_once 'Structures/Form/RuleInterface.php';
        if (!$rObj instanceof Structures_Form_RuleInterface) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_LACKSINTERFACE_RULE),
                                                self::ERROR_LACKSINTERFACE_RULE
                                                );
        }

        return $rObj;
    }

    /**
     * Removes a rule from an element.
     *
     * When a form is submitted, the values will be checked against any rules
     * that have been associated with the elements. A rule of a give type can
     * only be added to an individual element once. Attempting to add a rule
     * to the same element twice will have no extra effect. Therefore, it only
     * makes sense to try to remove a rule once. 
     *
     * This method will return true if the rule is not associated with the 
     * element. This means that it will also return true even if the rule was
     * never associated with the element in the first place.
     * 
     * @access public
     * @param  string  $element The name of the element to remove the rule from
     * @param  string  $rule    The name of the rule to remove
     * @return boolean true if the rule is no longer associated with the
     *                 element
     */
    public function removeRule($element, $rule)
    {
        // Check to make sure the element exists.
        if (!$this->elementExists($name)) {
            return false;
        }

        // Make sure the rule is registered.
        if (!$this->isRuleRegistered($rule)) {
            return false;
        }

        // Remove the element/rule association.
        if (isset($this->rules[$rule]['elements']) &&
            in_array($this->rules[$rule]['elements'], $element)
            ) {
            $key = array_search($this->rules[$rule]['elements'], $element);
            unset($this->rules[$rule]['elements'][$key]);
        }

        return true;
    }

    /**
     * Associates a rule with a form element object.
     *
     * When a form is submitted, the values will be checked against any rules
     * that have been associated with the elements. A rule of a give type can
     * only be added to an individual element once. Attempting to add a rule
     * to the same element twice will have no extra effect.
     *
     * This method passes any extra arguments on to the rule constructor.
     * This allows one method to be used for creating many types of rules.
     * The exact number and type of arguments that is passed to this method
     * varies depending on the type of rule to be created. If the wrong
     * number or type of arguments is passed, the rule constructor should
     * throw an exception.
     *
     * @access public
     * @param  object  $element The form element object
     * @param  string  $rule    The form rule name.
     * @return boolean true if the rule was applied.
     */
    public function addRuleToObject($element, $rule)
    {
        // Check to make sure the element exists.
        if (!$this->elementExists($element->getName())) {
            return false;
        }

        // Make sure the rule is registered.
        if (!$this->isRuleRegistered($rule)) {
            return false;
        }

        // Apply the rule to the element.
        $args    = func_get_args();
        $args[0] = $element->getName();

        return call_user_func_array(array($this, 'addRule'), $args);
    }

    /**
     * Removes a rule from a form element object.
     *
     * When a form is submitted, the values will be checked against any rules
     * that have been associated with the elements. A rule of a give type can
     * only be added to an individual element once. Attempting to add a rule
     * to the same element twice will have no extra effect. Therefore, it only
     * makes sense to try to remove a rule once. 
     *
     * This method will return true if the rule is not associated with the 
     * element. This means that it will also return true even if the rule was
     * never associated with the element in the first place.
     * 
     * @access public
     * @param  object  $element The form element to remove the rule from.
     * @param  string  $rule    The rule to remove from the object.
     * @return boolean true if the rule is no longer associated with the
     *                 element
     */
    public function removeRuleFromObject($element, $rule)
    {
        // Check to make sure the element exists.
        if (!$this->elementExists($element->getName())) {
            return false;
        }

        // Make sure the rule is registered.
        if (!$this->isRuleRegistered($rule)) {
            return false;
        }
        
        // We only need to disassociate the rule with the name.
        $this->removeRule($element->getName(), $rule);
    }

    /**
     * Returns whether or not the give rule is currently associated with any
     * form elements.
     *
     * @access public
     * @param  string  $name The name of the form rule to check.
     * @return boolean true if the rule is currently associated with an element
     */
    public function isRuleApplied($name)
    {
        return (isset($this->rules[$name]) && 
                count($this->rules[$name]['elements'])
                );
    }

    /**
     * Returns whether or not a rule with the given name already exists.
     *
     * @access public
     * @param  string  $rule The name fo the rule.
     * @return boolean true if the name is in use.
     */
    public function ruleExists($rule)
    {
        return array_key_exists($rule, $this->rules);
    }

    /**
     * Sets the require note.
     *
     * @access public
     * @param  string $note The text to use.
     * @return void
     */
    public function setRequiredNote($note)
    {
        $this->requiredNote = $note;
    }
    
    /**
     * Returns the current required note.
     *
     * @access public
     * @return string
     */
    public function getRequiredNote()
    {
        return $this->requiredNote;
    }

    /**
     * Sets the require symbol.
     *
     * @access public
     * @param  string $symbol The text to use.
     * @return void
     */
    public function setRequiredSymbol($symbol)
    {
        $this->requiredSymbol = $symbol;
    }
    
    /**
     * Returns the current required symbol.
     *
     * @access public
     * @return string
     */
    public function getRequiredSymbol()
    {
        return $this->requiredSymbol;
    }

    /**
     * Returns whether or not an element is required. 
     *
     * @access public
     * @param  string  $element The name of the element.
     * @return boolean true if the required rule is applied to the element.
     */
    public function isRequired($element)
    {
        return (isset($this->rules[self::REQUIRED_RULE]['elements']) &&
                in_array($element, $this->rules[self::REQUIRED_RULE]['elements'])
                );
    }

    /**
     * Returns whether or not the given rule is applied to the given element.
     *
     * This method returns true if the rule is applied to the element and false
     * in all other cases including if the rule or element is not defined.
     *
     * @access public
     * @param  string  $element The name of the element.
     * @param  string  $rule    The name of the rule.
     * @return boolean true if the rule is applied to the element.
     */
    public function isRuleAppliedToElement($element, $rule)
    {
        return (isset($this->rules[$rule]) &&
                in_array($this->rules[$rule]['elements'], $element)
                );
    }

    // }}}
    // {{{ Display

    /**
     * Sets a renderer object.
     *
     * Renderers must implement Structures_Form_RendererInterface. This helps to
     * ensure consistency among the API and avoid any fatal errors. A renderer
     * is used to position and display the form elements within a container
     * widget. 
     *
     * Unlike rules and elements, renderers are created on their own (not 
     * through a form method). This is because they do not need to know 
     * thing about the form at construction time and the form does not need to
     * know anything about the renderer until the form is to be displayed.
     *
     * A form may only have one renderer at a time. Setting a second renderer
     * will overwrite the first. If no renderer is set, the default renderer
     * will be used.
     *
     * @access public
     * @param  object $renderer An object that implements
     *                          Structures_Form::RENDERER_INTERFACE
     * @return void
     * @throws Structures_Form_Exception
     */
    public function setRenderer($renderer)
    {
        // Make sure that the renderer is an object and that it implements the
        // needed interface.
        require_once 'Structures/Form/RendererInterface.php';
        if (!is_object($renderer) ||
            !$renderer instanceof Structures_Form_RendererInterface
            ) {
            require_once 'Structures/Form/Exception.php';
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_LACKSINTERFACE_RENDERER),
                                                self::ERROR_LACKSINTERFACE_RENDERER
                                                );
        }

        // Set the renderer.
        $this->renderer = $renderer;

        // Set the form for the renderer.
        $this->renderer->setForm($this);
    }

    /**
     * Creates a default renderer object.
     *
     * This method may be called by group elements that need to renderer 
     * elements but do not have a renderer set.
     *
     * @access public
     * @return object.
     */
    public function getDefaultRenderer()
    {
        // Require the default renderer file.
        require_once $this->defaultRendererPath;

        // Create an instance of the default renderer.
        $class = $this->defaultRenderer;
        $obj   = new $class();

        return $obj;
    }

    /**
     * Creates a default renderer and sets it as the current renderer.
     *
     * An exception may be thrown by setRenderer. It will pass through to the
     * calling function.
     *
     * @access protected
     * @return void
     */
    protected function setDefaultRenderer()
    {
        // Get a default renderer.
        $obj = $this->getDefaultRenderer();

        // Set the renderer.
        $this->setRenderer($obj);
    }

    /**
     * Returns a container widget holding the form elements.
     *
     * Passes the elements, required note and required symbol to the renderer
     * and then calls renderer(). 
     *
     * Pulling elements out of a renderer is a pain in the ass. Trying to find
     * the correct widget (or parent widget) can be nearly impossible.
     * Therefore, even if you remove a widget from the form, it will still 
     * appear if the widget was removed after the form was rendered. 
     *
     * @access public
     * @return object A container widget holding the form.
     * @throws Structures_Form_Exception
     */
    public function render()
    {
        // Check to see if a renderer has been set.
        if (empty($this->renderer)) {
            // Try to create a default renderer.
            try {
                require_once 'Structures/Form/Exception.php';
                $this->setDefaultRenderer();
            } catch (Structures_Form_Exception $sfe) {
                // Set a prettier error message but keep the same code.
                throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_RENDER_FAILURE),
                                         $sfe
                                         );
            }
        }

        // Pass the elements to the renderer.
        $this->renderer->setElements($this->getAllElements());

        // Pass the required note and symbol.
        $this->renderer->setRequiredNote($this->requiredNote);
        $this->renderer->setRequiredSymbol($this->requiredSymbol);

        // Try to renderer the form.
        try {
            $form = $this->renderer->render();
            
            // Keep track of the rendered widget.
            $this->renderedForm = $form;

            // Return the container widget.
            return $form;
        } catch (Structures_Form_Exception $sfe) {
            // Set a prettier error message but keep the same code.
            throw new Structures_Form_Exception(self::getErrorMessage(self::ERROR_RENDER_FAILURE),
                                                $sfe
                                                );
        }          
    }

    /**
     * Returns the most recently rendered widget.
     *
     * This method will not render the form! It only returns the widget created
     * when render() was called!
     *
     * @access public
     * @return object The rendered form.
     */
    public function getRenderedForm()
    {
        return $this->renderedForm;
    }

    /**
     * Changes the UI of the form from the current set of elements to the given
     * element set.
     *
     * This method allows you to quickly and easily change from one element set
     * to another. In most cases it is only necessary to change renderers but
     * some renderers may have additional requirements for the elements that 
     * necessitate changing the classes that represent the elements. 
     * 
     * @access public
     * @param  object  $elementSet A Structures_Form_Element set.
     * @return boolean true if the element set was changed successfully.
     */
    public function swapElementSet(Structures_Form_ElementSet $elementSet)
    {
        // First, get the new element set.
        foreach ($elementSet as $element) {
            // Next, remove all of the elements for each type.
            $removed = array();
            foreach ($this->getElementsByType($element[0]) as $elem) {
                $removed[$elem->getName()] = $elem;
                $this->removeElementObject($elem);
            }
                
            // Next, unregister the old type and register the new type.
            $this->unRegisterElement($element[0]);
            $this->registerElement($element[0], $element[1], $element[2]);
            
            // Then create elements of the new type.
            $new = array();
            foreach ($removed as $name => $elem) {
                $newElem = $this->createElement($elem->getType(),
                                                $name,
                                                $elem->getLabel()
                                                );

                // Set all of the element's data.
                $newElem->setValue($elem->getValue());
                $elem->isFrozen() ? $newElem->freeze() : $newElem->unfreeze();
                $newElem->setLabel($elem->getLabel());

                // Then re-add the new element.
                $this->addElementObject($newElem);
            }
        }

        // Finally, set a new default renderer.
        $renderer = $elementSet->getDefaultRenderer();
        $this->defaultRenderer     = $renderer['class'];
        $this->defaultRendererPath = $renderer['path'];

        return true;
    }
    // }}}
}
?>