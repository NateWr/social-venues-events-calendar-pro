=== Social Venues for Events Calendar Pro ===
Contributors: NateWr
Author URI: https://github.com/NateWr
Plugin URL: http://themeofthecrop.com
Tags: calendar, class, concert, conference, date, dates, event, events, google maps, meeting, modern tribe, Organizer, seminar, summit, tribe, venue, widget, workshop
Requires at least: 3.8
Tested up to: 3.8.1
Stable tag: 1.0
License: GPLv2 or later
Donate link: http://themeofthecrop.com

Add social media profiles to venues in Events Calendar Pro. This plugin requires the Events Calendar Pro plugin by Modern Tribe.

== Description ==

**This plugin requires the [Events Calendar Pro](http://tri.be/shop/wordpress-events-calendar-pro/ "Purchase Events Calendar Pro") plugin by Modern Tribe.**

Add social media profiles to venues in Events Calendar Pro. Add links to a venue's profiles on Facebook, Twitter and more. These profiles will automatically be displayed on the venue's page.

This plugin adds a new meta box to the venue editing page, which allows you to add and edit social media profiles. By default, it supports the following social networks:

* Facebook
* Twitter
* Google+
* Youtube
* Flickr
* Pinterest
* Foursquare
* Instagram
* LinkedIn
* Vimeo
* Weibo

This plugin deliberately does not include specific stylesheets to customize the output. If you want some basic CSS to use, see the FAQ page.

Please note that this plugin **will not work** with the free version of [Events Calendar](http://wordpress.org/plugins/the-events-calendar/ "View Events Calendar on the WordPress plugin repository"). It requires the ability to have saved venues, which is only available in the [pro version ](http://tri.be/shop/wordpress-events-calendar-pro/ "Purchase Events Calendar Pro") of the plugin.

= How to Use =

Once you have installed and activated this plugin, go to the page where you can add or edit a venue. Under the Venue Information panel, a new panel called Social Media Profiles will appear. Choose your display settings and add each profile here. Look for the "Add new profile" link at the bottom of that panel to add more.

= Developers =

This plugin uses the font icons from [Font Awesome](http://fortawesome.github.io/Font-Awesome/ "Icon font for the web") to display social media icons. The selection of available networks is based on the icons available in this pack.

However, this plugin provides filters so you can easily customize and extend it for your particular needs. These filters allow you to:

* Customize which social networks can be selected and the HTML output for each network
* Add or remove CSS stylesheets that are loaded on the front-end
* Customize the default metadata and settings.

Read the FAQ page for a few tips and [get in touch](http://themeofthecrop.com/support/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Social%20Venues%20for%20Events%20Calendar "Support at Theme of the Crop") if you have any questions.

This plugin is [on GitHub](https://github.com/NateWr/social-venues-events-calendar "Social Venues for Events Calendar Pro on GitHub"), so fork it up.

== Installation ==

1. Unzip `social-venues-events-calendar.zip`
2. Upload the contents of `social-venues-events-calendar.zip` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Browse to the Events > Venues page in the admin dashboard to add or edit a venue
5. Find the Social Media Profiles input panel below the Venue Information panel

== Frequently Asked Questions ==

= The icons don't display pretty. =

I've deliberately not included a separate stylesheet with styles for the icons. This will prevent another call to your server for such a tiny file. But if you want something a bit nicer and you don't know CSS well, add this to your theme's style.css file:

`.svecp-icon {
	min-width: 2.5em;
	display: inline-block;
	text-align: center;
}`

= Can I add another social network or use different icons? =

Yes! This plugin has hooks that will allow you to customize the services registered with this plugin. You'll need a little familiarity with PHP code and your theme's functions.php file.

Adding the following code to your theme's functions.php file would change the output of the Facebook icon to an image in your theme's directory:

`/**
 * Use an image icon for the Facebook profile
 */
function my_modify_network_function( $services ) {

	$services['facebook']['html'] = '<img src="' . get_stylesheet_directory_uri() . '/img/icons/facebook.png">';

	return $services;

}
add_filter( 'svecp_services', 'my_modify_network_function' );`

Of course, you'll need to have an image in your theme's directory, at `/img/icons/facebook.png', for this to display properly.

Adding another social network is just as easy:

`/**
 * Add a new network to choose from when editing a venue
 */
function my_new_network_function( $services ) {

    $services['new_network'] = array(
		'label'				=> __( 'My New Network', SVECP_TEXTDOMAIN ),
		'html'				=> '<img src="/path/to/icon/image.png">'
	);

    return $services;

}
add_filter( 'svecp_services', 'my_new_network_function' );`

If you don't want to use the FontAwesome icons, you can prevent the stylesheet from being loaded with the following code:

`/**
 * Prevent FontAwesome stylesheet from loading
 */
function my_custom_stylesheets_function( $stylesheets ) {

	$new_stylesheets = array();
	foreach( $stylesheets as $stylesheet ) {
		if ( $stylesheet['handle'] != 'font-awesome' ) {
			$new_stylesheets[] = $stylesheet;
		}
	}

	return $new_stylesheets;

}
add_filter( 'svecp_enqueue_stylesheets', 'my_custom_stylesheets_function' );`

I'll be describing how to use this and other filters to customize this plugin more extensively in future blog posts. So look for news posts at [Theme of the Crop](http://themeofthecrop.com/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Social%20Venues%20for%20Events%20Calendar "Learn more Theme of the Crop"), or follow me on [Twitter](http://twitter.com/themeofthecrop "Follow Theme of the Crop on Twitter") and [Google+](https://plus.google.com/+Themeofthecrop "Join Theme of the Crop at Google+").

= I want more features =

What do you want? I think this plugin works best if it's kept nice and simple, but if you've got other needs for your venues [let me know](http://themeofthecrop.com/support/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Social%20Venues%20for%20Events%20Calendar "Contact Theme of the Crop"). Maybe I can help.

== Screenshots ==

1. Venue display with just the icons
2. Venue display with icons and action text
3. Adding new profiles on the venue page

== Changelog ==

= 1.0 (2014-01-27) =
* Initial release
