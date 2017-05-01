<?php

/* 
 * Copyright (C) 2016 elliott
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

function Stereo_render_element_content($content, $picture)
{
	global $page, $template;

	if ( isset($page['slideshow']) and $page['slideshow'] ) {
		return $content;
	}
	if ( !preg_match ( '/.*mpo$/i', $picture['file'] ) ) {
		return $content;
	}
	$gif_relative = preg_replace( '/jpg$/i', 'gif', $picture['path'] );
	$gif_absolute_path = Stereo_get_absolute_path( $gif_relative );
	$r_relative_path = preg_replace( '/.jpg$/i', '_r.jpg', $picture['path'] );
	$l_relative_path = preg_replace( '/.jpg$/i', '_l.jpg', $picture['path'] );
	$r_absolute_path = Stereo_get_absolute_path( $r_relative_path );
	$l_absolute_path = Stereo_get_absolute_path( $l_relative_path );

	if ( !file_exists( $r_absolute_path ) ) {
		Stereo_split_mpo( $picture['path'], $r_absolute_path, $l_absolute_path );
	}
	if ( !file_exists( $gif_absolute_path ) ) {
		Stereo_generate_gif( $r_absolute_path, $l_absolute_path, $gif_absolute_path );
	}
	$template->set_filename( 'Stereo_footer', STEREO_PATH . '/stereo_footer.tpl' );

	if ( isset( $_COOKIE[STEREO_MODE_COOKIE] ) ) {
		$mode = $_COOKIE[STEREO_MODE_COOKIE];
	} else {
		$mode = STEREO_MODE_GIF;
	}
	$template->set_filename( 'Stereo_picture', STEREO_PATH . "/picture_$mode.tpl" );
	$rel_dir = 'plugins/' . basename( realpath( __DIR__ . '/..' ) );
	load_language('plugin.lang', STEREO_PATH);
	$template->assign( array(
		'REL_DIR' => $rel_dir,
		'STEREO_FORMAT' => l10n( 'STEREO_FORMAT' ),
		'STEREO_FORMAT_GIF' => l10n( 'STEREO_FORMAT_GIF' ),
		'STEREO_FORMAT_CROSS_EYED' => l10n( 'STEREO_FORMAT_CROSS_EYED' ),
		'STEREO_FORMAT_WALL_EYED' => l10n( 'STEREO_FORMAT_WALL_EYED' ),
	) );
	$checkedKey = strtoupper( $mode ) . '_SELECTED';
	$template->assign( $checkedKey, 'checked' );
	switch( $mode ) {
		case STEREO_MODE_GIF:
			Stereo_render_gif( $picture, $gif_relative );
			break;
		case STEREO_MODE_CROSS_EYED:
		case STEREO_MODE_WALL_EYED:
			Stereo_render_side_by_side( $r_relative_path, $l_relative_path );
			break;
	}
	return $content .
		$template->parse( 'Stereo_picture', true ) .
		$template->parse( 'Stereo_footer', true );
}

function Stereo_render_gif( $picture, $gif_relative ) {
	global $prefixeTable, $template;

	$gif_url = PWG_DERIVATIVE_DIR . preg_replace( '/^\.\//', '', $gif_relative );
	$query = '
		SELECT *
		FROM '.$prefixeTable.'stereo
		WHERE media_id = ' . $picture['id'];
	$offset = pwg_db_fetch_assoc(pwg_query($query));
	$jsOffset = '';
	if ( $offset ) {
		$jsOffset = ", { x: {$offset['x']}, y: {$offset['y']} }";
	}
	$template->assign( array(
		'GIF_URL' => $gif_url,
		'WIGGLE_PARAMS' => $picture['id'] . $jsOffset,
	) );
}

function Stereo_get_absolute_path( $path ) {
	return realpath( PWG_DERIVATIVE_DIR ) . '/' . $path;
}

function Stereo_get_url( $path ) {
	return PWG_DERIVATIVE_DIR . preg_replace( '/^\.\//', '', $path );
}

function Stereo_render_side_by_side( $r_path, $l_path ) {
	global $template;
	$r_url = Stereo_get_url( $r_path );
	$l_url = Stereo_get_url( $l_path );
	$template->assign( array(
		'R_URL' => $r_url,
		'L_URL' => $l_url,
	) );
}

// Combine two jpgs into a single gif
function Stereo_generate_gif( $rjpg, $ljpg, $gif_path ) {
	// TODO: get rid of exec, though php-gd doesn't support animation
	exec( "convert -loop 0 -delay 0 $ljpg -delay 0 $rjpg $gif_path" );
}

// Split the MPO file into 2 JPEGs
function Stereo_split_mpo( $orig_path, $r_path, $l_path ) {
	$r_full = preg_replace( '/\.jpg$/', '_full.jpg', $r_path );
	$l_full = preg_replace( '/\.jpg$/', '_full.jpg', $l_path );
	$marker = hex2bin( 'ffd8ffe1' ); // EXIF start-of-image + app1 header
	$in = fopen( $orig_path, 'rb' );
	$out = fopen( $r_full, 'wb' ); // MPO stores the right image first
	$chunk_size = 1024 * 100; // Read 100k at a time
	$first = true; // Are we still reading / writing the first picture?
	$last_chunk = ''; // Save in case the marker crosses a chunk boundary
	do {
		$chunk = fread( $in, $chunk_size );
		if ( $first ) {
			$search_space = $last_chunk . $chunk;
			// Start searching 32 bytes in to skip the first marker
			$pos = strpos( $search_space, $marker, 32 );
			if ( $pos === false ) {
				fwrite( $out, $chunk );
				// Save the last 64 bytes of the chunk
				$last_chunk = substr( $chunk, -64 );
			} else {
				// Found the marker!
				// Correct position for the last chunk
				$pos = $pos - strlen( $last_chunk );
				// Write the final bit of the first JPEG
				fwrite( $out, $chunk, $pos );
				fclose( $out );
				// Now open the second file and write the rest of the chunk
				$out = fopen( $l_full, 'wb' );
				fwrite( $out, substr( $chunk, $pos ) );
				$first = false;
			}
		} else {
			fwrite( $out, $chunk );
		}
	} while ( !feof( $in ) );
	fclose( $in );
	fclose( $out );
	// Then resize the split files
	// TODO: multiple sizes?
	exec( "convert $l_full -resize 1024x $l_path" );
	exec( "convert $r_full -resize 1024x $r_path" );
}

function Stereo_tabsheet( $tabs, $context ) {
	global $prefixeTable;
	if ( $context != 'photo' ) {
		return $tabs;
	}
	load_language('plugin.lang', STEREO_PATH);
	check_input_parameter('image_id', $_GET, false, PATTERN_ID);
	$id = $_GET['image_id'];
	$query = '
		SELECT file from '.$prefixeTable.'images
		WHERE id = ' . $id;
	$result = pwg_db_fetch_assoc(pwg_query($query));
	if ( $result && preg_match ( '/.*mpo$/i', $result['file'] ) ) {
		$tabs['stereo'] = array(
			'caption' => l10n('STEREO_ADJUSTMENT'),
			'url' => Stereo_get_admin_url( $id )
		);
	}
	return $tabs;
}

function Stereo_get_admin_url( $id ) {
	$plug_dir = basename( realpath( __DIR__ . '/..' ) );
	return get_root_url() . 'admin.php?page=plugin&amp;section=' .
		$plug_dir . '/admin.php&amp;image_id=' . $id;
}

function Stereo_loc_end_element_set_global() {
	global $template;

	load_language( 'plugin.lang', STEREO_PATH );

	$template->set_filename( 'Stereo_batch_global', STEREO_PATH . '/batch_global.tpl' );
	$template->append( 'element_set_global_plugins_actions',
		array(
			'ID' => 'stereo',
			'NAME'=>l10n('STEREO_ADJUSTMENT'),
			'CONTENT' => $template->parse( 'Stereo_batch_global', true )
		)
	);
}

function Stereo_element_set_global_action( $action, $collection ) {
	if ( $action !== 'stereo' ) {
		return;
	}

	global $page, $prefixeTable;
	load_language( 'plugin.lang', STEREO_PATH );

	$x = trim( $_POST['offsetX'] );
	$y = trim( $_POST['offsetY'] );

	$set = array();
	if ( $x !== '' && is_numeric( $x ) ) {
		$set[] = "x = $x";
	}
	if ( $y !== '' && is_numeric( $y ) ) {
		$set[] = "y = $y";
	}

	if ( empty( $set ) ) {
		$page['errors'][] = l10n( 'STEREO_BATCH_NO_INPUT' );
	} else {
		$update_query = 'UPDATE ' . $prefixeTable . 'stereo SET ' .
			implode( ',', $set ) .
			' WHERE media_id IN (' . implode( ',', $collection ) . ')';
		pwg_query($update_query);
		$page['infos'][] = l10n( 'STEREO_EDIT_SUCCESS' );
	}
}


function Stereo_get_batch_manager_prefilters( $prefilters ) {
	load_language( 'plugin.lang', STEREO_PATH );

	$prefilters[] = array(
		'ID' => 'stereo0',
		'NAME' => l10n( '3D_FILTER' )
	);
	return $prefilters;
}

function Stereo_perform_batch_manager_prefilters( $filter_sets, $prefilter ) {
	if ( $prefilter === 'stereo0' ) {
		$query = "SELECT id FROM " . IMAGES_TABLE .
			" WHERE UPPER( RIGHT( file, 3 ) ) = 'MPO'";
		$filter_sets[] = query2array( $query, null, 'id' );
	}

	return $filter_sets;
}

/**
 * Get a difference score for the two images with a given offset
 *
 * @param $left Imagick object for the left-eye image
 * @param $right Imagick object for the right-eye image
 * @param $w int width of images
 * @param $h int height of images
 * @param $offX int
 * @param $offY int
 *
 * @return float Number from 0-1 representing how different the images are
 */
