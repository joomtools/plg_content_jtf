<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('JPATH_BASE') or die;

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
 *
 * Calendar Specific
 * @var   string   $format          The date format
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
 * @var   integer  $direction       The document direction
 */

$value = ($value != "0000-00-00 00:00:00") ? htmlspecialchars($value, ENT_COMPAT, 'UTF-8') : '';

// Build the field attributes array.
$fieldAttributes                   = array();
$fieldAttributes['id']             = $id;
$fieldAttributes['name']           = $name;
$fieldAttributes['value']          = $value;
$fieldAttributes['autocomplete']   = 'off';
$fieldAttributes['data-alt-value'] = $value;
$fieldAttributes['class']          = empty($class) ? 'validate-dateformat' : 'validate-dateformat ' . trim($class);

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
	$cssFileExt = ($direction === 'rtl') ? '-rtl.css' : '.css';

	// Load polyfills for older IE
	HTMLHelper::_('behavior.polyfill', array('event', 'classlist', 'map'), 'lte IE 11');

	// The static assets for the calendar
	HTMLHelper::_('script', $localesPath, array('version' => 'auto', 'relative' => true));
	HTMLHelper::_('script', $helperPath, array('version' => 'auto', 'relative' => true));
	HTMLHelper::_('script', 'system/fields/calendar.min.js', array('version' => 'auto', 'relative' => true));
	HTMLHelper::_('stylesheet', 'system/fields/calendar' . $cssFileExt, array('version' => 'auto', 'relative' => true));
	HTMLHelper::_('script', 'plugins/content/jtf/assets/js/jtfMoment.min.js', array('version' => 'auto'));
	HTMLHelper::_('script', 'plugins/content/jtf/assets/js/jtfValidateDateFormat.min.js', array('version' => 'auto'));
}

// Build the button attributes array.
$buttonAttributes                         = array();
$buttonAttributes['id']                   = $id . '_btn';
$buttonAttributes['class']                = trim($buttonClass);
$buttonAttributes['title']                = Text::_('JLIB_HTML_BEHAVIOR_OPEN_CALENDAR');
$buttonAttributes['data-inputfield']      = $id;
$buttonAttributes['data-button']          = $id . '_btn';
$buttonAttributes['data-firstday']        = Factory::getLanguage()->getFirstDay();
$buttonAttributes['data-weekend']         = Factory::getLanguage()->getWeekEnd();
$buttonAttributes['data-dayformat']       = $format;
$buttonAttributes['data-today-btn']       = $todaybutton;
$buttonAttributes['data-week-numbers']    = $weeknumbers;
$buttonAttributes['data-show-time']       = $showtime;
$buttonAttributes['data-show-others']     = $filltable;
$buttonAttributes['data-time-24']         = $timeformat;
$buttonAttributes['data-only-months-nav'] = $singleheader;
$buttonAttributes['data-min-year']        = empty($minYear) ? '' : $minYear;
$buttonAttributes['data-max-year']        = empty($maxYear) ? '' : $maxYear;

($disabled || $readonly) ? $buttonAttributes['disabled'] = 'disabled' : null;

$fieldData = array(
	'fieldAttributes'  => ArrayHelper::toString($fieldAttributes),
	'buttonAttributes' => ArrayHelper::toString($buttonAttributes),
	'buttonIcon'       => $buttonIcon,
);

echo $this->sublayout('field', $fieldData);
