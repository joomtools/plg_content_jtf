<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2021 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Input;

defined('JPATH_PLATFORM') or die;

use Joomla\Input\Files as JoomlaInputFiles;
use Joomla\Utilities\ArrayHelper;

/**
 * Extends Joomla! input files class
 *
 * @since  __DEPLOY_VERSION__
 */
class Files extends JoomlaInputFiles
{
	/**
	 * Method to decode a data array.
	 *
	 * @param   array  $data  The data array to decode.
	 *
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function decodeData(array $data): array
	{
		$result = array();

		if (is_array($data[0]))
		{
			if (!ArrayHelper::isAssociative($data[0]) && empty($data[0][0]))
			{
				return array();
			}

			foreach ($data[0] as $k => $v)
			{
				$result[$k] = $this->decodeData(array($data[0][$k], $data[1][$k], $data[2][$k], $data[3][$k], $data[4][$k]));
			}

			return $result;
		}

		return array('name' => $data[0], 'type' => $data[1], 'tmp_name' => $data[2], 'error' => $data[3], 'size' => $data[4]);
	}
}
