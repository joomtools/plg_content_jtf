<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2025 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete    Autocomplete attribute for the field.
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   string   $class           Classes for the input.
 * @var   string   $description     Description of the field.
 * @var   boolean  $disabled        Is this field disabled?
 * @var   string   $group           Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden          Is this field hidden in the form?
 * @var   string   $hint            Placeholder for the field.
 * @var   string   $id              DOM id of the field.
 * @var   string   $label           Label of the field.
 * @var   string   $labelclass      Classes to apply to the label.
 * @var   boolean  $multiple        Does this field support multiple values?
 * @var   string   $name            Name of the input field.
 * @var   string   $onchange        Onchange attribute for the field.
 * @var   string   $onclick         Onclick attribute for the field.
 * @var   string   $pattern         Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly        Is this field read only?
 * @var   boolean  $repeat          Allows extensions to duplicate elements.
 * @var   boolean  $required        Is this field required?
 * @var   integer  $size            Size attribute of the input.
 * @var   boolean  $spellcheck      Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   array    $checkedOptions  Options that will be set as checked.
 * @var   boolean  $hasValue        Has this field a value assigned?
 * @var   array    $options         Options available for this field.
 * @var   integer  $maxLength       The maximum length that the field shall accept.
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attributes for eg, data-*.
 *
 * Calendar Specific
 * @var   string   $buttonClass     The class for the icon button
 * @var   string   $buttonIcon      The iconclass for the shown icon
 * @var   string   $localesPath     The relative path for the locale file
 * @var   string   $helperPath      The relative path for the helper file
 * @var   string   $minYear         The minimum year, that will be subtracted/added to current year
 * @var   string   $maxYear         The maximum year, that will be subtracted/added to current year
 * @var   integer  $todaybutton     The today button
 * @var   integer  $weeknumbers     The week numbers display
 * @var   integer  $showtime        The time selector display
 * @var   integer  $filltable       The previous/next month filling
 * @var   integer  $timeformat      The time format
 * @var   integer  $singleheader    Display different header row for month/year
 * @var   string   $direction       The document direction
 * @var   string   $calendar        The calendar type
 * @var   array    $weekend         The weekends days
 * @var   integer  $firstday        The first day of the week
 * @var   string   $format          The format of date and time
 */

// Handle the special case for "now".
if (strtoupper($value) == 'NOW')
{
	$value = Factory::getDate()->format('Y-m-d H:i:s');
}

$value = ($value != "0000-00-00 00:00:00") ? htmlspecialchars($value, ENT_COMPAT, 'UTF-8') : '';

// Build the field attributes array.
$fieldAttributes                   = array();
$fieldAttributes['id']             = $id;
$fieldAttributes['name']           = $name;
$fieldAttributes['value']          = $value;
$fieldAttributes['autocomplete']   = 'off';
$fieldAttributes['data-alt-value'] = $value;
$fieldAttributes['class']          = empty($class) ? 'validate-dateformat' : 'validate-dateformat ' . trim($class);

!empty($dataAttribute) ? null : $dataAttribute = '';

empty($size)      ? null : $fieldAttributes['size']        = $size;
empty($maxlength) ? null : $fieldAttributes['maxlength']   = $maxLength;
!$readonly        ? null : $fieldAttributes['readonly']    = 'readonly';
!$disabled        ? null : $fieldAttributes['disabled']    = 'disabled';
empty($onchange)  ? null : $fieldAttributes['onchange']    = $onchange;
empty($hint)      ? null : $fieldAttributes['placeholder'] = $hint;

if ($required)
{
	$fieldAttributes['required']      = 'required';
	$fieldAttributes['aria-required'] = 'true';
}

