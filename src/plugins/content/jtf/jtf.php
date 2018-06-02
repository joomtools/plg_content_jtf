<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author      Guido De Gobbis <support@joomtools.de>
 * @copyright   2017 JoomTools.de - All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

defined('_JEXEC') or die('Restricted access');

JLoader::discover('JTFFramework', JPATH_PLUGINS . '/content/jtf/libraries/frameworks', true);
JLoader::register('JTFForm', JPATH_PLUGINS . '/content/jtf/libraries/form/form.php', true);

// Add form fields
JFormHelper::addFieldPath(JPATH_PLUGINS . '/content/jtf/libraries/form/fields');

// Add form rules
JFormHelper::addRulePath(JPATH_PLUGINS . '/content/jtf/libraries/form/rules');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Profiler\Profiler;

/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @since   3.0.0
 */
class PlgContentJtf extends CMSPlugin
{
	/**
	 * The regular expression to identify Plugin call.
	 *
	 * @var     string
	 * @since   3.0.0
	 */
	const PLUGIN_REGEX = "@(<(\w+)[^>]*>)?{jtf(\s.*)?}(</\\2>)?@";
	/**
	 * Set counter
	 *
	 * @var     int
	 * @since   3.0.0
	 */
	private static $count;
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var     boolean
	 * @since   3.0.0
	 */
	protected $autoloadLanguage = true;
	/**
	 * Global application object
	 *
	 * @var     JApplication
	 * @since   3.0.0
	 */
	protected $app = null;
	/**
	 * @var     stdClass
	 * @since   3.0.0
	 */
	private $doNotLoad;

	/**
	 * Set captcha name
	 *
	 * @var     string
	 * @since   3.0.0
	 */
	private $issetCaptcha;

	/**
	 * Set result of captcha validation
	 *
	 * @var     boolean
	 * @since   3.0.0
	 */
	private $validCaptcha = true;

	/**
	 * Set JTFForm object
	 *
	 * @var     JTFForm
	 * @since   3.0.0
	 */
	private $form = null;

	/**
	 * JFormField validation
	 *
	 * @var     boolean
	 * @since   3.0.0
	 */
	private $validField = true;

	/**
	 * Array with JFormField Names of submitted Files
	 *
	 * @var     array
	 * @since   3.0.0
	 */
	private $fileFields = array();

	/**
	 * Array with submitted Files
	 *
	 * @var     array
	 * @since   3.0.0
	 */
	private $submitedFiles = array();

	/**
	 * Array with User params
	 *
	 * @var     array
	 * @since   3.0.0
	 */
	private $uParams = array();

