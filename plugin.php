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
class WellKnownConstants {
  public $query_var = 'well-known';

  public $option_name = 'well_known_option_name';

  public $suffix_prefix = 'suffix_';
  public $type_prefix = 'type_';
  public $contents_prefix = 'contents_';
}


class WellKnownPlugin {
  /**
   * Add 'well-known' as a valid query variables.
   *
   * @param array $vars
   * @return array
   */
  public static function query_vars($vars) {
    $vars[] = (new WellKnownConstants())->query_var;

    return $vars;
  }

  /**
   * Add rewrite rules for .well-known.
   *
   * @param WP_Rewrite $wp_rewrite
   */
  public static function rewrite_rules($wp_rewrite) {
    $c = new WellKnownConstants();
    $well_known_rules = array(
      '.well-known/(.+)' => 'index.php?' . $c->query_var . '=' . $wp_rewrite->preg_index(1),
    );

    $wp_rewrite->rules = $well_known_rules + $wp_rewrite->rules;
  }

  /**
   * delegates the request to the matching (registered) class
   *
   * @param WP $wp
   */
  public static function delegate_request($wp) {
    $c = new WellKnownConstants();

    if (array_key_exists($c->query_var, $wp->query_vars)) {
      $id = $wp->query_vars[$c->query_var];

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
  $c = new WellKnownConstants();

  $options = get_option($c->option_name);
  if (is_array($options)) {
    foreach($options as $key => $value) {
      if (strpos($key, $c->suffix_prefix) !== 0) continue;

      $offset = substr($key, strlen($c->suffix_prefix) - strlen($key));
      $suffix = $options[$c->suffix_prefix . $offset];
      if ((empty($suffix)) || (strpos($query[$c->query_var], $suffix) !== 0)) continue;

      $type = $options[$c->type_prefix . $offset];
      if (empty($type)) $type = 'text/plain; charset=' . get_option('blog_charset');
      header('Content-Type: ' . $type, TRUE);

      $contents = $options[$c->contents_prefix . $offset];
      if (is_string($contents)) echo($contents);

      exit;
    }
  }

  status_header(404);
  header('Content-Type: text/plain; charset=' . get_option('blog_charset'), TRUE);
  echo 'Not ' . (is_array($options) ? 'Found' : 'configured');

  exit;
}
add_action('well-known', 'well_known');


// (mostly) adapted from Example #2 in https://codex.wordpress.org/Creating_Options_Pages
class WellKnownSettings {
  private $options;
  private $slug = 'well-known-admin';
  private $option_group = 'well_known_option_group';

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
    $c = new WellKnownConstants();

    $this->options = get_option($c->option_name);
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
    $c = new WellKnownConstants();

    $section_prefix = 'well_known_uri';
    $suffix_title = 'Path: /.well-known/';
    $type_title = 'Content-Type:';
    $contents_title = 'URI contents:';

    register_setting($this->option_group, $c->option_name, array($this, 'sanitize_field'));

    $options = get_option($c->option_name);
    if (!is_array($options)) $j = 1;
    else {
      $newopts = array();
      for ($i = 1, $j = 1;; $i++) {
	if (!isset($options[$c->suffix_prefix . $i])) break;
	if (empty($options[$c->suffix_prefix . $i])) continue;

/* courtesy of https://stackoverflow.com/questions/619610/whats-the-most-efficient-test-of-whether-a-php-string-ends-with-another-string#2137556 */
        $reversed_needle = strrev('_' . $i);
	foreach($options as $key => $value) {
	  if (stripos(strrev($key), $reversed_needle) !== 0) continue;

	  $newopts[substr($key, 0, 1 + strlen($key) - strlen($reversed_needle)) . $j] = $value;
	}
        $j++;
      }
      update_option($c->option_name, $newopts);

      for ($j = 1;; $j++) if (!isset($newopts[$c->suffix_prefix . $j])) break;
    }

    for ($i = 1; $i <= $j; $i++) {
      add_settings_section($section_prefix . $i, 'URI #' . $i, array($this, 'print_section_info'), $this->slug);
      add_settings_field($c->suffix_prefix . $i, $suffix_title, array($this, 'field_callback'), $this->slug,
			 $section_prefix . $i, array('id' => $c->suffix_prefix . $i, 'type' => 'text'));
      add_settings_field($c->type_prefix . $i, $type_title, array($this, 'field_callback'), $this->slug,
			 $section_prefix . $i, array('id' => $c->type_prefix . $i, 'type' => 'text'));
      add_settings_field($c->contents_prefix . $i, $contents_title, array($this, 'field_callback'), $this->slug,
			 $section_prefix . $i, array('id' => $c->contents_prefix . $i, 'type' => 'textarea'));
    }
  }

  public function print_section_info() {}

  public function field_callback($params) {
    $c = new WellKnownConstants();

    $id = $params['id'];
    $type = $params['type'];
    $value = '';

    $prefix = '<input type="' . $type . '" id="' . $id . '" name="' . $c->option_name . '[' . $id . ']" ';
    if ($type === 'text') {
      $prefix .= 'size="80" value="';
      if (isset($this->options[$id])) $value = esc_attr($this->options[$id]);
      $suffix =  '" />';
    } elseif ($type === 'textarea') {
      $prefix = '<textarea id="' . $id . '" name="' . $c->option_name . '[' . $id . ']" rows="4" cols="80">';
      if (isset($this->options[$id])) $value = esc_textarea($this->options[$id]);
      $suffix = '</textarea>';
    }
    echo($prefix . $value . $suffix);
  }

  public function sanitize_field($input) {
    $c = new WellKnownConstants();

    $valid = array();

    error_log('sanitize in: ' . print_r($input, TRUE));
    for ($i = 1;; $i++) {
      if (!isset($input[$c->suffix_prefix . $i])) break;

      $valid += $this->sanitize_suffix($input, $c->suffix_prefix . $i);
      $valid += $this->sanitize_type($input, $c->type_prefix . $i);
      $valid += $this->sanitize_contents($input, $c->contents_prefix . $i);
    }
    error_log('sanitize out: ' . print_r($valid, TRUE));

    return $valid;
  }

  public function sanitize_suffix($input, $id) {
    $valid = array();

    if (!isset($input[$id])) return $valid;

    $result = trim(sanitize_text_field($input[$id]), '/');
    if (strstr($result, '/') !== FALSE) {
      add_settings_error($id, 'invalid_suffix', __('URI path must not contain "/"') . ' - ' . $result, 'error');
      return $valid;
    }

    $valid[$id] = $result;
    return $valid;
  }

// a 90% implementation of https://www.w3.org/Protocols/rfc1341/4_Content-Type.html
//   no self-respecting browser should have problems with a Content-Type header that this considers valid...
  public function sanitize_type($input, $id) {
    $valid = array();
    $validP = TRUE;

    if (empty($input[$id])) {
      $valid[$id] = '';
      return $valid;
    }

    $parts = explode(';', $input[$id]);
    list($type, $subtype) = explode('/', $parts[0]);

    $token  = '/^([0-9A-Za-z' .   "'" . preg_quote('!#$%&*+^_`{|}~-') . '])+$/';
    $word   = '/^([0-9A-Za-z' .         preg_quote('!#$%&*+^_`{|}~-') . '])+$/';
    $string = '/^"([0-9A-Za-z' .        preg_quote('!#$%&*+^_`{|}~-') . ']|(\\"))+"$/';

    $type = trim(strtolower(sanitize_text_field($type)));
    if (empty($type)) {
      add_settings_error($id, 'missing_mime_type', __('Content-Type missing type'), 'error');
      $validP = FALSE;
    }
    // skipping "media" types (audio, image, video)
    if (   (!in_array($type, array('application', 'message', 'multipart', 'text')))
        && ((strpos($type, 'x-') !== 0) || (!preg_match($token, $type)))) {
      add_settings_error($id, 'invalid_mime_type', __('Content-Type has invalid MIME type') . ' - ' . $type, 'error');
      $validP = FALSE;
    }

    $subtype = trim(sanitize_text_field($subtype));
    if (empty($subtype)) {
      add_settings_error($id, 'missing_mime_subtype', __('Content-Type missing subtype'), 'error');
      $validP = FALSE;
    }
    if (!preg_match($token, $subtype)) {
      add_settings_error($id, 'invalid_mime_subtype', __('Content-Type invalid subtype') . ' - ' . $subtype, 'error');
      $validP = FALSE;
    }

    if (!$validP) return $valid;

    $result = $type . '/' . $subtype;
    for ($i = 1; $i < count($parts); $i++) {
      list($attribute, $value) = explode('=', $parts[$i]);

      $attribute = trim(sanitize_text_field($attribute));
      if (empty($attribute)) {
	add_settings_error($id, 'missing_attribute', __('Content-Type missing attribute'), 'error');
	$validP = FALSE;
	continue;
      }
      if (!preg_match($token, $attribute)) {
	add_settings_error($id, 'invalid_mime_attribute', __('Content-Type invalid attribute') . ' - ' . $attribute, 'error');
	$validP = FALSE;
      }

      $value = trim(sanitize_text_field($value));
      if (empty($value)) {
	add_settings_error($id, 'missing_value', __('Content-Type missing value'), 'error');
	$validP = FALSE;
      }
      if (!(preg_match($word, $value) || preg_match($string, $value))) {
	add_settings_error($id, 'invalid_mime_value', __('Content-Type invalid value') . ' - ' . $value, 'error');
	$validP = FALSE;
      }

      $result .= '; ' . $attribute . '=' . $value;
    }

    if ($validP) $valid[$id] = $result;
    return $valid;
  }

  public function sanitize_contents($input, $id) {
    $valid = array();

    if (isset($input[$id])) $valid[$id] = wp_filter_post_kses($input[$id]);
    return $valid;
  }
}

if (is_admin()) new WellKnownSettings();
?>
