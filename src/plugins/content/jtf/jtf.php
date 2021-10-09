<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2021 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die('Restricted access');

JLoader::registerNamespace('Jtf', JPATH_PLUGINS . '/content/jtf/libraries/jtf', false, false, 'psr4');

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Profiler\Profiler;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;
use Jtf\Form\Form;
use Jtf\Framework\FrameworkHelper;
use Jtf\Input\Files;
use Jtf\Layout\FileLayout;

/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgContentJtf extends CMSPlugin
{
	/**
	 * The regular expression to identify Plugin call.
	 *
	 * @var   string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	const PLUGIN_REGEX1 = "@(<(\w+)[^>]*>\s?)?{jtf(\s.*)?/?}(?(1)\s?</\\2>|)@uU";

	/**
	 * Set counter
	 *
	 * @var   integer
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private static $count = 0;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var   boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Global application object
	 *
	 * @var   CMSApplication
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Set Form object
	 *
	 * @var   Form
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $_form;

	/**
	 * Array with User params
	 *
	 * @var   array[]
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $uParams = array();

	/**
	 * Array with extension names (URL option) where jtf should not be executed.
	 *
	 * @var   array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $excludeOnExtensions = array(
		'com_finder',
		'com_config',
	);

	/**
	 * @var   boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $doNotLoad = false;

	/**
	 * Array with allowed params to override
	 *
	 * @var   string[]
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $uParamsAllowedOverride = array(
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
	);

	/**
	 * @var   boolean
	 *
	 * @since  __DEPLOY_VERSION__
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
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(object $subject, array $config = array())
	{
		parent::__construct($subject, $config);

		if ($this->app->isClient('administrator'))
		{
			return null;
		}

		$this->debug = (boolean) $this->params->get('debug', 0);
		$option      = $this->app->input->getCmd('option');
		$isEdit      = $this->app->input->getCmd('layout') == 'edit';

		if (in_array($option, $this->excludeOnExtensions) || $isEdit)
		{
			$this->doNotLoad = true;
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
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 *
	 * @throws  \Exception
	 */
	public function onContentPrepare(string $context, object &$article, &$params, $page = 0)
	{
		// Don't run in administration Panel or when the content is being indexed
		if (strpos($article->text, '{jtf') === false
			|| ($context == 'com_content.category' && $this->app->input->getCmd('layout') != 'blog')
			|| $context == 'com_finder.indexer'
			|| $this->doNotLoad)
		{
			$this->app->setUserState('plugins.content.jtf.start', null);

			return;
		}

		// Get all matches or return
		if (!preg_match_all(self::PLUGIN_REGEX1, $article->text, $matches))
		{
			$this->app->setUserState('plugins.content.jtf.start', null);

			return;
		}

		FormHelper::addFieldPrefix('Jtf\\Form\\Field');
		FormHelper::addRulePath(JPATH_PLUGINS . '/content/jtf/libraries/jtf/Form/Rule');
		FormHelper::addRulePrefix('Jtf\\Form\\Rule');

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

		$jtfHp      = $this->app->getUserState('plugins.content.jtf.hp', null);
		$startTime  = $this->app->getUserState('plugins.content.jtf.start');
		$checkToken = $this->checkToken();

		Factory::getSession()->getToken(true);

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

			if (!empty($formLang))
			{
				Factory::getLanguage()->load('jtf_theme', $formLang);
			}

			// Get form submit task
			$formSubmitted = ($this->app->input->getCmd('formTask') == $formTheme . "_sendmail") ? true : false;

			$this->setSubmit();

			if ($formSubmitted)
			{
				$submittedValues = $this->app->input->get($formTheme, array(), 'post', 'array');
				$honeypot       = $submittedValues[$jtfHp];
				$fillOutTime    = $this->debug || JDEBUG || $this->uParams['fillouttime'] == 0
					? 100000
					: microtime(1) - $startTime;
				$notSpamBot     = $fillOutTime > $this->uParams['fillouttime'];

				if ($honeypot !== '' || !$notSpamBot || !$checkToken)
				{
					$this->app->setUserState('plugins.content.jtf.start', null);
					$this->app->setUserState('plugins.content.jtf.hp', null);
					$this->app->redirect(JRoute::_('index.php', false));
				}

				if (!empty($_FILES))
				{
					$jInput         = new Files;
					$submittedFiles  = $jInput->get($formTheme);
					$submittedValues = array_merge_recursive($submittedValues, $submittedFiles);
				}

				$this->getForm()->bind($submittedValues);
				$this->setFieldValidates();

				$valid = $this->getForm()->validate($submittedValues);

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
							$this->app->setUserState('plugins.content.jtf.start', null);
							$this->app->setUserState('plugins.content.jtf.hp', null);
							$this->app->enqueueMessage($text, 'message');
							$this->app->redirect(JRoute::_('index.php', false));
						}
						else
						{
							$this->app->setUserState('plugins.content.jtf.start', null);
							$this->app->setUserState('plugins.content.jtf.hp', null);
							$this->app->redirect(JRoute::_('index.php?Itemid=' . (int) $this->uParams['redirect_menuid'], false));
						}
					}
				}

				$this->setErrors($this->getForm()->getErrors());
			}

			$html .= $this->getTmpl('form');

			$startPos = strpos($article->text, $replacement);
			$endPos = strlen($replacement);

			$article->text = substr_replace($article->text, $html, $startPos, $endPos);
			self::$count++;

			$this->clearOldFiles();
			$this->app->setUserState('plugins.content.jtf.start', microtime(1));
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
	 * @since  __DEPLOY_VERSION__
	 */
	private function init(string $userParams)
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
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function resetUserParams()
	{
		$this->uParams = array();
		$this->_form   = null;

		// Set default minimum fill out time
		$this->uParams['fillouttime'] = 0;
		$fillOutTimeOnOff = filter_var(
			$this->params->get('filloutTime_onoff'),
			FILTER_VALIDATE_BOOLEAN
		);

		if ($fillOutTimeOnOff)
		{
			$this->uParams['fillouttime'] = $this->params->get('filloutTime', 10);
		}

		// Set the default value for field description type
		$this->uParams['show_field_description_as'] = $this->params->get('show_field_description_as');

		// Set the default value for field marker
		$this->uParams['field_marker'] = $this->params->get('field_marker');

		// Set the default value for field marker place
		$this->uParams['field_marker_place'] = $this->params->get('field_marker_place');

		// Set the default option to display the required field description.
		$this->uParams['show_required_field_description'] = filter_var(
			$this->params->get('show_required_field_description'),
			FILTER_VALIDATE_BOOLEAN
		);

		if ($this->uParams['field_marker'] == 'optional' || $this->uParams['field_marker_place'] != 'label')
		{
			$this->uParams['show_required_field_description'] = false;
		}

		// Set default option to show captcha
		$this->uParams['captcha'] = filter_var(
			$this->params->get('captcha'),
			FILTER_VALIDATE_BOOLEAN
		);

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
		if ($this->params->get('framework') == 'joomla' || empty($this->uParams['framework'] = (array) $this->params->get('framework')))
		{
			if (version_compare(JVERSION, '4', 'ge'))
			{
				$this->uParams['framework'] = array('bs5');
			}
			else
			{
				$this->uParams['framework'] = array('bs2');
			}
		}
	}

	/**
	 * Set user Params and override default values
	 *
	 * @param   array  $vars  Params pairs from plugin call
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
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
	 * @param   string   $filePath   Filepath relative from inside of the theme
	 *
	 * @return  boolean|string  False on error
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function getThemePath(string $filePath)
	{
		$error    = array();
		$files    = array();
		$pathInfo = pathinfo($filePath);
		$ext      = $pathInfo['extension'];
		$file     = $pathInfo['filename'];
		$template = $this->app->getTemplate();

		foreach ($this->uParams['framework'] as $frwk)
		{
			$files[] = $file . '.' . $frwk . '.' . $ext;
		}

		$files[] = $filePath;
		$files   = ArrayHelper::arrayUnique($files);

		if ($template == 'yootheme')
		{
			$tParams = json_decode($this->app->getTemplate(true)->params->get('config'));

			empty($tParams->child_theme) ? null : $template .= '_' . $tParams->child_theme;
		}

		$absPaths = array();

		// Build template override path for theme
		$absPaths[] = JPATH_THEMES . '/' . $template
			. '/html/plg_content_jtf/'
			. $this->uParams['theme'];

		// Build plugin path for theme
		$absPaths[] = JPATH_PLUGINS . '/content/jtf/tmpl/'
			. $this->uParams['theme'];

		$error['path'] = true;

		foreach ($files as $filename)
		{
			foreach ($absPaths as $absPath)
			{
				// Set the right theme path
				if (is_dir($absPath))
				{
					if (!empty($error['path']))
					{
						unset($error['path']);
					}

					if (file_exists($absPath . '/' . $filename))
					{
						if ($ext == 'ini')
						{
							return $absPath;
						}

						return $absPath . '/' . $filename;
					}

					$error['file'] = true;
				}
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
	 * @since  __DEPLOY_VERSION__
	 */
	private function getForm(): Form
	{
		if (!empty($this->_form))
		{
			return $this->_form;
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
			JPATH_PLUGINS . '/content/jtf/layouts/joomla' . Version::MAJOR_VERSION,
			JPATH_PLUGINS . '/content/jtf/layouts',
			JPATH_SITE . '/layouts',
		);

		$form->showRequiredFieldDescription = $this->uParams['show_required_field_description'];
		$form->showfielddescriptionas       = $this->uParams['show_field_description_as'];
		$form->fieldmarker                  = $this->uParams['field_marker'];
		$form->fieldmarkerplace             = $this->uParams['field_marker_place'];

		$this->_form = $form;

		return $form;
	}

	/**
	 * Defines field validation, if not defined
	 *
	 * @param   Form  $form  Form or subform object
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function setFieldValidates($form = null)
	{
		$setValidationFor = array(
			'calendar',
			'captcha',
			'checkboxes',
			'color',
			'email',
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
			$fieldName = $field->fieldname;

			if ($fieldType == 'subform')
			{
				if ($field->validate == '')
				{
					$form->setFieldAttribute($fieldName, 'validate', 'subform');
				}

				$subform = $form->getField($fieldName)->loadSubForm();
				$this->setFieldValidates($subform);

				continue;
			}

			if (in_array($fieldType, $setValidationFor))
			{
				if ($field->validate == '')
				{
					if (in_array($fieldType, array('checkboxes', 'list', 'radio')))
					{
						$form->setFieldAttribute($fieldName, 'validate', 'options');
					}
					else
					{
						$form->setFieldAttribute($fieldName, 'validate', $fieldType);
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
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function cleanSubmittedFiles(array $submittedFiles, array $submittedValues): array
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
							$this->setErrors((array) Text::_('Fehler beim Speichern!'));

							continue;
						}
					}

					$validatedFiles[$key] = $this->cleanSubmittedFiles($submittedFiles[$key], $value);
				}
			}
		}

		return $validatedFiles;
	}

	/**
	 * Save submitted files
	 *
	 * @param   array  $validatedFile  List of submitted files
	 *
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function saveFiles(array $validatedFile): array
	{
		$nowPath     = date('Ymd');
		$uniqueToken = md5(microtime());
		$filePath    = 'images/' . $this->uParams['file_path'] . '/' . $nowPath . '/' . $uniqueToken;
		$uploadBase  = JPATH_BASE . '/' . $filePath;
		$uploadURL   = rtrim(JUri::base(), '/') . '/' . $filePath;

		if (!is_dir($uploadBase))
		{
			Folder::create($uploadBase);
		}

		if (!file_exists($uploadBase . '/.htaccess'))
		{
			File::write($uploadBase . '/.htaccess', Text::_('JTF_SET_ATTACHMENT_HTACCESS'));
		}

		$return = array();

		$save     = null;
		$fileName = File::stripExt($validatedFile['name']);
		$fileExt  = File::getExt($validatedFile['name']);
		$name     = OutputFilter::stringURLSafe($fileName) . '.' . $fileExt;

		$save = File::copy($validatedFile['tmp_name'], $uploadBase . '/' . $name);

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
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function setErrors(array $errors)
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

	/**
	 * Send e-mail to recipients
	 *
	 * @return  boolean|\RuntimeException  Boolean true if successful, boolean false if the `mailonline` configuration is set to 0,
	 *                                     or a JException object if the mail function does not exist or sending the message fails.
	 *
	 * @since  __DEPLOY_VERSION__
	 *
	 * @throws  \RuntimeException
	 */
	private function sendMail()
	{
		$mailer  = Factory::getMailer();

		$subject = $this->getValue('subject');
		$subject = !empty($subject) ? $subject : Text::sprintf('JTF_EMAIL_SUBJECT', $this->app->get('sitename'));

		$emailCredentials = $this->getEmailCredentials();

		$recipient     = $emailCredentials['mailto'];
		$cc            = $emailCredentials['cc'];
		$bcc           = $emailCredentials['bcc'];
		$replayToName  = $emailCredentials['visitor_name'];
		$replayToEmail = $emailCredentials['visitor_email'];

		if (empty($replayToEmail))
		{
			if (!empty($this->app->get('replyto')))
			{
				$replayToEmail = $this->app->get('replyto');
			}
			else
			{
				$replayToEmail = $this->app->get('mailfrom');
			}

			$replayToName = $this->app->get('fromname');
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

		return $mailer->Send();
	}

	/**
	 * Get submitted value
	 *
	 * @param   string  $name  Field name of submitted value
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function getValue(string $name): string
	{
		$data  = $this->getForm()->getData()->toArray();
		$value = (string) $this->uParams[$name];

		if (!empty($data[$name]))
		{
			return $data[$name];
		}

		if (!empty($value))
		{
			if (!empty($data[$value]))
			{
				return $data[$value];
			}

			return $value;
		}

		return '';
	}

	/**
	 * Get e-mail recipient credentials
	 *
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
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
				if (!empty($this->app->get('replyto')))
				{
					$recipients['mailto'][] = $this->app->get('replyto');
				}
				else
				{
					$recipients['mailto'][] = $this->app->get('mailfrom');
				}

				continue;
			}

			$items = explode(';', (string) $this->uParams[$name]);

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
	 * @since  __DEPLOY_VERSION__
	 */
	private function getTmpl(string $filename): string
	{
		$enctype       = '';
		$id            = $this->uParams['theme'];
		$index         = self::$count;
		$form          = $this->getForm();
		$form          = FrameworkHelper::setFrameworkClasses($form);
		$formClass     = $form->getAttribute('class', '');
		$controlFields = '<input type="hidden" name="option" value="' . $this->app->input->get('option') . '" />'
			. '<input type="hidden" name="formTask" value="' . $id . $index . '_sendmail" />'
			. '<input type="hidden" name="view" value="' . $this->app->input->get('view') . '" />'
			. '<input type="hidden" name="Itemid" value="' . $this->app->input->get('Itemid') . '" />'
			. '<input type="hidden" name="id" value="' . $this->app->input->get('id') . '" />';

		if ($form->setEnctype)
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
			'fillouttime'   => $this->uParams['fillouttime'],
		);

		$renderer = new FileLayout($filename);

		// Set Framwork as Layout->Suffix
		$renderer->setSuffixes($this->uParams['framework']);

		$renderer->setIncludePaths($form->layoutPaths);
		$renderer->setDebug($this->debug);

		return $renderer->render($displayData);
	}

	/**
	 * Get the content from article set as parameter for submit confirmation message
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 *
	 * @throws  \Exception
	 */
	private function getMessageArticleContent(): string
	{
		$itemId       = (int) $this->uParams['message_article'];
		$activeLang   = $this->app->get('language');
		$assocArticle = Associations::getAssociations(
			'com_content',
			'#__content',
			'com_content.item',
			$itemId,
			'id',
			null,
			null
		);

		if (in_array($activeLang, array_keys($assocArticle)))
		{
			$itemId = $assocArticle[$activeLang]->id;
		}

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
			->where('id=' . $db->quote($itemId));

		$content = $db->setQuery($query)->loadObject();

		// Prepare content
		$content->text = HTMLHelper::_('content.prepare', $content->text, '', 'mod_custom.content');

		return $content->text;
	}

	/**
	 * Set fieldset named 'submit' for the form, containing captcha and submit button
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function setSubmit()
	{
		$form           = $this->getForm();
		$captcha        = array();
		$button         = array();
		$submitFieldset = $form->getFieldset('submit');

		if (!empty($submitFieldset))
		{
			if (!empty($issetCaptcha = $this->issetField('captcha', 'submit')))
			{
				$captcha['submit'] = $issetCaptcha;
			}

			if (!empty($issetButton = $this->issetField('submit', 'submit')))
			{
				$button['submit'] = $issetButton;
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
	 * @since  __DEPLOY_VERSION__
	 */
	private function issetField(string $fieldType, $fieldsetName = null)
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
	 * @param   mixed  $captcha  Field name of captcha
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function setCaptcha($captcha)
	{
		$form  = $this->getForm();
		$jtfHp = md5('jtfhp' . Factory::getSession()->getToken());

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
				$cField  = new SimpleXMLElement(
					'<field name="primaryc" type="captcha" validate="captcha" '
					. 'description="JTF_CAPTCHA_DESC" label="JTF_CAPTCHA_LABEL" required="true"></field>'
				);

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

		$this->app->setUserState('plugins.content.jtf.hp', $jtfHp);
	}

	/**
	 * Set submit button to submit fieldset
	 *
	 * @param   mixed  $submit  Field name of submit button
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
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
			$cField = new SimpleXMLElement(
				'<field name="submit" type="submit" label="JTF_SUBMIT_BUTTON" hiddenlabel="true" gridlabel="jtfhp" notmail="1"></field>'
			);

			$form->setField($cField, null, true, 'submit');
		}
	}

	/**
	 * Delete outdated files
	 *
	 * @return  void
	 * @throws  \Exception
	 *
	 * @since  __DEPLOY_VERSION__
	 */
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

		$folders = Folder::folders($uploadBase);
		$nowPath = date('Ymd');
		$now     = new DateTime($nowPath);

		foreach ($folders as $folder)
		{
			$date   = new DateTime($folder);
			$clear = date_diff($now, $date)->days;

			if ($clear >= $fileClear)
			{
				Folder::delete($uploadBase . '/' . $folder);
			}
		}
	}

	/**
	 * Fix a bug for the upload folder path
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function bugfixUploadFolder()
	{
		$srcBase  = JPATH_BASE . '/images/0';
		$destBase = JPATH_BASE . '/images/' . $this->uParams['file_path'];
		$folders  = Folder::folders($srcBase);

		foreach ($folders as $key => $folder)
		{
			$src    = $srcBase . '/' . $folder;
			$dest   = $destBase . '/' . $folder;
			$moved  = false;
			$copied = false;

			if (is_dir($dest))
			{
				$copied = Folder::copy($src, $dest, '', true);
			}
			else
			{
				$moved = Folder::move($src, $dest);
			}

			if ($copied === true || $moved === true)
			{
				unset($folders[$key]);
			}
		}

		if (empty($folders))
		{
			Folder::delete($srcBase);
		}
	}

	/**
	 * Remove caching if plugin is called
	 *
	 * @param   string  $context  Context to identify the cache
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function removeCache(string $context)
	{
		$cachePagePlugin = PluginHelper::isEnabled('system', 'cache');
		$cacheIsActive   = $this->app->get('caching', 0) != 0;

		if (!$cacheIsActive && !$cachePagePlugin)
		{
			return;
		}

		$key         = (array) JUri::getInstance()->toString();
		$key         = md5(serialize($key));
		$group       = strstr($context, '.', true);
		$cacheGroups = array();

		if ($cacheIsActive)
		{
			$cacheGroups = array(
				$group        => 'callback',
				'com_modules' => '',
				'com_content' => 'view',
			);
		}

		if ($cachePagePlugin)
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
	 * @param   string  $method  The request method in which to look for the token key.
	 *
	 * @return  boolean  True if found and valid, false otherwise.
	 *
	 * @since  __DEPLOAY_VERSION__
	 */
	private function checkToken($method = 'post')
	{
		$token = Session::getFormToken();

		// Check from header first
		if ($token === $this->app->input->server->get('HTTP_X_CSRF_TOKEN', '', 'alnum'))
		{
			return true;
		}

		// Then fallback to HTTP query
		if (!$this->app->input->$method->get($token, '', 'alnum'))
		{
			if (Factory::getSession()->isNew())
			{
				return true;
			}

			return false;
		}

		return true;
	}
}
