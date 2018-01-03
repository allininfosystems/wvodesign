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

if (!defined('VB_AREA') AND !defined('THIS_SCRIPT'))
{
	echo 'VB_AREA and THIS_SCRIPT must be defined to continue';
	exit;
}

if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS']))
{
	echo 'Request tainting attempted.';
	exit;
}

@ini_set('pcre.backtrack_limit', -1);


@date_default_timezone_set(date_default_timezone_get());

// start the page generation timer
define('TIMESTART', microtime(true));

// set the current unix timestamp
define('TIMENOW', time());

// Define safe_mode
define('SAFEMODE', (@ini_get('safe_mode') == 1 OR strtolower(@ini_get('safe_mode')) == 'on') ? true : false);

// define current directory
if (!defined('CWD'))
{
	define('CWD', (($getcwd = getcwd()) ? $getcwd : '.'));
}

// #############################################################################
// fetch the core includes

if (!defined('VB_API'))
{
	define('VB_API', false);
}

// Get any missing $_SERVER headers
if (function_exists('getallheaders'))
{
	$headers = getallheaders();
	foreach ($headers AS $header => $value)
	{
		$header_name = strtr('HTTP_' . strtoupper($header), '-', '_');
		if (!isset($_SERVER[$header_name])) 
		{
			$_SERVER[$header_name] = $value; 
		};
	}
}

require_once(CWD . '/includes/class_core.php');
set_error_handler('vbulletin_error_handler');
set_exception_handler('vbulletin_exception_handler');

// initialize the data registry
$vbulletin = new vB_Registry();

// load the IP data & constants
$vbulletin->fetch_ip_data();

// Add AdSense if present
$vbulletin->adsense_pub_id = 'ca-pub-3356713957846289';
$vbulletin->adsense_host_id = 'ca-host-pub-2606800903002383';

if (CWD == '.')
{
	// getcwd() failed and so we need to be told the full forum path in config.php
	if (!empty($vbulletin->config['Misc']['forumpath']))
	{
		define('DIR', $vbulletin->config['Misc']['forumpath']);
	}
	else
	{
		trigger_error('<strong>Configuration</strong>: You must insert a value for <strong>forumpath</strong> in config.php', E_USER_ERROR);
	}
}
else
{
	define('DIR', CWD);
}

if (!empty($vbulletin->config['Misc']['datastorepath']))
{
		define('DATASTORE', $vbulletin->config['Misc']['datastorepath']);
}
else
{
		define('DATASTORE', DIR . '/includes/datastore');
}

if ($vbulletin->debug)
{
	restore_error_handler();
}

$dbtype = strtolower($vbulletin->config['Database']['dbtype']);

// Force MySQL to MySQLi
if ($dbtype == 'mysql')
{
	$dbtype = 'mysqli';
}
else if ($dbtype == 'mysql_slave')
{
	$dbtype = 'mysqli_slave';
}

//If type is missing, Force MySQLi
$dbtype = $dbtype ? $dbtype : 'mysqli';

// #############################################################################
// Load database class
switch ($dbtype)
{
	// Load standard MySQL class
	case 'mysql':
	{
		if ($vbulletin->debug AND ($vbulletin->input->clean_gpc('r', 'explain', TYPE_UINT) OR (defined('POST_EXPLAIN') AND !empty($_POST))))
		{
			// load 'explain' database class
			require_once(DIR . '/includes/class_database_explain.php');
			$db = new vB_Database_Explain($vbulletin);
		}
		else
		{
			$db = new vB_Database($vbulletin);
		}
		break;
	}

	case 'mysql_slave':
	{
		require_once(DIR . '/includes/class_database_slave.php');
		$db = new vB_Database_Slave($vbulletin);
		break;
	}

	// Load MySQLi class
	case 'mysqli':
	{
		if ($vbulletin->debug AND ($vbulletin->input->clean_gpc('r', 'explain', TYPE_UINT) OR (defined('POST_EXPLAIN') AND !empty($_POST))))
		{
			// load 'explain' database class
			require_once(DIR . '/includes/class_database_explain.php');
			$db = new vB_Database_MySQLi_Explain($vbulletin);
		}
		else
		{
			$db = new vB_Database_MySQLi($vbulletin);
		}
		break;
	}

	case 'mysqli_slave':
	{
		require_once(DIR . '/includes/class_database_slave.php');
		$db = new vB_Database_Slave_MySQLi($vbulletin);
		break;
	}

	// Load extended, non MySQL class
	default:
	{
		@include_once(DIR . "/includes/class_database_$dbtype.php");
		$dbclass = "vB_Database_$dbtype";
		$db = new $dbclass($vbulletin);
	}
}

