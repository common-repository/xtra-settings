<?php
add_action('wp_ajax_xtra_restore_image', 'xtra_restore_image');
add_action('wp_ajax_xtra_regenerate_thumbnails', 'xtra_regenerate_thumbnails');
add_action('wp_ajax_xtra_compress_image', 'xtra_compress_image');
add_action('wp_ajax_xtra_delete_image', 'xtra_delete_image');
add_action('wp_ajax_xtra_delete_backup', 'xtra_delete_backup');
add_action('wp_ajax_xtra_get_ipdata', 'xtra_get_ipdata');
add_action('wp_ajax_xtra_get_wpoptdata', 'xtra_get_wpoptdata');
add_action('wp_ajax_xtra_get_wpoptval', 'xtra_get_wpoptval');


function xtra_get_wpoptval() {
	xtra_verify_permission();
	if (isset($_POST['p1'])) $p1 = $_POST['p1'];
	if ($p1) $msg = get_option($p1, '');
	//if (is_array($msg)) $msg = serialize($msg);
	if (is_array($msg)) $msg = "<pre>".print_r($msg,1)."</pre>";
	$results = array( 'message' => ($msg ? $msg : "". esc_html__('N.A.', 'xtra-settings') .""));
	die( json_encode( $results ) );
}

function xtra_get_wpoptdata() {
	@set_time_limit( 900 ); // 15 minutes per image should be PLENTY
	xtra_verify_permission();
	define('SHORTINIT', true);
	
	if (isset($_POST['p1'])) $p1 = $_POST['p1'];
	if (isset($_POST['p2'])) $p2 = $_POST['p2'];
	if (isset($_POST['p3'])) $p3 = $_POST['p3'];
	if (isset($_POST['p4'])) $p4 = $_POST['p4'];
	if (isset($_POST['p5'])) $p5 = $_POST['p5'];
	
	if (true) {
		if (file_exists(ABSPATH)) 							$dir_iterator = new RecursiveDirectoryIterator(ABSPATH);
		if (file_exists(ABSPATH)) 							$xtra_iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
	}
	if (false) {
		if (file_exists(WP_CONTENT_DIR)) 					$dir_iterator = new RecursiveDirectoryIterator(WP_CONTENT_DIR);
		if (file_exists(WP_CONTENT_DIR)) 					$xtra_iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
	}
	if (false) {
		if (file_exists(WP_PLUGIN_DIR)) 					$dir_iterator1 = new RecursiveDirectoryIterator(WP_PLUGIN_DIR);
		if (file_exists(get_theme_root())) 					$dir_iterator2 = new RecursiveDirectoryIterator(get_theme_root());
		if (file_exists(WP_CONTENT_DIR ."/mu-plugins/")) 	$dir_iterator3 = new RecursiveDirectoryIterator(WP_CONTENT_DIR ."/mu-plugins/");
		$xtra_iterator = new AppendIterator();
		if (file_exists(WP_PLUGIN_DIR)) 					$xtra_iterator->append(new RecursiveIteratorIterator($dir_iterator1, RecursiveIteratorIterator::SELF_FIRST));
		if (file_exists(get_theme_root())) 					$xtra_iterator->append(new RecursiveIteratorIterator($dir_iterator2, RecursiveIteratorIterator::SELF_FIRST));
		if (file_exists(WP_CONTENT_DIR ."/mu-plugins/")) 	$xtra_iterator->append(new RecursiveIteratorIterator($dir_iterator3, RecursiveIteratorIterator::SELF_FIRST));
	}
	foreach ($xtra_iterator as $file) {
		if ($file->isFile()) {
			$filename = $file->getFilename();
			if (!preg_match('{\.(php|phps)$}i',$filename)) continue;
			$fullpath = $file->getPathname();
			//$size = $file->getSize();
			//$mtime = $file->getMTime();
			$relapath = str_ireplace(ABSPATH,"/",$fullpath);
			
			$fcstr = file_get_contents($fullpath);
			
			$found = false;
			if (strpos($fcstr,$p1)!==FALSE) $found = 1;
			if (!$found && strpos($p1,"_transient")!==FALSE) {
				$p2 = str_replace(array("_transient_","_site_transient_"),"",$p1);
				if (strpos($fcstr,$p2)!==FALSE) $found = 2;
			}

			if ($found) {
				$user = $fullpath;
				$user = str_replace(WP_PLUGIN_DIR,"",$user);
				$user = str_replace(get_theme_root(),"",$user);
				$user = str_replace(WP_CONTENT_DIR,"",$user);
				$user = preg_replace("#/?([^/]*)/?.*#","$1",$user);
				$msg = "<a href='#' title='$filename\n$relapath'><b>$user</b></a>";
				break;
			}
		}
	}
	
	$results = array( 'message' => ($msg ? $msg : "". esc_html__('N.A.', 'xtra-settings') ."")." ( ".round(timer_stop(0),2)."". esc_html__('sec', 'xtra-settings') ." )" );
	die( json_encode( $results ) );
}

