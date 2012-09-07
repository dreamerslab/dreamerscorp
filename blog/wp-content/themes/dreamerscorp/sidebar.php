 <?php
/*
 * @package WordPress
 * @subpackage Dreamerscorp
 */
?>
	<div id="sidebar">

	<!--  custom search  -->

          <div id="search-block" class="tab">
            <ul id="search-block-header" class="tab-nav">
              <li id="google-search" class="tab-nav-selected trans-bg1"><a href="#google-search-tabs">Google</a></li>
              <li id="wp-search" class="tab-nav trans-bg2"><a href="#wp-search-tabs">Default</a></li>
            </ul>
            <div class="tab-content">
              <div id="google-search-tabs" class="tabs-panel trans-bg1 tab-content tab-content-block">
                <div class="tabs-panel-wrap">
                  <h2>Search</h2>
                  <div class="sidebar-logos"></div>
                  <form action="" id="searchbox_011877027236058606103:vff8if8zzic" onsubmit="return false;">
                    <div class="search-block">
                      <input id="g-search-bar" type="text" name="q" size="40"/>
                      <input id="g-search-submit" type="submit" value="Search"/>
                    </div>
                  </form>
                  <div id="results_011877027236058606103:vff8if8zzic" style="display:none">
                    <div class="cse-closeResults">
                      <a>&times; Close</a>
                    </div>
                    <div class="cse-resultsContainer"></div>
                  </div>
                </div>
              </div> <!-- #google-search-tabs -->
              <div id="wp-search-tabs" class="tabs-panel tab-content tab-content-block">
                <div class="tabs-panel-wrap">
                  <h2>Search</h2>
                  <div class="sidebar-logos"></div>
                  <div class="search-block">
                    <?php get_search_form(); ?>
                  </div>
                </div>
              </div> <!-- #wp-search-tabs -->
            </div>
          </div>
<!--  end custom search  -->

			<?php	/* Widgetized sidebar, if you have the plugin installed. */
			 if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
			<?php endif; ?>


  <a id="rss-link" href="http://feeds2.feedburner.com/Dreamerscorp" rel="nofollow">Dreamerscorp RSS</a>
  <div id="rss-followers-wrap">
    <a id="rss-followers" href="http://feeds2.feedburner.com/Dreamerscorp" rel="nofollow">
      How many people follow Dremaerscrop?
    </a>
  </div>

	</div>