// get core functions
if (!empty($db->explain))
{
	$db->timer_start('Including Functions.php');
	require_once(DIR . '/includes/functions.php');
	$db->timer_stop(false);
}
else
{
	require_once(DIR . '/includes/functions.php');
}

// make database connection
$db->connect(
	$vbulletin->config['Database']['dbname'],
	$vbulletin->config['MasterServer']['servername'],
	$vbulletin->config['MasterServer']['port'],
	$vbulletin->config['MasterServer']['username'],
	$vbulletin->config['MasterServer']['password'],
	$vbulletin->config['MasterServer']['usepconnect'],
	$vbulletin->config['SlaveServer']['servername'],
	$vbulletin->config['SlaveServer']['port'],
	$vbulletin->config['SlaveServer']['username'],
	$vbulletin->config['SlaveServer']['password'],
	$vbulletin->config['SlaveServer']['usepconnect'],
	$vbulletin->config['Mysqli']['ini_file'],
	(isset($vbulletin->config['Mysqli']['charset']) ? $vbulletin->config['Mysqli']['charset'] : '')
);

// Allow setting of SQL mode, not generally required
if (isset($vbulletin->config['Database']['set_sql_mode']))
{
	$db->force_sql_mode($vbulletin->config['Database']['set_sql_mode']);
}
else
{
	$db->force_sql_mode(''); // Force blank mode if none set, avoids Strict Mode issues.
}

if (defined('DEMO_MODE') AND DEMO_MODE AND function_exists('vbulletin_demo_init_db'))
{
	vbulletin_demo_init_db();
}

// make $db a member of $vbulletin
$vbulletin->db =& $db;

// #############################################################################
// fetch options and other data from the datastore
if (!empty($db->explain))
{
	$db->timer_start('Datastore Setup');
}

$datastore_class = (!empty($vbulletin->config['Datastore']['class'])) ? $vbulletin->config['Datastore']['class'] : 'vB_Datastore';

if ($datastore_class != 'vB_Datastore')
{
	require_once(DIR . '/includes/class_datastore.php');
}

$vbulletin->datastore = new $datastore_class($vbulletin, $db);
if (!$vbulletin->datastore->fetch($specialtemplates))
{
	switch(VB_AREA)
	{
		case 'AdminCP':
		case 'Archive':
			exec_header_redirect('../install/install.php');
			break;
		case 'Forum':
		default:
			exec_header_redirect('install/install.php');
	}
}

if ($vbulletin->bf_ugp === null)
{
	echo '<div>vBulletin datastore error caused by one or more of the following:
		<ol>
			<li>You may have uploaded vBulletin files without also running the vBulletin upgrade script. If you have not run the upgrade script, do so now.</li>
			<li>The datastore cache may have been corrupted. Run <em>Rebuild Bitfields</em> from <em>tools.php</em>, which you can upload from the <em>do_not_upload</em> folder of the vBulletin package.</li>
		</ol>
	</div>';

	trigger_error('vBulletin datastore cache incomplete or corrupt', E_USER_ERROR);
}

if (defined('VB_PRODUCT') AND (!isset($vbulletin->products[VB_PRODUCT]) OR !($vbulletin->products[VB_PRODUCT])))
{
	exec_header_redirect(fetch_seo_url('forumhome|bburl', array()), 303);
}