function xtra_get_ipdata() {
	@set_time_limit( 900 ); // 15 minutes per image should be PLENTY
	xtra_verify_permission();
	$ip = $_POST['ip'];
	$host = @gethostbyaddr($ip);
	$results = array(
		'success'=>true,
		'ip'=> $ip,
		'message' => $host
	);

	die( json_encode( $results ) );
}


//---Delete Backup------------------------------------
function xtra_delete_image() {
	//@error_reporting( 0 ); // Don't break the JSON result
	@set_time_limit( 900 ); // 15 minutes per image should be PLENTY

	xtra_verify_permission();

	$id = $_POST['id'];
	$ids = $_POST['ids'];
	if ( !is_array($ids) ) $ids = array($id);


	global $xtra_ajax_sure;
	$xtra_ajax_sure = (int)$_POST['sure'];
	$xsure = esc_html__("SIMULATION! - ", 'xtra-settings' );
	if ($xtra_ajax_sure) $xsure = "";

	if ( is_array($ids) && count($ids) ) {
		$ok = 0;
		$nf = 0;
		$er = 0;
		$i = 0;
		$maxi = 1000;
		$fromi = (int)$_POST['fromi'];
		foreach($ids as $id) {
			$i++;
			if ($i < $fromi) continue;
			// --start-proc--
			$res = xtra_delete_image_do($id);
			// --end-proc--
			if ($res['delimageOK']=="OK") $ok++;
			if ($res['delimageOK']=="ERROR") $er++;
			if ($res['delimageOK']=="NO-FILE") $nf++;
			if ($i >= $fromi+$maxi-1) break;
		}
		$results = array(
			'success'=>true,
			'id'=> $id,
			'fromi'=> $i,
			'message' => $xsure.sprintf( esc_html__('Delete Image Files','xtra-settings').' (Bulk): '.esc_html__('OK: %s, NO-FILE: %s, ERROR: %s','xtra-settings') ,$ok,$nf,$er )
		);
	}

	die( json_encode( $results ) );
}

function xtra_delete_image_do($source_path) {
	global $xtra_ajax_sure;
	$upload_dir = XTRA_UPLOAD_DIR;
	$backupdir = $upload_dir ."/xtra-img-backup";
	$filename = basename($source_path);
	$reladir = str_replace(array($upload_dir."/","/".$filename),"",$source_path);

	if ( !file_exists($source_path) )
		return array(
			'delimageOK'		=>	"NO-FILE",
			'filename'			=>	$filename,
		);
	if ($xtra_ajax_sure) $res = unlink($source_path);
	else $res = true; //SIMULATION

	return array(
		'delimageOK'		=>	$res ? "OK" : "ERROR",
		'filename'			=>	$filename,
	);
}





//---Delete Backup------------------------------------
function xtra_delete_backup() {
	//@error_reporting( 0 ); // Don't break the JSON result
	@set_time_limit( 900 ); // 15 minutes per image should be PLENTY

	xtra_verify_permission();

	$id = $_POST['id'];
	$ids = $_POST['ids'];
	if ( !is_array($ids) ) $ids = array($id);


	global $xtra_ajax_sure;
	$xtra_ajax_sure = (int)$_POST['sure'];
	$xsure = esc_html__("SIMULATION! - ", 'xtra-settings' );
	if ($xtra_ajax_sure) $xsure = "";

	if ( is_array($ids) && count($ids) ) {
		$ok = 0;
		$nf = 0;
		$er = 0;
		$i = 0;
		$maxi = 100;
		$fromi = (int)$_POST['fromi'];
		foreach($ids as $id) {
			$i++;
			if ($i < $fromi) continue;
			// --start-proc--
			$res = xtra_delete_backup_do($id);
			// --end-proc--
			if ($res['delbackupOK']=="OK") $ok++;
			if ($res['delbackupOK']=="ERROR") $er++;
			if ($res['delbackupOK']=="NO-FILE") $nf++;
			if ($i >= $fromi+$maxi-1) break;
		}
		$results = array(
			'success'=>true,
			'id'=> $id,
			'fromi'=> $i,
			'message' => $xsure.sprintf( esc_html__('Delete Backup Files','xtra-settings').' (Bulk): '.esc_html__('OK: %s, NO-FILE: %s, ERROR: %s','xtra-settings') ,$ok,$nf,$er )
		);
		//if ($xtra_ajax_sure)
			xtra_remove_empty_subfolders(XTRA_UPLOAD_DIR . "/xtra-img-backup");
	}

	die( json_encode( $results ) );
}

