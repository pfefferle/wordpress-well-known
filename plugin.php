<?php
/*
Plugin Name: /.well-known/
Plugin URI: http://notizblog.org/
Description: This plugin enables "Well-Known URIs" support for WordPress (RFC 5785: http://tools.ietf.org/html/rfc5785).
Version: 0.4
Author: Matthias Pfefferle
Author URI: http://notizblog.org/
*/

//
add_action('admin_init', 'flush_rewrite_rules');
add_filter('query_vars', array('WellKnownPlugin', 'queryVars'));
add_action('parse_request', array('WellKnownPlugin', 'delegateRequest'));
add_action('generate_rewrite_rules', array('WellKnownPlugin', 'rewriteRules'));

/**
 * well-known class
 *
 * @author Matthias Pfefferle
 */
class WellKnownPlugin {
  /**
   * Add 'well-known' as a valid query variables.
   *
   * @param array $vars
   * @return array
   */
  function queryVars($vars) {
    $vars[] = 'well-known';
    return $vars;
  }

  /**
   * Add rewrite rules for .well-known.
   *
   * @param object $wp_rewrite WP_Rewrite object
   */
  function rewriteRules($wp_rewrite) {
    $wellKnownRules = array(
    	'.well-known/(.+)' => 'index.php?well-known='.$wp_rewrite->preg_index(1),
  	);

  	$wp_rewrite->rules = $wellKnownRules + $wp_rewrite->rules;
  }

  /**
   * delegates the request to the matching (registered) class
   */
  function delegateRequest() {
    global $wp;
    if( isset($wp->query_vars['well-known']) ) {
      do_action("well-known", $wp->query_vars);
      exit;
    }
  }
}