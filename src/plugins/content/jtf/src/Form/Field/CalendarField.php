<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace JoomTools\Plugin\Content\Jtf\Form\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\CalendarField as JoomlaCalendarField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use JoomTools\Plugin\Content\Jtf\Form\FormFieldExtension;

if (version_compare(JVERSION, '4', 'lt')) {
    FormHelper::loadFieldClass('calendar');
}

/**
 * Form Field class for the Joomla Platform.
 *
 * Provides a pop up date picker linked to a button.
 * Optionally may be filtered to use user's or server's time zone.
 *
 * @since  4.0.0
 */
class CalendarField extends JoomlaCalendarField
{
    use FormFieldExtension;

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since  4.0.0
     */
    protected function getInput()
    {
        $config = Factory::getApplication()->getConfig();
        $user = Factory::getApplication()->getIdentity();

        // Translate the format if requested
        $translateFormat = (string) $this->element['translateformat'];

        if ($translateFormat && $translateFormat != 'false') {
            $showTime = (string) $this->element['showtime'];

            $lang  = Factory::getLanguage();
            $debug = $lang->setDebug(false);

            if (empty($this->format)) {
                if ($showTime && $showTime != 'false') {
                    $this->format       = Text::_('DATE_FORMAT_CALENDAR_DATETIME');
                    $this->filterFormat = Text::_('DATE_FORMAT_FILTER_DATETIME');
                } else {
                    $this->format       = Text::_('DATE_FORMAT_CALENDAR_DATE');
                    $this->filterFormat = Text::_('DATE_FORMAT_FILTER_DATE');
                }
            }

            $lang->setDebug($debug);
        }

        $issetValue = $this->value && $this->value != $this->getDatabase()->getNullDate();

        // Get a date object.
        if ($issetValue) {
            try {
                $date = Factory::getDate($this->value, 'UTC');
            } catch (\Exception $e) {
                $this->value = '';
                $issetValue  = false;
            }
        }

        // If a known filter is given use it.
        switch (strtoupper($this->filter)) {
            case 'SERVER_UTC':
                // Convert a date to UTC based on the server timezone.
                if ($issetValue) {
                    // Get a date object based on the correct timezone.
                    $date->setTimezone(new \DateTimeZone($config->get('offset')));
                }
                break;

            case 'USER_UTC':
                // Convert a date to UTC based on the user timezone.
                if ($issetValue) {
                    // Get a date object based on the correct timezone.
                    $date->setTimezone($user->getTimezone());
                }
                break;
        }

        // Transform the date string.
        if ($issetValue) {
            $this->value = $date->format('Y-m-d H:i:s', true, false);
        }

        // Format value when not nulldate ('0000-00-00 00:00:00'), otherwise blank it as it would result in 1970-01-01.
        if ($issetValue && strtotime($this->value) !== false) {
            $format = $this->format;
            $tz     = date_default_timezone_get();
            date_default_timezone_set('UTC');

            if ($this->filterFormat) {
                $format = $this->filterFormat;
            }

            $date        = \DateTimeImmutable::createFromFormat('U', strtotime($this->value));
            $this->value = $date->format($format);
            // $this->value = strftime($this->format, strtotime($this->value));

            date_default_timezone_set($tz);
        } else {
            $this->value = '';
        }

        return $this->getRenderer($this->layout)->render($this->getLayoutData());
    }
}
