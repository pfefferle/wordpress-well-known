<?php
/*
Plugin Name: /.well-known/
Plugin URI: http://wordpress.org/extend/plugins/well-known/
Description: This plugin enables "Well-Known URIs" support for WordPress (RFC 5785: http://tools.ietf.org/html/rfc5785).
Version: 1.0.1
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
   * Add 'well-known' as a valid query variables.
   *
   * @param array $vars
   * @return array
   */
  public static function query_vars($vars) {
    $vars[] = 'well-known';

    return $vars;
  }

  /**
   * Add rewrite rules for .well-known.
   *
   * @param WP_Rewrite $wp_rewrite
   */
  public static function rewrite_rules($wp_rewrite) {
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
  public static function delegate_request($wp) {
    if (array_key_exists('well-known', $wp->query_vars)) {
      $id = $wp->query_vars['well-known'];

      do_action("well-known", $wp->query_vars);
      do_action("well_known_{$id}", $wp->query_vars);
    }
  }
}

add_filter('query_vars', array('WellKnownPlugin', 'query_vars'));
add_action('parse_request', array('WellKnownPlugin', 'delegate_request'), 99);
add_action('generate_rewrite_rules', array('WellKnownPlugin', 'rewrite_rules'), 99);

register_activation_hook(__FILE__, 'flush_rewrite_rules');
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');