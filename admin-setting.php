<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

global $xtra_do_memlog;
if ($xtra_do_memlog) xtra_memlog("---> start admin-settings.php");


function xtra_make_html() {
global $xtra_seltab, $xtra_tabs;
global $xtra_error_level_string;
$xtra_sets = xtra_make_dataset();
$xtra_tabcols = xtra_get_tab_colors();

	$html = '';
	$oldset = '';
	$ixx = 0;
	$ops = 0;
	$sec = 0;
	$tab = 0;
	$xtra_tabs = array();
	$oldtab = "";
	foreach ($xtra_sets as $setname => $set) {
		//xtra_memlog($setname);
		if (stripos($set['section'],'apache')!==FALSE && !xtra_is_apache()) continue;
		$ixx++;
		$stra = explode("-",$set['label']);
		$pre_label = isset($stra[0]) ? $stra[0] : "";
		$post_label = isset($stra[1]) ? $stra[1] : "";
		for ($j=2;$j<count((array)$stra);$j++) $post_label .= "-".$stra[$j];
		$txtw = '85%';
		if ( strlen($pre_label.$post_label)>12 ) $txtw = '80%';
		if ( strlen($pre_label.$post_label)>25 ) $txtw = '65%';
		if ( strlen($pre_label.$post_label)>35 ) $txtw = '50%';
		if ( str_ireplace(array('_num','_pnum','logged_in_for_text'),'',$setname)!=$setname ) $txtw = '70px';
		if ( str_ireplace(array('resize_upload','xtra_hits_enable_pnum'),'',$setname)!=$setname ) $txtw = '80px';
		if ( str_ireplace(array('twitter_metas_text','xtra_hits_send_mail_num'),'',$setname)!=$setname ) $txtw = '120px';
		if ( str_ireplace(array('xtra_share_buttons_text'),'',$setname)!=$setname ) $txtw = '65%';
		$curval = get_optionXTRA($setname,$set['default']);
		$resetbutt = "<a class='".((substr($setname,-6) == "_texta") ? "xtra_restore_texta" : "xtra_restore_input")." dashicons dashicons-image-rotate' title='".esc_html__('Reset Default', 'xtra-settings') .":\n".$set['default']."' onclick='jQuery(this).prevAll().first().val(\"".str_replace(array('\\',"\n"),array('\\\\',"\\n"),$set['default'])."\");'></a>";


		if ($set['tab'] != $oldtab) {
			//.. Tab
			$oldtab = $set['tab'];
			$tab = array_search($set['tab'],$xtra_tabs);
			if ($tab===false) {
				$xtra_tabs[] = $set['tab'];
				$tab = (string)(count((array)$xtra_tabs)-1);
			}
		}
		if ($set['section'] != $oldset) {
			//.. Section
			$sec++;
			$ops = 0;
			if ($ixx>1) $html .= '</table></div>';
			$addi = "";
			if (stripos($set['section'],'apache')!==FALSE) $addi = '<span class="m-left-50 small">'.'('. esc_html__('Only for Apache severs', 'xtra-settings') .') &nbsp;&nbsp;&nbsp;'. esc_html__('Your server is', 'xtra-settings') .': '.$_SERVER["SERVER_SOFTWARE"].'</span>';
			$html .= '<div class="xtra_box xtra_tabbes xtra_tabbeID_'.$tab.' '.(($xtra_seltab===$tab || $xtra_seltab==="*")?"active":"").'">';
			// Section - icons
			$dashicons = xtra_get_section_icons();
			$icn = "";
			if ($dicn = $dashicons[$set['section']]) $icn = ' class="icbefore dashicons-'.$dicn.'"';
			if ( get_optionXTRA( 'xtra_opt_show_opsnum' ) )
				$html .= '<h2 '.$icn.'><span class="secnum">'.$sec.".</span> ".$set['section'].$addi.'</h2>';
			else
				$html .= '<h2 '.$icn.'>'.$set['section'].$addi.'</h2>';
			$html .= '<table class="wp-list-table widefat fixed striped xtra">';
			$oldset = $set['section'];
		}

		//if ( substr($setname,-6) == "_title" || substr($setname,-5) == "_text" || substr($setname,-5) == "_pnum" || substr($setname,-4) == "_num" ) {
		if ( preg_match("#(_title|_text|_pnum|_num)\d*$#i",$setname) ) {
			//.. Extra text - input
			if ( xtra_check_files_writability($set['label']) ) {
				if ($setname=='xtra_debug_text') {
					if ($xtra_error_level_string)
						$html .= ' &nbsp;&nbsp;&nbsp;'.$pre_label.' '.esc_html($xtra_error_level_string).''.$post_label.'';
				}
				else if ($setname=='xtra_redir_bots_text') {
					$html .= ' &nbsp;&nbsp;&nbsp;<label for="'.$setname.'">'.$pre_label.'<input type="text" readonly="readonly" style="width:'.$txtw.';" name="'.$setname.'" id="'.$setname.'" value="'.esc_html($curval).'" />'.$post_label.'</label>';
				}
				else if ($setname=='xtra_twitter_metas_text') {
					$html .= ' &nbsp;&nbsp;&nbsp;<label for="'.$setname.'">'.$pre_label.'</label><span style="white-space:nowrap;font-size:150%;">@<input type="text" style="width:'.$txtw.';" name="'.$setname.'" id="'.$setname.'" value="'.esc_html($curval).'" />'.$resetbutt.'</span>';
				}
				else {
					$html .= ' &nbsp;&nbsp;&nbsp;<label for="'.$setname.'">'.$pre_label.'<input type="text" style="width:'.$txtw.';" name="'.$setname.'" id="'.$setname.'" value="'.esc_html($curval).'" />'.$resetbutt.''.$post_label.'</label>';
				}
			}
		}
		else if ( substr($setname,-6) == "_texta" ) {
			//.. Extra textarea
			if ( xtra_check_files_writability($set['label']) ) {
				$html .= ' &nbsp;&nbsp;&nbsp;<label for="'.$setname.'">'.$pre_label.':<br><textarea class="extra-texta" name="'.$setname.'" id="'.$setname.'" >'.esc_html($curval).'</textarea>'.$resetbutt.'<br>'.$post_label.'</label>';
			}
		}
		else if ( substr($setname,-4) == "_cbx" ) {
			//.. Extra checkbox
			if ( xtra_check_files_writability($set['label']) ) {
				$html .= ' &nbsp;&nbsp;&nbsp;<label for="'.$setname.'">'.$pre_label.$post_label.'<input type="checkbox" name="'.$setname.'" id="'.$setname.'" value="1" 	'.(($curval!=1)?'':'checked="checked"').' /></label>';
			}
		}
		else {
			//.. Label
			$clr = $xtra_tabcols[$set['tab']]['color'];
			if (!$clr) $clr = "#00a0d2";
			$left_indent = 20;
			$lvl = "";
			if (isset($set['level']) && $set['level']) $lvl = "padding-left: ".(($set['level'])*$left_indent)."px;";
			$sty = 'style="border-left: 4px solid transparent;'.$lvl.'"';
			if ( $curval
				|| strpos($setname,"_tools")!==FALSE
				|| ( $setname == "xtra_autoupdate_cron_buttons" && get_optionXTRA('xtra_all_autoupdate')!=-1 )
				|| ( $setname == "xtra_uptime_robot_buttons" && get_optionXTRA('xtra_disable_WPcron') )
				|| ( $setname == "xtra_hits_geoip_force" )
			) $sty = 'style="border-left: 4px solid '.$clr.';'.$lvl.'"';

			if ($ixx>1) $html .= '</td></tr>';
			$html .= '<tr>';
			if (xtra_instr($set['label'],"&nbsp; ")) {
				$indent = " class='ind1'";
				$ops_num = "<td></td>";
			}
			else {
				$indent = "";
				$ops++;
				$ops_num = "<td class='ops_num_td check-column'><div class='ops_num_div' style='color:$clr'>".$sec.".".$ops."</div></td>";
			}
			if ( get_optionXTRA( 'xtra_opt_show_opsnum' ) )
				$html .= $ops_num;
			$html .= '<th '.$sty.' scope="row">';
			$html .= preg_replace("#<br.*?>#i","<br/><span$indent>",$set['label'],1)."</span>";
			if ($setname == "xtra_autoupdate_cron_buttons") {
				$crn = xtra_get_crons('array','wp_version_check','date');
				if ($crn) $html .= '<br/><span>'. esc_html__('Next Auto-Update cron job scheduled for', 'xtra-settings') .': '.$crn.'</span>';
			}
			$html .= '</th><td>';

			if ( !xtra_check_files_writability($set['label']) ) {
				// is NOT writable
				$html .= ' &nbsp;&nbsp;&nbsp;&nbsp;('.((stripos($set['label'],'htaccess'))?'htaccess':'wp-config.php').' '. esc_html__('is not writable', 'xtra-settings') .'!)';
			}
			else if ( $setname == 'xtra_online_security_tools' ) {
				//... Security Tools buttons
				$html .= '
				<a class="button button-small" target="_blank" href="https://www.google.com/transparencyreport/safebrowsing/diagnostic/index.html#url='.home_url().'">Google Transparency</a>
				<a class="button button-small" target="_blank" href="https://sitecheck.sucuri.net/">Sucuri</a>
				<a class="button button-small" target="_blank" href="http://www.isithacked.com/">Is It Hacked?</a>
				<a class="button button-small" target="_blank" href="http://www.urlvoid.com/">URLVoid</a>
				<a class="button button-small" target="_blank" href="http://www.unmaskparasites.com/security-report/?page='.home_url().'">Unmask Parasites</a>
				';
			}
			else if ( $setname == 'xtra_online_speed_tools' ) {
				//... Speed Tools buttons
				$html .= '
				<a class="button button-small" target="_blank" href="https://developers.google.com/speed/pagespeed/insights/?url='.home_url().'">Google PageSpeed</a>
				<a class="button button-small" target="_blank" href="https://gtmetrix.com/">GTmetrix</a>
				<a class="button button-small" target="_blank" href="https://tools.pingdom.com/">Pingdom</a>
				';
			}
			else if ( $setname == 'xtra_online_seo_tools' ) {
				//... SEO Tools buttons
				$html .= '
				<a class="button button-small" target="_blank" href="https://www.google.com/webmasters/tools/">Google Search Console</a>
				<a class="button button-small" target="_blank" href="https://analytics.google.com/">Google Analytics</a>
				<a class="button button-small" target="_blank" href="https://seositecheckup.com/">SeoSiteCheckup</a>
				';
			}
			else if ( $setname == 'xtra_autoupdate_cron_buttons' && get_optionXTRA('xtra_all_autoupdate')!=-1 ) {
				//... Auto-Update Start Button
				$html .= '
				<a class="button button-small" href="'.wp_nonce_url('?page=xtra&do=maybe_auto_update','mynonce').'">'. esc_html__('Start Auto-Update check Now', 'xtra-settings') .' !!!</a>
				';
			}
			else if ( $setname == 'xtra_autoupdate_cron_buttons' && get_optionXTRA('xtra_all_autoupdate')==-1 ) {
				//... Auto-Update Start Button - disabled
				$html .= '
				<a class="button button-small button-disabled" href="#">'. esc_html__('Start Auto-Update check Now', 'xtra-settings') .' !!!</a>
				';
			}
			else if ( $setname == 'xtra_uptime_robot_buttons' ) {
				//... Uptime Robot Button
				$html .= '
				<a class="button button-small" target="_blank" href="https://uptimerobot.com/">Uptime Robot</a>
				';
			}
			else if ( $setname == 'xtra_hits_geoip_force' ) {
				//... GeoIP force refresh Button
				$html .= '
				<a class="button button-small" onclick="jQuery(this).next().show();" href="'.wp_nonce_url('?page=xtra&do=xtra_hits_geoip_force','mynonce').'">'. esc_html__('Force Refresh GeoIP Now', 'xtra-settings') .'</a>
				<div class="disp-none bold dark-red">'. esc_html__('Please wait for GeoIP update for about 10 sec.', 'xtra-settings') .'</div>
				';
			}
			else if ( $setname == 'xtra_share_buttons_place' ) {
				//... Share Buttons Position - Radio
				$html .= '
				<label class="no-wrap mright20" for="'.$setname.'3"><input type="radio" name="'.$setname.'" id="'.$setname.'3" value="3" 	'.(($curval!=3)?'':'checked="checked"').' />'. esc_html__('before title', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'6"><input type="radio" name="'.$setname.'" id="'.$setname.'6" value="6" 	'.(($curval!=6)?'':'checked="checked"').' />'. esc_html__('after title', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'2"><input type="radio" name="'.$setname.'" id="'.$setname.'2" value="2" 	'.(($curval!=2)?'':'checked="checked"').' />'. esc_html__('before text', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'1"><input type="radio" name="'.$setname.'" id="'.$setname.'1" value="1" 	'.(($curval!=1)?'':'checked="checked"').' />'. esc_html__('after text', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'4"><input type="radio" name="'.$setname.'" id="'.$setname.'4" value="4" 	'.(($curval!=4)?'':'checked="checked"').' />'. esc_html__('after article', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'5"><input type="radio" name="'.$setname.'" id="'.$setname.'5" value="5" 	'.(($curval!=5)?'':'checked="checked"').' />shortcode: [xtra_share_buttons]</label>
				';
			}
			else if ( $setname == 'xtra_share2_buttons_place' ) {
				//... Share Buttons 2 Position - Radio
				$html .= '
				<label class="no-wrap mright20" for="'.$setname.'3"><input type="radio" name="'.$setname.'" id="'.$setname.'3" value="3" 	'.(($curval!=3)?'':'checked="checked"').' />'. esc_html__('before title', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'6"><input type="radio" name="'.$setname.'" id="'.$setname.'6" value="6" 	'.(($curval!=6)?'':'checked="checked"').' />'. esc_html__('after title', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'2"><input type="radio" name="'.$setname.'" id="'.$setname.'2" value="2" 	'.(($curval!=2)?'':'checked="checked"').' />'. esc_html__('before text', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'1"><input type="radio" name="'.$setname.'" id="'.$setname.'1" value="1" 	'.(($curval!=1)?'':'checked="checked"').' />'. esc_html__('after text', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'4"><input type="radio" name="'.$setname.'" id="'.$setname.'4" value="4" 	'.(($curval!=4)?'':'checked="checked"').' />'. esc_html__('after article', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'5"><input type="radio" name="'.$setname.'" id="'.$setname.'5" value="5" 	'.(($curval!=5)?'':'checked="checked"').' />shortcode: [xtra_share_buttons2]</label>
				';
			}
			else if ( $setname == 'xtra_related_posts_place' ) {
				//... Related Posts Position - Radio
				$html .= '
				<label class="no-wrap mright20" for="'.$setname.'3"><input type="radio" name="'.$setname.'" id="'.$setname.'3" value="3" 	'.(($curval!=3)?'':'checked="checked"').' />'. esc_html__('before title', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'6"><input type="radio" name="'.$setname.'" id="'.$setname.'6" value="6" 	'.(($curval!=6)?'':'checked="checked"').' />'. esc_html__('after title', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'2"><input type="radio" name="'.$setname.'" id="'.$setname.'2" value="2" 	'.(($curval!=2)?'':'checked="checked"').' />'. esc_html__('before text', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'1"><input type="radio" name="'.$setname.'" id="'.$setname.'1" value="1" 	'.(($curval!=1)?'':'checked="checked"').' />'. esc_html__('after text', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'4"><input type="radio" name="'.$setname.'" id="'.$setname.'4" value="4" 	'.(($curval!=4)?'':'checked="checked"').' />'. esc_html__('after article', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'5"><input type="radio" name="'.$setname.'" id="'.$setname.'5" value="5" 	'.(($curval!=5)?'':'checked="checked"').' />shortcode: [xtra_related_posts]</label>
				';
				if ( $setname == 'xtra_related_posts_place' ) {
					//.... Related Posts Dont Show FROM Categories - array
					$ops++;
					$ops_num = "<td class='ops_num_td check-column'><div class='ops_num_div' style='color:$clr'>".$sec.".".$ops."</div></td>";
					$html .= '<tr>';
					if ( get_optionXTRA( 'xtra_opt_show_opsnum' ) )
						$html .= $ops_num;
					$tvala = get_optionXTRA( 'xtra_categories_exclude', array() );
					$sty = 'style="border-left: 4px solid transparent;'.$lvl.'"';
					if (count((array)$tvala)) $sty = 'style="border-left: 4px solid '.$clr.';'.$lvl.'"';
					$html .= '<th '.$sty.' scope="row">'. esc_html__('Don\'t allow posts from these categories', 'xtra-settings') .'</th>
							<td><div class="h-150">
							';
					foreach( get_categories() as $key => $cat ) {
						$categ = $cat->name;
						$slug = $cat->term_id;
						$tval = (in_array($slug,$tvala))?1:0;
						$html .= '<input class="exclude_checkbox" type="checkbox" name="xtra_categories_exclude[]" id="'.$slug.'" value="'.$slug.'" '.(($tval==0)?'':'checked="checked"').' />';
						$html .= '<label for="'.$slug.'" class="no-wrap;">'.$categ.'</label><br/>';
					}
					$html .= '</div>';
				}
			}
			else if ( $setname == 'xtra_share_buttons_shape' ) {
				//... Share Buttons Shape - Radio
				$html .= '
				<label class="no-wrap mright20" for="'.$setname.'0"><input type="radio" name="'.$setname.'" id="'.$setname.'0" value="0" 	'.(($curval!=0)?'':'checked="checked"').' />'. esc_html__('square', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'1"><input type="radio" name="'.$setname.'" id="'.$setname.'1" value="1" 	'.(($curval!=1)?'':'checked="checked"').' />'. esc_html__('rounded sqr', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'2"><input type="radio" name="'.$setname.'" id="'.$setname.'2" value="2" 	'.(($curval!=2)?'':'checked="checked"').' />'. esc_html__('circle', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'3"><input type="radio" name="'.$setname.'" id="'.$setname.'3" value="3" 	'.(($curval!=3)?'':'checked="checked"').' />'. esc_html__('rectangle', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'4"><input type="radio" name="'.$setname.'" id="'.$setname.'4" value="4" 	'.(($curval!=4)?'':'checked="checked"').' />'. esc_html__('rounded rect', 'xtra-settings') .'</label>
				';
			}
			else if ( $setname == 'xtra_share2_buttons_shape' ) {
				//... Share Buttons 2 Shape - Radio
				$html .= '
				<label class="no-wrap mright20" for="'.$setname.'0"><input type="radio" name="'.$setname.'" id="'.$setname.'0" value="0" 	'.(($curval!=0)?'':'checked="checked"').' />'. esc_html__('square', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'1"><input type="radio" name="'.$setname.'" id="'.$setname.'1" value="1" 	'.(($curval!=1)?'':'checked="checked"').' />'. esc_html__('rounded sqr', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'2"><input type="radio" name="'.$setname.'" id="'.$setname.'2" value="2" 	'.(($curval!=2)?'':'checked="checked"').' />'. esc_html__('circle', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'3"><input type="radio" name="'.$setname.'" id="'.$setname.'3" value="3" 	'.(($curval!=3)?'':'checked="checked"').' />'. esc_html__('rectangle', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'4"><input type="radio" name="'.$setname.'" id="'.$setname.'4" value="4" 	'.(($curval!=4)?'':'checked="checked"').' />'. esc_html__('rounded rect', 'xtra-settings') .'</label>
				';
			}
			else if ( stripos($set['section'],''. esc_html__('Wordpress Auto-Update', 'xtra-settings') .'')!==false && $setname != 'xtra_plugin_excl_hide_notif' && $setname != 'xtra_theme_excl_hide_notif' ) {
				//... Auto-Update - Radio
				$html .= '
				<label class="no-wrap mright20" for="'.$setname.'1"><input type="radio" name="'.$setname.'" id="'.$setname.'1" value="1" 	'.(($curval!=1)?'':'checked="checked"').' />'. esc_html__('Enable', 'xtra-settings') .'</label>
				<label class="no-wrap mright20" for="'.$setname.'2"><input type="radio" name="'.$setname.'" id="'.$setname.'2" value="0" 	'.(($curval!=0)?'':'checked="checked"').' />'. esc_html__('WP default', 'xtra-settings') .' <span class="fix-35">('.$set['def'].')</span></label>
				<label class="no-wrap mright20" for="'.$setname.'3"><input type="radio" name="'.$setname.'" id="'.$setname.'3" value="-1" 	'.(($curval!=-1)?'':'checked="checked"').' />'. esc_html__('Disable', 'xtra-settings') .'</label>
				';
				if ( $setname == 'xtra_plugin_autoupdate' ) {
					//.... Auto-Update Exclude Plugins - array
					$html .= '<tr>';
					if ( get_optionXTRA( 'xtra_opt_show_opsnum' ) )
						$html .= '<td></td>';
					$html .= '<th '.$sty.' scope="row"><div style="padding-left:'.$left_indent.'px;">&nbsp; - '. esc_html__('Exclude Plugins from Auto-Update', 'xtra-settings') .'<br/><span class="ind1">'. esc_html__('Works if Plugins Auto-Update (above) is Enabled', 'xtra-settings') .'</span></div></th>
							<td><div class="h-150">
							';
					$tvala = get_optionXTRA( 'xtra_plugins_exclude', array() );
					if ( ! function_exists( 'get_plugins' ) ) { require_once ABSPATH . 'wp-admin/includes/plugin.php'; }
					foreach( get_plugins() as $file => $pl ) { //$this->__pluginsFiles[$file] = $pl['Version'];
						$slug2 = dirname( plugin_basename( $file ) );
						$tval = (in_array($slug2,$tvala))?1:0;
						$html .= '<input class="exclude_checkbox" type="checkbox" name="xtra_plugins_exclude[]" id="'.$slug2.'" value="'.$slug2.'" '.(($tval==0)?'':'checked="checked"').' />';
						$html .= '<label for="'.$slug2.'" class="no-wrap;">'.$pl['Name'].'</label><br/>';
					}
					$html .= '</div>';
				}
				if ( $setname == 'xtra_theme_autoupdate' ) {
					//.... Auto-Update Exclude themes - array
					$html .= '<tr>';
					if ( get_optionXTRA( 'xtra_opt_show_opsnum' ) )
						$html .= '<td></td>';
					$html .= '<th '.$sty.' scope="row"><div style="padding-left:'.$left_indent.'px;">&nbsp; - '. esc_html__('Exclude themes from Auto-Update', 'xtra-settings') .'<br/><span class="ind1">'. esc_html__('Works if themes Auto-Update (above) is Enabled', 'xtra-settings') .'</span></div></th>
							<td><div class="h-150">
							';
					$tvala = get_optionXTRA( 'xtra_themes_exclude', array() );
					if ( ! function_exists( 'get_themes' ) ) { require_once ABSPATH . 'wp-admin/includes/theme.php'; }
					foreach( wp_get_themes() as $file => $pl ) { //$this->__themesFiles[$file] = $pl['Version'];
						//$slug2 = dirname( wp_basename( $file ) );
						$slug2 = $file;
						$tval = (in_array($slug2,$tvala))?1:0;
						$html .= '<input class="exclude_checkbox" type="checkbox" name="xtra_themes_exclude[]" id="'.$slug2.'" value="'.$slug2.'" '.(($tval==0)?'':'checked="checked"').' />';
						$html .= '<label for="'.$slug2.'" class="no-wrap;">'.$pl['Name'].'</label><br/>';
					}
					$html .= '</div>';
				}
			}
			else if ( xtra_instr($setname,'xtra_hits_exclude_') ) {
				//... Hits Exclude - double checkbox
				$html .= '<label class="no-wrap mright30" for="'.$setname.'"><input type="checkbox" name="'.$setname.'" id="'.$setname.'" value="1" '.(($curval==0)?'':'checked="checked"').' />'. esc_html__('Don\'t count', 'xtra-settings') .'</label> ';
				$html .= '<label class="no-wrap" for="'.$setname.'_abandon"><input type="checkbox" name="'.$setname.'_abandon" id="'.$setname.'_abandon" value="1" '.((get_optionXTRA($setname.'_abandon')==0)?'':'checked="checked"').' />'. esc_html__('Don\'t log', 'xtra-settings') .'</label>';
			}
			else {
				//... Checkbox
				$html .= '<input type="checkbox" name="'.$setname.'" id="'.$setname.'" value="1" '.(($curval==0)?'':'checked="checked"').' />';
			}
		}
		//.. Current value
		if (isset($set['current'])) $html .= ' &nbsp;&nbsp;&nbsp;&nbsp;(current: '.$set['current'].')';
		if (isset($set['_current']) && $set['_current']) $html .= ' &nbsp;&nbsp;&nbsp;&nbsp;'.$set['_current'].'';
		//.. Additions
		if ( $setname == 'xtra_related_posts_homepage' ) {
			//... Related Posts Dont Show ON Categories - array
			//$ops++;
			//$ops_num = "<td class='ops_num_td check-column'><div class='ops_num_div' style='color:$clr'>".$sec.".".$ops."</div></td>";
			$html .= '<tr>';
			if ( get_optionXTRA( 'xtra_opt_show_opsnum' ) )
				$html .= $ops_num;
			$tvala = get_optionXTRA( 'xtra_relposts_cat_exclude', array() );
			if (count($tvala)) $sty = 'style="border-left: 4px solid '.$clr.';'.$lvl.'"';
			$html .= '<th '.$sty.' scope="row">'. esc_html__('&nbsp; - don\'t show on these categories', 'xtra-settings') .'</th>
					<td><div class="h-150">
					';
			foreach( get_categories() as $key => $cat ) {
				$categ = $cat->name;
				$slug = $cat->term_id;
				$tval = (in_array($slug,$tvala))?1:0;
				$html .= '<input class="exclude_checkbox" type="checkbox" name="xtra_relposts_cat_exclude[]" id="'.$slug.'" value="'.$slug.'" '.(($tval==0)?'':'checked="checked"').' />';
				$html .= '<label for="'.$slug.'" class="no-wrap;">'.$categ.'</label><br/>';
			}
			$html .= '</div>';
		}
		if ( $setname == 'xtra_share_buttons_homepage' ) {
			//... Share Buttons Dont Show ON Categories - array
			//$ops++;
			//$ops_num = "<td class='ops_num_td check-column'><div class='ops_num_div' style='color:$clr'>".$sec.".".$ops."</div></td>";
			$html .= '<tr>';
			if ( get_optionXTRA( 'xtra_opt_show_opsnum' ) )
				$html .= $ops_num;
			$tvala = get_optionXTRA( 'xtra_share_cat_exclude', array() );
			if (count($tvala)) $sty = 'style="border-left: 4px solid '.$clr.';'.$lvl.'"';
			$html .= '<th '.$sty.' scope="row">'. esc_html__('&nbsp; - don\'t show on these categories', 'xtra-settings') .'</th>
					<td><div class="h-150">
					';
			foreach( get_categories() as $key => $cat ) {
				$categ = $cat->name;
				$slug = $cat->term_id;
				$tval = (in_array($slug,$tvala))?1:0;
				$html .= '<input class="exclude_checkbox" type="checkbox" name="xtra_share_cat_exclude[]" id="'.$slug.'" value="'.$slug.'" '.(($tval==0)?'':'checked="checked"').' />';
				$html .= '<label for="'.$slug.'" class="no-wrap;">'.$categ.'</label><br/>';
			}
			$html .= '</div>';
		}
		if ( $setname == 'xtra_share2_buttons_homepage' ) {
			//... Share Buttons 2 Dont Show ON Categories - array
			//$ops++;
			//$ops_num = "<td class='ops_num_td check-column'><div class='ops_num_div' style='color:$clr'>".$sec.".".$ops."</div></td>";
			$html .= '<tr>';
			if ( get_optionXTRA( 'xtra_opt_show_opsnum' ) )
				$html .= $ops_num;
			$tvala = get_optionXTRA( 'xtra_share2_cat_exclude', array() );
			if (count($tvala)) $sty = 'style="border-left: 4px solid '.$clr.';'.$lvl.'"';
			$html .= '<th '.$sty.' scope="row">'. esc_html__('&nbsp; - don\'t show on these categories', 'xtra-settings') .'</th>
					<td><div class="h-150">
					';
			foreach( get_categories() as $key => $cat ) {
				$categ = $cat->name;
				$slug = $cat->term_id;
				$tval = (in_array($slug,$tvala))?1:0;
				$html .= '<input class="exclude_checkbox" type="checkbox" name="xtra_share2_cat_exclude[]" id="'.$slug.'" value="'.$slug.'" '.(($tval==0)?'':'checked="checked"').' />';
				$html .= '<label for="'.$slug.'" class="no-wrap;">'.$categ.'</label><br/>';
			}
			$html .= '</div>';
		}
		if ( $setname == 'xtra_redir_bots3_texta' ) {
			//... self lock-out
			$html .= '</td></tr>';
			if ( get_optionXTRA( 'xtra_opt_show_opsnum' ) )
				$html .= '<tr><th colspan=3>';
			else
				$html .= '<tr><th colspan=2>';
			$html .= '<small class="dark-red normal">"admin.php" '. esc_html__('pages are not blocked to avoid self lock-out', 'xtra-settings') .'.<br/>'. esc_html__('If you accidentally blocked yourself, just temporarily rename the "block_visitors.php" file in the xtra-settings plugin folder.', 'xtra-settings') .'</small>';
		}
		if ( $setname == 'xtra_posts_status_color' ) {
			//... Highlight Post Color by Status
			$html .= '
			<table class="wp-list-table">
			<tr><td>'. esc_html__('Draft', 'xtra-settings') .'	<td class="p-0"><input 	type="text" class="xtra-color-picker" name="'.$setname.'1" id="'.$setname.'1" value="'.get_optionXTRA('xtra_posts_status_color1','#FCE3F2').'" />
			<tr><td>'. esc_html__('Pending', 'xtra-settings') .'	<td class="p-0"><input 	type="text" class="xtra-color-picker" name="'.$setname.'2" id="'.$setname.'2" value="'.get_optionXTRA('xtra_posts_status_color2','#f2e0ab').'" />
			<tr><td>'. esc_html__('Future', 'xtra-settings') .'	<td class="p-0"><input 	type="text" class="xtra-color-picker" name="'.$setname.'3" id="'.$setname.'3" value="'.get_optionXTRA('xtra_posts_status_color3','#C6EBF5').'" />
			<tr><td>'. esc_html__('Private', 'xtra-settings') .'	<td class="p-0"><input 	type="text" class="xtra-color-picker" name="'.$setname.'4" id="'.$setname.'4" value="'.get_optionXTRA('xtra_posts_status_color4','#b49de0').'" />
			</table>
			';
		}
		if ( $setname == 'xtra_hits_enable_pnum' ) {
			//... Hits log-size
			$h1 = get_optionXTRA('xtra_hits', array());
			$h2 = get_optionXTRA('xtra_hits_IPs', array());
			$h3 = get_optionXTRA('xtra_hits_IPdata', array());
			$h4 = get_optionXTRA('xtra_hits_HITdata', array());
			$xsize = 0;
			$xsize += (function_exists('mb_strlen') ? mb_strlen(serialize($h1), '8bit') : strlen(serialize($h1)) );
			$xsize += (function_exists('mb_strlen') ? mb_strlen(serialize($h2), '8bit') : strlen(serialize($h2)) );
			$xsize += (function_exists('mb_strlen') ? mb_strlen(serialize($h3), '8bit') : strlen(serialize($h3)) );
			$xsize += (function_exists('mb_strlen') ? mb_strlen(serialize($h4), '8bit') : strlen(serialize($h4)) );

			$html .= '<br/>('. esc_html__('current data', 'xtra-settings') .': '.round($xsize/1024,1).' kB';
			$html .= ' / '.count((array)$h1).' '. esc_html__('days', 'xtra-settings') .'';
			$html .= ' / '.count((array)$h2).' '. esc_html__('IPs', 'xtra-settings') .'';
			$html .= ' / '.count((array)$h4).' '. esc_html__('hits', 'xtra-settings') .'';
			$html .= ')';
			$html .= '<br/>';
			$html .= '<a class="button button-small" href="'.wp_nonce_url('admin.php?page=xtra&hitlist=1','mynonce').'">'. esc_html__('Show Hits Log', 'xtra-settings') .'</a>';
			$html .= '<a class="float-right button button-small" onclick="return confirm(\''. esc_html__('This will DELETE All Hits Data', 'xtra-settings') .'!\n'. esc_html__('Do you really want continue?', 'xtra-settings') .'\');" href="'.wp_nonce_url('?page=xtra&do=xtra_hits_reset','mynonce').'">'. esc_html__('Clear All Hits Data', 'xtra-settings') .'</a>';
			$html .= '<span class="float-right"> &nbsp;&nbsp;&nbsp;</span>';
			$html .= '<a class="float-right button button-small" onclick="return confirm(\''. esc_html__('This will DELETE Today Hits', 'xtra-settings') .'!\n'. esc_html__('Do you really want continue?', 'xtra-settings') .'\');" href="'.wp_nonce_url('?page=xtra&do=xtra_hits_today_reset','mynonce').'">'. esc_html__('Reset Today Data', 'xtra-settings') .'</a>';
			if (false) {
				// memory cleaning
				$h1 = null;
				$h2 = null;
				$h3 = null;
				$h4 = null;
				time_nanosleep(0, 10000000);
			}
		}
		//if ( $setname == 'xtra_hits_send_mail_num' ) {
		if ( $setname == 'xtra_hits_send_mail_text' ) {
			//... Hits mail button
			$last = get_optionXTRA('xtra_hits_send_mail_last', 0);
			//$around = get_optionXTRA('xtra_hits_send_mail_num', 0);
			$around = "";
			if (!$around) $around = "23:59:59";
			$around_ts = strtotime("today ".$around);
			if ($around_ts < time()) $around_ts = strtotime("tomorrow ".$around);
			if (!$around) $around_ts = strtotime("tomorrow ".$around);
			if ($around_ts === FALSE) $last + (24*60*60);
			//$next = $last + (24*60*60);
			if (true) $html .= '<br/>'. esc_html__('Server time', 'xtra-settings') .': '.date("Y-m-d H:i:s", time());
			if ($last) $html .= '<br/>&nbsp;&nbsp;&nbsp;- '. esc_html__('last sent', 'xtra-settings') .': '.date("Y-m-d H:i:s", $last);
			if ($around_ts) $html .= '<br/>&nbsp;&nbsp;&nbsp;- '. esc_html__('next send', 'xtra-settings') .': '.date("Y-m-d", $around_ts)." ". esc_html__('after', 'xtra-settings') ." ".date("H:i:s", $around_ts);
			if (true) $html .= '<br/>'. esc_html__('Local time', 'xtra-settings') .': '.wp_date("Y-m-d H:i:s", time());
			if (true) $html .= ' &nbsp;&nbsp;&nbsp; ('. esc_html__('difference', 'xtra-settings') .': '.round(( strtotime(wp_date("Y-m-d H:i:s", time())) - strtotime(date("Y-m-d H:i:s", time())) )/60) . ' min)';
			if ($last) $html .= '<br/>&nbsp;&nbsp;&nbsp;- '. esc_html__('last sent', 'xtra-settings') .': '.wp_date("Y-m-d H:i:s", $last);
			if ($around_ts) $html .= '<br/>&nbsp;&nbsp;&nbsp;- '. esc_html__('next send', 'xtra-settings') .': '.wp_date("Y-m-d", $around_ts)." ". esc_html__('after', 'xtra-settings') ." ".wp_date("H:i:s", $around_ts);
			$html .= '<br/><a class="button button-small" href="'.wp_nonce_url('?page=xtra&do=xtra_hits_send_mail_now','mynonce').'">'. esc_html__('Send Now', 'xtra-settings') .'</a>';
		}
		if ( $setname == 'xtra_hits_show_side' && !get_optionXTRA('xtra_hits_show_side') ) {
			//... Hits Counter Box
			$html .= '</td></tr>';
			if ( get_optionXTRA( 'xtra_opt_show_opsnum' ) )
				$html .= '<tr><th scope="row" colspan=3>';
			else
				$html .= '<tr><th scope="row" colspan=2>';
			$html .= xtra_hits_data_block(1);
		}
	}
	$html .= '</td></tr>';
	$html .= '</table></div>';
return $html;
}


