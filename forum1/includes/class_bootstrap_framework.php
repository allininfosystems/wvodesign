<?php
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
 * Bootstrap MVC to vBForum
 * Eventually this should be removed once refactoring of legacy code is complete.
 * All of these classes are context specific and need to be called after the
 * appropriate global.php or legacy bootstrap.
 * @see vB_Bootstrap
 *
 * @tutorial
 * 	require_once(DIR . '/includes/class_bootstrap_framework.php');
 *	vB_Bootstrap_Framework::init();
 *
 *	// Get Widgets
 *	$widgets = vBCms_Widget::getWidgetCollection(array(1), vBCms_Item_Widget::INFO_CONFIG);
 *	$widgets = vBCms_Widget::getWidgetControllers($widgets, true);
 *
 *	// Register the templater to be used for XHTML
 *	vB_View::registerTemplater(vB_View::OT_XHTML, new vB_Templater_vB());
 *
 *	foreach($widgets AS $widget)
 *	{
 *		echo($widget->getPageView());
 *	}
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */
class vB_Bootstrap_Framework
{
	/**
	 * Whether the bootstrap has been initialized.
	 *
	 * @var bool
	 */
	protected static $initialized;


	/**
	 * Initializes the bootstrap and framework.
	 */
	public static function init($relative_path = false)
	{
		if (!self::$initialized)
		{
			global $vbulletin;

			// Check datastore
			// Redundant, loaded by default when datastore is loaded
//			if (!sizeof($vbulletin->routes) AND VBINSTALL !== true)
//			{
//				$vbulletin->datastore->fetch(array('routes'));
//			}
			
			// Notify includes they are ok to run
			if (!defined('VB_ENTRY'))
			{
				define('VB_ENTRY', 1);
			}

			// Mark the framework as loaded
			if (!defined('VB_FRAMEWORK'))
			{
				define('VB_FRAMEWORK', true);
			}

			// Get the entry time
			if (!defined('VB_ENTRY_TIME'))
			{
				define('VB_ENTRY_TIME', microtime(true));
			}

			// vB core path
			if (!defined('VB_PATH'))
			{
				define('VB_PATH', realpath(dirname(__FILE__) . '/../vb') . '/');
			}

			// The package path
			if (!defined('VB_PKG_PATH'))
			{
				define('VB_PKG_PATH', realpath(VB_PATH . '../packages') . '/');
			}

			// Bootstrap to the new system
			require_once(VB_PATH . 'vb.php');

			vB::init($relative_path);
		}

		self::$initialized = true;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
