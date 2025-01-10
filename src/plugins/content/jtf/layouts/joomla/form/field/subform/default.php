<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2025 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use JoomTools\Plugin\Content\Jtf\Form\Form;
use JoomTools\Plugin\Content\Jtf\Framework\FrameworkHelper;

extract($displayData);

/**
 * Make thing clear
 *
 * @var   Form   $tmpl             The Empty form for template
 * @var   array   $forms            Array of JForm instances for render the rows
 * @var   bool    $multiple         The multiple state for the form field
 * @var   int     $min              Count of minimum repeating in multiple mode
 * @var   int     $max              Count of maximum repeating in multiple mode
 * @var   string  $fieldname        The field name
 * @var   string  $control          The forms control
 * @var   string  $label            The field label
 * @var   string  $description      The field description
 * @var   array   $buttons          Array of the buttons that will be rendered
 * @var   bool    $groupByFieldset  Whether group the subform fields by it`s fieldset
 */

$form = $forms[0];
$form = FrameworkHelper::setFrameworkClasses($forms[0]);

?>

<div class="subform-wrapper">
    <?php foreach ($form->getGroup('') as $field) : ?>
        <?php echo $field->renderField(); ?>
    <?php endforeach; ?>
</div>

