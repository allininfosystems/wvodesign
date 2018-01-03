/***********************************************************************/
			
		GlowHost - Spam-O-Matic plug-in
	
			v. 2.0.2 beta

/***********************************************************************/



/************************** 1. CHANGES TO VB ***************************/



3 new database tables created



9 hooks used:

	- register_addmember_process
	- register_addmember_complete
	- newpost_process
	- threadfpdata_presave
	- postdata_presave
	- showthread_start
	- inlinemod_action_switch
	- forumhome_complete
	- parse_templates


1 new template



No template modifications

 or table alterations



/************************** 2. INSTALLATION ****************************/



2.1 IF UPGRADING FROM SPAM-O-MATIC VERSION 1:
Make sure to remove the following file before installing version 2
/forum/includes/xml/bitfield_glowhostspamomatic.xml

2.2. Upload content of the upload folder into you forum root folder

*

2.3. Import product-glowhostspamomatic.xml into products (Located
 at 
AdminCP -> Plugin System -> Manage Products -> Add/Import Product)



/************************** 3. SETTINGS ********************************/



3.1. You can edit plug-in options at: AdminCP -> Settings -> Options ->
 
GlowHost - Spam-O-Matic



3.2. You can edit plug-in messages at: AdminCP -> Languages & Phrases ->

Phrase Manager -> Search Variable Name for 'glowhostspamomatic'



/************************** 4. ACTIONS *********************************/



4.1. 
	A) Located @ Edit user profile
	B) If you have you API keys (set in settings) - 
you will be able 
to submit user details to StopForumSpam.com and Akismet databases simply 
Delete a post as spam and ban the user to submit them if you have these 
options enabled.

4.2. Get API key for Akismet 
and enter it in settings. After this edit 
your usergroups and you will
 see the option to "Filter posts with Akismet". 
Only check groups with
 untrusted users (i.e. Unregistered / Not Logged In, 
Registered Users).



/************************** 5. UNINSTALLATION **************************/



5.1. After uninstall, please, remove 

/includes/xml/bitfield_glowhostspamomatic.xml file from server

==
* Please note, for this plugin to work, cURL must be installed on the server 
or allow_url_fopen enable in PHP settings