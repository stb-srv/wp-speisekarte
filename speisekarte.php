<?php
/**
 * Plugin Name: wp-speisekarte-stb-srv
 * Description: Zeigt eine Speisekarte als Accordion an, Kategorien und Speisen im Adminbereich verwalten, Sortierung per Drag & Drop, Bild-Upload pro Speise.
 * Version: 2.2.0
 * Author: stb-srv
 * Text Domain: speisekarte
 */

if (!defined('ABSPATH')) exit;

if (!function_exists('speisekarte_get_default_kategorie_id')) {
    function speisekarte_get_default_kategorie_id() {
        global $wpdb;
        $table_kat = $wpdb->prefix . 'speisekarte_kategorien';
        $name = 'Ohne Kategorie';
        $id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_kat WHERE name=%s", $name));
        if (!$id) {
            $max = $wpdb->get_var("SELECT MAX(sort) FROM $table_kat") ?? 0;
            $wpdb->insert($table_kat, ['name' => $name, 'sort' => $max + 1]);
            $id = $wpdb->insert_id;
        }
        return intval($id);
    }
}

class Speisekarte_Plugin {
    public function __construct() {
        register_activation_hook(__FILE__, [$this, 'install']);
        add_action('plugins_loaded', [$this, 'maybe_upgrade']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('admin_init', [$this, 'register_font_settings']);
        add_action('admin_init', [$this, 'handle_export_download']);
        add_action('wp_ajax_update_speisen_order', [$this, 'update_speisen_order']);
        add_shortcode('speisekarte', [$this, 'shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_assets']);
    }

    public function install() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_kat = $wpdb->prefix . 'speisekarte_kategorien';
        $table_speise = $wpdb->prefix . 'speisekarte_speisen';
        $table_inh = $wpdb->prefix . 'speisekarte_inhaltsstoffe';

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta("
            CREATE TABLE $table_kat (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                sort int NOT NULL DEFAULT 0,
                PRIMARY KEY (id)
            ) $charset_collate;
        ");
        dbDelta("
            CREATE TABLE $table_speise (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                nr varchar(20) DEFAULT '',
                name varchar(255) NOT NULL,
                beschreibung text,
                inhaltsstoffe text,
                preis decimal(8,2) DEFAULT 0,
                bild_id bigint(20) DEFAULT NULL,
                kategorie_id mediumint(9) NOT NULL,
                sort int NOT NULL DEFAULT 0,
                PRIMARY KEY (id)
            ) $charset_collate;
        ");
        dbDelta("
            CREATE TABLE $table_inh (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                code varchar(20) NOT NULL,
                name varchar(255) NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY code (code)
            ) $charset_collate;
        ");

        $exists = $wpdb->get_var("SELECT COUNT(*) FROM $table_inh");
        if (!$exists) {
            $defaults = [
                'a' => 'Glutenhaltig',
                'b' => 'Krebstiere',
                'c' => 'Eier',
                'd' => 'Fisch',
                'e' => 'Erdn\u00fcsse',
                'f' => 'Soja',
                'g' => 'Milch',
                'h' => 'Schalenfr\u00fcchte',
                'i' => 'Sellerie',
                'j' => 'Senf',
                'k' => 'Sesam',
                'l' => 'Schwefeldioxid/Sulfite',
                'm' => 'Lupinen',
                'n' => 'Weichtiere'
            ];
            foreach ($defaults as $code => $name) {
                $wpdb->insert($table_inh, ['code' => $code, 'name' => $name]);
            }
        }

        add_option('speisekarte_columns', 1);
        add_option('speisekarte_tile_height', 0);
        add_option('speisekarte_tile_width', 0);
        add_option('speisekarte_font_family', '');
        add_option('speisekarte_font_color', '#000000');
        add_option('speisekarte_tile_font_color', '#000000');
        add_option('speisekarte_item_font_color', '#000000');
        add_option('speisekarte_background_color', '#f1f1f1');
        add_option('speisekarte_active_color', '#e1e1e1');
        add_option('speisekarte_font_color_dark', '#dddddd');
        add_option('speisekarte_tile_font_color_dark', '#ffd700');
        add_option('speisekarte_item_font_color_dark', '#ffffff');
        add_option('speisekarte_background_color_dark', '#1b3b6f');
        add_option('speisekarte_active_color_dark', '#1e447c');
        add_option('speisekarte_item_font_family', '');
        add_option('speisekarte_item_font_size', '');
        add_option('speisekarte_item_font_weight', '');
        add_option('speisekarte_item_font_style', '');
        add_option('zusatz_farbe', '#992766');
        add_option('kategorie_farbe', '#000000');
        add_option('speisen_farbe', '#000000');
        add_option('preis_farbe', '#000000');
        add_option('zusatz_farbe_dark', '#DD9933');
        add_option('kategorie_farbe_dark', '#DD9933');
        add_option('speisen_farbe_dark', '#FFFFFF');
        add_option('preis_farbe_dark', '#FFFFFF');

        // ensure default category exists on installation
        speisekarte_get_default_kategorie_id();
    }

    public function maybe_upgrade() {
        global $wpdb;
        $table_inh = $wpdb->prefix . 'speisekarte_inhaltsstoffe';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_inh}'") !== $table_inh) {
            $this->install();
        }
        if (get_option('speisekarte_tile_width', null) === null) {
            add_option('speisekarte_tile_width', 0);
        }
        if (get_option('speisekarte_font_family', null) === null) {
            add_option('speisekarte_font_family', '');
        }
        if (get_option('speisekarte_font_color', null) === null) {
            add_option('speisekarte_font_color', '#000000');
        }
        if (get_option('speisekarte_tile_font_color', null) === null) {
            add_option('speisekarte_tile_font_color', '#000000');
        }
        if (get_option('speisekarte_item_font_color', null) === null) {
            add_option('speisekarte_item_font_color', '#000000');
        }
        if (get_option('speisekarte_background_color', null) === null) {
            add_option('speisekarte_background_color', '#f1f1f1');
        }
        if (get_option('speisekarte_active_color', null) === null) {
            add_option('speisekarte_active_color', '#e1e1e1');
        }
        if (get_option('speisekarte_font_color_dark', null) === null) {
            add_option('speisekarte_font_color_dark', '#dddddd');
        }
        if (get_option('speisekarte_tile_font_color_dark', null) === null) {
            add_option('speisekarte_tile_font_color_dark', '#ffd700');
        }
        if (get_option('speisekarte_item_font_color_dark', null) === null) {
            add_option('speisekarte_item_font_color_dark', '#ffffff');
        }
        if (get_option('speisekarte_background_color_dark', null) === null) {
            add_option('speisekarte_background_color_dark', '#1b3b6f');
        }
        if (get_option('speisekarte_active_color_dark', null) === null) {
            add_option('speisekarte_active_color_dark', '#1e447c');
        }
        if (get_option('speisekarte_item_font_family', null) === null) {
            add_option('speisekarte_item_font_family', '');
        }
        if (get_option('speisekarte_item_font_size', null) === null) {
            add_option('speisekarte_item_font_size', '');
        }
        if (get_option('speisekarte_item_font_weight', null) === null) {
            add_option('speisekarte_item_font_weight', '');
        }
        if (get_option('speisekarte_item_font_style', null) === null) {
            add_option('speisekarte_item_font_style', '');
        }
        if (get_option('zusatz_farbe', null) === null) {
            add_option('zusatz_farbe', '#992766');
        }
        if (get_option('kategorie_farbe', null) === null) {
            add_option('kategorie_farbe', '#000000');
        }
        if (get_option('speisen_farbe', null) === null) {
            add_option('speisen_farbe', '#000000');
        }
        if (get_option('preis_farbe', null) === null) {
            add_option('preis_farbe', '#000000');
        }
        if (get_option('zusatz_farbe_dark', null) === null) {
            add_option('zusatz_farbe_dark', '#DD9933');
        }
        if (get_option('kategorie_farbe_dark', null) === null) {
            add_option('kategorie_farbe_dark', '#DD9933');
        }
        if (get_option('speisen_farbe_dark', null) === null) {
            add_option('speisen_farbe_dark', '#FFFFFF');
        }
        if (get_option('preis_farbe_dark', null) === null) {
            add_option('preis_farbe_dark', '#FFFFFF');
        }

        $table_speise = $wpdb->prefix . 'speisekarte_speisen';
        $col = $wpdb->get_var("SHOW COLUMNS FROM $table_speise LIKE 'preis'");
        if (!$col) {
            $wpdb->query("ALTER TABLE $table_speise ADD preis decimal(8,2) DEFAULT 0 AFTER inhaltsstoffe");
        }
    }

    public function admin_menu() {
        add_menu_page('Speisekarte', 'Speisekarte', 'manage_options', 'speisekarte', [$this, 'admin_page'], 'dashicons-food', 26);
        add_submenu_page('speisekarte', 'Import/Export', 'Import/Export', 'manage_options', 'speisekarte-import', [$this, 'import_export_page']);
        add_submenu_page('speisekarte', 'Inhaltsstoffe', 'Inhaltsstoffe', 'manage_options', 'speisekarte-inhaltsstoffe', [$this, 'inhaltsstoffe_page']);
        add_submenu_page('speisekarte', 'Design', 'Design', 'manage_options', 'speisekarte-design', [$this, 'design_page']);
        add_submenu_page('speisekarte', 'Schrift', 'Schrift', 'manage_options', 'speisekarte-fonts', [$this, 'fonts_page']);
    }

    public function admin_assets($hook) {
        if (strpos($hook, 'speisekarte') !== false) {
            wp_enqueue_style('speisekarte-admin', plugin_dir_url(__FILE__).'assets/admin.css');
            wp_enqueue_script('speisekarte-admin', plugin_dir_url(__FILE__).'assets/admin.js', ['jquery', 'jquery-ui-sortable'], '1.0', true);
            wp_localize_script('speisekarte-admin', 'speisekarteAjax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('speisekarte_nonce')
            ]);
            wp_enqueue_media();
            if (strpos($hook, 'speisekarte-design') !== false || strpos($hook, 'speisekarte-fonts') !== false) {
                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script('wp-color-picker');
            }
        }
    }

