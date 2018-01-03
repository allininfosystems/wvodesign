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

$VB_API_WHITELIST = array(
	'session' => array('dbsessionhash', 'userid'),
	'show' => array()
);
class vB_APIMethod_login_facebook extends vBI_APIMethod
{
    public function output()
    {
        global $vbulletin, $db, $show, $VB_API_REQUESTS; 
        
        // check if facebook and session is enabled
		if (!is_facebookenabled())
		{
			return $this->error('feature_not_enabled');
		} 
        
        require_once(DIR . '/includes/functions_login.php');
        if (verify_facebook_app_authentication())
        {
            // create new session
            process_new_login('fbauto', false, '');

            // do redirect
            do_login_redirect();
        }

        else 
        {
            return $this->error('badlogin_facebook');
        }
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
?>
