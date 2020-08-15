# Hueman Pro v1.3.1
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
= 1.3.1 August 12, 2020 =
* fixed : [featured posts slider][CSS] featured post slider broken on some browsers due to wrong CSS rule.
* Hueman has been successfully tested with WordPress 5.5

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
