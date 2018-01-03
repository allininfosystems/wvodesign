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
 * Reroute Exception
 * Exception to throw to redirect the user to an arbitrary route path.
 *
 * A redirect message can be assigned to display to the user.  The message should
 * be phrased as it could be displayed to the user.
 *
 * @todo Create methods to add the redirect message and the target route path.
 *
 * @author vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */
class vB_Exception_Reroute extends vB_Exception
{
	/**
	 * The route to reroute to.
	 * The route path should not include the route class segment.
	 *
	 * @var string
	 */
	protected $route_path;



	/*Initialisation================================================================*/

	/**
	 * Creates a reroute exception with the given route to reroute the controller
	 * delegation.
	 *
	 * @param string $route_path				- The new route path
	 * @param string $message					- A user friendly error
	 * @param int $code							- The PHP code of the error
	 * @param string $file						- The file the exception was thrown from
	 * @param int $line							- The line the exception was thrown from
	 */
	public function __construct($route_path, $message = false, $code = false, $file = false, $line = false)
	{
		// Store route
		$this->route_path = $route_path;

		// Set default message
		$message = ($message) ? $message : '';

		// Standard exception initialisation
		parent::__construct($message, $code, $file, $line);
	}



	/*Routing=======================================================================*/

	/**
	 * Gets the new route that the exception was thrown with.
	 *
	 * @return string							- The reroute route
	 */
	public function getRoutePath()
	{
		return $this->route_path;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/