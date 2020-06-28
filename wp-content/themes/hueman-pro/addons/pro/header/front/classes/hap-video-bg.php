<?php

//add_filter( 'hph_background' , 'hu_set_file_video_bg' );
function hu_set_file_video_bg( $_original_bg ) {
    global $wp_embed;
    $video = do_shortcode('[video src="http://customizr-dev.dev/wp-content/uploads/2016/09/Clouds-4753.mp4"]');
    return sprintf('<div class="hu-video image">%1$s</div>', $video );
}



//add_filter( 'hph_background' , 'hu_set_ext_video_bg' );
function hu_set_ext_video_bg( $slide_src = '_not_set_', $slide_model ) {
    // if ( ! hu_is_home() || wp_is_mobile() )
    //   return $_original_bg;
    if ( ! is_array( $slide_model ) || ! array_key_exists( 'slide-video-bg', $slide_model ) )
       return '';
    //////////////// Parameters :  ////////////////
    $_video         = $slide_model['slide-video-bg']; //http://vimeo.com/108104171';// 'https://www.youtube.com/watch?v=oUlLeDKPCYA';'http://vimeo.com/108104171';//'http://vimeo.com/73373514';////<=the url of your vimeo or youtube video
    $_autoplay      = true;//<= true = autoplay
    $_mute_volume   = true;// true = volume set to 0
    $_unmute_on_hover = true;//true = the video will unmute on mouse hover
    $_pause_on_slide = true;//true = the video is paused on slide change
    $_related_videos  = false;// true = display related videos . Works just with youtube videos, vimeo doesn't allow this for non premium users.
    $_yt_loop       = true;
    //////////////////////////////////////////////
    //uncomment this video array to run the random video

    // if ( wp_is_mobile() ) {
    //   $_video         = array( 'https://www.youtube.com/watch?v=oIeBf1MYFIY','https://www.youtube.com/watch?v=oUlLeDKPCYA' );
    // } else {
    //   $_video         = array( 'https://www.youtube.com/watch?v=oIeBf1MYFIY','https://www.youtube.com/watch?v=oUlLeDKPCYA' );
    // }
    // $_video         = array( 'https://www.youtube.com/watch?v=oIeBf1MYFIY','https://www.youtube.com/watch?v=oUlLeDKPCYA' , 'http://vimeo.com/108792063', 'http://vimeo.com/73373514', 'http://vimeo.com/39312923', 'http://vimeo.com/108104171', 'http://vimeo.com/106535324' , 'http://vimeo.com/108138933', 'http://vimeo.com/107789364', 'http://vimeo.com/107580451' );

    //remove previous filter if set
    remove_filter('embed_oembed_html', '_add_an_id_for_youtube_player' , 10, 4);
    remove_filter('oembed_result' , 'enable_youtube_jsapi');
    remove_filter('oembed_result' , 'set_youtube_autoplay');
    remove_filter('oembed_fetch_url' , 'set_vimeo_url_query_args');


    //remove hueman filter
    remove_filter( 'embed_oembed_html', 'hu_embed_html', 10, 3 );
    if ( ! is_array($_video) ) {
      $_return_video = $_video;
    } else {
      $rand_key       = array_rand($_video, 1);
      $_return_video = $_video[$rand_key];
    }

    //youtube or vimeo?
    $_provider = ( false !== strpos($_return_video, 'youtube') ) ? 'youtube' : false;
    $_provider = ( false !== strpos($_return_video, 'vimeo') ) ? 'vimeo' : $_provider;

    if ( ! $_provider )
      return $_original_bg;

    if ( 'youtube' == $_provider ) {
        add_filter('embed_oembed_html', '_add_an_id_for_youtube_player' , 10, 4);
        //Adding parameters to WordPress oEmbed https://foxland.fi/adding-parameters-to-wordpress-oembed/
        add_filter('oembed_result' , 'enable_youtube_jsapi');
        if ( false !== $_autoplay )
          add_filter('oembed_result' , 'set_youtube_autoplay');
        if ( false !== $_yt_loop )
          add_filter('oembed_result' , 'set_youtube_loop');
        if ( false === $_related_videos )
          add_filter('oembed_result' , 'set_youtube_no_related_videos');
    } elseif ( 'vimeo' == $_provider && false !== $_autoplay ) {
        add_filter('oembed_fetch_url' , 'set_vimeo_url_query_args');
    }

    //write some javascript : dynamic centering on resizing, responsiveness, Vimeo and YouTube API controls
    _write_video_slide_script( $_mute_volume, $_unmute_on_hover, $_pause_on_slide, $_provider, $slide_model );

    $_return_video =  add_query_arg( 'cached' , time(),  $_return_video );

    //( false !== strpos($_return_video, '?') ) ? '&cached=' : '?cached=';
    $_return_video = apply_filters('the_content', $_return_video );

    /* For someone autombed is not hooked to the content */
    if ( false === strpos($_return_video, '<iframe') ){
        global $wp_embed;
        $_return_video = $wp_embed -> autoembed( $_return_video );
    }

    return sprintf(
        '%1$s<div class="hu-video image">%2$s</div>%3$s',
        ( 'youtube' == $_provider && '_not_set_' != $slide_src ) ? $slide_src : '',
        $_return_video,
        ( 'vimeo' == $_provider && '_not_set_' != $slide_src ) ? $slide_src : ''
    );
}




