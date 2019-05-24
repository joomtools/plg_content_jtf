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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * Form Field class for the Joomla Platform.
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class CategoryField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   3.0.0
	 */
	public $type = 'Category';

	/**
	 * Global application object
	 *
	 * @var     \Joomla\CMS\Application\CMSApplication
	 * @since   3.0.0
	 */
	protected $app = null;

	/**
	 * Method to get the field options for category
	 * Use the extension attribute in a form to specify the.specific extension for
	 * which categories should be displayed.
	 * Use the show_root attribute to specify whether to show the global category root in the list.
	 *
	 * @return  array    The field option objects.
	 *
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$options = array();
		$extension = $this->element['extension'] ? (string) $this->element['extension'] : (string) $this->element['scope'];
		$published = (string) $this->element['published'];
		$language  = (string) $this->element['language'];

		// Load the category options for a given extension.
		if (!empty($extension))
		{
			// Filter over published state or not depending upon if it is present.
			$filters = array();

			if ($published)
			{
				$filters['filter.published'] = explode(',', $published);
			}

			// Filter over language depending upon if it is present.
			if ($language)
			{
				$filters['filter.language'] = explode(',', $language);
			}

			if ($filters === array())
			{
				$options = HTMLHelper::_('category.options', $extension);
			}
			else
			{
				$options = HTMLHelper::_('category.options', $extension, $filters);
			}

			// Verify permissions.  If the action attribute is set, then we scan the options.
			if ((string) $this->element['action'])
			{
				// Get the current user object.
				$user = $this->app->getIdentity();

				foreach ($options as $i => $option)
				{
					/*
					 * To take save or create in a category you need to have create rights for that category
					 * unless the item is already in that category.
					 * Unset the option if the user isn't authorised for it. In this field assets are always categories.
					 */
					if ($user->authorise('core.create', $extension . '.category.' . $option->value) === false)
					{
						unset($options[$i]);
					}
				}
			}

			if (isset($this->element['show_root']))
			{
				array_unshift($options, HTMLHelper::_('select.option', '0', Text::_('JGLOBAL_ROOT')));
			}
		}
		else
		{
			Log::add(Text::_('JLIB_FORM_ERROR_FIELDS_CATEGORY_ERROR_EXTENSION_EMPTY'), Log::WARNING, 'jerror');
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
