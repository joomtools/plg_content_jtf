<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Supports a submit button.
 *
 * @since  11.1
 */
class JFormFieldSubmit extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Submit';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $layout = 'joomla.form.field.submit';


	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   11.1
	 */
	protected function getLabel()
	{
		$data = parent::getLayoutData();

		if ($data['label'] == $this->fieldname)
		{
			$data['label'] = JText::_('JSUBMIT');
		}

		// Here mainly for B/C with old layouts. This can be done in the layouts directly
		$labelData = array(
			'text'        => $data['label'],
			'description' => null,
			'for'         => $data['id'],
			'required'    => false,
			'classes'     => explode(' ', $data['labelclass']),
			'position'    => false,
		);

		$labelData['classes'][] = 'hidden';

		return $this->getRenderer($this->renderLabelLayout)->render($labelData);
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
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
	 * @since 3.7.0
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		if ($data['label'] == $this->fieldname)
		{
			$data['label'] = JText::_('JSUBMIT');
		}

		return $data;
	}
}
