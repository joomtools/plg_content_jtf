<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @since    3.8
 */
class PlgContentJtf extends CMSPlugin
{
	/**
	 * The regular expression to identify Plugin call.
	 *
	 * @var     string
	 * @since   1.0
	 */
	const PLUGIN_REGEX = "@(<(\w+)[^>]*>|){jtf(\s.*)?}(</\\2>|)@";

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var     boolean
	 * @since   3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Global application object
	 *
	 * @var     JApplication
	 * @since   11.1
	 */
	protected $app = null;

	/**
	 * Set counter
	 *
	 * @var     int
	 * @since   1.0
	 */
	private static $count = 0;

	/**
	 * @var     stdClass
	 * @since   1.0
	 */
	private $doNotLoad;

	/**
	 * Set captcha name
	 *
	 * @var     string
	 * @since   1.0
	 */
	private $issetCaptcha;

	/**
	 * Set result of captcha validation
	 *
	 * @var     boolean
	 * @since   1.0
	 */
	private $validCaptcha = true;

	/**
	 * Set Joomla\CMS\Form object
	 *
	 * @var     Joomla\CMS\Form
	 * @since   1.0
	 */
	private $form = null;

	/**
	 * JFormField validation
	 *
	 * @var     boolean
	 * @since   1.0
	 */
	private $validField = true;

	/**
	 * Array with JFormField Names of submitted Files
	 *
	 * @var     array
	 * @since   1.0
	 */
	private $fileFields = array();

	/**
	 * Array with submitted Files
	 *
	 * @var     array
	 * @since   1.0
	 */
	private $submitedFiles = array();

	/**
	 * Array with User params
	 *
	 * @var     array
	 * @since   1.0
	 */
	private $uParams = array();

	/**
	 * Debug
	 *
	 * @var     boolean
	 * @since   1.0
	 */
	private $debug = false;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   1.5
	 */
	public function __construct($subject, array $config = array())
	{
		parent::__construct($subject, $config);

		$option = Factory::getApplication()->input->getCmd('option');
		$layout = Factory::getApplication()->input->getCmd('layout');

		$this->doNotLoad = new stdClass();
		$this->doNotLoad->active = false;
		$this->doNotLoad->extension = $option;

		if (Factory::getApplication()->isClient('administrator')
			|| in_array($option, array('com_config', 'com_contact', 'com_finder',))
			|| $layout == 'edit'
		)
		{
			$this->doNotLoad->active = true;
		}

		if (!$this->doNotLoad->active)
		{
			JLoader::register('JFormField', JPATH_PLUGINS . '/content/jtf/libraries/form/FormField.php', true);
			JLoader::register('FormField', JPATH_PLUGINS . '/content/jtf/libraries/Form/FormField.php', true);
			JLoader::register('Joomla\CMS\Form\FormField', JPATH_PLUGINS . '/content/jtf/libraries/Form/FormField.php', true);
		}
	}

