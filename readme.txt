=== yiid connect ===
Contributors: Matthias Pfefferle
Donate link:
Tags: OpenID, XRD, well-known, XML, Discovery
Requires at least: 2.8
Tested up to: 2.9
Stable tag: 0.1

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

= 0.1 =

* Initial release

== Installation ==

1. Upload the `well-known`-folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the *Plugins* menu in WordPress
3. ...and that's it :)

== Faq ==