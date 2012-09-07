// lib
// preload
;(function(a){a.preload=function(){var c=[],b=arguments.length;for(;b--;){c.push(a("<img />").attr("src",arguments[b]));}};})(jQuery);
// tab
;(function(a){a.fn.tab=function(){var b=function(a,b){a.hide();b.show()};return this.each(function(){var c=a(this);var d=c.find(".tab-nav");var e=d.find("li").addClass("tab-nav-item");var f=e.find("a").addClass("tab-nav-btn");var g=c.find(".tab-content").first().find(".tab-content-block");e.first().addClass("tab-nav-selected");b(g,g.first());f.bind("click",function(c){c.preventDefault();var d=a(this);var f=a(this).parent();var h=a(d.attr("href"));e.removeClass("tab-nav-selected");f.addClass("tab-nav-selected");b(g,h)})})}})(jQuery);
// hover
;(function(a){a.fn.hover_class=function(b){return this.each(function(){var c=a(this);c.hover(function(){c.addClass(b)},function(){c.removeClass(b)})})}})(jQuery);

// execute
(function($,window){$.preload("/img/nivbgover.png");$(function(){var w=["1024","1152","1248","1280","1360","1440","1600","1680","1900"];var h=["768","800","864","900","949","960","1024","1050","1200"];var screen_w=screen.width;var screen_h=screen.height;var i=w.length;var j;for(;i--;){for(j=h.length;j--;){if(screen_w==w[i]&&screen_h==h[j]){$("body").css({"background-image":'url("/img/'+w[i]+"-"+h[j]+'.jpg")'});break;}}}$("a").hover_class("a-hover");$("textarea").hover_class("textarea-hover");$(".nav-link").hover_class("nav-link-hover");$(".nav-selected").hover_class("nav-link-selected-hover");$(".tab-nav-btn").hover_class("tab-nav-btn-hover");$(".tab").tab();});})(jQuery,window);