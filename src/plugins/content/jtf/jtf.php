<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.jtf
 *
 * @author       Guido De Gobbis
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/

defined('_JEXEC') or die('Restricted access');

class plgContentJtf extends JPlugin
{
	/**
	 * The regular expression to identify Plugin call.
	 *
	 * @var     string
	 * @since   1.0
	 */
	const PLUGIN_REGEX = "@(<(\w+)[^>]*>|){jtf(\s.*)?}(</\\2>|)@";

	/**
	 * Honeypot
	 *
	 * @var     string
	 * @since   1.0
	 */
	protected $honeypot;

	/**
	 * TODO Desctiption
	 *
	 * @var     string
	 * @since   1.0
	 */
	protected $issetCaptcha;

	/**
	 * TODO Desctiption
	 *
	 * @var     boolean
	 * @since   1.0
	 */
	protected $validCaptcha = true;

	/**
	 * Set enctype in form field if a file field is set
	 *
	 * @var     boolean
	 * @since   1.0
	 */
	protected $setEnctype = false;

	/**
	 * JFormField validation
	 *
	 * @var     boolean
	 * @since   1.0
	 */
	protected $validField = true;

	/**
	 * Array with JFormField Names of submitted Files
	 *
	 * @var     array
	 * @since   1.0
	 */
	protected $fileFields = array();

	/**
	 * Array with submitted Files
	 *
	 * @var     array
	 * @since   1.0
	 */
	protected $submitedFiles = array();

	/**
	 * Array with JForm Objects
	 *
	 * @var     array
	 * @since   1.0
	 */
	protected $form = array();

	/**
	 * Array with User params
	 *
	 * @var     array
	 * @since   1.0
	 */
	protected $uParams = array();

	/**
	 * Mail
	 *
	 * @var     array
	 * @since   1.0
	 */
	protected $mail = array();

	/**
	 * Debug
	 *
	 * @var     boolean
	 * @since   1.0
	 */
	protected $debug = false;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var     boolean
	 * @since   3.1
	 */
	protected $autoloadLanguage = true;

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
		$app = JFactory::getApplication();

		// Don't run in administration Panel or when the content is being indexed
		if ($app->isAdmin()
			|| $context == 'com_finder.indexer'
			|| strpos($article->text, '{jtf') === false
		)
		{
			return;
		}

		$this->debug = (boolean) $this->params->get('debug', 0);
		$cIndex      = 0;
		$template    = $app->getTemplate();
		$lang        = JFactory::getLanguage();
		$langTag     = $lang->getTag();

		// Get all matches or return
		if (!preg_match_all(self::PLUGIN_REGEX, $article->text, $matches))
		{
			return;
		}

		$pluginReplacements = $matches[0];
		$userParams         = $matches[3];

		JLoader::register('JForm', dirname(__FILE__) . '/assets/form.php');
		JLoader::register('JTLayoutFile', dirname(__FILE__) . '/assets/file.php');
		JLoader::register('JFormField', dirname(__FILE__) . '/assets/field.php');

		// Add form fields
		JFormHelper::addFieldPath(dirname(__FILE__) . '/assets/fields');

		// Add form rules
		JFormHelper::addRulePath(dirname(__FILE__) . '/assets/rules');

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

			$formTheme = $this->uParams['theme'] . $cIndex;

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

				$form = new JForm($formTheme, array('control' => $formTheme));

				// Load Formfields
				$form->loadFile($formXmlPath);

				// Set Formfields
				$this->form[$formTheme] = $form;

				// Define framework as layout suffix
				$layoutSuffix = array('');

				if (!empty($this->uParams['framework']))
				{
					$layoutSuffix = array($this->uParams['framework']);
				}

				// Set Framework as Layout->Suffix
				$this->form[$formTheme]->framework = $layoutSuffix;

				// Set Debug for Layouts override
				$this->form[$formTheme]->rendererDebug = $this->debug;

				// Set Layouts override
				$this->form[$formTheme]->layoutPaths = array(
					JPATH_THEMES . '/' . $template . '/html/plg_content_jtf/' . $this->uParams['theme'],
					JPATH_THEMES . '/' . $template . '/html/layouts/plugin/content/jtf',
					JPATH_THEMES . '/' . $template . '/html/layouts',
					JPATH_PLUGINS . '/content/jtf/layouts/jtf',
					JPATH_PLUGINS . '/content/jtf/layouts');

				$this->setSubmit();
				$this->setFrameworkFieldClass();

				// Get form submit task
				$task = $app->input->get('task', false, 'post');

