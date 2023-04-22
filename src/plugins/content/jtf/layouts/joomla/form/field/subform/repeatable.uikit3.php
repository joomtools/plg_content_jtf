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
		<div class="subform-repeatable uk-grid"
			 data-bt-add="a.group-add-<?php echo $displayData['unique_subform_id']; ?>"
			 data-bt-remove="a.group-remove-<?php echo $displayData['unique_subform_id']; ?>"
			 data-bt-move="a.group-move-<?php echo $displayData['unique_subform_id']; ?>"
			 data-minimum="<?php echo $displayData['min']; ?>"
			 data-maximum="<?php echo $displayData['max']; ?>">

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
		</div>
	</div>
</div>