function set_vimeo_url_query_args($provider) {
  $provider = add_query_arg( 'autoplay', 1 , $provider );
  $provider = add_query_arg( 'loop', 1 , $provider );
  $provider = add_query_arg( 'background', 1 , $provider );
  return $provider;
}
function set_youtube_autoplay($html) {
  if ( strstr( $html,'youtube.com/embed/' ) )
     return str_replace( '?feature=oembed', '?feature=oembed&autoplay=1&controls=0&showinfo=0', $html );
  return $html;
}

function set_youtube_loop($html) {
  if ( strstr( $html,'youtube.com/embed/' ) )
     return preg_replace( '|(youtube.com/embed/)(.*?)(\?feature=oembed)|', '$0&loop=1&playlist=$2', $html );
  return $html;
}

function set_youtube_no_related_videos($html) {
  if ( strstr( $html,'youtube.com/embed/' ) )
     return str_replace( '?feature=oembed', '?feature=oembed&rel=0', $html );
  return $html;
}

function _add_an_id_for_youtube_player($html, $url, $attr, $post_ID ) {
  //@to do make the id unique
  return str_replace('<iframe', '<iframe id="youtube-video"', $html);
}

function enable_youtube_jsapi($html) {
  if ( strstr( $html,'youtube.com/embed/' ) )
    return str_replace( '?feature=oembed', '?feature=oembed&enablejsapi=1', $html );
  return $html;
}


