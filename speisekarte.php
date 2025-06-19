<?php
/**
 * Plugin Name: wp-speisekarte-stb-srv
 * Description: Zeigt eine Speisekarte als Accordion an, Kategorien und Speisen im Adminbereich verwalten, Sortierung per Drag & Drop, Bild-Upload pro Speise.
 * Version: 1.6
 * Author: stb-srv
 * Text Domain: speisekarte
 */

if (!defined('ABSPATH')) exit;

class Speisekarte_Plugin {
    public function __construct() {
        register_activation_hook(__FILE__, [$this, 'install']);
        add_action('plugins_loaded', [$this, 'maybe_upgrade']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
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
    }

    public function maybe_upgrade() {
        global $wpdb;
        $table_inh = $wpdb->prefix . 'speisekarte_inhaltsstoffe';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_inh}'") !== $table_inh) {
            $this->install();
        }
    }

    public function admin_menu() {
        add_menu_page('Speisekarte', 'Speisekarte', 'manage_options', 'speisekarte', [$this, 'admin_page'], 'dashicons-food', 26);
        add_submenu_page('speisekarte', 'Import/Export', 'Import/Export', 'manage_options', 'speisekarte-import', [$this, 'import_export_page']);
        add_submenu_page('speisekarte', 'Inhaltsstoffe', 'Inhaltsstoffe', 'manage_options', 'speisekarte-inhaltsstoffe', [$this, 'inhaltsstoffe_page']);
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
        }
    }

    public function frontend_assets() {
        wp_enqueue_style('speisekarte-frontend', plugin_dir_url(__FILE__).'assets/frontend.css');
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
        return ob_get_clean();
    }
}

new Speisekarte_Plugin();