// Check is_writable
function xtra_check_files_writability($label) {
	if (stripos($label,'htaccess')!==false && !is_writable(ABSPATH . '.htaccess')) return false;
	if (stripos($label,'wp-config')!==false && !is_writable(ABSPATH . 'wp-config.php')) return false;
	return true;
}

function xtra_error_level_tostring($intval, $separator = ',')
{
    $errorlevels = array(
        E_ALL => 'E_ALL',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        E_DEPRECATED => 'E_DEPRECATED',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_STRICT => 'E_STRICT',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_NOTICE => 'E_NOTICE',
        E_PARSE => 'E_PARSE',
        E_WARNING => 'E_WARNING',
        E_ERROR => 'E_ERROR');
    $result = '';
    foreach($errorlevels as $number => $name)
    {
        if (($intval & $number) == $number) {
            $result .= ($result != '' ? $separator : '').$name; }
    }
    return $result;
}

function xtra_get_images($ret="html",$filterHook="",$filterField="") {
global $xtra_get_images_count;
global $xtra_get_images_size;
global $xtra_images_list;
	$xtra_images_list = array();

	$upload_dir = XTRA_UPLOAD_DIR;
	$i = 0;

	if (file_exists($upload_dir)) $dir_iterator = new RecursiveDirectoryIterator($upload_dir);
	if (file_exists($upload_dir)) $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
	// could use CHILD_FIRST if you so wish
	foreach ($iterator as $file) {
		if ($file->isFile()) {
			$filename = $file->getFilename();
			$fullpath = $file->getPathname();
			$fullurl = str_ireplace(ABSPATH,home_url()."/",$fullpath);
			$relapath = str_ireplace(array($upload_dir."/","/".$filename),"",$fullpath);

			if ($relapath && !preg_match('{^\d{4}/}',$relapath)) continue;
			if (preg_match('{-\d+x\d+\.}i',$filename)) continue;
			if (!preg_match('{\.(jpg|jpeg|jpe|png|gif|bmp|tiff?|ico)$}i',$filename)) continue;

			$size = $file->getSize();
			$xtra_get_images_size += $size;
			$mtime = $file->getMTime();

			$key = $relapath."/".$filename;
			$xtra_images_list[$key]['filename'] = $filename;
			$xtra_images_list[$key]['fullpath'] = $fullpath;
			$xtra_images_list[$key]['fullurl'] = $fullurl;
			$xtra_images_list[$key]['relapath'] = $relapath;
			$xtra_images_list[$key]['size'] = $size;
			$xtra_images_list[$key]['mtime'] = $mtime;
			$i++;
		}
	}
	//ksort($xtra_images_list);
	krsort($xtra_images_list);
	$i = 0;
	$img_postids = xtra_get_image_ids();
	$html = "";
	foreach ($xtra_images_list as $row) {
			$restore = "";
			if ( file_exists(str_replace(XTRA_UPLOAD_DIR,XTRA_UPLOAD_DIR ."/xtra-img-backup",$row['fullpath'])) ) {
				//$restore = "<a target='_blank' title='restore from backup' href=''><div class='dashicons dashicons-upload'></div></a>";
				$restore = "<div title='has backup' class='dashicons dashicons-upload'></div>";
			}
			$postids = array();
			if (isset($img_postids[$row['relapath']."/".$row['filename']]))
				$postids = explode(",",$img_postids[$row['relapath']."/".$row['filename']]);
			$postlinks = array();
			$postid_1 = "";
			foreach ( (array)$postids as $postid ) {
				if (!$postid) continue;
				$postid_1 = $postid;
				$postlinks[] = "<a target='_blank' title='view post $postid\n".get_the_title($postid)."' href='".home_url()."/?p=$postid'><div class='dashicons dashicons-visibility'></div></a>";
				$postlinks[] = "<a target='_blank' title='edit post $postid\n".get_the_title($postid)."' href='".site_url()."/wp-admin/post.php?post=$postid&action=edit'><div class='dashicons dashicons-welcome-write-blog'></div></a>";
			}
			//$postlinks = array_merge($postlinks,(array)$restore);
			$postlinkstxt = "";
			if (count((array)$postlinks)) $postlinkstxt = implode(" ",$postlinks);
			$i++;
			list($imgw,$imgh) = getimagesize($row['fullpath']);
			$html .= "<tr>";
			$html .= "<td>".$row['relapath']."</td>";
			//$html .= "<th scope='row'><a target='_blank' title='view image' href='".$row['fullurl']."'>".	$row['filename']."</a></th>";
			$html .= "<td><a target='_blank' title='view image' href='".$row['fullurl']."' class='bold'>".	$row['filename']."</a></td>";
			$html .= "<td align='right'>$imgw&nbsp;&nbsp;x</td>";
			$html .= "<td>$imgh</td>";
			$html .= "<td>".xtra_get_image_quality($row['fullpath'],1,0,1,"?")." $restore</td>";
			$html .= "<td align='right'>".round($row['size']/1024,0)." KB</td>";
			$html .= "<td>".date("Y-m-d", $row['mtime'])."</td>";
			$html .= "<td>$postlinkstxt</td>";
			$html .= "<td>".
				'<input type="checkbox" class="xtra_image_cb" name="delXimage'.$i.'" id="delXimage'.$i.'" pid="'.$postid_1.'" value="'.$row['fullpath'].'" />'.
				"</td>";
			$html .= "</tr>";
	}
	//echo "<h2>".$upload_dir."</h2>";
	//echo "<pre>".$filepath."</pre>";
	$xtra_get_images_count = $i;
	if ($ret=="array") return $xtra_images_list;
	if ($ret=="html") return $html;
}

