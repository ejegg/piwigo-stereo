<?php

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

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');
// Check access and exit when user status is not ok
check_status(ACCESS_ADMINISTRATOR);
// FIXME: Duplicated boilerplate - could be avoided with a hook in the else
// clause at the bottom of admin/photo.php letting you set the right include file
if (!isset($_GET['image_id']) or !isset($_GET['section']))
{
	die('Invalid data!');
}

check_input_parameter('image_id', $_GET, false, PATTERN_ID);

$admin_photo_base_url = get_root_url().'admin.php?page=photo-'.$_GET['image_id'];
$self_url = get_root_url().'admin.php?page=plugin&amp;section=piwigo-stereo/admin.php&amp;image_id='.$_GET['image_id'];

global $template;

$template->set_filename('plugin_admin_content', STEREO_PATH . 'admin.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');

include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');
$tabsheet = new tabsheet();
$tabsheet->set_id('photo');
$tabsheet->select('stereo');
$tabsheet->assign();
$template->assign(array(
	'PWG_TOKEN' => get_pwg_token(),
	'F_ACTION'  => $self_url,
	'TITLE'     => 'Stereo adjustment for a picture',
));