	/**
	 * Plugin to generates Forms within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @param   mixed    $params   The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return   void
	 * @since    1.6
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Don't run in administration Panel or when the content is being indexed
		if (strpos($article->text, '{jtf') === false
			|| $context == 'com_finder.indexer'
			|| $this->doNotLoad->extension == 'com_config'
			|| $this->app->input->getCmd('layout') == 'edit')
		{
			return;
		}

		$this->debug = (boolean) $this->params->get('debug', 0);
		$cIndex      =& self::$count;
		$lang        = Factory::getLanguage();
		$langTag     = $lang->getTag();

		// Get all matches or return
		if (!preg_match_all(self::PLUGIN_REGEX, $article->text, $matches))
		{
			return;
		}

		$code = array_keys($matches[1], '<code>');
		$pre = array_keys($matches[1], '<pre>');

		if (!empty($code) || !empty($pre))
		{
			array_walk($matches,
				function (&$array, $key, $tags) {
					foreach ($tags as $tag)
					{
						if ($tag !== null && $tag !== false)
						{
							unset($array[$tag]);
						}
					}
				},
				array_merge($code, $pre)
			);
		}

		$pluginReplacements = $matches[0];
		$userParams         = $matches[3];

		// Do not load if not permitted extansion ist load too.
		if (!empty($pluginReplacements) && $this->doNotLoad->active)
		{
			$this->app->enqueueMessage(Text::sprintf('JTF_CAN_NOT_LOAD', $this->doNotLoad->extension), 'notice');
			return;
		}

		JLoader::register('JTFForm', JPATH_PLUGINS . '/content/jtf/libraries/form/form.php', true);
		JLoader::discover('JTFFramework', JPATH_PLUGINS . '/content/jtf/libraries/frameworks', true);

		// Add form fields
		JFormHelper::addFieldPath(JPATH_PLUGINS . '/content/jtf/libraries/form/fields');

		// Add form rules
		JFormHelper::addRulePath(JPATH_PLUGINS . '/content/jtf/libraries/form/rules');

		foreach ($pluginReplacements as $rKey => $replacement)
		{
			// Clear html replace
			$html = '';

			$this->resetUserParams();
			$this->loadLanguage('jtf_global', JPATH_PLUGINS . '/content/jtf/assets');

			if (!empty($userParams[$rKey]))
			{
				$vars = explode('|', $userParams[$rKey]);

				// Set user params
				$this->setUserParams($vars);
			}

			// Set form counter as index
			$this->uParams['index'] = (int) $cIndex;

			$formTheme = $this->uParams['theme'] . (int) $cIndex;

			// Get form submit task
			$formSubmitted = ($this->app->input->get('task', false, 'post') == $formTheme . "_sendmail") ? true : false;

			$formXmlPath = $this->getFieldsFile();

			if (!empty($formXmlPath))
			{
				$formLang = dirname(
					dirname(
						dirname(
							$this->getLanguagePath('language/' . $langTag . '/' . $langTag . '.jtf_theme.ini')
						)
					)
				);

				$lang->load('jtf_theme', $formLang);
				$this->setSubmit();

				if ($formSubmitted)
				{
					$submitValues = $this->getTranslatedSubmittedFormValues();

					$this->getForm()->bind($submitValues);

					$startTime = $this->app->input->getFloat('start');
					$fillOutTime = microtime(1) - $startTime;

					$notSpamBot = $fillOutTime > $this->uParams['fillouttime'] ? true : false;

					if ($submitValues['jtf_important_notices'] == '' && $notSpamBot)
					{
						$valid = $this->validate();
					}
					else
					{
						$this->app->redirect(JRoute::_('index.php', false));
					}

					if ($valid)
					{
						$sendmail = $this->sendMail();

						if ($sendmail)
						{
							$this->app->enqueueMessage(Text::_('JTF_EMAIL_THANKS'), 'message');
							$this->app->redirect(JRoute::_('index.php', false));
						}
					}
				}
			}

			$html .= $this->getTmpl('form');

			$pos = strpos($article->text, $replacement);
			$end = strlen($replacement);

			$article->text = substr_replace($article->text, $html, $pos, $end);
			$cIndex++;
		}
	}

	/**
	 * Reset user Params to default
	 *
	 * @return   void
	 * @since    1.0
	 */
	private function resetUserParams()
	{
		$this->uParams = array();
		$this->form    = null;

		$this->uParams['startTime'] = microtime(1);

		// Set minimum fillout time
		$this->uParams['fillouttime'] = $this->params->get('filloutTime', 16);

		// Set default captcha value
		$this->uParams['captcha'] = $this->params->get('captcha');

		// Clear recipient
		$this->uParams['mailto'] = null;

		// Clear cc
		$this->uParams['cc'] = null;

		// Clear bcc
		$this->uParams['bcc'] = null;

		// Clear visitor_name
		$this->uParams['visitor_name'] = null;

		// Clear visitor_email
		$this->uParams['visitor_email'] = null;

		// Clear subject
		$this->uParams['subject'] = null;

		// Set default theme
		$this->uParams['theme'] = 'default';

		// Set time to clear uploads
		$this->uParams['file_clear'] = (int) $this->params->get('file_clear', 30);

		// Set path in images to save uploaded files
		$this->uParams['file_path'] = (int) $this->params->get('file_path', 'uploads');


		// Set default framework value
		$this->uParams['framework'] = array($this->params->get('framework'));
	}

