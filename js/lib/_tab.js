;( function ( $ ){
  $.fn.tab = function (){
    var switch_block = function ( $all_blocks, $this_block ){
      $all_blocks.hide();
      $this_block.show();
    };

    return this.each( function (){
      // cache jquery obj
      var $this_tab   = $( this );
      var $tab_nav    = $this_tab.find( '.tab-nav' );
      var $tab_items  = $tab_nav.find( 'li' ).addClass( 'tab-nav-item' );
      var $tab_btns   = $tab_items.find( 'a' ).addClass( 'tab-nav-btn' );
      var $tab_blocks = $this_tab.find( '.tab-content' ).first().find( '.tab-content-block' );

      // initialize
      $tab_items.first().addClass( 'tab-nav-selected' );
      switch_block( $tab_blocks, $tab_blocks.first());

      // add fns
      $tab_btns.bind( 'click', function ( e ){
        e.preventDefault();
        var $this_btn   = $( this );
        var $this_item  = $( this ).parent();
        var $this_block = $( $this_btn.attr( 'href' ));

        $tab_items.removeClass( 'tab-nav-selected' );
        $this_item.addClass( 'tab-nav-selected' );
        switch_block( $tab_blocks, $this_block );
      });
    });
  };
}( jQuery ));