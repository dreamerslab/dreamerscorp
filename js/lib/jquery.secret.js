/*! Copyright 2011, Ben Lin (http://dreamerslab.com/)
* Licensed under the MIT License (LICENSE.txt).
*
* Version: 1.0.1
*
* Requires: jQuery 1.3.0+
*/
// wrap everything in an anonymous function
(function(a){var b,c="trunk",d={trunk:{}},e={"in":function(a,e){return d[c]===undefined&&(d[c]={}),d[c][a]=e,b},out:function(a){return d[c]!==undefined&&d[c][a]!==undefined?d[c][a]:!1},call:function(e,f){var g;if(d[c]!==undefined&&d[c][e]!==undefined){g=d[c][e];if(a.isFunction(g))g.apply(d[c],a.isArray(f)?f:[f]);else throw'$.secret error: on action "call" - "'+e+'" is not a function'}return b},clear:function(a){var e=!1,f;for(f in d)if(f===a){e=!0;break}return e?delete d[a]:delete d[c][a],b}};a.secret=function(a,d,f){var g,h;if(d===undefined||typeof d!="string")throw'$.secret error: on action "'+a+'" - second argument "'+d+'" is undefined or is not a string';return b=this,g=d.split("."),g.length>1?(c=g[0],h=g[1]):(c="trunk",h=d),e[a](h,f)}})(jQuery)