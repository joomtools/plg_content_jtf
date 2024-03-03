<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\Field\NoteField as JoomlaNoteField;
use Jtf\Form\FormFieldExtension;

/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @since  4.0.0
 */
class NoteField extends JoomlaNoteField
{
    /**
     * Name of the layout being used to render the field
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $layout = 'joomla.form.field.note';

    use FormFieldExtension {
        getLayoutData as traitGetLayoutData;
    }

    /**
     * Method to get the field label markup.
     *
     * @return  string  The field label markup.
     *
     * @since  4.0.0
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
     * @since  4.0.0
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
     * @since  4.0.0
     */
    protected function getLayoutData()
    {
        $data = $this->traitGetLayoutData();

        if (empty($data['label']) && empty($data['description'])) {
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