function xtra_get_image_quality($fullpath="",$estimate=true,$nonjpg_estimate=false,$colorize=false,$notvalid="n.a.") {
	if (!$fullpath) return;
	//if (function_exists('wp_get_image_editor')) $qual = wp_get_image_editor( $fullpath )->get_quality();
	$qual = "";
	if (preg_match('{\.jpe?g$}i',$fullpath)) {
		$exif = @exif_read_data($fullpath, 0, true);
		$qual = (count((array)$exif['COMMENT']) && xtra_instr($exif['COMMENT'][0],"quality")) ? trim(substr($exif['COMMENT'][0],-3)) : "";
	}
	if (!$qual && $estimate) {
		list($imgw,$imgh) = getimagesize($fullpath);
		$size = filesize($fullpath);
		$qual = round( 101-( ($imgw*$imgh*3) / $size ) );
		if (!preg_match('{\.jpe?g$}i',$fullpath)) {
			if ($nonjpg_estimate) $qual = round( 101-( ($imgw*$imgh*4) / $size ) );
			else $qual = "";
		}
	}
	if (!$qual) return $notvalid;
	if ($colorize && $qual > get_optionXTRA('xtra_custom_jpeg_quality_num',82)) return "<span class='dark-red xbold'>".$qual."%</span>";
	return $qual."%";
}
function xtra_get_crons($ret="html",$filterHook="",$filterField="") {
global $xtra_get_crons_count;
global $crons;
	$crons = array();
	$cronsa = get_option('cron');
	$cron_info_text = "";
	$i = 0;
	foreach ( (array)$cronsa as $timestamp => $cronhooks ) {
		foreach ( (array)$cronhooks as $hook => $keys ) {
			if (!$hook) continue;
			if ($filterHook && $hook!=$filterHook) continue;
			$tkeys = "";
			foreach ( (array)$keys as $k => $v ) {
				$i++;
				$crons['timestamp'] = $timestamp;
				if ($timestamp) $crons['date'] = date("Y-m-d H:i:s",$timestamp);
				$crons['hook'] = $hook;
				$crons['key'] = $k;
				$cron_info_text .= "<tr><th scope='row'>".date("Y-m-d H:i:s",$timestamp)."</th>";
				$cron_info_text .= "<td>".$hook."</td>";
				//$cron_info_text .= "<td>".$k."</td>";
				$cron_info_text .= '<input type="hidden" name="cronHook'.$i.'" value="'.$hook.'" />';
				$cron_info_text .= '<input type="hidden" name="cronTime'.$i.'" value="'.$timestamp.'" />';
				$cron_info_text .= '<input type="hidden" name="cronKey'.$i.'" value="'.$k.'" />';
				if (is_array($v)) {
					foreach ($v as $k1 => $v1) {
						if (is_array($v1) && !empty($v1)) $tkeys .= "(".implode(", ",$v1)."), ";
						else if ($v1) $tkeys .= "$v1, ";
					}
				}
				else if ($v) $tkeys .= "$v, ";
			}
			$crons['options'] = $tkeys;
			$cron_info_text .= "<td>".$tkeys."</td>";
			$cron_info_text .= '<td><input type="checkbox" name="delXcron'.$i.'" id="delXcron'.$i.'" value="1" /></td>';
			if ($filterHook && $filterField) return $crons[$filterField];
		}
	}
	$xtra_get_crons_count = $i;
	if ($ret=="array") return $crons;
	if ($ret=="html") return $cron_info_text;
}

