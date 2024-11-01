var xtra_stopper = 0;
var xtra_timestart = new Date().getTime();
var xtra_timeend = 0;
var xtra_totaltime = 0;

function xtra_do_images(dox)
{
	xtra_timestart = new Date().getTime();
	xtra_stopper = 0;

	var images = [];
	if (dox == "Regenerate Thumbnails") {
		jQuery('.xtra_image_cb:checked').each(function(i) {
		   images.push(this.getAttribute("pid"));
		});
	}
	else {
		jQuery('.xtra_image_cb:checked').each(function(i) {
		   images.push(this.value);
		});
	}

	var target = jQuery('#xtra_ajax_resultsDiv');
	target.html('');
	target.show();
	var what = '';
	if (dox=='Compress') 				what = xtra_vars.compression;
	if (dox=='Restore') 				what = xtra_vars.restore;
	if (dox=='Delete Backup') 			what = xtra_vars.xdelete;
	if (dox=='Delete Image') 			what = xtra_vars.ximgdelete;
	if (dox=='Regenerate Thumbnails') 	what = xtra_vars.regenerate;
	var stpbut = jQuery('#xtra_ajax_stopButton');
	stpbut.show();
	stpbut.val(xtra_vars.stop+' '+what);

	jQuery('#xtra_ajax_buttonsDiv').hide();
	jQuery('#xtra_ajax_buttonsDiv2').hide();

	var xMethod = jQuery('#xtra_ajax_compM1').is(":checked") ? 1 : 0;
	xMethod = jQuery('#xtra_ajax_compM2').is(":checked") ? 2 : xMethod;

	var sure = jQuery('#xtra_ajax_sure').is(":checked") ? 1 : 0;
	var suretxt = '';
	if (!sure) suretxt = '<strong class="dark-red">'+xtra_vars.simulation_mode+'</strong> - '+xtra_vars.checksure+'';

	target.append('<div><h3>'+what+' '+xtra_vars.started+'</h3>'+suretxt+'</div><hr>');
	xtra_do_next(dox,images,0,xMethod,1,what);
}

function xtra_do_next(dox,images,next_index,xMethod,fromi,what)
{
	if (!images.length) 				return xtra_do_complete(what,2);
	if (xtra_stopper) 					return xtra_do_complete(what,1);
	if (next_index >= images.length) 	return xtra_do_complete(what);

	var cgif = jQuery('#xtra_ajax_convGIF').is(":checked") ? 1 : 0;
	var cbmp = jQuery('#xtra_ajax_convBMP').is(":checked") ? 1 : 0;
	var cpng = jQuery('#xtra_ajax_convPNG').is(":checked") ? 1 : 0;
	var sure = jQuery('#xtra_ajax_sure').is(":checked") ? 1 : 0;
	var compQ = jQuery('#xtra_ajax_compPRC').val();
	var maxW = jQuery('#xtra_ajax_maxW').val();
	var maxH = jQuery('#xtra_ajax_maxH').val();

	var acti = '';
	if (dox=='Compress') 				acti = 'xtra_compress_image';
	if (dox=='Restore') 				acti = 'xtra_restore_image';
	if (dox=='Delete Backup') 			acti = 'xtra_delete_backup';
	if (dox=='Regenerate Thumbnails') 	acti = 'xtra_regenerate_thumbnails';
	if (dox=='Delete Image') 			acti = 'xtra_delete_image';

	jQuery.post(
		ajaxurl,{
			_wpnonce: xtra_vars._wpnonce,
			action: acti,
			id: images[next_index],
			ids: images,
			fromi: fromi,
			sure: sure,
			compQ: compQ,
			maxW: maxW,
			maxH: maxH,
			xMethod: xMethod,
			cgif: cgif,
			cbmp: cbmp,
			cpng: cpng
		},
		function(response)
		{
			var result;
			var target = jQuery('#xtra_ajax_resultsDiv');
			target.show();
			var stpbut = jQuery('#xtra_ajax_stopButton');
			stpbut.show();
			try {
				result = JSON.parse(response);
				if ( result['message'].indexOf('Bulk') > -1 )
					target.append('<div class="indented">' + fromi+'-'+result['fromi'] + ' / ' + images.length + ' &gt;&gt; ' + result['message'] +'</div>');
				else
					target.append('<div class="indented">' + (next_index+1) + '/' + images.length + ' &gt;&gt; ' + result['message'] +'</div>');
			}
			catch(e) {
				target.append('<div>' + xtra_vars.invalid_response + '</div>');
				if (console) {
					console.warn(images[next_index] + ': '+ e.message);
					console.warn('JSON Response: ' + response);
				}
		    }
			target.animate({scrollTop: 999999});
			if ( result['message'].indexOf('Bulk') > -1 && result['fromi'] >= images.length )
				return xtra_do_complete(what);
			else
				xtra_do_next(dox,images,next_index+1,xMethod,result['fromi']+1,what);
		}
	);
}

function xtra_do_stop()
{
	xtra_stopper = 1;
}

function xtra_do_complete(what,param)
{
	jQuery('#xtra_ajax_stopButton').hide();
	jQuery('#xtra_ajax_buttonsDiv').show();
	jQuery('#xtra_ajax_buttonsDiv2').show();

	var target = jQuery('#xtra_ajax_resultsDiv');
	var target2 = jQuery('#xtra_refresh_images2');
	xtra_timeend = new Date().getTime();
	xtra_totaltime = ' ( ' + Math.round( ( xtra_timeend - xtra_timestart ) / 1000 ) + ' ' + xtra_vars.sec + ' )';

	var msg = what+' Complete'+xtra_totaltime;
	var premsg = '';
	var refrbutt = '<input type="submit" id="xtra_refresh_images2" name="xtra_refresh_images" value="'+xtra_vars.refresh+'" class="button button-primary bold" />';
	if (param == 1) msg = what+' '+xtra_vars.stopped+xtra_totaltime;
	if (param == 2) premsg = '<span class="red">'+xtra_vars.nothing_selected+'</span>';

	target.append(premsg+'<hr><div><strong>'+msg+'</strong></div><br/>'+refrbutt);
	target2.show();
	target.animate({scrollTop: 999999});
}



function xtra_do_ipdata(ip,tid)
{
	var target = jQuery('#'+tid);
	if (target.html() != '') return;
	jQuery.post(
		ajaxurl,{
			_wpnonce: xtra_vars._wpnonce,
			action: 'xtra_get_ipdata',
			ip: ip
		},
		function(response)
		{
			var result;
			try {
				result = JSON.parse(response);
				target.html(result['message']);
			}
			catch(e) {
				if (console) {
					console.warn(ip + ' : '+ e.message);
					console.warn('JSON Response: ' + response);
				}
		    }
		}
	);
}

function xtra_do_ajax(target,action,p1,p2,p3,p4,p5)
{
	var target = jQuery(target);
	jQuery.post(
		ajaxurl,{
			_wpnonce: xtra_vars._wpnonce,
			action: action,
			p1: p1,
			p2: p2,
			p3: p3,
			p4: p4,
			p5: p5
		},
		function(response)
		{
			var result;
			try {
				result = JSON.parse(response);
				target.html(result['message']);
			}
			catch(e) {
				if (console) {
					console.warn('Error at '+action + '. ErrorMessage: '+ e.message);
					console.warn('JSON Response: ' + response);
				}
		    }
		}
	);
}