function Stereo_diff_images( $left, $right, $w, $h, $offX, $offY ) {
	$newW = $w - abs( $offX );
	$newH = $h - abs( $offY );
	if ( $newW < 1 || $newH < 1 ) {
		throw new RuntimeException( 'Offset greater than width or height!' );
	}
	$newLeft = clone $left;
	$newRight = clone $right;
	if ( $offX > 0 ) {
		$xLeft = $offX;
		$xRight = 0;
	} else {
		$xLeft = 0;
		$xRight = -1 * $offX;
	}
	if ( $offY > 0 ) {
		$yLeft = $offY;
		$yRight = 0;
	} else {
		$yLeft = 0;
		$yRight = -1 * $offY;
	}
	if ( $offX !== 0 || $offY !== 0 ) {
		$newLeft->cropImage( $newW, $newH, $xLeft, $yLeft );
		$newRight->cropImage( $newW, $newH, $xRight, $yRight );
	}
	$compare = $newLeft->compareImages( $newRight, Imagick::METRIC_MEANSQUAREERROR );
	return $compare[1];
}

function Stereo_find_best_alignment(
	$leftPath,
	$rightPath,
	$xGrid = 0.1,
	$yGrid = 0.02,
	$xRand = 0.05,
	$yRand = 0.01,
	$scaleFactor = 0.95
) {
	$left = new Imagick( $leftPath );
	$right = new Imagick( $rightPath );
	$w = $left->getImageWidth();
	$h = $left->getImageHeight();
	//$left->blurImage( $w / 200, 2 );
	//$right->blurImage( $w / 200, 2 );
	$bestX = 0;
	$bestY = 0;
	$bestDiff = 1;
	// First check 9 evenly spaced grid points
	for( $testX = -1; $testX < 2; $testX++ ) {
		for ( $testY = -1; $testY < 2; $testY++ ) {
			$offX = floor( $testX * $w * $xGrid );
			$offY = floor( $testY * $h * $yGrid );
			$score = Stereo_diff_images( $left, $right, $w, $h, $offX, $offY );
			if ( $score < $bestDiff ) {
				$bestDiff = $score;
				$bestX = $offX;
				$bestY = $offY;
			}
		}
	}
	// Then do some random searches around the best of those
	$iter = 0;
	$scale = 1;
	do {
		$testX = ( mt_rand( 0, 1000 ) - 500 ) / 1000;
		$testY = ( mt_rand( 0, 1000 ) - 500 ) / 1000;
		$offX = $bestX + round( $testX * $w * $xRand * $scale );
		$offY = $bestY + round( $testY * $h * $yRand * $scale );
		$score = Stereo_diff_images( $left, $right, $w, $h, $offX, $offY );
		if ( $score < $bestDiff ) {
			echo "$leftPath: Score for $offX, $offY is $score\n";
			$bestDiff = $score;
			$bestX = $offX;
			$bestY = $offY;
		}
		$scale *= $scaleFactor;
		$iter++;
	} while ( $iter < 20 );
	return array(
		'x' => $bestX,
		'y' => $bestY,
		'd' => $bestDiff,
	);
}

