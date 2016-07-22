<?php

/* 
 * Copyright (C) 2015 elliott
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

function render_Stereo_element_content($content, $picture)
{
	global $page;

	if ( isset($page['slideshow']) and $page['slideshow'] ) {
		return $content;
	}
	if ( !preg_match ( '/.*mpo$/i', $picture['file'] ) ) {
		return $content;
	}
	$gif_relative = preg_replace( '/jpg$/i', 'gif', $picture['path'] );
	$gif_url = PWG_DERIVATIVE_DIR . preg_replace( '/^\.\//', '', $gif_relative );
	$absolute_path = realpath( PWG_DERIVATIVE_DIR ) . '/' . $gif_relative;
	if ( !file_exists( $absolute_path ) ) {
		Stereo_generate_gif( $picture, $absolute_path );
	}
	$rel_dir = 'plugins/' . basename( realpath( __DIR__ . '/..' ) );
	return $content . " <img src=\"$gif_url\" id=\"stereoGif\" />
  <script type=\"text/javascript\" src=\"$rel_dir/libgif.js\" ></script>
  <script type=\"text/javascript\" src=\"$rel_dir/hammer.js\" ></script>
  <script type=\"text/javascript\" src=\"$rel_dir/wiggleAdjust.js\" ></script>
  <script type=\"text/javascript\">
     var img = document.getElementById('stereoGif');
     var superG = new SuperGif({gif:img});
     var adjust = new WiggleAdjust(superG, {$picture['id']});
     superG.load( adjust.attach );
  </script>
";
}

function Stereo_generate_gif( $picture, $gif_path ) {
	 $orig_path = realpath($picture['path']);

	 $rjpg = tempnam( '/tmp', 'piwigo_Stereo_' ) . '.jpg';
	 $ljpg = tempnam( '/tmp', 'piwigo_Stereo_' ) . '.jpg';
	 //TODO: security!
	 exec( "exiftool -trailer:all= '$orig_path' -o $rjpg" );
	 exec( "exiftool '$orig_path' -mpimage2 -b > $ljpg" );
	 exec( "convert -loop 0 -delay 0 $ljpg -delay 0 $rjpg -resize 1024x $gif_path" );
	 exec( "rm $rjpg $ljpg" );
}

function Stereo_tabsheet( $tabs, $context ) {
	global $admin_photo_base_url;
	if ( $context != 'photo' ) {
		return $tabs;
	}
	$tabs['stereo'] = array(
		'caption' => 'Stereo adjustment',
		'url' => get_root_url().'admin.php?page=plugin&amp;section=piwigo-stereo/admin.php&amp;image_id='.$_GET['image_id']
	);
	return $tabs;
}
