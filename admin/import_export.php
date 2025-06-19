<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_kat    = $wpdb->prefix . 'speisekarte_kategorien';
$table_speise = $wpdb->prefix . 'speisekarte_speisen';

// Export CSV
if (isset($_GET['export']) && check_admin_referer('speisekarte_export')) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="speisekarte_export.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Kategorie', 'Nr', 'Name', 'Beschreibung', 'Inhaltsstoffe', 'Preis', 'BildID'], ';');

    $kats = $wpdb->get_results("SELECT * FROM $table_kat ORDER BY sort, name", ARRAY_A);
    foreach ($kats as $kat) {
        $speisen = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_speise WHERE kategorie_id=%d ORDER BY sort, nr", $kat['id']), ARRAY_A);
        foreach ($speisen as $sp) {
            fputcsv($output, [
                $kat['name'],
                $sp['nr'],
                $sp['name'],
                $sp['beschreibung'],
                $sp['inhaltsstoffe'],
                $sp['preis'],
                $sp['bild_id'],
            ], ';');
        }
    }
    fclose($output);
    exit;
}

$import_message = '';
if (isset($_POST['speisekarte_import']) && check_admin_referer('speisekarte_import', 'speisekarte_import_nonce')) {
    if (!empty($_FILES['import_file']['tmp_name'])) {
        $data = json_decode(file_get_contents($_FILES['import_file']['tmp_name']), true);
        if (is_array($data)) {
            foreach ($data as $k_index => $kat) {
                $wpdb->insert($table_kat, ['name' => $kat['name'], 'sort' => $k_index]);
                $kat_id = $wpdb->insert_id;
                if (!empty($kat['speisen']) && is_array($kat['speisen'])) {
                    foreach ($kat['speisen'] as $s_index => $sp) {
                        $wpdb->insert($table_speise, [
                            'nr'           => $sp['nr'],
                            'name'         => $sp['name'],
                            'beschreibung' => $sp['beschreibung'],
                            'inhaltsstoffe'=> $sp['inhaltsstoffe'],
                            'preis'        => $sp['preis'],
                            'bild_id'      => $sp['bild_id'],
                            'kategorie_id' => $kat_id,
                            'sort'         => $s_index,
                        ]);
                    }
                }
            }
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
    <h2>Import</h2>
    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('speisekarte_import', 'speisekarte_import_nonce'); ?>
        <input type="file" name="import_file" accept="application/json" required>
        <button class="button button-primary" name="speisekarte_import">Importieren</button>
    </form>
</div>
