/*
 * Date Format 1.2.3
 * (c) 2007-2009 Steven Levithan <stevenlevithan.com>
 * MIT license
 *
 * Includes enhancements by Scott Trenda <scott.trenda.net>
 * and Kris Kowal <cixar.com/~kris.kowal/>
 *
 * Accepts a date, a mask, or a date and a mask.
 * Returns a formatted version of the given date.
 * The date defaults to the current date/time.
 * The mask defaults to dateFormat.masks.default.
 */
Date.prototype.format=function(b,c){var a=function(){var d=/d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,e=/\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,g=/[^-+\dA-Z]/g,f=function(i,h){i=String(i);h=h||2;while(i.length<h){i="0"+i;}return i;};return function(l,z,u){var j=a;if(arguments.length==1&&Object.prototype.toString.call(l)=="[object String]"&&!/\d/.test(l)){z=l;l=undefined;}l=l?new Date(l):new Date;if(isNaN(l)){throw SyntaxError("invalid date");}z=String(j.masks[z]||z||j.masks["default"]);if(z.slice(0,4)=="UTC:"){z=z.slice(4);u=true;}var w=u?"getUTC":"get",q=l[w+"Date"](),h=l[w+"Day"](),n=l[w+"Month"](),t=l[w+"FullYear"](),v=l[w+"Hours"](),p=l[w+"Minutes"](),x=l[w+"Seconds"](),r=l[w+"Milliseconds"](),i=u?0:l.getTimezoneOffset(),k={d:q,dd:f(q),ddd:j.i18n.dayNames[h],dddd:j.i18n.dayNames[h+7],m:n+1,mm:f(n+1),mmm:j.i18n.monthNames[n],mmmm:j.i18n.monthNames[n+12],yy:String(t).slice(2),yyyy:t,h:v%12||12,hh:f(v%12||12),H:v,HH:f(v),M:p,MM:f(p),s:x,ss:f(x),l:f(r,3),L:f(r>99?Math.round(r/10):r),t:v<12?"a":"p",tt:v<12?"am":"pm",T:v<12?"A":"P",TT:v<12?"AM":"PM",Z:u?"UTC":(String(l).match(e)||[""]).pop().replace(g,""),o:(i>0?"-":"+")+f(Math.floor(Math.abs(i)/60)*100+Math.abs(i)%60,4),S:["th","st","nd","rd"][q%10>3?0:(q%100-q%10!=10)*q%10]};return z.replace(d,function(m){return m in k?k[m]:m.slice(1,m.length-1);});};}();a.masks={"default":"ddd mmm dd yyyy HH:MM:ss",shortDate:"m/d/yy",mediumDate:"mmm d, yyyy",longDate:"mmmm d, yyyy",fullDate:"dddd, mmmm d, yyyy",shortTime:"h:MM TT",mediumTime:"h:MM:ss TT",longTime:"h:MM:ss TT Z",isoDate:"yyyy-mm-dd",isoTime:"HH:MM:ss",isoDateTime:"yyyy-mm-dd'T'HH:MM:ss",isoUtcDateTime:"UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"};a.i18n={dayNames:["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],monthNames:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec","January","February","March","April","May","June","July","August","September","October","November","December"]};return a(this,b,c);};