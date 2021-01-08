<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var   string  $id             Form attribute id and name.
 * @var   \JForm  $form           JForm object instance.
 * @var   string  $enctype        Set form attribute enctype, if file field is set.
 * @var   string  $formClass      Classes for the form.
 * @var   string  $frwkCss        Css styles needed for selected css-framework.
 * @var   string  $controlFields  Form hidden control fields.
 * @var   string  $fillouttime    Minimum time to wait before submit form.
 */

// Including fallback code for HTML5 non supported browsers.
// TODO Check if needed in joomla 4
HTMLHelper::_('jquery.framework');
HTMLHelper::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true));

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('script', 'plugins/content/jtf/assets/js/jtfDomIsReady.min.js', array('version' => 'auto'));
HTMLHelper::_('script', 'plugins/content/jtf/assets/js/jtfScrollToError.min.js', array('version' => 'auto'));

$role                         = '';
$invalidColor                 = '#ff0000';
$invalidBackgroundColor       = '#f2dede';
$frwk                         = $form->framework[0];
$showRequiredFieldDescription = $form->showRequiredFieldDescription;

if ($frwk == 'bs3')
{
	$role = ' role="form"';
}

$jsToAdd = "var jtfFrwk = '" . strtoupper($frwk) . "';";
$jsToAdd .= "if (typeof jtfTtf === 'undefined') {";
$jsToAdd .= "	var jtfTtf = {},";
$jsToAdd .= "		jtfBadgeClass = {};";
$jsToAdd .= "}";

if ($fillouttime > 0)
{
	$jtfBadgeClass           = array();
	$jtfBadgeClass['uikit3'] = 'uk-badge';
	$jtfBadgeClass['uikit']  = 'uk-badge uk-badge-notification';
	$jtfBadgeClass['bs4']    = 'label label-dark';
	$jtfBadgeClass['bs3']    = 'badge';
	$jtfBadgeClass['bs2']    = 'label label-inverse';
	$jtfBadgeClass['joomla'] = 'label label-inverse';

	$jsToAdd .= "jtfTtf." . $id . " = " . $fillouttime . ";";
	$jsToAdd .= "jtfBadgeClass." . $id . " = '" . $jtfBadgeClass[$frwk] . "';";

	HTMLHelper::_('script', 'plugins/content/jtf/assets/js/jtfTimeToFill.min.js', array('version' => 'auto'));
}

Factory::getDocument()->addScriptDeclaration($jsToAdd);

Factory::getDocument()->addStyleDeclaration("
	.hidden{display:none;visibility:hidden;}
	.jtfhp{position:absolute;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0);border:0!important;float:none!important;}
	.jtf .invalid:not(label):not(fieldset){border-color:" . $invalidColor . "!important;background-color:" . $invalidBackgroundColor . "!important;}
	.jtf .invalid,.jtf .invalid::placeholder{color:" . $invalidColor . ";}
	.jtf .invalid:-ms-input-placeholder{color:" . $invalidColor . ";}
	.jtf .invalid::-ms-input-placeholder{color:" . $invalidColor . ";}
	.jtf .marker{font-weight:bold;}
	.jtf [disabled]{pointer-events:none;}
	.jtf .inline{display:inline-block!important;line-height:150%;}"
);

?>
<div class="jtf contact-form">
	<form name="<?php echo $id; ?>"
		  id="<?php echo $id; ?>"
		  action="<?php echo Route::_("index.php"); ?>"
		  method="post"
		  class="<?php echo $formClass; ?>"
		<?php echo $role ?>
		<?php echo $enctype ?>
	>
		<?php if ($showRequiredFieldDescription) : ?>
			<p>
				<strong><?php echo $test = Text::sprintf('JTF_REQUIRED_FIELDS_LABEL', Text::_('JTF_FIELD_MARKED_LABEL_REQUIRED')); ?></strong>
			</p>
		<?php endif; ?>

		<?php foreach ($form->getFieldsets() as $fieldset) :
			$fieldsetClass = !empty($fieldset->class)
				? ' class="' . $fieldset->class . '"' : '';
			$fieldsetLabelClass = !empty($fieldset->labelclass)
				? ' class="' . $fieldset->labelclass . '"' : '';
			$fieldsetDescClass = !empty($fieldset->descriptionclass)
				? ' class="' . $fieldset->descriptionclass . '"' : ''; ?>

			<fieldset<?php echo $fieldsetClass; ?>>

				<?php if (!empty($fieldset->label)
					&& strlen($legend = trim(Text::_($fieldset->label)))
				) : ?>
					<legend<?php echo $fieldsetLabelClass; ?>><?php echo $legend; ?></legend>
				<?php endif; ?>

				<?php if (!empty($fieldset->description)
					&& strlen($desc = trim(Text::_($fieldset->description)))
				) : ?>
					<p<?php echo $fieldsetDescClass; ?>><?php echo $desc; ?></p>
				<?php endif; ?>

				<?php if (in_array($form->framework[0], array('uikit', 'uikit3'))) : ?>
				<div class="uk-grid" data-uk-grid-margin>
					<?php endif; ?>
					<?php foreach ($form->getFieldset($fieldset->name) as $field)
					{
						echo $field->renderField();
					}
					?>
					<?php if (in_array($form->framework[0], array('uikit', 'uikit3'))) : ?>
				</div>
			<?php endif; ?>

			</fieldset>
		<?php endforeach;

		// Set control fields to evaluate Form
		echo $controlFields;
		echo HTMLHelper::_('form.token');
		?>

	</form>
</div>