function xtra_delete_backup_do($source_path) {
	global $xtra_ajax_sure;
	$upload_dir = XTRA_UPLOAD_DIR;
	$backupdir = $upload_dir ."/xtra-img-backup";
	$filename = basename($source_path);
	$reladir = str_replace(array($upload_dir."/","/".$filename),"",$source_path);

	if ( !file_exists($backupdir."/".$reladir."/".$filename) )
		return array(
			'delbackupOK'		=>	"NO-FILE",
			'filename'			=>	$filename,
			'reladir'			=>	$reladir,
			'delbackup_path'	=>	$backupdir."/".$reladir."/".$filename,
		);
	if ($xtra_ajax_sure) $res = unlink($backupdir."/".$reladir."/".$filename);
	else $res = true; //SIMULATION

	return array(
		'delbackupOK'		=>	$res ? "OK" : "ERROR",
		'filename'			=>	$filename,
		'reladir'			=>	$reladir,
		'delbackup_path'	=>	$backupdir."/".$reladir."/".$filename,
	);
}





//---Restore------------------------------------

function xtra_restore_image() {
	//@error_reporting( 0 ); // Don't break the JSON result
	@set_time_limit( 900 ); // 15 minutes per image should be PLENTY

	xtra_verify_permission();

	$id = $_POST['id'];
	$ids = $_POST['ids'];
	if ( !is_array($ids) ) $ids = array($id);

	global $xtra_ajax_sure;
	$xtra_ajax_sure = (int)$_POST['sure'];
	$xsure = esc_html__("SIMULATION! - ", 'xtra-settings' );
	if ($xtra_ajax_sure) $xsure = "";

	if ( is_array($ids) && count($ids) ) {
		$ok = 0;
		$nf = 0;
		$er = 0;
		$i = 0;
		$maxi = 100;
		$fromi = (int)$_POST['fromi'];
		foreach($ids as $id) {
			$i++;
			if ($i < $fromi) continue;
			// --start-proc--
			$res = xtra_restore_image_do($id);
			// --end-proc--
			if ($res['restoreOK']=="OK") $ok++;
			if ($res['restoreOK']=="ERROR") $er++;
			if ($res['restoreOK']=="NO-FILE") $nf++;
			if ($i >= $fromi+$maxi-1) break;
		}
		$results = array(
			'success'=>true,
			'id'=> $id,
			'fromi'=> $i,
			'message' => $xsure.sprintf( esc_html__('Restore','xtra-settings').' (Bulk): '.esc_html__('OK: %s, NO-FILE: %s, ERROR: %s','xtra-settings') ,$ok,$nf,$er )
		);
	}

	die( json_encode( $results ) );
}

function xtra_restore_image_do($source_path) {
	global $xtra_ajax_sure;
	$upload_dir = XTRA_UPLOAD_DIR;
	$backupdir = $upload_dir ."/xtra-img-backup";
	$filename = basename($source_path);
	$reladir = str_replace(array($upload_dir."/","/".$filename),"",$source_path);


		if ( $xtra_ajax_sure && !file_exists( $upload_dir ) ) wp_mkdir_p( $upload_dir );
		if ( $xtra_ajax_sure && !file_exists( $upload_dir."/".$reladir ) ) wp_mkdir_p( $upload_dir."/".$reladir );

		if ( ! is_writable( dirname($source_path) ) ) {
			die( json_encode( array( 'success' => false, 'message' => sprintf( esc_html__( '%s is not writable', 'xtra-settings' ), dirname($source_path) ) ) ) );
		}
		if ( !file_exists($backupdir."/".$reladir."/".$filename) )
			return array(
				'restoreOK'		=>	"NO-FILE",
				'filename'		=>	$filename,
				'reladir'		=>	$reladir,
				'restore_path'	=>	$backupdir."/".$reladir."/".$filename,
			);
		if ($xtra_ajax_sure) $res = copy($backupdir."/".$reladir."/".$filename,$source_path);
		else $res = true; //SIMULATION

		return array(
			'restoreOK'		=>	$res ? "OK" : "ERROR",
			'filename'		=>	$filename,
			'reladir'		=>	$reladir,
			'from_path'		=>	$backupdir."/".$reladir."/".$filename,
			'restore_path'	=>	$source_path,
		);

}

function xtra_backup_image($source_path) {
	global $xtra_ajax_sure;
	$upload_dir = XTRA_UPLOAD_DIR;
	$backupdir = $upload_dir ."/xtra-img-backup";
	$filename = basename($source_path);
	$reladir = str_replace(array($upload_dir."/","/".$filename),"",$source_path);

	if ( !file_exists( $backupdir."/".$reladir."/".$filename ) ) {

		if ( !file_exists( $backupdir ) ) wp_mkdir_p( $backupdir );
		if ( !file_exists( $backupdir."/".$reladir ) ) wp_mkdir_p( $backupdir."/".$reladir );

		if ( ! is_writable( $backupdir."/".$reladir ) ) {
			die( json_encode( array( 'success' => false, 'message' => sprintf( esc_html__( '%s is not writable', 'xtra-settings' ), $backupdir."/".$reladir ) ) ) );
		}
		if ($xtra_ajax_sure) $res = copy($source_path,$backupdir."/".$reladir."/".$filename);
		else $res = true; //SIMULATION

		return array(
			'backupOK'		=>	$res ? "OK" : "ERROR",
			'backup_path'	=>	$backupdir."/".$reladir."/".$filename,
		);
	}
	return array(
		'backupOK'		=>	"EXIST",
		'backup_path'	=>	$backupdir."/".$reladir."/".$filename,
	);
}





