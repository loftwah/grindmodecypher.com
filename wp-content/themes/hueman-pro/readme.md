# Hueman Pro v1.4.23
![Hueman - Pro](/screenshot.png)

> The premium version of the popular Hueman WordPress theme.

View more themes from this author: http://presscustomizr.com

## Demo and Documentation
* **Demo** : http://demo-hueman.presscustomizr.com/
* **Documentation** : http://docs.presscustomizr.com/article/236-first-steps-with-the-hueman-wordpress-theme


## Theme License
The **Hueman WordPress theme** theme itself is nothing but 100% GPLv3. See headers of files for further details.[GNU GPL v3.0 or later](http://www.gnu.org/licenses/gpl-3.0.en.html)


## Other Licenses
See headers of files for further details.

## Changelog
= 1.1.37 February 8th, 2020 =
* fixed : [html] element div not allowed as child of element button
* fixed : [html] Bad value for attribute datetime on element time
* fixed : adapt social links for Viber link type.
* fixed : style for .entry h2 span not compliant with accessibility standards
* fixed : featured image can be stretched when displayed in featured posts
* fixed : removed wrong href="#" on div elements in sharrre
* fixed : [pro header] html errors related to lazyloaded img in pro header
* added : new options to control the visibility of post metas ( date and categories ) in post lists

= 1.1.36 January 31st, 2020 =
* added : single post featured image => added new options, similar to the one of the page
* added : allow users to chose if featured image is cropped or not when displayed in a single post or page
* improved : implemented a CSS flexbox display for the search button

= 1.1.35 January 20th, 2020 =
* improved : [Header pro background] improved loading performances on mobiles
* added : sidebars => new options to customize the topbox default texts "Follow" and "More"

= 1.1.34 January 7th, 2020 =
* fixed : [Pro header] Slider element and slider js script have the same id attribute
* fixed : added noopener noreferrer relationship attributes to footer credit link
* added : an option allowing users to wrap the site title or logo in an H1 tag
* added : Flipboard icon to social icons
* added : [Pro header] let user set Hx tag globally and override this option on a per-slide basis

= 1.1.33 December 22nd, 2019 =
* fixed : old option favicon still printed, no way to remove it from the customizer
* fixed : social media links in the sidebar and footer should be using rel="noopener" or rel="noreferrer" when using target _blank
* fixed : search results as standard post list layout do not display page featured image
* fixed : sidebar icon toggles: namespace their CSS class name to avoid conflicting plugins issues
* fixed : custom widget zones: a static front page shows widget zones assigned to "Pages"
* fixed : The mobile menu doesn't automatically close when clicking on an internal anchor link item
* fixed : searchform input not compliant with latest accessibility standards + generating problems with cache plugins
* fixed : sidebar width in js code => localize width values instead of using hardcoded values

= 1.1.32 December 11th, 2019 =
* fixed : header's height and single post content layout can be broken in some cases
* fixed : removed spurious quote in the logo img tag
* fixed : [Pro Header] slider height can be too small on mobile landscape => site title text overlapping

= 1.1.31 December 2nd, 2019 =
* added : filters to WP_Query in Tabs widgets
* improved : reduce theme folder size
* updated : Nimble Builder recommendation notification in admin, and allow deactivation with a php constant

= 1.1.30 November 14th, 2019 =
* fixed : a bug with the latest version of Chrome browser which displayed an horizontal scrollbar in some cases
* improved : provide child theme info in config page
* improved : [Pro Header Background] allow user to use H1 tag instead of default H2 for slider title. Fixes https://github.com/presscustomizr/hueman-pro-addons/issues/195

= 1.1.29 October 22nd, 2019 =
* fixed : block editor quote "style large" issue
* improved : implement skip to content for TRT requirement

= 1.1.28 September 13th, 2019 =
* fixed : shortcodes not processed in html widgets

= 1.1.27 September 10th, 2019 =
* fixed : pro related posts grid that could break on mobile devices
* fixed : add do_shortcode filter cb to widget_text filter hook only if needed
* fixed : post format meta boxes for the block editor
* added : Classic and Masonry Grid => Allow a 1 column layout

= 1.1.26 August 29th, 2019 =
* fixed : admin notice style on mobile
* improved : better keyboard navigation to comply with new TRT requirements : https://make.wordpress.org/themes/2019/08/03/planning-for-keyboard-navigation/
* improved : option tree updated to v2.7.3

= 1.1.25 July 18th, 2019 =
* fixed : styling issue for comment form cookies consent not correctly displayed.
* fixed : linkedIn icon not displayed because of a js error
* fixed : pro header => improves SEO by replacing H1 tag by H2 when using a title
* fixed : masonry grid refreshed when changing orientation form portrait to landscapeand viceversa

= 1.1.24 June 30th, 2019 =
* fixed : get rid of the perspective property which causes issues in recent chrome versions
* improved : added image dimensions to the logo
* added : new option to control tags visiblity in single posts
* added : new options to control single author and date post meta visibility

= 1.1.23 June 4th, 2019 =
* improved : Hueman Pro custom widgets can now be overriden from a child theme

= 1.1.22 May 29th, 2019 =
* fixed : possible fatal error in admin

= 1.1.21 May 29th, 2019 =
* fixed : add image dimensions into header image customization using the standard wp function get_header_image_tag
* improved : add an option to control the singular page featured image visibility disabled by default

= 1.1.20 May 9th, 2019 =
* added : new wp_body_open theme Hook
* improved : sharre bar, replaced broken sharre count by "+" sign, removed deprecated Google Plus

= 1.1.19 April 24th, 2019 =
* fixed : smooth scroll throwing JS errors in latest chrome. fixes #787
* improved : sidebar => add an option to set an order on mobile devices. fixes #779

= 1.1.18 April 9th, 2019 =
* fixed : a bug with the images of featured pages not displayed sometimes.

= 1.1.17 April 5th 2019 =
* fixed : display correctly taxonomy/post_type_archive titles. fixes #750
* improved : new style for checkboxes in customizer controls. 

= 1.1.16 March 20th, 2019 =
* fixed : possible fatal error in WooCommerce single products

= 1.1.15 March 2nd, 2019 =
* fixed : Remove title attribute "Permalink To" on thumbnails links in post lists
* fixed : possible PHP error when upgrading server to PHP 7.0+

= 1.1.14 February 27th, 2019 =
* fixed : wp commentform cookies consent checkbox style. fixes #770
* fixed : search button in the topbar menu now displayed on tablet. fixes #653
* fixed : style blockquotes in comments. fixes #772
* fixed : infinite scrolling issue with Next Gen Gallery plugin
* improved : footer credits now uses parsable tags like {{year}}.

= 1.1.13 February 13th, 2019 =
* fixed : BBPress profile links displaying unwanted dots. fixes #765
* improved : social icon links => added support for links like tel:*** or skype:**** or call:****
* added : a new RGB+ alpha color control in the customizer, allowing transparency customization. fixes #767

= 1.1.12 February 11th, 2019 =
* fixed : Custom subheading option not reachable
* fixed : the blog category filter for pro infinite scroll
* fixed : social icon links like tel:*** or skype:**** or call:**** should be allowed
* fixed : the back to top icon font-size should be fixed and not in em
* fixed : removed title attribute "Permalink To" on thumbnails links in post lists
* fixed : white-space CSS rule problem with long tag
* improved : compatibility with Disqus comments system

= 1.1.11 January 16th, 2019 =
* improved : use default browser title tooltip for the social icons
* added : the option to filter the home/blog posts by category

= 1.1.10 December 19th, 2018 =
* fixed : php7.3 warning when using "continue" in a switch statement
* improved : style of the new WordPress block editor, especially to enlarge the editor content width

= 1.1.9 December 17th, 2018 =
* fixed : the contextualizer select is not properly initialized

= 1.1.8 December 16th, 2018 =
* fixed : admin style not compatible with WP5.0
* fixed : WooCommerce related product position in some cases. fixes #729
* improved : update FontAwesome to v5.5.0. fixes #727
* improved : compatibility with Nimble Builder v1.4.0
* improved : analytics params to external links

= 1.1.7 November 26th, 2018 =
* fixed : set only the featured posts thumbnail img width to 100%. fixes #703
* fixed : hu_is_customize_left_panel() => the check on $pagenow does not work on multisite install
* added : add sidebars background color option. fixes #718
* improved : add wp 5.0 compatibility patch. fixes #719
* improved : improve gutenberg alignment compatibility. fixes #702
* improved : Nimble Builder compatibility. Don't load css and javascript front assets when using the full Nimble template
* improved : replace select2 with our namespaced czrSelect2
* updated the Google Fonts list

= 1.1.6 November 7th 2018 =
* improved : make sure to use the more proper the_title_attribute wp function. fixes #713
* improved : add responsive embeds (videos) support for the new WordPress editor. fixes #708
* improved : compatibility with the Nimble Builder plugin
* added : social icons line, map, discord

= 1.1.5 October 25th 2018 =
* added : support for new WordPress editor, block cover image alignwide/alignfull
* improved : readme.txt file, according to the latest wordpress.org requirements

= 1.1.4 October 7th 2018 =
* fixed : Gutenberg reponsive video embed compatiblity issue. fixes #698
* fixed : improved child-theme support by making templates easier to override for developers
* improved : better Nimble section builder integration
* added : a new option for collapsible submenus in mobiles

= 1.1.3 August 1st 2018 =
* imp : adapt the post format metaboxes to the Gutenberg editor plugin
* fix: make the color of the external link icon the same as the user defined primary color

= 1.1.2 July 19th 2018 =
* fixed : url validation broken in the social links module
* added : new option to force the global column layout, even in contexts where it has been customized
* added : new option to disable the icon + text before the archive titles
* added : new option to use the original featured image size in grids
* added : Strava social network in the list of icons

= 1.1.1 June 28th 2018 =
* fixed : the Font Customizer was missing in the live customizer 

= 1.1.0 June 27th 2018 =
* Improvements : Performance and UX enhancements made to the live customizer. Compatibility with the contextualizer feature introduced in Hueman Addons v2.1.0.
* Added : the custom css can now be customized on each pages independently

= 1.0.28 February 14th 2018 =
* Fix : Multisite compatibility problem
* Fix : Title/Subtitle polylang plugin translation doesn't appear
* Imp : minor syntax issues in javascript code on front
* Added : new option to control the visibility of the sharre counters

= 1.0.27 February 8th 2018 =
* Fix : in admin make sure the stylesheet to fix the wp-footer position is printed in the relevant context
* Imp : add translation catalogue (pot file)
* Imp : add the latest WordPress.org langpacks
* Imp : update font awesome resources to the latest version (5)
* Imp : make function hu_is_authorized_tmpl pluggable so it can be redefined to include custom templates

= 1.0.26 December 15th 2017 =
* Fix : conflict with Polylang-Pro menu
* Improved : customizer javascript code

= 1.0.25 November 20th 2017 =
* Fix : WP 4.9 Code Editor issue could impact the custom css customizer option when checking errors in the code
* Fix : the infinite scroll button should inherit the user picked primary color

= 1.0.24 November 16th 2017 =
* Fix : custom css compatibility issue with WP4.9.

= 1.0.23 November 14th 2017 =
* Fix : slider customization compatibility with the WordPress 4.9 changes in the customizer

= 1.0.22 November 13th 2017 =
* Fix : Contact Form 7 recaptcha CSS style issue.
* Fix : incorrect css rules for .screen-reader-text.
* Improved : the featured image of a page should be displayed in search results.
* Improved : compatibility with WP4.9, target release date November 14th 2017

= 1.0.21 October 14th 2017 =
* imp : various improvements in the welcome / about admin page. Now includes a documentation search field
* improved : added a filter for the footer credits
* improved : performances => style.css doesn't have to be loaded if no child theme used.
* improved : a child theme css is now loaded after the css rules generated by the user options. fixes #577
* improved : user option generated css is now printed using wp_add_inline_style()

= 1.0.20 October 6th 2017 =
* fix : bottom portion of sidebar gets cut off in tablet view when content is short.
* fix : intermittent save error with the font customizer due to a possible null returned value by the sanitize callback
* fix : archive and page titles font-size.
* fix : potential loss of customizations when wp_cache_get() returns false.
* fix : social links not refreshing in the footer on initial customization ( when nothing printed yet )
* fix : headings hidden on mobile when no header image displayed.
* added : new option js-mobile-detect for optional javascript Mobile device detection. Loads the mobile-detect script ( 35 kb ) when checked
* added : mobile-detect.js library conditionally enqueued
* improved : replaced the ajax call by a javascript library to check if the device is mobile.
* improved : admin page wording and style
* improved : Footer credits made smaller. Now use the WordPress icon. Default link to the Hueman theme page instead of presscustomizr.com.
* updated : customize control js

= 1.0.18 August 2nd 2017 =
* improved : added support for pagination in pages using "nextpage". Compatible with the WP-PageNavi plugin.

= 1.0.17 August 2nd 2017 =
* fix : Infinite scroll produces php notice: array to string conversion issue
* fix : Infinite scroll: sometimes not all the posts are loaded

= 1.0.16 July 26th 2017 =
* fixed : fix wp.com sites managing compatiblity
* fixed : position of the header widget on mobile viewports
* improvement : site title and logo options are mutually exclusive

= 1.0.14 July 6th 2017 =
* fixed : more specificity for sidebars selectors in mobile viewports. Fixes #531.

= 1.0.13 July 4th 2017 =
* fixed : more css specificity added to the sidebars when building the dynamic style

= 1.0.12 July 3rd 2017 =
* fixed : more css specificity added to the sticky sidebars. Fixes #529
* fixed : the images of the featured post could be too high in some scenario. Two new image sizes have been added and a max-height depending on the culumn layout has been set in the css rules. fixes #525.

= 1.0.11 June 29th 2017 =
* fixed : huajax used to set the browser agent when the sticky sidebar is on might be too slow. Restrict the ajax query only when user has checked the sticky sidebars for either mobile devices or desktops, or both. Fixes #523.
* fixed : related posts should not inherit the main post-title fittext font-size
* added : new option in Adanced options > Mobile Devices > Make font sizes flexible. , responsive font-size is unchecked by default. Fixes #522

= 1.0.10 June 26th 2017 =
* updated : Pro Font Customizr : Google fonts collection updated to the latest
* fixed : when doing an ajax request on front, always make sure that the response is a well formed object, and fallback on the localized param if not
* updated : flexslider.min.js. Fixes : #511
* fixed : removed check_ajax_referer( 'hu-front-nonce', 'HuFrontNonce' ); when doing a front end ajax request. Should fix #512
* fixed : boxed - avoid header elements to horizontally overflow the viewport.fixes #508 3. and 4.fixes https://github.com/presscustomizr/hueman-pro-addons/issues/48
* fixed : when the layout is boxed + sticky header on on dekstop, the width of the header should be inherited from the used width (or default one ), and not rely on %.
* fixed : comment reply font size too small when viewed in mobile. fixes #504
* fixed : wp contact form 7 style. fixes #491
* fixed : on the blog page, the ( optional ) featured posts thumbnail size was not large enough when using a 1 or 2 columns layout. 'thumb-large' size is now only used for 3 columns layout. Fixes #350
* fixed: fix use of the add_editor_style wp function : needs relative paths + add rtl class to the inline font style in the wp editor see https://github.com/presscustomizr/customizr/issues/926
* added : a custom event "header-image-loaded" : partially fixes https://github.com/presscustomizr/hueman/issues/508
* replaced : hu_sanitize_hex_color() by core WP maybe_hash_hex_color doing the same job since WP 3.4
* improved : change page title tag from h2 to h1 to be consistent with single posts
* improved : increased .page-title font-size from 1em to 1.3em
* improved : .single .post-title from 2.375em to 2.62em => to make them taller than h2 title inside the content. Fix #515
* improved : 'header-mobile-sticky' classes shall not be added to the body element when 2 menus ( 'both_menus') are displayed on mobiles
* added : new localized params for a fittext implementation on front
* improved : slightly increased the max font-size of comments from 0.875rem to 0.93rem
* added : the headings ( Hx ) font size is now better resized for all type of devices with a dynamic resizing. Use fittext.js => based on the heading's parent container width, instead of relying on the css @media queries, not covering all device dimensions.
* added : include Custom Post Types in post lists ( archives and search results ). In archives, it handles the case where a CPT has been registered and associated with an existing built-in taxonomy like category or post_tag. Fixes #513
* added : new filters for hueman posts widget to alter the query args and the date format. Fixes #343
* added : '__before_logo_or_site_title' and '__after_logo_or_site_title' in hu_print_logo_or_title()

= 1.0.9 June 9th 2017 =
* fixed : Font Customizer, avoid double slashes when defining wfc base url. Double slash might hurt caching plugins as when specifying the path of the scripts to treat in a different the double slash might make the match fail. Which is what happens with w3tc for example.
* fixed : fix use of the add_editor_style wp function : needs relative paths. add rtl class to the inline font style in the wp editor see presscustomizr/customizr#926
* updated : Font Customizer, wfc customizer colors
* improved : Font Customizer, removed the requirejs dependency in conflict with WP4.8
* improved : Font Customizer, deactivate the add_editor_style callback on after_setup_theme. Was not used in the editor and could trigger errors.

= 1.0.8 June 6th 2017 =
* fixed : when topbar is sticky and header has an header image, wait for the image to be fully loaded before setting the header's height. Fix #486
* fixed Issue in hu_get_raw_option, php warning. wp_cache_get( 'alloptions', 'options' ) should always be cast to an array(). It might happen that it returns a boolean. fixes #492
* fixed : fix inaccurate smartload img regex pattern => file extensions were not correctly taken in account
* changed : hu_get_placeholder_thumb() to hu_print_placeholder_thumb(). Retrocompatibility handled.
* added : js matchMedia utility. fallsback on old browsers compatibility
* improved : in hu_set_option remove redundant retrieving of theme options

= 1.0.7 May 22th 2017 =
* fixed : related posts could be displayed twice in some specific cases where the server is slow
* fixed : when the top bar sticky header is enabled and the regular header image is displayed, the main header menu could be wrong positionned. Fixed by waiting for the header image to be fully loaded before firing the sticky topbar

= 1.0.6 May 17th 2017 =
* fixed : [pro] Featured content builder : title and subtitle length getting hidden when fixed title is enabled
* fixed : Compatibility issue with the Event Calendar plugin on date picker ( fixes #454 )
* fixed : wrong variable name in HU_utils::hu_cache_dp_options()
* fixed : search field background color in main header not inheriting the correct color
* fixed : desktop tobpar down arrow not showing up because fired too early
* fixed : sticky sidebars not properly disabled on tablets when option set
* fixed : php notice for undefined HUEMAN_VERSION constant in admin
* fixed : replaced OT_VERSION by time() for ot-admin-css as version param
* added a new option : in Header Design, "Apply a semi-transparent filter to the topbar on scroll." Enabled by default. ( fixes #469 )
* updated : Hueman Addons thumbnail
* updated : hu_related_posts by hu_get_related_posts. Retro compatibiliy handled in functions/init-retro-compat.php
* improved : esc_url gmpg.org/xfn/11 to better support https protocol
* improved : remove ot datepicker and timepicker - hueman doesn't use them fixes #454
* improved : customizer control visibility dependencies
* improved : get wp_is_mobile() on front with an ajax request. Fixed #470
* improved : utility hu_booleanize_checkbox_val()
* improved : Mobile menu, if the selected menu location has no menu assigned, the theme will try to assign another menu in this order : topbar, mobile, header.
* improved : mobile children menu items style
* improved : mobile menu search field centering and width. Use of css calc()
* improved : the header ads widget can now be displayed on top of the header image
* improved : tmpl parts/related-posts now loaded with hu_get_template_part() to easily override it
* added : mobile menu, specific for mobile devices
* added : mobile menu notice for admin user if not mobile menu assigned
* added : new option to set a specific logo for mobile devices
* added : new option to print the logo / title and tagline on top of the header image
* added : new option Display the site title in the header. Enabled by default
* added : include attachments in search results
* added : fitText jQuery plugin ( < 1kb )
* added : js ajax utility
* added : utility hu_user_can_see_customize_notices_on_front()
* added : filter 'hu_is_related_posts_enabled' as condition to display the related_posts tmpl
* added : new option to include attachment images in search results. In the customizer, Advanced Options > Search Results.
* added : [pro] New module : Better Related Posts. in the customizer > Main Body Design > Single Posts Settings. New module allowing users to customize the block of related posts : number of columns, number of posts, relationship ( tags, categories, post format, all criterias, none ), ordered by, heading text, lazy loading on scroll

= 1.0.5 May 8th 2017 =
* improved : better initialization process for the customizer preview when fired from appearance > themes.

= 1.0.4 April 30th 2017 =
* fixed : blog description rendering and blogdescription partial refresh
* fixed : hu_get_search_title printing icons
* fixed : fix IE11 js compatibility
* fixed : Sticky sidebar, disabling on mobile should be consistent with wp_is_mobile()
* fixed : header site title or logo is not anymore wrapped in a h1 tag
* added : desktop menus search field options. Users can now add the search field in top menu or in the header menu. Implemented for desktop and mobile devices.
* added : hu_get_id() utility
* added : HU_IS_PRO_ADDONS constant
* added : implemented pro link
* added : implemented a better sticky menu options. Users can now choose between : don't show on scroll, always visible, reveal on scroll up. Implemented for desktop and mobile devices.
* improved : front end javascript framework performances
* improved : sidebars are not sticky by default
* improved : style.css comments
* improved : WooCommerce compatibility
* improved : sharre bar behaviour for mobile devices and on scroll

= 1.0.3 April 19th 2017 =
* fixed : blogdescription partial refresh

= 1.0.2 April 18th 2017 =
* fixed : in the header slider. When the context was not is_singular(), the default title was only displayed when the parent scope was not customized.
* fixed : line height of the call to slider action button was too high

= 1.0.1 April 15th 2017 =
* imp : better centering of the slider nav icon
* updated : activation key classes

= 1.0.0 April 14th 2017 =
* Initial release
