<script type="text/javascript">
	function wp_rp_display_excerpt_onclick(){
		var wp_rp_display_excerpt = document.getElementById('wp_rp_display_excerpt');
		var wp_rp_excerpt_max_length_label = document.getElementById('wp_rp_excerpt_max_length_label');
		if(wp_rp_display_excerpt.checked){
			wp_rp_excerpt_max_length_label.style.display = '';
		} else {
			wp_rp_excerpt_max_length_label.style.display = 'none';
		}
	}
	function wp_rp_display_thumbnail_onclick(){
		var wp_rp_display_thumbnail = document.getElementById('wp_rp_display_thumbnail');
		var wp_rp_thumbnail_span = document.getElementById('wp_rp_thumbnail_span');
		if(wp_rp_display_thumbnail.checked){
			wp_rp_thumbnail_span.style.display = '';
			jQuery('#wp-rp-thumbnails-info').fadeOut();
			if (window.localStorage) {
				window.localStorage.wp_rp_thumbnails_info = "close";
			}
		} else {
			wp_rp_thumbnail_span.style.display = 'none';
		}
	}
</script>