//---Regenerate thumbnails------------------------------------
function xtra_regenerate_thumbnails() {
	//@error_reporting( 0 ); // Don't break the JSON result
	@set_time_limit( 900 ); // 15 minutes per image should be PLENTY

	xtra_verify_permission();

	$id = $_POST['id'];
	$ids = $_POST['ids'];
	if ( !is_array($ids) ) $ids = array($id);

	global $xtra_ajax_sure;
	$xtra_ajax_sure = (int)$_POST['sure'];
	$xsure = esc_html__("SIMULATION! - ", 'xtra-settings' );
	if ($xtra_ajax_sure) $xsure = "";

	if ( is_array($ids) && count($ids) ) {
		$ok = 0;
		$er = 0;
		$i = 0;
		$maxi = 5;
		$fromi = (int)$_POST['fromi'];
		foreach($ids as $id) {
			$i++;
			if ($i < $fromi) continue;
			// --start-proc--
			$res = xtra_regenerate_thumbnails_do($id);
			// --end-proc--
			if ($res['regenOK']=="OK") $ok++;
			if ($res['regenOK']=="ERROR") {
				$er++;
				$ers .= '<br/>&nbsp;&nbsp;&nbsp; '.$i.' >> '. esc_html__('ERROR', 'xtra-settings') .': '.$res['regenErrMsg'].' (ID:'.$id.') '.$res['filepath'].'';
			}
			if ($i >= $fromi+$maxi-1) break;
		}
		$results = array(
			'success'=>true,
			'id'=> $id,
			'fromi'=> $i,
			'message' => $xsure.sprintf( esc_html__('Regenerate Thumbs','xtra-settings').' (Bulk): '.esc_html__('OK: %s, ERROR: %s','xtra-settings') ,$ok,$er ).$ers
		);
	}

	die( json_encode( $results ) );
}

function xtra_regenerate_thumbnails_do( $attachment_id )
{
	global $xtra_ajax_sure;
	$err = "";

	// We only want to look at image attachments
	if ( !$attachment_id )
		return array(
			'regenOK'		=>	"ERROR",
			'regenErrMsg'	=>	"". esc_html__('Empty attachment ID.', 'xtra-settings') ."",
			'filepath'		=>	$filepath,
		);

	if ( !wp_attachment_is_image($attachment_id) )
		return array(
			'regenOK'		=>	"ERROR",
			'regenErrMsg'	=>	"". sprintf(esc_html__('%s is not an image attachment.', 'xtra-settings') ,$attachment_id) ."",
			'filepath'		=>	$filepath,
		);

	$filepath = get_attached_file( $attachment_id, true );
	if ( false === $filepath || ! file_exists( $filepath ) )
		return array(
			'regenOK'		=>	"ERROR",
			'regenErrMsg'	=>	"". esc_html__('The originally uploaded image file cannot be found.', 'xtra-settings') ."",
			'filepath'		=>	$filepath,
		);

	if ($xtra_ajax_sure) $metadata = wp_generate_attachment_metadata( $attachment_id, $filepath );
	else $metadata = 1; //SIMULATION

	// Was there an error?
	if ( is_wp_error( $metadata ) )
		return array(
			'regenOK'		=>	"ERROR",
			'regenErrMsg'	=>	"". esc_html__('metadata ERROR', 'xtra-settings') .": ".$metadata->get_error_message(),
			'filepath'		=>	$filepath,
		);
	if ( empty( $metadata ) )
		return array(
			'regenOK'		=>	"ERROR",
			'regenErrMsg'	=>	"". esc_html__('Empty metadata.', 'xtra-settings') ."",
			'filepath'		=>	$filepath,
		);

	// If this fails, then it just means that nothing was changed (old value == new value)
	if ($xtra_ajax_sure) wp_update_attachment_metadata( $attachment_id, $metadata );

	return array(
		'regenOK'		=>	$err ? "ERROR" : "OK",
		'regenErrMsg'	=>	$err ? $err : "",
		'filepath'		=>	$filepath,
	);
}


//---Compress------------------------------------