    public function frontend_assets() {
        wp_enqueue_style('speisekarte-frontend', plugin_dir_url(__FILE__).'assets/frontend.css');
        $vars = '';
        $font = trim(get_option('speisekarte_font_family', ''));
        if ($font) $vars .= '--font-family:' . esc_attr($font) . ';';
        $zusatz = get_option('zusatz_farbe', '#992766');
        if ($zusatz) $vars .= '--zusatz-weiss:' . esc_attr($zusatz) . ';';
        $kategorie = get_option('kategorie_farbe', '#000000');
        if ($kategorie) $vars .= '--kategorie-weiss:' . esc_attr($kategorie) . ';';
        $speisen = get_option('speisen_farbe', '#000000');
        if ($speisen) $vars .= '--speisen-weiss:' . esc_attr($speisen) . ';';
        $preis = get_option('preis_farbe', '#000000');
        if ($preis) $vars .= '--preis-weiss:' . esc_attr($preis) . ';';
        $item_font_family = trim(get_option('speisekarte_item_font_family', ''));
        if ($item_font_family) $vars .= '--item-font-family:' . esc_attr($item_font_family) . ';';
        $item_font_size = trim(get_option('speisekarte_item_font_size', ''));
        if ($item_font_size) $vars .= '--item-font-size:' . esc_attr($item_font_size) . ';';
        $item_font_weight = trim(get_option('speisekarte_item_font_weight', ''));
        if ($item_font_weight) $vars .= '--item-font-weight:' . esc_attr($item_font_weight) . ';';
        $item_font_style = trim(get_option('speisekarte_item_font_style', ''));
        if ($item_font_style) $vars .= '--item-font-style:' . esc_attr($item_font_style) . ';';
        $bg = get_option('speisekarte_background_color', '#f1f1f1');
        if ($bg) $vars .= '--toggle-bg:' . esc_attr($bg) . ';';
        $active = get_option('speisekarte_active_color', '#e1e1e1');
        if ($active) $vars .= '--toggle-active-bg:' . esc_attr($active) . ';';
        $dark_vars = '';
        $zusatz_d = get_option('zusatz_farbe_dark', '#DD9933');
        if ($zusatz_d) $dark_vars .= '--zusatz-dunkel:' . esc_attr($zusatz_d) . ';';
        $kategorie_d = get_option('kategorie_farbe_dark', '#DD9933');
        if ($kategorie_d) $dark_vars .= '--kategorie-dunkel:' . esc_attr($kategorie_d) . ';';
        $speisen_d = get_option('speisen_farbe_dark', '#FFFFFF');
        if ($speisen_d) $dark_vars .= '--speisen-dunkel:' . esc_attr($speisen_d) . ';';
        $preis_d = get_option('preis_farbe_dark', '#FFFFFF');
        if ($preis_d) $dark_vars .= '--preis-dunkel:' . esc_attr($preis_d) . ';';
        $bg_d = get_option('speisekarte_background_color_dark', '#1b3b6f');
        if ($bg_d) $dark_vars .= '--toggle-bg:' . esc_attr($bg_d) . ';';
        $active_d = get_option('speisekarte_active_color_dark', '#1e447c');
        if ($active_d) $dark_vars .= '--toggle-active-bg:' . esc_attr($active_d) . ';';
        $inline = '';
        if ($vars) {
            $inline .= ':root{' . $vars . '}';
        }
        if ($dark_vars) {
            $inline .= '@media (prefers-color-scheme: dark){:root{' . $dark_vars . '}}';
            $inline .= 'body.is-dark-theme,';
            $inline .= 'body.dark-mode{'. $dark_vars . '}';
        }
        if ($inline) {
            wp_add_inline_style('speisekarte-frontend', $inline);
        }
        wp_enqueue_script('speisekarte-frontend', plugin_dir_url(__FILE__).'assets/frontend.js', ['jquery'], '1.0', true);
    }

