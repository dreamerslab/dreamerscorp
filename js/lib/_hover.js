;( function ( $ ){
  $.fn.hover_class = function ( hover_to ){
    return this.each( function (){

      var $current = $( this );

      $current.hover(
        // handlerin
        function (){
          $current.addClass( hover_to );
        },

        // handlerout
        function (){
          $current.removeClass( hover_to );
        }
      );
    });
  };
}( jQuery ));