if (!$disabled || !$readonly)
{
	if (version_compare(JVERSION, '4', 'lt'))
	{
		$calendar   = '';
		$firstday   = Factory::getLanguage()->getFirstDay();
		$weekend    = explode(',', Factory::getLanguage()->getWeekEnd());
		$cssFileExt = ($direction === 'rtl') ? '-rtl.css' : '.css';

		// Load polyfills for older IE
		HTMLHelper::_('behavior.polyfill', array('event', 'classlist', 'map'), 'lte IE 11');

		// The static assets for the calendar
		HTMLHelper::_('script', $localesPath, array('version' => 'auto', 'relative' => true));
		HTMLHelper::_('script', $helperPath, array('version' => 'auto', 'relative' => true));
		HTMLHelper::_('script', 'system/fields/calendar.min.js', array('version' => 'auto', 'relative' => true));
		HTMLHelper::_('stylesheet', 'system/fields/calendar' . $cssFileExt, array('version' => 'auto', 'relative' => true));
	}
	else
	{
		// Get some system objects.
		$document = Factory::getApplication()->getDocument();
		$lang     = Factory::getApplication()->getLanguage();

		// Add language strings
		$strings = [
			// Days
			'SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY',
			// Short days
			'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT',
			// Months
			'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER',
			// Short months
			'JANUARY_SHORT', 'FEBRUARY_SHORT', 'MARCH_SHORT', 'APRIL_SHORT', 'MAY_SHORT', 'JUNE_SHORT',
			'JULY_SHORT', 'AUGUST_SHORT', 'SEPTEMBER_SHORT', 'OCTOBER_SHORT', 'NOVEMBER_SHORT', 'DECEMBER_SHORT',
			// Buttons
			'JCLOSE', 'JCLEAR', 'JLIB_HTML_BEHAVIOR_TODAY',
			// Miscellaneous
			'JLIB_HTML_BEHAVIOR_WK',
		];

		foreach ($strings as $c)
		{
			Text::script($c);
		}

		// These are new strings. Make sure they exist. Can be generalised at later time: eg in 4.1 version.
		if ($lang->hasKey('JLIB_HTML_BEHAVIOR_AM'))
		{
			Text::script('JLIB_HTML_BEHAVIOR_AM');
		}

		if ($lang->hasKey('JLIB_HTML_BEHAVIOR_PM'))
		{
			Text::script('JLIB_HTML_BEHAVIOR_PM');
		}

		// Redefine locale/helper assets to use correct path, and load calendar assets
		$document->getWebAssetManager()
			->registerAndUseScript('field.calendar.helper', $helperPath, [], ['defer' => true])
			->useStyle('field.calendar' . ($direction === 'rtl' ? '-rtl' : ''))
			->useScript('field.calendar');
	}

	HTMLHelper::_('script', 'plg_content_jtf/jtfMoment.min.js', ['version' => 'auto', 'relative' => true], ['defer' => 'defer']);
	HTMLHelper::_('script', 'plg_content_jtf/jtfValidateDateFormat.min.js', ['version' => 'auto', 'relative' => true], ['defer' => 'defer']);
}

// Build the button attributes array.
$buttonAttributes = array(
	'id'                   => $id . '_btn',
	'class'                => trim($buttonClass),
	'title'                => Text::_('JLIB_HTML_BEHAVIOR_OPEN_CALENDAR'),
	'data-inputfield'      => $id,
	'data-button'          => $id . '_btn',
	'data-date-format'     => $format,
	'data-dayformat'       => $format,
	'data-firstday'        => empty($firstday) ? '' : $firstday,
	'data-weekend'         => empty($weekend) ? '' : implode(',', $weekend),
	'data-today-btn'       => $todaybutton,
	'data-week-numbers'    => $weeknumbers,
	'data-show-time'       => $showtime,
	'data-show-others'     => $filltable,
	'data-time24'          => $timeformat,
	'data-only-months-nav' => $singleheader,
	'data-min-year'        => empty($minYear) ? '' : $minYear,
	'data-max-year'        => empty($maxYear) ? '' : $maxYear,
	'data-date-type'       => strtolower($calendar),
);

($disabled || $readonly) ? $buttonAttributes['disabled'] = 'disabled' : null;

$fieldData = array(
	'fieldAttributes'  => ArrayHelper::toString($fieldAttributes),
	'buttonAttributes' => ArrayHelper::toString($buttonAttributes),
	'dataAttribute'    => null,
	'buttonIcon'       => $buttonIcon,
);

if (version_compare(JVERSION, '4', 'ge'))
{
	$fieldData['dataAttribute'] = $dataAttribute;
}

echo $this->sublayout('field', $fieldData);
