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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var   string   $autocomplete       Autocomplete attribute for the field.
 * @var   boolean  $autofocus          Is autofocus enabled?
 * @var   string   $class              Classes for the input.
 * @var   string   $description        Description of the field.
 * @var   boolean  $disabled           Is this field disabled?
 * @var   string   $group              Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden             Is this field hidden in the form?
 * @var   string   $hint               Placeholder for the field.
 * @var   string   $id                 DOM id of the field.
 * @var   string   $label              Label of the field.
 * @var   string   $labelclass         Classes to apply to the label.
 * @var   boolean  $multiple           Does this field support multiple values?
 * @var   string   $name               Name of the input field.
 * @var   string   $onchange           Onchange attribute for the field.
 * @var   string   $onclick            Onclick attribute for the field.
 * @var   string   $pattern            Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly           Is this field read only?
 * @var   boolean  $repeat             Allows extensions to duplicate elements.
 * @var   boolean  $required           Is this field required?
 * @var   integer  $size               Size attribute of the input.
 * @var   boolean  $spellcheck         Spellcheck state for the form field.
 * @var   string   $validate           Validation rules to apply.
 * @var   string   $value              Value attribute of the field.
 * @var   array    $checkedOptions     Options that will be set as checked.
 * @var   boolean  $hasValue           Has this field a value assigned?
 * @var   array    $options            Options available for this field.
 * @var   array    $inputType          Options available for this field.
 * @var   string   $accept             File types that are accepted.
 * @var   integer  $maxLength          The maximum length that the field shall accept.
 * @var   integer  $uploadMaxSize      Limitation for Upload.
 * @var   string   $uploadMaxSizeName  Unique name für max_filesize
 * @var   string   $uploadIcon         Icon for the draggable area.
 * @var   string   $buttonClass        Class for the button.
 * @var   string   $buttonIcon         Icon for the button.
 * @var   string   $framework          Framework used.
 */

$maxSize = HTMLHelper::_('number.bytes', $uploadMaxSize);

Text::script('JTF_JS_UPLOAD_ERROR_MESSAGE_SIZE');
Text::script('JTF_JS_UPLOAD_ERROR_FILE_NOT_ALLOWED');
Text::script('JTF_JS_UPLOAD_ALLOWED_FILES_EXT');
Text::script('JTF_JS_UPLOAD_LIST_WRAPPER_' . strtoupper($framework));
Text::script('JTF_JS_ERROR_WRAPPER_' . strtoupper($framework));

HTMLHelper::_('script', 'plg_content_jtf/jtfLite2.min.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('script', 'plg_content_jtf/jtfUploadFile.min.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'plg_content_jtf/jtfUploadFile.min.css', ['version' => 'auto', 'relative' => true]);

?>
<div class="uploader-wrapper">
	<div class="dragarea">
		<div class="dragarea-content">
			<p class="upload-icon">
                <?php echo $this->sublayout('icon', array('icon' => $uploadIcon)); ?>
			</p>
			<p class="lead">
                <?php echo Text::_('JTF_DRAG_FILE_HERE'); ?>
			<noscript class="invalid"><br/><?php echo Text::_('JTF_DRAG_FILE_HERE_NOSCRIPT'); ?></noscript>
			</p>
			<p>
				<button type="button" class="<?php echo $buttonClass; ?> select-file-button">
                    <?php echo $this->sublayout('icon', array('icon' => $buttonIcon)); ?>
                    <?php echo Text::_('JTF_SELECT_FILE'); ?>
				</button>
			</p>
			<p class="maxUploadSize">
                <?php echo Text::sprintf('JTF_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?>
			</p>
			<p class="allowedExt"></p>
		</div>
		<div class="legacy-uploader">
			<input type="hidden" class="file-uplaoder" name="<?php echo $uploadMaxSizeName; ?>"
				   value="<?php echo $uploadMaxSize; ?>">
			<input type="file"
				   name="<?php echo $name; ?>"
				   id="<?php echo $id; ?>"
				   class="file-uplaoder validate-file<?php echo !empty($class) ? ' ' . $class : ''; ?>"
				   draggable="true"
                <?php echo !empty($size) ? ' size="' . $size . '"' : ''; ?>
                <?php echo !empty($accept) ? ' accept="' . $accept . '"' : ''; ?>
                <?php echo !empty($multiple) ? ' multiple' : ''; ?>
                <?php echo $disabled ? ' disabled' : ''; ?>
                <?php echo $autofocus ? ' autofocus' : ''; ?>
                <?php echo !empty($onchange) ? ' onchange="' . $onchange . '"' : ''; ?>
                <?php echo $required ? ' required aria-required="true"' : ''; ?> />
		</div>
		<div class="upload-list"></div>
	</div>
</div>

