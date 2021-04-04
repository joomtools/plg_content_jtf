<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2021 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormHelper;
use Jtf\Form\Form;
use Jtf\Form\FormFieldExtension;

if (version_compare(JVERSION, '4', 'lt'))
{
	FormHelper::loadFieldClass('subform');
}

/**
 * The Field to load the form inside current form
 *
 * @Example with all attributes:
 * 	<field name="field-name" type="subform"
 * 		formsource="path/to/form.xml" min="1" max="3" multiple="true" buttons="add,remove,move"
 * 		layout="joomla.form.field.subform.repeatable-table" groupByFieldset="false" component="com_example" client="site"
 * 		label="Field Label" description="Field Description" />
 *
 * @since  3.0
 */
class SubformField extends \JFormFieldSubform
{
	use FormFieldExtension {
		__set as traitSet;
		setup as traitSetup;
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'groupByFieldset':
			case 'layout':
				break;

			default:
				$this->traitSet($name, $value);
		}
	}

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null): bool
	{
		if (!$this->traitSetup($element, $value, $group))
		{
			return false;
		}

		$attributes = array(
			'formsource',
			'min',
			'max',
			'layout',
			'groupByFieldset',
			'buttons',
		);

		foreach ($attributes as $attributeName)
		{
			$this->__set($attributeName, $element[$attributeName]);
		}

		if ($this->value && is_string($this->value))
		{
			// Guess here is the JSON string from 'default' attribute
			$this->value = json_decode($this->value, true);
		}

		if (!$this->formsource && $element->form)
		{
			// Set the formsource parameter from the content of the node
			$this->formsource = $element->form->saveXML();
		}

		// Set default value depend from "multiple" mode
		$this->layout = !$this->multiple ? 'joomla.form.field.subform.default' : 'joomla.form.field.subform.repeatable';

		return true;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getInput(): string
	{
		// Prepare data for renderer
		$data    = $this->getLayoutData();
		$tmpl    = null;
		$control = $this->name;

		try
		{
			$tmpl  = $this->loadSubForm();
			$forms = $this->loadSubFormData($tmpl);
		}
		catch (\Exception $e)
		{
			return $e->getMessage();
		}

		$data['tmpl']            = $tmpl;
		$data['forms']           = $forms;
		$data['min']             = $this->min;
		$data['max']             = $this->max;
		$data['control']         = $control;
		$data['buttons']         = $this->buttons;
		$data['fieldname']       = $this->fieldname;
		$data['groupByFieldset'] = $this->groupByFieldset;

		/**
		 * For each rendering process of a subform element, we want to have a
		 * separate unique subform id present to could distinguish the eventhandlers
		 * regarding adding/moving/removing rows from nested subforms from their parents.
		 */
		static $uniqueSubformId = 0;
		$data['unique_subform_id'] = ('sr-' . ($uniqueSubformId++));

		// Prepare renderer
		$renderer = $this->getRenderer($this->layout);

		// Allow to define some JLayout options as attribute of the element
		if ($this->element['component'])
		{
			$renderer->setComponent((string) $this->element['component']);
		}

		if ($this->element['client'])
		{
			$renderer->setClient((string) $this->element['client']);
		}

		// Render
		$html = $renderer->render($data);

		// Add hidden input on front of the subform inputs, in multiple mode
		// for allow to submit an empty value
		if ($this->multiple)
		{
			$html = '<input name="' . $this->name . '" type="hidden" value="" />' . $html;
		}

		return $html;
	}

	/**
	 * Loads the Form instance for the subform.
	 *
	 * @return  Form  The form instance.
	 *
	 * @throws  \InvalidArgumentException if no form provided.
	 * @throws  \RuntimeException if the form could not be loaded.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function loadSubForm()
	{
		$control       = $this->name;

		if ($this->multiple)
		{
			$control .= '[' . $this->fieldname . 'X]';
		}

		// Prepare the form template
		$formname                           = $this->form->getName() . '.subform.' . ($this->group ? $this->group . '.' : '') . $this->fieldname;
		$tmpl                               = Form::getInstance($formname, $this->formsource, array('control' => $control));
		$tmpl->layoutPaths                  = !empty($this->form->layoutPaths) ? $this->form->layoutPaths : array();
		$tmpl->framework                    = !empty($this->form->framework) ? $this->form->framework : array();
		$tmpl->renderDebug                  = !empty($this->form->rendererDebug) ? $this->form->rendererDebug : false;
		$tmpl->fieldmarker                  = $this->form->fieldmarker;
		$tmpl->fieldmarkerplace             = $this->form->fieldmarkerplace;
		$tmpl->showfielddescriptionas       = $this->form->showfielddescriptionas;
		$tmpl->showRequiredFieldDescription = $this->form->showRequiredFieldDescription;

		return $tmpl;
	}

	/**
	 * Binds given data to the subform and its elements.
	 *
	 * @param   Form  $subForm  Form instance of the subform.
	 *
	 * @return  Form[]  Array of Form instances for the rows.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function loadSubFormData(Form &$subForm): array
	{
		$value         = $this->value ? (array) $this->value : array();

		// Simple form, just bind the data and return one row.
		if (!$this->multiple)
		{
			$subForm->bind($value);

			return array($subForm);
		}

		// Multiple rows possible: Construct array and bind values to their respective forms.
		$forms = array();
		$value = array_values($value);

		// Show as many rows as we have values, but at least min and at most max.
		$c = max($this->min, min(count($value), $this->max));

		for ($i = 0; $i < $c; $i++)
		{
			$control                                = $this->name . '[' . $this->fieldname . $i . ']';
			$itemForm                               = Form::getInstance($subForm->getName() . $i, $this->formsource, array('control' => $control));
			$itemForm->layoutPaths                  = !empty($this->form->layoutPaths) ? $this->form->layoutPaths : array();
			$itemForm->framework                    = !empty($this->form->framework) ? $this->form->framework : array();
			$itemForm->renderDebug                  = !empty($this->form->rendererDebug) ? $this->form->rendererDebug : false;
			$itemForm->fieldmarker                  = $this->form->fieldmarker;
			$itemForm->fieldmarkerplace             = $this->form->fieldmarkerplace;
			$itemForm->showfielddescriptionas       = $this->form->showfielddescriptionas;
			$itemForm->showRequiredFieldDescription = $this->form->showRequiredFieldDescription;

			if (!empty($value[$i]))
			{
				$itemForm->bind($value[$i]);
			}

			$forms[] = $itemForm;
		}

		return $forms;
	}
}