	/**
	 * Set user Params
	 *
	 * @param   array $vars Params pairs from Plugin call
	 *
	 * @since    3.8
	 */
	private function setUserParams($vars)
	{
		$uParams = array();

		if (!empty($vars))
		{
			foreach ($vars as $var)
			{
				list($key, $value) = explode('=', trim($var));

				$key = strtolower($key);

				if ($key == 'framework')
				{
					$uParams[trim($key)] = array(trim($value));
				}
				else
				{
					$uParams[trim($key)] = trim($value);
				}
			}
		}

		// Merge user params width default params
		$this->uParams = array_merge($this->uParams, $uParams);
	}

	/**
	 * Checks if all needed files for Forms are found
	 *
	 * @return   string|boolean
	 * @since    1.0
	 */
	private function getFieldsFile()
	{
		$template  = $this->app->getTemplate();
		$framework = !empty($this->uParams['framework'][0]) ? '.' . $this->uParams['framework'][0] : '';
		$file      = 'fields' . $framework . '.xml';

		$formPath = array(
			JPATH_THEMES . '/' . $template . '/html/plg_content_jtf/' . $this->uParams['theme'],
			JPATH_PLUGINS . '/content/jtf/tmpl/' . $this->uParams['theme']
		);

		foreach ($formPath as $path)
		{
			if (file_exists($path . '/' . $file))
			{
				return $path . '/' . $file;
			}

			if (file_exists($path . '/fields.xml'))
			{
				return $path . '/fields.xml';
			}
		}

		$this->app->enqueueMessage(
			Text::sprintf('JTF_THEME_ERROR', $this->uParams['theme']),
			'error'
		);

		return false;
	}

	private function getLanguagePath($filename)
	{
		$template = $this->app->getTemplate();

		// Build template override path for theme
		$tAbsPath = JPATH_THEMES . '/' . $template
			. '/html/plg_content_jtf/'
			. $this->uParams['theme'];

		// Build plugin path for theme
		$bAbsPath = JPATH_PLUGINS . '/content/jtf/tmpl/'
			. $this->uParams['theme'];

		// Set the right theme path
		if (file_exists($tAbsPath . '/' . $filename))
		{
			return $tAbsPath . '/' . $filename;
		}

		if (file_exists($bAbsPath . '/' . $filename))
		{
			return $bAbsPath . '/' . $filename;
		}

		return false;
	}

	private function setSubmit()
	{
		$form           = $this->getForm();
		$captcha        = array();
		$button         = array();
		$submitFieldset = $form->getFieldset('submit');

		if (!empty($submitFieldset))
		{
			if (!empty($this->issetField('captcha', 'submit')))
			{
				$captcha['submit'] = $this->issetField('captcha', 'submit');
			}

			if (!empty($this->issetField('submit', 'submit')))
			{
				$button['submit']  = $this->issetField('submit', 'submit');
			}
		}
		else
		{
			$form->setField(new SimpleXMLElement('<fieldset name="submit"></fieldset>'));
			$captcha = $this->issetField('captcha');
			$button  = $this->issetField('submit');
		}

		$this->setCaptcha($captcha);
		$this->setSubmitButton($button);
	}

	private function getForm()
	{
		if (!empty($this->form))
		{
			return $this->form;
		}

		$template            = $this->app->getTemplate();
		$control             = $this->uParams['theme'] . (int) $this->uParams['index'];
		$formName            = $control;
		$formXmlPath         = $this->getFieldsFile();
		$formControl         = array('control' => $control);
		$form                = JTFForm::getInstance($formName, $formXmlPath, $formControl);
		$form->framework     = $this->uParams['framework'];
		$form->rendererDebug = $this->debug;
		$form->layoutPaths   = array(
			JPATH_THEMES . '/' . $template . '/html/plg_content_jtf/' . $this->uParams['theme'],
			JPATH_THEMES . '/' . $template . '/html/layouts/plugin/content/jtf',
			JPATH_THEMES . '/' . $template . '/html/layouts',
			JPATH_PLUGINS . '/content/jtf/layouts/jtf',
			JPATH_PLUGINS . '/content/jtf/layouts',
		);

		$this->form = $form;

		return $form;
	}

	private function issetField($fieldtype, $fieldsetname = null)
	{
		$form   = $this->getForm();
		$fields = $form->getFieldset($fieldsetname);

		foreach ($fields as $field)
		{
			$type = (string) $field->getAttribute('type');

			if ($type == $fieldtype)
			{
				return (string) $field->getAttribute('name');
			}
		}

		return false;
	}

