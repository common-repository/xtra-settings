=== XTRA Settings ===
Contributors: fures
Donate link: http://www.fures.hu/xtra-settings/donate.php
Tags: WordPress settings, hidden settings, tips and tricks, tweaks, WordPress options
Requires at least: 3.7
Tested up to: 6.0
Stable tag: 2.1.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

XTRA adds 40+ hidden settings, tweaks and options to tailor your wordpress website in a clean format and super-light weight.

== Description ==
XTRA adds 40+ hidden settings, tweaks and options to tailor your wordpress website in a clean format and super-light weight. The plugin uses WP actions, filters, the .htaccess file and the wp-config.php file for setting options. This plugin also includes a manual Database backup, cleanup and optimization tool to make your wordpress website lighter and faster. Also included are Related Posts, Share Buttons, WP Auto-Update, Maintenance Mode and Debug Mode with easy switches. Image compression organized in bulk ajax batches for fast speed and convenience.

= Features =
* Security - Server hardening, WP Security settings, disable xml-rpc and all feeds
* Speed - Compression, Cache, Memory and Minifier
* SEO - settings and JavaScript Defer and Footer
* Social - settings and Share Buttons, 2 blocks, 6 positions
* WP Settings - Admin, WP mods, Cron, Maintenance and Debug modes
* Update - auto-update core, themes, selected plugins, translations
* Hits Counter - simple, slim, usable
* Post settings - Related Posts, Revisions, Content changes
* Database - Backup, Cleanup and Optimize
* Cron Jobs - check and delete
* Plugins - temporarily mute
* Images - bulk image compression, maximum size, regenerate thumbs

== Installation ==
1. Install the plugin through the WordPress plugins screen directly or upload the plugin files to the `/wp-content/plugins/xtra-settings` directory.
2. Activate the plugin through the 'Plugins' screen in WordPress Admin.

== Screenshots ==
1. **XTRA Settings Security screen**
2. **XTRA Settings Database Cleanup and Optimize screen**
3. **XTRA Settings Speed screen**
4. **XTRA Settings WP Settings screen**
5. **XTRA Settings Posts and Content screen**
6. **XTRA Settings WP Modes screen**

== Frequently Asked Questions ==
* Please make a backup of your .htaccess and wp-config.php files.
* Some options don't work if your .htaccess and wp-config.php files are not writable.
* Plugin deactivation restores all WP settings.
* Plugin delete makes a complete clean-up of all XTRA settings.

== Changelog ==

= 2.1.8 =
* Fixed Website under Maintenance error at xtra-settings/xtra.php:3499 with mb_strrpos()

= 2.1.7 =
* Compatibility with WP 6.0

= 2.1.6 =
* Compatibility with WP 5.8

= 2.1.5 =
* Compatibility with WP 5.7
* Tweaked Restore options form: asking if include the Hit Counter data

= 2.1.4 =
* Compatibility with WP 5.6
* Added Backup and Restore of all your current settings - in a right side box
* Added Remove Admin Notices - at 16.7
* Added Delete selected images
* Fixed some PHP 8+ warnings and notices - hopefully
* Fixed geoip URL to https://freegeoip.app/json/
* Tweaked to not create empty wp-config if not exists
* Tweaked initial settings to show colors, numbers and hits chart

= 2.1.3 =
* Fixed some more PHP 7+ notices

= 2.1.2 =
* Compatibility with WP 5.5
* Added Themes Auto-Update exclude list

= 2.1.1 =
* Fixed Hit Counter notification email sending multiple times

= 2.1.0 =
* Removed most details from Hits Counter notification email to decrease spam rating
* Added option to still enable all details in Hits Counter notification email
* Removed custom send time from Hits Counter notification email. Mail send is at day-change: midnight by server time.

= 2.0.9 =
* Fixed Hits Counter analytics for Countries, IPs and Pages showing 0
* Fixed Hits Counter analytics links for Pages
* Remove onclicks from Hits Counter notification emails
* Remove date_default_timezone_set as it interferes with WP Site Health check

= 2.0.8 =
* Fixed htaccess redundant comments
* Fixed some more PHP 7+ notices
* Export DB Tables function source reference is corrected based on ttodua's request

