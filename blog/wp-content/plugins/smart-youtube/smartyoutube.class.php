<?php
class SmartYouTube
{
	var $local_version;
	var $custom_colors;
	var $plugin_url;
	var $options;
	var $key;

	function SmartYouTube()
	{
		$this->__construct();
	}
	
	function __construct()
	{
		$this->local_version = '3.8.9'; // TODO: Change this number???
		$this->custom_colors = array(
			'blank' 	=> array('d6d6d6', 'f0f0f0'),
			'storm' 	=> array('3a3a3a', '999999'),
			'iceberg' 	=> array('2b405b', '6b8ab6'),
			'acid' 		=> array('006699', '54abd6'),
			'green' 	=> array('234900', '4e9e00'),
			'orange' 	=> array('e1600f', 'febd01'),
			'pink' 		=> array('cc2550', 'e87a9f'),
			'purple' 	=> array('402061', '9461ca'),
			'rubyred' 	=> array('5d1719', 'cd311b'),
		);
		
		$this->plugin_url = defined('WP_PLUGIN_URL') ? 
							trailingslashit(WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__))) : 
							trailingslashit(get_bloginfo('wpurl')) . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__));

		$this->key = 'smart_youtube';

	 
		$this->options=$this->get_options();
							
		$this->add_filters_and_hooks();
	}
	
	function add_filters_and_hooks()
	{
		if ($this->options['posts'] == 'on')  
		{
			add_filter('the_content', array($this, 'check'), 100);
			add_filter('the_excerpt', array($this, 'check'), 100);
		}
		
		if ($this->options['comments'] == 'on') 
		{
			add_filter('comment_text', array($this, 'check'), 100);
		}
		
		add_action( 'plugins_loaded', array($this, 'install'));
		add_action( 'after_plugin_row', array($this, 'check_plugin_version'));

		add_action('admin_menu', array($this, 'add_menu_items'));
		
		register_activation_hook(__FILE__, array($this, 'install'));
	}
	
	function add_menu_items()
	{
	    add_options_page('Smart Youtube options', 'Smart Youtube', 8, __FILE__, array($this, 'options_page'));
	}
		
	function options_page()
	{
	   /* $status = $this->get_info();
    			
    	$the_version = $status[1];
    	$the_message = $status[3];	
	
		if ((version_compare(strval($the_version), strval($this->local_version), '>') == 1))
		{
			$msg = 'Latest version available '.' <strong>'.$the_version.'</strong><br />'.$the_message;	
			  _e('<div id="message" class="updated fade"><p>' . $msg . '</p></div>');			
		}
		*/	
			
        // If form was submitted
    	if (isset($_POST['submitted'])) 
    	{			
    		check_admin_referer('smart-youtube');		
    		
    		#---------------------------------------------------------------------#
    		$this->options['img'] = !isset($_POST['disp_img'])                ? 'off' : 'on';
    		$this->options['link'] = !isset($_POST['disp_link'])              ? 'off' : 'on';
    		$this->options['valid'] = !isset($_POST['valid'])                 ? 'off' : 'on';
    		$this->options['search'] = !isset($_POST['disp_search'])          ? 'off' : 'on';
    		$this->options['ann'] = !isset($_POST['disp_ann'])                ? 'off' : 'on';
    		
    		$this->options['info'] = !isset($_POST['disp_info'])              ? 'off' : 'on';
    		
    		$this->options['width'] = !isset($_POST['disp_width'])            ? 425 : intval($_POST['disp_width']);
    		$this->options['height'] = !isset($_POST['disp_height'])          ? 344 : intval($_POST['disp_height']);
    		
    		$this->options['widthhq'] = !isset($_POST['disp_widthhq'])        ? 480 : intval($_POST['disp_widthhq']);
    		$this->options['heighthq'] = !isset($_POST['disp_heighthq'])      ? 295 : intval($_POST['disp_heighthq']);
    		
    		$this->options['widthside'] = !isset($_POST['disp_widthside'])    ? 150 : intval($_POST['disp_widthside']);
    		$this->options['heightside'] = !isset($_POST['disp_heightside'])  ? 125 : intval($_POST['disp_heightside']);
    		
    		$this->options['rel'] = !isset($_POST['embedRel'])                ? 1 : $_POST['embedRel'];
    		$this->options['border'] = !isset($_POST['embedBorder'])          ? 0 : 1;
    		$this->options['color'] = !isset($_POST['embedColor'])            ? 'blank' : $_POST['embedColor'];
    		$this->options['autoplay'] = !isset($_POST['autoplay'])           ? 0 : 1;
			$this->options['loop'] = !isset($_POST['loop'])                   ? 0 : 1;
    		$this->options['privacy'] = !isset($_POST['disp_privacy'])            ? 0 : 1;
    		
    		$this->options['posts'] = !isset($_POST['disp_posts'])            ? 'off' : 'on';
    		$this->options['comments'] = !isset($_POST['disp_comments'])      ? 'off' : 'on';
				$this->options['iframe'] = !isset($_POST['iframe'])      ? 'off' : 'on';
            
            $this->options['template'] = !isset($_POST['disp_template']) ? 
                                    				'{video}' : 
                                                    stripslashes(htmlspecialchars($_POST['disp_template']));;
                $this->options['tag'] = !isset($_POST['tag'])            ? '' : $_POST['tag'];
            $this->options['dark_player'] = ! isset( $_POST['dark_player'] ) ? 'off' : 'on';
            $this->options['logoless'] = ! isset( $_POST['logoless'] ) ? 'off' : 'on';
            
            update_option($this->key, $this->options);
            
            $msg_status = 'Smart Youtube options saved.';
            				
            // Show message
            _e('<div id="message" class="updated fade"><p>' . $msg_status . '</p></div>');
    		
    	} 
	
		$disp_img        = $this->options['img']     == 'on'  ? 'checked="checked"' : '';
		$disp_link       = $this->options['link']    == 'on'  ? 'checked="checked"' : '';
		$disp_search     = $this->options['search']  == 'on'  ? 'checked="checked"' : '';
		$disp_ann        = $this->options['ann']     == 'on'  ? 'checked="checked"' : '';
		$disp_info       = $this->options['info']    == 'on'  ? 'checked="checked"' : '';
		
		$valid           = $this->options['valid']   == 'on'  ? 'checked="checked"' : '';
		
		$disp_width      = $this->options['width'];
		$disp_height     = $this->options['height'];
		
		$disp_widthhq    = $this->options['widthhq'];
		$disp_heighthq   = $this->options['heighthq'];
		
		$disp_widthside  = $this->options['widthside'];
		$disp_heightside = $this->options['heightside'];
		
		$disp_autoplay   = $this->options['autoplay']     ? 'checked="checked"' : '';
		$disp_loop       = $this->options['loop']         ? 'checked="checked"' : '';
		$disp_rel        = $this->options['rel']          ? 'checked="checked"' : '';		
		$disp_rel2 			 = $this->options['rel'] 				?		'' : 'checked="checked"';
		$disp_border     = $this->options['border']       ? 'checked="checked"' : '';
		$disp_color      = $this->options['color'];
		$disp_posts      = $this->options['posts']        == 'on' ? 'checked="checked"' : '' ;
		$disp_comments   = $this->options['comments']     == 'on' ? 'checked="checked"' : '';
		
		$disp_privacy    = $this->options['privacy']    ? 'checked="checked"' : '';
		$iframe    = $this->options['iframe']   =='on'  ? 'checked="checked"' : '';
	
		$disp_template   = wp_specialchars($this->options['template']);
                $tag   = $this->options['tag'];
		$dark_player = $this->options['dark_player'] == 'on' ? 'checked="checked"' : '';
		$logoless = $this->options['logoless'] == 'on' ? 'checked="checked"' : '';
		
		if (!$disp_width)
		{
			$disp_width = 425;
		}
		
		if (!$disp_height)
		{
			$disp_height = 344;
		}			
		
    	global $wp_version;	
    	
		$embed_img = $this->plugin_url . '/img/embed_selection-vfl29294.png';
			
		echo '<script src="' . $this->plugin_url . '/yt.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="' . $this->plugin_url . '/styleyt.css" />';
	
    $imgpath     = $this->plugin_url.'/i';	
    $actionurl   = $_SERVER['REQUEST_URI'];
    $nonce       = wp_create_nonce( 'smart-youtube');
    $example     = htmlentities('<div style="float:left;margin-right: 10px;">{video}</div>');

    // Configuration Page
    
    echo <<<END
<div class="wrap smartyoutube" >
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>Smart YouTube</h2>
	<div id="poststuff" style="margin-top:10px;">
		 <div id="sideblock" style="float:right;width:270px;margin-left:10px;"> 

		 <iframe width=270 height=800 frameborder="0" src="http://www.prelovac.com/plugin/news.php?id=0&utm_source=plugin&utm_medium=plugin&utm_campaign=Smart%2BYoutube"></iframe>

 	</div>
     	
	 	<div id="mainblock" style="width:710px">
			<div class="dbx-content">			
				<h2 id="usageHeader">Usage <span style="font-size:small">[<a href="#">view instructions</a>]</span></h2>
		    
    			<div id="usage" style="display:none">
                    <p>To use the video in your posts, paste YouTube video URL with <strong>httpv://</strong> (notice the 'v'). </p>
                    <p><strong>Important:</strong> The URL should just be copied into your post normally and the letter 'v' added, do not create a clickable link!</p>
                    <p>Example: httpv://www.youtube.com/watch?v=OWfksMD4PAg</p>
                    <p>If you want to embed High/HD Quality video use <strong>httpvh://</strong> instead (Video High Defintion).</p>
                    <p>To embed playlists use httpvp:// (eg. httpvp://www.youtube.com/view_play_list?p=528026B4F7B34094)</p>
                    <p>Smart Youtube also supports migrated blogs from Wordpress.com using [youtube=youtubeadresss]</p>
    
    		<ul>
                        <li>httpv:// - regular video</li>
                        <li>httpvh:// - high/HD quality</li>
                        <li>httpvp:// - playlist</li>
			<li>httpvhp:// - HD playlist</li>
                        <li>[youtube=youtubeadresss] - supported for blogs migrated from wordpress.com</li>
                    </ul>
                </div>
				
                <form name="yteform" action="$action_url" method="post">
					<input type="hidden" name="submitted" value="1" /> 
					<input type="hidden" id="_wpnonce" name="_wpnonce" value="$nonce" />
    				<h2>Options</h2>
    				<p> You can adjust the way your embeded youtube videos behave in the options below.</p>
    				
    				<h3>Video settings</h3>
                    <div>
                    	<input id="check3" type="checkbox" name="disp_posts" $disp_posts />
                    	<label for="check3">Display videos in posts</label>
                    </div>
    				<div>
    					<input id="check4" type="checkbox" name="disp_comments" $disp_comments />
    					<label for="check4">Display videos in comments</label>
    				</div>
<br />
<div>
    					<input id="iframe" type="checkbox" name="iframe" $iframe />
    					<label for="iframe">Use IFRAME embed code (recommended)</label>
    				</div>
    
    
    				<h3>Video Appearence</h3>
    				<p class="instruct">Video template. Default is just {video}. <br />
    					You can try <code>$example</code> if you want the text to wrap around video.</p>
    				<textarea cols="50" id="disp_template" name="disp_template">$disp_template</textarea>
    				<p class="instruct">Video width and height in normal mode (httpv://). Default is 425x344. </p>
    				<div id="inputSizeNormal">
    					<input class="width" name="disp_width" value="$disp_width" size="7"/>x<input class="height"  name="disp_height" value="$disp_height" size="7" />
    				</div>
    				<div class="size-chooser" forDiv="inputSizeNormal">
    	 				<a v-width="320" v-height="265" href="#">
    	 					<span></span>
                    	 	<div class="outer">
                    	 		<div></div>
                    	 	</div>
                    	 </a>
                    	 <a v-width="425" v-height="344" href="#">
                    	 	<span></span>
                    	 	<div class="outer">
                    	 		<div></div>
                    	 	</div>
                    	 </a>
                    	 <a v-width="480" v-height="385" href="#">
                    	 	<span></span>
                    	 	<div class="outer">
                    	 		<div></div>
                    	 	</div>
                    	 </a>
                    	 <a v-width="640" v-height="505" href="#">
                    	 	<span></span>
                    	 	<div class="outer">
                    	 		<div></div>
                    	 	</div>
                    	 </a>
			<a v-width="960" v-height="745" href="#">
                    	 	<span></span>
                    	 	<div class="outer">
                    	 		<div></div>
                    	 	</div>
                    	 </a>
                    	 <br clear="both" />
                    </div>
    
    				<p class="instruct">Video width and height in <strong>high quality</strong> mode (httpvh://). Default is 480x295. </p>
    				<div id="inputSizeHQ">
    					<input class="width"  name="disp_widthhq" value="$disp_widthhq" size="7" />x<input class="height" name="disp_heighthq" value="$disp_heighthq" size="7" />
    				</div>
    				<div class="size-chooser" forDiv="inputSizeHQ">
                        <a v-width="480" v-height="295" href="#">
                        	<span></span>
                            <div class="outer">
                            	<div></div>
                            </div>
                        </a>
                            <a v-width="560" v-height="340" href="#">
                            <span></span>
                            <div class="outer">
                            	<div></div>
                            </div>
                        </a>
                        <a v-width="640" v-height="385" href="#">
                            <span></span>
                            <div class="outer">
                            	<div></div>
                            </div>
                        </a>
                        <a v-width="853" v-height="505" href="#">
                            <span></span>
                            <div class="outer">
                            	<div></div>
                            </div>
                        </a>
			 <a v-width="1280" v-height="745" href="#">
                            <span></span>
                            <div class="outer">
                            	<div></div>
                            </div>
                        </a>
                        <br clear="both" />
                    </div>
    
    				<p class="instruct">Video width and height in <strong>sidebar</strong> mode (regardless of quality). Default is 150x125.</p>
    				<input id="disp_widthside"  name="disp_widthside" value="$disp_widthside" size="7" />x<input id="disp_heightside"  name="disp_heightside" value="$disp_heightside" size="7" /><br /><br />		
                    <div id="watch-customize-embed-form">
                    	<input type="radio" $disp_rel id="embedCustomization1" name="embedRel" value="1"/>
                    	<label for="embedCustomization1">Include related videos</label><br/>
                    	<input type="radio" $disp_rel2  id="embedCustomization0" name="embedRel" value="0"/>
                    	<label for="embedCustomization0">Do not include related videos</label><br/>
                    </div>
                    <img id="watch-customize-embed-theme-preview" src="$this->plugin_url/img/preview_embed_blank_sm.gif"/>
                    <div id="watch-customize-embed-theme-swatches">
                        <a onclick="onChangeColor('blank');  return false;" class="watch-image-radio-link radio_selected" href="#" id="theme_color_blank_img"><img alt="" src="http://s.ytimg.com/yt/img/pixel-vfl73.gif" class="watch-embed-select watch-embed-blank"/></a>
                        <a onclick="onChangeColor('storm');  return false;" class="watch-image-radio-link" href="#" id="theme_color_storm_img"><img alt="" src="http://s.ytimg.com/yt/img/pixel-vfl73.gif" class="watch-embed-select watch-embed-storm"/></a>
                        <a onclick="onChangeColor('iceberg');  return false;" class="watch-image-radio-link " href="#" id="theme_color_iceberg_img"><img alt="" src="http://s.ytimg.com/yt/img/pixel-vfl73.gif" class="watch-embed-select watch-embed-iceberg"/></a>
                        <a onclick="onChangeColor('acid');  return false;" class="watch-image-radio-link" href="#" id="theme_color_acid_img"><img alt="" src="http://s.ytimg.com/yt/img/pixel-vfl73.gif" class="watch-embed-select watch-embed-acid"/></a>
                        <a onclick="onChangeColor('green');  return false;" class="watch-image-radio-link" href="#" id="theme_color_green_img"><img alt="" src="http://s.ytimg.com/yt/img/pixel-vfl73.gif" class="watch-embed-select watch-embed-green"/></a>
                        <a onclick="onChangeColor('orange');  return false;" class="watch-image-radio-link" href="#" id="theme_color_orange_img"><img alt="" src="http://s.ytimg.com/yt/img/pixel-vfl73.gif" class="watch-embed-select watch-embed-orange"/></a>
                        <a onclick="onChangeColor('pink');  return false;" class="watch-image-radio-link" href="#" id="theme_color_pink_img"><img alt="" src="http://s.ytimg.com/yt/img/pixel-vfl73.gif" class="watch-embed-select watch-embed-pink"/></a>
                        <a onclick="onChangeColor('purple');  return false;" class="watch-image-radio-link" href="#" id="theme_color_purple_img"><img alt="" src="http://s.ytimg.com/yt/img/pixel-vfl73.gif" class="watch-embed-select watch-embed-purple"/></a>
                        <a onclick="onChangeColor('rubyred');  return false;" class="watch-image-radio-link" href="#" id="theme_color_rubyred_img"><img alt="" src="http://s.ytimg.com/yt/img/pixel-vfl73.gif" class="watch-embed-select watch-embed-rubyred"/></a>
                        <input id="embedColor" type="hidden" name="embedColor" value="$disp_color">
                        <input id="prevUrl" type="hidden" name="prevUrl" value="$this->plugin_url/img/">
                    </div>
                    <div style="margin: 0 0 0 4px; clear: both;">
                    	<input type="checkbox" onchange="onUpdatePreviewImage();" id="show_border_checkbox" name="embedBorder" $disp_border /><label for="show_border_checkbox"> Show Border</label><br />
                    	<input type="checkbox" id="autoplay_checkbox" name="autoplay" $disp_autoplay /><label for="autoplay_checkbox"> Autoplay videos</label><br />
						<input type="checkbox" id="loop_checkbox" name="loop" $disp_loop /><label for="loop_checkbox"> Loop videos</label><br />
                    	<input type="checkbox" id="disp_search" name="disp_search" $disp_search /><label for="disp_search"> Display search box</label><br />
                    	<input type="checkbox" id="disp_info" name="disp_info" $disp_info /><label for="disp_info"> Remove Titles & Ratings</label><br />
                    	<input type="checkbox" id="disp_ann" name="disp_ann" $disp_ann /><label for="disp_ann"> Remove Annotations</label><br />
                    	<input type="checkbox" id="privacy" name="disp_privacy" $disp_privacy /><label for="privacy"> Enable privacy-enhanced mode [<a target="_blank" href="http://www.google.com/support/youtube/bin/answer.py?answer=141046">?</a>] (videos may not work for Iphone users)</label><br />
                    	<input type="checkbox" id="dark_player" name="dark_player" $dark_player /><label for="dark_player"> Use new Youtube "dark" player (requires IFRAME mode to be turned on)</label><br />
                    	<input type="checkbox" id="logoless" name="logoless" $logoless /><label for="logoless">Hide YouTube Logo</label><br />
                    </div>
    
                    <h3>Custom code compatibility</h3>
                    <p>Use this option if you have used another youtube plugin and switched over to Smart Youtube.<p>
                    <p>For example if you used [yt]qYWWBwf2wHE[/yt] type of code, you would type yt in the box below.</p>
                    <input id="tag" type="text" name="tag" value="$tag" />
                    <label for="tag">Custom code</label>
                    
                    <h3>xHTML validation</h3>
                    <p class="instruct">Enabling the option below will change default YouTube code to be xHTML valid. (videos may not work for Iphone users)</p>
                    <input id="valid" type="checkbox" name="valid" $valid />
                    <label for="valid">Enable xHTML Validation</label>
                    
                    <h3>RSS feed options</h3>
                    <p class="instruct">Some RSS feed readers like Bloglines will show embeded YouTube videos. Some will not and Smart YouTube allows you to display a video link and a video screenshot instead.</p>
                    <p class="instruct">Smart YouTube will always embed the video but it can not know if the reader supports embeded video or not. So use these additional options at your own likening.</p>
                    
                    <input id="check2" type="checkbox" name="disp_link" $disp_link />
                    <label for="check2">Display video link in RSS feed</label><br />
                    <input id="check1" type="checkbox" name="disp_img" $disp_img />
                    <label for="check1">Display video preview image in RSS feed</label>
    
    				<div class="submit"><input type="submit" name="Submit" value="Update options" /></div>
				</form>
			</div>
	 	</div>
	</div>
</div>
<h5 class="author">Another fine WordPress plugin by <a href="http://www.prelovac.com/vladimir/">Vladimir Prelovac</a></h5>
END;
	}
	

	/**
    * Looks for Smart Youtube URL(s) in the post content
    * and replace them with proper HTML tags
    * 
    * @param mixed $the_content
    * @param mixed $side
    * @return mixed
    */
	function check($the_content, $side = 0)
	{
	    if (strpos($the_content, "httpv") !== false  ) 
	    {
	      $char_codes = array('&#215;', '&#8211;');
	      $replacements = array("x", "--");
	      $the_content = str_replace($char_codes, $replacements, $the_content);
            
            
	      preg_match_all("/http(v|vh|vhd):\/\/([a-zA-Z0-9\-\_]+\.|)youtube\.com\/watch(\?v\=|\/v\/|#!v=)([a-zA-Z0-9\-\_]{11})([^<\s]*)/", $the_content, $matches['youtube.com'], PREG_SET_ORDER); 	      
	      preg_match_all("/http(v|vh|vhd):\/\/([a-zA-Z0-9\-\_]+\.|)youtu\.be\/([a-zA-Z0-9\-\_]{11})([^<\s]*)/", $the_content, $matches['youtu.be'], PREG_SET_ORDER);
	      
	      if (isset($matches['youtube.com']))
		      foreach ($matches['youtube.com'] as $match) 
		      {	 
	            	$the_content = preg_replace("/http".$match[1].":\/\/([a-zA-Z0-9\-\_]+\.|)youtube\.com\/watch(\?v\=|\/v\/|#!v=)([a-zA-Z0-9\-\_]{11})([^\s<]*)/", $this->tag($match[4], $match[1], $match[5], $side), $the_content, 1);	
		      }
	      
	      if (isset($matches['youtu.be']))
	      { 
	      
		      foreach ($matches['youtu.be'] as $match) 
		      {	 
		    
	            	$the_content = preg_replace("/http".$match[1].":\/\/([a-zA-Z0-9\-\_]+\.|)youtu\.be\/([a-zA-Z0-9\-\_]{11})([^<\s]*)/", $this->tag($match[3], $match[1], $match[4], $side), $the_content, 1);	
		      }
	    	}
	      
	      
            
	      preg_match_all("/http(vp|vhp):\/\/([a-zA-Z0-9\-\_]+\.|)youtube\.com\/view_play_list(\?p\=|\/v\/|#!v=)([a-zA-Z0-9\-\_]{16})([^<\s]*)/", $the_content, $matches, PREG_SET_ORDER);
	      foreach ($matches as $match) 
	      {
            	$the_content = preg_replace("/http".$match[1].":\/\/([a-zA-Z0-9\-\_]+\.|)youtube\.com\/view_play_list(\?p\=|\/v\/|#!v=)([a-zA-Z0-9\-\_]{16})([^\s<]*)/", $this->tag($match[4], $match[1], $match[5], $side), $the_content, 1);	
	      }	

	     
	    }
	
        // to work with migrated blogs from Wordpress.com replacing [youtube=youtubeadresss]
        if (strpos($the_content, "[youtube") !== false ) 
        {
        	preg_match_all("/\[youtube\=http:\/\/([a-zA-Z0-9\-\_]+\.|)youtube\.com\/watch(\?v\=|\/v\/|#!v=)([a-zA-Z0-9\-\_]{11})([^<\s]*)\]/", $the_content, $matches, PREG_SET_ORDER);
        	foreach ($matches as $match) 
        	{
        		$the_content = preg_replace("/\[youtube\=http:\/\/([a-zA-Z0-9\-\_]+\.|)youtube\.com\/watch(\?v\=|\/v\/|#!v=)([a-zA-Z0-9\-\_]{11})([^\s<]*)\]/", $this->tag($match[3], '', $match[4], $side), $the_content, 1);
        	}
        }
        $tag=$this->options['tag'];

        if ($tag!='' && strpos($the_content, "[".$tag."]") !== false )
        {
                preg_match_all("/\[$tag\]([a-zA-Z0-9\-\_]{11})([^<\s]*)\[\/$tag\]/", $the_content, $matches, PREG_SET_ORDER);
        	foreach ($matches as $match) 
        	{
                      //print_r($matches);
        	      $the_content = preg_replace("/\[$tag\]([a-zA-Z0-9\-\_]{11})([^<\s]*)\[\/$tag\]/", $this->tag($match[1], '', '', $side), $the_content, 1);
        	}
	}
        
        return $the_content;
	}
	
	function tag($file, $high = 'v', $time = '', $side = 0)
	{
	    $playlist        = 0;
			$disp_rel        = $this->options['rel'];
	    $disp_border     = $this->options['border'];
	    $disp_color      = $this->options['color'];
	    $autoplay        = $this->options['autoplay'];
		$loop            = $this->options['loop'];
	    $disp_search     = $this->options['search'] == 'on' ? 1 : 0;
	    $disp_info       = $this->options['info'] == 'on' ? '&showinfo=0' : '';
	    $disp_ann        = $this->options['ann'] == 'on' ? '&iv_load_policy=3' : '';
	    $template        = trim($this->options['template']) == '' ? '{video}' : $this->options['template']; 
	    $valid           = $this->options['valid'];
	    $dark_player     = ( $this->options['dark_player'] == 'on' ) ? '&theme=dark' : '';
	    $logoless = $this->options['logoless'];
	
    	switch ($high)
    	{
    		case 'v': 
    		    $high = ''; 
    		    break;
    		case 'vh': 
    		    $high = '&amp;hd=1'; 
    		    break;
    		case 'vhd': 
    		    $high = '&amp;hd=1'; 
    		    break;
    		case 'vp': 
    		    $high = ''; 
    		    $playlist = 1; 
    		    break;
		case 'vhp': 
    		    $high = '&amp;hd=1'; 
    		    $playlist = 1; 
    		    break;		
    		default: 
    		    $high = ''; 
    		    break;
    	}
	
	    $width = $side ? 
	            $this->options['widthside'] : 
	            ($high ? $this->options['widthhq'] : $this->options['width']);
	    
        $height = $side ? 
                $this->options['heightside'] : 
                ($high ? $this->options['heighthq'] : $this->options['height']);
	
    	if (!$width)
    	{    		
    	    $width = !$high ? 480 : 425 ;
    	}
    	
    	if (!$height)
    	{
    		$height = !$high ? 360 : 344;
    	}	
		
	    if ($disp_border)
	    {
		    $height += 18;
	    }
	    
	    if ( $logoless = 'on' ) {
			$ll = '&modestbranding=1';
		} else {
			$ll = '';
		}

          if ($autoplay)
            $ap='&autoplay=1';
            	    
	    $root_url = $this->options['privacy'] ? 'http://www.youtube-nocookie.com' : 'http://www.youtube.com'; 

	  if ($this->options['iframe']=='on')
$video_url = htmlspecialchars("$root_url/embed/$file?color1={$this->custom_colors[$disp_color][0]}&color2={$this->custom_colors[$disp_color][1]}&border=$disp_border&fs=1&hl=en$ap$ll&loop=$loop{$disp_info}$disp_ann&showsearch=$disp_search&rel=$disp_rel$dark_player", ENT_QUOTES) . $high . $time;
else
$video_url = htmlspecialchars("$root_url/v/$file?color1={$this->custom_colors[$disp_color][0]}&color2={$this->custom_colors[$disp_color][1]}&border=$disp_border&fs=1&hl=en$ap$ll&loop=$loop{$disp_info}$disp_ann&showsearch=$disp_search&rel=$disp_rel$dark_player", ENT_QUOTES) . $high . $time;

		if ($playlist)
		{

		$video_url = htmlspecialchars("$root_url/p/$file?color1={$this->custom_colors[$disp_color][0]}&color2={$this->custom_colors[$disp_color][1]}&border=$disp_border&fs=1&hl=en$ap$ll&loop={$disp_info}$disp_ann&showsearch=$disp_search&rel=$disp_rel$dark_player", ENT_QUOTES) . $high . $time;	
		   
if ($this->options['iframe']=='on')
$yte_tag = <<<EOT
<span class="youtube">
<object type="application/x-shockwave-flash" width="$width" height="$height" data="$video_url" >
<param name="movie" value="$video_url" />
<param name="allowFullScreen" value="true" />
<param name="allowscriptaccess" value="always" />
</object>
</span>
EOT;
else
$yte_tag = <<<EOT
<span class="youtube">
<iframe  title="YouTube video player" class="youtube-player" type="text/html" src="$video_url" width="$width" height="$height" frameborder="0" allowfullscreen></iframe>
</span>
EOT;
		}		
		elseif ($valid == 'off' || strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')===TRUE ||  strpos($_SERVER['HTTP_USER_AGENT'], 'iPod')===TRUE ||  strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')===TRUE)
		{
if ($this->options['iframe']=='on')
$yte_tag = <<<EOT
<span class="youtube">
<iframe title="YouTube video player" class="youtube-player" type="text/html" width="$width" height="$height" src="$video_url" frameborder="0" allowfullscreen></iframe>
</span>
EOT;
else		    
		    $yte_tag = <<<EOT
<span class="youtube">
<object width="$width" height="$height">
<param name="movie" value="$video_url" />
<param name="allowFullScreen" value="true" />
<embed wmode="opaque" src="$video_url" type="application/x-shockwave-flash" allowfullscreen="true" width="$width" height="$height"></embed>
<param name="wmode" value="opaque" />
</object>
</span>
EOT;
        }		
        else 
        {
           
if ($this->options['iframe']=='on')
$yte_tag = <<<EOT
<span class="youtube">
<iframe title="YouTube video player" class="youtube-player" type="text/html" width="$width" height="$height" src="$video_url" frameborder="0" allowfullscreen></iframe>
</span>
EOT;
else
            $yte_tag = <<<EOT
<span class="youtube">
<object type="application/x-shockwave-flash" width="$width" height="$height" data="$video_url">
<param name="movie" value="$video_url" />
<param name="allowFullScreen" value="true" />
<param name="wmode" value="transparent" />
</object>
</span>
EOT;
	    }

        if (is_feed())
        {
        		
            if ($high)
            {
                $high = '&fmt=18';
            }
            if ($playlist)
            	$url='http://www.youtube.com/view_play_list?p=';
            else
            	$url='http://www.youtube.com/watch?v=';
            	
            if ($this->options['img'] == 'on')
            {
            	$yte_tag .= '<p><a href="'.$url . $file . $high. '"><img src="http://img.youtube.com/vi/' . $file . '/default.jpg" width="130" height="97" border=0></a></p>';
            }
            
            if ($this->options['link'] == 'on')
            {
            	$yte_tag.='<p><a href="'.$url . $file . $high . '">www.youtube.com/watch?v=' . $file . '</a></p>';
            }	
            //  if ($this->options['link'] == 'off' && $this->options['img'] == 'off')
            //    $yte_tag='http://www.youtube.com/watch?v='.$file;	
        }
        
        return str_replace('{video}',  $yte_tag, html_entity_decode($template)); 
	}
	
    function install()
    {
        // register widget
        if (function_exists('register_sidebar_widget'))
        {
            register_sidebar_widget('Smart YouTube', array($this, 'widget'));
        }    
        
        if (function_exists('register_widget_control'))
        {    
            register_widget_control('Smart YouTube', array($this, 'widget_control'));
        }
        
        if (get_option($this->key)) {
					$this->options=$this->get_options();
	}
      /*  
        // upgrade the options format (as all-in-one, not single entries)
        $this->options = array(
            'posts'          => 'on',
            'comments'		 => 'off',
            'img'			 => 'off',
            'width'	         => 425,
            'height'	     => 344,
            'widthhq'	     => 480,
            'heighthq'		 => 295,
            'widthside'		 => 150,
            'heightside'	 => 125,
            'rel'			 => 1,
            'color'			 => 'blank',
            'border'		 => 0,
            'link'			 => 'on',
            'valid'			 => 'off',
            'search'		 => 'off',
            'info'			 => 'on',
            'ann'			 => 'on',
            'template'		 => '{video}',
            'autoplay'		 => 0,
            'privacy'		 => 0,
            'wtext'			 => '',
            'wtitle'		 => '',
            'tag'                => '',
	    			'iframe' =>	'on'
        );
                
        // migrate the old-fashion options
        foreach ($this->options as $key => $value)
        {
            if (false !== $old_option = get_option("smart_yt_$key"))
            {
                $this->options[$key] = $old_option;
            }
            
            delete_option("smart_yt_$key");
        }
        
        add_option($this->key, $this->options);*/
    }
    
               // Handle our options
          function get_options()
          {
              
             $options = array(
            'posts'          => 'on',
            'comments'		 => 'off',
            'img'			 => 'off',
            'width'	         => 425,
            'height'	     => 344,
            'widthhq'	     => 480,
            'heighthq'		 => 295,
            'widthside'		 => 150,
            'heightside'	 => 125,
           	'rel'			 => 1,
           	'color'			 => 'blank',
            'border'		 => 0,
            'link'			 => 'on',
            'valid'			 => 'off',
            'search'		 => 'off',
            'info'			 => 'on',
            'ann'			 => 'on',
            'template'		 => '{video}',
            'autoplay'		 => 0,
            'privacy'		 => 0,
            'wtext'			 => '',
            'wtitle'		 => '',
            'tag'                => '',
	    			'iframe' =>	'on',
	    			'dark_player' => '',
	    			'logoless' => 'on'
        );
              
              $saved = get_option($this->key);
              
              if (!empty($saved)) {
                  foreach ($saved as $key => $option)
                      $options[$key] = $option;
              }
              
              if ($saved != $options)
                  update_option($this->key, $options);
              
              
             

              
              return $options;
          }    

          
    function widget($args = array())
		{
			extract ($args);
			$text = apply_filters( 'widget_text', $this->check($this->options['wtext'], 1));
			$title = apply_filters( 'widget_title', $this->options['wtitle']);
			echo
			$before_widget,
			$before_title, $title, $after_title,
			$text,
			$after_widget;
		}
    
    function widget_control()
    {
        if ($_REQUEST['submit'])
		{
		    $this->options['wtext'] = stripslashes($_REQUEST['yte_text']);
		    $this->options['wtitle'] = stripslashes($_REQUEST['yte_title']);
		}
		
		update_option($this->key, $this->options);
		
		$text = wp_specialchars($this->options['wtext']);
		$title = wp_specialchars($this->options['wtitle']);
		
		echo <<<EOT
		Title:<br /><input type="text" id="yte_title" name="yte_title" value="$title" /><br />
		Insert HTML code below. In addition to normal text you may use httpv, httpvh and httpvhd links just like in your posts.<br />
		<textarea id="text" name="yte_text" rows="10" cols="16" class="widefat">$text</textarea>
		<input type="hidden" id="submit" name="submit" value="1" />
EOT;
    }

    /**
     * Checks the plugin version
     * @param $plugin
     * @return unknown_type
     */
    function check_plugin_version($plugin)
    {
        global $plugindir;
        
        if (strpos($plugin, 'smartyoutube.php') === false) return;
        
        $status = $this->get_info();
        
        $the_version = $status[1];
        $the_message = $status[3];    

        if ((version_compare(strval($the_version), strval($this->local_version), '>') != 1)) return;
        
        $msg = "Latest version available: <strong>$the_version</strong><br />.$the_message";                
        
        echo <<<EOT
        <td colspan="5" class="plugin-update" style="line-height:1.2em;">
            Latest version available: <strong>$the_version</strong><br />$the_message
        </td>
EOT;
    
    }
    
    /**
     * Gets plugin info from WordPress Codex repo 
     * @return mixed
     */
    function get_info()
    {
        $checkfile = 'http://svn.wp-plugins.org/smart-youtube/trunk/smartyoutube.chk';
        
        $status = array();
        
        return $status; //???
        
        $vcheck = wp_remote_fopen($checkfile);
                
        if ($vcheck)
        {
            $version = $$this->local_version;
                                    
            $status = explode('@', $vcheck);
            return $status;                
        }            
    }
}