    public function admin_page() {
        include(plugin_dir_path(__FILE__).'admin/admin.php');
    }

    public function import_export_page() {
        include(plugin_dir_path(__FILE__).'admin/import_export.php');
    }

    public function inhaltsstoffe_page() {
        include(plugin_dir_path(__FILE__).'admin/inhaltsstoffe.php');
    }

    public function design_page() {
        include(plugin_dir_path(__FILE__).'admin/design.php');
    }

    public function fonts_page() {
        include(plugin_dir_path(__FILE__).'admin/fonts.php');
    }

    public function register_font_settings() {
        $opts = [
            'zusatz_farbe', 'kategorie_farbe', 'speisen_farbe', 'preis_farbe',
            'zusatz_farbe_dark', 'kategorie_farbe_dark', 'speisen_farbe_dark', 'preis_farbe_dark',
            'speisekarte_font_family', 'speisekarte_item_font_family', 'speisekarte_item_font_size',
            'speisekarte_item_font_weight', 'speisekarte_item_font_style'
        ];
        foreach ($opts as $opt) {
            register_setting('speisekarte_fonts', $opt);
        }
    }

    public function handle_export_download() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $page = $_GET['page'] ?? '';
        if ($page !== 'speisekarte-import') {
            return;
        }

        global $wpdb;
        $table_kat    = $wpdb->prefix . 'speisekarte_kategorien';
        $table_speise = $wpdb->prefix . 'speisekarte_speisen';
        $table_inh    = $wpdb->prefix . 'speisekarte_inhaltsstoffe';
        $upload       = wp_upload_dir();
        $export_dir   = trailingslashit($upload['basedir']) . 'speisekarte_exports';
        wp_mkdir_p($export_dir);

