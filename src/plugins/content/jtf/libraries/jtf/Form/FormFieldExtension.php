<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Jtf\Layout\FileLayout;

/**
 * Abstract Form Field class for the Joomla Platform.
 *
 * @since  4.0.0
 */
trait FormFieldExtension
{
    /**
     * The control.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $control;

    /**
     * The hidden state for the form field label.
     *
     * @var   boolean
     *
     * @since  4.0.0
     */
    protected $hiddenlabel = false;

    /**
     * The hidden state for the form field label.
     *
     * @var   boolean
     *
     * @since  4.0.0
     */
    protected $inline = false;

    /**
     * The value of the gridgruop attribute.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $gridgroup;

    /**
     * The value of the gridlabel attribute.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $gridlabel;

    /**
     * The value of the gridfield attribute.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $gridfield;

    /**
     * The value of the optionlabelclass attribute.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $optionlabelclass;

    /**
     * The value of the optionclass attribute.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $optionclass;

    /**
     * The value of the icon attribute.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $icon;

    /**
     * The value of the buttonclass attribute.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $buttonclass;

    /**
     * The value of the buttonicon attribute.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $buttonicon;

    /**
     * The value of the description class based on framework.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $descriptionclass;

    /**
     * Set the value for field description type.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $showfielddescriptionas = 'text';

    /**
     * Set the value for field marker.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $fieldmarker = 'optional';

    /**
     * Set the value for field marker place.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $fieldmarkerplace = 'field';

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since  4.0.0
     */
    public function __get($name)
    {
        switch ($name) {
            case 'control':
            case 'hiddenLabel':
            case 'hiddenlabel':
            case 'optionclass':
            case 'optionlabelclass':
            case 'gridgroup':
            case 'gridlabel':
            case 'gridfield':
            case 'icon':
            case 'inline':
            case 'buttonclass':
            case 'buttonicon':
            case 'descriptionclass':
            case 'showfielddescriptionas':
            case 'fieldmarker':
            case 'fieldmarkerplace':
                $name = strtolower($name);

                return $this->$name;

            default:
                return parent::__get($name);
        }
    }

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to set the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function __set($name, $value)
    {
        switch (strtolower($name)) {
            case 'control':
            case 'optionclass':
            case 'optionlabelclass':
            case 'gridgroup':
            case 'gridlabel':
            case 'gridfield':
            case 'icon':
            case 'buttonclass':
            case 'buttonicon':
            case 'descriptionclass':
            case 'showfielddescriptionas':
            case 'fieldmarker':
            case 'fieldmarkerplace':
                $name        = strtolower($name);
                $this->$name = (string) $value;
                break;

            case 'hiddenLabel':
            case 'hiddenlabel':
            case 'inline':
                $value       = (string) $value;
                $name        = strtolower($name);
                $this->$name = ($value === 'true' || $value === $name || $value === '1');
                break;

            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to attach a JForm object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form
     *                                       field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as as an array container for
     *                                       the field. For example if the field has name="foo" and the group value is
     *                                       set to "bar" then the full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @since  4.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        if (parent::setup($element, $value, $group)) {
            $attributes = array(
                'control',
                'hiddenLabel',
                'hiddenlabel',
                'icon',
                'inline',
                'buttonclass',
                'buttonicon',
                'gridgroup',
                'gridlabel',
                'gridfield',
                'optionclass',
                'optionlabelclass',
                'descriptionclass',
                'showfielddescriptionas',
                'fieldmarker',
                'fieldmarkerplace',
            );

            if ($this->form instanceof Form) {
                foreach ($attributes as $attributeName) {
                    switch ($attributeName) {
                        case 'hiddenLabel':
                            if (!empty($element[$attributeName])) {
                                $this->__set("hiddenlabel", $element[$attributeName]);
                            }
                            break;

                        case 'showfielddescriptionas':
                        case 'fieldmarker':
                        case 'fieldmarkerplace':
                            $this->__set($attributeName, $this->form->$attributeName);
                            break;

                        default:
                            $this->__set($attributeName, $element[$attributeName]);
                            break;
                    }
                }

                if ($this->readonly) {
                    $element['required'] = 'false';
                    $this->__set('required', 'false');
                }

                if ($this->disabled) {
                    $element['required'] = 'false';
                    $this->__set('required', 'false');
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Method to get a control group with label and input.
     *
     * @param   array  $options  Options to be passed into the rendering of the field
     *
     * @return  string  A string containing the html for the control group
     *
     * @since  4.0.0
     */
    public function renderField($options = array())
    {
        $type                = strtolower($this->type);
        $fieldMarker         = $this->fieldmarker;
        $issetHiddenLabel    = $this->hiddenlabel;
        $issetLabel          = !empty($label = $this->getAttribute('label'));
        $issetHint           = !empty($hint = $this->hint);
        $newHint             = array();
        $fieldMarkerDesc     = Text::_('JTF_FIELD_MARKED_DESC_' . strtoupper($fieldMarker));
        $isHintExcludedField = in_array(
            $type,
            array(
                'editor',
                'checkbox',
                'checkboxes',
                'radio',
                'list',
                'captcha',
                'file',
            )
        );

        $isDisabledDescriptionField = in_array(
            $type,
            array(
                'note',
                'spacer',
                'submit',
            )
        );

        $hiddenLabelClass = 'jtfhp';

        $framework    = (array) $this->getForm()->framework;
        $usedFramwork = $framework[0];

        foreach (array('bs5', 'bs4') as $fValue) {
            if (in_array($fValue, $framework, true)) {
                $hiddenLabelClass = 'visually-hidden';
            }
        }

        if ($issetHiddenLabel) {
            $this->fieldmarkerplace       = 'hint';
            $this->showfielddescriptionas = !empty($this->showfielddescriptionas) ? 'text' : false;
            $this->labelclass             = !empty((string) $this->labelclass)
                ? $hiddenLabelClass . ' ' . (string) $this->labelclass
                : $hiddenLabelClass;

            if (!$issetHint && $issetLabel && !$isHintExcludedField) {
                $newHint[] = Text::_($label);
            }
        }

        if ($issetHint && !$isHintExcludedField) {
            $newHint[] = Text::_($hint);
        }

        if ($isHintExcludedField && $this->fieldmarkerplace == 'hint') {
            $this->fieldmarkerplace = 'field';
        }

        if ($this->fieldmarkerplace == 'hint'
            && !$isDisabledDescriptionField
            && (($fieldMarker == 'required' && $this->required) || ($fieldMarker != 'required' && !$this->required))
        ) {
            $newHint[] = Text::_('JTF_FIELD_MARKED_HINT_' . strtoupper($fieldMarker));
        }

        if (!empty($newHint)) {
            $this->hint = implode(' ', $newHint);
        }

        if ($isDisabledDescriptionField || $this->fieldmarkerplace != 'field') {
            $fieldMarkerDesc = '';
        }

        // Description preprocess
        $description = !empty($this->description) && !$isDisabledDescriptionField ? $this->description : null;
        $description = !empty($description) && $this->translateDescription ? Text::_($description) : $description;

        $options['id']                     = $this->id;
        $options['required']               = $this->required;
        $options['icon']                   = $this->getAttribute('icon');
        $options['gridGroup']              = $this->getAttribute('gridgroup');
        $options['gridLabel']              = $this->getAttribute('gridlabel');
        $options['gridField']              = $this->getAttribute('gridfield');
        $options['hiddenLabel']            = $issetHiddenLabel;
        $options['description']            = $description;
        $options['descriptionClass']       = $this->descriptionclass;
        $options['fieldMarker']            = $fieldMarker;
        $options['fieldMarkerPlace']       = $this->fieldmarkerplace;
        $options['fieldMarkerDesc']        = $fieldMarkerDesc;
        $options['showFieldDescriptionAs'] = $this->showfielddescriptionas;
        $options['framework']              = $usedFramwork;

        return parent::renderField($options);
    }

    /**
     * @return  Form
     *
     * @since  4.0.0
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since  4.0.0
     */
    public function getLayoutData()
    {
        $data = parent::getLayoutData();
        $data = array_merge($data, array(
                                     'control'                => $this->control,
                                     'buttonClass'            => $this->buttonclass,
                                     'buttonIcon'             => $this->buttonicon,
                                     'optionLabelClass'       => $this->optionlabelclass,
                                     'optionClass'            => $this->optionclass,
                                     'framework'              => $this->form->framework[0],
                                     'fieldMarker'            => $this->fieldmarker,
                                     'fieldMarkerPlace'       => $this->fieldmarkerplace,
                                     'showFieldDescriptionAs' => $this->showfielddescriptionas,
                                 )
        );

        return $data;
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since  4.0.0
     */
    protected function getOptions()
    {
        $options = parent::getOptions();

        // Define global option class attributes
        $globalOptonClass      = empty((string) $this->optionclass)
            ? array()
            : explode(' ', trim((string) $this->optionclass));
        $globalOptonLabelClass = empty((string) $this->optionlabelclass)
            ? array()
            : explode(' ', trim((string) $this->optionlabelclass));

        foreach ($this->element->xpath('option') as $optionKey => $option) {
            // Merge option class with the globally set one
            $optionClass = array();

            if (!empty($option['class'])) {
                $optionClass = explode(' ', trim((string) $option['class']));
            }

            $optionClass                = ArrayHelper::arrayUnique(array_merge($globalOptonClass, $optionClass));
            $options[$optionKey]->class = implode(' ', $optionClass);

            // Merge option labelclass with the globally set one
            $optionLabelClass = array();

            if (!empty($option['labelclass'])) {
                $optionLabelClass = explode(' ', trim((string) $option['labelclass']));
            }

            $optionLabelClass                = ArrayHelper::arrayUnique(array_merge($globalOptonLabelClass, $optionLabelClass));
            $options[$optionKey]->labelclass = implode(' ', $optionLabelClass);
        }

        return $options;
    }

    /**
     * Get the renderer
     *
     * @param   string  $layoutId  Id to load
     *
     * @return  FileLayout
     *
     * @since  4.0.0
     */
    protected function getRenderer($layoutId = 'default')
    {
        $renderer = new FileLayout($layoutId);

        $renderer->setDebug($this->isDebugEnabled());

        $layoutPaths = $this->getLayoutPaths();

        if ($layoutPaths) {
            $renderer->setIncludePaths($layoutPaths);
        }

        $framework = !empty($this->form->framework) ? $this->form->framework : array();

        // Set Framework as Layout->Suffix
        if (!empty($framework)) {
            $renderer->setSuffixes($framework);
        }

        $layoutFileExists = $renderer->checkLayoutExists();

        if (!$layoutFileExists) {
            throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->fieldname));
        }

        return $renderer;
    }

    /**
     * Allow to override renderer include paths in child fields
     *
     * @return  array
     *
     * @since  4.0.0
     */
    protected function getLayoutPaths()
    {
        return !empty($this->form->layoutPaths) ? $this->form->layoutPaths : parent::getLayoutPaths();
    }

    /**
     * Is debug enabled for this field
     *
     * @return  boolean
     *
     * @since  4.0.0
     */
    protected function isDebugEnabled()
    {
        return ($this->getAttribute('debug', 'false') === 'true' || !empty($this->form->rendererDebug));
    }

    /**
     * Method to validate a FormField object based on field data.
     *
     * @param   mixed          $value        The optional value to use as the default for the field.
     * @param   string         $group        The optional dot-separated form group path on which to find the field.
     * @param   Registry|null  $input        An optional Registry object with the entire data set to validate
     *                                       against the entire form.
     *
     * @return  boolean|\Exception  Boolean true if field value is valid, Exception on failure.
     * @throws  \UnexpectedValueException
     *
     * @since  4.0.0
     */
    public function validate($value = null, $group = null, Registry $input = null)
    {
        // Make sure there is a valid SimpleXMLElement.
        if (!($this->element instanceof \SimpleXMLElement)) {
            throw new \UnexpectedValueException(sprintf('%s::validate `element` is not an instance of SimpleXMLElement', \get_class($this)));
        }

        $fieldName = (string) $this->element['name'];
        $key       = $group ? $group . '.' . $fieldName : $fieldName;

        // Check if the field is shown.
        if (!empty($showOn = (string) $this->element['showon'])) {
            $isShown = $this->isFieldShown($showOn);

            // Remove required flag before the validation, if field is not shown
            if (!$isShown) {
                $this->element['required'] = 'false';
                $this->element['disabled'] = 'true';

                // Field exists in request data
                if ($input->exists($key)) {
                    $input->set($key, '');
                }
            }
        }

        if (version_compare(JVERSION, '4', 'lt')) {
            // If it is Joomla 3 the field validation is make in Form and we return true at this point.
            return true;
        }

        $valid = parent::validate($value, $group, $input);

        if ($valid instanceof \Exception && (string) $this->element['type'] === 'subform') {
            // Get the subform errors.
            $errors = $this->form->getErrors($key);
            $errors = array_unique($errors, SORT_STRING);

            // Merge the errors.
            $valid = array($valid);
            $valid = array_merge($valid, $errors);
        }

        return $valid;
    }

    /**
     * Evaluates whether the field was displayed
     *
     * @param   string  $showOn  The value of the show on attribute.
     *
     * @return  boolean
     *
     * @since  4.0.0
     */
    private function isFieldShown(string $showOn)
    {
        $regex = array(
            'search'  => array(
                '[AND]',
                '[OR]',
            ),
            'replace' => array(
                ' [AND]',
                ' [OR]',
            ),
        );

        $showOn       = str_replace($regex['search'], $regex['replace'], $showOn);
        $showOnValues = explode(' ', $showOn);

        return $this->fieldIsShownValidation($showOnValues);
    }

    /**
     * Evaluate showon values
     *
     * @param   string[]  $values  Array of strings with show on name:value pair
     *
     * @return  boolean
     *
     * @since  4.0.0
     */
    private function fieldIsShownValidation(array $values)
    {
        $valuesSum      = count($values) - 1;
        $conditionValid = array();
        $values         = (array) $values;
        $isShown        = false;

        if (empty($values)) {
            return false;
        }

        foreach ($values as $key => $value) {
            $not       = false;
            $glue      = '';
            $separator = ':';

            if (strpos($value, '[OR]') !== false) {
                $glue  = 'or';
                $value = strtr($value, array('[OR]' => ''));
            }

            if (strpos($value, '[AND]') !== false) {
                $glue  = 'and';
                $value = strtr($value, array('[AND]' => ''));
            }

            if (strpos($value, '!') !== false) {
                $not       = true;
                $separator = '!:';
            }

            list($fieldName, $expectedValue) = explode($separator, $value);

            $fieldValue      = (array) $this->form->getValue($fieldName);
            $valueValidation = (($not === false && in_array($expectedValue, $fieldValue))
                || ($not === true && !in_array($expectedValue, $fieldValue)));

            if ($glue === '') {
                if ((int) $key === (int) $valuesSum) {
                    return $valueValidation;
                }

                $conditionValid[$key] = $valueValidation;
            }

            if ($glue == 'and') {
                $isShown              = $conditionValid[$key - 1] && $valueValidation;
                $conditionValid[$key] = $isShown;
            }

            if ($glue == 'or') {
                $isShown              = $conditionValid[$key - 1] || $valueValidation;
                $conditionValid[$key] = $isShown;
            }
        }

        return $isShown;
    }
}
