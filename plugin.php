<?php
/*
Plugin Name: /.well-known/
Plugin URI: http://notizblog.org/
Description: This plugin enables "Well-Known URIs" support for WordPress (RFC 5785: http://tools.ietf.org/html/rfc5785).
Version: 0.1
Author: Matthias Pfefferle
Author URI: http://notizblog.org/
*/

//
add_filter('query_vars', array('WellKnown', 'queryVars'));
add_action('parse_request', array('WellKnown', 'delegateRequest'));
add_action('generate_rewrite_rules', array('WellKnown', 'rewriteRules'));

/**
 * well-known class
 *
 * @author Matthias Pfefferle
 */
class WellKnown {
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
    global $wp_query, $wp;
    
    $wellKnown = array();
    $wellKnown = apply_filters('well-known', $wellKnown);
    
    $queryVars = $wp->query_vars;
    
    if( array_key_exists('well-known', $queryVars) ) {
      if (array_key_exists($queryVars['well-known'], $wellKnown)) {
        $remoteFunction = $wellKnown[$queryVars['well-known']];
        
        call_user_func($remoteFunction);
      } else {
        header("HTTP/1.1 404 Not Found");
        echo "there is no such uri: /.well-known/".$queryVars['well-known'];
      }

      exit;
    }
  }
}