$(document).ready(function(){
	$('#select_lang').change(function(){
		var code = $(this).val();
		window.location = '{$current_url_lang}' + code;
	});
	$('#select_cur').change(function(){
		var code = $(this).val();
		window.location = '{$current_url_cur}' + code;
	});
	$('#catalogs').hover(function(){
		var list = $(this).children().eq(1);
		list.show();
		list.find('li').hover(function(){
			if($(this).find('ul').length>0){
				$(this).children().eq(1).show();
			}
		},function(){
			if($(this).find('ul').length>0){
				$(this).children().eq(1).hide();
			}
		});
	},function(){
		$(this).children().eq(1).hide();
	});
});
