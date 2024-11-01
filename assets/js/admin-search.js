jQuery(document).ready(function($) {

	//---search---
	$('#xtra_searchall_input').keyup(debounce(function (event) {
		xtra_searchall_do();
	}, 450));

	$('#xtra_searchall_clear').click(debounce(function (event) {
		$('#xtra_searchall_input').val('');
		xtra_searchall_do();
	}, 50));

	//---filter---
	$('#xtra_filter_input').keyup(debounce(function (event) {
		xtra_filter_do($("#xtra_filter_input").val(),$('#xtra_filter_input'),$('#xtra_filter_results'));
	}, 450));

	$('#xtra_filter_clear').click(debounce(function (event) {
		$('#xtra_filter_input').val('');
		xtra_filter_do('',$('#xtra_filter_input'),$('#xtra_filter_results'));
	}, 50));

	$('#xtra_filter_input2').keyup(debounce(function (event) {
		xtra_filter_do($("#xtra_filter_input2").val(),$('#xtra_filter_input2'),$('#xtra_filter_results2'));
	}, 450));

	$('#xtra_filter_clear2').click(debounce(function (event) {
		$('#xtra_filter_input2').val('');
		xtra_filter_do('',$('#xtra_filter_input2'),$('#xtra_filter_results2'));
	}, 50));

	//---skipped---
	$('#xtra_skipped_hide').click(debounce(function (event) {
		$('#xtra_skipped_hide').addClass('xtra-hd1');
		$('#xtra_skipped_show').removeClass('xtra-hd1');
		xtra_skipped_do('xtra_skipped_hit',$('#xtra_filter_input'),$('#xtra_filter_results'));
	}, 450));

	$('#xtra_skipped_show').click(debounce(function (event) {
		$('#xtra_skipped_show').addClass('xtra-hd1');
		$('#xtra_skipped_hide').removeClass('xtra-hd1');
		xtra_skipped_do('',$('#xtra_filter_input'),$('#xtra_filter_results'));
	}, 50));

	$('#xtra_skipped_hide2').click(debounce(function (event) {
		$('#xtra_skipped_hide2').addClass('xtra-hd1');
		$('#xtra_skipped_show2').removeClass('xtra-hd1');
		xtra_skipped_do('xtra_skipped_hit',$('#xtra_filter_input2'),$('#xtra_filter_results2'));
	}, 450));

	$('#xtra_skipped_show2').click(debounce(function (event) {
		$('#xtra_skipped_show2').addClass('xtra-hd1');
		$('#xtra_skipped_hide2').removeClass('xtra-hd1');
		xtra_skipped_do('',$('#xtra_filter_input2'),$('#xtra_filter_results2'));
	}, 50));

	function xtra_skipped_do(str,inp,res) {
		if (str) {
			var rows;
			rows = $(inp).closest('table tbody').find('tr:containsi("'+str+'")').not($(inp).closest('tr'));
			$(rows).addClass('xtra-hd1');
			$(res).html($(inp).closest('table tbody').find('tr:visible').length-1+' hits&nbsp;&nbsp;&nbsp;');
		}
		else {
			$(inp).closest('table tbody').find('tr').removeClass('xtra-hd1');
			$(res).html('');
		}
	}

	function xtra_filter_do(str,inp,res) {
		$(inp).closest('table tbody').find('tr').removeClass('xtra-hd2');
		if (str) {
			var rows;
			rows = $(inp).closest('table tbody').find('tr:not(:containsi("'+str+'")):not(".xtra-thead")').not($(inp).closest('tr'));
			$(rows).addClass('xtra-hd2');
			$(res).html($(inp).closest('table tbody').find('tr:visible:not(".xtra-thead")').length-1+' hits&nbsp;&nbsp;&nbsp;');
		}
		else {
			$(res).html('');
		}
	}

	function xtra_searchall_do() {
		var val = $("#xtra_searchall_input").val();
		var ttx = "";
		var htx = "";
		$('tr', $('.xtra_tabbes')).each(function() {
			ttx = $(this).html();
			htx = ttx;
			htx = htx.replace(new RegExp('<div class="xtra_shili".*?>(.*?)</div>','gi'), '$1');
			$(this).html(htx);
		});
		if (val != '') {
			$('.xtra_tabbes').hide();
			$('.nav-tab').hide();
			$('.nav-tab-search').show();
			$('tr:containsi('+val+')', $('.xtra_tabbes')).each(function() {
				$(this).closest('.xtra_tabbes').show();
				ttx = $(this).html();
				htx = ttx;
				htx = htx.replace(/&nbsp;/gi, '&#160;');
				//htx = htx.replace(new RegExp('(>[^<]*)'+val+'(?!\w*;)','gi'), '$1<div class="xtra_shili" style="display:inline;background-color:#EA8483;">'+val+'</div>');
				htx = htx.replace(new RegExp('(>[^<]*?)'+val+'(?![^<]*?<\/(a|textarea))','gi'), '$1<div class="xtra_shili" style="display:inline;background-color:#EA8483;">'+val+'</div>');
				$(this).html(htx);
			});
		}
		else {
			$('.nav-tab').show();
			$('.nav-tab-search').hide();
			$('.nav-tab-active').click();
		}
	}

	function debounce(fn, delay) {
	  var timer = null;
	  return function () {
		var context = this, args = arguments;
		clearTimeout(timer);
		timer = setTimeout(function () {
		  fn.apply(context, args);
		}, delay);
	  };
	}

	$.extend($.expr[':'], {
	  'containsi': function(elem, i, match, array)
	  {
		return (elem.textContent || elem.innerText || '').toLowerCase()
		.indexOf((match[3] || "").toLowerCase()) >= 0;
	  }
	});


});