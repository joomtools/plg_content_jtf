<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   System.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2018 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form\Field;

defined('JPATH_PLATFORM') or die;

use Jtf\Form\FormField;
use Joomla\CMS\Language\Text;

/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @link   http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since  1.7.0
 */
class NoteField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Note';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.7.3
	 */
	protected $layout = 'joomla.form.field.note';

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   11.1
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
	 * @since   11.1
	 */
	protected function getInput()
	{
		if (empty($this->layout))
		{
			throw new UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
		}

		return $this->getRenderer($this->layout)->render($this->getLayoutData());
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since   3.7.3
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		if (empty($data['label']) && empty($data['description']))
		{
			return '';
		}

		$heading = $this->element['heading'] ? (string) $this->element['heading'] : 'h4';
		$close = (string) $this->element['close'];

		$extraData = array(
			'heading' => $heading,
			'close'   => $close,
		);

		return array_merge($data, $extraData);
	}
}
