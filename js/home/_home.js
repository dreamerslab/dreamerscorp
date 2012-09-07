( function ( $, window ){
  $( function (){
    // set bg
    var w        = [ '1024', '1152', '1248', '1280', '1360', '1440', '1600', '1680', '1900' ];
    var h        = [ '768', '800', '864', '900', '949', '960', '1024', '1050', '1200' ];
    var screen_w = screen.width;
    var screen_h = screen.height;
    var i        = w.length;
    var j;

    for( ; i-- ; ){
      for( j = h.length; j-- ; ){
        if( screen_w == w[ i ] && screen_h == h[ j ]){
          $( 'body' ).css({
            'background-image' : 'url("/img/' + w[ i ] + '-' + h[ j ] + '.jpg")'
          });
          break;
        }
      }
    }

    $( '.tab' ).tab();
    $( '.gallery-link' ).fancybox({
      titlePosition  : 'inside',
      centerOnScroll : true
    });
  })
})( jQuery, window );