if (!empty($db->explain))
{
	$db->timer_stop(false);
}

if ($vbulletin->options['cookietimeout'] < 60)
{
	// values less than 60 will probably break things, so prevent that
	$vbulletin->options['cookietimeout'] = 60;
}

// #############################################################################
/**
* If shutdown functions are allowed, register exec_shut_down to be run on exit.
* Disable shutdown function for IIS CGI with Gzip enabled since it just doesn't work, sometimes, unless we kill the content-length header
* Also disable for PHP4 due to the echo() timeout issue
*/
define('SAPI_NAME', php_sapi_name());
/*
if (!defined('NOSHUTDOWNFUNC'))
{
	if ((SAPI_NAME == 'cgi' OR SAPI_NAME == 'cgi-fcgi') AND $vbulletin->options['gzipoutput'] AND strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false)
	{
		define('NOSHUTDOWNFUNC', true);
	}
	else
	{
		vB_Shutdown::add('exec_shut_down');
	}
}
*/
define('NOSHUTDOWNFUNC', true);

// fetch url of referring page after we have access to vboptions['forumhome']
$vbulletin->url = $vbulletin->input->fetch_url();
define('REFERRER_PASSTHRU', $vbulletin->url);

// #############################################################################
// demo mode stuff
if (defined('DEMO_MODE') AND DEMO_MODE AND function_exists('vbulletin_demo_init_page'))
{
	vbulletin_demo_init_page();
}

// #############################################################################
// setup the hooks & plugins system
if ($vbulletin->options['enablehooks'] OR defined('FORCE_HOOKS'))
{
	require_once(DIR . '/includes/class_hook.php');
	$hookobj = vBulletinHook::init();
	if ($vbulletin->options['enablehooks'] AND !defined('DISABLE_HOOKS'))
	{
		if (!empty($vbulletin->pluginlistadmin) AND is_array($vbulletin->pluginlistadmin))
		{
			$vbulletin->pluginlist = array_merge($vbulletin->pluginlist, $vbulletin->pluginlistadmin);
			unset($vbulletin->pluginlistadmin);
		}
		vBulletinHook::set_pluginlist($vbulletin->pluginlist);
	}
}
else
{
	// make a null class for optimization
	/**
	* @ignore
	*/
	class vBulletinHook {
		public static function init(){ return new vBulletinHook(); }
		public static function fetch_hook() { return false; }
		public static function fetch_hookusage() { return array(); }
	}
	$vbulletin->pluginlist = '';
}
$template_hook = array();

// $new_datastore_fetch does not require single quotes
$new_datastore_fetch = $datastore_fetch = array();

($hook = vBulletinHook::fetch_hook('init_startup')) ? eval($hook) : false;

if (!empty($datastore_fetch))
{
	// Remove the single quotes that $datastore_fetch required
	foreach ($datastore_fetch AS $value)
	{
		$new_datastore_fetch[] = substr($value, 1, -1);
	}
}

$vbulletin->datastore->fetch($new_datastore_fetch);
unset($datastore_fetch, $new_datastore_fetch);

// #############################################################################
// Parse the friendly uri for the current request
if (defined('FRIENDLY_URL_LINK'))
{
	require_once(DIR . '/includes/class_friendly_url.php');

	$friendly = vB_Friendly_Url::fetchLibrary($vbulletin, FRIENDLY_URL_LINK . '|nosession');

	if ($vbulletin->input->friendly_uri = $friendly->get_uri())
	{
		// don't resolve the wolpath
		define('SKIP_WOLPATH', 1);
	}
}

// #############################################################################
// do a callback to modify any variables that might need modifying based on HTTP input
// eg: doing a conditional redirect based on a $goto value or $vbulletin->noheader must be set
if (function_exists('exec_postvar_call_back'))
{
	exec_postvar_call_back();
}

