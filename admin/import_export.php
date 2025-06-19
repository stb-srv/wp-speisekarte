<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_kat    = $wpdb->prefix . 'speisekarte_kategorien';
$table_speise = $wpdb->prefix . 'speisekarte_speisen';
$table_inh    = $wpdb->prefix . 'speisekarte_inhaltsstoffe';
$upload       = wp_upload_dir();
$export_dir   = trailingslashit($upload['basedir']) . 'speisekarte_exports';
wp_mkdir_p($export_dir);

$import_message = '';
if (isset($_POST['speisekarte_import']) && check_admin_referer('speisekarte_import', 'speisekarte_import_nonce')) {
    if (!empty($_FILES['import_file']['tmp_name'])) {
        $content = file_get_contents($_FILES['import_file']['tmp_name']);
        $data = json_decode($content, true);
        if ($data && isset($data['speisen'])) {
            $cat_map = [];
            $cat_sort = 0;
            if (!empty($data['kategorien'])) {
                foreach ($data['kategorien'] as $kat) {
                    $name = sanitize_text_field($kat['name'] ?? '');
                    $sort = intval($kat['sort'] ?? $cat_sort++);
                    $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_kat WHERE name=%s", $name));
                    if ($existing) {
                        $cat_map[$name] = intval($existing->id);
                        $wpdb->update($table_kat, ['sort' => $sort], ['id' => $cat_map[$name]]);
                    } else {
                        $wpdb->insert($table_kat, ['name' => $name, 'sort' => $sort]);
                        $cat_map[$name] = $wpdb->insert_id;
                    }
                    if ($sort >= $cat_sort) $cat_sort = $sort + 1;
                }
            }
            if (!empty($data['inhaltsstoffe'])) {
                foreach ($data['inhaltsstoffe'] as $inh) {
                    $code = sanitize_text_field($inh['code'] ?? '');
                    $name = sanitize_text_field($inh['name'] ?? '');
                    if (!$code) continue;
                    $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_inh WHERE code=%s", $code));
                    if ($existing) {
                        $wpdb->update($table_inh, ['name' => $name], ['id' => $existing]);
                    } else {
                        $wpdb->insert($table_inh, ['code' => $code, 'name' => $name]);
                    }
                }
            }
            $sort_map = [];
            foreach ($data['speisen'] as $sp) {
                $kat_name = sanitize_text_field($sp['kategorie'] ?? '');
                if (!isset($cat_map[$kat_name])) {
                    $existing_kat = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_kat WHERE name=%s", $kat_name));
                    if ($existing_kat) {
                        $cat_map[$kat_name] = intval($existing_kat->id);
                        $wpdb->update($table_kat, ['sort' => $cat_sort], ['id' => $cat_map[$kat_name]]);
                    } else {
                        $wpdb->insert($table_kat, ['name' => $kat_name, 'sort' => $cat_sort]);
                        $cat_map[$kat_name] = $wpdb->insert_id;
                    }
                    $sort_map[$kat_name] = 0;
                    $cat_sort++;
                }
                if (!isset($sort_map[$kat_name])) $sort_map[$kat_name] = 0;
                $sort = isset($sp['sort']) ? intval($sp['sort']) : $sort_map[$kat_name];
                $sort_map[$kat_name] = $sort + 1;
                $nr   = sanitize_text_field($sp['nr'] ?? '');
                $name = sanitize_text_field($sp['name'] ?? '');
                $data = [
                    'nr'           => $nr,
                    'name'         => $name,
                    'beschreibung' => sanitize_text_field($sp['beschreibung'] ?? ''),
                    'inhaltsstoffe'=> sanitize_text_field($sp['inhaltsstoffe'] ?? ''),
                    'preis'        => floatval(str_replace(',', '.', $sp['preis'] ?? 0)),
                    'bild_id'      => intval($sp['bild_id'] ?? 0),
                    'kategorie_id' => $cat_map[$kat_name],
                    'sort'         => $sort,
                ];
                $existing = null;
                if ($nr !== '') {
                    $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_speise WHERE kategorie_id=%d AND nr=%s", $cat_map[$kat_name], $nr));
                }
                if (!$existing) {
                    $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_speise WHERE kategorie_id=%d AND name=%s", $cat_map[$kat_name], $name));
                }
                if ($existing) {
                    $wpdb->update($table_speise, $data, ['id' => intval($existing->id)]);
                } else {
                    $wpdb->insert($table_speise, $data);
                }
            }
            $import_message = '<div class="updated notice"><p>Import erfolgreich.</p></div>';
        } else {
            $handle = fopen($_FILES['import_file']['tmp_name'], 'r');
            if ($handle) {
                fgetcsv($handle, 0, ';'); // header
                $cat_map = [];
                $cat_sort = 0;
                $sort_map = [];
                while (($row = fgetcsv($handle, 0, ';')) !== false) {
                    if (count($row) < 7) {
                        continue;
                    }
                    list($kat_name, $nr, $name, $beschr, $inh, $preis, $bild) = $row;
                    $kat_name = sanitize_text_field($kat_name);
                    if (!isset($cat_map[$kat_name])) {
                        $existing_kat = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_kat WHERE name=%s", $kat_name));
                        if ($existing_kat) {
                            $cat_map[$kat_name] = intval($existing_kat->id);
                            $wpdb->update($table_kat, ['sort' => $cat_sort], ['id' => $cat_map[$kat_name]]);
                        } else {
                            $wpdb->insert($table_kat, ['name' => $kat_name, 'sort' => $cat_sort]);
                            $cat_map[$kat_name] = $wpdb->insert_id;
                        }
                        $sort_map[$kat_name] = 0;
                        $cat_sort++;
                    }
                    $sort = $sort_map[$kat_name]++;
                    $nr   = sanitize_text_field($nr);
                    $name = sanitize_text_field($name);
                    $data = [
                        'nr'           => $nr,
                        'name'         => $name,
                        'beschreibung' => sanitize_text_field($beschr),
                        'inhaltsstoffe'=> sanitize_text_field($inh),
                        'preis'        => floatval(str_replace(',', '.', $preis)),
                        'bild_id'      => intval($bild),
                        'kategorie_id' => $cat_map[$kat_name],
                        'sort'         => $sort,
                    ];
                    $existing = null;
                    if ($nr !== '') {
                        $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_speise WHERE kategorie_id=%d AND nr=%s", $cat_map[$kat_name], $nr));
                    }
                    if (!$existing) {
                        $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_speise WHERE kategorie_id=%d AND name=%s", $cat_map[$kat_name], $name));
                    }
                    if ($existing) {
                        $wpdb->update($table_speise, $data, ['id' => intval($existing->id)]);
                    } else {
                        $wpdb->insert($table_speise, $data);
                    }
                }
                fclose($handle);
                $import_message = '<div class="updated notice"><p>Import erfolgreich.</p></div>';
            } else {
                $import_message = '<div class="error notice"><p>Ungültige Datei.</p></div>';
            }
        }
    }
}
?>
<div class="wrap speisekarte-admin">
    <h1>Import/Export</h1>
    <?php echo $import_message; ?>
    <h2>Export</h2>
    <form method="get" action="">
        <input type="hidden" name="page" value="speisekarte-import">
        <?php wp_nonce_field('speisekarte_export'); ?>
        <input type="hidden" name="export" value="1">
        <button class="button">Daten exportieren</button>
    </form>
    <?php
    $export_files = glob(trailingslashit($export_dir) . '*.json');
    if ($export_files):
        usort($export_files, function($a, $b){ return filemtime($b) - filemtime($a); });
    ?>
    <h3>Export-Historie</h3>
    <ul>
        <?php foreach ($export_files as $file): $base = basename($file); ?>
            <li>
                <?php echo esc_html($base); ?> - <?php echo date('Y-m-d H:i', filemtime($file)); ?>
                (<a href="?page=speisekarte-import&amp;download=<?php echo urlencode($base); ?>">Download</a>)
            </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    <h2>Import</h2>
    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('speisekarte_import', 'speisekarte_import_nonce'); ?>
        <input type="file" name="import_file" accept=".json,text/csv" required>
        <button class="button button-primary" name="speisekarte_import">Importieren</button>
    </form>
</div>
