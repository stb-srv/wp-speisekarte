<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_kat    = $wpdb->prefix . 'speisekarte_kategorien';
$table_speise = $wpdb->prefix . 'speisekarte_speisen';
$upload       = wp_upload_dir();
$export_dir   = trailingslashit($upload['basedir']) . 'speisekarte_exports';
wp_mkdir_p($export_dir);

$import_message = '';
if (isset($_POST['speisekarte_import']) && check_admin_referer('speisekarte_import', 'speisekarte_import_nonce')) {
    if (!empty($_FILES['import_file']['tmp_name'])) {
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
                    $wpdb->insert($table_kat, ['name' => $kat_name, 'sort' => $cat_sort++]);
                    $cat_map[$kat_name] = $wpdb->insert_id;
                    $sort_map[$kat_name] = 0;
                }
                $sort = $sort_map[$kat_name]++;
                $wpdb->insert($table_speise, [
                    'nr'           => sanitize_text_field($nr),
                    'name'         => sanitize_text_field($name),
                    'beschreibung' => sanitize_text_field($beschr),
                    'inhaltsstoffe'=> sanitize_text_field($inh),
                    'preis'        => floatval(str_replace(',', '.', $preis)),
                    'bild_id'      => intval($bild),
                    'kategorie_id' => $cat_map[$kat_name],
                    'sort'         => $sort,
                ]);
            }
            fclose($handle);
            $import_message = '<div class="updated notice"><p>Import erfolgreich.</p></div>';
        } else {
            $import_message = '<div class="error notice"><p>Ungültige Datei.</p></div>';
        }
    }
}
?>
<div class="wrap">
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
    $export_files = glob(trailingslashit($export_dir) . '*.csv');
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
        <input type="file" name="import_file" accept="text/csv" required>
        <button class="button button-primary" name="speisekarte_import">Importieren</button>
    </form>
</div>
