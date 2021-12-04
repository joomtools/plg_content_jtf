<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2021 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Language\Text;

/* @var array $displayData */

$layout = new FileLayout('joomla.form.field.subform.repeatable_tmpl');
$layout->setSuffixes($displayData['tmpl']->framework);
$layout->addIncludePaths($displayData['tmpl']->layoutPaths);
$layout->setDebug($displayData['tmpl']->renderDebug);
?>

<div class="row">
	<div class="subform-repeatable-wrapper subform-layout">
		<div class="subform-repeatable"
			 data-bt-add="a.group-add-<?php echo $displayData['unique_subform_id']; ?>"
			 data-bt-remove="a.group-remove-<?php echo $displayData['unique_subform_id']; ?>"
			 data-bt-move="a.group-move-<?php echo $displayData['unique_subform_id']; ?>"
			 data-minimum="<?php echo $displayData['min']; ?>"
			 data-maximum="<?php echo $displayData['max']; ?>">

			<?php if (!empty($displayData['buttons']['add'])) : ?>
				<div class="btn-toolbar" role="toolbar">
					<div class="btn-group btn-group-sm">
						<a class="btn btn-mini button btn-success group-add-<?php echo $displayData['unique_subform_id']; ?>"
						   aria-label="<?php echo Text::_('JGLOBAL_FIELD_ADD'); ?>">
							<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
						</a>
					</div>
				</div>
			<?php endif; ?>

			<?php echo $layout->render( $displayData); ?>
		</div>
	</div>
</div>