// #############################################################################
// initialize $show variable - used for template conditionals
$show = array();

// #############################################################################
// Clean Cookie Vars
$vbulletin->input->clean_array_gpc('c', array(
	'vbulletin_collapse'              => TYPE_STR,
	COOKIE_PREFIX . 'referrerid'      => TYPE_UINT,
	COOKIE_PREFIX . 'userid'          => TYPE_UINT,
	COOKIE_PREFIX . 'password'        => TYPE_STR,
	COOKIE_PREFIX . 'lastvisit'       => TYPE_UINT,
	COOKIE_PREFIX . 'lastactivity'    => TYPE_UINT,
	COOKIE_PREFIX . 'threadedmode'    => TYPE_NOHTML,
	COOKIE_PREFIX . 'sessionhash'     => TYPE_NOHTML,
	COOKIE_PREFIX . 'userstyleid'     => TYPE_INT,
	COOKIE_PREFIX . 'languageid'      => TYPE_UINT,
	COOKIE_PREFIX . 'skipmobilestyle' => TYPE_BOOL,
));


// #############################################################################
// VB API Request Signature Verification
if (defined('VB_API') AND VB_API === true)
{
	// API disabled
	if (!$vbulletin->options['enableapi'] OR !$vbulletin->options['apikey'])
	{
		print_apierror('api_disabled', 'API is disabled');
	}

	global $VB_API_PARAMS_TO_VERIFY, $VB_API_REQUESTS;

	$vbulletin->input->clean_array_gpc('r', array(
		'debug'         => TYPE_BOOL,
		'showall'       => TYPE_BOOL,
	));

	if ($VB_API_REQUESTS['api_c'])
	{
		// Get client information from api_c. api_c has been intvaled in api.php
		$client = $db->query_first("SELECT *
			FROM " . TABLE_PREFIX . "apiclient
			WHERE apiclientid = $VB_API_REQUESTS[api_c]");

		if (!$client)
		{
			print_apierror('invalid_clientid', 'Invalid Client ID');
		}

		// An accesstoken is passed but invalid
		if ($VB_API_REQUESTS['api_s'] AND $VB_API_REQUESTS['api_s'] != $client['apiaccesstoken'])
		{
			print_apierror('invalid_accesstoken', 'Invalid Access Token');
		}

		$signtoverify = md5(http_build_query($VB_API_PARAMS_TO_VERIFY, '', '&') . $VB_API_REQUESTS['api_s'] . $client['apiclientid'] . $client['secret'] . $vbulletin->options['apikey']);
		$vbulletin->input->clean_array_gpc('r', array(
			'debug' => TYPE_BOOL,
		));
		if ($VB_API_REQUESTS['api_sig'] !== $signtoverify AND !($vbulletin->debug AND $vbulletin->GPC['debug']))
		{
			//echo ' Should be: ' . $signtoverify . ' md5("' . http_build_query($VB_API_PARAMS_TO_VERIFY, '', '&') . $VB_API_REQUESTS['api_s'] . $client['apiclientid'] . $client['secret'] . '")';
			print_apierror('invalid_api_signature', 'Invalid API Signature');
		}
		else
		{
			$vbulletin->apiclient = $client;
		}

		if ($vbulletin->options['enableapilog'])
		{
			$hide = array(
				'vb_login_password',
				'vb_login_md5password',
				'vb_login_md5password_utf',
				'password',
				'password_md5',
				'passwordconfirm',
				'passwordconfirm_md5',
				/* Not currently used by mapi
				but might be in the future */
				'currentpassword',
				'currentpassword_md5',
				'newpassword',
				'newpasswordconfirm',
				'newpassword_md5',
				'newpasswordconfirm_md5',
			);

			$post_copy = $_POST;

			foreach ($hide AS $param)
			{
				if ($post_copy[$param])
				{
					$post_copy[$param] = '*****';
				}
			}

			$db->query_write("
				INSERT INTO " . TABLE_PREFIX . "apilog (apiclientid, method, paramget, parampost, ipaddress, dateline)
				VALUES (
					$VB_API_REQUESTS[api_c],
					'" . $db->escape_string($VB_API_REQUESTS['api_m']) . "',
					'" . $db->escape_string(serialize($_GET)) . "',
					'" . (($vbulletin->options['apilogpostparam'])?$db->escape_string(serialize($post_copy)):'') . "',
					'" . $db->escape_string(IPADDRESS) . "',
					'" . TIMENOW . "'
				)
			");

			unset($hide, $post_copy);
		}

		// TODO: Disable human verification in this release. enabled it when release API to public
		$vbulletin->options['hvcheck'] = 0;
		$vbulletin->options['vbforum_url'] = '';
		$vbulletin->options['vbcms_url'] = '';
		$vbulletin->options['vbblog_url'] = '';
	}
	// api_init is a special method that is able to generate new client info.
	elseif ($VB_API_REQUESTS['api_m'] != 'api_init' AND !($vbulletin->debug AND $vbulletin->GPC['debug']))
	{
		print_apierror('missing_api_signature', 'Missing API Signature');
	}
}

// #############################################################################
// Setup session
if (!empty($db->explain))
{
	$db->timer_start('Session Handling');
}

$vbulletin->input->clean_array_gpc('r', array(
	's'       => TYPE_NOHTML,
	'styleid' => TYPE_INT,
	'langid'  => TYPE_INT,
));

// conditional used in templates to hide things from search engines.
$show['search_engine'] = ($vbulletin->superglobal_size['_COOKIE'] == 0 AND preg_match("#(google|bingbot|yahoo! slurp|facebookexternalhit)#si", $_SERVER['HTTP_USER_AGENT']));

// handle session input
if (!VB_API)
{
	$sessionhash = (!empty($vbulletin->GPC['s']) ? $vbulletin->GPC['s'] : $vbulletin->GPC[COOKIE_PREFIX . 'sessionhash']); // override cookie
}
else
{
	$sessionhash = '';
}

// Set up user's chosen language
if ($vbulletin->GPC['langid'] AND !empty($vbulletin->languagecache["{$vbulletin->GPC['langid']}"]['userselect']))
{
	$languageid =& $vbulletin->GPC['langid'];
	vbsetcookie('languageid', $languageid);
}
else if ($vbulletin->GPC[COOKIE_PREFIX . 'languageid'] AND !empty($vbulletin->languagecache[$vbulletin->GPC[COOKIE_PREFIX . 'languageid']]['userselect']))
{
	$languageid = $vbulletin->GPC[COOKIE_PREFIX . 'languageid'];
}
else
{
	$languageid = 0;
}

// Test mobile browser
$mobile_browser = false;
$mobile_browser_advanced = false;
if ($vbulletin->options['mobilestyleid_advanced'] OR $vbulletin->options['mobilestyleid_basic'])
{
	if (stripos($_SERVER['HTTP_USER_AGENT'], 'windows') === false OR preg_match('/(Windows Phone OS|htc)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
	{
		if (
			preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|Windows Phone OS|htc)/i', strtolower($_SERVER['HTTP_USER_AGENT']))
			OR
			stripos($_SERVER['HTTP_ACCEPT'],'application/vnd.wap.xhtml+xml') !== false
			OR
			((isset($_SERVER['HTTP_X_WAP_PROFILE']) OR isset($_SERVER['HTTP_PROFILE'])))
			OR
			stripos($_SERVER['ALL_HTTP'],'OperaMini') !== false
		)
		{
			$mobile_browser = true;
		}
		// This array is big and may be bigger later on. So we move it to a second if.
		else if (in_array(
					strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4)),
					array(
					'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
					'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
					'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
					'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
					'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
					'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
					'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
					'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
					'wapr','webc','winw','winw','xda ','xda-')
				)
			)
		{
			$mobile_browser = true;
			if(strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4)) == 'oper' AND  preg_match('/(linux|mac)/i', $_SERVER['HTTP_USER_AGENT']))
			{
				$mobile_browser = false;
			}
		}
	}

	if (
		$mobile_browser
			AND
		preg_match('/(ipad|ipod|iphone|blackberry|android|pre\/|palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine|Windows Phone OS|htc)/i', strtolower($_SERVER['HTTP_USER_AGENT']))
	)
	{
		$mobile_browser_advanced = true;
	}
}

