<?php
	/*
	*/
	$htaccess = ABSPATH . '/.htaccess';
	$htaccess_rules = file_exists($htaccess) ? @file_get_contents($htaccess) : false;
	$errors = array();
	$rules = array();
	$rule = '\.(jpeg|jpg|gif|bmp|png|swf|js|css)$';
	$action = './wp-content/plugins/http-express/do_http_header.php?file=%{REQUEST_FILENAME}';
	$rules[] = "###Begin HTTP Express";
	$rules[] = "&lt;IfModule mod_rewrite.c&gt;";
	$rules[] = "RewriteEngine On";
	$rules[] = "RewriteBase /";
	$rules[] = "RewriteCond %{REQUEST_FILENAME} -f";
	$rules[] = "RewriteRule " . $rule . ' ' . $action . " [L]";
	$rules[] = "&lt;/IfModule&gt;";
	$rules[] = "###End HTTP Express";
	
	if(strpos($htaccess_rules,"###Begin HTTP Express") === false){
		$errors[] = sprintf(__('Status: <strong>Not working!</strong> Please set permission to writable for you WordPress installation root directory and reactivate HTTP Express or insert the following lines at the beginning of your .htaccess file  (do not remove comments): <p><pre>%s</pre></p>'),implode("<br />",$rules));
	}
	
	
?>
	<div class="wrap">
		<h2>HTTP Express <?php echo HTTP_EXPRESS_VERSION ?></h2>
		
		<?php if(!empty($errors)): ?>
			<div class="errors">
				<?php echo implode("<br />",$errors); ?>
			</div>
		<?php else: ?>
			<p><?php echo __("Status: HTTP Express is working properly. All images, css and javascripts are correctly served with a 15 days cache time","kifulab_http_express") ?></p>
			
			<input type="button" class="button-primary" value="<?php echo __("Test HTTP Express","kifulab_http_express") ?>" id="test-http-headers" />
			<div id="http_express_response" style="margin:15px 0"></div>
		<?php endif; ?>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			
			jQuery('#test-http-headers').click(function(){
				jQuery('#http_express_response').html('<?php echo __('Loading response for test image. Please wait','kifulab_http_express'); ?>..');
				jQuery.get('<?php echo HTTP_EXPRESS_URL ?>/http_express_test.php',{url: '<?php echo HTTP_ESPRESS_TEST_IMAGE ?>'},function(data){
					jQuery('#http_express_response').html('<img src="<?php echo HTTP_ESPRESS_TEST_IMAGE ?>" /><br />');
					jQuery('#http_express_response').append('<br /><?php echo __('<strong>Response for test image</strong>','kifulab_http_express'); ?>:');
					jQuery('#http_express_response').append('<br /><pre>'+data+'</pre>');
				});
			});

		});
	</script>
	
	<?php if(!is_writable($htaccess)): ?>
		<p><?php echo __('<strong>Warning</strong>: .htaccess file is not writable. Please set right permissions or remember to remove the corresponding lines before uninstalling HTTP Express'); ?></p>
	<?php endif; ?>