function xtra_compress_image()
{
	//@error_reporting( 0 ); // Don't break the JSON result
	@set_time_limit( 900 ); // 15 minutes per image should be PLENTY

	xtra_verify_permission();

//var_dump($_POST);

	$id = $_POST['id'];
	$ids = $_POST['ids'];
	if ( !is_array($ids) ) $ids = array($id);

	$xMethod = (int)$_POST['xMethod'];

	global $xtra_ajax;
	$xtra_ajax = 1;
	global $xtra_ajax_sure;
	$xtra_ajax_sure = (int)$_POST['sure'];
	$xsure = esc_html__("SIMULATION! - ", 'xtra-settings' );
	if ($xtra_ajax_sure) $xsure = "";

	$compQ = (int)$_POST['compQ'];
	if (!$compQ || $compQ>100 || $compQ<1) $compQ = get_optionXTRA('xtra_custom_jpeg_quality_num',82);

	$maxW = (int)$_POST['maxW'];
	if (!$maxW || $maxW>100000 || $maxW<1) $maxW = null;
	$maxH = (int)$_POST['maxH'];
	if (!$maxH || $maxH>100000 || $maxH<1) $maxH = null;

	$cgif = (int)$_POST['cgif'];
	$cbmp = (int)$_POST['cbmp'];
	$cpng = (int)$_POST['cpng'];

	if ( is_array($ids) && count($ids) ) {
		$ok = 0;
		$er = 0;
		$rs = 0;
		$i = 0;
		$maxi = 20;
		$fromi = (int)$_POST['fromi'];
		foreach($ids as $id) {
			$i++;
			if ($i < $fromi) continue;
			$filename = basename($id);
			$reladir = str_replace(array(XTRA_UPLOAD_DIR."/","/".$filename),"",$id);
			$relafile = $reladir."/".$filename;
			// --start-proc--
			$terr = "";
			if ( ! is_writable( $id ) ) {
				//die( json_encode( array( 'success' => false, 'message' => sprintf( esc_html__( '%s is not writable', 'xtra-settings' ), $id ) ) ) );
				$terr = sprintf( esc_html__( '%s is not writable', 'xtra-settings' ), $id );
			}

			unset($info);
			$info = getimagesize($id);
			$imgW = $info[0];
			$imgH = $info[1];
			unset($info);
			$imgmem = $imgW * $imgH * 4; // 4 bytes per pixel
			$mem = xtra_mem_usage($imgmem,1.5);
			if ( $mem['imgmem'] >= $mem['avamem'] ) {
				if ($i > $fromi) {
					die( json_encode( array(
			'fromi'=> $i-1,
			'message' => $xsure.sprintf( esc_html__('Compress','xtra-settings').' (Bulk): '.esc_html__('OK: %s, RESIZED: %s, ERROR: %s','xtra-settings') ,$ok,$rs,$er ).$ers.
				"<br/>$i >> ". esc_html__('Too large image', 'xtra-settings') ." $relafile ($imgW x $imgH = ".$mem['imgmemMB']."M) - ". esc_html__('low memory', 'xtra-settings') ." (".$mem['maxmemMB']."M ". esc_html__('allowed', 'xtra-settings') ." - ".$mem['curmemMB']."M ". esc_html__('used', 'xtra-settings') ." = ".$mem['avamemMB']."M ". esc_html__('available', 'xtra-settings') .") ". esc_html__('Trying once again.', 'xtra-settings') ."",
					) ) );
				}
				else {
					$terr = "". esc_html__('Too large image', 'xtra-settings') ." ($imgW x $imgH = ".$mem['imgmemMB']."M) - ". esc_html__('low memory', 'xtra-settings') ." (".$mem['maxmemMB']."M ". esc_html__('allowed', 'xtra-settings') ." - ".$mem['curmemMB']."M ". esc_html__('used', 'xtra-settings') ." = ".$mem['avamemMB']."M ". esc_html__('available', 'xtra-settings') .")";
				}
			}
			$callW = null;
			if ($imgW > $maxW) $callW = $maxW;
			$callH = null;
			if ($imgH > $maxH) $callH = $maxH;
			if ($terr) {
				$res = array('imageOK'=>"ERROR: ".$terr);
			}
			elseif ($xMethod==1) {
				$res = xtra_compressImage($id, $id, $compQ, $callW, $callH, $cgif, $cbmp, $cpng); // $maxW, $maxH does not work yet
				$method = "GD";
			}
			elseif ($xMethod==2) {
				$res = xtra_wpeditor_image_resize($id, $compQ, $callW, $callH);
				$method = "WP";
			}
			else {
				$res = array('imageOK'=>'ERROR: '. esc_html__('Invalid Method', 'xtra-settings') .'!!!');
				$method = "";
			}
			// --end-proc--
			if (xtra_instr($res['imageOK'],"OK")) $ok++;
			if (xtra_instr($res['imageOK'],"ERROR")) {
				$er++;
				$ers .= '<br/>&nbsp;&nbsp;&nbsp; - '.$res['imageOK'].' '.esc_html__('Try to compress alone.', 'xtra-settings').' '.$relafile.'';
			}
			if (xtra_instr($res['imageOK'],"RESIZED")) $rs++;
			if ($i >= $fromi+$maxi-1) break;
		}
		$results = array(
			'fromi'=> $i,
			'message' => $xsure.sprintf( esc_html__('Compress','xtra-settings').' (Bulk): '.esc_html__('OK: %s, RESIZED: %s, ERROR: %s','xtra-settings') ,$ok,$rs,$er ).$ers
		);
	}

	die( json_encode( $results ) );
}

