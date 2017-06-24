<?php
/**
 * @package          Joomla.Plugin
 * @subpackage       Content.jtf
 *
 * @author           Guido De Gobbis
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license          GNU General Public License version 3 or later
 **/

defined('JPATH_BASE') or die;

extract($displayData);

/**
 * Layout variables
 * ---------------------
 *    $options         : (array)  Optional parameters
 *    $label           : (string) The html code for the label (not required if $options['hiddenLabel'] is true)
 *    $input           : (string) The input field html code
 */

if (!empty($options['showonEnabled']))
{
	JHtml::_('jquery.framework');
	JHtml::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));
	JHtml::_('script', 'plugins/content/jtf/assets/js/showon.js', array('version' => 'auto'));
}

$container = array();

if (!empty($options['gridgroup']))
{
	$container[] = ' class="' . $options['gridgroup'] . '"';
}

if (!empty($options['rel']))
{
	$container[] = $options['rel'];
}
?>

<?php if (!empty($container)) : ?>
<div<?php echo implode(' ', $container); ?>>
<?php endif; ?>

	<?php if (empty($input)) : ?>

		<?php echo $label; ?>

	<?php else : ?>

		<?php if (empty($options['hiddenLabel'])) : ?>

			<?php if (!empty($options['gridlabel'])) : ?>
				<div class="<?php echo $options['gridlabel']; ?>">
			<?php endif; ?>

			<?php echo $label; ?>

			<?php if (!empty($options['gridlabel'])) : ?>
				</div>
			<?php endif; ?>

		<?php endif; ?>

		<?php if (!empty($options['gridfield'])) : ?>
			<div class="<?php echo $options['gridfield']; ?>">
		<?php endif; ?>

		<?php if (!empty($options['icon'])) : ?>
			<div class="input-prepend">
				<span class="add-on">
					<span class="<?php echo $options['icon']; ?>"></span>
				</span>
		<?php endif; ?>

			<?php echo $input; ?>

		<?php if (!empty($options['icon'])) : ?>
			</div>
		<?php endif; ?>

		<?php if (!empty($options['gridfield'])) : ?>
			</div>
		<?php endif; ?>

	<?php endif; ?>

<?php if (!empty($container)) : ?>
</div>
<?php endif; ?>
