<?php
global $ita_vars;

?>

jQuery(document).ready(function($) {
	$("#<?php $ita_vars['plugin_post'];?>.\\[customer_id\\]").change(function() {
		var jv_customer_id = $(this).val();
		var data = {
			'action'		: 'ita_select_get_landscape_by_customer',
			'customer_id'	: jv_customer_id
		};
		$.post(ita_ajax.ajaxurl, data, function (response) {
			$("#<?php $ita_vars['plugin_post'];?>.\\[landscape_id\\]").html(response);
		});
	});
});