function xtra_hits_data_block($jsBlock='', $fullPage='',	$active='',	$forceChart='',	$forceSum='',	$forceIP='',	$forceHits='',	$forceGeo='',	$forceIPc='',	$forcePages='',	$numIP='',	$numHits='' ) {
	global $js_h400, $js_w2;
	
	if (''===$jsBlock) 		$jsBlock 	= 1;
	if (''===$fullPage) 	$fullPage 	= false;
	if (''===$active) 		$active 	= 1;
	if (''===$forceChart) 	$forceChart = get_optionXTRA('xtra_show_hit_chart',0);
	if (''===$forceSum) 	$forceSum 	= true;
	if (''===$forceIP) 		$forceIP 	= get_optionXTRA('xtra_hits_show_ips',0);
	if (''===$forceHits) 	$forceHits 	= get_optionXTRA('xtra_hits_show_hitsdata',0);
	if (''===$forceGeo) 	$forceGeo 	= get_optionXTRA('xtra_hits_show_geo',0);
	if (''===$forceIPc) 	$forceIPc 	= get_optionXTRA('xtra_hits_show_ipc',0);
	if (''===$forcePages) 	$forcePages	= get_optionXTRA('xtra_hits_show_pages',0);
	if (''===$numIP) 		$numIP 		= get_optionXTRA('xtra_hits_show_ips_num',10);
	if (''===$numHits) 		$numHits 	= get_optionXTRA('xtra_hits_show_hitsdata_num',50);
	
	$butt_prim = array('1' => '','2' => '','3' => '','4' => '','5' => '','6' => '');
	$tbl_hid = array('1' => 'xtra-hidden','2' => 'xtra-hidden','3' => 'xtra-hidden','4' => 'xtra-hidden','5' => 'xtra-hidden','6' => 'xtra-hidden');
	$butt_prim[$active] = 'button-primary';
	$tbl_hid[$active] = '';
	
		$js_printopt = "jQuery('<iframe>', {name: 'myiframe',class: 'printFrame'}).appendTo('body').contents().find('body').append(jQuery(this).closest('.xtra_box').html());window.frames['myiframe'].focus();window.frames['myiframe'].print();setTimeout(() => { jQuery('.printFrame').remove(); }, 1000);event.stopPropagation();";
		$btn = "";
		if (true) $btn .= '
			<form action="?page=xtra" method="post" class="inline">
				<input type="button" value="Print" class="float-right button button-xsmall" onclick="'.$js_printopt.'" />
				'.wp_nonce_field( 'mynonce' ).'
			</form>
		';
		if (true) $btn .= '
			&nbsp;<a class="float-right button button-xsmall" onclick="event.stopPropagation();return true;" href="'.wp_nonce_url('admin.php?page=xtra&hitlist=1','mynonce').'">'. esc_html__('All Hits', 'xtra-settings') .'</a>
		';
	$html = '';
	if (true) $html .= '';
	if (true) $html .= '
				<div class="xtra_box">
					<h2>'. esc_html__('Hit Counter', 'xtra-settings') .'
					'.($fullPage ? '<span class="m-left-50 small"><a class="button button-primary" href="?page=xtra">'. esc_html__('Back to main page', 'xtra-settings') .'</a></span>' : '').'
					</h2>
					<table class="wp-list-table widefat fixed striped xtra"><tr><td>
							<div class="float-right chart-button">'.$btn.'</div>
						'. ($forceChart ? xtra_hits_show("chart") : '') .'
							<div class="clear"></div>
						'. ($fullPage ? '<div>' : '<div class="max-h-150" onclick="'.($jsBlock>=1 ? $js_h400 : '').($jsBlock>=2 ? $js_w2 : '').'">') .'
							<div class="float-left xtra_tabs_wrap">
								'.($forceSum ? '<input class="xtra_tabs_butt button button-small '.$butt_prim['1'].'" type="button" value="'. esc_html__('Sum', 'xtra-settings') .'" data-show="sum" />' : '') .'
								'.($forceIP ? '<input class="xtra_tabs_butt button button-small '.$butt_prim['2'].'" type="button" value="'. esc_html__('Unique', 'xtra-settings') .'" data-show="IPs" />' : '') .'
								'.($forceHits ? '<input class="xtra_tabs_butt button button-small '.$butt_prim['3'].'" type="button" value="'. esc_html__('Log', 'xtra-settings') .'" data-show="hits" />' : '') .'
								'.($forceGeo ? '<input class="xtra_tabs_butt button button-small '.$butt_prim['4'].'" type="button" value="'. esc_html__('Geo', 'xtra-settings') .'" data-show="countries" />' : '') .'
								'.($forceIPc ? '<input class="xtra_tabs_butt button button-small '.$butt_prim['5'].'" type="button" value="'. esc_html__('IPs', 'xtra-settings') .'" data-show="ipc" />' : '') .'
								'.($forcePages ? '<input class="xtra_tabs_butt button button-small '.$butt_prim['6'].'" type="button" value="'. esc_html__('Pages', 'xtra-settings') .'" data-show="pages" />' : '') .'
							</div>
							<table class="wp-list-table widefat striped xtra-tabs xtra-tabs-sum '.$tbl_hid['1'].'">
								'.($forceSum ? xtra_hits_show("tr") : '') .'
							</table>
							<table class="wp-list-table widefat striped xtra-tabs xtra-tabs-IPs '.$tbl_hid['2'].'">
								'. ($forceIP ? xtra_hits_show("IPs",$numIP) : '') .'
							</table>
							<table class="wp-list-table widefat striped xtra-tabs xtra-tabs-hits '.$tbl_hid['3'].'">
								'. ($forceHits ? xtra_hits_show("HITs",$numHits) : '') .'
							</table>
							<table class="wp-list-table widefat striped xtra-tabs xtra-tabs-countries '.$tbl_hid['4'].'">
								'. ($forceGeo ? xtra_hits_show("Countries",$numHits) : '') .'
							</table>
							<table class="wp-list-table widefat striped xtra-tabs xtra-tabs-ipc '.$tbl_hid['5'].'">
								'. ($forceIPc ? xtra_hits_show("IPc",$numHits) : '') .'
							</table>
							<table class="wp-list-table widefat striped xtra-tabs xtra-tabs-pages '.$tbl_hid['6'].'">
								'. ($forcePages ? xtra_hits_show("Pages",$numHits) : '') .'
							</table>
						</div>
					</td></tr></table>
				</div>
	';
	if (true) $html .= '
				<script type="application/javascript">
					(function($) {
						$(\'.xtra_tabs_butt\').on(\'click\', function(e) {
							e.preventDefault();
							e.stopPropagation();
							$(this).closest(\'div\').find(\'.xtra_tabs_butt\').removeClass(\'button-primary\');
							$(this).addClass(\'button-primary\').blur();
							$(this).closest(\'div\').parent().find(\'.xtra-tabs\').addClass(\'xtra-hidden\');
							$(this).closest(\'div\').parent().find(\'.xtra-tabs-\'+$(this).data(\'show\')).removeClass(\'xtra-hidden\');
						});
					})(jQuery);
				</script>
	';
	if ($fullPage) $html = '
		<div class="wrap _xtra">
			<div class="xtra_full_wr">
				<h1 id="main_title">XTRA Settings<span id="main_version" class="m-left-50 small">'. esc_html__('Current version', 'xtra-settings') .': '.XTRA_VERSION.'</span></h1>
				'.$html.'
			</div>
		</div>';
return $html;
}


//---DEFAULTS---
global $wpdb;
global $xtra_seltab, $xtra_tabs;
$xtra_seltab = "0";
if(isset($_GET['xtra_seltab'])) $xtra_seltab = $_GET['xtra_seltab'];
global $xtra_pageload_time;
global $xtra_error_level_string;
$xtra_error_level_string = xtra_error_level_tostring(error_reporting(), ', ');

if ( ! function_exists( 'get_plugins' ) ) require_once ABSPATH . 'wp-admin/includes/plugin.php';

//---SUBMIT---

//. first click
//if(@$_REQUEST['first']==1)
	update_optionXTRA( 'xtra_msg', 1);

//. fetch, xfetch
if(isset($_POST['xtra_submit_last_seltab'])) $xtra_seltab = (string)$_POST['xtra_submit_last_seltab'];
if(isset($_REQUEST['fetch']) && $_REQUEST['fetch']) $xtra_seltab = "database";
if(isset($_REQUEST['xfetch']) && $_REQUEST['xfetch']) $xtra_seltab = "database";
if(isset($_POST['xtra_submit_options']) && get_optionXTRA('xtra_opt_show_own')) $xtra_seltab = "own";
if(isset($_POST['xtra_submit_delcron'])) $xtra_seltab = "crons";
if(isset($_POST['xtra_submit_muteplugin'])) $xtra_seltab = "plugins";
if(isset($_POST['xtra_submit_unmuteplugin'])) $xtra_seltab = "plugins";
if(isset($_POST['xtra_submit_delimage'])) $xtra_seltab = "images";
if(isset($_POST['xtra_refresh_images'])) $xtra_seltab = "images";

//. _GET do
if(isset($_GET['do'])) {
	xtra_check_nonce();
	if ($_GET['do'] == 'maybe_auto_update') {
		//do_action( 'wp_maybe_auto_update' ); //the action is after the html output
		$settings_status = '<div class="notice notice-success is-dismissible"><h3>'. esc_html__('Auto-Update started successfully in the background', 'xtra-settings') .'.</h3></div>';
	}
	if ($_GET['do'] == 'xtra_hits_send_mail_now') {
		xtra_hits_send_mail("sendnow");
		$settings_status = '<div class="notice notice-success is-dismissible"><h3>'. esc_html__('Hits stats mail was sent successfully', 'xtra-settings') .'.</h3></div>';
	}
	if ($_GET['do'] == 'xtra_hits_reset') {
		xtra_hits_reset();
		$settings_status = '<div class="notice notice-success is-dismissible"><h3>'. esc_html__('Hits stats was reset successfully', 'xtra-settings') .'.</h3></div>';
	}
	if ($_GET['do'] == 'xtra_hits_today_reset') {
		xtra_hits_today_reset();
		$settings_status = '<div class="notice notice-success is-dismissible"><h3>'. esc_html__('Today Hits stats was reset successfully', 'xtra-settings') .'.</h3></div>';
	}
	if ($_GET['do'] == 'xtra_hits_geoip_force') {
		xtra_hits_geoip_force();
		$settings_status = '<div class="notice notice-success is-dismissible"><h3>'. esc_html__('GeoIP was refreshed successfully', 'xtra-settings') .'.</h3></div>';
	}
}


//. allopts Backup
if(isset($_POST['xtra_allopts_backup'])) {
//	xtra_check_nonce();
//	xtra_allopts_EXPORT();
}
//. allopts Restore
else if(isset($_POST['xtra_allopts_restore'])) {
	xtra_check_nonce();
	if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
	if ( !current_user_can( 'upload_files' ) ) // Verify the current user can upload files
		wp_die(esc_html__('You do not have permission to upload files.', 'xtra-settings'));
	$allowedMimes = array( 'json' => 'application/json' );
	$fileInfo = wp_check_filetype(basename($_FILES['file']['name']), $allowedMimes);
	if (!empty($fileInfo['type'])) {
		//add_filter( 'upload_dir', 'xtra_upload_dir' );
		//$movefile = wp_handle_upload($_FILES['file'], array('test_form'=>false,'mimes'=>$allowedMimes) );
		$movefile = wp_handle_upload($_FILES['file'], array('test_form'=>false,'test_type'=>false) );
		//remove_filter( 'upload_dir', 'xtra_upload_dir' );

		if ( $movefile && ! isset( $movefile['error'] ) ) {
			//echo "File is valid, and was successfully uploaded.\n";
			//var_dump( $movefile );
			xtra_allopts_IMPORT($movefile['file'],$_POST['xtra_allopts_restore_include_hits']);
			_xtra_add_all_file_insertions();
			$settings_status = '<div class="notice notice-success is-dismissible"><h3>'. esc_html__('XTRA Options restored successfully', 'xtra-settings') .'.</h3></div>';
		} else {
			echo $movefile['error'];
		}
	}
	else {
		$settings_status = '<div class="notice notice-error is-dismissible"><h3>'. esc_html__('This file type is not allowed to upload here', 'xtra-settings') .'.</h3></div>';
	}
	if (file_exists($movefile['file'])) unlink($movefile['file']);
}
//. DB Backup
else if(isset($_POST['xtra_database_backup'])) {
//	xtra_check_nonce();
//	xtra_EXPORT_TABLES(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
}
//. DB Restore
else if(isset($_POST['xtra_database_restore'])) {
	xtra_check_nonce();
	if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
	if ( !current_user_can( 'upload_files' ) ) // Verify the current user can upload files
		wp_die(esc_html__('You do not have permission to upload files.', 'xtra-settings'));
	$allowedMimes = array( 'sql' => 'text/sql' );
	$fileInfo = wp_check_filetype(basename($_FILES['file']['name']), $allowedMimes);
	if (!empty($fileInfo['type'])) {
		//add_filter( 'upload_dir', 'xtra_upload_dir' );
		//$movefile = wp_handle_upload($_FILES['file'], array('test_form'=>false,'mimes'=>$allowedMimes) );
		$movefile = wp_handle_upload($_FILES['file'], array('test_form'=>false,'test_type'=>false) );
		//remove_filter( 'upload_dir', 'xtra_upload_dir' );

		if ( $movefile && ! isset( $movefile['error'] ) ) {
			//echo "File is valid, and was successfully uploaded.\n";
			//var_dump( $movefile );
			xtra_IMPORT_TABLES(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME,$movefile['file']);
			_xtra_add_all_file_insertions();
			$settings_status = '<div class="notice notice-success is-dismissible"><h3>'. esc_html__('Database restored successfully', 'xtra-settings') .'.</h3></div>';
			//$settings_status = '<div class="notice notice-error is-dismissible"><h3>'. esc_html__('This function does not work yet', 'xtra-settings') .'.</h3></div>';
		} else {
			echo $movefile['error'];
		}
	}
	else {
		$settings_status = '<div class="notice notice-error is-dismissible"><h3>'. esc_html__('This file type is not allowed to upload here', 'xtra-settings') .'.</h3></div>';
	}
	if (file_exists($movefile['file'])) unlink($movefile['file']);
}
//. DB Optimize
else if(isset($_POST['xtra_optimize_submit'])) {
	xtra_check_nonce();
	$status = xtra_table_optimize($_POST['hid_tables']);
	if($status) {
		$settings_status = '<div class="notice notice-success is-dismissible"><h3>'. esc_html__('Database optimized successfully', 'xtra-settings') .'.</h3></div>';
	} else {
		$settings_status = '<div class="notice notice-error is-dismissible"><h3>'. esc_html__('Error occured while optimizing', 'xtra-settings') .'.</h3></div>';
	}
}
//. DB Cleanup
else if(isset($_POST['xtra_rem_opt_submit'])) {
	xtra_check_nonce();
	$status = xtra_table_remove($_POST['hid_twsirem']);
	if($status) {
		$settings_status = '<div class="notice notice-success is-dismissible"><h3>'. esc_html__('Cleaning done successfully', 'xtra-settings') .'.</h3></div>';
	} else {
		$settings_status = '<div class="notice notice-error is-dismissible"><h3>'. esc_html__('Error occured while cleaning', 'xtra-settings') .'.</h3></div>';
	}
}
//. Reset Options
else if(isset($_POST['xtra_reset_opt_submit']) && $_POST['hid_reset_all']==1) {
	xtra_check_nonce();
	xtra_delete_all_options();
	$settings_status = '<div class="notice notice-success is-dismissible"><h3>'. esc_html__('All XTRA options deleted and reset successfully', 'xtra-settings') .'.</h3></div>';
}
//xtra_options_save(); //save after submit // added action at shutdown









//. Run Once
if ( get_optionXTRA('xtra_RunOnce', 0) != "2.1.5" ) {
	update_optionXTRA('xtra_opt_show_own', 1 );
	update_optionXTRA('xtra_opt_sticky_tab', 1 );
	update_optionXTRA('xtra_opt_show_colors', 1 );
	update_optionXTRA('xtra_opt_show_opsnum', 1 );
	
	update_optionXTRA('xtra_opt_show_database', 1 );
	update_optionXTRA('xtra_opt_show_crons', 1 );
	update_optionXTRA('xtra_opt_show_plugins', 1 );
	update_optionXTRA('xtra_opt_show_images', 1 );
	//update_optionXTRA('xtra_opt_show_all', 1 );
	
	update_optionXTRA('xtra_hits_enable', 1 );
	update_optionXTRA('xtra_hits_show_side', 1 );
	
	update_optionXTRA('xtra_RunOnce', "2.1.5" );
}








//. own options
if(isset($_POST['xtra_submit_options'])) {
	xtra_check_nonce();
	$xtra_own_options = xtra_make_ownset();
	foreach ($xtra_own_options as $setname => $set) {
		if (empty($set['title'])) update_optionXTRA( $setname, ($_POST[$setname] ? $_POST[$setname] : 0)	);
	}
}
//. CHANGES
if(isset($_POST['xtra_submit_changes'])) {
	xtra_check_nonce();
	$xtra_sets = xtra_make_dataset();
	
	//.. Arrays
	if (is_array($_POST['xtra_plugins_exclude'])) { update_optionXTRA( 'xtra_plugins_exclude', $_POST['xtra_plugins_exclude'] ); }
	else { update_optionXTRA( 'xtra_plugins_exclude', array() ); }
	if (is_array($_POST['xtra_themes_exclude'])) { update_optionXTRA( 'xtra_themes_exclude', $_POST['xtra_themes_exclude'] ); }
	else { update_optionXTRA( 'xtra_themes_exclude', array() ); }
	if (is_array($_POST['xtra_categories_exclude'])) { update_optionXTRA( 'xtra_categories_exclude', $_POST['xtra_categories_exclude'] ); }
	else { update_optionXTRA( 'xtra_categories_exclude', array() ); }
	if (is_array($_POST['xtra_relposts_cat_exclude'])) { update_optionXTRA( 'xtra_relposts_cat_exclude', $_POST['xtra_relposts_cat_exclude'] ); }
	else { update_optionXTRA( 'xtra_relposts_cat_exclude', array() ); }
	if (is_array($_POST['xtra_share_cat_exclude'])) { update_optionXTRA( 'xtra_share_cat_exclude', $_POST['xtra_share_cat_exclude'] ); }
	else { update_optionXTRA( 'xtra_share_cat_exclude', array() ); }
	if (is_array($_POST['xtra_share2_cat_exclude'])) { update_optionXTRA( 'xtra_share2_cat_exclude', $_POST['xtra_share2_cat_exclude'] ); }
	else { update_optionXTRA( 'xtra_share2_cat_exclude', array() ); }
	
	foreach ($xtra_sets as $setname => $set) {
		if ($set['submitX']) {
			$statusa[$setname] = $setname($_POST[$setname]); // call function (update_option in function)
		} elseif ( substr($setname,-6) == "_texta" ) {
			$xtra_txa = array_map( 'sanitize_text_field', explode( "\n", stripslashes_deep($_POST[$setname]) ) );
			$xtra_txa2 = array();
			foreach((array)$xtra_txa as $xtra_txaa) {
				if ($xtra_txaa) $xtra_txa2[] = trim($xtra_txaa);
			}
			update_optionXTRA( $setname, implode( "\n", $xtra_txa2 ) ); // just update_option
		} else {
			update_optionXTRA( $setname, stripslashes_deep(sanitize_text_field($_POST[$setname])) ); // just update_option
		}
		if ($setname == 'xtra_posts_status_color') {
			update_optionXTRA('xtra_posts_status_color1',sanitize_text_field($_POST['xtra_posts_status_color1']));
			update_optionXTRA('xtra_posts_status_color2',sanitize_text_field($_POST['xtra_posts_status_color2']));
			update_optionXTRA('xtra_posts_status_color3',sanitize_text_field($_POST['xtra_posts_status_color3']));
			update_optionXTRA('xtra_posts_status_color4',sanitize_text_field($_POST['xtra_posts_status_color4']));
		}
		if ( xtra_instr($setname,'xtra_hits_exclude_') ) {
			update_optionXTRA($setname.'_abandon',sanitize_text_field($_POST[$setname.'_abandon']));
		}
	}
	if (in_array(false,$statusa)) {
		$settings_status = '<div class="notice notice-error is-dismissible"><h3>'. esc_html__('Error applying some settings', 'xtra-settings') .'.</h3><table class="wp-list-table fixed striped xtra">';
		foreach($statusa as $stsname=>$sts) {
			$settings_status .= "<tr><td>".$xtra_sets[$stsname]['label']."</td><td class='p-left-100'>".($sts?"OK":"Error")."</td></tr>";
		}
		$settings_status .= '</table></div>';
	} else {
		$settings_status = '<div class="notice notice-success is-dismissible"><h3>'. esc_html__('Settings saved successfully', 'xtra-settings') .'<small> &nbsp;&nbsp;&nbsp;('. esc_html__('The effect may only be visible at the next page load.', 'xtra-settings') .')</small></h3></div>';
	}
}
//. Plugins
else if(isset($_POST['xtra_submit_muteplugin'])) {
	xtra_check_nonce();
	if ($_POST['xtra_submit_muteplugin_sure']=="1") {
		$settings_status = '<div class="notice notice-success is-dismissible"><h3>'. esc_html__('The following Plugins were muted', 'xtra-settings') .':</h3>';
	}
	else {
		$settings_status = '<div class="notice notice-error is-dismissible"><h3>'. esc_html__('Sorry, you have not checked the "I am sure" checkbox', 'xtra-settings') .'.</h3>';
	}
	$muted_plugins = get_optionXTRA('xtra_muted_plugins',array());
	foreach ($_POST as $key => $val) {
		if (strpos($key,"muteXplugin")!==FALSE) {
			$pl_file = $val;
			if ($_POST['xtra_submit_muteplugin_sure']=="1") {
				if ( !in_array($pl_file,$muted_plugins) ) $muted_plugins[] = $pl_file;
				//from doc: deactivate_plugins( $plugins, $silent = false ) {}
				deactivate_plugins( $pl_file, true );
			}
			$settings_status .= '<h4>&nbsp; - '.$pl_file.'</h4>';
		}
	}
	update_optionXTRA('xtra_muted_plugins', $muted_plugins);
	$settings_status .= '</div>';
}
else if(isset($_POST['xtra_submit_unmuteplugin'])) {
	xtra_check_nonce();
	if ($_POST['xtra_submit_muteplugin_sure']=="1") {
		$settings_status = '<div class="notice notice-success is-dismissible"><h3>'. esc_html__('The following Plugins were un-muted', 'xtra-settings') .':</h3>';
	}
	else {
		$settings_status = '<div class="notice notice-error is-dismissible"><h3>'. esc_html__('Sorry, you have not checked the "I am sure" checkbox', 'xtra-settings') .'.</h3>';
	}
	$muted_plugins = get_optionXTRA('xtra_muted_plugins',array());
	foreach ($_POST as $key => $val) {
		if (strpos($key,"muteXplugin")!==FALSE) {
			$pl_file = $val;
			if ($_POST['xtra_submit_muteplugin_sure']=="1") {
				array_splice($muted_plugins, array_search($pl_file, $muted_plugins ), 1); //delete item + reindex array
				//from doc: activate_plugins( $plugins, $redirect = '', $network_wide = false, $silent = false ) {}
				activate_plugins( $pl_file, '', false, true );
			}
			$settings_status .= '<h4>&nbsp; - '.$pl_file.'</h4>';
		}
	}
	update_optionXTRA('xtra_muted_plugins', $muted_plugins);
	$settings_status .= '</div>';
}
//. wpopts
else if(isset($_POST['xtra_submit_delwpopts'])) {
	xtra_check_nonce();
	if ($_POST['xtra_submit_delwpopts_sure']=="1") {
		$settings_status = '<div class="notice notice-success is-dismissible"><h3>'. esc_html__('The following WP-Options deleted', 'xtra-settings') .':</h3>';
	}
	else {
		$settings_status = '<div class="notice notice-error is-dismissible"><h3>'. esc_html__('Sorry, you have not checked the "I am sure" checkbox', 'xtra-settings') .'.</h3>';
	}
	foreach ($_POST as $key => $val) {
		if (strpos($key,"delXwpopts")!==FALSE) {
			//$num = str_replace("delXwpopts","",$key);
			//$wpo_name = $_POST['wpoptsName'.$num];
			$wpo_name = $val;
			if ($_POST['xtra_submit_delwpopts_sure']=="1") {
				delete_option( $wpo_name );
			}
			$settings_status .= '<h4>&nbsp; - '.$wpo_name.'</h4>';
		}
	}
	$settings_status .= '</div>';
}
//. Crons
else if(isset($_POST['xtra_submit_delcron'])) {
	xtra_check_nonce();
	if ($_POST['xtra_submit_delcron_sure']=="1") {
		$settings_status = '<div class="notice notice-success is-dismissible"><h3>'. esc_html__('The following Cron Jobs deleted', 'xtra-settings') .':</h3>';
	}
	else {
		$settings_status = '<div class="notice notice-error is-dismissible"><h3>'. esc_html__('Sorry, you have not checked the "I am sure" checkbox', 'xtra-settings') .'.</h3>';
	}
	$cronsa = get_option('cron');
	foreach ($_POST as $key => $val) {
		if (strpos($key,"delXcron")!==FALSE) {
			$num = str_replace("delXcron","",$key);
			$cr_time = $_POST['cronTime'.$num];
			$cr_hook = $_POST['cronHook'.$num];
			$cr_key = $_POST['cronKey'.$num];
			$cr_args = $cronsa[$cr_time][$cr_hook][$cr_key]['args'];
			if ($_POST['xtra_submit_delcron_sure']=="1") {
				wp_unschedule_event( $cr_time, $cr_hook, $cr_args );
			}
			$settings_status .= '<h4>&nbsp; - '.$cr_hook.' - '.date("Y-m-d H:i:s",$cr_time).'</h4>';
		}
	}
	$settings_status .= '</div>';
}
//. Images
else if(isset($_POST['xtra_submit_delimage'])) {
	xtra_check_nonce();
	if ($_POST['xtra_submit_delimage_sure']!="1") {
		$settings_status = '<div class="notice notice-error is-dismissible"><h3>'. esc_html__('Sorry, you have not checked the "I am sure" checkbox', 'xtra-settings') .'.</h3></div>';
	}
	$settings_status = '<div class="notice notice-error is-dismissible"><h3>'. esc_html__('This function does not work yet', 'xtra-settings') .'.</h3></div>';
	// TBD...
}




//---HTML output---

if (isset($_GET['hitlist'])) {
	xtra_check_nonce();
	//. hitlist page
		echo xtra_hits_data_block(0,1,3,0,1,1,1,1,1,1,1000000000,1000000000);
	}
elseif (isset($_GET['wpopts'])) {
	xtra_check_nonce();

	//. wpopts page
	@set_time_limit( 900 ); // 15 minutes per image should be PLENTY

	$xtra_wpopts = wp_load_alloptions();
	//.. check_opt
	foreach($xtra_wpopts as $name => $valu) {
		$i++;
		if ($i <= 10000) $names[] = $name;
		else break;
	}
	$ret = xtra_check_opt($names);

	//.. option list
	$sjs = "
				<span class='m-left-30 small float-right'>
					<span id='xtra_filter_results'></span><label>".esc_html__('Filter', 'xtra-settings') .":<input id='xtra_filter_input' type='text' style='width:120px;' value='' /></label>
					<input class='button button-small' type='button' id='xtra_filter_clear' value='X' />
				</span>
	";
	$html = '';
	if (true) $html .= '
		<div class="wrap _xtra">
			<div class="xtra_full_wr">
				<h1 id="main_title">XTRA Settings<span id="main_version" class="m-left-50 small">'. esc_html__('Current version', 'xtra-settings') .': '.XTRA_VERSION.'</span>
				<span class="m-left-50 dark-red"><b>'. esc_html__('Be careful', 'xtra-settings') .'!</b> '. esc_html__('Only delete those orphans that you are sure are not being used anymore', 'xtra-settings') .'!</span>
				<span class="m-left-50 small"><a class="button button-primary" href="?page=xtra">'. esc_html__('Back to main page', 'xtra-settings') .'</a></span>
				</h1>
				<div class="xtra_box">
					<form action="?page=xtra" method="post" id="wpoptsform">
					<table class="wp-list-table widefat fixed striped xtra"><tr><td>
						<div class="h-550 clear">
							<table class="wp-list-table widefat striped slimrow">
							<tr><td colspan=6>
								<h2>WP-Options'.$sjs.'</h2>
							</tr>
							<tr class="xtra-thead">
								<th></th>
<td><input type="checkbox" title="'. esc_html__('select all orphans', 'xtra-settings') .'" onclick="jQuery(\'#wpoptsform input:checkbox\').not(this).not(\'#xtra_submit_delwpopts_sure\').prop(\'checked\', this.checked);"</td>
								<th>'. esc_html__('Used by', 'xtra-settings') .'</th>
								<th>'. esc_html__('Name', 'xtra-settings') .'</th>
								<th>'. esc_html__('Length', 'xtra-settings') .'</th>
								<th>'. esc_html__('Value', 'xtra-settings') .' [...]</th>
							</tr>
	';
	$i = 0;
	ksort($xtra_wpopts, SORT_NATURAL | SORT_FLAG_CASE);
	$names = array();
	foreach($xtra_wpopts as $name => $valu) {
		$i++;
		$srcbut = ' <a href="#" onclick="xtra_do_ajax(this,\'xtra_get_wpoptdata\',\''.$name.'\');return false;" title="'. esc_html__('Double-check usage in all WP php files', 'xtra-settings') .'.">'.$i.'.</a>';
		$valbut = ' <a href="#" onclick="xtra_do_ajax(jQuery(this).parent(),\'xtra_get_wpoptval\',\''.$name.'\');return false;" >[...]</a>';
		$html .= '
							<tr>
								<td>'.$srcbut.'</td>
								<td>'.(!empty($ret[$name]) ? '' : '<input type="checkbox" name="delXwpopts'.$i.'" value="'.$name.'" />').'</td>
								<td>'.(!empty($ret[$name]) ? ' '.$ret[$name].'' : '<b class="dark-red">'. esc_html__('Orphan', 'xtra-settings') .'?</b>' ).'</td>
								<td class="xbold">'.$name.'</td>
								<td>'.strlen($valu).'</td>
								<td class="wordbreak">'.substr($valu,0,100).(strlen($valu)>100 ? $valbut : '').'</td>
							</tr>
		';
		if ($i <= 300) $names[] = $name;
		//if ($i > 5) break;
	}
	if (true) $html .= '
							</table>
						</div>
					</td></tr></table>
					<div class="butt-wrap">
						<label for="xtra_submit_delwpopts_sure"><input type="checkbox" name="xtra_submit_delwpopts_sure" id="xtra_submit_delwpopts_sure" value="1" />'. esc_html__('I am sure', 'xtra-settings') .'</label><br/><br/>
						<input type="submit" name="xtra_submit_delwpopts" value="'. esc_html__('Delete All Selected Options', 'xtra-settings') .'" class="button button-primary button-hero mybigbutt" />
					</div>
					'.wp_nonce_field( 'mynonce' ).'
					</form>
				</div>
			</div>
		</div>
	';
	echo $html;
}
elseif (isset($_GET['dbtable'])) {
	xtra_check_nonce();
	//. dbtable page
	$ttbl = "wp3k_bp_activity";
	if ( !empty($_GET['dbtable']) ) $ttbl = $_GET['dbtable'];
	$results = $wpdb->get_results("SELECT * FROM ".$ttbl."" , ARRAY_A); //get result as associative array

	$sjs = "
				<span class='m-left-50 small'>
					<span id='xtra_filter_results'></span><label>".esc_html__('Filter', 'xtra-settings') .":<input id='xtra_filter_input' type='text' style='width:120px;' value='' /></label>
					<input class='button button-small' type='button' id='xtra_filter_clear' value='X' />
				</span>
	";
	$dbsel = "<select style='font-family: Consolas,Menlo,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New,monospace,sans-serif;' onchange='location.href=this.value;'>";
	$sql = "SELECT TABLE_NAME AS table_name, TABLE_ROWS as table_rows, DATA_FREE as data_overload, (data_length + index_length) as data_size FROM information_schema.TABLES WHERE table_schema = '".DB_NAME."'";
	$res = $wpdb->get_results( $sql, OBJECT );
	foreach ($res as $tres) {
		$dbsel .= "<option value='".wp_nonce_url('?page=xtra&dbtable='.$tres->table_name,'mynonce')."' ".($ttbl==$tres->table_name ? "SELECTED" : "").">".$tres->table_name;
		$spaci = str_repeat( "&nbsp;", abs(40-strlen($tres->table_name))*1 );
		if (true) $dbsel .= $spaci."[".$tres->table_rows."]";
		$dbsel .= "</option>";
	}
	$dbsel .= "</select>";
	
	$html = '';
	if (true) $html .= '
		<div class="wrap _xtra">
			<div class="xtra_full_wr">
				<h1 id="main_title">XTRA Settings<span id="main_version" class="m-left-50 small">'. esc_html__('Current version', 'xtra-settings') .': '.XTRA_VERSION.'</span><span class="m-left-50 small"><a class="button button-primary" href="?page=xtra">'. esc_html__('Back to main page', 'xtra-settings') .'</a></span></h1>
				<div class="xtra_box">
					<table class="wp-list-table widefat fixed striped xtra"><tr><td>
						<div class="h-550 clear">
							<table class="wp-list-table widefat striped slimrow">
	';
	$i = 0;
	if (empty($results)) $html .= '<tr><td><h2>'. esc_html__('DB Table', 'xtra-settings') .': '.$dbsel."<span class='m-left-50'>".(count((array)$results))." '. esc_html__('records', 'xtra-settings') .'</span></h2>";
	foreach($results as $result) {
		$i++;
		if ($i == 1) {
			$html .= '<tr><td colspan='.count((array)$result).'><h2>'. esc_html__('DB Table', 'xtra-settings') .': '.$dbsel."<span class='m-left-50'>".(count((array)$results))." '. esc_html__('records', 'xtra-settings') .'</span>".$sjs.'</h2>';
			$html .= '
			<tr class="xtra-thead">';
			foreach($result as $key => $val) {
				$html .= '<th>'.esc_html($key).'</th>';
			}
			$html .= '</tr>';			
		}
		$html .= '<tr>';
		foreach($result as $key => $val) {
			if (strlen($val) > 300) $html .= '<td class="wordbreak">'.esc_html($val).'</td>';
			else $html .= '<td>'.esc_html($val).'</td>';
		}
		$html .= '</tr>';
	}
	if (true) $html .= '
							</table>
						</div>
					</td></tr></table>
				</div>
			</div>
		</div>
	';
	echo $html;
}
else
{
//. all other pages
	
	

if ( !get_optionXTRA('xtra_opt_disable_boxresize_js') ) {
	global $js_h400, $js_w2;
	$js_h400 = "jQuery(this).css('max-height','400px');jQuery(this).css('height','400px');event.stopPropagation();";
	$js_h400back = "jQuery('.max-h-150').css('max-height','150px');jQuery('.texta-150').css('height','150px');";
	$js_w2 = "jQuery('.xtra_left').addClass('xtra_left2');jQuery('.xtra_right').addClass('xtra_right2');setTimeout(function(){window.dispatchEvent(new Event('resize'))}, 500);";
	$js_w2back = "jQuery('.xtra_left').removeClass('xtra_left2');jQuery('.xtra_right').removeClass('xtra_right2');setTimeout(function(){window.dispatchEvent(new Event('resize'))}, 500);";
}
if ($xtra_do_memlog) xtra_memlog("start HTML output");
?>
<div class="wrap _xtra" onclick="<?php echo $js_h400back.$js_w2back;?>">
    <div class="xtra_full_wr">
	<?php
	//---Left Column
	$vertical = "";
	if (get_optionXTRA('xtra_opt_vertical')) $vertical = "vertical-tabs";
	?>
    <div class="xtra_left <?php echo $vertical; ?>">

	<h1 id="main_title">XTRA Settings<span id="main_version" class="m-left-50 small"><?php esc_html_e('Current version', 'xtra-settings'); ?>: <?php echo XTRA_VERSION;?></span>
	<span class="float-right small" id="searchdiv"><?php esc_html_e('Search', 'xtra-settings'); ?>:<input type="text" id="xtra_searchall_input" value="" /><input type="button" class="button-clear" id="xtra_searchall_clear" value="X"></span>
	</h1>
	<?php if (isset($settings_status)) echo $settings_status;?>
	<h2 class="nav-tab-wrapper <?php if (get_optionXTRA('xtra_opt_sticky_tab')) echo "xtra_sticky"; ?>">
		<?php
		$thisHtml = xtra_make_html();
		$xtra_tabcols = xtra_get_tab_colors();
		//. Tabs
		//.. Left Tabs
		$i = 0;
		$oldtab = "";
		foreach ($xtra_tabs as $thistab) {
			if ($thistab!=$oldtab) {
				$addi = "";
				if ($xtra_seltab===(string)$i) $addi = "nav-tab-active";
				$tsty = ' style="border-top: 4px solid '.$xtra_tabcols[$thistab]['color'].';"';
				echo '<a class="nav-tab '.$addi.'" href="" activate="'.$i.'"'.$tsty.'>'.$thistab.'</a>';
				$oldtab = $thistab;
				$i++;
			}
		}
		
		//.. Right Tabs
		$xtra_right_tabs = array(
			0	=> Array('show_database' 	,'database'	,''. esc_html__('Database', 'xtra-settings') .'')	,
			1	=> Array('show_crons' 		,'crons'	,''. esc_html__('Crons', 'xtra-settings') .'')		,
			2	=> Array('show_plugins' 	,'plugins'	,''. esc_html__('Plugins', 'xtra-settings') .'')	,
			3	=> Array('show_images' 		,'images'	,''. esc_html__('Images', 'xtra-settings') .'')		,
			4	=> Array('show_own'	 		,'own'		,'XTRA')											,
			5	=> Array('show_all' 		,'*'		,''. esc_html__('All', 'xtra-settings') .'')		,
		);
		krsort($xtra_right_tabs);
		if (get_optionXTRA('xtra_opt_vertical')) ksort($xtra_right_tabs);
		foreach ($xtra_right_tabs as $key => $val) {
			$tshow = $val[0];
			$$tshow = get_optionXTRA('xtra_opt_'.$val[0]);
			if ( $$tshow )
				echo '<a class="nav-tab float-right b-top-4-ccc '.($xtra_seltab==$val[1]?"nav-tab-active":"").'" href="" activate="'.$val[1].'">'.$val[2].'</a>';
		}
		echo '<a style="display:none;background:#f1f1f1 !important;" class="nav-tab nav-tab-search b-top-4-ccc" href="" activate="none">'. esc_html__('Search', 'xtra-settings') .'</a>';

		?>
	</h2>
	<div id="xtra_sticky_spacer"></div>


<div class="xtra-tab-wrapper">


	<form action="?page=xtra" method="post">
	<?php
	//. All Settings
	echo $thisHtml;
	wp_nonce_field( 'mynonce' );
	?>
    <div class="xtra_opt_submit butt-wrap <?php if (strlen($xtra_seltab)>1) echo "disp-none"; ?>">
	<input type="submit" name="xtra_submit_changes" value="<?php esc_html_e('Save All Options', 'xtra-settings'); ?>" class="button button-primary button-hero mybigbutt" /></div>
	<input type="hidden" name="xtra_submit_last_seltab" id="xtra_submit_last_seltab" value="<?php echo $xtra_seltab; ?>" />
	</form>
<?php
if ($xtra_do_memlog) xtra_memlog("All Settings");
?>


	<?php
	//. Database Backup
	if ( $show_database ) {
	?>
	<div class="xtra_box xtra_tabbes xtra_tabbeID_database <?php if (($xtra_seltab=="database" || $xtra_seltab==="*") && @$_REQUEST['fetch']!=1) echo "active"; ?>">
		<h2 class="icbefore dashicons-download"><?php esc_html_e('Database Backup', 'xtra-settings'); ?></h2>
		<table class="wp-list-table widefat fixed striped xtra">

		<tr><th><?php esc_html_e('Backup the whole WordPress database', 'xtra-settings'); ?><br/><span>(<?php esc_html_e('Download in an .sql file', 'xtra-settings'); ?>)</span></th><td>
	<form action="?page=xtra" method="post">
		<p class="submit"><input type="submit" name="xtra_database_backup" value="<?php esc_html_e('Backup DB Now', 'xtra-settings'); ?>" class="button button-primary" /></p>
		<?php wp_nonce_field( 'mynonce' ); ?>
		<input type="hidden" name="xtra_submit_last_seltab" id="xtra_submit_last_seltab" value="database" />
	</form>
		</td></tr>

	<?php
	//. Database Restore
	?>
		<tr><th><?php esc_html_e('Restore database', 'xtra-settings'); ?><br/><span>(<?php esc_html_e('From exported .sql file', 'xtra-settings'); ?>)</span></th><td>
	<form action="?page=xtra" method="post" enctype="multipart/form-data" onsubmit="if(!jQuery('#file').val()){alert('Upload file is not selected!');return false};return confirm('<?php esc_html_e('Do you really want to DELETE all Database & RESTORE from this file?', 'xtra-settings'); ?> \n\n'+jQuery('#file').val().replace(/^.*\\/, ''));">
		<p><label for="file"><?php esc_html_e('Select an .sql file to upload', 'xtra-settings'); ?>:<br/><input type="file" id="file" name="file" accept=".sql" value="" /></label></p>
		<p class="submit"><input type="submit" name="xtra_database_restore" value="<?php esc_html_e('Restore DB Now', 'xtra-settings'); ?>" class="button button-primary" /></p>
		<?php wp_nonce_field( 'mynonce' ); ?>
		<input type="hidden" name="xtra_submit_last_seltab" id="xtra_submit_last_seltab" value="database" />
	</form>
		</td></tr>
	<?php
	?>
		</table>
    </div>


	<?php
	//. Database Cleanup
	$a1 = '<a href="admin.php?page=xtra&xfetch=';
	$ax = 1;
	$a2 = '">';
	$a3 = '</a>';
	$tnames = array(
		''. esc_html__('Revisions', 'xtra-settings') .'',
		''. esc_html__('Auto-Drafts', 'xtra-settings') .'',
		''. esc_html__('Trash Posts', 'xtra-settings') .'',
		''. esc_html__('Spam Comments', 'xtra-settings') .'',
		''. esc_html__('Disapproved Comments', 'xtra-settings') .'',
		''. esc_html__('Transient Options', 'xtra-settings') .'<br/><span>('. esc_html__('site and feed', 'xtra-settings') .')',
		''. esc_html__('Expired Transients', 'xtra-settings') .'<br/><span>('. esc_html__('more than 7 days', 'xtra-settings') .')',
	);
	?>
    <div class="xtra_box xtra_tabbes xtra_tabbeID_database  <?php if (($xtra_seltab=="database" || $xtra_seltab==="*") && @$_REQUEST['fetch']!=1) echo "active"; ?>">
    <h2 class="icbefore dashicons-trash"><?php esc_html_e('Database Cleanup', 'xtra-settings'); ?></h2>
    <table class="wp-list-table widefat fixed striped xtra">
	<?php
	$i = 0;
	foreach ($tnames as $tname) {
		echo '<tr><th>'. esc_html__('Delete', 'xtra-settings') .' '.$tname.'</th><td><form action="?page=xtra" method="post"><input type="hidden" name="hid_twsirem" value="'.++$i.'" />
			<input type="submit" name="xtra_rem_opt_submit" value="'. esc_html__('Clean Now', 'xtra-settings') .'" class="button button-primary" />
			&nbsp;&nbsp;&nbsp; '. esc_html__('current', 'xtra-settings') .': '.$a1.$ax++.$a2.xtra_table_remove_count($i).$a3.'';
		wp_nonce_field( 'mynonce' );
		echo '<input type="hidden" name="xtra_submit_last_seltab" id="xtra_submit_last_seltab" value="database" />';
		echo '</form></td></tr>';
		if(isset($_REQUEST['xfetch']) && $_REQUEST['xfetch']==$i) echo "<tr><td colspan=2><textarea class='texta-350'>".xtra_table_remove_show($i)."</textarea></td></tr>";
	}
	?>
    </table>
    </div>
<?php
if ($xtra_do_memlog) xtra_memlog("Database 1");
?>



	<?php
	//. Optimize Database
	?>
	<div class="xtra_box xtra_tabbes xtra_tabbeID_database  <?php if ($xtra_seltab=="database" || $xtra_seltab==="*") echo "active"; ?>">
		<h2 class="icbefore dashicons-admin-generic"><?php esc_html_e('Optimize Database and Tables', 'xtra-settings'); ?></h2>
		<?php if(!isset($_REQUEST['fetch']) || $_REQUEST['fetch']!=1){ ?>
			<table class="wp-list-table widefat fixed striped xtra">
			<tr><th><?php esc_html_e('Show database tables, sizes and overload', 'xtra-settings'); ?><br/><span>(<?php esc_html_e('Optimize button will be at the bottom of the table list', 'xtra-settings'); ?>)</span></th><td>
				<p class="submit"><a href="admin.php?page=xtra&fetch=1" class="button button-primary" /><?php esc_html_e('Show Tables', 'xtra-settings'); ?></a></p>
			</td></tr>
			</table>
		<?php } else { ?>
			<form action="?page=xtra" method="post">
				<p class="submit"><input type="submit" name="xtra_submit_hide_tables" value="<?php esc_html_e('Hide Tables', 'xtra-settings'); ?>" class="button button-primary" /></p>
				<input type="hidden" name="xtra_submit_last_seltab" id="xtra_submit_last_seltab" value="database" />
				<?php wp_nonce_field( 'mynonce' ); ?>
			</form>
		<?php } ?>
		<?php
		if(isset($_REQUEST['fetch']) && $_REQUEST['fetch']==1){

		$sql = "SELECT TABLE_NAME AS table_name, DATA_FREE as data_overload, (data_length + index_length) as data_size FROM information_schema.TABLES WHERE table_schema = '".DB_NAME."'";
		$results = $wpdb->get_results( $sql, OBJECT );
		$total_size = 0;
		$total_overload_size = 0;
		//print_r($results);
		?>
		<table class="wp-list-table widefat fixed striped xtra slimrow">
			<tr>
				<th><?php esc_html_e('Name', 'xtra-settings'); ?></th>
				<th><?php esc_html_e('Size', 'xtra-settings'); ?></th>
				<th><?php esc_html_e('Overload', 'xtra-settings'); ?></th>
				<th><?php esc_html_e('Overload', 'xtra-settings'); ?> %</th>
			</tr>
			<?php
			$table_str = "";
			foreach($results as $result){
				$table_str .= $result->table_name.',';
				?>
				<tr>
				  <td><?php echo "<a href='".wp_nonce_url('?page=xtra&dbtable='.$result->table_name,'mynonce')."'>".$result->table_name."</a>";?></td>
				  <td><?php $total_size += $result->data_size; echo xtra_getSizes($result->data_size);?></td>
				  <td class="xtra_box <?php if($result->data_overload>0){ echo 'xtra_error';} ?>"><?php $total_overload_size += $result->data_overload; echo $result->data_overload>0 ? xtra_getSizes($result->data_overload) : "-";?></td>
				  <td class="xtra_box <?php if($result->data_overload>0){ echo 'xtra_error';} ?>"><?php $total_data_size += $result->data_size; echo ($result->data_overload>0 && $result->data_size>0) ? round($result->data_overload/$result->data_size*100)." %" : "-";?></td>
				</tr>
			<?php } //foreach ?>
			<tr>
				<th><?php esc_html_e('Total', 'xtra-settings'); ?></th>
				<th><?php echo xtra_getSizes($total_size); ?></th>
				<th class="xtra_box <?php if($total_overload_size>0){ echo 'xtra_error';} ?>"><?php echo $total_overload_size ? xtra_getSizes($total_overload_size) : "-"; ?></th>
				<th class="xtra_box <?php if($total_overload_size>0){ echo 'xtra_error';} ?>"><?php echo ($total_overload_size && $total_data_size) ? round($total_overload_size/$total_data_size*100)." %" : "-"; ?></th>
			</tr>
		</table>
		<form action="?page=xtra" method="post">
			<input type="hidden" name="hid_tables" value="<?php echo rtrim($table_str,',');?>" />
			<p class="submit"><input type="submit" name="xtra_optimize_submit" value="<?php esc_html_e('Optimize Tables', 'xtra-settings'); ?>" class="button button-primary" /></p>
			<input type="hidden" name="xtra_submit_last_seltab" id="xtra_submit_last_seltab" value="database" />
			<?php wp_nonce_field( 'mynonce' ); ?>
		</form>
		<?php
		} //if(@$_REQUEST...
		?>
    </div>
<?php
if ($xtra_do_memlog) xtra_memlog("Database 2");
	}
?>





	<?php
	//. Crons
	if ( $show_crons ) {
	?>
	<?php

	$html = '<tr class="xtra-thead">
		<th scope="row">'. esc_html__('Timestamp', 'xtra-settings') .'</th>
		<td>'. esc_html__('Hook', 'xtra-settings') .'</td>
		<td>'. esc_html__('Options', 'xtra-settings') .'</td>
		<td><input type="checkbox" onclick="jQuery(\'#cronform input:checkbox\').not(this).not(\'#xtra_submit_delcron_sure\').prop(\'checked\', this.checked);"</td>
	</tr>
	';
	$html .= xtra_get_crons("html");
	global $xtra_get_crons_count;
	/*
		<h2 class="icbefore dashicons-clock"><?php printf(esc_html__('Listing %s Active Cron Jobs', 'xtra-settings'),$xtra_get_crons_count); ?><span class="m-left-50 small"><?php echo "".esc_html__('Server time', 'xtra-settings') .": ".date_i18n("Y-m-d H:i:s");?></span></h2>
	*/
	?>
	<div class="xtra_box xtra_tabbes xtra_tabbeID_crons <?php if ($xtra_seltab=="crons" || $xtra_seltab==="*") echo "active"; ?>">
		<h2 class="icbefore dashicons-clock"><?php printf(esc_html__('Listing %s Active Cron Jobs', 'xtra-settings'),$xtra_get_crons_count); ?><span class="m-left-50 small"><?php echo "".esc_html__('Server time', 'xtra-settings') .": ".date("Y-m-d H:i:s", time())."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ".esc_html__('Local time', 'xtra-settings') .": ".wp_date("Y-m-d H:i:s", time())."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ".esc_html__('(diff', 'xtra-settings') .': '.round(( strtotime(wp_date("Y-m-d H:i:s", time())) - strtotime(date("Y-m-d H:i:s", time())) )/60).' min)';?></span></h2>
		<form action="?page=xtra" method="post" id="cronform">
		<table class="wp-list-table widefat striped xtra slimrow">
			<?php echo $html; ?>
		</table>
		<div class="butt-wrap">
		<label for="xtra_submit_delcron_sure"><input type="checkbox" name="xtra_submit_delcron_sure" id="xtra_submit_delcron_sure" value="1" /><?php esc_html_e('I am sure', 'xtra-settings'); ?></label><br/><br/>
		<input type="submit" name="xtra_submit_delcron" value="<?php esc_html_e('Delete All Selected Cron Jobs', 'xtra-settings'); ?>" class="button button-primary button-hero mybigbutt" />
		</div>
		<?php wp_nonce_field( 'mynonce' ); ?>
		</form>
	</div>
	<?php
if ($xtra_do_memlog) xtra_memlog("Crons");
	}
	?>




	<?php
	//. Plugins
	if ( $show_plugins ) {
	?>
	<?php
	if ( ! function_exists( 'get_plugins' ) ) { require_once ABSPATH . 'wp-admin/includes/plugin.php'; }
	$all_plugins = get_plugins();
	$muted_plugins = get_optionXTRA('xtra_muted_plugins',array());

	//	[Name]	[PluginURI]	[Version]	[Description]	[Author]	[AuthorURI]	[TextDomain]	[DomainPath]	[Network]	[Title]	[AuthorName]
	$html = '<tr class="xtra-thead">
		<td></td>
		<th scope="row">'. esc_html__('Name', 'xtra-settings') .'</th>
		<td>'. esc_html__('Version', 'xtra-settings') .'</td>
		<td align="center">'. esc_html__('Links', 'xtra-settings') .'</td>
		<td><input type="checkbox" onclick="jQuery(\'#pluginform input:checkbox\').not(this).not(\'#xtra_submit_muteplugin_sure\').prop(\'checked\', this.checked);"</td>
	</tr>
	';
	$i = 0;
	$a = 0;
	foreach ($all_plugins as $pl_file=>$pl_data) {
		if (!isset($pl_data['Name'])) continue;
		$i++;
		$t0 = $t1 = $t2 = $t3 = $t4 = $t5 = "";
		$t1 = $pl_data['Name'];
		$t2 = $pl_data['Version'];
		if ($pl_data['PluginURI']) $t3 = "<a target='_blank' title='".esc_html__('Plugin Page', 'xtra-settings') ."' href='".$pl_data['PluginURI']."'><div class='dashicons dashicons-admin-plugins'></div></a>&nbsp;";
		if ($pl_data['AuthorURI']) $t3 .= "<a target='_blank' title='".esc_html__('Author Page', 'xtra-settings') .": ".$pl_data['Author']."' href='".$pl_data['AuthorURI']."'><div class='dashicons dashicons-admin-users'></div></a>&nbsp;";
		$t3 .= "<a target='_blank' title='".esc_html__('Plugin File', 'xtra-settings') .": ".$pl_file."\nslug: ".dirname( plugin_basename( $pl_file ) )."'><div class='dashicons dashicons-media-default'></div></a>&nbsp;";
		$t3 .= "<a target='_blank' title='".wordwrap($pl_data['Description'],50)."'><div class='dashicons dashicons-info'></div></a>";
		$t4 = '<input type="checkbox" name="muteXplugin'.$i.'" id="muteXplugin'.$i.'" value="'.$pl_file.'" />';

		$sty = "";
		$sty2 = "border-active";
		$styx = "";
		$curval = 1;
		$a++;
		if (!is_plugin_active($pl_file)) {
			$a--;
			$curval = 0;
			$sty = "text-disabled";
			$sty2 = "";
			if ( !in_array($pl_file,$muted_plugins) ) $t4 = "";
		}
		else if (stripos($pl_data['Name'],"XTRA")===0) $t4 = "";
		if ( in_array($pl_file,$muted_plugins) ) {
			$sty = "dark-red";
			$styx = "style='text-decoration: line-through;'";
		}
		//$t0 = '<input type="checkbox" name="plg'.$i.'" id="plg'.$i.'" value="1" '.(($curval==0)?'':'checked="checked"').' />';
		$html .= "<tr>
			<td class='text-right $sty $sty2'>".($t0)."</td>
			<th scope='row' class='$sty' $styx>".($t1)."</th>
			<td class='$sty'>".($t2)."</td>
			<td align='right' class='$sty'>".($t3)."</td>
			<td class='$sty'>".($t4)."</td>
		</tr>
		";
//			<td style='$sty'>".($t5)."</td>
//			<td style='$sty'>".($xres)."</td>
	}
	?>
	<div class="xtra_box xtra_tabbes xtra_tabbeID_plugins <?php if ($xtra_seltab=="plugins" || $xtra_seltab==="*") echo "active"; ?>">
		<h2 class="icbefore dashicons-admin-plugins"><?php printf(esc_html__('Listing %s Installed Plugins', 'xtra-settings'),$i); ?> <span class="m-left-50 small">(<?php printf(esc_html__('%s active, %s in-active', 'xtra-settings'),$a,($i-$a)); ?>)</span></h2>
		<form action="?page=xtra" method="post" id="pluginform">
		<table class="wp-list-table widefat striped xtra slimrow">
			<?php echo $html; ?>
		</table>
		<div class="butt-wrap">
		<p><span class="xbold"><?php esc_html_e('Mute plugin means temporarily switch it off', 'xtra-settings'); ?>.</span> 
		<br/><?php esc_html_e('Mute will de-activate the plugin without running the defined deactivate-action of the plugin.', 'xtra-settings'); ?> 
		<br/><?php esc_html_e('Un-Mute will activate the muted plugin without running the defined activate-action of the plugin.', 'xtra-settings'); ?>
		<br/>(<?php esc_html_e('Only active plugins can be muted - except for XTRA. Only muted plugins can be un-muted.', 'xtra-settings'); ?>)</p>
		<label for="xtra_submit_muteplugin_sure"><input type="checkbox" name="xtra_submit_muteplugin_sure" id="xtra_submit_muteplugin_sure" value="1" /><?php esc_html_e('I am sure', 'xtra-settings'); ?></label><br/><br/>
		<input type="submit" name="xtra_submit_muteplugin" value="<?php esc_html_e('Mute Selected Plugins', 'xtra-settings'); ?>" class="button button-primary button-hero mybigbutt" />
		<input type="submit" name="xtra_submit_unmuteplugin" value="<?php esc_html_e('Un-Mute Selected Plugins', 'xtra-settings'); ?>" class="button button-primary button-hero mybigbutt" />
		</div>
		<?php wp_nonce_field( 'mynonce' ); ?>
		</form>
	</div>
	<?php
if ($xtra_do_memlog) xtra_memlog("Plugins");
	}
	?>


	<?php
	//. Images
	if ( $show_images ) {
	?>
	<?php

	$html = '<tr class="xtra-thead">
		<td>'. esc_html__('Path', 'xtra-settings') .'</td>
		<td>'. esc_html__('Name', 'xtra-settings') .'</td>
		<td align="center">'. esc_html__('W', 'xtra-settings') .'</td>
		<td>'. esc_html__('H', 'xtra-settings') .'</td>
		<td></td>
		<td align="center">'. esc_html__('Size', 'xtra-settings') .'</td>
		<td align="center">'. esc_html__('Mod', 'xtra-settings') .'</td>
		<td>'. esc_html__('Links', 'xtra-settings') .'</td>
		<td><input type="checkbox" onclick="jQuery(\'#imageform input:checkbox\').not(this).not(\'#xtra_submit_delimage_sure\').not(\'#xtra_ajax_sure\').not(\'#xtra_ajax_convGIF\').not(\'#xtra_ajax_convBMP\').not(\'#xtra_ajax_convPNG\').prop(\'checked\', this.checked);"</td>
	</tr>
	';
	$html .= xtra_get_images("html");
	global $xtra_get_images_count;
	global $xtra_get_images_size;
	?>
	<div class="xtra_box xtra_tabbes xtra_tabbeID_images <?php if ($xtra_seltab=="images" || $xtra_seltab==="*") echo "active"; ?>">
		<form action="?page=xtra" method="post" id="imageform">
		<h2 class="icbefore dashicons-format-gallery"><?php printf(esc_html__('Listing %s Images', 'xtra-settings'),$xtra_get_images_count); ?> <?php echo "(".round($xtra_get_images_size/1024/1024,0)." MB".")"; ?>
		<input type="submit" name="xtra_refresh_images" value="<?php esc_html_e('Refresh Image List', 'xtra-settings'); ?>" class="button button-primary bold float-right" />
		</h2>
		<div class="xtra h-350 clear">
		<table class="wp-list-table widefat striped xtra slimrow">
			<?php
			echo $html;
			?>
		</table>
		</div>

		<table class="wp-list-table widefat xtra" style="margin-top:-14px;">
		<tr>
		<td class="xbold">
			<br/>
			<?php esc_html_e('Compression Settings', 'xtra-settings'); ?>:
		<td align="right">
			<br/>
			<?php esc_html_e('Compression quality', 'xtra-settings'); ?>:<input type="text" size="1" id="xtra_ajax_compPRC" value="<?php echo get_optionXTRA('xtra_custom_jpeg_quality_num',82);?>" />%
		<td>
			<br/>
		<?php if (extension_loaded('gd') && function_exists('gd_info') ) { ?>
			<input type="radio" name="xtra_ajax_compM" id="xtra_ajax_compM1" value="1" onchange="jQuery('#xtra_ajax_resize_span').toggle(!this.checked);" />PHP GD <?php esc_html_e('method', 'xtra-settings'); ?>
		<br/>
		<?php } ?>
			<input type="radio" name="xtra_ajax_compM" id="xtra_ajax_compM2" value="2" checked="checked" onchange="jQuery('#xtra_ajax_resize_span').toggle(this.checked);" />WP-Editor method<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<?php esc_html_e('recommended', 'xtra-settings'); ?>)
		<td>
			<br/>
			<span id="xtra_ajax_resize_span">
			<strong><?php esc_html_e('Resize Large Images', 'xtra-settings'); ?>:</strong>
			<br/>
			<?php esc_html_e('max width', 'xtra-settings'); ?>:<input type="text" size="2" id="xtra_ajax_maxW" value="1600" />px
			&nbsp;&nbsp;&nbsp;
			<?php esc_html_e('max height', 'xtra-settings'); ?>:<input type="text" size="2" id="xtra_ajax_maxH" value="1600" />px
			<br/>
			<i>(<?php esc_html_e('use 0 for not resizing', 'xtra-settings'); ?>)</i>
			</span>
		<tr>
		<td colspan=4 style="border-top: 1px solid #CCC;">
		<tr id="xtra_ajax_buttonsDiv">
		<td class="xbold">
				<?php esc_html_e('With Selected Images', 'xtra-settings'); ?>:
		<td align="right">
				<input type="checkbox" id="xtra_ajax_sure" value="1" /><label for="xtra_ajax_sure"><?php esc_html_e('I am sure', 'xtra-settings'); ?>:</label>
		<td colspan=2>
				<input type="button" id="xtra_ajax_compressButton" value="<?php esc_html_e('Compress Images', 'xtra-settings'); ?>" title="...with auto-backup" class="button button-primary bold" onclick="xtra_do_images('Compress');" />
				&nbsp;&nbsp;&nbsp;
				<input type="button" id="xtra_ajax_restoreButton" value="<?php esc_html_e('Restore from Backup', 'xtra-settings'); ?>" class="button button-primary bold" onclick="xtra_do_images('Restore');" />
				&nbsp;&nbsp;&nbsp;
				<input type="button" id="xtra_ajax_delbackupButton" value="<?php esc_html_e('Delete Backup File', 'xtra-settings'); ?>" class="button button-primary bold" onclick="xtra_do_images('Delete Backup');" />
				&nbsp;&nbsp;&nbsp;
				<input type="button" id="xtra_ajax_delimageButton" value="<?php esc_html_e('Delete Image File', 'xtra-settings'); ?>" class="button button-primary bold" onclick="xtra_do_images('Delete Image');" />
		<tr id="xtra_ajax_buttonsDiv2">
		<td class="xbold">
		<td>
		<td colspan=2>
				<input type="button" id="xtra_ajax_regenButton" value="<?php esc_html_e('Regenerate Sizes & Thumbs', 'xtra-settings'); ?>" class="button button-primary bold" onclick="xtra_do_images('Regenerate Thumbnails');" />
				<strong><--<?php esc_html_e('ATTENTION: No backup here! This can not be undone!', 'xtra-settings'); ?></strong>
		<tr>
		<td class="xbold" colspan=4>
			<div class="textcenter"><input type="button" id="xtra_ajax_stopButton" value="<?php esc_html_e('Stop Compress', 'xtra-settings'); ?>" style="display:none;" class="button button-primary button-hero mybigbutt" onclick="xtra_do_stop();" /></div>
			<div id="xtra_ajax_resultsDiv" class="xtra_ajax_response"></div>
		</table>
		<?php wp_nonce_field( 'mynonce' ); ?>
		</form>
	</div>
	<?php
/*		<br/>
		<br/>
		<input type="checkbox" id="xtra_ajax_convGIF" value="1"><label for="xtra_ajax_convGIF">Convert GIF to JPG</label>&nbsp;&nbsp;&nbsp;
		<input type="checkbox" id="xtra_ajax_convBMP" value="1"><label for="xtra_ajax_convBMP">Convert BMP to JPG</label>&nbsp;&nbsp;&nbsp;
		<input type="checkbox" id="xtra_ajax_convPNG" value="1"><label for="xtra_ajax_convPNG">Convert PNG to JPG</label>
*/
//		<label for="xtra_submit_delimage_sure"><input type="checkbox" name="xtra_submit_delimage_sure" id="xtra_submit_delimage_sure" value="1" />I am sure</label><br/><br/>
//		<input type="submit" name="xtra_submit_delimage" value="Delete All Selected Images" class="button button-primary button-hero mybigbutt" />
if ($xtra_do_memlog) xtra_memlog("Images");
	}
	?>


	<?php
	//. Images sizes
	if ( $show_images ) {
	?>
	<?php
	$all_image_sizes = xtra_get_image_sizes();
	$html = '<tr class="xtra-thead">
		<td></td>
		<th scope="row">'. esc_html__('Name', 'xtra-settings') .'</th>
		<td>'. esc_html__('Width', 'xtra-settings') .'</td>
		<td>'. esc_html__('Height', 'xtra-settings') .'</td>
		<td>'. esc_html__('Crop', 'xtra-settings') .'</td>
		<td><input type="checkbox" onclick="jQuery(\'#imgsizform input:checkbox\').not(this).not(\'#xtra_submit_imgsiz_sure\').prop(\'checked\', this.checked);"</td>
	</tr>
	';
	$i = 0;
	foreach ($all_image_sizes as $ims_name=>$ims_data) {
		$i++;
		$t0 = $t1 = $t2 = $t3 = $t4 = $t5 = "";
		$t1 = $ims_name;
		$t2 = $ims_data['width'];
		$t3 = $ims_data['height'];
		$t4 = $ims_data['crop'];
		$t5 = '<input type="checkbox" name="imgsiz'.$i.'" id="imgsiz'.$i.'" value="1" />';

		$sty = "";
		$sty2 = ""; //"border-left: 4px solid #00a0d2;";
		$curval = 1;

		$html .= "<tr>
			<td class='text-right'>".($t0)."</td>
			<th scope='row'>".($t1)."</th>
			<td>".($t2)."</td>
			<td>".($t3)."</td>
			<td>".($t4)."</td>
			<td>".($t5)."</td>
		</tr>
		";
	}
	?>
	<div class="xtra_box xtra_tabbes xtra_tabbeID_images <?php if ($xtra_seltab=="images" || $xtra_seltab==="*") echo "active"; ?>">
		<h2 class="icbefore dashicons-format-gallery"><?php printf(esc_html__('Listing %s Image Sizes', 'xtra-settings'),$i); ?></h2>
		<form action="?page=xtra" method="post" id="imgsizform">
		<table class="wp-list-table widefat striped xtra slimrow">
			<?php echo $html; ?>
		</table>
		<div class="butt-wrap">
		</div>
		<?php //wp_nonce_field( 'mynonce' ); ?>
		</form>
	</div>
	<?php
if ($xtra_do_memlog) xtra_memlog("Image sizes");
	}
	?>






	<?php
	//. XTRA
	$xtra_own_options = xtra_make_ownset();
	$html_own_inner = "";
	foreach ($xtra_own_options as $oname => $oset) {
		if ( !( $oname!="xtra_hits_enable" || (get_optionXTRA('xtra_hits_enable') && get_optionXTRA('xtra_hits_show_side')) ) ) continue;
		$isset = get_optionXTRA( $oname );
		$sth = 'style="border-left: 4px solid transparent;"';
		//$tc = '#CCCCCC';
		$tc = '#7BB3CF';
		$sty = 'style="border-left: 4px solid '.$tc.';"';
		$stx = 'style="border-left: 4px solid transparent;"';
		if ($isset) $stx = $sty;
		if (empty($oset['title'])) {
			$html_own_inner .= "<tr><th $stx>".$oset['label']."</th><td>";
			$html_own_inner .= "<input type='checkbox' name='".$oname."' value='1' ". ($isset==1 ? "checked='checked'" : "") ." />";
			$html_own_inner .= "</td></tr>";
		}
		elseif ($oset['title']==2) {
			$html_own_inner .= "<tr><th $sty>".$oset['label']."</th><td>";
			$html_own_inner .= "<a class='float-right button button-small' href='". wp_nonce_url('admin.php?page=xtra&wpopts=1','mynonce') ."'>WP-Options</a>";
			$html_own_inner .= "</td></tr>";
		}
		else {
			if ( $show_own ) 
				$html_own_inner .= "</table><h3 class='m-left-10 mtop30'>".$oset['label']."</h3><table class='wp-list-table widefat fixed striped xtra'>";
			else 
				$html_own_inner .= "<tr><th $sth colspan=2><h3>".$oset['label']."</h3></th></tr>";
		}
	}
	$html_own = "
	<h2 ". ($show_own ? "class='icbefore dashicons-lightbulb'" : "") .">XTRA Plugin ".esc_html__('Own Settings', 'xtra-settings') ."</h2>
	<form action='?page=xtra' method='post'>
		<table class='wp-list-table widefat fixed striped xtra'>
		".$html_own_inner."
		". ($show_own ? "</table><table class='w-100 mtop30'>" : "") ."
			<tr><td colspan=2 class='textcenter'><input type='submit' name='xtra_submit_options' value='".esc_html__('Save Settings', 'xtra-settings') ."' class='button button-primary".($show_own ? " button-hero mybigbutt" : "")."' />
		</table>
		". wp_nonce_field( 'mynonce' ) ."
	</form>
	";
	
	
	
	
	if ( $show_own ) {
	?>
	<div class="xtra_box xtra_tabbes xtra_tabbeID_own <?php if ($xtra_seltab=="own" || $xtra_seltab==="*") echo "active"; ?>">
		<?php echo $html_own; ?>
	</div>
	<?php
if ($xtra_do_memlog) xtra_memlog("XTRA");
	}
	?>



	
	
	</div><!--xtra-tab-wrapper-->
	</div><!--xtra_left-->







	<?php
	//---Right Column
	?>
    <div class="xtra_right">
	<?php
	//. Donation
	?>
    <div class="xtra_box">
		<h2><?php esc_html_e('Enjoy? Consider a Donation', 'xtra-settings'); ?></h2>
		<table class="wp-list-table widefat fixed striped xtra"><tr><td>
		<p><?php printf(esc_html__('If you like XTRA, %sgive a 5-rate%s review on %swordpress.org%s', 'xtra-settings'),'<a class="button button-primary button-small" target="_blank" href="https://wordpress.org/support/plugin/xtra-settings/reviews/#new-post"><b class="xbold">','</b></a>','<a target="_blank" href="https://wordpress.org/support/plugin/xtra-settings/reviews/#new-post">','</a>'); ?></p>
		<p><?php printf(esc_html__('If you have questions or bug reports, write on %sXTRA Support Forum%s on wordpress.org', 'xtra-settings'),'<a target="_blank" href="https://wordpress.org/support/plugin/xtra-settings/">','</a>'); ?></p>
	<?php if (false) { ?>
		<p><b class="xbold"><?php esc_html_e('I need your support for efforts from my free time', 'xtra-settings'); ?></b></p>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="CJ2GTRHYB3AFE">
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
	<?php } ?>
	</td></tr></table>
	</div>
<?php
if ($xtra_do_memlog) xtra_memlog("Donation");
?>



	<?php
	if ( get_optionXTRA('xtra_opt_show_hitcntr') ) {
	//. Hit counter
	if ( get_optionXTRA('xtra_hits_enable') && get_optionXTRA('xtra_hits_show_side') ) {
		echo xtra_hits_data_block(2);
	?>
	<?php
	}
	}
if ($xtra_do_memlog) xtra_memlog("Hit counter");
	?>






	<?php
	if ( get_optionXTRA('xtra_opt_show_siteinf') ) {
	$xtra_pageload_time = timer_stop(0);
	//. Site Info
	$site_info = array(
		'Page Load Time'			=> $xtra_pageload_time . " sec",
		'Database Queries'			=> get_num_queries(),
		'Memory Usage'				=> memory_get_usage(true)/1024/1024 . " MB",
		'Peak Memory'				=> memory_get_peak_usage(true)/1024/1024 . " MB",
		'Memory Limit'				=> ini_get('memory_limit'),
		'Max upload file size'		=> ini_get('upload_max_filesize'),
		'Max script exec time'		=> ini_get('max_execution_time') . " sec",
		'Error Reporting Level'		=> $xtra_error_level_string,
		'Web server'				=> $_SERVER["SERVER_SOFTWARE"],
		'PHP version'				=> phpversion(),
		'MySQL version'				=> $wpdb->db_version(),
		'------------------'		=> 'BLOG INFO',
		'name'						=> get_bloginfo('name'),
		'description'				=> get_option('blogdescription'),
		'admin_email'				=> get_option('admin_email'),
		'WP version'				=> get_bloginfo('version'),
		'charset'					=> (get_option('blog_charset')?get_option('blog_charset'):'UTF-8'),
		'html_type' 				=> get_option('html_type'),
		'Site URL'					=> site_url(),
		'Home URL'					=> home_url(),
		'ABSPATH'					=> ABSPATH,
		'CONTENT_DIR'				=> WP_CONTENT_DIR,
		'PLUGIN_DIR'				=> WP_PLUGIN_DIR,
		'stylesheet_directory'		=> get_stylesheet_directory_uri(),
		'stylesheet_url'			=> get_stylesheet_uri(),
		'template_directory'		=> get_stylesheet_directory_uri(),
		'template_url'				=> get_template_directory_uri(),
		'rss_url'					=> get_feed_link('rss'),
		'rss2_url'					=> get_feed_link('rss2'),
		'atom_url'					=> get_feed_link('atom'),
		'rdf_url'					=> get_feed_link('rdf'),
		'comments_rss2_url'			=> get_feed_link('comments_rss2'),
		'comments_atom_url'			=> get_feed_link('comments_atom'),
		'pingback_url'				=> site_url( 'xmlrpc.php' ),
		'-------------------'		=> 'XTRA PLUGIN INFO',
		'XTRA_HOST'					=> XTRA_HOST,
		'XTRA_PLUGIN'				=> XTRA_PLUGIN,
		'XTRA_PLUGIN_BASENAME'		=> XTRA_PLUGIN_BASENAME,
		'XTRA_PLUGIN_DIR'			=> XTRA_PLUGIN_DIR,
		'XTRA_PLUGIN_SLUG'			=> XTRA_PLUGIN_SLUG,
		'XTRA_VERSION'				=> XTRA_VERSION,
		'XTRA_WPCONTENT_BASENAME'	=> XTRA_WPCONTENT_BASENAME,
		'XTRA_UPLOAD_DIR'			=> XTRA_UPLOAD_DIR,
		'XTRA_UPLOAD_URL'			=> XTRA_UPLOAD_URL,
	);
	$site_info_text = "";
	$i = 0;
	foreach ($site_info as $key=>$val) {
		$i++;
		if (xtra_instr($key,"----")) $site_info_text .= "<tr><td colspan=2><h3>".esc_html($val)."</h3></td></tr>";
		elseif ($i == 1) 	$site_info_text .= "<tr><td><span class='xbold'>".esc_html($key)."</span><td><span class='xbold'>".esc_html($val)."</span>";
		else 				$site_info_text .= "<tr><td>".esc_html($key)."<td>".esc_html($val)."";
	}
	//.. DB tables
	$sql = "SELECT TABLE_NAME AS table_name, DATA_FREE as data_overload, (data_length + index_length) as data_size FROM information_schema.TABLES WHERE table_schema = '".DB_NAME."'";
	$results = $wpdb->get_results( $sql, OBJECT );
	$site_info_text .= "<tr><td colspan=2><h3>WP DATABASE TABLES</h3></td></tr>";
	$total_size = 0;
	foreach ($results as $result) {
		$total_size += $result->data_size;
		$site_info_text .= "<tr><td>"."<a href='".wp_nonce_url('?page=xtra&dbtable='.$result->table_name,'mynonce')."'>".$result->table_name."</a>"."</td><td>".esc_html(xtra_getSizes($result->data_size))."</td></tr>";
	}
	$site_info_text .= "<tr><td class='bold'>TOTAL"."</td><td class='bold'>".esc_html(xtra_getSizes($total_size))."</td></tr>";

	?>
    <div class="xtra_box">
		<h2><?php esc_html_e('Site Info', 'xtra-settings'); ?></h2>
		<table class="wp-list-table widefat fixed striped xtra"><tr><td>
			<div class="max-h-150" onclick="<?php echo $js_h400.$js_w2;?>">
				<table class="wp-list-table widefat fixed striped xtra">
					<?php echo $site_info_text;?>
				</table>
			</div>
		</td></tr></table>
    </div>
<?php
	}
	
if ($xtra_do_memlog) xtra_memlog("Site Info");
?>






	<?php
	//. Current htaccess
	if (get_optionXTRA( 'xtra_opt_show_curr_ins' )) {
	$xtra_htacc_sts = xtra_my_extract_from_markers( ABSPATH . '.htaccess', "XTRA Settings" );
	?>
    <div class="xtra_box"><form action="?page=xtra" method="post">
		<h2><?php esc_html_e('Current XTRA insertions into .htaccess', 'xtra-settings'); ?></h2>
		<table class="wp-list-table widefat fixed striped xtra">
			<tr><td><textarea class="texta-150" onclick="<?php echo $js_h400.$js_w2;?>"><?php echo esc_html(implode("\n",$xtra_htacc_sts));?></textarea></td></tr>
		</table>
		<?php wp_nonce_field( 'mynonce' ); ?>
    </form></div>
<?php
if ($xtra_do_memlog) xtra_memlog("Current htaccess");
?>






	<?php
	//. Current wp-config
	$xtra_wpconfig_sts = xtra_extract_from_markers( ABSPATH . 'wp-config.php', "XTRA Settings" );
	?>
    <div class="xtra_box"><form action="?page=xtra" method="post">
		<h2><?php esc_html_e('Current XTRA insertions into wp-config.php', 'xtra-settings'); ?></h2>
		<table class="wp-list-table widefat fixed striped xtra">
			<tr><td><textarea class="texta-150" onclick="<?php echo $js_h400.$js_w2;?>"><?php echo esc_html(implode("\n",$xtra_wpconfig_sts));?></textarea></td></tr>
		</table>
		<?php wp_nonce_field( 'mynonce' ); ?>
    </form></div>
<?php
	}
if ($xtra_do_memlog) xtra_memlog("Current wp-config");
?>





	<?php
	//. XTRA Plugin Settings
	if ( ! $show_own ) {
	?>
    <div class="xtra_box">
		<?php echo $html_own; ?>
	</div>
<?php
if ($xtra_do_memlog) xtra_memlog("XTRA Settings");
	}
?>





	<?php
	//. All Options
if ( true ) {
	?>
	<div class="xtra_box">
	<h2><?php esc_html_e('Backup & Restore All XTRA Options', 'xtra-settings'); ?></h2>
	<table class="wp-list-table widefat fixed striped xtra">
		<tr><th><?php esc_html_e('Backup and Restore all XTRA options', 'xtra-settings'); ?><br/><span><?php esc_html_e('Backup will include the Hit Counter data.', 'xtra-settings'); ?></span>
		</th>
		</tr>
		<tr><td>
	<form action="?page=xtra" method="post">
		<div class="butt-wrap2">
		<input type="submit" name="xtra_allopts_backup" value="<?php esc_html_e('Backup XTRA Options', 'xtra-settings'); ?>" class="button button-primary bold" />
		<?php wp_nonce_field( 'mynonce' ); ?>
		</div>
	</form>
		</td>
		</tr>
		<tr><td>
	<form action="?page=xtra" method="post" enctype="multipart/form-data" onsubmit="if(!jQuery('#file02').val()){alert('Upload file is not selected!');return false};return confirm('<?php esc_html_e('Do you really want to DELETE all Options & RESTORE from this file?', 'xtra-settings'); ?> \n\n'+jQuery('#file02').val().replace(/^.*\\/, ''));">
		<div class="butt-wrap2">
		<p><label for="file02"><?php esc_html_e('Select a .json file to upload', 'xtra-settings'); ?>:<br/><input type="file" id="file02" name="file" accept=".json" value="" /></label></p>
		<p><input type="checkbox" id="xtra_allopts_restore_include_hits" name="xtra_allopts_restore_include_hits" value="1" /><label for="xtra_allopts_restore_include_hits"><?php esc_html_e('Including the Hit Counter data?', 'xtra-settings'); ?></label></p>
		<input type="submit" name="xtra_allopts_restore" value="<?php esc_html_e('Restore XTRA Options', 'xtra-settings'); ?>" class="button button-primary bold" />
		<?php wp_nonce_field( 'mynonce' ); ?>
		</div>
	</form>
		</td>
		</tr>
	</table></div>
<?php
}
if ($xtra_do_memlog) xtra_memlog("All Options");
?>




	<?php
	//. Delete All Options
	?>
	<div class="xtra_box">
	<h2><?php esc_html_e('Delete All XTRA Options', 'xtra-settings'); ?></h2>
	<table class="wp-list-table widefat fixed striped xtra">
		<tr><th><?php esc_html_e('Delete and reset all XTRA options to Wordpress defaults', 'xtra-settings'); ?><br/><span><?php esc_html_e('It will also delete all Hit Counter data', 'xtra-settings'); ?>.</span><br/><span><?php esc_html_e('It will also delete all XTRA insertions from .htaccess and wp-config.php files', 'xtra-settings'); ?>.</span>
		<br/>
		<div class="butt-wrap2">
		<form action="?page=xtra" method="post" onsubmit="return confirm('<?php esc_html_e('Do you really want to DELETE & RESET all XTRA options?', 'xtra-settings'); ?>');">
			<input type="hidden" name="hid_reset_all" value="1" />
			<input type="submit" name="xtra_reset_opt_submit" value="<?php esc_html_e('Reset All Now', 'xtra-settings'); ?>!" class="button button-primary bold" />
			<?php wp_nonce_field( 'mynonce' ); ?>
		</form>
		</div>
		</th></tr>
	</table></div>
<?php
if ($xtra_do_memlog) xtra_memlog("Delete All Options");
?>




	</div><!--xtra_right-->
    <div class="clear"></div>

<?php
if ($xtra_do_memlog)
	echo xtra_memlog("memoryTable");
?>

</div><!--xtra_full_wr-->
</div><!--wrap-->

<?php
}


if ( isset( $_GET['do'] ) ) {
	xtra_check_nonce();
	if ($_GET['do'] == 'maybe_auto_update') {
		do_action( 'wp_maybe_auto_update' );
	}
}
?>