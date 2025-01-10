<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2025 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Form\Field\ListField;

/**
 * List of supported frameworks
 *
 * @since   4.0.0
 */
class JFormFieldFrwk extends ListField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   4.0.0
     */
    protected $type = 'Frwk';

    /**
     * @var     array
     * @since   4.0.0
     */
    protected $exclude = array(
        'FrameworkHelper',
    );

    /**
     * Method to get the field options.
     *
     * @return   array  The field option objects.
     * @since    3.0.0
     */
    protected function getOptions()
    {
        $frwkPath = JPATH_PLUGINS . '/content/jtf/src/Framework';
        $frwk     = Folder::files($frwkPath);

        $options = array();

        foreach ($frwk as $file) {
            $fileName = File::stripExt($file);

            if (in_array($fileName, $this->exclude)) {
                continue;
            }

            $framework    = 'JoomTools\\Plugin\\Content\\Jtf\\Framework\\' . ucfirst($fileName);
            $fileRealName = $framework::$name;

            $tmp = array(
                'value' => strtolower($fileName),
                'text'  => $fileRealName,
            );

            // Add the option object to the result set.
            $options[] = (object) $tmp;
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form
     *                                       field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for
     *                                       the field. For example if the field has name="foo" and the group value is
     *                                       set to "bar" then the full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @since   1.7.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        parent::setup($element, $value, $group);

        if (empty($this->value)) {
            $this->value = 'bs5';
        }

        return true;
    }
}
