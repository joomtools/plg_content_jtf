<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2021 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Provides radio button inputs
 *
 * @link   http://www.w3.org/TR/html-markup/command.radio.html#command.radio
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldRadio extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Radio';

	/**
	 * Name of the core layout being used as fallback
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $defaultLayout = 'joomla.form.field.radio';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $layout;

	/**
	 * Method to get the radio button field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getInput()
	{
		if (empty($this->defaultLayout))
		{
			throw new UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
		}

		if (version_compare(JVERSION, '4.0', 'ge'))
		{
			$this->defaultLayout = $this->defaultLayout . '.button';
		}

		// Set default layout if layout is not defined
		$this->layout = $this->layout ?: $this->defaultLayout;

		// Render field
		$field = $this->getRenderer($this->layout)->render($this->getLayoutData());

		if (!empty($field))
		{
			return $field;
		}

		// Return default layout
		return $this->getRenderer($this->defaultLayout)->render($this->getLayoutData());
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		$extraData = array(
			'options' => $this->getOptions(),
			'value'   => (string) $this->value,
		);

		return array_merge($data, $extraData);
	}
}