	/**
	 * Get and translate submitted Form values
	 *
	 * @param   array  $submittedValues  Array of submitted values
	 *
	 * @return   array
	 * @since    1.0
	 */
	private function getTranslatedSubmittedFormValues($submittedValues = array())
	{
		$formTheme = $this->uParams['theme'] . $this->uParams['index'];

		// Get Form values
		if (empty($submittedValues))
		{
			$submittedValues = $this->app->input->get($formTheme, array(), 'post', 'array');
		}

		foreach ($submittedValues as $subKey => $_subValue)
		{
			if (is_array($_subValue))
			{
				$subValue = $this->getTranslatedSubmittedFormValues($_subValue);
			}
			else
			{
				$subValue = Text::_($_subValue);
			}

			$submittedValues[$subKey] = $subValue;
		}

		return $submittedValues;
	}

	private function validate()
	{
		$form     = $this->getForm();
		$token    = JSession::checkToken();
		$fields = $form->getFieldset();

		foreach ($fields as $field)
		{
			$this->validateField($field);
		}

		$valid = ($token && $this->validField) ? true : false;

		if (!empty($this->fileFields))
		{
			if ($valid)
			{
				$this->clearOldFiles();
				$this->saveFiles();
			}
			else
			{
				foreach ($this->fileFields as $fileField)
				{
					$this->invalidField($fileField);
				}
			}
		}

		if ($this->validCaptcha !== true)
		{
			$this->invalidField($this->issetCaptcha);
			$valid = false;
		}

		return $valid;
	}

	private function validateField($field)
	{
		$form          = $this->getForm();
		$value         = $field->value;
		$showon        = $field->showon;
		$showField     = true;
		$validateField = true;
		$valid         = false;
		$type          = strtolower($field->type);
		$validate      = $field->validate;
		$required      = $field->required;
		$fieldName     = $field->fieldname;
		$uploadmaxsize = $field->uploadmaxsize;
		$uploadmaxsize = number_format((float) $uploadmaxsize, 2) * 1024 * 1024;

		if ($showon)
		{
			$_showon_value    = explode(':', $showon);
			$_showon_value[1] = Text::_($_showon_value[1]);
			$showon_value     = $form->getValue($_showon_value[0]);

			if (!in_array($showon_value, $_showon_value))
			{
				$showField = false;
				$valid     = true;
				$form->setValue($fieldName, null, '');

				if ($type == 'spacer')
				{
					$form->setFieldAttribute($fieldName, 'label', '');
				}
			}
		}

		if ($required && empty($value))
		{
			if (!$showField)
			{
				$validateField = false;
			}
		}

		if ($validateField && $showField)
		{
			if ($type == 'file')
			{
				$submittedFiles = $this->getSubmittedFiles($fieldName);
				$value = $submittedFiles['files'];

				if ($submittedFiles['sumsize'] > $uploadmaxsize)
				{
					$validateField = false;
				}
			}

			if (in_array($type, array('radio', 'checkboxes', 'list')))
			{
				$oField = $form->getFieldXml($fieldName);
				$oCount = count($oField->option);

				for ($i = 0; $i < $oCount; $i++)
				{
					$_val = (string) $oField->option[$i]->attributes()->value;

					if ($_val)
					{
						if (is_array($value))
						{
							$val = in_array(Text::_($_val), $value) ? Text::_($_val) : $_val;
						}
						else
						{
							$val = $value == Text::_($_val) ? $value : $_val;
						}

						$oField->option[$i]->attributes()->value = $val;
					}
				}
			}

			if ($type == 'email')
			{
				$form->setFieldAttribute($fieldName, 'tld', 'tld');
			}

			if ($validateField)
			{
				if ($validate)
				{
					$rule = JFormHelper::loadRuleType($validate);
				}
				else
				{
					$rule = JFormHelper::loadRuleType($type);
				}
			}

			if (!empty($rule) && $required)
			{
				if ($type == 'captcha')
				{
					$valid = $rule->test($form->getFieldXml($fieldName), $value, null, null, $form);

					if ($valid !== true)
					{
						$this->validCaptcha = $valid;
						$this->issetCaptcha = $fieldName;
						$valid              = false;
					}
				}
				else
				{
					$valid = $rule->test($form->getFieldXml($fieldName), $value);
				}
			}
			else
			{
				$valid = $validateField;
			}

		}

		if (!$valid && $type != 'captcha')
		{
			$this->invalidField($fieldName);
		}
	}