function xtra_mem_usage($imgmem,$xxx=1.5) {
	$maxmem = preg_replace("#(\d+).*#","$1",ini_get('memory_limit'));
	$maxmem = $maxmem*1024*1024;
	$curmem = $xxx * memory_get_peak_usage(true);
	$avamem = $maxmem - $curmem;
	if ( $imgmem + $curmem >= $maxmem ) {
		ini_set('memory_limit',(2*$maxmem)."M");
		$maxmem = preg_replace("#(\d+).*#","$1",ini_get('memory_limit'));
		$maxmem = $maxmem*1024*1024;
		$curmem = $xxx * memory_get_peak_usage(true);
		$avamem = $maxmem - $curmem;
	}
	return array(
		'imgmem'		=> $imgmem,
		'maxmem'		=> $maxmem,
		'curmem'		=> $curmem,
		'avamem'		=> $avamem,
		'imgmemMB'		=> round($imgmem/1024/1024),
		'maxmemMB'		=> round($maxmem/1024/1024),
		'curmemMB'		=> round($curmem/1024/1024),
		'avamemMB'		=> round($avamem/1024/1024),
	);
}

function xtra_compressImage($source_path, $destination_path, $quality=82, $max_w=null, $max_h=null, $convert_gif_to_jpg=false, $convert_bmp_to_jpg=false, $convert_png_to_jpg=false) {
	global $xtra_ajax_sure;

	// TODO: max_w and max_h not working yet
	$max_w=null;
	$max_h=null;
	// TODO: conversions will break linked images URL - disable
	$convert_gif_to_jpg = false;
	$convert_bmp_to_jpg = false;
	$convert_png_to_jpg = false;

	//$quality :: 0 - 100

	$fsiz1 = filesize($source_path);

	$backup = xtra_backup_image($source_path);

	if ( $backup['backupOK']!="ERROR" ) {
		if ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/JPEG') {
			$image = imagecreatefromjpeg($source_path);
			//quality ranges from 0 (worst quality, smaller file) to 100 (best quality, biggest file). The default is the default IJG quality value (about 75).
			if ($xtra_ajax_sure) $res2 = imagejpeg($image, $destination_path, $quality);
			imagedestroy($image);
		}
		elseif ($info['mime'] == 'image/gif' || $info['mime'] == 'image/GIF') {
			if ($convert_gif_to_jpg) {
				$image = imagecreatefromgif($source_path);
				$destination_path = xtra_unique_name(str_ireplace(".gif",".jpg",$destination_path)); //unique name???
				if ($xtra_ajax_sure) $res2 = imagejpeg($image, $destination_path, $quality);
				imagedestroy($image);
			}
		}
		elseif ($info['mime'] == 'image/bmp' || $info['mime'] == 'image/BMP') {
			if ($convert_bmp_to_jpg) {
				$image = imagecreatefrombmp($source_path);
				$destination_path = xtra_unique_name(str_ireplace(".bmp",".jpg",$destination_path)); //unique name???
				if ($xtra_ajax_sure) $res2 = imagejpeg($image, $destination_path, $quality);
				imagedestroy($image);
			}
		}
		elseif ($info['mime'] == 'image/png' || $info['mime'] == 'image/PNG') {
			if ($convert_png_to_jpg) {
				$image = imagecreatefrompng($source_path);
				$destination_path = xtra_unique_name(str_ireplace(".png",".jpg",$destination_path)); //unique name???
				if ($xtra_ajax_sure) $res2 = imagejpeg($image, $destination_path, $quality);
				imagedestroy($image);
			}
			else {
				$image = imagecreatefrompng($source_path);
				imageAlphaBlending($image, true);
				imageSaveAlpha($image, true);
				$png_quality = 9 - (($quality * 9 ) / 100 );
				if ($xtra_ajax_sure) $res2 = imagePng($image, $destination_path, $png_quality);//Compression level: from 0 (no compression) to 9.
				imagedestroy($image);
			}
		}
	}
	$fsiz2 = filesize($destination_path);
	$savingPRC = 100-round($fsiz2/$fsiz1*100)."%";
	$savingKB = round(($fsiz1-$fsiz2)/1024)." KB";

	return array_merge($backup,array(
		'path'			=>	$destination_path,
		'name'			=>	$destination_path==$source_path ? basename($destination_path) : basename($source_path)." ==> ".basename($destination_path),
		'imageOK'		=>	$res2 ? "OK" : "ERROR",
		'compressPRC'	=>	$quality,
		'savingPRC'		=>	$savingPRC,
		'savingKB'		=>	$savingKB,
		'wh'			=>	$max_w."x".$max_h,
	));
}

function xtra_unique_name($name_or_path,$dir="") {
	$name = $name_or_path;
	if ( !$dir && strpos($name_or_path,"/")!==FALSE ) {
		$name = basename($name_or_path);
		$dir = str_replace("/".$name,"",$name_or_path);
	}

	$chks = explode(".",$name);
	$ext = $chks[count((array)$chks)-1];
	$name_no_ext = str_replace(".".$ext,"",$name);

	while(file_exists($dir."/".$name)) {
		$name = $name_no_ext."-xtra".rand(1,10000).".".$ext;
	}
	return $dir."/".$name;
}



