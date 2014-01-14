=== /.well-known/ ===
Contributors: pfefferle
Donate link: http://www.14101978.de
Tags: well-known, discovery
Requires at least: 3.5.1
Tested up to: 3.8
Stable tag: 1.0.1

This plugin enables "Well-Known URIs" support for WordPress

== Description ==

This plugin enables "Well-Known URIs" support for WordPress (RFC 5785: http://tools.ietf.org/html/rfc5785).

From the RFC:

   It is increasingly common for Web-based protocols to require the
   discovery of policy or other information about a host ("site-wide
   metadata") before making a request.  For example, the Robots
   Exclusion Protocol <http://www.robotstxt.org/> specifies a way for
   automated processes to obtain permission to access resources;
   likewise, the Platform for Privacy Preferences
   tells user-agents how to discover privacy policy beforehand.

   While there are several ways to access per-resource metadata (e.g.,
   HTTP headers, WebDAV's PROPFIND [RFC4918]), the perceived overhead
   (either in terms of client-perceived latency and/or deployment
   difficulties) associated with them often precludes their use in these
   scenarios.

   When this happens, it is common to designate a "well-known location"
   for such data, so that it can be easily located.  However, this
   approach has the drawback of risking collisions, both with other such
   designated "well-known locations" and with pre-existing resources.

   To address this, this memo defines a path prefix in HTTP(S) URIs for
   these "well-known locations", "/.well-known/".  Future specifications
   that need to define a resource for such site-wide metadata can
   register their use to avoid collisions and minimise impingement upon
   sites' URI space.

== Changelog ==

= 0.6.2 =

* bug fix

= 0.6.0 =

* refactored the code

= 0.5.1 =

* fixed some php-warnings

= 0.5 =

* better action/filter

= 0.4 =

* some improvements for host-meta (jrd)

= 0.3 =

* adding well-known uris a bit more wordpress-like

= 0.2.1.1 =

* Ooops, copy&paste bug

= 0.2.1 =

* Forgot to flush the rewrite rules

= 0.2 =

* Better doku

= 0.1 =

* Initial release

== Installation ==

1. Upload the `well-known`-folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the *Plugins* menu in WordPress
3. ...and that's it :)

== Frequently Asked Questions ==

= How can I define a well-known uri? =


Set a callback for an URI (/.well-known/robots.txt). The action is a combination of `well_known_` and the file-name, in this case the hook must look like

`add_action('well_known_robots.txt', 'robots_txt');`


Print robots.txt:


`function robots_txt($query) {
  header('Content-Type: text/plain; charset=' . get_option('blog_charset'), true);
  echo "User-agent: *";
  echo "Allow: /";

  exit;
}`

= Is there an implementation where I can write off? =

Yes, you can find an example plugin, which defines a well-known-uri,
here: http://wordpress.org/extend/plugins/host-meta/