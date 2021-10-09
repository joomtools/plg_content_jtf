<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2021 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var   array   $options  Optional parameters
 * @var   string  $label    The html code for the label
 * @var   string  $input    The input field html code
 */

if (!empty($options['showonEnabled']))
{
	if (version_compare(JVERSION, 4, 'lt'))
	{
		HTMLHelper::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));
	}
	else
	{
		Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('showon');
	}

	HTMLHelper::_('script', 'plugins/content/jtf/assets/js/jtfShowon.min.js', array('version' => 'auto'), array('defer' => 'defer'));
}

$required         = $options['required'];
$description      = $options['description'];
$descriptionClass = $options['descriptionClass'];
$fieldMarker      = $options['fieldMarker'];
$fieldMarkerDesc  = $options['fieldMarkerDesc'];

$showFieldDescription = ($options['showFieldDescriptionAs'] == 'text' && !empty($description));
$showfieldMarkerDesc = !empty($fieldMarkerDesc) && (($required && $fieldMarker == 'required') || (!$required && $fieldMarker == 'optional'));

$container = array();

if (!empty($options['gridGroup']))
{
	$container[] = ' class="' . $options['gridGroup'] . '"';
}

if (!empty($options['rel']))
{
	$container[] = $options['rel'];
}

$gridLabel = '';

if (!empty($options['gridLabel']))
{
	$gridLabel = ' class="' . $options['gridLabel'] . '"';
}

$gridField = '';

if (!empty($options['gridField']))
{
	$gridField = ' class="' . $options['gridField'] . '"';
}
?>
<div<?php echo implode(' ', $container); ?>>

	<?php if (empty($input)) : ?>
		<div<?php echo $gridLabel; ?>>
			<?php echo $label; ?>
		</div>
		<?php if (!empty($gridField)) : ?>
			<div<?php echo $gridField; ?>></div>
		<?php endif; ?>
	<?php else : ?>

		<?php if (!empty($label)) : ?>
			<div<?php echo $gridLabel; ?>>
				<?php echo $label; ?>
			</div>
		<?php endif; ?>

		<?php if (!empty($gridField)) : ?>
			<div<?php echo $gridField; ?>>
		<?php endif; ?>

		<?php if (!empty($options['icon'])) : ?>
			<?php echo $this->sublayout(
				'icon_prepend',
				array(
					'icon'  => $options['icon'],
					'input' => $input,
				)
			); ?>
		<?php else : ?>
			<?php echo $input; ?>
		<?php endif; ?>

		<?php if ($showfieldMarkerDesc) : ?>
			<div id="<?php echo $options['id'] . '-marker'; ?>" class="marker <?php echo $descriptionClass; ?>">
				<small><?php echo $fieldMarkerDesc; ?></small><br/>
			</div>
		<?php endif; ?>

		<?php if ($showFieldDescription) : ?>
			<div id="<?php echo $options['id'] . '-desc'; ?>" class="description <?php echo $descriptionClass; ?>">
				<small><?php echo $description; ?></small>
			</div>
		<?php endif; ?>
		<?php if (!empty($gridField)) : ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
