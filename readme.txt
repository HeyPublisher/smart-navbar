=== Amazon Book Store ===
Contributors: loudlever
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Y8SL68GN5J2PL
Tags: affiliate, amazon, product, book store, affiliate sales,ASIN, Amazon Associate, monetize
Requires at least: 2.8
Tested up to: 4.1.0
Stable tag: 2.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sell Amazon products in sidebar widgets, unique to the individual POST or generically from a default pool of products that you define.

== Description ==

The Amazon Book Store widget gives you a simple way to sell Amazon products in the sidebar of your blog, magazine, or other WordPress-powered website.  The widget can be included multiple times, with different products being displayed in each instance.  

You can sell specific products on specific pages of your website, or define a 'pool' of products from which the widget will randomly chose.  This allows you to quickly build up an Amazon store of products that are related to your individual POSTs.  

== Installation ==

**Install the Widget**

Install the plugin, then activate it.  Once activated, configure the Widget:

* First, drag the 'Amazon Book Store' widget to your sidebar. 
* Where prompted, set the "Title" you want displayed above the widget
* Select how many product images you want displayed in the widget, 1 or 2.

**Configure the ASIN pool**

Navigate to 'Settings' -> 'Amazon Book Store'.  Input your Amazon Affiliate ID and country, the add the ASINs for the products you want to be displayed in the widget.  See [How to Find Amazon ASINs](http://askville.amazon.com/find-Amazon-ASIN-product-details-page/AnswerViewer.do?requestId=11106037) for more information.  There are two categorizations of settings:

* POST Specific:  If set, these products will be displayed when users are reading the designated POST.
* Default: If the request is to a POST that does not have specific ASINs defined, then the widget will display products from this group.

== Frequently Asked Questions ==

= Can I Use This Widget More Than Once In My Sidebar? =
Yes.  Absolutely.  The widget will keep track of which products have already been displayed on the page and will ensure that duplicates are not shown.

== Screenshots ==

1. Widget displayed in the Widget manager.
2. Widget displayed in the Side-Bar (your styling may differ)
3. Widget process flow, illustrating how the products are selected for display
4. ASIN configuration for POSTs and for Default pool

== Upgrade Notice ==

= 2.1.0 =
* Affiliate ID and Country are now managed on the Settings page - you will need to update these values after upgrading.

= 2.0.0 =
* Improved administration screen layout.
* WordPress 4.1 compliant.

= 1.1.2 =
* Upgraded to jQuery and made WordPress 4.x compatible.

= 1.1.1 =
* Fixed problem with wrapping of form labels on the Settings page.

= 1.1.0 =
* IMPORTANT: If you're upgrading from a previous version of the widget, please take note that the name of the widget has been changed from 'SupportGreatAuthors' to 'Amazon Book Store'.
* There is now a configuration management screen at 'Settings' -> 'Amazon Book Store' where you can define the default ASINs to display in the widget and/or associate specific ASINS to specific POSTs.
* This version delivers enhanced functionality and the ability to create a 'pool' of products which the widget will randomly select from.  Because of this functionality, when upgrading from a previous installed version, the plugin will do it's best to migrate your previous settings to the new configuration.  If you had multiple instances of the plugin installed in your sidebar, this migration may not work as smoothly as we'd hope.  Please check your configurations after installing.

== Changelog ==

= 2.1.1 =
* Fix upgrade error.

= 2.1.0 =
* Affiliate ID and Country are now managed on the Settings page - you will need to update these values after upgrading.
* Duplicate products are no longer displayed when you use the widget multiple times on a page.
* Widget no longer displays on page if there are insufficient unique ASINs for it's display.  

= 2.0.0 =
* Redesigned plugin and improved admin layout.
* WordPress 4.1 compliant.

= 1.1.3 =
* Fixed a layout problem where books and media product images had different widths.
* Updated description of plugin.

= 1.1.2 =
* Updated JavaScript in admin tool to make use of jQuery.
* Fixed issue with ASIN assignment for existing POSTS in Admin screen.
* Plugin is now WP 4.x compatible.
* Release date (12/8/2014)

= 1.1.1 =
* Fixed problem with wrapping of form labels on the Settings page.  
* Extra long POST titles are now truncated to 40 characters for display purposes.
* Removed the POST id from the display title. 
* Added screenshot showing Settings configuration screen.

= 1.1.0 =
* Changed name of sidebar widget to 'Amazon Book Store'
* Added configuration management screen at 'Settings' -> 'Amazon Book Store'
* Widget can now be used with UK, DE, FR and CA affiliate programs.
* Widget can be configured to display 1 or 2 product images in widget.
* Added ability to create a 'pool' of ASINs for POSTs, as well as a 'default' pool to be used as a last resort.   

= 1.0.1 =
* Updated screenshots to highlight the fact that users should change their Amazon Associate ID.
* Plugin is now owned and maintained by [Loudlever, Inc.](http://www.loudlever.com)
* Update Release (03/26/2010)

= 1.0 =
* Plugin Release (10/23/2009)