	/**
	 * Get submitted Files
	 *
	 * @param   string $fieldName JFormField Name
	 *
	 * @return   array
	 * @since    1.0
	 */
	private function getSubmittedFiles($fieldName)
	{
		$value       = array();
		$sumSize     = 0;
		$index       = (int) $this->uParams['index'];
		$jinput      = new \Joomla\Input\Files;
		$submitFiles = $jinput->get($this->uParams['theme'] . $index);

		if (Joomla\Utilities\ArrayHelper::isAssociative($submitFiles[$fieldName]))
		{
			$submitFiles = array($submitFiles[$fieldName]);
		}

		$issetFiles = false;

		if (!empty($submitFiles[$fieldName][0]['name']))
		{
			$issetFiles = true;
			$files      = $submitFiles[$fieldName];
		}

		if ($issetFiles)
		{
			$value['files']                  = $files;
			$this->submitedFiles[$fieldName] = $files;
			$this->fileFields[]              = $fieldName;

			foreach ($files as $file)
			{
				$sumSize += $file['size'];
			}

			$value['sumsize'] = $sumSize;
		}

		return $value;
	}

	private function invalidField($fieldName)
	{
		$form       = $this->getForm();
		$type       = $form->getFieldAttribute($fieldName, 'type');
		$label      = Text::_($form->getFieldAttribute($fieldName, 'label'));
		$errorClass = 'invalid';

		$class = $form->getFieldAttribute($fieldName, 'class');
		$class = $class
			? trim(str_replace($errorClass, '', $class)) . ' '
			: '';

		$labelClass = $form->getFieldAttribute($fieldName, 'labelclass');
		$labelClass = $labelClass
			? trim(str_replace($errorClass, '', $labelClass)) . ' '
			: '';

		$form->setFieldAttribute($fieldName, 'class', $class . $errorClass);
		$form->setFieldAttribute($fieldName, 'labelclass', $labelClass . $errorClass);

		if ($fieldName == $this->issetCaptcha)
		{
			$this->app->enqueueMessage((string) $this->validCaptcha, 'error');
		}
		elseif ($type == 'file')
		{
			$this->app->enqueueMessage(
					Text::sprintf('JTF_FILE_FIELD_ERROR', $label), 'error'
				);
		}
		else
		{
			$this->app->enqueueMessage(
					Text::sprintf('JTF_FIELD_ERROR', $label), 'error'
				);
		}

		$this->validField = false;
	}

	private function clearOldFiles()
	{
		jimport('joomla.filesystem.folder');

		if (!$fileClear = (int) $this->uParams['file_clear'])
		{
			return;
		}

		$uploadBase = JPATH_BASE . '/images/' . $this->uParams['file_path'];

		if (!is_dir($uploadBase))
		{
			return;
		}

		$folders = JFolder::folders($uploadBase);
		$nowPath = date('Ymd');
		$now     = new DateTime($nowPath);

		foreach ($folders as $folder)
		{
			$date   = new DateTime($folder);
			$clrear = date_diff($now, $date)->days;

			if ($clrear >= $fileClear)
			{
				JFolder::delete($uploadBase . '/' . $folder);
			}
		}
	}

	private function saveFiles()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$form          = $this->getForm();
		$submitedFiles = $this->submitedFiles;
		$nowPath       = date('Ymd');
		$filePath      = 'images/' . $this->uParams['file_path'] . '/' . $nowPath;
		$uploadBase    = JPATH_BASE . '/' . $filePath;
		$uploadURL     = rtrim(JUri::base(), '/') . '/' . $filePath;

		if (!is_dir($uploadBase))
		{
			JFolder::create($uploadBase);
		}

		if (!file_exists($uploadBase . '/.htaccess'))
		{
			JFile::write($uploadBase. '/.htaccess', Text::_('JTF_SET_ATTACHMENT_HTACCESS'));
		}