				if ($task == $formTheme . "_sendmail")
				{
					$submitValues = $this->getTranslatedSubmittedFormValues();

					switch (true)
					{
						case isset($submitValues['subject']):
							$this->mail['subject'] = 'subject';
							break;

						case !isset($submitValues['subject'])
							&& isset($this->uParams['subject']):
							$submitValues['subject'] = $this->mail['subject'] = $this->uParams['subject'];
							break;

						default:
							$submitValues['subject'] = $this->mail['subject'] = '';
							break;
					}

					$this->form[$formTheme]->bind($submitValues);

					if ($submitValues['jtf_important_notices'] == '')
					{
						$valid = $this->validate();
					}
					else
					{
						$valid = false;
					}

				}

				$html .= $this->getTmpl('form');

				if ($task == $formTheme . "_sendmail")
				{
					if (!empty($submitValues['jtf_important_notices']))
					{
						$app->redirect(JRoute::_('index.php', false));
					}

					if ($valid)
					{
						$sendmail = $this->sendMail();

						if ($sendmail)
						{
							$app->enqueueMessage(JText::_('JTF_EMAIL_THANKS'), 'message');
							$app->redirect(JRoute::_('index.php', false));
						}
					}

				}

			}

			$pos = strpos($article->text, $replacement);
			$end = strlen($replacement);

			$article->text = substr_replace($article->text, $html, $pos, $end);
			$cIndex++;
			$this->resetUserParams();
		}
	}

	/**
	 * Reset user Params to default
	 *
	 * @return   void
	 * @since    1.0
	 */
	protected function resetUserParams()
	{
		$this->uParams       = array();
		$version             = new JVersion();
		$joomla_main_version = substr($version->RELEASE, 0, strpos($version->RELEASE, '.'));

		// Set default captcha value
		$this->uParams['captcha'] = $this->params->get('captcha');

		// Set Joomla main version
		$this->uParams['jversion'] = $joomla_main_version;

		// Set default recipient
		$this->uParams['mailto'] = JFactory::getConfig()->get('mailfrom');

		// Set default theme
		$this->uParams['theme'] = 'default';

		// Set default framework value
		$this->uParams['framework'] = $this->params->get('framework', 0);
	}

	/**
	 * Set user Params
	 *
	 * @param   array $vars Params pairs from Plugin call
	 *
	 * @return   array
	 * @since    1.0
	 */
	protected function setUserParams($vars)
	{
		$uParams = array();

		if (!empty($vars))
		{
			foreach ($vars as $var)
			{
				list($key, $value) = explode('=', trim($var));
				$uParams[trim($key)] = trim($value);
			}

		}

		if (!empty($uParams['mailto']))
		{
			$uParams['mailto'] = str_replace('#', '@', $uParams['mailto']);
		}

		if (!empty($uParams['sender']))
		{
			$this->mail['sender'] = explode(' ', $uParams['sender']);
			unset($uParams['sender']);
		}

		// Merge user params width default params
		$this->uParams = array_merge($this->uParams, $uParams);

		return $uParams;
	}

	/**
	 * Checks if all needed files for Forms are found
	 *
	 * @return   bool
	 * @since    1.0
	 */
	protected function getFieldsFile()
	{
		$app       = JFactory::getApplication();
		$template  = $app->getTemplate();
		$framework = !empty($this->uParams['framework']) ? '.' . $this->uParams['framework'] : '';
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

		$app->enqueueMessage(
			JText::sprintf('JTF_THEME_ERROR', $this->uParams['theme'])
			, 'error'
		);

		return false;
	}

	protected function getLanguagePath($filename)
	{
		$template = JFactory::getApplication()->getTemplate();

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

	protected function setSubmit()
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

	protected function getForm()
	{
		return $this->form[$this->uParams['theme'] . (int) $this->uParams['index']];
	}

	protected function issetField($fieldtype, $fieldsetname = null)
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

	protected function setFrameworkFieldClass()
	{
		$form          = $this->getForm();
		$formclass     = explode(' ', $form->getAttribute('class', ''));
		$path = dirname(__FILE__);
		$framework = 'joomla';

		if (!empty($form->framework[0]))
		{
			$framework = $form->framework[0];
		}

		if (file_exists($path . '/assets/frameworks/' . $framework . '.php'))
		{
			include_once $path . '/assets/frameworks/' . $framework . '.php';
		}
		else
		{
			include_once $path . '/assets/frameworks/joomla.php';
			$framework = 'joomla';
		}

		$frwkClassName = 'JTFFramework' . ucfirst($framework);
		$frwkClasses = new $frwkClassName($formclass);
		$classes = $frwkClasses->getClasses();

		if (!empty($form->getAttribute('gridlabel')))
		{
			$classes['class']['gridlabel'][] = $form->getAttribute('gridlabel');
		}

		if (!empty($form->getAttribute('gridfield')))
		{
			$classes['class']['gridfield'][] = $form->getAttribute('gridfield');
		}

		$fields = $form->getFieldset();

		foreach ($fields as $field)
		{
			$this->setFieldClass($field->getAttribute('name'), $classes);
		}
	}

	protected function setFieldClass($fieldname, $frwkClasses)
	{
		$form  = $this->getform();
		$field = $form->getField($fieldname);
		$type  = strtolower($field->getAttribute('type'));
		$classes = array(
			'frwkDefaultClass' => array(),
			'frwkFieldClass'   => array(),
			'fieldClass'       => array(),
		);

		if (in_array($type, array('file')))
		{
			$this->setEnctype = true;
		}

		if (in_array($type, array('text', 'plz', 'tel')))
		{
			if (!empty($frwkClasses['class']['default']))
			{
				$classes['frwkDefaultClass'] = array_flip($frwkClasses['class']['default']);
			}
		}

		if (!empty($frwkClasses['class'][$type]['field']))
		{
			$classes['frwkFieldClass'] = array_flip($frwkClasses['class'][$type]['field']);
		}

		if (!empty($form->getFieldAttribute($fieldname, 'class')))
		{
			$classes['fieldClass'] = array_flip(
				explode(' ', $form->getFieldAttribute($fieldname, 'class'))
			);
		}

		if (in_array($type, array('checkboxes', 'radio', 'textarea', 'captcha')))
		{
			$form->setFieldAttribute($fieldname, 'icon', null);

		}

		if (in_array($type, array('checkboxes', 'radio')))
		{
			$field->setOptionsClass($frwkClasses['class'][$type]['options']);
		}

		if (in_array($type, array('submit', 'calendar', 'color', 'file')))
		{
			$uploadicon  = null;
			$buttonicon  = null;
			$buttonclass = null;

			if (!empty($frwkClasses['class'][$type]['uploadicon']))
			{
				$uploadicon = $frwkClasses['class'][$type]['uploadicon'];
			}

			if (!empty($frwkClasses['class'][$type]['buttons']['class']))
			{
				$buttonclass = $frwkClasses['class'][$type]['buttons']['class'];
			}

			if (!empty($frwkClasses['class'][$type]['buttons']['icon']))
			{
				$buttonicon = $frwkClasses['class'][$type]['buttons']['icon'];
			}

			if (!empty($form->getFieldAttribute($fieldname, 'icon')))
			{
				if ($type == 'file')
				{
					$uploadicon = $form->getFieldAttribute($fieldname, 'icon');
				}
				else
				{
					$buttonicon = $form->getFieldAttribute($fieldname, 'icon');
				}
			}

			if (!empty($form->getFieldAttribute($fieldname, 'uploadicon')))
			{
				$uploadicon = $form->getFieldAttribute($fieldname, 'uploadicon');
			}

			if (!empty($form->getFieldAttribute($fieldname, 'buttonicon')))
			{
				$buttonicon = $form->getFieldAttribute($fieldname, 'buttonicon');
			}

			if (($type == 'submit' || $type == 'file') && !empty($classes['fieldClass']))
			{
				$buttonclass = implode(' ', array_keys($classes['fieldClass']));
				$classes['fieldClass'] = array();
			}

			if (!empty($form->getFieldAttribute($fieldname, 'buttonclass')))
			{
				$buttonclass = $form->getFieldAttribute($fieldname, 'buttonclass');
			}

			if (!empty($uploadicon))
			{
				$form->setFieldAttribute($fieldname, 'uploadicon', $uploadicon);
			}

			if (!empty($buttonicon))
			{
				$form->setFieldAttribute($fieldname, 'buttonicon', $buttonicon);
			}

			if (!empty($buttonclass))
			{
				$form->setFieldAttribute($fieldname, 'buttonclass', $buttonclass);
			}

			if (!empty($uploadicon) || !empty($buttonicon) || !empty($buttonclass))
			{
				$form->setFieldAttribute($fieldname, 'icon', null);
			}

		}

		$class = array_merge($classes['frwkDefaultClass'], $classes['frwkFieldClass'], $classes['fieldClass']);
		$fieldClass = array_keys($class);

		$form->setFieldAttribute($fieldname, 'class', implode(' ', $fieldClass));

		$grid['group']['frwk'] = !empty($frwkClasses['class']['gridgroup']) ? array_flip($frwkClasses['class']['gridgroup']) : array();
		$grid['label']['frwk'] = !empty($frwkClasses['class']['gridlabel']) ? array_flip($frwkClasses['class']['gridlabel']) : array();
		$grid['field']['frwk'] = !empty($frwkClasses['class']['gridfield']) ? array_flip($frwkClasses['class']['gridfield']) : array();
		$grid['group']['field'] = array();
		$grid['label']['field'] = array();
		$grid['field']['field'] = array();

		if (!empty($form->getFieldAttribute($fieldname, 'gridgroup')))
		{
			$grid['group']['field'] = array_flip(
				explode(' ', $form->getFieldAttribute($fieldname, 'gridgroup'))
			);
		}

		if (!empty($form->getFieldAttribute($fieldname, 'gridlabel')))
		{
			$grid['label']['field'] = array_flip(
				explode(' ', $form->getFieldAttribute($fieldname, 'gridlabel'))
			);
		}

		if (!empty($form->getFieldAttribute($fieldname, 'gridfield')))
		{
			$grid['field']['field'] = array_flip(
				explode(' ', $form->getFieldAttribute($fieldname, 'gridfield'))
			);
		}

		$gridgroup = array_keys(array_merge($grid['group']['frwk'], $grid['group']['field']));
		$gridlabel = array_keys(array_merge($grid['label']['frwk'], $grid['label']['field']));
		$gridfield = array_keys(array_merge($grid['field']['frwk'], $grid['field']['field']));

		$form->setFieldAttribute($fieldname, 'gridgroup', implode(' ', $gridgroup));
		$form->setFieldAttribute($fieldname, 'gridlabel', implode(' ', $gridlabel));
		$form->setFieldAttribute($fieldname, 'gridfield', implode(' ', $gridfield));

		return;
	}

	/**
	 * Get and translate submitted Form values
	 *
	 * @param   array  $submittedValues  Array of submitted values
	 *
	 * @return   array
	 * @since    1.0
	 */
	protected function getTranslatedSubmittedFormValues($submittedValues = array())
	{
		$app       = JFactory::getApplication();
		$formTheme = $this->uParams['theme'] . $this->uParams['index'];

		// Get Form values
		if (empty($submittedValues))
		{
			$submittedValues = $app->input->get($formTheme, array(), 'post', 'array');
		}

		foreach ($submittedValues as $subKey => $_subValue)
		{
			if (is_array($_subValue))
			{
				$subValue = $this->getTranslatedSubmittedFormValues($_subValue);
			}
			else
			{
				$subValue = JText::_($_subValue);
			}

			$submittedValues[$subKey] = $subValue;
		}

		return $submittedValues;
	}

	protected function validate()
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

	protected function validateField($field)
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

		if ($showon)
		{
			$_showon_value    = explode(':', $showon);
			$_showon_value[1] = JText::_($_showon_value[1]);
			$showon_value     = $form->getValue($_showon_value[0]);

			if (!in_array($_showon_value[1], $showon_value))
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
				$value = $this->getSubmittedFiles($fieldName);
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
							$val = in_array(JText::_($_val), $value) ? JText::_($_val) : $_val;
						}
						else
						{
							$val = $value == JText::_($_val) ? $value : $_val;
						}

						$oField->option[$i]->attributes()->value = $val;
					}
				}
			}

			if ($type == 'email')
			{
				$form->setFieldAttribute($fieldName, 'tld', 'tld');

				if ($required || !empty($value))
				{
					$this->mail['sender_email'] = 'email';
				}
			}

			if ($validate)
			{
				$rule = JFormHelper::loadRuleType($validate);
			}
			else
			{
				$rule = JFormHelper::loadRuleType($type);
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
	protected function getSubmittedFiles($fieldName)
	{
		$value       = null;
		$index       = (int) $this->uParams['index'];
		$jinput      = JFactory::getApplication()->input;
		$submitFiles = $jinput->files->get($this->uParams['theme'] . $index);

		$issetFiles = false;

		if (!empty($submitFiles[$fieldName][0]['name']))
		{
			$issetFiles = true;
			$files      = $submitFiles[$fieldName];
		}
		elseif (!empty($submitFiles[$fieldName]['name']))
		{
			$issetFiles = true;
			$files      = array($submitFiles[$fieldName]);
		}

		if ($issetFiles)
		{
			$value                           = $files;
			$this->submitedFiles[$fieldName] = $files;
			$this->fileFields[]              = $fieldName;
		}

		return $value;
	}

	protected function invalidField($fieldName)
	{
		$form       = $this->getForm();
		$label      = JText::_($form->getFieldAttribute($fieldName, 'label'));
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
			JFactory::getApplication()
				->enqueueMessage((string) $this->validCaptcha, 'error');
		}
		else
		{
			JFactory::getApplication()
				->enqueueMessage(
					JText::sprintf('JTF_FIELD_ERROR', $label), 'error'
				);
		}

		$this->validField = false;
	}

	protected function clearOldFiles()
	{
		jimport('joomla.filesystem.folder');

		if (!$fileClear = (int) $this->params->get('file_clear'))
		{
			return;
		}

		$filePath   = !$this->params->get('file_path', 'uploads')
			? 'images/uploads'
			: 'images/' . $this->params->get('file_path');
		$uploadBase = JPATH_BASE . '/' . $filePath;

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

	protected function saveFiles()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$form          = $this->getForm();
		$submitedFiles = $this->submitedFiles;
		$nowPath       = date('Ymd');

		$filePath = 'images/' . $this->params->get('file_path', 'uploads');

		$uploadBase = JPATH_BASE . '/' . $filePath . '/' . $nowPath;
		$uploadURL  = rtrim(JUri::base(), '/') . '/' . $filePath . '/' . $nowPath;

		if (!is_dir($uploadBase))
		{
			JFolder::create($uploadBase);
		}

		if (!file_exists(JPATH_BASE . '/' . $filePath . '/.htaccess'))
		{
			JFile::write(JPATH_BASE . '/' . $filePath . '/.htaccess', JText::_('JTF_SET_ATTACHMENT_HTACCESS'));
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

	protected function getTmpl($filename)
	{
		$index   = $this->uParams['index'];
		$id      = $this->uParams['theme'];
		$form    = $this->getForm();
		$enctype = '';

		if ($this->setEnctype)
		{
			$enctype = ' enctype="multipart/form-data"';
		}

		$displayData = array(
			'id'        => $id,
			'index'     => (int) $index,
			'fileClear' => $this->params->get('file_clear'),
			'form'      => $form,
			'enctype'   => $enctype,
		);

		$renderer = new JTLayoutFile($filename);

		// Set Framwork as Layout->Suffix
		if (!empty($this->uParams['framework']))
		{
			$renderer->setSuffixes(array($this->uParams['framework']));
		}

		$renderer->addIncludePaths($form->layoutPaths);
		$renderer->setDebug($this->debug);

		return $renderer->render($displayData);
	}

	protected function sendMail()
	{
		$jConfig = JFactory::getConfig();
		$index   = $this->uParams['index'];
		$data    = $this->form[$this->uParams['theme'] . $index]->getData()->toArray();

		if ($this->mail)
		{
			foreach ($this->mail as $key => $field)
			{
				if (is_array($field))
				{
					foreach ($field as $value)
					{
						$_field[] = isset($data[$value]) ? $data[$value] : $value;
					}

					$field = implode(' ', $_field);

					unset($_field);
				}
				else
				{
					$field = isset($data[$field]) ? $data[$field] : $field;
				}

				$mail[$key] = $field;
			}
		}

		$replayToEmail = !empty($mail['sender_email'])
			? $mail['sender_email']
			: '';

		$replayToName = !empty($mail['sender'])
			? $mail['sender']
			: '';

		$recipient = $this->uParams['mailto'];

		$subject = (!empty($mail['subject']))
			? $mail['subject']
			: JText::sprintf('JTF_EMAIL_SUBJECT', $jConfig->get('sitename'));

		$mailer = JFactory::getMailer();
		$hBody  = $this->getTmpl('message.html');
		$pBody  = $this->getTmpl('message.plain');

		$mailer->setSender(array($jConfig->get('mailfrom'), $jConfig->get('fromname')));

		if (!empty($replayToEmail))
		{
			$mailer->addReplyTo($replayToEmail, $replayToName);
		}

		$mailer->addRecipient($recipient);
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
	protected function setCaptcha($captcha)
	{
		$form   = $this->getForm();
		$hField = new SimpleXMLElement('<field name="jtf_important_notices" type="text" gridgroup="jtfhp" notmail="1"></field>');

		$form->setField($hField, null, true, 'submit');
		JFactory::getDocument()->addStyleDeclaration('.hidden{display:none;visibility:hidden;}.jtfhp{position:absolute;top:-999em;left:-999em;height:0;width:0;}');

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
	protected function setSubmitButton($submit)
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
			}

			$form->setFieldAttribute($submit, 'notmail', true);
		}
		else
		{
			$cField = new SimpleXMLElement('<field name="submit" type="submit" label="JTF_SUBMIT_BUTTON" notmail="1"></field>');
			$form->setField($cField, null, true, 'submit');
		}
	}
}