// Set up user's chosen style
if ($vbulletin->GPC['styleid'])
{
	$styleid =& $vbulletin->GPC['styleid'];
	vbsetcookie('userstyleid', $styleid);
	if ($styleid == -1)
	{
		vbsetcookie('skipmobilestyle', 1);
		$vbulletin->GPC[COOKIE_PREFIX . 'skipmobilestyle'] = 1;
	}
	else if ($styleid == $vbulletin->options['mobilestyleid_advanced'] OR $styleid == $vbulletin->options['mobilestyleid_basic'])
	{
		vbsetcookie('skipmobilestyle', 0);
		$vbulletin->GPC[COOKIE_PREFIX . 'skipmobilestyle'] = 0;
	}
}
else if ($mobile_browser_advanced AND $vbulletin->options['mobilestyleid_advanced'] AND !$vbulletin->GPC[COOKIE_PREFIX . 'skipmobilestyle'] AND !$vbulletin->GPC[COOKIE_PREFIX . 'userstyleid'])
{
	$styleid = $vbulletin->options['mobilestyleid_advanced'];
}
else if ($mobile_browser AND $vbulletin->options['mobilestyleid_basic'] AND !$vbulletin->GPC[COOKIE_PREFIX . 'skipmobilestyle'] AND !$vbulletin->GPC[COOKIE_PREFIX . 'userstyleid'])
{
	$styleid = $vbulletin->options['mobilestyleid_basic'];
}
else if ($vbulletin->GPC[COOKIE_PREFIX . 'userstyleid'])
{
	$styleid = $vbulletin->GPC[COOKIE_PREFIX . 'userstyleid'];
}
else
{
	$styleid = 0;
}

