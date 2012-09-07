( function ( $ ){
  $( function (){
    var $a = $( 'a' );

    $a.click( function (){
      this.blur();
    }).
    mousedown( function (){
      this.blur();
    });

    if( /msie/i.test( navigator.userAgent ) && !/opera/i.test( navigator.userAgent )){
      $a.focus( function (){
        this.blur();
      });
    }
  });
})( jQuery );