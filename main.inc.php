<?php
/*
Version: 0.2.1
Plugin Name: Stereo
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=836
Author: Elliott Eggleston <ejegg@ejegg.com>
Author URI: https://ejegg.com
Description: Display and manage stereoscopic (3D / 3-D) images.
*/

/* 
 * Copyright (C) 2016 Elliott Eggleston <ejegg@ejegg.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
global $conf;

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

define('STEREO_PATH', PHPWG_PLUGINS_PATH.basename(dirname(__FILE__)).'/');

$conf['picture_ext'][] = 'mpo';

include_once( __DIR__ . '/include/functions.php' );

add_event_handler('render_element_content', 'Stereo_render_element_content', 40, 2 ); //TODO: what are these numbers?
add_event_handler('tabsheet_before_select', 'Stereo_tabsheet');
add_event_handler('loc_end_element_set_global', 'Stereo_loc_end_element_set_global');
add_event_handler('element_set_global_action', 'Stereo_element_set_global_action', 40, 2);
add_event_handler('get_batch_manager_prefilters', 'Stereo_get_batch_manager_prefilters');
add_event_handler('perform_batch_manager_prefilters', 'Stereo_perform_batch_manager_prefilters', 40, 2);