	/**
	 * Debug
	 *
	 * @var     boolean
	 * @since   3.0.0
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
	 * @return   void
	 * @since    3.0.0
	 */
	public function __construct($subject, array $config = array())
	{
		parent::__construct($subject, $config);

		if ($this->app->isClient('administrator'))
		{
			return null;
		}

		$this->debug = (boolean) $this->params->get('debug', 0);
		$option      = $this->app->input->getCmd('option');
		$layout      = $this->app->input->getCmd('layout');

		$component_exclusions = explode(
			'|n|',
			str_replace(
				array("\r\n", "\r", "\n"),
				'|n|',
				str_replace(' ',
					'',
					$this->params->get('component_exclusions')
				)
			)
		);

		$component_exclusions = array_unique(array_filter($component_exclusions, 'strlen'));

		$this->doNotLoad            = new stdClass();
		$this->doNotLoad->active    = false;
		$this->doNotLoad->extension = $option;

		if (in_array($option, $component_exclusions) || $layout == 'edit')
		{
			$this->doNotLoad->active = true;
		}

		if (!$this->doNotLoad->active)
		{
			JLoader::register('JFormField', JPATH_PLUGINS . '/content/jtf/libraries/form/FormField.php', true);
			JLoader::register('FormField', JPATH_PLUGINS . '/content/jtf/libraries/form/FormField.php', true);
			JLoader::registerNamespace('Joomla\CMS\Form\FormField', JPATH_PLUGINS . '/content/jtf/libraries/form/FormField.php', true);
			JLoader::registerNamespace('Joomla\CMS\Form\FormField', JPATH_PLUGINS . '/content/jtf/libraries/form/FormField.php', true, false, 'psr4');
		}
		self::$count = 0;
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
	 * @since    3.0.0
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

		// Do not load if not permitted extansion ist load too.
		if ($this->doNotLoad->active)
		{
			$this->app->enqueueMessage(Text::sprintf('JTF_CAN_NOT_LOAD', $this->doNotLoad->extension), 'notice');

			return;
		}

		// Get all matches or return
		if (!preg_match_all(self::PLUGIN_REGEX, $article->text, $matches))
		{
			return;
		}

		// Get language tag
		$langTag = $this->app->get('language');

		// Exclude <code/> and <pre/> matches
		$code    = array_keys($matches[1], '<code>');
		$pre     = array_keys($matches[1], '<pre>');

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
				}, array_merge($code, $pre)
			);
		}

		$pluginReplacements = $matches[0];
		$userParams         = $matches[3];

		// Load global form language
		$this->loadLanguage('jtf_global', JPATH_PLUGINS . '/content/jtf/assets');

		foreach ($pluginReplacements as $rKey => $replacement)
		{
			// Clear html replace
			$html = '';

			$this->init($userParams[$rKey]);

			if (empty($this->uParams['formXmlPath']))
			{
				return;
			}

			$formTheme = $this->uParams['theme'] . (int) self::$count;

			$formLang = $this->getThemePath('language/' . $langTag . '/' . $langTag . '.jtf_theme.ini');

			if (!empty($formLang))
			{
				Factory::getLanguage()->load('jtf_theme', $formLang);
			}

			// Get form submit task
			$formSubmitted = ($this->app->input->get('task', false, 'post') == $formTheme . "_sendmail") ? true : false;

			if ($formSubmitted)
			{
				$submitValues = $this->getTranslatedSubmittedFormValues();

				$this->getForm()->bind($submitValues);

				$startTime   = $this->app->input->getFloat('start');
				$fillOutTime = $this->debug || JDEBUG ? 10000 : microtime(1) - $startTime;
				$notSpamBot  = $fillOutTime > $this->uParams['fillouttime'] ? true : false;

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

		$this->setSubmit();

		$html .= $this->getTmpl('form');

		$pos = strpos($article->text, $replacement);
		$end = strlen($replacement);

		$article->text = substr_replace($article->text, $html, $pos, $end);
		self::$count++;


		// Set profiler start time and memory usage and mark afterLoad in the profiler.
		JDEBUG ? Profiler::getInstance('Application')->mark('plgContentJtf') : null;
	}

	private function init($userParams)
	{
		$this->resetUserParams();

		if (!empty($userParams))
		{
			$vars = explode('|', $userParams);

			// Set user params
			$this->setUserParams($vars);
		}

		$this->uParams['formXmlPath'] = $this->getThemePath('fields.xml', true);

	}

	/**
	 * Reset user params to default values set in plugin
	 *
	 * @return   void
	 * @since    3.0.0
	 */
	private function resetUserParams()
	{
		$this->uParams = array();
		$this->form    = null;

		$this->uParams['startTime'] = microtime(1);

		// Set default minimum fillout time
		$this->uParams['fillouttime'] = $this->params->get('filloutTime', 16);

		// Set default captcha value
		$this->uParams['captcha'] = $this->params->get('captcha');

		// Clear mail recipient
		$this->uParams['mailto'] = null;

		// Clear mail cc
		$this->uParams['cc'] = null;

		// Clear mail bcc
		$this->uParams['bcc'] = null;

		// Clear visitor_name
		$this->uParams['visitor_name'] = null;

		// Clear visitor_email
		$this->uParams['visitor_email'] = null;

		// Clear subject
		$this->uParams['subject'] = null;

		// Set theme to default
		$this->uParams['theme'] = 'default';

		// Set default time to clear uploads
		$this->uParams['file_clear'] = (int) $this->params->get('file_clear', 30);

		// Set default path in images to save uploaded files
		$this->uParams['file_path'] = trim($this->params->get('file_path', 'uploads'), '\/');

		// Set default framework value
		$this->uParams['framework'] = (array) $this->params->get('framework');
	}

	/**
	 * Set user Params and override default values
	 *
	 * @param   array  $vars  Params pairs from plugin call
	 *
	 * @return   void
	 * @since    3.0.0
	 */
	private function setUserParams(array $vars)
	{
		$uParams = array();

		if (!empty($vars))
		{
			foreach ($vars as $var)
			{
				list($key, $value) = explode('=', trim($var));

				$key = trim(strtolower($key));
				$value = trim($value, '\/');

				if ($key == 'framework')
				{
					$value = explode(',', $value);
					$value = \Joomla\Utilities\ArrayHelper::arrayUnique($value);
				}

				$uParams[$key] = $value;
			}
		}

		// Merge user params width default params
		$this->uParams = array_merge($this->uParams, $uParams);
	}

	/**
	 * Get absolute theme filepath
	 *
	 * @param   string  $filePath   Filepath relativ from inside of the theme
	 * @param   bool    $framework  Search file with framework suffix
	 *
	 * @return   bool|string
	 * @since    3.0.0
	 */
	private function getThemePath($filePath, $framework = false)
	{
		$error = array();
		$files = array($filePath);
		$ext   = JFile::getExt($filePath);

		if ($framework)
		{
			$file  = JFile::stripExt($filePath);
			$files = array();

			foreach ($this->uParams['framework'] as $frwk)
			{
				$_frwk = '';

				if ($frwk != 'joomla')
				{
					$_frwk = '.' . $frwk;
				}

				$files[] = $file . $_frwk . '.' . $ext;
			}

			$files[] = $filePath;
			$files = \Joomla\Utilities\ArrayHelper::arrayUnique($files);
		}

		$template = $this->app->getTemplate();

		// Build template override path for theme
		$tAbsPath = JPATH_THEMES . '/' . $template
			. '/html/plg_content_jtf/'
			. $this->uParams['theme'];
		$tAbsPathFlat = str_replace('/', '.', trim($tAbsPath, '\/'));

		// Build plugin path for theme
		$bAbsPath = JPATH_PLUGINS . '/content/jtf/tmpl/'
			. $this->uParams['theme'];
		$bAbsPathFlat = str_replace('/', '.', trim($bAbsPath, '\/'));

		$error['path'][$tAbsPathFlat] = true;
		$error['path'][$bAbsPathFlat] = true;

		foreach ($files as $filename)
		{
			// Set the right theme path
			if (is_dir($tAbsPath ))
			{
				if (isset($error['path']))
				{
					unset($error['path']);
				}

				if (file_exists($tAbsPath . '/' . $filename))
				{
					if ($ext == 'ini')
					{
						return $tAbsPath;
					}

					return $tAbsPath . '/' . $filename;
				}
				$error['file'][$filename] = true;
			}

			if (is_dir($bAbsPath))
			{
				if (isset($error['path']))
				{
					unset($error['path']);
				}

				if (file_exists($bAbsPath . '/' . $filename))
				{
					if ($ext == 'ini')
					{
						return $bAbsPath;
					}

					return $bAbsPath . '/' . $filename;
				}
				$error['file'][$filename] = true;
			}
		}

		if (!empty($error['path']) && $ext != 'ini')
		{
			$this->app->enqueueMessage(
				Text::sprintf('JTF_THEME_ERROR', $this->uParams['theme']),
				'error'
			);
		}

		if (!empty($error['file']) && $ext != 'ini')
		{
			$this->app->enqueueMessage(
				Text::sprintf('JTF_FORM_XML_FILE_ERROR', implode(', ', $files),$this->uParams['theme']),
				'error'
			);
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
				$button['submit'] = $this->issetField('submit', 'submit');
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
		$formName            = $this->uParams['theme'] . (int) self::$count;
		$formXmlPath         = $this->uParams['formXmlPath'];
		$form                = JTFForm::getInstance($formName, $formXmlPath);
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
	 * Set captcha to submit fieldset or remove it, if is set off global
	 *
	 * @param   mixed  $captcha  Fieldname of captcha
	 *
	 * @return   void
	 * @since    3.0.0
	 */
	private function setCaptcha($captcha)
	{
		$form   = $this->getForm();
		$hField = new SimpleXMLElement('<field name="jtf_important_notices" type="text" gridgroup="jtfhp" hiddenLabel="true" notmail="1"></field>');

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
	 * @since    3.0.0
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
			$cField = new SimpleXMLElement('<field name="submit" type="submit" label="JTF_SUBMIT_BUTTON" hiddenLabel="true" notmail="1"></field>');
			$form->setField($cField, null, true, 'submit');
		}
	}

	/**
	 * Get and translate submitted Form values
	 *
	 * @param   array  $submittedValues  Array of submitted values
	 *
	 * @return   array
	 * @since    3.0.0
	 */
	private function getTranslatedSubmittedFormValues($submittedValues = array())
	{
		$formTheme = $this->uParams['theme'] . self::$count;

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
		$form   = $this->getForm();
		$token  = JSession::checkToken();
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
				$value          = $submittedFiles['files'];

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
	 * @param   string  $fieldName  JFormField Name
	 *
	 * @return   array
	 * @since    3.0.0
	 */
	private function getSubmittedFiles($fieldName)
	{
		$value       = array();
		$sumSize     = 0;
		$index       = (int) self::$count;
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

		$bugUploadBase = JPATH_BASE . '/images/0';

		if (is_dir($bugUploadBase))
		{
			$this->bugfixUploadFolder();
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

	private function bugfixUploadFolder()
	{
		$srcBase = JPATH_BASE . '/images/0';
		$destBase = JPATH_BASE . '/images/' . $this->uParams['file_path'];
		$folders    = JFolder::folders($srcBase);

		foreach ($folders as $key => $folder)
		{
			$src   = $srcBase . '/' . $folder;
			$dest  = $destBase . '/' . $folder;

			if (is_dir($dest))
			{
				$copied = JFolder::copy($src, $dest, '', true);
			}
			else
			{
				$moved = JFolder::move($src, $dest);
			}

			if ($copied === true || $moved === true)
			{
				unset($folders[$key]);
			}
		}

		if (empty($folders))
		{
			JFolder::delete($srcBase);
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
			JFile::write($uploadBase . '/.htaccess', Text::_('JTF_SET_ATTACHMENT_HTACCESS'));
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

	private function sendMail()
	{
		$jConfig = Factory::getConfig();
		$mailer  = Factory::getMailer();

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

		$hBody = $this->getTmpl('message.html');
		$pBody = $this->getTmpl('message.plain');

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
	 * @param   $name
	 *
	 * @return   mixed
	 * @since    3.0.0
	 */
	private function getValue($name)
	{
		$data  = $this->getForm()->getData()->toArray();
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
					$recipients[$name] = trim(implode(' ', $recipients[$name]));
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

	private function getTmpl($filename)
	{
		$enctype       = '';
		$id            = $this->uParams['theme'];
		$index         = self::$count;
		$form          = $this->getForm();
		$form          = JTFFrameworkHelper::setFrameworkClasses($form);
		$formClass     = $form->getAttribute('class', '');
		$frwkCss       = $form->frwkClasses->getCss();
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
}
