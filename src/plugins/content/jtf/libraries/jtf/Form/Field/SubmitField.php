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

use Joomla\CMS\Form\FormField;
use Jtf\Form\FormFieldExtension;
use Joomla\CMS\Language\Text;

/**
 * Form Field class for the Joomla Platform.
 * Supports a submit button.
 *
 * @since  3.0.0
 */
class SubmitField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $type = 'Submit';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $layout = 'joomla.form.field.submit';

	use FormFieldExtension {
		getLayoutData as traitGetLayoutData;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since  3.0.0
	 */
	protected function getLabel(): string
	{
		$data = $this->traitGetLayoutData();

		if ($data['label'] == $this->fieldname)
		{
			$data['label'] = Text::_('JSUBMIT');
		}

		// Here mainly for B/C with old layouts. This can be done in the layouts directly
		$labelData = array(
			'text'                   => $data['label'],
			'description'            => null,
			'for'                    => $data['id'],
			'required'               => false,
			'classes'                => explode(' ', $data['labelclass']),
			'position'               => false,
			'fieldMarker'            => $data['fieldMarker'],
			'fieldMarkerPlace'       => $data['fieldMarkerPlace'],
			'showFieldDescriptionAs' => $data['showFieldDescriptionAs'],
		);

		return $this->getRenderer($this->renderLabelLayout)->render($labelData);
	}
}
