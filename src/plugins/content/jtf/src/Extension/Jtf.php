<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2025 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace JoomTools\Plugin\Content\Jtf\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Cache\CacheController;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Exception\CacheExceptionInterface;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Profiler\Profiler;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;
use JoomTools\Plugin\Content\Jtf\Form\Form;
use JoomTools\Plugin\Content\Jtf\Form\FormFactory;
use JoomTools\Plugin\Content\Jtf\Form\FormFactoryInterface;
use JoomTools\Plugin\Content\Jtf\Framework\FrameworkHelper;
use JoomTools\Plugin\Content\Jtf\Input\Files;
use JoomTools\Plugin\Content\Jtf\Layout\FileLayout;

/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @since        4.0.0
 */
final class Jtf extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * The regular expression to identify Plugin call.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    const PLUGIN_REGEX1 = "@(<(\w+)[^>]*>\s?)?{jtf(\s.*)?/?}(?(1)\s?</\\2>|)@uU";

    /**
     * Set counter
     *
     * @var   integer
     *
     * @since  4.0.0
     */
    private static $count = 0;

    /**
     * The context being passed to the plugin.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $context;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var   boolean
     *
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Set Form object
     *
     * @var   Form
     *
     * @since  4.0.0
     */
    private $_form;

    /**
     * Array with User params
     *
     * @var   array[]
     *
     * @since  4.0.0
     */
    private $uParams = [];

    /**
     * Array with extension names (URL option) where jtf should not be executed.
     *
     * @var   array
     *
     * @since  4.0.0
     */
    private $excludeOnExtensions = array(
        'com_finder',
        'com_config',
    );

    /**
     * @var   boolean
     *
     * @since  4.0.0
     */
    private $doNotLoad = false;

    /**
     * Array with allowed params to override
     *
     * @var   string[]
     *
     * @since  4.0.0
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
     * @var   array
     *
     * @since  4.0.0
     */
    private $tokens = [];

    /**
     * @var   boolean
     *
     * @since  4.0.0
     */
    private $debug = false;

    /**
     * Constructor
     *
     * @param   DispatcherInterface  $dispatcher  The object to observe -- event dispatcher.
     * @param   array                $config      An optional associative array of configuration settings.
     * @param   Input                $input       The input data.
     *
     * @since  4.0.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config, Input $input)
    {
        $container = Factory::getContainer();
        $container->alias(FormFactory::class, FormFactoryInterface::class)
            ->set(
                FormFactoryInterface::class,
                function (Container $container) {
                    $factory = new FormFactory();
                    $factory->setDatabase($container->get(DatabaseInterface::class));

                    return $factory;
                },
                true
            );

        parent::__construct($dispatcher, $config);

        $this->debug = (boolean) $this->params->get('debug', 0);
        $option      = $input->getCmd('option');
        $isEdit      = $input->getCmd('layout') == 'edit';

        if (\in_array($option, $this->excludeOnExtensions) || $isEdit) {
            $this->doNotLoad = true;
        }
    }

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepare' => 'onContentPrepare',
        ];
    }

    /**
     * Plugin that retrieves contact information for contact
     *
     * @param   ContentPrepareEvent $event  The event instance.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since  __DEPLOY_VERSION__
     *
     */
    public function onContentPrepare(ContentPrepareEvent $event)
    {
        $context = $event->getContext();
        $article = $event->getItem();
        $params  = $event->getParams();

        /** @var CMSApplicationInterface $app */
        $app = $this->getApplication();

        // Don't run in administration Panel or when the content is being indexed
        if (empty($article->text)
            || \strpos($article->text, '{jtf') === false
            || ($context == 'com_content.category' && $app->input->getCmd('layout') != 'blog')
            || $context == 'com_finder.indexer'
            || $this->doNotLoad
        ) {
            return;
        }

        // Get all matches or return
        if (!\preg_match_all(self::PLUGIN_REGEX1, $article->text, $matches)) {
            return;
        }

        $this->context = $context;

        switch (true) {
            case !\is_object($app->getUserState('plugins.content.jtf.' . $context)):
                $app->setUserState('plugins.content.jtf.' . $context, null);
            case !\is_object($app->getUserState('plugins.content.jtf.hp')):
                $app->setUserState('plugins.content.jtf.hp', null);
            case !\is_object($app->getUserState('plugins.content.jtf.start')):
                $app->setUserState('plugins.content.jtf.start', null);
            default:
                break;
        }

        FormHelper::addFieldPrefix('JoomTools\\Plugin\\Content\\Jtf\\Form\\Field');
        FormHelper::addRulePrefix('JoomTools\\Plugin\\Content\\Jtf\\Form\\Rule');

        // Get language tag
        $langTag = $app->get('language');

        // Exclude <code/> and <pre/> matches
        $code = \array_keys($matches[1], '<code>');
        $pre  = \array_keys($matches[1], '<pre>');

        if (!empty($code) || !empty($pre)) {
            \array_walk($matches,
                function (&$array, $key, $tags) {
                    foreach ($tags as $tag) {
                        if ($tag !== null && $tag !== false) {
                            unset($array[$tag]);
                        }
                    }
                },     \array_merge($code, $pre)
            );
        }

        if ($app->getUserState('plugins.content.jtf.' . $context)) {
            $this->tokens[$context] = clone $app->getUserState('plugins.content.jtf.' . $context);
        }

        $pluginReplacements = $matches[0];
        $userParams         = $matches[3];

        // Load global form language
        $this->loadLanguage('jtf_global', JPATH_PLUGINS . '/content/jtf/assets');

        foreach ($pluginReplacements as $rKey => $replacement) {
            // Clear html replace
            $html = '';

            $this->init($userParams[$rKey]);

            if (empty($this->uParams['formXmlPath'])) {
                return;
            }

            // Add override paths for form fields and rules
            FormHelper::addFieldPath($this->getFormFieldOverridePaths('field'));
            FormHelper::addRulePath($this->getFormFieldOverridePaths('rule'));

            $formTheme = $this->uParams['theme'] . (int) self::$count;
            $this->loadThemeLanguage('jtf_theme');

            $jtfHp = $app->getUserState('plugins.content.jtf.hp.' . $context . '.' . $formTheme);
            $app->setUserState('plugins.content.jtf.hp.' . $context . '.' . $formTheme, null);

            $startTime = $app->getUserState('plugins.content.jtf.start.' . $context . '.' . $formTheme);
            $app->setUserState('plugins.content.jtf.start.' . $context . '.' . $formTheme, null);

            $token = UserHelper::genRandomPassword(32);
            $app->setUserState('plugins.content.jtf.' . $context . '.' . $formTheme, $token);

            // Get form submit task
            $formSubmitted = ($app->input->getCmd('formTask') == $formTheme . "_sendmail") ? true : false;

            $this->setSubmit();

            if ($formSubmitted) {
                $checkToken      = $this->checkToken($formTheme);
                $checkFormToken  = Session::checkToken();
                $submittedValues = $app->input->get($formTheme, array(), 'post', 'array');
                $honeypot        = $submittedValues[$jtfHp];
                $fillOutTime     = $this->debug || JDEBUG || $this->uParams['fillouttime'] == 0
                    ? 100000
                    : \microtime(1) - $startTime;
                $notSpamBot      = $fillOutTime > $this->uParams['fillouttime'];

                if ($honeypot !== '' || !$notSpamBot || !$checkToken || !$checkFormToken) {
                    $app->redirect(Route::_('index.php', false));
                }

                if (!empty($_FILES)) {
                    $jInput          = new Files;
                    $submittedFiles  = $jInput->get($formTheme);
                    $submittedValues = \array_merge_recursive($submittedValues, $submittedFiles);
                }

                $this->getForm()->bind($submittedValues);
                $this->setFieldValidates();

                $valid = $this->getForm()->validate($submittedValues);

                if ($valid) {
                    if (!empty($submittedFiles)) {
                        $validatedValues = $this->getForm()->getData()->toArray();
                        $validatedFiles  = $this->cleanSubmittedFiles($submittedFiles, $validatedValues);
                        $newBind         = \array_merge($validatedValues, $validatedFiles);

                        $this->getForm()->resetData();
                        $this->getForm()->bind($newBind);
                    }

                    $sendmail = $this->sendMail();

                    if ($sendmail === true) {
                        if ($this->uParams['redirect_menuid'] !== null) {
                            $this->uParams['message_article'] = null;
                        }

                        if ($this->uParams['message_article'] === null) {
                            $text = Text::_('JTF_EMAIL_THANKS');
                        } else {
                            $text = $this->getMessageArticleContent();
                        }

                        if ($this->uParams['redirect_menuid'] === null) {
                            $app->setUserState('plugins.content.jtf.start.' . $context . '.' . $formTheme, null);
                            $app->setUserState('plugins.content.jtf.hp.' . $context . '.' . $formTheme, null);
                            $app->enqueueMessage($text, 'message');
                            $app->redirect(Route::_('index.php', false));
                        } else {
                            $app->setUserState('plugins.content.jtf.start.' . $context . '.' . $formTheme, null);
                            $app->setUserState('plugins.content.jtf.hp.' . $context . '.' . $formTheme, null);
                            $app->redirect(Route::_('index.php?Itemid=' . (int) $this->uParams['redirect_menuid'], false));
                        }
                    }
                }

                $this->setErrors($this->getForm()->getErrors());
            }

            $html .= $this->getTmpl('form');

            $startPos = \strpos($article->text, $replacement);
            $endPos   = \strlen($replacement);

            $article->text = \substr_replace($article->text, $html, $startPos, $endPos);
            self::$count++;

            $this->clearOldFiles();
            $app->setUserState('plugins.content.jtf.start.' . $context . '.' . $formTheme, \microtime(1));
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
     * @since  4.0.0
     */
    private function init($userParams)
    {
        $this->resetUserParams();

        if (!empty($userParams)) {
            $vars = \explode('|', $userParams);

            // Set user params
            $this->setUserParams($vars);
        }

        $this->uParams['formXmlPath'] = $this->getThemeFile('fields.xml', true);
    }

    /**
     * Reset user params to default values set in plugin
     *
     * @return  void
     *
     * @since  4.0.0
     */
    private function resetUserParams()
    {
        $this->uParams = [];
        $this->_form   = null;

        // Set default minimum fill out time
        $this->uParams['fillouttime'] = 0;
        $fillOutTimeOnOff             = \filter_var(
            $this->params->get('filloutTime_onoff'),
            FILTER_VALIDATE_BOOLEAN
        );

        if ($fillOutTimeOnOff) {
            $this->uParams['fillouttime'] = $this->params->get('filloutTime', 10);
        }

        // Set the default value for field description type
        $this->uParams['show_field_description_as'] = $this->params->get('show_field_description_as');

        // Set the default value for field marker
        $this->uParams['field_marker'] = $this->params->get('field_marker');

        // Set the default value for field marker place
        $this->uParams['field_marker_place'] = $this->params->get('field_marker_place');

        // Set the default option to display the required field description.
        $this->uParams['show_required_field_description'] = \filter_var(
            $this->params->get('show_required_field_description'),
            FILTER_VALIDATE_BOOLEAN
        );

        if ($this->uParams['field_marker'] == 'optional' || $this->uParams['field_marker_place'] != 'label') {
            $this->uParams['show_required_field_description'] = false;
        }

        // Set default option to show captcha
        $this->uParams['captcha'] = \filter_var(
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
        $this->uParams['file_path'] = \trim($this->params->get('file_path', 'uploads'), '\\/');

        // Set default framework value
        if ($this->params->get('framework') == 'joomla' || empty($this->uParams['framework'] = (array) $this->params->get('framework'))) {
            if (\version_compare(JVERSION, '4', 'ge')) {
                $this->uParams['framework'] = array('bs5');
            } else {
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
     * @since  4.0.0
     */
    private function setUserParams(array $vars)
    {
        $uParams = [];

        if (!empty($vars)) {
            foreach ($vars as $var) {
                $var = \trim($var);

                if (empty($var)) {
                    continue;
                }

                list($key, $value) = \explode('=', $var);

                $key   = \trim(strtolower($key));
                $value = \trim($value, '\/');

                if (!in_array($key, $this->uParamsAllowedOverride)) {
                    continue;
                }

                if ($key == 'framework') {
                    $value = \explode(',', $value);
                    $value = ArrayHelper::arrayUnique($value);
                }

                $uParams[$key] = $value;
            }
        }

        // Merge user params width default params
        $this->uParams = \array_merge($this->uParams, $uParams);
    }

    /**
     * Get absolute theme filepath.
     *
     * @param   string  $filename  Name of the language file (jtf_theme.ini).
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function loadThemeLanguage($filename)
    {
        $files    = $this->getFilenames($filename);
        $absPaths = $this->getThemePaths();

        foreach ($files as $langFileName) {
            foreach ($absPaths as $absPath) {
                // Set the right theme path
                if (\is_dir($absPath)) {
                  $this->loadLanguage($langFileName, $absPath);
                }
            }
        }
    }

    /**
     * Get absolute theme filepath
     *
     * @return  boolean|string  False if not a YT child template is used, else the child template name.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function getYTChildTemplate()
    {
        $app      = $this->getApplication();
        $template = $app->getTemplate();
        $tParams  = null;

        if ($template == 'yootheme') {
            $tParams  = \json_decode($app->getTemplate(true)->params->get('config'));
        }

        return empty($tParams->child_theme) ? false : $template . '_' . $tParams->child_theme;
    }

    /**
     * Get absolute theme filepath
     *
     * @param   string  $type  Type of field path to add (field|rule).
     *
     * @return  string[]  Array of strings with absolute paths for searching of overrides.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function getFormFieldOverridePaths($type) {
        $absPaths = $this->getThemePaths();
        \array_pop($absPaths);
        $absPaths = \array_reverse($absPaths);
        $ucfType = \ucfirst($type);
        $return = [];

        foreach ($absPaths as $absPath) {
            $return[] = $absPath . '/Form/'.$ucfType;
        }

        return $return;
    }

    /**
     * Get absolute theme filepath
     *
     * @return  string[]  Array of strings with absolute paths for searching of themes.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function getThemePaths() {
        $app      = $this->getApplication();
        $template = $app->getTemplate();
        $ytChild  = $this->getYTChildTemplate();

        $absPaths = [];

        if (!empty($ytChild)) {
            // Build template override path for YT child template
            $absPaths[] = JPATH_THEMES . '/' . $ytChild
                . '/html/plg_content_jtf/'
                . $this->uParams['theme'];
        }

        // Build template override path for theme
        $absPaths[] = JPATH_THEMES . '/' . $template
            . '/html/plg_content_jtf/'
            . $this->uParams['theme'];

        // Build plugin path for theme
        $absPaths[] = JPATH_PLUGINS . '/content/jtf/tmpl/'
            . $this->uParams['theme'];

        return ArrayHelper::arrayUnique($absPaths);
    }

    /**
     * Get absolute theme filepath
     *
     * @param   string  $filename  Filename
     *
     * @return  string[]  Array of strings with the filename combination to search for.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function getFilenames($filename)
    {
        $files    = [];
        $pathInfo = \pathinfo($filename);
        $ext     = !empty($pathInfo['extension']) ? $pathInfo['extension'] : null;
        $file    = $pathInfo['filename'];

        foreach ($this->uParams['framework'] as $frwk) {
            if (empty($ext) || $ext == 'ini') {
                $files[] = $file . '.' . $frwk;
            } else {
                $files[] = $file . '.' . $frwk . '.' . $ext;
            }
        }

        $files[] = $filename;

        return ArrayHelper::arrayUnique($files);
    }

    /**
     * Get absolute theme filepath
     *
     * @param   string  $filename  Filepath relative from inside the theme
     *
     * @return  boolean|string  False on error
     *
     * @since  4.0.0
     */
    private function getThemeFile($filename)
    {
        $app           = $this->getApplication();
        $error         = [];
        $files         = $this->getFilenames($filename);
        $absPaths      = $this->getThemePaths();
        $error['path'] = true;

        foreach ($files as $file) {
            foreach ($absPaths as $absPath) {
                // Set the right theme path
                if (\is_dir($absPath)) {
                    if (!empty($error['path'])) {
                        unset($error['path']);
                    }

                    if (\file_exists($absPath . '/' . $file)) {
                        return $absPath . '/' . $file;
                    }

                    $error['file'] = true;
                }
            }
        }

        if (!empty($error['path'])) {
            $app->enqueueMessage(
                Text::sprintf('JTF_THEME_ERROR', $this->uParams['theme']),
                'error'
            );
        }

        if (!empty($error['file'])) {
            $app->enqueueMessage(
                Text::sprintf('JTF_FORM_XML_FILE_ERROR', \implode(', ', $files), $this->uParams['theme']),
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
     * @since  4.0.0
     */
    private function getForm()
    {
        if (!empty($this->_form)) {
            return $this->_form;
        }

        $app                 = $this->getApplication();
        $template            = $app->getTemplate();
        $ytChildTemplate     = $this->getYTChildTemplate();
        $formName            = $this->uParams['theme'] . (int) self::$count;
        $formXmlPath         = $this->uParams['formXmlPath'];
        $form                = Form::getInstance($formName, $formXmlPath, array('control' => $formName));
        $form->framework     = $this->uParams['framework'];
        $form->rendererDebug = $this->debug;
        $form->layoutPaths   = [];

        if (!empty($ytChildTemplate)) {
            $form->layoutPaths = array(
                JPATH_THEMES . '/' . $ytChildTemplate . '/html/plg_content_jtf/' . $this->uParams['theme'],
                JPATH_THEMES . '/' . $ytChildTemplate . '/html/layouts/plugin/content/jtf',
                JPATH_THEMES . '/' . $ytChildTemplate . '/html/layouts',
            );
        }

        $form->layoutPaths = \array_merge(
            $form->layoutPaths,
            array(
                JPATH_THEMES . '/' . $template . '/html/plg_content_jtf/' . $this->uParams['theme'],
                JPATH_THEMES . '/' . $template . '/html/layouts/plugin/content/jtf',
                JPATH_THEMES . '/' . $template . '/html/layouts',
                JPATH_PLUGINS . '/content/jtf/layouts/jtf',
                JPATH_PLUGINS . '/content/jtf/layouts/joomla' . Version::MAJOR_VERSION,
                JPATH_PLUGINS . '/content/jtf/layouts',
                JPATH_SITE . '/layouts',
            )
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
     * @since  4.0.0
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

        if (empty($form)) {
            $form = $this->getForm();
        }

        $fields = $form->getFieldset();

        foreach ($fields as $field) {
            $fieldType = \strtolower($field->type);
            $fieldName = $field->fieldname;

            if ($fieldType == 'subform') {
                if ($field->validate == '') {
                    $form->setFieldAttribute($fieldName, 'validate', 'subform');
                }

                $subform = $form->getField($fieldName)->loadSubForm();
                $this->setFieldValidates($subform);

                continue;
            }

            if (\in_array($fieldType, $setValidationFor)) {
                if ($field->validate == '') {
                    if (\in_array($fieldType, array('checkboxes', 'list', 'radio'))) {
                        $form->setFieldAttribute($fieldName, 'validate', 'options');
                    } else {
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
     * @since  4.0.0
     */
    private function cleanSubmittedFiles($submittedFiles, $submittedValues)
    {
        $validatedFiles = [];

        foreach ($submittedFiles as $key => $value) {
            if (isset($submittedValues[$key])) {
                if (empty($value)) {
                    $validatedFiles[$key] = [];

                    continue;
                }

                if (\is_array($value)) {
                    if (isset($value['error']) && $value['error'] === 0) {
                        if ($savedFile = $this->saveFiles($value)) {
                            $validatedFiles = \array_merge($validatedFiles, $savedFile);

                            continue;
                        } else {
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
     * @since  4.0.0
     */
    private function saveFiles($validatedFile)
    {
        $nowPath     = date('Ymd');
        $uniqueToken = \md5(microtime());
        $filePath    = 'images/' . $this->uParams['file_path'] . '/' . $nowPath . '/' . $uniqueToken;
        $uploadBase  = JPATH_BASE . '/' . $filePath;
        $uploadURL   = rtrim(Uri::base(), '/') . '/' . $filePath;

        if (!is_dir($uploadBase)) {
            Folder::create($uploadBase);
        }

        if (!file_exists($uploadBase . '/.htaccess')) {
            File::write($uploadBase . '/.htaccess', Text::_('JTF_SET_ATTACHMENT_HTACCESS'));
        }

        $return = [];

        $save     = null;
        $fileName = File::stripExt($validatedFile['name']);
        $fileExt  = File::getExt($validatedFile['name']);
        $name     = OutputFilter::stringURLSafe($fileName) . '.' . $fileExt;

        $save = File::copy($validatedFile['tmp_name'], $uploadBase . '/' . $name);

        if ($save) {
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
     * @since  4.0.0
     */
    private function setErrors(array $errors)
    {
        foreach ($errors as $error) {
            $errorMessage = $error;

            if ($error instanceof \Exception) {
                $errorMessage = $error->getMessage();
            }

            $this->getApplication()->enqueueMessage($errorMessage, 'error');
        }
    }

    /**
     * Send e-mail to recipients
     *
     * @return  boolean|\RuntimeException  Boolean true if successful, boolean false if the `mailonline` configuration
     *                                     is set to 0, or a JException object if the mail function does not exist or
     *                                     sending the message fails.
     *
     * @throws  \RuntimeException
     * @since  4.0.0
     *
     */
    private function sendMail()
    {
        $app    = $this->getApplication();
        $config = clone $app->getConfig();
        $mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer($config);

        $subject = $this->getValue('subject');
        $subject = !empty($subject) ? $subject : Text::sprintf('JTF_EMAIL_SUBJECT', $app->get('sitename'));

        $emailCredentials = $this->getEmailCredentials();

        $recipient     = $emailCredentials['mailto'];
        $cc            = $emailCredentials['cc'];
        $bcc           = $emailCredentials['bcc'];
        $replayToName  = $emailCredentials['visitor_name'];
        $replayToEmail = $emailCredentials['visitor_email'];

        if (empty($replayToEmail)) {
            if (!empty($app->get('replyto'))) {
                $replayToEmail = $app->get('replyto');
            } else {
                $replayToEmail = $app->get('mailfrom');
            }

            $replayToName = $app->get('fromname');
        }

        $hBody = $this->getTmpl('message.html');
        $pBody = $this->getTmpl('message.plain');

        $mailer->setSender($app->get('mailfrom'), $replayToName);
        $mailer->addReplyTo($replayToEmail, $replayToName);
        $mailer->addRecipient($recipient);

        if (!empty($cc)) {
            $mailer->addCc($cc);
        }

        if (!empty($bcc)) {
            $mailer->addBcc($bcc);
        }

        $mailer->setSubject($subject);
        $mailer->IsHTML(true);
        $mailer->setBody($hBody);
        $mailer->AltBody = $pBody;

        /** TODO: Schleife für alle E-Mail-Adressen um sie als Empfänger einzutragen
         * Multiple TO, CC und BCC muss vorher aber noch auf einem Liveserver getestet werden.
         */

        /* foreach ...
        $return = $mailer->Send();

        if($return !== true)
        {
            return $return;
        }

        $mailer->clearAddresses();
        $mailer->clearCCs();
        $mailer->clearBCCs();
        $mailer->addRecipient($bcc);
        */

        return $mailer->Send();
    }

    /**
     * Get submitted value
     *
     * @param   string  $name  Field name of submitted value
     *
     * @return  string
     *
     * @since  4.0.0
     */
    private function getValue($name)
    {
        $data = $this->getForm()->getData()->toArray();

        if (!empty($data[$name])) {
            return $data[$name];
        }

        if (!empty($value = (string) $this->uParams[$name])) {
            if (!empty($data[$value])) {
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
     * @since  4.0.0
     */
    private function getEmailCredentials()
    {
        $app        = $this->getApplication();
        $recipients = [];

        foreach (array('mailto', 'cc', 'bcc', 'visitor_name', 'visitor_email') as $name) {
            $recipients[$name] = null;
            $recipient         = [];

            if (empty($this->uParams[$name]) && $name == 'mailto') {
                if (!empty($app->get('replyto'))) {
                    $recipients['mailto'][] = $app->get('replyto');
                } else {
                    $recipients['mailto'][] = $app->get('mailfrom');
                }

                continue;
            }

            $items = \explode(';', (string) $this->uParams[$name]);

            if ($name == 'visitor_email') {
                $items = array($items[0]);
            }

            foreach ($items as $item) {
                if (empty($item)) {
                    continue;
                }

                $value = null;

                if (strpos($item, '@') !== false) {
                    $value = \trim($item);
                }

                if (strpos($item, '#') !== false) {
                    $value = str_replace('#', '@', \trim($item));
                }

                if (!empty($value)) {
                    $recipient[] = $value;

                    continue;
                }

                if (empty($value = $this->getValue($item))) {
                    continue;
                }

                if (is_string($value)) {
                    $value = (array) $value;
                }

                $value = array_values(array_filter($value));

                \array_walk($value,
                    function (&$email) {
                        $email = str_replace('#', '@', \trim($email));
                    }
                );

                $recipient = \array_merge($recipient, $value);
            }

            $recipients[$name] = \is_array($recipient) ? ArrayHelper::arrayUnique($recipient) : $recipient;

            if (\in_array($name, array('visitor_name', 'visitor_email'))) {
                $recipients[$name] = \trim(implode(' ', $recipients[$name]));
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
     * @since  4.0.0
     */
    private function getTmpl($filename)
    {
        $app       = $this->getApplication();
        $enctype   = '';
        $id        = $this->uParams['theme'];
        $index     = self::$count;
        $form      = $this->getForm();
        $form      = FrameworkHelper::setFrameworkClasses($form);
        $formClass = $form->getAttribute('class', '');
        $token     = $app->getUserState('plugins.content.jtf.' . $this->context);
        $formName  = $id . $index;

        $controlFields   = [];
        $controlFields[] = '<input type="hidden" name="option" value="' . $app->input->get('option') . '" />';
        $controlFields[] = '<input type="hidden" name="formTask" value="' . $formName . '_sendmail" />';
        $controlFields[] = '<input type="hidden" name="view" value="' . $app->input->get('view') . '" />';
        $controlFields[] = '<input type="hidden" name="Itemid" value="' . $app->input->get('Itemid') . '" />';
        $controlFields[] = '<input type="hidden" name="id" value="' . $app->input->get('id') . '" />';
        $controlFields[] = '<input type="hidden" name="' . $token->$formName . '" value="1" />';

        if ($form->setEnctype) {
            $enctype = ' enctype="multipart/form-data"';
        }

        $displayData = array(
            'id'            => $formName . '_form',
            'fileClear'     => $this->params->get('file_clear'),
            'form'          => $form,
            'formClass'     => $formClass,
            'enctype'       => $enctype,
            'controlFields' => \implode('', $controlFields),
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
     * @throws  \Exception
     * @since  4.0.0
     *
     */
    private function getMessageArticleContent()
    {
        $itemId       = (int) $this->uParams['message_article'];
        $activeLang   = $this->getApplication()->get('language');
        $assocArticle = Associations::getAssociations(
            'com_content',
            '#__content',
            'com_content.item',
            $itemId,
            'id',
            null,
            null
        );

        if (\in_array($activeLang, \array_keys($assocArticle))) {
            $itemId = $assocArticle[$activeLang]->id;
        }

        $db    = $this->getDatabase();
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
     * @since  4.0.0
     */
    private function setSubmit()
    {
        $form           = $this->getForm();
        $captcha        = [];
        $button         = [];
        $submitFieldset = $form->getFieldset('submit');

        if (!empty($submitFieldset)) {
            if (!empty($issetCaptcha = $this->issetField('captcha', 'submit'))) {
                $captcha['submit'] = $issetCaptcha;
            }

            if (!empty($issetButton = $this->issetField('submit', 'submit'))) {
                $button['submit'] = $issetButton;
            }
        } else {
            $form->setField(new \SimpleXMLElement('<fieldset name="submit"></fieldset>'));
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
     * @since  4.0.0
     */
    private function issetField($fieldType, $fieldsetName = null)
    {
        $form   = $this->getForm();
        $fields = $form->getFieldset($fieldsetName);

        foreach ($fields as $field) {
            $type = (string) $field->getAttribute('type');

            if ($type == $fieldType) {
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
     * @since  4.0.0
     */
    private function setCaptcha($captcha)
    {
        $form  = $this->getForm();
        $jtfHp = \md5('jtfhp' . Factory::getSession()->getToken());

        // Set captcha to submit fieldset
        if (!empty($this->uParams['captcha'])) {
            if (!empty($captcha)) {
                if (empty($captcha['submit'])) {
                    $cField = $form->getFieldXml($captcha);

                    $form->removeField($captcha);
                    $form->setField($cField, null, true, 'submit');
                } else {
                    $captcha = $captcha['submit'];
                }
            } else {
                $captcha = 'captcha';
                $cField  = new \SimpleXMLElement(
                    '<field name="primaryc" type="captcha" validate="captcha" '
                    . 'description="JTF_CAPTCHA_DESC" label="JTF_CAPTCHA_LABEL" required="false"></field>'
                );

                $form->setField($cField, null, true, 'submit');
            }

            $form->setFieldAttribute($captcha, 'notmail', true);
        }

        // Remove Captcha if disabled by plugin
        if (empty($this->uParams['captcha']) && !empty($captcha)) {
            $captcha = false;

            $form->removeField($captcha);
        }

        $operators = array('-', '+', '*');
        $operator  = $operators[\rand(0, 2)];
        $number    = \rand(1, 10);
        $hint      = $number . ' ' . $operator . ' ' . \rand(1, $number);
        $hField    = new \SimpleXMLElement(
            '<field name="' . $jtfHp . '" type="text" label="JTF_CAPTCHA_MATH" size="10" hint="' . $hint . '" gridgroup="jtfhp" notmail="1"></field>'
        );

        $form->setField($hField, null, true, 'submit');

        $formTheme = $form->getName();

        $this->getApplication()->setUserState('plugins.content.jtf.hp.' . $this->context . '.' . $formTheme, $jtfHp);
    }

    /**
     * Set submit button to submit fieldset
     *
     * @param   mixed  $submit  Field name of submit button
     *
     * @return  void
     *
     * @since  4.0.0
     */
    private function setSubmitButton($submit)
    {
        $form = $this->getForm();

        // Set submit button to submit fieldset
        if (!empty($submit)) {
            if (empty($submit['submit'])) {
                $cField = $form->getFieldXml($submit);
                $form->removeField($submit);
                $form->setField($cField, null, true, 'submit');
            } else {
                $cField = $form->getFieldXml($submit['submit']);
                $form->removeField($submit['submit']);
                $form->setField($cField, null, true, 'submit');
                $submit = $submit['submit'];
            }

            $form->setFieldAttribute($submit, 'notmail', true);
        } else {
            $cField = new \SimpleXMLElement(
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
     * @since  4.0.0
     */
    private function clearOldFiles()
    {
        if (!$fileClear = (int) $this->uParams['file_clear']) {
            return;
        }

        $bugUploadBase = JPATH_BASE . '/images/0';

        if (\is_dir($bugUploadBase)) {
            $this->bugfixUploadFolder();
        }

        $uploadBase = JPATH_BASE . '/images/' . $this->uParams['file_path'];

        if (!\is_dir($uploadBase)) {
            return;
        }

        $folders = Folder::folders($uploadBase);
        $nowPath = \date('Ymd');
        $now     = new \DateTime($nowPath);

        foreach ($folders as $folder) {
            $date  = new \DateTime($folder);
            $clear = \date_diff($now, $date)->days;

            if ($clear >= $fileClear) {
                Folder::delete($uploadBase . '/' . $folder);
            }
        }
    }

    /**
     * Fix a bug for the upload folder path
     *
     * @return  void
     *
     * @since  4.0.0
     */
    private function bugfixUploadFolder()
    {
        $srcBase  = JPATH_BASE . '/images/0';
        $destBase = JPATH_BASE . '/images/' . $this->uParams['file_path'];
        $folders  = Folder::folders($srcBase);

        foreach ($folders as $key => $folder) {
            $src    = $srcBase . '/' . $folder;
            $dest   = $destBase . '/' . $folder;
            $moved  = false;
            $copied = false;

            if (\is_dir($dest)) {
                $copied = Folder::copy($src, $dest, '', true);
            } else {
                $moved = Folder::move($src, $dest);
            }

            if ($copied === true || $moved === true) {
                unset($folders[$key]);
            }
        }

        if (empty($folders)) {
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
     * @since  4.0.0
     */
    private function removeCache($context)
    {
        $cachePagePlugin = PluginHelper::isEnabled('system', 'cache');
        $cacheIsActive   = $this->getApplication()->get('caching', 0) != 0;

        if (!$cacheIsActive && !$cachePagePlugin) {
            return;
        }

        $key         = (array) Uri::getInstance()->toString();
        $key         = \md5(serialize($key));
        $group       = \strstr($context, '.', true);
        $cacheGroups = [];

        if ($cacheIsActive) {
            $cacheGroups = array(
                $group        => 'callback',
                'com_modules' => '',
                'com_content' => 'view',
            );
        }

        if ($cachePagePlugin) {
            $cacheGroups['page'] = 'callback';
        }

        foreach ($cacheGroups as $group => $handler) {
            try {
                // $cache = Factory::getCache($group, $handler);
                /** @var CacheControllerFactoryInterface $cacheControllerFactory */
                $cacheControllerFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);

                if (empty($cacheControllerFactory)) {
                    throw new \RuntimeException('JTF: Cannot get Joomla cache controller factory');
                }

                /** @var CacheController $cache */
                $cache = $cacheControllerFactory->createCacheController($handler, ['defaultgroup' => $group]);

                if (empty($cache) || !\property_exists($cache, 'cache') || !\method_exists($cache->cache, 'remove')) {
                    throw new \RuntimeException('JTF: Cannot get Joomla cache controller');
                }
            }
            catch (CacheExceptionInterface|\Throwable $e) {
                continue;
            }

            $cache->cache->remove($key);
            $cache->cache->setCaching(false);
        }
    }

    /**
     * Checks for a form token in the request.
     *
     * Use in conjunction with Html::_('form.token') or Session::getFormToken.
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

        if ($token && $this->getApplication()->input->get($token, null, 'alnum')) {
            return true;
        }

        return false;
    }
}