= 2.0.7 =
* Compatibility with WP 5.4
* Fixed deprecated create_function PHP 7+ warnings
* Fixed exif_read_data PHP 7+ warnings
* Fixed PHP 7+ warnings and majority of notices (none on my system)
* Fixed some css for xsmall and restore buttons
* Fixed position of lightbulb icon on the admin bar

= 2.0.6 =
* Compatibility with WP 5.3
* Fixed javascript exclude at Open all external links in new tab

= 2.0.5 =
* Fixed Hits Counter daily mail sent multiple times
* Added Icon spacing for Social Share icons
* Added require authentication for REST API requests
* Added Disable Self Pingback

= 2.0.4 =
* Fixed Hits Counter daily mail sent multiple times
* Fixed Start Auto-Update Check button
* Tweaked Social Share icons zooming method

= 2.0.3 =
* Compatibility with WP 5.2
* Fixed Hits Counter daily mail sent multiple times
* Fixed wrong share button links caused by output buffer order

= 2.0.2 =
* Compatibility with WP 5.1
* Fixed Hits Counter daily mail sent twice
* Fixed Export Database header already started warning in some rare cases

= 2.0.1 =
* Fixed placement of Facebook SDK into wp-footer
* Fixed php warning: A non-numeric value encountered in …/plugins/xtra-settings/xtra.php on line 1066
* Tweaked php error reporting by disabling display of php warnings

= 2.0.0 =
* Fixed placement of Facebook SDK root div on singular pages

= 1.8.5 =
* Compatibility with WP 5.0

= 1.8.4 =
* Fixed warning in freegeoip.net calls in case the call is forbidden

= 1.8.3 =
* Tweaked Add self-link to all uploaded images in posts by linking to full size image

= 1.8.2 =
* Fixed some bugs

= 1.8.1 =
* Fixed a very insidious bug in Shorten the Title in post-lists
* Added Add time() as query string if Debug Mode is ON (this ensures instant refresh in development stage)
* Fixed is_customize_preview() not returning good results
* Fixed Remove query strings: only from script and css filenames (.js, .css)

= 1.8.0 =
* Added hide shortcode if not used for Social Share Buttons and Related Posts
* Fixed shortcode bug at Related Posts
* Fixed some css and positioning for Social Share Buttons and Related Posts

= 1.7.9 =
* Added Disable Comments globally into WP Settings WordPress Mods
* Added inline-block option for Social Share Buttons header
* Fixed some css and positioning for Social Share Buttons

= 1.7.8 =
* Fixed warnings of in_array() in xtra.php on line 1892
* Tweaked html places for Share Buttons and Related Posts

= 1.7.7 =
* Fixed a bug at 17.5 Auto-Resize Image Uploads

= 1.7.6 =
* Tweaked Images to show current quality ratio for JPGs
* Tweaked Hit Counter's User Agent analysis to improve mobile device recognition in Hit Log
* Added SEO add meta tags options separated as Description, Keywords and Robots
* Added Shortcodes selector in editor (25.7)
* Tweaked some code and reordered own options panel

= 1.7.5 =
* Tweaked some js localisation in admin-search.js
* Fixed a page link in Hit Counter if WP is not in the root folder
* Tweaked adding FeedBurner into Google group in Hit Counter
* Added new sub-options to Exclude from count & Don't log in Hit Counter
* Removed deprecated Enable WordPress Cache option (6.1)

= 1.7.4 =
* Fixed some translation related bugs in js
* Fixed a text bug in Memory and PHP Execution values (7.1, 7.2)
* Tweaked some script texts for localisation

= 1.7.3 =
* Fixed a translation related bug

= 1.7.2 =
* Added Top Countries, Top IPs and Top Pages tabs (23.4, 23.5, 23.6) to Hit Counter
* Tweaked Require a Featured Image (26.1) action priority
* Tweaked Related Posts by adding category filter (in 24.1)
* Added Don't mix categories (24.4) to Related Post
* Added 2nd Share Buttons block (15)
* Added 2 new shapes to Share Buttons blocks (14.2, 15.2)
* Added Facebook native Like button to Share Buttons blocks (14.3, 15.3)
* Added a new position (after title) in Share Buttons and Related Posts (14.3, 15.3, 24.2)
* Tweaked Share buttons settings by adding category filter (in 14.1 and 15.1)
* Added translation-ready outputs
* Tweaked css for small screens

