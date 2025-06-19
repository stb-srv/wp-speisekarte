<?php
global $wpdb;
$table_kat = $wpdb->prefix . 'speisekarte_kategorien';
$table_speise = $wpdb->prefix . 'speisekarte_speisen';
$table_inh = $wpdb->prefix . 'speisekarte_inhaltsstoffe';
$inhaltsstoff_codes = [];
$rows = $wpdb->get_results("SELECT code, name FROM $table_inh ORDER BY code");
foreach ($rows as $r) {
    $inhaltsstoff_codes[$r->code] = $r->name;
}

$columns = intval(get_option('speisekarte_columns', 1));
$tile_height = intval(get_option('speisekarte_tile_height', 0));
$tile_width = intval(get_option('speisekarte_tile_width', 0));
if (isset($_POST['columns_save'])) {
    $columns = max(1, min(4, intval($_POST['columns'])));
    $tile_height = max(0, intval($_POST['tile_height']));
    $tile_width = max(0, intval($_POST['tile_width']));
    update_option('speisekarte_columns', $columns);
    update_option('speisekarte_tile_height', $tile_height);
    update_option('speisekarte_tile_width', $tile_width);
}



// Kategorie hinzufügen/bearbeiten
if (isset($_POST['kat_save'])) {
    $name = sanitize_text_field($_POST['kat_name']);
    if (isset($_POST['kat_id']) && $_POST['kat_id']) {
        $wpdb->update($table_kat, ['name' => $name], ['id' => intval($_POST['kat_id'])]);
    } else {
        $max = $wpdb->get_var("SELECT MAX(sort) FROM $table_kat") ?? 0;
        $wpdb->insert($table_kat, ['name' => $name, 'sort' => $max + 1]);
    }
}
// Kategorie löschen
if (isset($_GET['kat_del'])) {
    $wpdb->delete($table_kat, ['id' => intval($_GET['kat_del'])]);
}
// Mehrere Kategorien löschen
if (isset($_POST['bulk_del_kats']) && !empty($_POST['cat_ids']) && check_admin_referer('speisekarte_bulk_delete')) {
    foreach ((array)$_POST['cat_ids'] as $id) {
        $wpdb->delete($table_kat, ['id' => intval($id)]);
    }
}

// Speise hinzufügen/bearbeiten
if (isset($_POST['speise_save'])) {
    $inh = isset($_POST['inhaltsstoffe']) ? (array)$_POST['inhaltsstoffe'] : [];
    $inh = array_map('sanitize_text_field', $inh);
    $data = [
        'nr' => sanitize_text_field($_POST['nr']),
        'name' => sanitize_text_field($_POST['name']),
        'beschreibung' => sanitize_text_field($_POST['beschreibung']),
        'inhaltsstoffe' => implode(',', $inh),
        'preis' => floatval(str_replace(',', '.', $_POST['preis'])),
        'bild_id' => intval($_POST['bild_id']),
        'kategorie_id' => intval($_POST['kategorie_id'])
    ];
    if (isset($_POST['speise_id']) && $_POST['speise_id']) {
        $wpdb->update($table_speise, $data, ['id' => intval($_POST['speise_id'])]);
    } else {
        $max = $wpdb->get_var($wpdb->prepare("SELECT MAX(sort) FROM $table_speise WHERE kategorie_id=%d", $data['kategorie_id'])) ?? 0;
        $data['sort'] = $max + 1;
        $wpdb->insert($table_speise, $data);
    }
}
// Speise löschen
if (isset($_GET['speise_del'])) {
    $wpdb->delete($table_speise, ['id' => intval($_GET['speise_del'])]);
}
// Mehrere Speisen löschen
if (isset($_POST['bulk_del_speisen']) && !empty($_POST['speise_ids']) && check_admin_referer('speisekarte_bulk_delete')) {
    foreach ((array)$_POST['speise_ids'] as $id) {
        $wpdb->delete($table_speise, ['id' => intval($id)]);
    }
}

