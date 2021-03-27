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
	FormHelper::loadFieldClass('list');
}

/**
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @since  __DEPLOY_VERSION__
 */
class ListField extends \JFormFieldList
{
	/**
	 * Name of the layout being used to render the field
	 *
	 * @var   string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $layout = 'joomla.form.field.list';

	use FormFieldExtension;

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getInput(): string
	{
		$data = $this->getLayoutData();

		$data['options'] = (array) $this->getOptions();

		return $this->getRenderer($this->layout)->render($data);
	}
}