= 1.7.1 =
* Fixed Change default WP email Sender Address
* Tweaked xtra_options_save action priority at shutdown
* Tweaked get_optionXTRA function against isset function anomalies on global arrays
* Tweaked name space and names in CSS files
* Tweaked and reorganized admin settings include
* Tweaked tab design
* Added text domain xtra-settings for getting closer to translation-ready
* Added option numbers for better reference with option to show/hide
* Added disable option for showing colors
* Added disable option for showing right side boxes
* Added option to put own XTRA plugin settings on a tab
* Added optional sticky tab when scrolling

= 1.7.0 =
* Fixed code of Allow PHP in text widgets

= 1.6.9 =
* Fixed extract_from_markers function bug in WP 4.9
* Tested with Wordpress 4.9

= 1.6.7 =
* Tweaked Mute/Un-Mute Plugins, added some explanations
* Tweaked xtra js and css loading in admin area

= 1.6.6 =
* Tweaked css for light and vertical formats
* Fixed icon usage in Hit List

= 1.6.5 =
* Added Mute/Un-Mute Plugins: i.e. silently disable plugins without actual deactivation
* Tweaked option filter javascript regex for highlighting
* Added DB Tables view linked from the Site Info box

= 1.6.4 =
* Added Block access by Targeted Page
* Tweaked Block access: Admin pages are not allowed to block to avoid suicide
* Tweaked Block access: block_visitors.php is added that you can rename in case of accidental self-block
* Fixed Hide Admin Toolbar fatal error for get_plugins function (not being auto included on front-end by WP)
* Tweaked user capabilities instead of roles (roles are not being reliable as per WP documentation)
* Tweaked further restrict XTRA Settings: even the page view is only for admins
* Tweaked Extend WordPress Search to include taxonomy descriptions (not only names)

= 1.6.3 =
* Tweaked plugin data storage into one option array instead of more than 100 option variables

= 1.6.2 =
* Fixed mu-plugins directory missing error

= 1.6.1 =
* Added WP-Options abandoned orphan option search and delete in XTRA settings box
* Added type icons to Hit Counter hit list
* Added separate page for Hit Counter all hit list with filters
* Tweaked filters for Hit Counter hit list
* Fixed execution time measurement for geoIP and host lookup array handling
* Fixed to long floating numbers in execution time measurement array

= 1.6.0 =
* Added execution time measurement for geoIP and host lookup in Hits Counter options
* Fixed Cron jobs delete failure
* Fixed the alternative position Hit Counter with js tabs and filter in hit list

= 1.5.9 =
* Added Disable WP Heartbeat with optional exception for post/page editor pages
* Tweaked Hit Counter with js tabs and filter in hit list
* Fixed Hit Counter email next date display
* Fixed Hit Counter email title language
* Fixed some PHP notices
* Tweaked include ajax.php only when admin-ajax called

= 1.5.8 =
* Tweaked Hit Counter hit list to optionally show also skipped hits (with reason for skipping)
* Added user names in hits for logged in users
* Tweaked Hit Counter email send with optional time for sending

= 1.5.7 =
* Added optional light-weight GeoIP info for Hits Counter
* Added Apache Cache expiration days option
* Applied name space in css to avoid conflicts
* Added JS box resizing for the admin page

= 1.5.6 =
* Tweaked Hit Counter exclude strings
* Added Redirect bad bots by substrings
* Added option to use/or not the slow gethostbyaddr php function for host name lookup
* Added Restore Default buttons for each input field
* Fixed missing array indexes from _SERVER

= 1.5.5 =
* Fixed Hit Counter exclude strings bug

= 1.5.4 =
* Fixed Hit Counter day-change bug
* Tweaked Hit Counter to show detailed Hits and/or IPs
* Removed Wonderslider plugin bug fix

= 1.5.3 =
* Added Regenerate Thumbnails & Image Sizes in Images tab
* Added Hits Counter exclude list for user-agent, remote server and IP substrings
* Tweaked Redirect some bots to referring URL code: deprecated previous function
* Tweaked Block external POST code: deprecated previous function
* Fixed time zone setting in xtra.php
* Fixed Hit Counter action hook to shutdown

= 1.5.2 =
* Added Hide Notification for Excluded Plugins
* Tweaked Hit Counter functions
* Added Add XTRA Hit Counter Dashboard Widget
* Added Add XTRA Menu to Admin Bar

