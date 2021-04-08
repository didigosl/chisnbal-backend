<?php
$code = <<<EOT
raw::jQuery(function($) {
	$('.chosen-select').chosen({allow_single_deselect:true}); 
    $('.chosen-select').next().css({'width':250});
		/*
		$('#recent-box [data-rel="tooltip"]').tooltip({placement: tooltip_placement});
		function tooltip_placement(context, source) {
			var $source = $(source);
			var $parent = $source.closest('.tab-content')
			var off1 = $parent.offset();
			var w1 = $parent.width();

			var off2 = $source.offset();
			//var w2 = $source.width();

			if( parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2) ) return 'right';
			return 'left';
		}


		$('.dialogs,.comments').ace_scroll({
			size: 300
	    });

		*/
		//Android's default browser somehow is confused when tapping on label which will lead to dragging the task
		//so disable dragging when clicking on label
		var agent = navigator.userAgent.toLowerCase();
		if("ontouchstart" in document && /applewebkit/.test(agent) && /android/.test(agent))
		  $('#tasks').on('touchstart', function(e){
			var li = $(e.target).closest('#tasks li');
			if(li.length == 0)return;
			var label = li.find('label.inline').get(0);
			if(label == e.target || $.contains(label, e.target)) e.stopImmediatePropagation() ;
		});

	})
EOT;
return [
	'head' => [
		'inc::back/js/ace-extra.min.js',
		'inc::if lte IE 8::back/js/html5shiv.js',
		'inc::if lte IE 8::back/js/respond.min.js',
	],
	'foot' => [
		'raw::if IE::window.jQuery || document.write("<script src=\'' . $this->config->params->staticsPath . 'back/js/jquery1x.min.js\'>"+"<"+"/script>");',
		'raw::if(\'ontouchstart\' in document.documentElement) document.write("<script src=\'' . $this->config->params->staticsPath . 'back/js/jquery.mobile.custom.min.js\'>"+"<"+"/script>");',
		'inc::back/js/bootstrap.min.js',
		'inc::back/js/jquery.colorbox-min.js',
		//'inc::if lte IE 8::back/js/excanvas.min.js',
		'inc::back/js/jquery-ui.min.js',
		//'inc::back/js/jquery-ui.custom.min.js',
		'inc::back/js/jquery.ui.touch-punch.min.js',
		'inc::back/js/jquery.gritter.min.js',
		'inc::back/js/ace-elements.min.js',
		'inc::back/js/ace.min.js',
		'inc::back/js/bootbox.min.js"',
		//'inc::back/js/ace/ace.onpage-help.js',
		//'inc::back/js/rainbow.js',
		// 'inc::js/jquery.chained.remote.min.js',
		'inc::js/linkagesel/js/linkagesel-min.js',
		'inc::js/director.min.js',
		'inc::js/jsviews.min.js',
		'inc::js/jquery.twbsPagination.min.js',
		'inc::js/layer/layer.js',
		'inc::back/js/chosen.jquery.min.js',
		'inc::back/js/my.js',
		$code,
	],
];