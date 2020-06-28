/*
 * WordPress Font Customizer front end scripts
 * copyright (c) 2014-2015 Nicolas GUILLAUME (nikeo), Press Customizr.
 * GPL2+ Licensed
 */
( function( $ ) {
	//gets the localized params
    var effectsAndIconsSelectorCandidates	= WfcFrontParams.effectsAndIconsSelectorCandidates;
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
	$('body').addClass( CurrentBrowserName || '' );



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

          $( selectorData.static_effect_selector ).addClass( 'font-effect-' + selectorData.static_effect );
      }
	}

} )( jQuery );




//GOOGLE FONTS STUFFS
//gets the localized params
// var Gfonts      = WebFontsParams.Gfonts,
//   Families    = [],
//   Subsets     = [];

// for ( var key in Gfonts ){
//   //Creates the subsets array
//   //if several subsets are defined for the same fonts > adds them and makes a subset array of unique subset values
//   var FontSubsets = Gfonts[key];
//   for ( var subkey in FontSubsets ) {
//     if ( 'all-subsets' == FontSubsets[subkey] )
//       continue;
//     if ( FontSubsets[subkey] && ! $.inArray( FontSubsets[subkey] , FontSubsets ) ) {
//       Subsets.push(Gfonts[key])
//     }
//   }
//   //fill the families array and add the subsets to the last family (Google Syntax)
//   Families.push( key );
// }

// //are subsets defined?
// if ( Subsets && Subsets.join(',') ) {
//   Families.push('&subset=' +  Subsets.join(',') );
// }

// if ( 0 != Gfonts.length ) {
//   //Loads the fonts
//   WebFont.load({
//       google: {
//         families: Families
//       },
//       // loading: function() {console.log('loading')},
//     // active: function() {},
//     // inactive: function() {},
//     // fontloading: function(familyName, fvd) {},
//     // fontactive: function(familyName, fvd) {},
//     // fontinactive: function(familyName, fvd) {}
//   });
// }