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
use Jtf\Form\FormFieldExtension;

if (version_compare(JVERSION, '4', 'lt'))
{
	FormHelper::loadFieldClass('checkbox');
}

/**
 * Form Field class for the Joomla Platform.
 * Single checkbox field.
 * This is a boolean field with null for false and the specified option for true
 *
 * @since  3.0.0
 */
class CheckboxField extends \JFormFieldCheckbox
{
	/**
	 * Name of the layout being used to render the field
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $layout = 'joomla.form.field.checkbox';

	use FormFieldExtension {
		getLayoutData as traitGetLayoutData;
	}

	/**
	 * Method to get the field input markup.
	 * The checked element sets the field to selected.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.0.0
	 */
	protected function getInput()
	{
		if (empty($this->layout))
		{
			throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
		}

		return $this->getRenderer($this->layout)->render($this->getLayoutData());
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since   3.0.0
	 */
	protected function getLayoutData()
	{
		$data            = $this->traitGetLayoutData();
		$data['value']   = $this->default ?: '1';
		$data['checked'] = $this->checked || $this->value;

		return $data;
	}
}