		foreach ($submitedFiles as $fieldName => $files)
		{
			$value = array();

			foreach ($files as $file)
			{
				$save     = null;
				$fileName = JFile::stripExt($file['name']);
				$fileExt  = JFile::getExt($file['name']);
				$name     = JFilterOutput::stringURLSafe($fileName) . '.' . $fileExt;

				$save = JFile::copy($file['tmp_name'], $uploadBase . '/' . $name);

				if ($save)
				{
					$value[$name] = $uploadURL . '/' . $name;
				}
			}

			$form->setValue($fieldName, null, $value);
		}
	}

	private function getTmpl($filename)
	{
//		$this->setFrameworkFieldClass();

		$index         = $this->uParams['index'];
		$id            = $this->uParams['theme'];
		$form          = $this->getForm();

		$form = JTFFrameworkHelper::setFrameworkClasses($form);

		$formClass     = $form->getAttribute('class', '');
		$frwkCss       = $form->frwrkClasses->getCss();
		$enctype       = '';
		$controlFields = '<input type="hidden" name="option" value="' . $this->app->input->get('option') . '" />'
			. '<input type="hidden" name="task" value="' . $id . $index . '_sendmail" />'
			. '<input type="hidden" name="view" value="' . $this->app->input->get('view') . '" />'
			. '<input type="hidden" name="itemid" value="' . $this->app->input->get('idemid') . '" />'
			. '<input type="hidden" name="start" value="' . $this->uParams['startTime'] . '" />'
			. '<input type="hidden" name="id" value="' . $this->app->input->get('id') . '" />';

		if (!empty($form->setEnctype))
		{
			$enctype = ' enctype="multipart/form-data"';
		}

		$displayData = array(
			'id'            => $id . (int) $index . '_form',
			'fileClear'     => $this->params->get('file_clear'),
			'form'          => $form,
			'formClass'     => $formClass,
			'enctype'       => $enctype,
			'frwkCss'       => $frwkCss,
			'controlFields' => $controlFields,
		);

		$renderer = new JLayoutFile($filename);

		// Set Framwork as Layout->Suffix
		if (!empty($this->uParams['framework'][0]) && $this->uParams['framework'][0] != 'joomla')
		{
			$renderer->setSuffixes($this->uParams['framework']);
		}

		$renderer->addIncludePaths($form->layoutPaths);
		$renderer->setDebug($this->debug);

		return $renderer->render($displayData);
	}

	private function getEmailCredentials()
	{
		$recipients = array();

		foreach (array('mailto', 'cc', 'bcc', 'visitor_name', 'visitor_email') as $name)
		{
			$recipients[$name] = null;

			if (!empty($this->uParams[$name]))
			{
				$items = explode(';', $this->uParams[$name]);
				$i     = 0;

				if ($name == 'visitor_email')
				{
					$items = array($items[0]);
				}

				foreach ($items as $item)
				{
					$item = str_replace('#', '@', trim($item));

					if (strpos($item, '@') !== false)
					{
						$recipient[$item] = $i++;
					}
					else
					{
						$value = $this->getValue($item);
						$value = str_replace('#', '@', trim($value));

						$recipient[$value] = $i++;
					}
				}

				$recipients[$name] = array_flip($recipient);
				unset($recipient);

				if (in_array($name, array('visitor_name', 'visitor_email')))
				{
					$recipients[$name]  = trim(implode(' ', $recipients[$name]));
				}

			}
			else
			{
				if ($name == 'mailto')
				{
					if (!empty(Factory::getConfig()->get('replyto')))
					{
						$recipients['mailto'][] = Factory::getConfig()->get('replyto');
					}
					else
					{
						$recipients['mailto'][] = Factory::getConfig()->get('mailfrom');
					}
				}
			}
		}

		return $recipients;
	}

	private function sendMail()
	{
		$jConfig = Factory::getConfig();
		$mailer = Factory::getMailer();

		$subject = $this->getValue('subject');
		$subject = !empty($subject) ? $subject : Text::sprintf('JTF_EMAIL_SUBJECT', $jConfig->get('sitename'));

		$emailCredentials = $this->getEmailCredentials();

		$recipient     = $emailCredentials['mailto'];
		$cc            = $emailCredentials['cc'];
		$bcc           = $emailCredentials['bcc'];
		$replayToName  = $emailCredentials['visitor_name'];
		$replayToEmail = $emailCredentials['visitor_email'];

		if (empty($replayToEmail))
		{
			if (!empty($jConfig->get('replyto')))
			{
				$replayToEmail = $jConfig->get('replyto');
			}
			else
			{
				$replayToEmail = $jConfig->get('mailfrom');
			}

			$replayToName = $jConfig->get('fromname');
		}

		$hBody  = $this->getTmpl('message.html');
		$pBody  = $this->getTmpl('message.plain');

		$mailer->addReplyTo($replayToEmail, $replayToName);
		$mailer->addRecipient($recipient);

		if (!empty($cc))
		{
			$mailer->addCc($cc);
		}

		if (!empty($bcc))
		{
			$mailer->addBcc($bcc);
		}

		$mailer->setSubject($subject);
		$mailer->IsHTML(true);
		$mailer->setBody($hBody);
		$mailer->AltBody = $pBody;

		$send = $mailer->Send();

		return $send;
	}

	/**
	 * Set captcha to submit fieldset or remove it, if is set off global
	 *
	 * @param   mixed  $captcha  Fieldname of captcha
	 *
	 * @return   void
	 * @since    3.0
	 */
	private function setCaptcha($captcha)
	{
		$form   = $this->getForm();
		$hField = new SimpleXMLElement('<field name="jtf_important_notices" type="text" gridgroup="jtfhp" notmail="1"></field>');

		$form->setField($hField, null, true, 'submit');
		Factory::getDocument()->addStyleDeclaration('.hidden{display:none;visibility:hidden;}.jtfhp{position:absolute;top:-999em;left:-999em;height:0;width:0;}');

		// Set captcha to submit fieldset
		if (!empty($this->uParams['captcha']))
		{
			if (!empty($captcha))
			{
				if (empty($captcha['submit']))
				{
					$cField = $form->getFieldXml($captcha);

					$form->removeField($captcha);
					$form->setField($cField, null, true, 'submit');
				}
				else
				{
					$captcha = $captcha['submit'];
				}
			}
			else
			{
				$captcha = 'captcha';
				$cField  = new SimpleXMLElement('<field name="captcha" type="captcha" validate="captcha" description="JTF_CAPTCHA_DESC" label="JTF_CAPTCHA_LABEL"></field>');

				$form->setField($cField, null, true, 'submit');
			}

			$form->setFieldAttribute($captcha, 'notmail', true);
		}

		// Remove Captcha if disabled by plugin
		if (empty($this->uParams['captcha']) && !empty($captcha))
		{
			$captcha = false;

			$form->removeField($captcha);
		}

		$this->issetCaptcha = $captcha;
	}

	/**
	 * Set submit button to submit fieldset
	 *
	 * @param   mixed  $submit  Fieldname of submit button
	 *
	 * @return   void
	 * @since    3.0
	 */
	private function setSubmitButton($submit)
	{
		$form = $this->getForm();

		// Set submit button to submit fieldset
		if (!empty($submit))
		{
			if (empty($submit['submit']))
			{
				$cField = $form->getFieldXml($submit);
				$form->removeField($submit);
				$form->setField($cField, null, true, 'submit');
			}
			else
			{
				$cField = $form->getFieldXml($submit['submit']);
				$form->removeField($submit['submit']);
				$form->setField($cField, null, true, 'submit');
				$submit = $submit['submit'];
			}

			$form->setFieldAttribute($submit, 'notmail', true);
		}
		else
		{
			$cField = new SimpleXMLElement('<field name="submit" type="submit" label="JTF_SUBMIT_BUTTON" notmail="1"></field>');
			$form->setField($cField, null, true, 'submit');
		}
	}

	/**
	 * @param $name
	 *
	 * @return mixed
	 *
	 * @since 3.8
	 */
	private function getValue($name)
	{
		$data = $this->getForm()->getData()->toArray();
		$value = null;

		if (!empty($this->uParams[$name]))
		{
			$value = $this->uParams[$name];

			if (!empty($data[$value]))
			{
				$value = $data[$value];
			}
		}
		else if (!empty($data[$name]))
		{
			$value = $data[$name];
		}

		return $value;
	}
}
