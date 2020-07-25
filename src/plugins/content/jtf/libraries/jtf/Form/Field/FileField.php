<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Utility\Utility;
use Jtf\Form\FormFieldExtension;

if (version_compare(JVERSION, '4', 'lt'))
{
	FormHelper::loadFieldClass('file');
}

/**
 * Form Field class for the Joomla Platform.
 * Provides an input field for files
 *
 * @link   http://www.w3.org/TR/html-markup/input.file.html#input.file
 * @since  3.0.0
 */
class FileField extends \JFormFieldFile
{
	/**
	 * The icon shown for upload.
	 *
	 * @var    mixed
	 * @since  3.0.0
	 */
	protected $uploadicon = null;

	/**
	 * The max upload size.
	 *
	 * @var    mixed
	 * @since  3.0.0
	 */
	protected $uploadmaxsize = null;

	use FormFieldExtension
	{
		getLayoutData as traitGetLayoutData;
		__get as traitGet;
		__set as traitSet;
		setup as traitSetup;
	}

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since  3.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'uploadicon':
			case 'uploadmaxsize':
				return $this->{$name};

			default:
				return $this->traitGet($name);
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
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'uploadicon':
			case 'uploadmaxsize':
				$this->{$name} = (string) $value;
				break;

			default:
				$this->traitSet($name, $value);
		}
	}

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.0.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		if (!$this->traitSetup($element, $value, $group))
		{
			return false;
		}

		$attributes = array(
			'uploadicon',
			'uploadmaxsize',
		);

		foreach ($attributes as $attributeName)
		{
			$this->__set($attributeName, $element[$attributeName]);
		}

		return true;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.0.0
	 */
	protected function getInput()
	{
		// Switch the layouts
		$this->layout = $this->control === 'simple' ? $this->layout . '.simple' : $this->layout;

		return $this->getRenderer($this->layout)->render($this->getLayoutData());
	}


	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return   array
	 * @since   3.0
	 */
	protected function getLayoutData()
	{
		$data = $this->traitGetLayoutData();

		if (empty($this->uploadmaxsize) && $this->uploadmaxsize !== 0)
		{
			$uploadMaxSize = ComponentHelper::getParams('com_media')->get('upload_maxsize', 0) * 1024 * 1024;
		}
		else
		{
			$uploadMaxSize = number_format((float) $this->uploadmaxsize, 2) * 1024 * 1024;
		}

		if ($uploadMaxSize === 0)
		{
			$uploadMaxSize = Utility::getMaxUploadSize();
		}

		if (strpos($data['name'], '][]') === false)
		{
			$data['name'] = $data['name'] . '[]';
		}

		$name = str_replace('][]', ']', $data['name']);

		$uploadMaxSizeName = $name . '[max_file_size]';
		$extraData         = array(
			'uploadIcon'        => $this->uploadicon,
			'uploadMaxSize'     => $uploadMaxSize,
			'uploadMaxSizeName' => $uploadMaxSizeName,
		);

		return array_merge($data, $extraData);
	}
}
