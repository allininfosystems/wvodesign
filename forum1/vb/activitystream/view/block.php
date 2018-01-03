<?php

/* ======================================================================*\
  || #################################################################### ||
  || # vBulletin 4.2.5 - Licence Number VBF6025393
  || # ---------------------------------------------------------------- # ||
  || # Copyright ©2000-2017 vBulletin Solutions Inc. All Rights Reserved. ||
  || # This file may not be redistributed in whole or significant part. # ||
  || # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
  || #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
  || #################################################################### ||
  \*====================================================================== */

/**
 * Class to view the activity stream
 *
 * @package	vBulletin
 * @version	$Revision: 92140 $
 * @date		$Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 */
class vB_ActivityStream_View_Block extends vB_ActivityStream_View
{
	/*
	 * Process the activity stream block
	 *
	 */
	public function process($config)
	{
		global $show;

		$activitybits = '';

		$show['as_blog'] = (vB::$vbulletin->products['vbblog']);
		$show['as_cms'] = (vB::$vbulletin->products['vbcms']);
		$show['as_socialgroup'] = (
			vB::$vbulletin->options['socnet'] & vB::$vbulletin->bf_misc_socnet['enable_groups']
				AND
			vB::$vbulletin->userinfo['permissions']['socialgrouppermissions'] & vB::$vbulletin->bf_ugp_socialgrouppermissions['canviewgroups']
		);

		switch($config['activitystream_sort'])
		{
			case '1':
				$this->orderby = 'score DESC, dateline DESC';
				$sort = 'popular';
				break;
			default: // recent
				$this->getnew = false;
				$this->orderby = 'dateline DESC';
				$sort = 'recent';
		}

		switch ($config['activitystream_filter'])
		{
			case '1':
				$this->setWhereFilter('type', 'photo');
				break;
			case '2':
				$this->setWhereFilter('section', 'forum');
				break;
			case '3':
				if ($show['as_cms'])
				{
					$this->setWhereFilter('section', 'cms');
				}
				break;
			case '4':
				if ($show['as_blog'])
				{
					$this->setWhereFilter('section', 'blog');
				}
				break;
			case '5':
				$this->setWhereFilter('section', 'socialgroup');
				break;
			default: // all
		}

		switch($config['activitystream_date'])
		{
			case '0':
				$this->setWhereFilter('maxdateline', TIMENOW - 24 * 60 * 60);
				break;
			case '1':
				$this->setWhereFilter('maxdateline', TIMENOW - 7 * 24 * 60 * 60);
				break;
			case '2':
				$this->setWhereFilter('maxdateline', TIMENOW - 30 * 24 * 60 *60);
				break;
			default: // 3 - anytime
		}

		($hook = vBulletinHook::fetch_hook($this->hook_beforefetch)) ? eval($hook) : false;

		$this->setPage(1, $config['activitystream_limit']);

		$result = $this->fetchStream($sort, true);
		$cleaned = array_filter($result['bits']);
		return $cleaned;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