// Alle Kategorien laden
$kats = $wpdb->get_results("SELECT * FROM $table_kat ORDER BY sort, name");
?>
<div class="wrap">
    <h1>Speisekarte</h1>
    <form method="post" style="margin-bottom:1em;" id="columns_form">
        <label>Anzahl Spalten:
            <select name="columns">
                <?php for($i=1;$i<=4;$i++): ?>
                    <option value="<?php echo $i; ?>" <?php selected($columns, $i); ?>><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </label>
        <label style="margin-left:10px;">Kachel-Höhe (px, 0 = auto):
            <input type="number" name="tile_height" value="<?php echo esc_attr($tile_height); ?>" min="0" style="width:6em;">
        </label>
        <label style="margin-left:10px;">Kachel-Breite (px, 0 = auto):
            <input type="number" name="tile_width" value="<?php echo esc_attr($tile_width); ?>" min="0" style="width:6em;">
        </label>
        <button class="button" name="columns_save">Speichern</button>
    </form>
    <h2>Speise erstellen/bearbeiten</h2>
    <form method="post" id="speise_form">
        <input type="hidden" name="speise_id" value="">
        <select name="kategorie_id" required>
            <option value="">Kategorie wählen</option>
            <?php foreach($kats as $k): ?>
                <option value="<?php echo $k->id; ?>"><?php echo esc_html($k->name); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="nr" placeholder="Nr" style="width:5em;">
        <input type="text" name="name" placeholder="Name" required>
        <input type="text" name="beschreibung" placeholder="Beschreibung">
        <input type="text" name="preis" placeholder="Preis (€)" style="width:7em;">
        <div class="inh-select">
            <input type="text" id="inh_filter" placeholder="Inhaltsstoffe filtern">
            <select id="inh_dropdown">
                <option value="">Inhaltsstoff wählen</option>
                <?php foreach($inhaltsstoff_codes as $code => $name): ?>
                    <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($code.' - '.$name); ?></option>
                <?php endforeach; ?>
            </select>
            <div class="inh-selected"></div>
        </div>
        <input type="hidden" name="bild_id" class="bild_id">
        <button type="button" class="button bild_upload">Bild wählen</button>
        <span class="bild_preview"></span>
        <button class="button button-primary" name="speise_save">Speichern</button>
    </form>
    <h2>Kategorien</h2>
    <form method="post" style="margin-bottom:2em;" id="kat_form">
        <input type="hidden" name="kat_id" value="">
        <input type="text" name="kat_name" placeholder="Neue Kategorie" required>
        <button class="button button-primary" name="kat_save">Speichern</button>
    </form>
    <form method="post" id="kat_bulk_form">
        <?php wp_nonce_field('speisekarte_bulk_delete'); ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th style="width:30px;"><input type="checkbox" id="kat_all"></th>
                    <th>Name</th>
                    <th>Aktion</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($kats as $k): ?>
                <tr data-id="<?php echo $k->id; ?>" data-name="<?php echo esc_attr($k->name); ?>">
                    <td><input type="checkbox" class="kat_cb" name="cat_ids[]" value="<?php echo $k->id; ?>"></td>
                    <td><?php echo esc_html($k->name); ?></td>
                    <td>
                        <a href="#" class="kat_edit">Bearbeiten</a> |
                        <a href="?page=speisekarte&kat_del=<?php echo $k->id; ?>" onclick="return confirm('Wirklich löschen?')">Löschen</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p><button class="button" name="bulk_del_kats" onclick="return confirm('Ausgewählte Kategorien löschen?')">Ausgewählte löschen</button></p>
    </form>
    <form method="post" id="speisen_bulk_form">
        <?php wp_nonce_field('speisekarte_bulk_delete'); ?>
    <h3>Speisen-Liste</h3>
    <div class="speisen-filter">
        <select id="speisen_kat_filter">
            <option value="">Alle Kategorien</option>
            <?php foreach($kats as $k): ?>
                <option value="<?php echo $k->id; ?>"><?php echo esc_html($k->name); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" id="speisen_search" placeholder="Suche...">
        <label style="margin-left:10px;"><input type="checkbox" id="speisen_all"> Alle auswählen</label>
    </div>
    <?php foreach($kats as $k):
        $speisen = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_speise WHERE kategorie_id=%d ORDER BY sort, nr", $k->id
        ));
        if(!$speisen) continue; ?>
        <div class="speisen-kat-block" data-kat="<?php echo $k->id; ?>">
        <h4><?php echo esc_html($k->name); ?></h4>
        <ul class="speisen-sortable" data-kat="<?php echo $k->id; ?>">
        <?php foreach($speisen as $s): ?>
            <li class="speise-item"
                data-id="<?php echo $s->id; ?>"
                data-kategorie="<?php echo $s->kategorie_id; ?>"
                data-nr="<?php echo esc_attr($s->nr); ?>"
                data-name="<?php echo esc_attr($s->name); ?>"
                data-beschreibung="<?php echo esc_attr($s->beschreibung); ?>"
                data-inhaltsstoffe="<?php echo esc_attr($s->inhaltsstoffe); ?>"
                data-preis="<?php echo esc_attr($s->preis); ?>"
                data-bild="<?php echo esc_attr($s->bild_id); ?>">
                <input type="checkbox" class="speise_cb" name="speise_ids[]" value="<?php echo $s->id; ?>"> 
                <b><?php echo esc_html($s->nr); ?> <?php echo esc_html($s->name); ?> - <?php echo number_format($s->preis, 2, ',', '.'); ?> €</b>
                <?php if($s->bild_id) { $url = wp_get_attachment_url($s->bild_id); echo '<img src="'.esc_url($url).'" style="height:32px;vertical-align:middle;">'; } ?>
                <small><?php echo esc_html($s->beschreibung); ?></small>
                <a href="#" class="speise_edit">Bearbeiten</a> |
                <a href="?page=speisekarte&speise_del=<?php echo $s->id; ?>" onclick="return confirm('Löschen?')">Löschen</a>
            </li>
        <?php endforeach; ?>
</ul>
</div>
    <?php endforeach; ?>
    <p><button class="button" name="bulk_del_speisen" onclick="return confirm('Ausgewählte Speisen löschen?')">Ausgewählte löschen</button></p>
    </form>
</div>
