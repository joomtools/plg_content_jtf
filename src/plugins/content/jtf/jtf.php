<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Profiler\Profiler;
use Joomla\Utilities\ArrayHelper;
use Jtf\Form\Form;
use Joomla\CMS\Form\Rule\CaptchaRule;

JLoader::discover('Jtf\Frameworks\Framework', JPATH_PLUGINS . '/content/jtf/libraries/jtf/Frameworks');
JLoader::registerNamespace('Jtf', JPATH_PLUGINS . '/content/jtf/libraries/jtf', false, false, 'psr4');
JLoader::register('JFormField', JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/FormField.php', true);
JLoader::register('FormField', JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/FormField.php', true);
JLoader::registerNamespace('Joomla\CMS\Form\FormField', JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/FormField.php', true);
JLoader::registerNamespace('Joomla\CMS\Form\FormField', JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/FormField.php', true, false, 'psr4');

JLoader::register('JFormFieldCaptcha', JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/fields/CaptchaField.php', true);
JLoader::register('CaptchaField', JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/fields/CaptchaField.php', true);
JLoader::registerNamespace('Joomla\CMS\Form\Field\CaptchaField', JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/fields/CaptchaField.php', true);
JLoader::registerNamespace('Joomla\CMS\Form\Field\CaptchaField', JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/fields/CaptchaField.php', true, false, 'psr4');


// Add form fields
JFormHelper::addFieldPath(JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/fields');

// Add form rules
JFormHelper::addRulePath(JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/rules');
JLoader::registerNamespace('Joomla\CMS\Form\Rule', JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/rules', false, false, 'psr4');

JLoader::register('JFormRuleTel', JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/rules/TelRule.php', true);
JLoader::register('TelRule', JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/rules/TelRule.php', true);
JLoader::registerNamespace('Joomla\CMS\Form\Rule\TelRule', JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/rules/TelRule.php', true);
JLoader::registerNamespace('Joomla\CMS\Form\Rule\TelRule', JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/rules/TelRule.php', true, false, 'psr4');

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
	 * The context being passed to the plugin.
	 *
	 * @var     string
	 * @since   3.0.0
	 */
	protected $context;

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
	 * Set Form object
	 *
	 * @var     Form
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
	private $fileFields = [];

	/**
	 * Array with submitted Files
	 *
	 * @var     array
	 * @since   3.0.0
	 */
	private $submitedFiles = [];

	/**
	 * Array with User params
	 *
	 * @var     array
	 * @since   3.0.0
	 */
	private $uParams = [];

	/**
	 * Array with allowed params to override
	 *
	 * @var     array
	 * @since   3.0.0
	 */
	private $uParamsAllowedOverride = [
		'fillouttime',
		'mailto',
		'cc',
		'bcc',
		'visitor_name',
		'visitor_email',
		'subject',
		'message_article',
		'redirect_menuid',
		'theme',
		'framework',
	];

	/**
	 * @var   array
	 *
	 * @since  3.0.0
	 */
	private $tokens = array();

	/**
	 * @var   boolean
	 *
	 * @var     boolean
	 * @since   3.0.0
	 */
	private $debug = false;

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 *
	 * @return   void
	 * @since    3.0.0
	 */
	public function __construct(&$subject, $config = array())
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

		$this->context = $context;


		switch (true)
		{
			case !is_object($this->app->getUserState('plugins.content.jtf.' . $context)):
				$this->app->setUserState('plugins.content.jtf.' . $context, null);
			case !is_object($this->app->getUserState('plugins.content.jtf.hp')):
				$this->app->setUserState('plugins.content.jtf.hp', null);
			case !is_object($this->app->getUserState('plugins.content.jtf.start')):
				$this->app->setUserState('plugins.content.jtf.start', null);
			default:
				break;
		}

		// Get language tag
		$langTag = $this->app->get('language');

		// Exclude <code/> and <pre/> matches
		$code = array_keys($matches[1], '<code>');
		$pre  = array_keys($matches[1], '<pre>');

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

		if ($this->app->getUserState('plugins.content.jtf.' . $context))
		{
			$this->tokens[$context] = clone $this->app->getUserState('plugins.content.jtf.' . $context);
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
			$formLang  = $this->getThemePath('language/' . $langTag . '/' . $langTag . '.jtf_theme.ini');
			$jtfHp     = $this->app->getUserState('plugins.content.jtf.hp.' . $context . '.' . $formTheme);
			$startTime = $this->app->getUserState('plugins.content.jtf.start.' . $context . '.' . $formTheme);


			$token  = Joomla\CMS\Session\Session::getFormToken(true);
			$this->app->setUserState('plugins.content.jtf.' . $context . '.' . $formTheme, $token);


			if (!empty($formLang))
			{
				Factory::getLanguage()->load('jtf_theme', $formLang);
			}

			// Get form submit task
			$formSubmitted = ($this->app->input->getCmd('formTask') == $formTheme . "_sendmail") ? true : false;

			if ($formSubmitted)
			{
				$checkToken      = $this->checkToken($formTheme);
				$submittedValues = $this->app->input->get($formTheme, array(), 'post', 'array');
				$honeypot        = $submittedValues[$jtfHp];
				$fillOutTime     = $this->debug || JDEBUG || $this->uParams['fillouttime'] == 0
					? 100000
					: microtime(1) - $startTime;
				$notSpamBot     = $fillOutTime > $this->uParams['fillouttime'];

				if ($honeypot != '' || !$notSpamBot || !$checkToken)
				{
					$this->app->setUserState('plugins.content.jtf.start.' . $context . '.' . $formTheme, null);
					$this->app->setUserState('plugins.content.jtf.hp.' . $context . '.' . $formTheme, null);
					$this->app->redirect(JRoute::_('index.php', false));
				}

				if (!empty($_FILES))
				{
					$jinput         = new \Jtf\Input\Files;
					$submittedFiles  = $jinput->get($formTheme);
					$submittedValues = array_merge_recursive($submittedValues, $submittedFiles);
				}

				$this->getForm()->bind($submittedValues);
				$this->setFieldValidates();

				$valid = $this->getForm()->validate($submittedValues);

				// Validate captcha
				if ($valid && !empty($this->uParams['captcha']))
				{
					$captchaRule  = new CaptchaRule();
					$element      = new SimpleXMLElement('<element></element>');
					$captchaValue = !empty($submittedValues['captcha']) ? $submittedValues['captcha'] : '';

					$valid = $captchaRule->test($element, $captchaValue, null, null, $this->getForm());
				}

				if ($valid)
				{
					if (!empty($submittedFiles))
					{
						$validatedValues = $this->getForm()->getData()->toArray();
						$validatedFiles  = $this->cleanSubmittedFiles($submittedFiles, $validatedValues);
						$newBind         = array_merge($validatedValues, $validatedFiles);

						$this->getForm()->resetData();
						$this->getForm()->bind($newBind);
					}

					$sendmail = $this->sendMail();

					if ($sendmail)
					{

						if ($this->uParams['redirect_menuid'] !== null)
						{
							$this->uParams['message_article'] = null;
						}

						if ($this->uParams['message_article'] === null)
						{
							$text = Text::_('JTF_EMAIL_THANKS');
						}
						else
						{
							$text = $this->getMessageArticleContent();
						}


						if ($this->uParams['redirect_menuid'] === null)
						{
							$this->app->setUserState('plugins.content.jtf.start.' . $context . '.' . $formTheme, null);
							$this->app->setUserState('plugins.content.jtf.hp.' . $context . '.' . $formTheme, null);
							$this->app->enqueueMessage($text, 'message');
							$this->app->redirect(JRoute::_('index.php', false));
						}
						else
						{
							$this->app->setUserState('plugins.content.jtf.start.' . $context . '.' . $formTheme, null);
							$this->app->setUserState('plugins.content.jtf.hp.' . $context . '.' . $formTheme, null);
							$this->app->redirect(JRoute::_('index.php?Itemid=' . (int) $this->uParams['redirect_menuid'], false));
						}
					}
				}

				$this->setErrors($this->getForm()->getErrors());
			}

			$this->setSubmit();

			$html .= $this->getTmpl('form');

			$pos = strpos($article->text, $replacement);
			$end = strlen($replacement);

			$article->text = substr_replace($article->text, $html, $pos, $end);
			self::$count++;

			$this->clearOldFiles();
			$this->app->setUserState('plugins.content.jtf.start.' . $context . '.' . $formTheme, microtime(1));
		}

		$this->removeCache($context);

		// Set profiler start time and memory usage and mark afterLoad in the profiler.
		JDEBUG ? Profiler::getInstance('Application')->mark('plgContentJtf') : null;
	}

	/**
	 * Initialize user parameters
	 *
	 * @param   string  $userParams  String with user defined parameters
	 *
	 * @return  void
	 *
	 * @since  3.0.0
	 */
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

		// Set default minimum fillout time
		$this->uParams['fillouttime'] = 0;

		if ($this->params->get('filloutTime_onoff', 1) == 1)
		{
			$this->uParams['fillouttime'] = $this->params->get('filloutTime', 10);
		}

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

		// Clear message_article
		$this->uParams['message_article'] = null;

		// Clear redirect_menuid
		$this->uParams['redirect_menuid'] = null;

		// Set theme to default
		$this->uParams['theme'] = 'default';

		// Set default time to clear uploads
		$this->uParams['file_clear'] = (int) $this->params->get('file_clear', 30);

		// Set default path in images to save uploaded files
		$this->uParams['file_path'] = trim($this->params->get('file_path', 'uploads'), '\\/');

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

				$key   = trim(strtolower($key));
				$value = trim($value, '\/');

				if (!in_array($key, $this->uParamsAllowedOverride))
				{
					continue;
				}

				if ($key == 'framework')
				{
					$value = explode(',', $value);
					$value = ArrayHelper::arrayUnique($value);
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
			$files   = ArrayHelper::arrayUnique($files);
		}

		$template = $this->app->getTemplate();

		// Build template override path for theme
		$tAbsPath     = JPATH_THEMES . '/' . $template
			. '/html/plg_content_jtf/'
			. $this->uParams['theme'];
		$tAbsPathFlat = str_replace('/', '.', trim($tAbsPath, '\/'));

		// Build plugin path for theme
		$bAbsPath     = JPATH_PLUGINS . '/content/jtf/tmpl/'
			. $this->uParams['theme'];
		$bAbsPathFlat = str_replace('/', '.', trim($bAbsPath, '\/'));

		$error['path'][$tAbsPathFlat] = true;
		$error['path'][$bAbsPathFlat] = true;

		foreach ($files as $filename)
		{
			// Set the right theme path
			if (is_dir($tAbsPath))
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
				Text::sprintf('JTF_FORM_XML_FILE_ERROR', implode(', ', $files), $this->uParams['theme']),
				'error'
			);
		}

		return false;
	}

	/**
	 * Define form object, if not defined
	 *
	 * @return  Form
	 *
	 * @since  3.0.0
	 */
	private function getForm()
	{
		if (!empty($this->form))
		{
			return $this->form;
		}

		$template            = $this->app->getTemplate();
		$formName            = $this->uParams['theme'] . (int) self::$count;
		$formXmlPath         = $this->uParams['formXmlPath'];
		$form                = Form::getInstance($formName, $formXmlPath, array('control' => $formName));
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

	private function setFieldValidates($form = null)
	{
		$setValidationFor = array(
			'calendar',
			'captcha',
			'checkboxes',
			'color',
			'e-mail',
			'file',
			'list',
			'number',
			'password',
			'plz',
			'radio',
			'rules',
			'tel',
			'username',
		);

		if (empty($form))
		{
			$form = $this->getForm();
		}

		$fields = $form->getFieldset();

		foreach ($fields as $field)
		{
			$fieldType = strtolower($field->type);
			$fieldname = $field->fieldname;

			if ($fieldType == 'subform')
			{
				if ($field->validate == '')
				{
					$form->setFieldAttribute($fieldname, 'validate', 'subform');
				}

				$subform = $form->getField($fieldname)->loadSubForm();
				$this->setFieldValidates($subform);

				continue;
			}

			if (in_array($fieldType, $setValidationFor))
			{
				if ($field->validate == '')
				{
					if (in_array($fieldType, array('checkboxes', 'list', 'radio')))
					{
						$form->setFieldAttribute($fieldname, 'validate', 'options');
					}
					else
					{
						$form->setFieldAttribute($fieldname, 'validate', $fieldType);
					}
				}
			}
		}
	}

	/**
	 * Get submitted Files
	 *
	 * @param   array  $submittedFiles   Dot separated field path to find submitted file for the field
	 * @param   array  $submittedValues  Dot separated field path to find submitted file for the field
	 *
	 * @return   array
	 * @since    3.0.0
	 */
	private function cleanSubmittedFiles($submittedFiles, $submittedValues)
	{
		$validatedFiles = array();

		foreach ($submittedFiles as $key => $value)
		{
			if (isset($submittedValues[$key]))
			{
				if (empty($value))
				{
					$validatedFiles[$key] = array();
					continue;
				}

				if (is_array($value))
				{

					if (isset($value['error']) && $value['error'] === 0)
					{
						if ($savedFile = $this->saveFiles($value))
						{
							$validatedFiles = array_merge($validatedFiles, $savedFile);
							continue;
						}
						else
						{
							$this->setErrors(Text::_('Fehler beim Speichern!'));
							continue;
						}
					}

					$validatedFiles[$key] = $this->cleanSubmittedFiles($submittedFiles[$key], $value);
				}
				continue;
			}
			else
			{
				continue;
			}
		}

		return $validatedFiles;
	}

	/**
	 * Save submited files
	 *
	 * @param   array   $validatedFile
	 *
	 * @return   array
	 * @since    3.0.0
	 */
	private function saveFiles($validatedFile)
	{
		$nowPath     = date('Ymd');
		$uniqueToken = md5(microtime());
		$filePath    = 'images/' . $this->uParams['file_path'] . '/' . $nowPath . '/' . $uniqueToken;
		$uploadBase  = JPATH_BASE . '/' . $filePath;
		$uploadURL   = rtrim(JUri::base(), '/') . '/' . $filePath;

		if (!is_dir($uploadBase))
		{
			JFolder::create($uploadBase);
		}

		if (!file_exists($uploadBase . '/.htaccess'))
		{
			JFile::write($uploadBase . '/.htaccess', Text::_('JTF_SET_ATTACHMENT_HTACCESS'));
		}

		$return = array();

		$save     = null;
		$fileName = JFile::stripExt($validatedFile['name']);
		$fileExt  = JFile::getExt($validatedFile['name']);
		$name     = JFilterOutput::stringURLSafe($fileName) . '.' . $fileExt;

		$save = JFile::copy($validatedFile['tmp_name'], $uploadBase . '/' . $name);

		if ($save)
		{
			$return[$name] = $uploadURL . '/' . $name;
		}

		return $return;
	}

	/**
	 * Set all form validation errors, if any.
	 *
	 * @param   array  $errors  Array of error messages or RuntimeException objects.
	 *
	 * @return   void
	 * @since     11.1
	 */
	private function setErrors($errors)
	{
		foreach ($errors as $error)
		{
			$errorMessage = $error;

			if ($error instanceof \Exception)
			{
				$errorMessage = $error->getMessage();
			}

			$this->app->enqueueMessage($errorMessage, 'error');
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
		$value = (string) $this->uParams[$name];

		if (!empty($data[$name]))
		{
			return $data[$name];
		}

		if (!empty($value))
		{
			$value = $this->uParams[$name];

			if (!empty($data[$value]))
			{
				$value = $data[$value];
			}

			return $value;
		}

		return $value;
	}

	/**
	 * Get e-mail recipient credentials
	 *
	 * @return  array
	 *
	 * @since  3.0.0
	 */
	private function getEmailCredentials(): array
	{
		$recipients = array();

		foreach (array('mailto', 'cc', 'bcc', 'visitor_name', 'visitor_email') as $name)
		{
			$recipients[$name] = null;
			$recipient         = array();

			if (empty($this->uParams[$name]) && $name == 'mailto')
			{
				if (!empty(Factory::getConfig()->get('replyto')))
				{
					$recipients['mailto'][] = Factory::getConfig()->get('replyto');
				}
				else
				{
					$recipients['mailto'][] = Factory::getConfig()->get('mailfrom');
				}

				continue;
			}

			$items = explode(';', $this->uParams[$name]);

			if ($name == 'visitor_email')
			{
				$items = array($items[0]);
			}

			foreach ($items as $item)
			{
				$value = null;

				if (strpos($item, '@') !== false)
				{
					$value = trim($item);
				}

				if (strpos($item, '#') !== false)
				{
					$value = str_replace('#', '@', trim($item));
				}

				if (!empty($value))
				{
					$recipient[] = $value;

					continue;
				}

				if (empty($value = $this->getValue($item)))
				{
					continue;
				}

				if (is_string($value))
				{
					$value = (array) $value;
				}

				$value = array_values(array_filter($value));

				array_walk($value,
					function (&$email) {
						$email = str_replace('#', '@', trim($email));
					}
				);

				$recipient = array_merge($recipient, $value);
			}

			$recipients[$name] = is_array($recipient) ? ArrayHelper::arrayUnique($recipient) : $recipient;

			if (in_array($name, array('visitor_name', 'visitor_email')))
			{
				$recipients[$name] = trim(implode(' ', $recipients[$name]));
			}
		}

		return $recipients;
	}

	/**
	 * Get the HTML layout for the form
	 *
	 * @param   string  $filename  Filename of the HTML layout template
	 *
	 * @return  string
	 *
	 * @since  3.0.0
	 */
	private function getTmpl($filename)
	{
		$enctype       = '';
		$id            = $this->uParams['theme'];
		$index         = self::$count;
		$form          = $this->getForm();
		$form          = Jtf\Frameworks\FrameworkHelper::setFrameworkClasses($form);
		$formClass     = $form->getAttribute('class', '');
		$controlFields = '<input type="hidden" name="option" value="' . $this->app->input->get('option') . '" />'
			. '<input type="hidden" name="formTask" value="' . $id . $index . '_sendmail" />'
			. '<input type="hidden" name="view" value="' . $this->app->input->get('view') . '" />'
			. '<input type="hidden" name="Itemid" value="' . $this->app->input->get('Itemid') . '" />'
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
			'controlFields' => $controlFields,
			'fillouttime' => $this->uParams['fillouttime'],
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

	/**
	 * Set fieldset named 'submit' for the form, containing captcha and submit button
	 *
	 * @return  void
	 *
	 * @since  3.0.0
	 */
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

	/**
	 * @param   string  $fieldType     Field type to search for
	 * @param   string  $fieldsetName  Name of the fieldset to search into
	 *
	 * @return  false|string  False if the field is not set, else the field name
	 *
	 * @since  3.0.0
	 */
	private function issetField($fieldType, $fieldsetName = null)
	{
		$form   = $this->getForm();
		$fields = $form->getFieldset($fieldsetName);

		foreach ($fields as $field)
		{
			$type = (string) $field->getAttribute('type');

			if ($type == $fieldType)
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
		$jtfHp = md5('jtfhp' . Factory::getSession()->getToken());

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
				$cField  = new SimpleXMLElement('<field name="captcha" type="captcha" validate="captcha" description="JTF_CAPTCHA_DESC" label="JTF_CAPTCHA_LABEL" required="false"></field>');

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

		$operators = array('-', '+', '*');
		$operator  = $operators[rand(0, 2)];
		$number    = rand(1, 10);
		$hint      = $number . ' ' . $operator . ' ' . rand(1, $number);
		$hField    = new SimpleXMLElement(
			'<field name="' . $jtfHp . '" type="text" label="JTF_CAPTCHA_MATH" size="10" hint="' . $hint . '" gridgroup="jtfhp" notmail="1"></field>'
		);

		$form->setField($hField, null, true, 'submit');

		$formTheme = $form->getName();

		$this->app->setUserState('plugins.content.jtf.hp.' . $this->context . '.' . $formTheme, $jtfHp);
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
		$srcBase  = JPATH_BASE . '/images/0';
		$destBase = JPATH_BASE . '/images/' . $this->uParams['file_path'];
		$folders  = JFolder::folders($srcBase);

		foreach ($folders as $key => $folder)
		{
			$src  = $srcBase . '/' . $folder;
			$dest = $destBase . '/' . $folder;

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

	private function validateField($field, $form)
	{
//		$form          = $this->getForm();
		$value         = $field->value;
		$showon        = $field->showon;
		$showField     = true;
		$validateField = true;
		$valid         = false;
		$type          = strtolower($field->type);
		$validate      = $field->validate;
		$required      = $field->required;
		$fieldName     = $field->fieldname;

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
				$uploadmaxsize  = $field->uploadmaxsize;
				$uploadmaxsize  = number_format((float) $uploadmaxsize, 2) * 1024 * 1024;
				$fieldPath      = trim(str_replace(array('][', '[', ']'), array('.', '.', ''), $field->name), '.');
				$submittedFiles = $this->getSubmittedFiles($fieldPath);
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

	private function getMessageArticleContent()
	{
		$itemId       = (int) $this->uParams['message_article'];
		$activeLang   = $this->app->get('language');
		$assocArticle = Associations::getAssociations('com_content', '#__content', 'com_content.item', $itemId, 'id', null, null);;

		if (in_array($activeLang, array_keys($assocArticle)))
		{
			$itemId = $assocArticle[$activeLang]->id;
		}

		return $this->getContent($itemId)->text;
	}

	private function getContent($id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$query->concatenate(
				array(
					$db->quoteName('introtext'),
					$db->quoteName('fulltext'),
				)
			) . ' AS text'
		)
			->from('#__content')
			->where('id=' . $db->quote($id));

		$content = $db->setQuery($query)->loadObject();

		// Prepare content
		$content->text = JHtml::_('content.prepare', $content->text, '', 'mod_custom.content');

		return $content;
	}

	/**
	 * Remove caching if plugin is called
	 *
	 * @param   string  $context
	 *
	 * @return   void
	 * @since    3.0.0
	 */
	private function removeCache($context)
	{
		$cachePagePlugin = PluginHelper::isEnabled('system', 'cache');
		$cacheIsActive   = Factory::getConfig()->get('caching', 0) != 0
			? true
			: false;

		if (!$cacheIsActive && !$cachePagePlugin)
		{
			return;
		}

		$key         = (array) JUri::getInstance()->toString();
		$key         = md5(serialize($key));
		$group       = strstr($context, '.', true);
		$cacheGroups = array();

		if($cacheIsActive)
		{
			$cacheGroups = array(
				$group        => 'callback',
				'com_modules' => '',
				'com_content' => 'view',
			);
		}

		if($cachePagePlugin)
		{
			$cacheGroups['page'] = 'callback';
		}

		foreach ($cacheGroups as $group => $handler)
		{
			$cache = JFactory::getCache($group, $handler);
			$cache->cache->remove($key);
			$cache->cache->setCaching(false);
		}
	}

	/**
	 * Checks for a form token in the request.
	 *
	 * Use in conjunction with \JHtml::_('form.token') or Session::getFormToken.
	 *
	 * @param   string  $formTheme  The theme name of the form.
	 *
	 * @return  boolean  True if found and valid, false otherwise.
	 *
	 * @since  __DEPLOAY_VERSION__
	 */
	private function checkToken($formTheme)
	{
		$token = !empty($this->tokens[$this->context]->$formTheme)
			? $this->tokens[$this->context]->$formTheme
			: false;


		if ($token && $this->app->input->get($token, null, 'alnum'))
		{
			return true;
		}

		return false;
	}
}