= 1.5.1 =
* Added Search (filter) for options on the top
* Added Hits Chart with Google Charts API
* Tweaked optional tab settings

= 1.5.0 =
* Added Simple Hit Counter in XTRA options box with daily stats email sending
* Tweaked Bulk Image Compression and Resize with memory usage monitoring and restart if needed
* Fixed a bug in Debug mode

= 1.4.9 =
* Tweaked Add alt tag to images: take title if set
* Fixed Social Share Buttons wrong url when using Related Posts

= 1.4.8 =
* Added Extend WordPress Search: Search not only in title and post content, but also in tags, categories and comments
* Added Highlight Search Results
* Added Shorten the Title in non-singular views
* Tweaked Open ALL links in a new tab code

= 1.4.7 =
* Added disable wpautop globally
* Added thumbnails in posts admin list
* Added column shortcodes
* Fixed deactivate function call
* Fixed admin message show
* Fixed bulk image compression ajax error handling
* Added Harden your HTTP response header including Protect against XSS attacks, Page-Framing, Click-Jacking and Content-Sniffing in one option
* Added Disable WP Cron

= 1.4.6 =
* Added Ajax Bulk Image Compression methods with image backup and restore
* Added Auto-Resize large image upload to maximum width and height
* Tweaked Protect From Malicious URL Requests: including HTTP_USER_AGENT libwww, Wget, EmailSiphon, EmailWolf
* Tweaked php output buffer handling to increase code speed
* Tweaked JS and CSS position in the footer and defer parsing
* Tweaked regexp for add self-link, share buttons and related posts positioning

= 1.4.5 =
* Added Highlight Post Color by Status
* Added Defer parsing of all JavaScript in the SEO tab
* Added Move all JavaScript to the footer in the SEO tab
* Added Light View and Vertical Tabs in Xtra Plugin Settings
* Tweaked UI with sticky selected tabs
* Removed block URL longer than 255 from malicious requests in Security tab

= 1.4.4 =
* Fixed Share Buttons and Related Posts after article position

= 1.4.3 =
* Include section dashicons to make recognition easier
* Some tweaks on Share Buttons and Related Posts positioning
* Tweaked the gui to clear design and improve readability

= 1.4.2 =
* Added Related Posts feature with options in Posts tab
* Fixed Cron job removal
* Tweaked meta keywords on non-tagged pages
* Tweaked show share buttons with [xtra_share_buttons] shortcode
* Tweaked the HTML Minifier code working with inline img codes (e.g. Ploylang flags)

= 1.4.1 =
* Added Check Auto-Update Now trigger button
* Added Plugins Auto-Update exclude list
* Added Remove double title meta tag in SEO
* Fixed readme bug in Upgrade Notice
* Fixed SEO bug adding head meta and OG tags
* Tweaked Add self-link to all uploaded images
* Tweaked the HTML Minifier code to be more reliable

= 1.4.0 =
* Tweak Open All Links in New Tab and Image Self Link regexes
* Tweak plugin deactivation method
* Tweak getting post image as thumbnail or 1st attachment or 1st in-post image
* Removed the tab called All
* Added color codes for tabs
* Added HTML Minifier

= 1.3.9 =
* Added X-Header Apache security settings to protect 3 attack types
* Finetuned Apache server settings for Compression and Caching
* Separate Apache server settings in case you have a different web server

= 1.3.8 =
* Fixed Social share buttons not just on posts, also on pages

= 1.3.7 =
* Added Social tab
* Added 8 Social share buttons in the Social tab
* Added Facebook JS SDK block option
* Added Redirect Attachments to their Parent Post
* Repositioned 2 redirect settings into the SEO tab

= 1.3.6 =
* Added Cron Jobs management
* Added SEO tab
* Added Facebook OG tags and Twitter cards in HTML head using post title, excerpt and thumbnail image
* Remove Plugins Information - did not add extra value
* Optimized tabs: put all WP settings into one tab

= 1.3.4 =
* Tabbed user interface
* Added Maintenance mode and Plugins Information

= 1.2.1 =
* Redesigned user interface
* Added Database Backup, Cleanup and Optimize features

= 1.1.0 =
* Added WordPress Debug mode settings
* Added WordPress Auto-Update settings

= 1.0.0 =
* Initial version

== Upgrade Notice ==

= 2.1.8 =
For the best user experience, always upgrade to the latest version.