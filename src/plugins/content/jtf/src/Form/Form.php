<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace JoomTools\Plugin\Content\Jtf\Form;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\Form as JoomlaForm;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Form Class for the Joomla Platform.
 *
 * This class implements a robust API for constructing, populating, filtering, and validating forms.
 * It uses XML definitions to construct form fields and a variety of field and rule classes to
 * render and validate the form.
 *
 * @since  4.0.0
 */
class Form extends JoomlaForm
{
    /**
     * The form object errors array.
     *
     * @var   array
     *
     * @since  4.0.0
     */
    protected $subFormErrors = array();

    /**
     * Array of layoutPaths.
     *
     * @var   array
     *
     * @since  4.0.0
     */
    public $layoutPaths = array();

    /**
     * Array of frameworks.
     *
     * @var   array
     *
     * @since  4.0.0
     */
    public $framework = array();

    /**
     * Set enctype.
     *
     * @var   boolean
     *
     * @since  4.0.0
     */
    public $setEnctype = false;

    /**
     * Set enctype.
     *
     * @var   boolean
     *
     * @since  4.0.0
     */
    public $rendererDebug = false;

    /**
     * Set the option to display the required field description.
     *
     * @var   boolean
     *
     * @since  4.0.0
     */
    public $showRequiredFieldDescription = true;

    /**
     * Set the value for field description type.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    public $showfielddescriptionas = 'text';

    /**
     * Set the value for field marker.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    public $fieldmarker = 'optional';

    /**
     * Set the value for field marker place.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    public $fieldmarkerplace = 'field';

    /**
     * Method to instantiate the form object.
     *
     * @param   string  $name     The name of the form.
     * @param   array   $options  An array of form options.
     *
     * @since  4.0.0
     */
    public function __construct(string $name, array $options = array())
    {
        parent::__construct($name, $options);
    }

    /**
     * Method to get an instance of a form.
     *
     * @param   string          $name     The name of the form.
     * @param   string          $data     The name of an XML file or string to load as the form definition.
     * @param   array           $options  An array of form options.
     * @param   boolean         $replace  Flag to toggle whether form fields should be replaced if a field
     *                                    already exists with the same group/name.
     * @param   string|boolean  $xpath    An optional xpath to search for the fields.
     *
     * @return  Form  Form instance.
     *
     * @throws  \InvalidArgumentException if no data provided.
     * @throws  \RuntimeException if the form could not be loaded.
     * @since  4.0.0
     *
     */
    public static function getInstance($name, $data = null, $options = array(), $replace = true, $xpath = false)
    {
        // Reference to array with form instances
        $forms = &self::$forms;

        // Only instantiate the form if it does not already exist.
        if (!isset($forms[$name])) {
            $data = trim($data);

            if (empty($data)) {
                throw new \InvalidArgumentException(
                    sprintf('%1$s(%2$s, *%3$s*)', __METHOD__, $name, gettype($data))
                );
            }

            // Instantiate the form.
            $forms[$name] = new self($name, $options);

            // Load the data.
            if (substr($data, 0, 1) == '<') {
                if ($forms[$name]->load($data, $replace, $xpath) == false) {
                    throw new \RuntimeException(
                        sprintf('%s() could not load form', __METHOD__)
                    );
                }
            } else {
                if ($forms[$name]->loadFile($data, $replace, $xpath) === false) {
                    throw new \RuntimeException(
                        sprintf(
                            '%s() could not load file %s', __METHOD__,
                            str_replace(JPATH_ROOT, '', $data)
                        )
                    );
                }
            }
        }

        return $forms[$name];
    }

