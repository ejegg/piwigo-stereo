<?php
/*
Version: 0.1
Plugin Name: Stereo
Plugin URI: 
Author: Elliott Eggleston <ejegg@ejegg.com>
Author URI: http://ejegg.com
Description: Display and manage stereoscopic (3D / 3-D) images.
*/

/* 
 * Copyright (C) 2015 Elliott Eggleston <ejegg@ejegg.com>
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

add_event_handler('render_element_content', 'render_Stereo_element_content', 40, 2 ); //TODO: what are these numbers?
add_event_handler('tabsheet_before_select', 'Stereo_tabsheet');
#add_event_handler('loc_end_picture_modify', 'Stereo_modify');