<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2018 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form\Field;

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Supports a text field telephone numbers.
 *
 * @link   http://www.w3.org/TR/html-markup/input.tel.html
 * @see    JFormRuleTel for telephone number validation
 * @see    JHtmlTel for rendering of telephone numbers
 * @since  1.7.0
 */
class TelephoneField extends TextField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Telephone';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $layout = 'joomla.form.field.tel';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		// Trim the trailing line in the layout file
		return rtrim($this->getRenderer($this->layout)->render($this->getLayoutData()), PHP_EOL);
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since 3.7.0
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		// Initialize some field attributes.
		$maxLength    = !empty($this->maxLength) ? ' maxlength="' . $this->maxLength . '"' : '';

		$extraData = array(
			'maxLength' => $maxLength,
		);

		return array_merge($data, $extraData);
	}
}