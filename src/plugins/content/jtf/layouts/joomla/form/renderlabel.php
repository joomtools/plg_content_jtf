<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

/**
 * Layout variables
 * ---------------------
 *
 * @var   string   $text                    The label text
 * @var   string   $description             An optional description to use in a tooltip
 * @var   string   $for                     The id of the input this label is for
 * @var   boolean  $required                True if a required field
 * @var   array    $classes                 A list of classes
 * @var   string   $position                The tooltip position. Bottom for alias
 * @var   string   $fieldMarker             Field type to mark (required/optional)
 * @var   string   $fieldMarkerPlace        Place to set the field marker
 * @var   string   $showFieldDescriptionAs  Show description as tooltip or as text attached on the field
 */

$classes = array_filter((array) $classes);

$id    = $for . '-lbl';
$title = '';

if (!empty($description) && $showFieldDescriptionAs == 'tooltip') {
    if ($text && $text !== $description) {
        HTMLHelper::_('bootstrap.popover');
        $classes[] = 'hasPopover';
        $title     = ' title="' . htmlspecialchars(trim($text, ':')) . '"'
            . ' data-content="' . htmlspecialchars($description) . '"';

        if (!$position && Factory::getLanguage()->isRtl()) {
            $position = ' data-placement="left" ';
        }
    } else {
        HTMLHelper::_('bootstrap.tooltip');
        $classes[] = 'hasTooltip';
        $title     = ' title="' . HTMLHelper::_('tooltipText', trim($text, ':'), $description, 0) . '"';
    }
}

if ($required) {
    $classes[] = 'required';
}

?>
<label id="<?php echo $id; ?>"
	   for="<?php echo $for; ?>"
    <?php if (!empty($classes)) {
        echo ' class="' . implode(' ', $classes) . '"';
    } ?>
    <?php echo $title; ?>
    <?php echo $position; ?>>
    <?php echo $text; ?>
    <?php if ($fieldMarkerPlace == 'label') : ?>
        <?php if (($required && $fieldMarker == 'required') || (!$required && $fieldMarker == 'optional')) : ?>
			<span
				class="star marker">&#160;<?php echo Text::_('JTF_FIELD_MARKED_LABEL_' . strtoupper($fieldMarker)); ?></span>
        <?php endif; ?>
    <?php endif; ?>
</label>
