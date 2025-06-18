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

// Speise hinzufügen/bearbeiten
if (isset($_POST['speise_save'])) {
    $inh = isset($_POST['inhaltsstoffe']) ? (array)$_POST['inhaltsstoffe'] : [];
    $inh = array_map('sanitize_text_field', $inh);
    $data = [
        'nr' => sanitize_text_field($_POST['nr']),
        'name' => sanitize_text_field($_POST['name']),
        'beschreibung' => sanitize_text_field($_POST['beschreibung']),
        'inhaltsstoffe' => implode(',', $inh),
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

// Alle Kategorien laden
$kats = $wpdb->get_results("SELECT * FROM $table_kat ORDER BY sort, name");
?>
<div class="wrap">
    <h1>Speisekarte</h1>
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
        <div class="inh-select">
            <input type="text" id="inh_filter" placeholder="Inhaltsstoffe filtern">
            <div class="inh-checkbox-list">
            <?php foreach($inhaltsstoff_codes as $code => $name): ?>
                <label><input type="checkbox" name="inhaltsstoffe[]" value="<?php echo esc_attr($code); ?>"> <?php echo esc_html($code.' - '.$name); ?></label><br>
            <?php endforeach; ?>
            </div>
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
    <table class="widefat">
        <thead><tr><th>Name</th><th>Aktion</th></tr></thead>
        <tbody>
        <?php foreach($kats as $k): ?>
            <tr data-id="<?php echo $k->id; ?>" data-name="<?php echo esc_attr($k->name); ?>">
                <td><?php echo esc_html($k->name); ?></td>
                <td>
                    <a href="#" class="kat_edit">Bearbeiten</a> |
                    <a href="?page=speisekarte&kat_del=<?php echo $k->id; ?>" onclick="return confirm('Wirklich löschen?')">Löschen</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <h3>Speisen-Liste</h3>
    <div class="speisen-filter">
        <select id="speisen_kat_filter">
            <option value="">Alle Kategorien</option>
            <?php foreach($kats as $k): ?>
                <option value="<?php echo $k->id; ?>"><?php echo esc_html($k->name); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" id="speisen_search" placeholder="Suche...">
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
                data-bild="<?php echo esc_attr($s->bild_id); ?>">
                <b><?php echo esc_html($s->nr); ?> <?php echo esc_html($s->name); ?></b>
                <?php if($s->bild_id) { $url = wp_get_attachment_url($s->bild_id); echo '<img src="'.esc_url($url).'" style="height:32px;vertical-align:middle;">'; } ?>
                <small><?php echo esc_html($s->beschreibung); ?></small>
                <a href="#" class="speise_edit">Bearbeiten</a> |
                <a href="?page=speisekarte&speise_del=<?php echo $s->id; ?>" onclick="return confirm('Löschen?')">Löschen</a>
            </li>
        <?php endforeach; ?>
        </ul>
        </div>
    <?php endforeach; ?>
</div>