function Stereo_meta_align( $leftPath, $rightPath ) {
	// Then do some random searches around the best of those
	$iter = 0;
	$scale = 1;
	$bestScore = 1;
	$xGrid = 0.1;
	$yGrid = 0.02;
	$xRand = 0.06;
	$yRand = 0.01;
	$scaleFactor = 0.98;
	do {
		$testXRand = $xRand + $xRand * ( mt_rand( 0, 1000 ) - 500 ) / 1000;
		$testYRand = $yRand + $yRand * ( mt_rand( 0, 1000 ) - 500 ) / 1000;
		$testScaleFactor = $scaleFactor + ( mt_rand( 0, 1000 ) - 500 ) / 10000;
		if ( $testScaleFactor > 1 ) {
			$testScaleFactor = 2 - $testScaleFactor;
		}
		$avg = 0;
		for( $i = 1; $i < 6; $i++ ) {
			$align = Stereo_find_best_alignment(
				str_replace( 'l', 'l' . $i, $leftPath ),
				str_replace( 'r', 'r' . $i, $rightPath ),
				$xGrid,
				$yGrid,
				$testXRand,
				$testYRand,
				$testScaleFactor
			);
			$avg+= $align['d'];
		}
		$avg = $avg / 5;
		if ( $avg < $bestScore ) {
			echo "Score for $testXRand, $testYRand, $testScaleFactor, is $avg\n";
			$bestScore = $avg;
			$xRand = $testXRand;
			$yRand = $testYRand;
			$scaleFactor = $testScaleFactor;
		}
		$iter++;
	} while ( $iter < 100 );
	echo "Score for $xRand, $yRand, $scaleFactor, is $bestScore\n";
}
