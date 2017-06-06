<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.jtf
 *
 * @author       Guido De Gobbis
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Provides an input field for files
 *
 * @link   http://www.w3.org/TR/html-markup/input.file.html#input.file
 * @since  11.1
 */
class JFormFieldFile extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'File';

	/**
	 * The accepted file type list.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	protected $accept;

	/**
	 * The icon shown for upload.
	 *
	 * @var    mixed
	 * @since  3.7.2
	 */
	protected $uploadicon = null;

	/**
	 * The max upload size.
	 *
	 * @var    mixed
	 * @since  3.7.2
	 */
	protected $uploadmaxsize = null;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.6
	 */
	protected $layout = 'joomla.form.field.file';

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'accept':
			case 'uploadicon':
			case 'uploadmaxsize':
				return $this->{$name};
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'accept':
			case 'uploadicon':
				$this->{$name} = (string) $value;
				break;

			case 'uploadmaxsize':
				$this->{$name} = (int) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->accept        = (string) $this->element['accept'];
			$this->uploadicon    = isset($this->element['uploadicon']) ? (string) $this->element['uploadicon'] : null;
			$this->uploadmaxsize = isset($this->element['uploadmaxsize']) ? (int) $this->element['uploadmaxsize'] : null;
		}

		return $return;
	}

	/**
	 * Method to get the field input markup for the file field.
	 * Field attributes allow specification of a maximum file size and a string
	 * of accepted file extensions.
	 *
	 * @return  string  The field input markup.
	 *
	 * @note    The field does not include an upload mechanism.
	 * @see     JFormFieldMedia
	 * @since   11.1
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
	 * @since   3.6
	 */
	protected function getLayoutData()
	{
		$data        = parent::getLayoutData();
		$mediaParams = JComponentHelper::getParams('com_media');

		if (empty($this->uploadmaxsize) && $this->uploadmaxsize !== 0)
		{
			$uploadmaxsize = $mediaParams->get('upload_maxsize', 0) * 1024 * 1024;
		}
		else
		{
			$uploadmaxsize = ((int) $this->uploadmaxsize) * 1024 * 1024;
		}

		if ($uploadmaxsize == 0)
		{
			$uploadmaxsize = JUtility::getMaxUploadSize();
		}

		$extraData = array(
			'accept'        => $this->accept,
			'multiple'      => $this->multiple,
			'uploadicon'    => $this->uploadicon,
			'uploadmaxsize' => $uploadmaxsize,
		);

		return array_merge($data, $extraData);
	}
}
