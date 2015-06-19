jQuery(document).ready(function($) {
	plugin_post='Y21hcmlu';
	//SELECT_GET_SYSTEM_BY_LANDSCAPE
	$("#"+plugin_post+"\\[landscape_id\\]").change(function() {
		var jv_landscape_id = $(this).val();
		var data = {
			'action'		: 'ita_select_get_system_by_landscape',
			'landscape_id'	: jv_landscape_id
		};
		$.post(ita_ajax.ajaxurl, data, function (response) {
			$("#"+plugin_post+"\\[system_id\\]").html(response);
		});
	});
});