$vbulletin->styleid = $styleid;
$vbulletin->mobile_browser = $mobile_browser;
$vbulletin->mobile_browser_advanced = $mobile_browser_advanced;

($hook = vBulletinHook::fetch_hook('init_startup_session_setup_start')) ? eval($hook) : false;

// build the session and setup the environment
$cookie_user = $vbulletin->GPC[COOKIE_PREFIX . 'userid'];
$rememberme = $vbulletin->GPC[COOKIE_PREFIX . 'password'];

$vbulletin->session = new vB_Session($vbulletin, $sessionhash, $cookie_user, $rememberme, $styleid, $languageid);
$vbulletin->userinfo =& $vbulletin->session->fetch_userinfo();

if (
	!defined('SKIP_SESSIONCREATE') AND
	$vbulletin->userinfo['userid'] == 0 AND 
	($rememberme == 'facebook' || $rememberme == 'facebook-retry')
)
{
	require_once(DIR . '/includes/class_facebook.php');
	$userid = vB_Facebook::instance()->getVbUseridFromFbUserid();

	if ($userid)
	{	
		if ($cookie_user == $userid)
		{
			require_once(DIR . '/includes/functions_login.php');

			$facebook_auth = verify_facebook_authentication();
			if ($facebook_auth)
			{
				$vbulletin->session->created = false;
				process_new_login('fbauto', false, '');
			}
		}
	}

	//redirect to handle a stale FB cookie when doing a FB "remember me".
	//only do it once to prevent redirect loops -- don't try this with
	//posts since we'd lose the post data in that case
	//
	//Some notes on the JS code (don't want them in the JS inself to avoid
	//increasing what gets sent to the browser).
	//1) This code is deliberately designed to avoid using subsystems that
	//	would increase the processing time for something that doesn't need it
	//	(we even avoid initializing JQUERY here).  This is the reason it is
	//	inline and not in a template.
	//2) The code inits the FB system which will create update the cookie
	//	if it is able to validate the user.  The cookie is what we are after.
	//	We use getLoginStatus instead of setting status to true because
	//	the latter introduces a race condition were we can do the redirect
	//	before the we've fully initialized and updated the cookie.  The
	//	explicit call to getLoginStatus allows us to redirect when the
	//	status is obtained.
	//3) If we fail to update the cookie we catch that when we try to
	//	create the vb session (which is why we only allow one retry)
	//4) The JS here should *never* prompt the user, assuming the FB
	//	docs are correct.
	//5) If the FB version is changed it needs to changed in the
	//	FB library class and the facebook.js file
	else if(
		strtolower($_SERVER['REQUEST_METHOD']) == 'get' AND
		$vbulletin->options['enablefacebookconnect'] AND
		$vbulletin->options['facebooksecret'] AND
		$vbulletin->options['facebookappid'] AND
		$rememberme == 'facebook'
	)
	{
		//if this isn't a retry, then do a redirect
		vbsetcookie('password', 'facebook-retry', true, true, true);
		$fbredirect = "
			<!DOCTYPE html>
			<html>
			<head>
				<script type='text/javascript' src='//connect.facebook.net/en_US/sdk.js'></script>
				<script type='text/javascript'>
					FB.init({
						appId   : '{$vbulletin->options['facebookappid']}',
						version : 'v2.2',
						status  : false,
						cookie  : true,
						xfbml   : false
					});

					FB.getLoginStatus(function(response)
					{
						window.top.location.reload(true);
					});
				</script>
			</head>
			<body></body>
			</html>
		";
		echo $fbredirect;
		exit;
	}
	else
	{
		//we tried and failed to log in via FB.  That probably means that the user
		//is logged out of facebook.  Let's kill the autolog in so that we stop
		//trying to connect via FB
		vbsetcookie('userid', '', true, true, true);
		vbsetcookie('password', '', true, true, true);
	}
}

