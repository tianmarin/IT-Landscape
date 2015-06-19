jQuery(document).ready(function($) {

//	plugin_post='Y21hcmlu';

	$.fn.ita_on = function(){
		this.closest('tr').show();
		return this;
	}
	$.fn.ita_off = function(){
		this.closest('tr').hide();
		this.val('');
		return this;
	}
	console.log("ita_fft_vars.switch_field :"+ita_fft_vars.switch_field);
	$(ita_fft_vars.switch_field).change(function(){
		opts			= ita_fft_vars.opts;
		plugin_post		= ita_fft_vars.plugin_post;
		
		for(i=0 ; i<opts.length ; i++){
			if( $(this).val() == opts[i][0] ){			
				for(j=0 ; j<opts[i][1].length ; j++){
					console.debug(opts[i][1][j][0]+" : " + opts[i][1][j][1]);
					if(opts[i][1][j][1] == 1){
						$("#"+plugin_post+"\\["+opts[i][1][j][0]+"\\]").ita_on();
					}else{
						$("#"+plugin_post+"\\["+opts[i][1][j][0]+"\\]").ita_off();
					}
				}
			}
		}
	});
	$(ita_fft_vars.switch_field).show(function(){
		opts			= ita_fft_vars.opts;
		plugin_post		= ita_fft_vars.plugin_post;
		
		for(i=0 ; i<opts.length ; i++){
			if( $(this).val() == opts[i][0] ){			
				for(j=0 ; j<opts[i][1].length ; j++){
					console.debug(opts[i][1][j][0]+" : " + opts[i][1][j][1]);
					if(opts[i][1][j][1] == 1){
						$("#"+plugin_post+"\\["+opts[i][1][j][0]+"\\]").ita_on();
					}else{
						$("#"+plugin_post+"\\["+opts[i][1][j][0]+"\\]").ita_off();
					}
				}
			}
		}
	});
});