function _write_video_slide_script( $_mute_volume, $_unmute_on_hover, $_pause_on_slide, $_provider, $slide_model ) {
  $slide_id = $slide_model['id'];

  ?>
    <script type="text/javascript">
      jQuery(function ($) {
        var $slideWrap    = $( '#<?php echo $slide_id; ?>' ),
            $videoWrap    = $('.hu-video', $slideWrap ),
            $vid_iframe     = $('iframe' , $videoWrap ),
            initial_vid_H      = $vid_iframe.height(),
            initial_vid_W       = $vid_iframe.width(),
            is_active       = false,
            $slider_wrapper = $videoWrap.closest('.pc-section-slider'),
            $_pause_on_slide = <?php echo (true != $_pause_on_slide) ? 'false' : 'true'; ?>,
            $_mute_volume   = <?php echo (true != $_mute_volume) ? 'false' : 'true'; ?>,
            $_unmute_on_hover = <?php echo (true != $_unmute_on_hover) ? 'false' : 'true'; ?>,
            _provider       = '<?php echo $_provider; ?>';

        //$('.carousel-caption' , $videoWrap ).remove();
        //$('.carousel-image', $videoWrap ).css('text-align' , 'center');

        //Beautify the video
        //$('iframe' , $videoWrap )
          //.css('position','relative');
          //.attr('width' , '').attr('height' , '')
          //.css('width', '100%').css('max-width', '100%')


        _re_center();

        $(window).resize( function() {
          setTimeout(function() { _re_center();}, 200)
        });

        function _re_center() {
          var section_W      = $(window).width(),
              section_H      = $('.pc-section-slider').height(),
              new_Hvid, new_Wvid, actual_vid_W;

          //Re-Dimension
          if (  ( section_H / section_W ) > ( initial_vid_H / initial_vid_W ) ) {
              new_Hvid = section_H;
              new_Wvid = new_Hvid * ( initial_vid_W / initial_vid_H );
          } else {
              new_Wvid = section_W;
              new_Hvid = new_Wvid * ( initial_vid_H / initial_vid_W );
          }
          //$('iframe' , $videoWrap).attr('height' , '').attr('width' , '');
          $.when( $('iframe' , $videoWrap ).css('height' , new_Hvid ).css('width' , new_Wvid ) ).done( function() {
            actual_vid_W = $('iframe' , $videoWrap ).width();
            //Re-center horizontally
            if ( actual_vid_W >= section_W ) {
              $('.pc-section-slider').find('.hu-video').css('right', ( ( actual_vid_W - section_W ) / 2 ) +'px' );
            }
          });
        }






        //VIMEO PLAYER API
        if ( 'vimeo' == _provider ) {
            $videoWrap.hide();
            //load the Vimeo API script asynchronously
            // var tag = document.createElement('script');
            // tag.src = "https://player.vimeo.com/api/player.js";
            // var firstScriptTag = document.getElementsByTagName('script')[0];
            // firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            //http://developer.vimeo.com/player/js-api
            // Listen for messages from the player
            if ( window.addEventListener ){
                window.addEventListener('message', onMessageReceived, false);
            }
            else {
                window.attachEvent('onmessage', onMessageReceived, false);
            }

            //Mute volume when player is ready
            //=> This method can receive messages from both youtube and vimeo frames if both providers are used in the same slider
            function onMessageReceived( msg ) {
                if ( msg && msg.origin && -1 == msg.origin.indexOf('vimeo') )
                  return;

                var data, $player;
                try {
                    data          = JSON.parse( msg.data ),
                    $player       = $('iframe[src*=vimeo]', '.hu-video' );
                } catch( er ) { return; }

                switch ( data.event ) {
                    case 'ready':
                        $.when( $slideWrap.find('img').fadeOut( 1000 ) ).done( function() {
                            $videoWrap.show();
                        });

                        ( true == $_mute_volume ) && post('setVolume', +0.00001 , $player );
                    break;
                }
            }

             // Helper function for sending a message to the player
            function post(action, value, player) {
                if ( ! player || 0 == player.length )
                  return;
                var data = {
                  method: action
                };

                if (value)
                  data.value = value;

                var message   = JSON.stringify(data);
                    url       = player.attr('src').split('?');
                player[0].contentWindow.postMessage(message, url);
            }

            //EVENTS :
            //Unmute on hover
            // $_mute_volume && $_unmute_on_hover && $slider_wrapper.hover( function() {
            //   post('setVolume', +0.8 , $('iframe[src*=vimeo]', $slider_wrapper ) );
            // }, function() {
            //   post('setVolume', +0.000001 , $('iframe[src*=vimeo]', $slider_wrapper ) );
            // });

            // Call the API on 'slid'
            // $slider_wrapper.on('slid', function() {
            //   ( true == $_pause_on_slide) && _pause_inactive_slides();
            // });

            // function _pause_inactive_slides() {
            //   var $activeSlide  = $slider_wrapper,
            //       $player       = $('iframe[src*=vimeo]', $activeSlide );

            //   //if current slide has no player. Pause all other players, else play this player
            //   if ( ! $player.length ) {
            //     $slider_wrapper.find('iframe[src*=vimeo]').each( function() {
            //       post( 'pause', false , $(this) );
            //     });
            //   }
            //   post( 'play', null, $player );
            // }
        }//end of vimeo == _provider










        //YOUTUBE PLAYER API
        //https://developers.google.com/youtube/iframe_api_reference
        //https://developers.google.com/youtube/player_parameters
        //http://stackoverflow.com/questions/8869372/how-do-i-automatically-play-a-youtube-video-iframe-api-muted
        //http://css-tricks.com/play-button-youtube-and-vimeo-api/

        if ( 'youtube' == _provider ) {
            console.log('YOUTUBE VIDEO');
            var player;
            //important : the function onYouTubeIframeAPIReady has to be added to the window object
            //when wrapped in another closure
            window.onYouTubeIframeAPIReady = function () {
                console.log('onYouTubeIframeAPIReady');
                player = new YT.Player('youtube-video', {
                  events: {
                    'onReady': onPlayerReady
                    //'onStateChange': onPlayerStateChange
                  }
                });
            }

            //Play and Mute volume when player is ready
            function onPlayerReady() {
                <?php if ( wp_is_mobile() ) : ?>
                  player.playVideo();
                <?php endif; ?>
                _bind_youtube_events();
                console.log('ON PLAYER READY', player);

                $slideWrap.find('img').fadeOut('slow');

                if ( true == $_mute_volume ) {
                    player.mute();
                }
                else {
                    player.unMute();
                }
            }

            function _bind_youtube_events() {
              //Unmute on hover
              // if ( $_mute_volume && $_unmute_on_hover ){
              //   $slider_wrapper.hover( function() {
              //   if ( 0 != $('iframe[src*=youtube]', $(this) ).length )
              //     player.unMute();
              //   }, function() {
              //     if ( 0 != $('iframe[src*=youtube]', $(this) ).length )
              //       player.mute();
              //   });
              // }

              // Call the API on 'slid'
              $slider_wrapper.on('slid', function() {
                if ( true !== $_pause_on_slide)
                  return;
                var $activeSlide  = $slider_wrapper,
                    $player       = $('iframe[src*=youtube]', $activeSlide );

                //if current slide has no player. Pause all other players, else play this player if it was in pause
                // if ( ! $player.length ) {
                //   $slider_wrapper.find('iframe[src*=youtube]').each( function() {
                //     player.pauseVideo();
                //   });
                // } else if ( 2 == player.getPlayerState() ){
                //   player.playVideo();
                // }

              });//end on slid

          };//end _bind_youtube_events

          //load the youtube API script asynchronously
          czrapp.youtupeAPIloaded = czrapp.youtupeAPIloaded || false;
          if ( ! czrapp.youtupeAPIloaded ) {
              var tag = document.createElement('script');
              tag.src = "http://www.youtube.com/player_api";
              var firstScriptTag = document.getElementsByTagName('script')[0];
              firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

              czrapp.youtupeAPIloaded = true;
          }
        }//end if player == youtube

      });
    </script>
  <?php
}
?>