function xtra_verify_permission()
{
	if ( ! current_user_can( 'manage_options' ) ) {
		die( json_encode( array( 'success' => false, 'message' => esc_html__( 'Administrator permission is required', 'xtra-settings' ) ) ) );
	}
	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'xtra_ajax_nonce' ) ) {
		die( json_encode( array( 'success' => false, 'message' => esc_html__( 'Access token has expired, please reload the page.', 'xtra-settings' ) ) ) );
	}
}






/**
 * Replacement for deprecated image_resize function
 * @param string $file Image file path.
 * @param int $max_w Maximum width to resize to.
 * @param int $max_h Maximum height to resize to.
 * @param bool $crop Optional. Whether to crop image or resize.
 * @param string $suffix Optional. File suffix.
 * @param string $dest_path Optional. New image file path.
 * @param int $jpeg_quality Optional, default is 90. Image quality percentage.
 * @return mixed WP_Error on failure. String with new destination path.
 */
function xtra_wpeditor_image_resize( $file, $jpeg_quality = 82, $max_w=null, $max_h=null, $crop = false, $suffix = null, $dest_path = null ) {
	global $xtra_ajax_sure;
	global $xtra_ajax;
	if ( function_exists( 'wp_get_image_editor' ) ) {
		// WP 3.5 and up use the image editor

		$fsiz1 = filesize($file);
		if ($xtra_ajax) $backup = xtra_backup_image($file);
		else $backup['backupOK'] = 1;

		if ( $backup['backupOK']!="ERROR" ) {
			$editor = wp_get_image_editor( $file );
			if (!$xtra_ajax && is_wp_error( $editor ) ) return $editor;
			$editor->set_quality( $jpeg_quality );

			$ftype = pathinfo( $file, PATHINFO_EXTENSION );

			// try to correct for auto-rotation if the info is available
			if (function_exists('exif_read_data') && ($ftype == 'jpg' || $ftype == 'jpeg') ) {
				$exif = @exif_read_data($file);
				$orientation = is_array($exif) && array_key_exists('Orientation', $exif) ? $exif['Orientation'] : 0;
				switch($orientation) {
					case 3:
						$editor->rotate(180);
						break;
					case 6:
						$editor->rotate(-90);
						$sav = $max_w;
						$max_w = $max_h;
						$max_h = $sav;
						break;
					case 8:
						$editor->rotate(90);
						$sav = $max_w;
						$max_w = $max_h;
						$max_h = $sav;
						break;
				}
			}

			$resiz = 0;
			if ($max_w || $max_h) {
				$resized = $editor->resize( $max_w, $max_h, $crop );
				$resiz = 1;
			}
			if (!$xtra_ajax && is_wp_error( $resized ) ) return $resized;

			if ($xtra_ajax) {
				$dest_file = $file;
				if ($xtra_ajax_sure) $saved = $editor->save( $dest_file );
			}
			else {
				$dest_file = $editor->generate_filename( $suffix, $dest_path );
				// FIX: make sure that the destination file does not exist.  this fixes
				// an issue during bulk resize where one of the optimized media filenames may get
				// used as the temporary file, which causes it to be deleted.
				while (file_exists($dest_file)) {
					$dest_file = $editor->generate_filename('TMP', $dest_path );
				}
				$saved = $editor->save( $dest_file );
			}
			//$editor->__destruct(); // does not help to lower memory usage.

			if (!$xtra_ajax && is_wp_error( $saved ) ) return $saved;

			if (!$xtra_ajax) return $dest_file;
		}
		if ($xtra_ajax) {
			$fsiz2 = filesize($dest_file);
			$savingPRC = 100-round($fsiz2/$fsiz1*100)."%";
			$savingKB = round(($fsiz1-$fsiz2)/1024)." KB";
			return array_merge($backup,array(
				'path'			=>	$dest_file,
				'name'			=>	$dest_file==$file ? basename($dest_file) : basename($file)." ==> ".basename($dest_file),
				'imageOK'		=>	is_wp_error( $saved ) ? "ERROR" : "OK".($resiz ? " RESIZED" : ""),
				'compressPRC'	=>	$jpeg_quality,
				'savingPRC'		=>	$savingPRC,
				'savingKB'		=>	$savingKB,
				'wh'			=>	$max_w."x".$max_h,
			));
		}
	}
	return false;
}


/**
 * imagecreatefrombmp converts a bmp to an image resource
 *
 * @author http://www.programmierer-forum.de/function-imagecreatefrombmp-laeuft-mit-allen-bitraten-t143137.htm
 */

