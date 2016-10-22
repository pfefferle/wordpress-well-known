<?php
/*
Plugin Name: /.well-known/
Plugin URI: http://wordpress.org/extend/plugins/well-known/
Description: This plugin enables "Well-Known URIs" support for WordPress (RFC 5785: http://tools.ietf.org/html/rfc5785).
Version: 1.0.2
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

      // run the more specific hook first
      do_action("well_known_{$id}", $wp->query_vars);
      do_action("well-known", $wp->query_vars);
    }
  }
}

add_filter('query_vars', array('WellKnownPlugin', 'query_vars'));
add_action('parse_request', array('WellKnownPlugin', 'delegate_request'), 99);
add_action('generate_rewrite_rules', array('WellKnownPlugin', 'rewrite_rules'), 99);

register_activation_hook(__FILE__, 'flush_rewrite_rules');
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');

function well_known($query) {
  $options = get_option('well_known_option_name');
  if (is_array($options)) {
    well_knowing($query, $options['suffix_1'], $options['contents_1']);
    well_knowing($query, $options['suffix_2'], $options['contents_2']);
    well_knowing($query, $options['suffix_3'], $options['contents_3']);
  }

  status_header(404);
  header('Content-Type: text/plain; charset=' . get_option('blog_charset'), true);
  echo 'Not ' . (is_array($options) ? 'Found' : 'configured');

  exit;
}
add_action('well-known', 'well_known');

function well_knowing($query, $suffix, $contents) {
  if ((empty($suffix)) || (strstr($query['well-known'], $suffix) === false)) return;

  header('Content-Type: text/plain; charset=' . get_option('blog_charset'), true);
  if (is_string($contents)) echo($contents);

  exit;
}

// adapted from Example #2 in https://codex.wordpress.org/Creating_Options_Pages
class WellKnownSettings {
  private $options;
  private $slug = 'well-known-admin';
  private $option_group = 'well_known_option_group';
  private $option_name = 'well_known_option_name';
  private $suffix_id = 'suffix';
  private $contents_id = 'contents';

  public function __construct() {
    add_action('admin_menu', array($this, 'add_plugin_page'));
    add_action('admin_notices', array($this, 'admin_notices'));
    add_action('admin_init', array($this, 'page_init'));
  }

  public function add_plugin_page() {
    add_options_page('Settings Admin', 'Well-Known URIs', 'manage_options', $this->slug, array($this, 'create_admin_page'));
  }

  public function admin_notices() {
   settings_errors($this->option_group);
  }

  public function create_admin_page() {
    $this->options = get_option($this->option_name);
?>
    <div class="wrap">
      <h1>Well-Known URIs</h1>
        <form method="post" action="options.php">
<?php
    settings_fields($this->option_group);
    do_settings_sections($this->slug);
    submit_button();
?>
        </form>
    </div>
<?php
    }

  public function page_init() {
    $section_id = 'well_known_uri';
    $suffix_title = 'Path: /.well-known/';
    $contents_title = 'Textual contents:';

    register_setting($this->option_group, $this->option_name, array($this, 'sanitize_field'));

    add_settings_section($section_id . '_1', 'URI #1', array($this, 'print_section_info'), $this->slug);
    add_settings_field($this->suffix_id . '_1', $suffix_title, array($this, 'field_callback'), $this->slug,
		       $section_id . '_1', array('id' => $this->suffix_id . '_1', 'type' => 'text'));
    add_settings_field($this->contents_id . '_1', $contents_title, array($this, 'field_callback'), $this->slug,
		       $section_id . '_1', array('id' => $this->contents_id . '_1', 'type' => 'textarea'));

    add_settings_section($section_id . '_2', 'URI #2', array($this, 'print_section_info'), $this->slug);
    add_settings_field($this->suffix_id . '_2', $suffix_title, array($this, 'field_callback'), $this->slug,
		       $section_id . '_2', array('id' => $this->suffix_id . '_2', 'type' => 'text'));
    add_settings_field($this->contents_id . '_2', $contents_title, array($this, 'field_callback'), $this->slug,
		       $section_id . '_2', array('id' => $this->contents_id . '_2', 'type' => 'textarea'));

    add_settings_section($section_id . '_3', 'URI #3', array($this, 'print_section_info'), $this->slug);
    add_settings_field($this->suffix_id . '_3', $suffix_title, array($this, 'field_callback'), $this->slug,
		       $section_id . '_3', array('id' => $this->suffix_id . '_3', 'type' => 'text'));
    add_settings_field($this->contents_id . '_3', $contents_title, array($this, 'field_callback'), $this->slug,
		       $section_id . '_3', array('id' => $this->contents_id . '_3', 'type' => 'textarea'));
  }

  public function print_section_info() { }

  public function field_callback($params) {
    $id = $params['id'];
    $type = $params['type'];
    $value = '';

    $prefix = '<input type="' . $type . '" id="' . $id . '" name="' . $this->option_name . '[' . $id . ']" ';
    if ($type == 'text') {
      $prefix .= 'size="80" value="';
      if (isset($this->options[$id])) $value = esc_attr($this->options[$id]);
      $suffix =  '" />';
    } elseif ($type == 'textarea') {
      $prefix = '<textarea id="' . $id . '" name="' . $this->option_name . '[' . $id . ']" rows="4" cols="80">';
      if (isset($this->options[$id])) $value = esc_textarea($this->options[$id]);
      $suffix = '</textarea>';
    }
    echo($prefix . $value . $suffix);
  }

  public function sanitize_field($input) {
    $valid = array();

    $valid += $this->sanitize_suffix($input, $this->suffix_id . '_1');
    $valid += $this->sanitize_suffix($input, $this->suffix_id . '_2');
    $valid += $this->sanitize_suffix($input, $this->suffix_id . '_3');
    $valid += $this->sanitize_contents($input, $this->contents_id . '_1');
    $valid += $this->sanitize_contents($input, $this->contents_id . '_2');
    $valid += $this->sanitize_contents($input, $this->contents_id . '_3');

    return $valid;
  }
  public function sanitize_suffix($input, $id) {
    $valid = array();

    if (isset($input[$id])) {
      $valid[$id] = trim(sanitize_text_field($input[$id]), '/');
      if (strstr($valid[$id], '/') !== FALSE) {
	add_settings_error($id, 'invalid_suffix', __('URI path must not contain "/"'), 'error');
      }
    }
    return $valid;
  }
  public function sanitize_contents($input, $id) {
    $valid = array();

    $valid[$id] = $input[$id];
    if (isset($input[$id])) $valid[$id] = wp_filter_post_kses($input[$id]);
    return $valid;
  }
}

if (is_admin()) new WellKnownSettings();
?>