    /**
     * Set the value of an attribute of the form itself
     *
     * @param   string  $name   Name of the attribute to get
     * @param   null    $value  Value to set for the attribute
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function setAttribute(string $name, $value = null)
    {
        if ($this->xml instanceof \SimpleXMLElement) {
            $attributes = $this->xml->attributes();

            if (!empty($value)) {
                // Ensure that the attribute exists
                if (empty($attributes[$name])) {
                    $this->xml->addAttribute($name, trim($value));
                } else {
                    $attributes[$name] = trim($value);
                }
            }
        }

        $this->syncPaths();
    }

    /**
     * Reset submitted Values
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function resetData()
    {
        $this->data = new Registry;
    }

    /**
     * Method to validate form data.
     *
     * Validation warnings will be pushed into Form::$errors and should be
     * retrieved with Form::getErrors() when validate returns boolean false.
     *
     * @param   array   $data   An array of field values to validate.
     * @param   string  $group  The optional dot-separated form group path on which to filter the
     *                          fields to be validated.
     *
     * @return  boolean  True on success.
     *
     * @since  4.0.0
     */
    public function validate($data, $group = null)
    {
        // Make sure there is a valid Form XML document.
        if (!($this->xml instanceof \SimpleXMLElement)) {
            throw new \UnexpectedValueException(
                sprintf(
                    '%s::%s `xml` is not an instance of SimpleXMLElement',
                    \get_class($this), __METHOD__
                )
            );
        }

        $return = true;

        // Create an input registry object from the data to validate.
        $input = new Registry($data);

        // Get the fields for which to validate the data.
        $fields = $this->findFieldsByGroup($group);

        if (!$fields) {
            // PANIC!
            return false;
        }

        // Validate the fields.
        foreach ($fields as $field) {
            $name = (string) $field['name'];

            // Define field name for messages
            if ($field['label']) {
                $fieldLabel = $field['label'];

                // Try to translate label if not set to false
                $translate = (string) $field['translateLabel'];

                if (!($translate === 'false' || $translate === 'off' || $translate === '0')) {
                    $fieldLabel = Text::_($fieldLabel);
                }
            } else {
                $fieldLabel = Text::_($name);
            }

            $disabled = ((string) $field['disabled'] === 'true' || (string) $field['disabled'] === 'disabled');

            $fieldExistsInRequestData = $input->exists($name) || $input->exists($group . '.' . $name);

            // If the field is disabled, but it is passed in the request,
            // this is invalid as disabled fields are not added to the request
            if ($disabled && $fieldExistsInRequestData) {
                throw new \RuntimeException(Text::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', $fieldLabel));
            }

            // Get the field groups for the element.
            $attrs     = $field->xpath('ancestor::fields[@name]/@name');
            $groups    = array_map('strval', $attrs ?: []);
            $attrGroup = implode('.', $groups);

            $key = $attrGroup ? $attrGroup . '.' . $name : $name;

            $fieldObj = $this->loadField($field, $attrGroup);

            if ($fieldObj) {
                $valid = $fieldObj->validate($input->get($key), $attrGroup, $input);

                // Check for an error.
                if ($valid !== true) {
                    $this->setErrors($valid);
                    $return = false;
                }
            } elseif ($input->exists($key)) {
                // The field returned false from setup and shouldn't be included in the page body - yet we received
                // a value for it. This is probably some sort of injection attack and should be rejected
                $this->errors[] = new \RuntimeException(Text::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', $key));
                $return         = false;
            }
        }

        return $return;
    }

    /**
     * Return all errors, if any.
     *
     * @param   string  $subFormId  The unique Id of the Subform to add the errors in $subFormErrors
     *
     * @return  array  Array of error messages or RuntimeException objects.
     *
     * @since  4.0.0
     */
    public function getErrors($subFormId = null)
    {
        if (!is_null($subFormId)) {
            return $this->subFormErrors[$subFormId];
        }

        return $this->errors;
    }

    /**
     * Add instanceof Exception to the errors array
     *
     * @param   \Exception|\Exception[]  $errors     Single Exception or array of Exceptions
     * @param   string                   $subFormId  The unique Id of the Subform to add the errors in $subFormErrors
     *
     * @return   void
     *
     * @since  4.0.0
     */
    public function setErrors($errors, $subFormId = null)
    {
        if (is_array($errors)) {
            foreach ($errors as $error) {
                $this->setErrors($error, $subFormId);
            }

            return;
        }

        if ($errors instanceof \Exception) {
            if (!is_null($subFormId)) {
                $this->subFormErrors[$subFormId][] = $errors;

                return;
            }

            $this->errors[] = $errors;
        }
    }
}