if (!function_exists('imagecreatefrombmp')) {

	function imagecreatefrombmp($filename) {
		// version 1.00
		if (!($fh = fopen($filename, 'rb'))) {
			trigger_error( sprintf( esc_html__( 'imagecreatefrombmp: Can not open %s!','xtra-settings') , $filename ), E_USER_WARNING );
			return false;
		}
		// read file header
		$meta = unpack('vtype/Vfilesize/Vreserved/Voffset', fread( $fh, 14 ) );
		// check for bitmap
		if ($meta['type'] != 19778) {
			trigger_error( sprintf( esc_html__( 'imagecreatefrombmp: %s is not a bitmap!', 'xtra-settings' ), $filename ), E_USER_WARNING );
			return false;
		}
		// read image header
		$meta += unpack('Vheadersize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vcolors/Vimportant', fread($fh, 40));
		// read additional 16bit header
		if ($meta['bits'] == 16) {
			$meta += unpack('VrMask/VgMask/VbMask', fread($fh, 12));
		}
		// set bytes and padding
		$meta['bytes'] = $meta['bits'] / 8;
		$meta['decal'] = 4 - (4 * (($meta['width'] * $meta['bytes'] / 4)- floor($meta['width'] * $meta['bytes'] / 4)));
		if ($meta['decal'] == 4) {
			$meta['decal'] = 0;
		}
		// obtain imagesize
		if ($meta['imagesize'] < 1) {
			$meta['imagesize'] = $meta['filesize'] - $meta['offset'];
			// in rare cases filesize is equal to offset so we need to read physical size
			if ($meta['imagesize'] < 1) {
				$meta['imagesize'] = @filesize($filename) - $meta['offset'];
				if ($meta['imagesize'] < 1) {
					trigger_error( sprintf( esc_html__( 'imagecreatefrombmp: Cannot obtain filesize of %s !', 'xtra-settings' ), $filename ), E_USER_WARNING );
					return false;
				}
			}
		}
		// calculate colors
		$meta['colors'] = !$meta['colors'] ? pow(2, $meta['bits']) : $meta['colors'];
		// read color palette
		$palette = array();
		if ($meta['bits'] < 16) {
			$palette = unpack('l' . $meta['colors'], fread($fh, $meta['colors'] * 4));
			// in rare cases the color value is signed
			if ($palette[1] < 0) {
				foreach ($palette as $i => $color) {
					$palette[$i] = $color + 16777216;
				}
			}
		}
		// create gd image
		$im = imagecreatetruecolor($meta['width'], $meta['height']);
		$data = fread($fh, $meta['imagesize']);
		$p = 0;
		$vide = chr(0);
		$y = $meta['height'] - 1;
		$error = 'imagecreatefrombmp: ' . $filename . ' '. esc_html__('has not enough data', 'xtra-settings') .'!';
		// loop through the image data beginning with the lower left corner
		while ($y >= 0) {
			$x = 0;
			while ($x < $meta['width']) {
				switch ($meta['bits']) {
					case 32:
					case 24:
						if (!($part = substr($data, $p, 3))) {
							trigger_error($error, E_USER_WARNING);
							return $im;
						}
						$color = unpack('V', $part . $vide);
						break;
					case 16:
						if (!($part = substr($data, $p, 2))) {
							trigger_error($error, E_USER_WARNING);
							return $im;
						}
						$color = unpack('v', $part);
						$color[1] = (($color[1] & 0xf800) >> 8) * 65536 + (($color[1] & 0x07e0) >> 3) * 256 + (($color[1] & 0x001f) << 3);
						break;
					case 8:
						$color = unpack('n', $vide . substr($data, $p, 1));
						$color[1] = $palette[ $color[1] + 1 ];
						break;
					case 4:
						$color = unpack('n', $vide . substr($data, floor($p), 1));
						$color[1] = ($p * 2) % 2 == 0 ? $color[1] >> 4 : $color[1] & 0x0F;
						$color[1] = $palette[ $color[1] + 1 ];
						break;
					case 1:
						$color = unpack('n', $vide . substr($data, floor($p), 1));
						switch (($p * 8) % 8) {
							case 0:
								$color[1] = $color[1] >> 7;
								break;
							case 1:
								$color[1] = ($color[1] & 0x40) >> 6;
								break;
							case 2:
								$color[1] = ($color[1] & 0x20) >> 5;
								break;
							case 3:
								$color[1] = ($color[1] & 0x10) >> 4;
								break;
							case 4:
								$color[1] = ($color[1] & 0x8) >> 3;
								break;
							case 5:
								$color[1] = ($color[1] & 0x4) >> 2;
								break;
							case 6:
								$color[1] = ($color[1] & 0x2) >> 1;
								break;
							case 7:
								$color[1] = ($color[1] & 0x1);
								break;
						}
						$color[1] = $palette[ $color[1] + 1 ];
						break;
					default:
						trigger_error( sprintf( esc_html__( 'imagecreatefrombmp: %s has %d bits and this is not supported!', 'xtra-settings' ), $filename, $meta['bits'] ), E_USER_WARNING );
						return false;
				}
				imagesetpixel($im, $x, $y, $color[1]);
				$x++;
				$p += $meta['bytes'];
			}
			$y--;
			$p += $meta['decal'];
		}
		fclose($fh);
		return $im;
	}
}









?>