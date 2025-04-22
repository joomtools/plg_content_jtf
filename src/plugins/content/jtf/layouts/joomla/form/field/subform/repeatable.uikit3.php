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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

/* @var   array  $displayData */

$layout = new FileLayout('joomla.form.field.subform.repeatable_tmpl');
$layout->setSuffixes($displayData['tmpl']->framework);
$layout->addIncludePaths($displayData['tmpl']->layoutPaths);
$layout->setDebug($displayData['tmpl']->rendererDebug);
?>

<div class="uk-grid">
	<div class="subform-repeatable-wrapper subform-layout uk-width-1-1">
		<joomla-field-subform class="subform-repeatable uk-grid"
							  name="<?php echo $displayData['name']; ?>"
							  button-add="a.group-add-<?php echo $displayData['unique_subform_id']; ?>"
							  button-remove="a.group-remove-<?php echo $displayData['unique_subform_id']; ?>"
							  button-move="a.group-move-<?php echo $displayData['unique_subform_id']; ?>"
							  repeatable-element=".subform-repeatable-group"
							  minimum="<?php echo $displayData['min']; ?>"
							  maximum="<?php echo $displayData['max']; ?>">

            <?php if (!empty($displayData['buttons']['add'])) : ?>
				<div class="uk-margin-bottom uk-width-1-1">
					<div class="uk-button-group">
						<a class="uk-button uk-button-small uk-button-primary group-add-<?php echo $displayData['unique_subform_id']; ?>"
						   aria-label="<?php echo Text::_('JGLOBAL_FIELD_ADD'); ?>">
							<span uk-icon="icon: plus"></span>
						</a>
					</div>
				</div>
            <?php endif; ?>

            <?php echo $layout->render($displayData); ?>
		</joomla-field-subform>
	</div>
</div>