        if (isset($_GET['export']) && check_admin_referer('speisekarte_export')) {
            $filename = 'speisekarte_export_' . date('Ymd_His') . '.json';
            $filepath = trailingslashit($export_dir) . $filename;

            $data = [
                'kategorien'     => [],
                'inhaltsstoffe' => [],
                'speisen'        => [],
            ];

            $kats = $wpdb->get_results("SELECT * FROM $table_kat ORDER BY sort, name", ARRAY_A);
            foreach ($kats as $kat) {
                $data['kategorien'][] = [
                    'name' => $kat['name'],
                    'sort' => intval($kat['sort']),
                ];
                $speisen = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_speise WHERE kategorie_id=%d ORDER BY sort, nr", $kat['id']), ARRAY_A);
                foreach ($speisen as $sp) {
                    $data['speisen'][] = [
                        'kategorie'      => $kat['name'],
                        'nr'            => $sp['nr'],
                        'name'          => $sp['name'],
                        'beschreibung'  => $sp['beschreibung'],
                        'inhaltsstoffe' => $sp['inhaltsstoffe'],
                        'preis'         => $sp['preis'],
                        'bild_id'       => $sp['bild_id'],
                        'sort'          => intval($sp['sort']),
                    ];
                }
            }

            $inhalte = $wpdb->get_results("SELECT code, name FROM $table_inh ORDER BY code", ARRAY_A);
            foreach ($inhalte as $i) {
                $data['inhaltsstoffe'][] = [
                    'code' => $i['code'],
                    'name' => $i['name'],
                ];
            }

            file_put_contents($filepath, json_encode($data));
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            readfile($filepath);
            exit;
        }

        if (isset($_GET['download'])) {
            $file = basename($_GET['download']);
            $filepath = trailingslashit($export_dir) . $file;
            if (file_exists($filepath)) {
                $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
                if ($ext === 'json') {
                    header('Content-Type: application/json; charset=utf-8');
                } else {
                    header('Content-Type: text/csv; charset=utf-8');
                }
                header('Content-Disposition: attachment; filename="' . $file . '"');
                readfile($filepath);
            }
            exit;
        }
    }

    public function update_speisen_order() {
        check_ajax_referer('speisekarte_nonce', 'nonce');
        global $wpdb;
        $ids = $_POST['ids'] ?? [];
        $kat_id = intval($_POST['kat_id'] ?? 0);

        foreach ($ids as $pos => $id) {
            $wpdb->update($wpdb->prefix . 'speisekarte_speisen', ['sort' => $pos], ['id' => intval($id), 'kategorie_id' => $kat_id]);
        }
        wp_send_json_success();
    }

    public function shortcode() {
        ob_start();
        include(plugin_dir_path(__FILE__).'templates/speisekarte-frontend.php');
        $output = ob_get_clean();
        return $output;
    }
}

new Speisekarte_Plugin();
