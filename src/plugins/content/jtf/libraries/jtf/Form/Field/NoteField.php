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
	FormHelper::loadFieldClass('note');
}

/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @since  __DEPLOY_VERSION__
 */
class NoteField extends \JFormFieldNote
{
	/**
	 * Name of the layout being used to render the field
	 *
	 * @var   string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $layout = 'joomla.form.field.note';

	use FormFieldExtension
	{
		getLayoutData as traitGetLayoutData;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getLabel()
	{
		return '';
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getInput()
	{
		return $this->getRenderer($this->layout)->render($this->getLayoutData());
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getLayoutData()
	{
		$data = $this->traitGetLayoutData();

		if (empty($data['label']) && empty($data['description']))
		{
			return array();
		}

		$heading = $this->element['heading'] ? (string) $this->element['heading'] : 'h4';
		$close   = (string) $this->element['close'];

		$extraData = array(
			'heading' => $heading,
			'close'   => $close,
		);

		return array_merge($data, $extraData);
	}
}
