=== WordPress Font Customizer ===
Author URI: https://presscustomizr.com/
Plugin URI: https://presscustomizr.com/
Requires at least: 4.7
Requires PHP: 5.4
Tested up to: 5.7
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html


== Copyright
WordPress Font Customizer is a WordPress plugin designed and developed by Nicolas Guillaume (nikeo) in Nice, France (https://presscustomizr.com), and distributed under the terms of the GNU GPL v2.0 or later.
Enjoy it!


== Licenses
Unless otherwise specified, all the theme files, scripts and images
are licensed under GNU General Public License version 2, see file license.txt.


== DOCUMENTATION AND SUPPORT
DOCUMENTATION : https://docs.presscustomizr.com
SUPPORT : https://presscustomizr.com/support/


== Changelog
= 3.2.3 June 1st, 2021 =
* fixed : [accessibility] broken accessibility for checkboxes in the customizer

= 3.2.2 February 9th, 2021 =
* fixed : [javascript] don't print front scripts when WFC is deactivated

= 3.2.1 January 21st, 2021 =
* fixed : [effect] effect not applied on all expected selectors due to an error in the inline javascript code

= 3.2.0 November 30th, 2020 =
* improved : [performance][JS] remove webfontload library from front js
* improved : [performance][JS] write front js inline
* improved : [performance][CSS] write base front CSS inline + load stylesheet for effects only when needed
* improved : [performance][CSS] loads Google effect images locally

= 3.1.2 September 2, 2020 =
* fixed : Color picker CSS broken https://github.com/presscustomizr/hueman-pro-addons/issues/214

= 3.1.1 May 14th 2020 =
* added : [performance] add an option to disable totally Font Customizer if needed
* added : [performance] added display=swap parameter to Google fonts to ensure text remains visible during webfont load

= 3.1.0 April 20th 2020 =
* fixed : a possible php warning displayed when generating CSS rules for line-height

= 3.0.8 April 5th 2019 =
* improved : Implemented a unified style fox checkboxes in the customizer accross Press Customizr themes and plugin.

= 3.0.7 November 26th 2018 =
* Updated Google fonts.

= 3.0.6 November 16th 2018 =
* fixed : tc_is_customizing() => the check on $pagenow does NOT work on multisite install @see https://github.com/presscustomizr/nimble-builder/issues/240
* improved : replace select2 with our namespaced czrSelect2 wfc part of the fix for presscustomizr/themes-customizer-fmk#50

= 3.0.5 September 18th 2018 =
* updated Font Awesome icons
* updated the plugin updater
* updated the customizer framework
