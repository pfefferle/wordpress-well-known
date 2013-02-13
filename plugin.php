<?php
/*
Plugin Name: /.well-known/
Plugin URI: http://wordpress.org/extend/plugins/well-known/
Description: This plugin enables "Well-Known URIs" support for WordPress (RFC 5785: http://tools.ietf.org/html/rfc5785).
Version: 0.6.1
Author: pfefferle
Author URI: http://notizblog.org/
*/

/**
 * well-known class
 *
 * @author Matthias Pfefferle
 */
class WellKnownPlugin {
  /**
   * constructor
   */
  public function __construct() {
    add_filter('query_vars', array($this, 'query_vars'));
    add_action('parse_request', array($this, 'delegate_request'));
    add_action('generate_rewrite_rules', array($this, 'rewrite_rules'));
    add_action('admin_init', 'flush_rewrite_rules');
    
    register_activation_hook(__FILE__, 'flush_rewrite_rules');
    register_deactivation_hook(__FILE__, 'flush_rewrite_rules');
  }
  
  /**
   * Add 'well-known' as a valid query variables.
   *
   * @param array $vars
   * @return array
   */
  public function query_vars($vars) {
    $vars[] = 'well-known';

    return $vars;
  }

  /**
   * Add rewrite rules for .well-known.
   *
   * @param WP_Rewrite $wp_rewrite
   */
  public function rewrite_rules($wp_rewrite) {
    $well_known_rules = array(
      '.well-known/(.+)' => 'index.php?well-known='.$wp_rewrite->preg_index(1),
    );

    $wp_rewrite->rules = $well_known_rules + $wp_rewrite->rules;
  }

  /**
   * delegates the request to the matching (registered) class
   *
   * @param WP $wp
   */
  public function delegate_request($wp) {
    if (array_key_exists('well-known', $wp->query_vars)) {
      $id = $wp->query_vars['well-known'];
      
      do_action("well-known", $wp->query_vars);
      do_action("well_known_{$id}", $wp->query_vars);
    }
  }
}

new WellKnownPlugin;