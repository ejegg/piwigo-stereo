<?php

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

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

function plugin_install() {
	global $prefixeTable;

	$query = '
    CREATE TABLE IF NOT EXISTS '.$prefixeTable.'stereo (
      media_id int(11) NOT NULL,
      x int NOT NULL DEFAULT 0,
      y int NOT NULL DEFAULT 0,
      PRIMARY KEY (media_id)
    ) ENGINE = MyISAM
    ;';
	pwg_query($query);
}


function plugin_uninstall() {
	global $prefixeTable;

	$query = '
    DROP TABLE '.$prefixeTable.'stereo
    ;';
	pwg_query($query);
}
