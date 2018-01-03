<?php if (!defined('IDIR')) { die; }
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin  - Licence Number VBF6025393
|| # ---------------------------------------------------------------- # ||
|| # All PHP code in this file is �2000-2011 vBulletin Solutions Inc. # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
/**
* drupal6_cms Import articles
* 
* @package         ImpEx.drupal6_cms
* @version        $Revision: 2255 $
* @author        Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout    $Name:  $
* @date         $Date: 2009-12-17 19:35:15 -0800 (Thu, 17 Dec 2009) $
* @copyright     http://www.vbulletin.com/license.html
*
*/ 

class joomla_cms_004 extends joomla_cms_000
{
    var $_dependent     = '003';

    function joomla_cms_004(&$displayobject)
    {
        $this->_modulestring = 'Import Article';
    }

    function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
    {
        if ($this->check_order($sessionobject,$this->_dependent))
        {
            if ($this->_restart)
            {
                if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source,'clear_imported_articles'))
                {
                    $displayobject->display_now("<h4>{$displayobject->phrases['article_restart_ok']}</h4>");
                    $this->_restart = true;
                }
                else
                {
                    $sessionobject->add_error(substr(get_class($this) , -3), $displayobject->phrases['post_restart_failed'], $displayobject->phrases['check_db_permissions']);
                }
            }

            // Start up the table
            $displayobject->update_basic('title', $displayobject->phrases['import_article']);
            $displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
            $displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
            $displayobject->update_html($displayobject->make_table_header($displayobject->phrases['import_article']));

            // Ask some questions
            $displayobject->update_html($displayobject->make_input_code('Enter the number of articles (content) per page','perpage',100));
            #$displayobject->update_html($displayobject->make_input_code('Enter the node type you want to import as an article','node_type','page'));


            // End the table
            $displayobject->update_html($displayobject->do_form_footer($displayobject->phrases['continue'],$displayobject->phrases['reset']));

            // Reset/Setup counters for this
            $sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
            $sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');
            $sessionobject->add_session_var('startat','0');
        }
        else
        {
            // Dependant has not been run
            $displayobject->update_html($displayobject->do_form_header('index',''));
            $displayobject->update_html($displayobject->make_description("<p>{$displayobject->phrases['dependant_on']}<i><b> " . $sessionobject->get_module_title($this->_dependent) . "</b> {$displayobject->phrases['cant_run']}</i> ."));
            $displayobject->update_html($displayobject->do_form_footer($displayobject->phrases['continue'], ''));
            $sessionobject->set_session_var(substr(get_class($this) , -3),'FALSE');
            $sessionobject->set_session_var('module','000');
        }
    }

    function resume(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
    {
        // Set up working variables
        $displayobject->update_basic('displaymodules','FALSE');
        $target_database_type    = $sessionobject->get_session_var('targetdatabasetype');
        $target_table_prefix    = $sessionobject->get_session_var('targettableprefix');
        $source_database_type    = $sessionobject->get_session_var('sourcedatabasetype');
        $source_table_prefix    = $sessionobject->get_session_var('sourcetableprefix');

        // Per page vars
        $article_start_at        = $sessionobject->get_session_var('startat');
        $article_per_page        = $sessionobject->get_session_var('perpage');
        $class_num                = substr(get_class($this) , -3);

        // Clone and cache
        $article_object         = new ImpExData($Db_target, $sessionobject, 'article');
        $idcache                 = new ImpExCache($Db_target, $target_database_type, $target_table_prefix);

        if(!$sessionobject->get_session_var($class_num . '_start'))
        {
            $sessionobject->timing($class_num,'start' ,$sessionobject->get_session_var('autosubmit'));
        }

        $article_array = $this->get_joomla_content_details($Db_source, $source_database_type, $source_table_prefix, $article_start_at, $article_per_page);

        $displayobject->display_now("<h4>{$displayobject->phrases['importing']} " . $article_array['count'] . " nodes</h4><p><b>{$displayobject->phrases['from']}</b> : " . $article_start_at . " ::  <b>{$displayobject->phrases['to']}</b> : " . $article_array['lastid'] . "</p>");

        $article_object = new ImpExData($Db_target, $sessionobject, 'cms_article', 'cms');
    
        foreach ($article_array['data'] as $article_id => $article)
        {

            $try = (phpversion() < '5' ? $article_object : clone($article_object));

            // Mandatory
            $try->set_value('mandatory', 'importcmsarticleid',    $article_id);

            $try->set_value('mandatory', 'pagetext',       ($article['fulltext'] != NULL ? $article['fulltext'] : $article['introtext']));
            $try->set_value('mandatory', 'title',            $article['title']);
            $try->set_value('mandatory', 'postauthor',         $idcache->get_id('user', $article['created_by']));
            $try->set_value('mandatory', 'previewtext',         substr($article['introtext'], 0, 2040));

            $try->set_value('nonmandatory', 'created',         $article['created']);
    
            if($try->is_valid())
            {
                if (!method_exists($try, 'import_article'))
                {
                    die ('You have not selected the CMS target system, restart the import and ensure you select the correct target');
                }
    
                if($try->import_article($Db_target, $target_database_type, $target_table_prefix))
                {
                    if(shortoutput)
                    {
                        $displayobject->display_now('.');
                    }
                    else
                    {
                        $displayobject->display_now('<br />' . $article['article_id'] . ' <span class="isucc"><b>' . $try->how_complete() . '%</b></span> Article -> ' . $article['title']);
                    }

                    $sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
                }
                else
                {
                    $sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
                    $sessionobject->add_error($Db_target, 'warning', $class_num, $article['article_id'], $displayobject->phrases['article_not_imported'], $displayobject->phrases['article_not_imported_rem']);
                    $displayobject->display_now("<br />{$displayobject->phrases['failed']} :: {$displayobject->phrases['article_not_imported']}");
                }
            }
            else
            {
                $sessionobject->add_error($Db_target, 'invalid', $class_num, $article['article_id'], $displayobject->phrases['invalid_object'], $try->_failedon);
                $displayobject->display_now("<br />{$displayobject->phrases['invalid_object']}" . $try->_failedon);
                $sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
            }
            unset($try);
        }

        if (empty($article_array['count']) OR $article_array['count'] < $article_per_page)
        {
            $sessionobject->timing($class_num,'stop', $sessionobject->get_session_var('autosubmit'));
            $sessionobject->remove_session_var($class_num . '_start');

            $displayobject->update_html($displayobject->module_finished($this->_modulestring,
                $sessionobject->return_stats($class_num, '_time_taken'),
                $sessionobject->return_stats($class_num, '_objects_done'),
                $sessionobject->return_stats($class_num, '_objects_failed')
            ));

            $sessionobject->set_session_var($class_num,'FINISHED');
            $sessionobject->set_session_var('module','000');
            $sessionobject->set_session_var('autosubmit','0');
            $displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
        }
        else
        {
            $sessionobject->set_session_var('startat',$article_array['lastid']);
            $displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
        }
    }// End resume
}//End Class
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:23, Fri Mar 18th 2011
|| # CVS: $RCSfile: 009.php,v $ - $Revision: 2255 $
|| ####################################################################
\*======================================================================*/
?>