// Hide sessionid in url if we are a search engine or if we have a cookie
$vbulletin->session->set_session_visibility(($show['search_engine'] OR $vbulletin->superglobal_size['_COOKIE'] > 0) AND !VB_API);
$vbulletin->session->do_lastvisit_update($vbulletin->GPC[COOKIE_PREFIX . 'lastvisit'], $vbulletin->GPC[COOKIE_PREFIX . 'lastactivity']);
define('USER_DEFAULT_STYLE_TYPE', isset($vbulletin->stylecache['mobile'][$vbulletin->userinfo['realstyleid']]) ? 'mobile' : 'standard');

// put the sessionhash into contact-us links automatically if required (issueid 21522)
if ($vbulletin->session->visible AND $vbulletin->options['contactuslink'] != '' AND substr(strtolower($vbulletin->options['contactuslink']), 0, 7) != 'mailto:')
{
	if (strpos($vbulletin->options['contactuslink'], '?') !== false)
	{
		$vbulletin->options['contactuslink'] = str_replace('?', '?' . $vbulletin->session->vars['sessionurl'], $vbulletin->options['contactuslink']);
	}
	else
	{
		$vbulletin->options['contactuslink'] .= $vbulletin->session->vars['sessionurl_q'];
	}
}

($hook = vBulletinHook::fetch_hook('init_startup_session_setup_complete')) ? eval($hook) : false;

