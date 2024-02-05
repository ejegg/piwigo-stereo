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


class stereo_maintain {
	public function install($plugin_version, &$errors=array()) {
		global $prefixeTable;

		$query = '
			CREATE TABLE IF NOT EXISTS '.$prefixeTable.'stereo (
			  media_id int(11) NOT NULL,
			  x int NOT NULL DEFAULT 0,
			  y int NOT NULL DEFAULT 0,
			  r float(5,1) NOT NULL DEFAULT 0,
			  PRIMARY KEY (media_id)
			);';
		pwg_query($query);
	}

	public function update($old_version, $new_version, &$errors=array()) {
		global $prefixeTable;

		if ($old_version !== 'auto' && version_compare($old_version, '0.3.2', '<=')) {
			$query = '
				ALTER TABLE '.$prefixeTable.'stereo
				ADD r float(5,1) NOT NULL DEFAULT 0;';
			pwg_query($query);
		}
	}

	public function uninstall() {
		global $prefixeTable;

		$query = '
			DROP TABLE '.$prefixeTable.'stereo;';

		pwg_query($query);
	}

	public function activate() {}
	public function deactivate() {}
}

// Need to use class_alias because id has '-' in it.
class_alias('stereo_maintain', 'piwigo-stereo_maintain');
// Newer versions of Piwigo now replace the - with a _
class_alias('stereo_maintain', 'piwigo_stereo_maintain');

function plugin_install($plugin_id, $plugin_version, &$errors) {
	$legacyInstance = new stereo_maintain();
	$legacyInstance->install($plugin_version, $errors);
}

function plugin_uninstall($plugin_id) {
	$legacyInstance = new stereo_maintain();
	$legacyInstance->uninstall();
}
