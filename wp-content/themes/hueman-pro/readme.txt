# Hueman Pro v1.4.23
![Hueman - Pro](/screenshot.png)

> The premium version of the popular Hueman WordPress theme.

View more themes from this author: http://presscustomizr.com

## Demo and Documentation
* **Demo** : http://demo-hueman.presscustomizr.com/
* **Documentation** : http://docs.presscustomizr.com/article/236-first-steps-with-the-hueman-wordpress-theme


## Theme License
The **Hueman Pro WordPress theme** theme is licensed under GPLv3. See headers of files for further details.[GNU GPL v3.0 or later](http://www.gnu.org/licenses/gpl-3.0.en.html)


## Other Licenses
See headers of files for further details.

## Changelog
= 1.4.23 May 26rd 2022 =
* fixed : [PHP] fix pagination issue.
* fixed : [PHP] fix date formate issue with the widgets.

= 1.4.22 May 23rd 2022 =
* fixed : [PHP] responsive issue for safari browser.

= 1.4.21 December 2nd 2021 =
* checked : [WP 5.9] preliminary successfull tests with upcoming version of WordPress.
* fixed : [PHP] possible php notice in rare cases when getting img source.

= 1.4.20 November 12th 2021 =
* fixed : [javascript] print a notice ( using console.log) when an issue is detected with JQuery

= 1.4.19 October 27th 2021 =
* fixed : [HTML] removed type attribute for script elements
* fixed : [HTML] removed type attribute for style elements
* fixed : [CSS] The first argument to the linear-gradient function should be "to top", not "top" as per w3 specification
* fixed : [CSS] value hidden doesn't exist for property webkit-backface-visibility

= 1.4.18 October 6th 2021 =
* improved : [HTML][PHP] removed superfluous title attributes "Permalink To" post-title hovering
* improved : [HTML] better HTML5 semantic
* fixed : [admin] update notice improvements

= 1.4.17 September 20th 2021 =
* fixed : [PHP] improved compatibility with PHP 8.0

= 1.4.16 September 10th 2021 =
* improved : update notice now includes a link to changelog
* added : sms and map icon in social list
* improved : design of the theme's admin page

= 1.4.15 August 25th 2021 =
* fixed : [PHP] wrong usage of ob_end_clean() could break the html page structure

= 1.4.14 July 28th 2021 =
* fixed : [PHP8] theme updater possible error with PHP8

= 1.4.13 July 24th 2021 =
* fixed : [block editor] query loop block style broken.

= 1.4.12 July 21st 2021 =
* 100% compatible with WordPress 5.9
* fixed : [WP 5.9] removed call to deprecated filter 'block_editor_preload_paths'

= 1.4.11 July 1st, 2021 =
* fixed : option for external links (icon + open in new tab) not working for links inside lists

= 1.4.10 June 21st, 2021 =
* fixed : [HTML5] remove W3C deprecated attributes for script and style tags
* fixed : PHP Notice: Trying to access array offset on value of type bool

= 1.4.9 April 24th, 2021 =
* fixed : [post slider][rtl] regression introduced in #916 : featured posts slider hidden on rtl websites
* fixed : [Gutenberg] buttons block in line style broken
* fixed : [widget][category] when displaying category hierarchy, children categories the categories should have an horizontal padding
* fixed : [gutenberg] CSS bug on mobile view when image added with gutenberg and set to full width
* fixed : [gutenberg] editor style for alignfull alignwide

= 1.4.8 March 17th, 2021 =
* fixed : [menu] dropdown Menus not staying down when selected in mobile landscape mode

= 1.4.7 March 3rd, 2021 =
* fixed : [customizer] scope params not properly passed when registering contextualized modules, leading to wrong setting titles.

= 1.4.6 March 2nd, 2021 =
* successfully tested with WP 5.7 scheduled for March 9th
* fixed : [Post Nav] "Next story" printed in the post title on Google news tab
* fixed : [PHP 8.0] Required parameter $xxx follows optional parameter $yyy
* fixed : [Font Customizer][javascript] don't print front scripts when WFC is deactivated

= 1.4.5 February 2nd, 2021 =
* fixed : [header slider] prevent poor image quality on mobiles when using Chrome ( and potentially other browsers )
* updated : Font Awesome icons to latest version (v5.15.2)
* added : [social links] added Tiktok icon

= 1.4.4 January 22nd, 2021 =
* fixed : [font color] excerpt font color not accessible => too low constrast ratio
* fixed : [WP editor] buttons link should not be underlined
* fixed : [font customizer] effect not applied on all expected selectors due to an error in the inline javascript code

= 1.4.3 January 18th, 2021 =
* fixed : [admin] removed unused option-tree code
* fixed : [audio post format] Audio player does not work in audio article format
* added : [social icons] mastodon icon
* improved : [SEO] allow site title to be wrapped in a H1 tag when home is a static page

= 1.4.2 January 6th, 2021 =
* fixed : [WP nav menu widget] menu items icon not displayed if menu encapsulated in custom wrapper
* fixed : [SEO] For best SEO results, ensure that home page includes only one H1 tag

= 1.4.1 January 4th, 2021 =
* fixed : [customizer][contextualizer] a control could be registered multiple times
* fixed : [customizer][contextualizer] pro header slider control not registered in some cases due to wrong params

= 1.4.0 December 14th, 2020 =
* fixed : [PHP 8] Fix deprecation notices for optional function parameters declared before required parameter

= 1.3.10 November 30th, 2020 =
* fixed : [performance][Sharrre buttons] include js assets only when relevant, on single 'post' type
* fixed : [WP5.6][WP5.7] remove jquery-migrate dependencies
* fixed : [performance][related posts] load javascript assets only when relevant
* improved : [Font customizer][performance][JS] remove webfontload library from front js
* improved : [Font customizer][performance][JS] write front js inline
* improved : [Font customizer][performance][CSS] write base front CSS inline + load stylesheet for effects only when needed
* improved : [Font customizer][performance][CSS] loads Google effect images locally

= 1.3.9 November 19th, 2020 =
* added : [CSS][links] added a new option to opt-out underline on links. Option located in customizer > web page design > General design options

= 1.3.8 November 17th 2020 =
* fixed : [TRT requirement][accessibility] Links within content must be underlined
* improved : [WP 5.6][jQuery] adapt to WP jQuery updated version. Prepare removal of jQuery Migrate in future WP 5.7
* fixed : [Nimble Builder compatibility] lazy loading broken for post thumbnails in post lists when using NB header

= 1.3.7 November 3rd, 2020 =
* tested : [WordPress] Hueman Pro v1.3.7 is 100% compatible with WP 5.5.3
* fixed : [Header banner] Added a new option to disable header image linking to home when no site title/description
* fixed : [Infinite scrool] WooCommerce, if infinite scroll is not supported,remove the "load more products" button
* added : [Infinite scroll] implement a new filters 'czr_infinite_scroll_handle_text' allowing developers to replace the text "Load more..." by a custom one

= 1.3.6 October 9th, 2020 =
* improved : [performance] implement preload for Font Awesome icons
* improved : [performance] preload Titillium self hosted font when used
* improved : [performance] set Titillium self hosted font as default font

= 1.3.5 October 7th, 2020 =
* fixed : [CSS][plugin compatibility] Code Syntax Block style broken
* added : [CSS] add current theme version as CSS class to body tag

= 1.3.4 September 18, 2020 =
* fixed : [admin] potential vulnerability issue 

= 1.3.3 September 9, 2020 =
* fixed : [customizer] Color picker CSS broken
* Successfully tested with WP 5.5.1

= 1.3.2 August 15, 2020 =
* fixed : [customizer] javascript problem leading to broken features like social links, background color

= 1.3.1 August 12, 2020 =
* fixed : [featured posts slider][CSS] featured post slider broken on some browsers due to wrong CSS rule.

= 1.3.0 July 21st, 2020 =
* Hueman Pro has been successfully tested with WordPress 5.5
* fixed : [forms] padding in select input breaks text readability
* fixed : [Gutenberg] CSS rules for table alignment not specific enough
* fixed : [compatibility with WP5.5] adapt customizer color-picker script with latest version of WP 5.5

= 1.2.13 June 18th, 2020 =
* fixed : [performance] Defer loading Font Awesome icons is disabled by default to prevent issues ( with broken javascript and/or third party plugins )

= 1.2.12 June 17th, 2020 =
* fixed : [Font Awesome] icons could not be printed in cases when a third party plugin loads FA
* fixed : [SEO] prevent printing mutliple H1 for site-title

= 1.2.11 June 14th, 2020 =
* fixed : [CPT] single CPT page missed a title. Reported for Sensei LMS plugin
* fixed : [Font awesome][performance] consider enabling defer_font_awesome by default
* fixed : [external links icons] icones should be inside the a tag to be clickable
* fixed : [featured image][single page] option to control featured image in single page is broken when using page-templates/child-menu.php
* fixed : [SVG upload] removed support for svg upload as per new TRT rules
* fixed : [favicon] removed retro compatibility for old favicon as per new TRT rules
* fixed : [admin] removed loading of remote cloudfare CDN js script as per new TRT rules. + removed unused js scripts    
* improved : [performance] better defer loading of Font Awesome
* added : [metas][post grids] add an option to display authors in post grids

= 1.2.10 May 16th, 2020 =
* fixed : [option tree] possible PHP error in admin
* improved : [TRT] added min PHP version required and WP version tested up to
* added : [social links] new options to control the visibility of the social links in sidebar and footer
* added : [font customizer][performance] add an option to disable totally Font Customizer if needed

= 1.2.9 May 4th, 2020 =
* fixed : [javascript] External link icon not displayed on pages
* fixed : [related posts] image dimensions are not consistent accross column layout
* improved : increased max size of singular featured images

= 1.2.8 April 27th, 2020 =
* fixed : [performance][srcset attribute] => limit browsers choice for srcset on high resolution device
* fixed : [Font Customizer] a possible php warning displayed when generating CSS rules for line-height
* improved : [post grids] adapt grid max size for srcset according to user options for image and pro grid columns
* added : [post grids] 2 new image sizes for masonry grid

= 1.2.7 April 19th, 2020 =
* fixed : [Flexslider][RTL] flexslider broken in RTL mode since updated to flexslider v2.7.2

= 1.2.6 April 18th 2020 =
* fixed : [Header][banner image] add options to handle height and width of the image
* fixed : [header image][sticky header] header height might not be set to the correct value on page load
* fixed : [Lazy load] images are not lazy loaded when dynamic content is inserted in the DOM ( example with TablePress plugin )
* fixed : [Nimble Builder compatibility] on home, Nimble Builder sections inserted on hooks __before_featured and __after_featured are not rendered when featured posts are disabled
* improved : [performance][related posts] adapt image size of related posts depending on the current column layout.
* improved : [performance][Dynamic Tabs widget] reduce image size of Dynamic Tabs widget

= 1.2.5 April 13th 2020 =
* fixed : [javascript] make sure $.fn.fitText plugin is loaded before invoking it.
* fixed : [options] missing quote broke the json of candidates for filtrable options
* fixed : [options] setting set_theme_mod might break previous theme mods when too large and invoked multiple times

= 1.2.4 April 3rd, 2020 =
* fixed : [options] potential issue when creating the list of contextualizable options

= 1.2.3 March 27th, 2020 =
* fixed : [mobile menu] clicking on an anchor link that has child submenu should unfold the submenu
* improved : [Header] banner image => make it easier to adjust width automatically
* improved : [performance] load flexslider.js with defer
* improved : [performance] removed smoothscroll option and script
* updated : [javascript] flexslider.js to v2.7.2
* updated : [javascript] mobile-detect.js to v2.8.34

= 1.2.2 March 19th, 2020 =
* fixed : [standard grid] left padding broken on mobile devices
* improved : [performance] lazyloading threshold set to 0 instead of 200px by default

= 1.2.1 March 18th 2020 =
* fixed : [masonry] Masonry grid could be hidden when pro header disabled

= 1.2.0 March 17th, 2020 =
* fixed : [performance] flexslider.js can be loaded on blog page even when featured posts are disabled
* fixed : [CSS] prefix .pad class more specifically to avoid potential conflict with plugins
* fixed : Use the child-theme version when enqueueing its style
* improved : [asset] update fontawesome to latest version ( current is 5.5.0 )
* added : [post lists] introduce a new option to allow full post content to be displayed when using "standard" grid
* added : [post lists] introduce a new option to allow hide the post thumbnails in post lists
* added : [performance] new option to load main script with defer mode
* added : [performance] new option to defer loading of FontAwesome to avoid blocking rendering issues

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