// Because of Signature Verification, VB API won't need to verify securitytoken
// CSRF Protection for POST requests
if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' AND !VB_API)
{
	if (empty($_POST) AND isset($_SERVER['CONTENT_LENGTH']) AND $_SERVER['CONTENT_LENGTH'] > 0)
	{
		die('The file(s) uploaded were too large to process.');
	}

	if ($vbulletin->userinfo['userid'] > 0 AND defined('CSRF_PROTECTION') AND CSRF_PROTECTION === true)
	{
		$vbulletin->input->clean_array_gpc('p', array(
			'securitytoken' => TYPE_STR,
		));

		if (!in_array($_POST['do'], $vbulletin->csrf_skip_list))
		{
			if (!verify_security_token($vbulletin->GPC['securitytoken'], $vbulletin->userinfo['securitytoken_raw']))
			{
				switch ($vbulletin->GPC['securitytoken'])
				{
					case '':
						define('CSRF_ERROR', 'missing');
						break;
					case 'guest':
						define('CSRF_ERROR', 'guest');
						break;
					case 'timeout':
						define('CSRF_ERROR', 'timeout');
						break;
					default:
						define('CSRF_ERROR', 'invalid');
				}
			}
		}
	}
	else if (!defined('CSRF_PROTECTION') AND !defined('SKIP_REFERRER_CHECK'))
	{
		if (VB_HTTP_HOST AND $_SERVER['HTTP_REFERER'])
		{
			$host_parts = $vbulletin->input->parse_url($_SERVER['HTTP_HOST']);
			$http_host_port = intval($host_parts['port']);
			$http_host = strtolower(VB_HTTP_HOST . ((!empty($http_host_port) AND $http_host_port != '80') ? ":$http_host_port" : ''));

			$referrer_parts = $vbulletin->input->parse_url($_SERVER['HTTP_REFERER']);
			$ref_port = isset($referrer_parts['port']) ? intval($referrer_parts['port']) : 0;
			$ref_host = strtolower($referrer_parts['host'] . ((!empty($ref_port) AND $ref_port != '80') ? ":$ref_port" : ''));

			if ($http_host == $ref_host)
			{	/* Instant match is good enough
				no need to check anything further. */
				$pass_ref_check = true;
			}
			else
			{
				$pass_ref_check = false;
				$allowed = array('.paypal.com');
				$allowed[] = '.'.preg_replace('#^www\.#i', '', $http_host);
				$whitelist = preg_split('#\s+#', $vbulletin->options['allowedreferrers'], -1, PREG_SPLIT_NO_EMPTY); // Get whitelist
				$allowed = array_unique(is_array($whitelist) ? array_merge($allowed,$whitelist) : $allowed); // Merge and de-duplicate.

				foreach ($allowed AS $host)
				{
					$host = strtolower($host);
					if (substr($host,0,1) == '.' AND
					(preg_match('#' . preg_quote($host, '#') . '$#siU', $ref_host) OR substr($host,1) == $ref_host))
					{
						$pass_ref_check = true;
						break;
					}
				}
				unset($allowed, $whitelist);
			}

			if ($pass_ref_check == false)
			{
				die('In order to accept POST requests originating from this domain, the admin must add the domain to the whitelist.');
			}
		}
	}
}


// Google Web Accelerator can display sensitive data ignoring any headers regarding caching
// it's a good thing for guests but not for anyone else
if ($vbulletin->userinfo['userid'] > 0 AND isset($_SERVER['HTTP_X_MOZ']) AND strpos($_SERVER['HTTP_X_MOZ'], 'prefetch') !== false)
{
	if (SAPI_NAME == 'cgi' OR SAPI_NAME == 'cgi-fcgi')
	{
		header('Status: 403 Forbidden');
	}
	else
	{
		header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
	}
	die('Prefetching is not allowed due to the various privacy issues that arise.');
}

// use the session-specified style if there is one
if ($vbulletin->session->vars['styleid'] != 0)
{
	$vbulletin->userinfo['styleid'] = $vbulletin->session->vars['styleid'];
}

if (!empty($db->explain))
{
	$db->timer_stop(false);
}

bootstrap_framework(); // load the vB Framework.

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 93328 $
|| # $Date: 2017-03-10 15:26:29 -0800 (Fri, 10 Mar 2017) $
|| ####################################################################
\*======================================================================*/
?>
