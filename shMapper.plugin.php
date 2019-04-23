<?php
/*
Plugin Name: ShMapper
Plugin URI: http://genagl.ru/?p=652
Description: Location and logistics services for NKO
Version: 0.0.121
Author: Genagl
Author URI: http://genagl.ru/author
Contributors: Teplitsa Support Team (suptestru@gmail.com)
License: GPL2
Text Domain:   shmapper
Domain Path:   /lang/
*/
/*  Copyright 2018  Genagl  (email: genag1@list.ru)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/ 

//библиотека переводов
function init_textdomain_shmapper() 
{ 
	if (function_exists('load_plugin_textdomain')) 
	{
		load_plugin_textdomain("shmapper", false , dirname( plugin_basename( __FILE__ ) ) .'/lang/');     
	} 
}
add_action('plugins_loaded', 'init_textdomain_shmapper');

//Paths
define('SHM_URLPATH', WP_PLUGIN_URL.'/shmapper/');
define('SHM_REAL_PATH', WP_PLUGIN_DIR.'/'.plugin_basename(dirname(__FILE__)).'/');
define('SHMAPPER', 'shmapper');
define('SHM_MAP', 'shm_map');
define('SHM_POINT', 'shm_point');
define('SHM_POINT_TYPE', 'shm_point_type');
define('SHM_REQUEST', 'shm_request');
define('SHMAPPER_PLAIN_TEXT_TYPE_ID', 1);
define('SHMAPPER_NAME_TYPE_ID', 2);
define('SHMAPPER_PLAIN_NUMBER_TYPE_ID', 3);
define('SHMAPPER_EMAIL_TYPE_ID', 4);
define('SHMAPPER_PHONE_TYPE_ID', 5);
define('SHMAPPER_TEXTAREA_TYPE_ID', 6);
define('SHMAPPER_IMAGE_TYPE_ID', 7);
define('SHMAPPER_MARK_TYPE_ID', 8);
define('SHMAPPER_TITLE_TYPE_ID', 9);
define('SHM_CSV_STROKE_SEPARATOR', ';');
define('SHM_CSV_ROW_SEPARATOR', '
');

require_once(SHM_REAL_PATH.'class/ShmShmapper.class.php');
require_once(SHM_REAL_PATH.'class/ShmMapper_ajax.class.php');
if(!class_exists("Shm_Post"))
	require_once(SHM_REAL_PATH.'class/Shm_Post.php');
if(!class_exists("Shm_Object_Type"))
	require_once(SHM_REAL_PATH.'class/Shm_Object_Type.php');
require_once(SHM_REAL_PATH.'class/ShmMap.class.php');
require_once(SHM_REAL_PATH.'class/ShmMapPointType.class.php');
require_once(SHM_REAL_PATH.'class/ShmPoint.class.php');
require_once(SHM_REAL_PATH.'class/ShmMapperRequest.class.php');
require_once(SHM_REAL_PATH.'class/ShmForm.class.php');
require_once(SHM_REAL_PATH.'class/ShmMapper_Assistants.class.php');
require_once(SHM_REAL_PATH.'shortcode/shm_shortcodes.php');
require_once(SHM_REAL_PATH.'widget/ShMap.widget.php');

register_activation_hook( __FILE__, array( ShmShmapper, 'activate' ) );
if (function_exists('register_deactivation_hook'))
{
	register_deactivation_hook(__FILE__, array(ShmShmapper, 'deactivate'));
}
add_action("init", "init_shmapper", 1);
function init_shmapper()
{
	ShmShmapper::get_instance();
	ShmMapper_Assistants::get_instance();
	ShmMapper_ajax::get_instance();
	ShmMap::init();
	ShmMapperRequest::init();
	ShmMapPointType::init();
	ShmPoint::init();
	ShmForm::init();
}
function shm_is_session()
{
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	return is_plugin_active( 'wp-session-manager/wp-session-manager.php' ) ;		
}