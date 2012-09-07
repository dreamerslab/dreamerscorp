( function ( $ ){
  $( function (){

    var set_scroll_target = function ( anchor, to ){
      $( anchor ).click( function (){
         $.scrollTo( $( to ), { duration: 1000 });
      });
    };

    set_scroll_target( '.nav-about',   '#page-wrap' );
    set_scroll_target( '.nav-recruit', '#nav-recruit' );
    set_scroll_target( '.nav-contact', '#nav-contact' );

  });
})( jQuery );