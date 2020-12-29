// Nov 2020 => the script is now printed inline in its minified version
( function() {
  //gets the localized params
    var effectsAndIconsSelectorCandidates = wfcFrontParams.effectsAndIconsSelectorCandidates;
  function UgetBrowser() {
          var browser = {},
              ua,
              match,
              matched;

          ua = navigator.userAgent.toLowerCase();

          match = /(chrome)[ /]([\w.]+)/.exec( ua ) ||
              /(webkit)[ /]([\w.]+)/.exec( ua ) ||
              /(opera)(?:.*version|)[ /]([\w.]+)/.exec( ua ) ||
              /(msie) ([\w.]+)/.exec( ua ) ||
              ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
              [];

          matched = {
              browser: match[ 1 ] || "",
              version: match[ 2 ] || "0"
          };

          if ( matched.browser ) {
              browser[ matched.browser ] = true;
              browser.version = matched.version;
          }

          // Chrome is Webkit, but Webkit is also Safari.
          if ( browser.chrome ) {
              browser.webkit = true;
          } else if ( browser.webkit ) {
              browser.safari = true;
          }

          return browser;
  }//end of UgetBrowser

  var CurrentBrowser  = UgetBrowser();
  var CurrentBrowserName = '';

  //ADDS BROWSER CLASS TO BODY
  var i = 0;
  for (var browserkey in CurrentBrowser ) {
    if (i > 0)
      continue;
      CurrentBrowserName = browserkey;
     i++;
  }
  var body_el = document.querySelectorAll('body');
  if ( body_el && body_el[0] ) {
    body_el[0].classList.add(CurrentBrowserName || '');
  }


  //Applies effect css classes if any
  //
  //What do we need to do ?
  //Static effect : If a static effect has been set by user, we add a class font-effect- + effect suffix to the selector
  //
  // The localized data looks like :
  // if static effect set : array( 'static_effect' => $data['static-effect'] , 'static_effect_selector' => $data['selector'] );
  // can have both arrays
  for ( var key in effectsAndIconsSelectorCandidates ){

      var selectorData = effectsAndIconsSelectorCandidates[ key ];
      //do we have a static effect for this selector ?
      if ( selectorData.static_effect ) {
          //inset effect can not be applied to Mozilla. @todo Check next versions
          if ( 'inset' == selectorData.static_effect && true === CurrentBrowser.mozilla )
            continue;

          var effect_el = document.querySelectorAll( selectorData.static_effect_selector );
          if ( effect_el && effect_el[0] ) {
            effect_el[0].classList.add( 'font-effect-' + selectorData.static_effect );
          }
      }
  }
})();