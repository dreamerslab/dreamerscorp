( function ( $ ){
  // preload img
  $.preload( '/img/loading.gif' );

  $( function(){
    var current_lang;

    var lang = {
      en : {
        sent : 'Your message have been sent, thank you!',
        error : 'We are having problems sending your message, please try again later :3'
      },

      tw : {
        sent : '您的意見已經送出，謝謝!',
        error : '寄送郵件發生錯誤，請稍後再試 :3'
      }
    };

    var validate_rules = {
      name : {
        required : true
      },
      email : {
        required : true,
        email : true
      },
      message : {
        required : true
      }
    };


    var validate_msg = {
      name : {
        required : 'en{We need to know who you are.}tw{請填寫您的名字.}'
      },
      email : {
        required : 'en{We need to know how to reply you.}tw{請填寫您的電子郵件.}',
        email : 'en{The email format is incorrect.}tw{您輸入的電子郵件格式錯誤.}'
      },
      message : {
        required : 'en{Are you sure you want to leave your message empty?}tw{請填寫您的意見.}'
      }
    };

    var $lang = {
      en : $( '.en' ),
      tw : $( '.tw' )
    };

    var switch_lang = function ( $lang, lang ){
      var another = lang === 'tw' ? 'en' : 'tw';

      $lang[ another ].hide();
      $lang[ lang ].fadeIn( 1000 );
      current_lang = lang;
    };

    var browser_lang = function (){
      if( navigator.language ){
        return navigator.language;
      }

      if( navigator.browserLanguage ){
        return navigator.browserLanguage;
      }

      // default to en
      return 'en-EN';
    };

    var send = function(){
      $form.ajaxSubmit({
        target   : '#jquery-msg-content',
        url      : '/msg.php',
        dataType : 'json',
        success  : function( rsp ){
          // set msg to success or error by the ajax reponse
          var msg = rsp == 'success' ?
            lang[ current_lang ].sent :
            lang[ current_lang ].error;

          // display msg and unblock the screen after 3 second
          $.msg( 'replace', msg ).
            msg( 'unblock', 3000, 1 );
        }
      });
    };

    // cache jquery obj
    var $form = $( '#contact-form' );

  // --- execute -----------------------------------------------------------------

    current_lang = function (){
      return browser_lang().toLowerCase().split( '-' )[ 1 ] === 'tw' ?
        'tw' : 'en';
    }();

    switch_lang( $lang, current_lang );

    // change the language when click the in the right top corner
    $( '.switch-lang' ).click( function (){
      var lang = $( this ).attr( 'lang' );

      switch_lang( $lang, lang );
    });

    $form.validate({
      rules : validate_rules,
      messages : validate_msg,

      // for cancel the showing of original error message besides the input
      errorPlacement : function ( err, el ){ return false },

      // after binding function to invalidHandler, current_lang can be dynamicly changed. if bind a object, the object can't be changed easily
      invalidHandler : function ( form, validator ){
        var regex = current_lang === 'en' ?
          /en\{(.+?)\}/ : /tw\{(.+?)\}/;

        var content = [];

        $.each( validator.errorList, function ( i, err ){
          var match = regex.exec( err.message );

          if( match.length > 1 ){
            content.push( match[ 1 ]);
          }
        });

        content = content.join( '<br />' );

        $.msg({
          bgPath: '/img/',
          content : content
        });
      },
      submitHandler : function(){
        $.msg({
          autoUnblock : false,
          afterBlock : send,
          // clear input values before unblock screen
          beforeUnblock : function(){
            $form.clearForm();
          },
          bgPath: '/img/',
          // set default content to a loading img
          content : '<img src="/img/loading.gif"/>',
          msgID : 1
        });
      }
    });
  });

})( jQuery );