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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var   string  $autocomplete      Autocomplete attribute for the field.
 * @var   boolean $autofocus         Is autofocus enabled?
 * @var   string  $class             Classes for the input.
 * @var   string  $description       Description of the field.
 * @var   boolean $disabled          Is this field disabled?
 * @var   string  $group             Group the field belongs to. <fields> section in form XML.
 * @var   boolean $hidden            Is this field hidden in the form?
 * @var   string  $hint              Placeholder for the field.
 * @var   string  $id                DOM id of the field.
 * @var   string  $label             Label of the field.
 * @var   string  $labelclass        Classes to apply to the label.
 * @var   boolean $multiple          Does this field support multiple values?
 * @var   string  $name              Name of the input field.
 * @var   string  $onchange          Onchange attribute for the field.
 * @var   string  $onclick           Onclick attribute for the field.
 * @var   string  $pattern           Pattern (Reg Ex) of value of the form field.
 * @var   boolean $readonly          Is this field read only?
 * @var   boolean $repeat            Allows extensions to duplicate elements.
 * @var   boolean $required          Is this field required?
 * @var   integer $size              Size attribute of the input.
 * @var   boolean $spellcheck        Spellcheck state for the form field.
 * @var   string  $validate          Validation rules to apply.
 * @var   string  $value             Value attribute of the field.
 * @var   array   $checkedOptions    Options that will be set as checked.
 * @var   boolean $hasValue          Has this field a value assigned?
 * @var   array   $options           Options available for this field.
 * @var   array   $inputType         Options available for this field.
 * @var   array   $spellcheck        Options available for this field.
 * @var   string  $accept            File types that are accepted.
 * @var   integer $uploadMaxSize     Limitation for Upload.
 * @var   string  $uploadInfoClass   The class for the upload info box.
 * @var   string  $uploadMaxSizeName Unique name für max_filesize
 * @var   string  $framework         Framework used.
 */

$maxSize         = HTMLHelper::_('number.bytes', $uploadMaxSize);
$uploadInfoClass = !empty($uploadInfoClass) ? ' ' . $uploadInfoClass : null;

Text::sprintf('JTF_JS_UPLOAD_ERROR_MESSAGE_SIZE', $maxSize, array('jsSafe' => true, 'interpretBackSlashes' => true, 'script' => true));
Text::script('JTF_JS_UPLOAD_ERROR_FILE_NOT_ALLOWED', true);
Text::script('JTF_JS_UPLOAD_ALLOWED_FILES_EXT', true);
Text::script('JTF_JS_UPLOAD_LIST_WRAPPER_' . strtoupper($framework));
Text::script('JTF_JS_ERROR_WRAPPER_' . strtoupper($framework));

HTMLHelper::_('script', 'plugins/content/jtf/assets/js/jtfLite2.min.js', array('version' => 'auto'));
HTMLHelper::_('script', 'plugins/content/jtf/assets/js/jtfUploadFile.min.js', array('version' => 'auto'));
HTMLHelper::_('stylesheet', 'plugins/content/jtf/assets/css/jtfUploadFile.min.css', array('version' => 'auto'));

?>
<div class="uploader-wrapper">
	<div class="upload-info<?php echo $uploadInfoClass; ?>" role="alert">
		<p class="maxUploadSize">
			<?php echo Text::sprintf('JTF_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?>
		</p>
		<p class="allowedExt"></p>
	</div>
	<p>
		<input type="hidden" class="file-uplaoder" name="<?php echo $uploadMaxSizeName; ?>"
			   value="<?php echo $uploadMaxSize; ?>">
		<input type="file"
			   name="<?php echo $name; ?>"
			   id="<?php echo $id; ?>"
			   class="file-uplaoder validate-file<?php echo !empty($class) ? ' ' . $class : ''; ?>"
			<?php echo !empty($size) ? ' size="' . $size . '"' : ''; ?>
			<?php echo !empty($accept) ? ' accept="' . $accept . '"' : ''; ?>
			<?php echo !empty($multiple) ? ' multiple' : ''; ?>
			<?php echo $disabled ? ' disabled' : ''; ?>
			<?php echo $autofocus ? ' autofocus' : ''; ?>
			<?php echo !empty($onchange) ? ' onchange="' . $onchange . '"' : ''; ?>
			<?php echo $required ? ' required aria-required="true"' : ''; ?> />
	</p>
	<div class="upload-list"></div>
</div>
