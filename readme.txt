=== well-known-uris ===
Contributors: pfefferle, mrose17
Donate link: http://www.14101978.de
Tags: well-known, well-known-uris, discovery
Requires at least: 3.5.1
Tested up to: 4.6.1
Stable tag: 1.0.3
License: MPL2
    
"Well-Known URIs" for WordPress!

== Description ==

This plugin enables "Well-Known URIs" support for WordPress (RFC 5785: http://tools.ietf.org/html/rfc5785).

From the RFC:

> It is increasingly common for Web-based protocols to require the
> discovery of policy or other information about a host ("site-wide
> metadata") before making a request.  For example, the Robots
> Exclusion Protocol <http://www.robotstxt.org/> specifies a way for
> automated processes to obtain permission to access resources;
> likewise, the Platform for Privacy Preferences
> tells user-agents how to discover privacy policy beforehand.

> While there are several ways to access per-resource metadata (e.g.,
> HTTP headers, WebDAV's PROPFIND [RFC4918]), the perceived overhead
> (either in terms of client-perceived latency and/or deployment
> difficulties) associated with them often precludes their use in these
> scenarios.

> When this happens, it is common to designate a "well-known location"
> for such data, so that it can be easily located.  However, this
> approach has the drawback of risking collisions, both with other such
> designated "well-known locations" and with pre-existing resources.

> To address this, this memo defines a path prefix in HTTP(S) URIs for
> these "well-known locations", "/.well-known/".  Future specifications
> that need to define a resource for such site-wide metadata can
> register their use to avoid collisions and minimise impingement upon
> sites' URI space.

    
You will need 'manage_options' capability in order to use the Settings
page for this plugin.
    
== Changelog ==

= 1.0.3 =

* Fork from the original -- https://wordpress.org/plugins/well-known/ --
  many thanks to https://profiles.wordpress.org/pfefferle/ for the
  excellent plugin!

== Installation ==

1. Upload the `well-known`-folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the *Plugins* menu in WordPress
3. If you wish to define one or more Well-Known URIs that return static output,
   go to *Settings > Well-Known URIs* and define them, e.g.
    
    `Path: /.well-known/`
        robots.txt
    
    `Content-Type:`
        text/plain; charset=utf-8
    
    `URI contents:`
        User-agent: *
        Allow: /
4. If you want to configure a Well-Known URI that returns dynamic output,
   first, edit the plugin source to define a function invoked by
   `do_action` for the action `"well_known_uri_" + $path`. That function
    will be invoked when `/.well-known/${path}` is requested.

== Frequently Asked Questions ==

= How can I define a well-known uri? =

Set a callback for an URI (e.g., "/.well-known/robots.txt"),
identified by `"well_known_uri_" + $path` (e.g., `"well_known_uri_robots.txt"`).

    `add_action('well_known_uri_robots.txt', 'robots_txt');`

In the callback, do whatever processing is appropriate, e.g.,

    `function robots_txt($query) {
      header('Content-Type: text/plain; charset=' . get_option('blog_charset'), TRUE);
      echo "User-agent: *";
      echo "Allow: /";

      exit;
    }`

This code defines a URI that returns static output, as shown in Step 3 above.
(For static output, you will want to use *Settings > Well-Known URIs* page to
avoid writing any code.)    
    
= Is there an implementation where I can write off? =

Yes, you can find an example plugin, which defines a Well-Known URI,
here: http://wordpress.org/extend/plugins/host-meta/
