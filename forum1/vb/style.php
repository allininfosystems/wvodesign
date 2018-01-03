<?php if (!defined('VB_ENTRY')) die('Access denied.');
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.5 - Licence Number VBF6025393
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2017 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/

/**
 * General purpose class for handling styles.
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */
class vB_Style
{
	/*Properties====================================================================*/

	/**
	 * Info of all styles.
	 *
	 * @var array mixed
	 */
	protected static $styles;



	/*Accessors=====================================================================*/

	/**
	 * Recursively maps the styles to create a flat array of styles with depth.
	 *
	 * @param int $styleid						- Root style to map from
	 * @param int $depth						- Current depth
	 * @param array $styles						- Current style map
	 * @return array							- Finished map
	 */
	public function getStyles($styleid = -1, $depth = 1, &$styles = false)
	{
		if ((-1 == $styleid) AND isset(self::$styles))
		{
			return self::$styles;
		}

		$styles = $styles ? $styles : array();

		foreach (vB::$vbulletin->stylecache[$styleid][1] AS $style)
		{
			$style['depth'] = $depth;
			$styles[$style['styleid']] = $style;

			// check for children
			if (isset(vB::$vbulletin->stylecache[$style['styleid']]))
			{
				self::getStyles($style['styleid'], $depth + 1, $styles);
			}

		}

		if (-1 == $styleid)
		{
			self::$styles = $styles;
		}

		return $styles;
	}



	/*Accessors=====================================================================*/

	/**
	 * Fetches info for a single style.
	 *
	 * @param int $styleid
	 * @return array
	 */
	public static function getStyle($styleid)
	{
		self::getStyles();

		if (isset(self::$styles[$styleid]))
		{
			return self::$styles[$styleid];
		}

		return false;
	}


	/**
	 * Checks if a styleid is valid
	 *
	 * @param int $styleid
	 * @return bool
	 */
	public static function validStyle($styleid)
	{
		self::getStyles();

		return isset(self::$styles[$styleid]);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/