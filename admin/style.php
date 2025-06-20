<?php
if (!defined('ABSPATH')) exit;

// --- Design Settings ---
$options = get_option('wp_speisekarte_settings', []);
$bg_color = $options['bg_color'] ?? '#f1f1f1';
$active_color = $options['active_color'] ?? '#e1e1e1';
$font_color = $options['font_color'] ?? '#000000';
$bg_color_dark = $options['bg_color_dark'] ?? '#1b3b6f';
$active_color_dark = $options['active_color_dark'] ?? '#1e447c';
$font_color_dark = $options['font_color_dark'] ?? '#dddddd'; // ensure fallback

// --- Font Settings ---
$font_family       = get_option('speisekarte_font_family', '');
$item_font_family  = get_option('speisekarte_item_font_family', '');
$item_font_size    = get_option('speisekarte_item_font_size', '');
$item_font_weight  = get_option('speisekarte_item_font_weight', '');
$item_font_style   = get_option('speisekarte_item_font_style', '');
$font_kategorie    = get_option('speisekarte_font_kategorie', '');
$font_speise       = get_option('speisekarte_font_speise', '');
$font_preis        = get_option('speisekarte_font_preis', '');
$font_beschreibung = get_option('speisekarte_font_beschreibung', '');
$font_inhalt_label = get_option('speisekarte_font_inhalt_label', '');
$font_inhalt       = get_option('speisekarte_font_inhalt', '');

$zusatz_farbe         = $options['zusatz_farbe'] ?? '#992766';
$kategorie_farbe      = $options['kategorie_farbe'] ?? '#000000';
$speisen_farbe        = $options['speisen_farbe'] ?? '#000000';
$preis_farbe          = $options['preis_farbe'] ?? '#000000';
$zusatz_farbe_dark    = $options['zusatz_farbe_dark'] ?? '#DD9933';
$kategorie_farbe_dark = $options['kategorie_farbe_dark'] ?? '#DD9933';
$speisen_farbe_dark   = $options['speisen_farbe_dark'] ?? '#FFFFFF';
$preis_farbe_dark     = $options['preis_farbe_dark'] ?? '#FFFFFF';

if (isset($_POST['style_save'])) {
    check_admin_referer('speisekarte_style_save');
    $input = $_POST['wp_speisekarte_settings'] ?? [];
    $bg_color = sanitize_hex_color($input['bg_color'] ?? '') ?: '#f1f1f1';
    $active_color = sanitize_hex_color($input['active_color'] ?? '') ?: '#e1e1e1';
    $font_color = sanitize_hex_color($input['font_color'] ?? '') ?: '#000000';
    $bg_color_dark = sanitize_hex_color($input['bg_color_dark'] ?? '') ?: '#1b3b6f';
    $active_color_dark = sanitize_hex_color($input['active_color_dark'] ?? '') ?: '#1e447c';
    $font_color_dark = sanitize_hex_color($input['font_color_dark'] ?? '') ?: '#dddddd'; // keep default

    $font_family = sanitize_text_field($_POST['font_family']);
    $item_font_family = sanitize_text_field($_POST['item_font_family']);
    $item_font_size = sanitize_text_field($_POST['item_font_size']);
    $item_font_weight = isset($_POST['item_font_weight']) ? 'bold' : '';
    $item_font_style = isset($_POST['item_font_style']) ? 'italic' : '';
    $font_kategorie = sanitize_text_field($_POST['font_kategorie']);
    $font_speise = sanitize_text_field($_POST['font_speise']);
    $font_preis = sanitize_text_field($_POST['font_preis']);
    $font_beschreibung = sanitize_text_field($_POST['font_beschreibung']);
    $font_inhalt_label = sanitize_text_field($_POST['font_inhalt_label']);
    $font_inhalt = sanitize_text_field($_POST['font_inhalt']);

    $zusatz_farbe = sanitize_hex_color($input['zusatz_farbe'] ?? '') ?: '#992766';
    $kategorie_farbe = sanitize_hex_color($input['kategorie_farbe'] ?? '') ?: '#000000';
    $speisen_farbe = sanitize_hex_color($input['speisen_farbe'] ?? '') ?: '#000000';
    $preis_farbe = sanitize_hex_color($input['preis_farbe'] ?? '') ?: '#000000';
    $zusatz_farbe_dark = sanitize_hex_color($input['zusatz_farbe_dark'] ?? '') ?: '#DD9933';
    $kategorie_farbe_dark = sanitize_hex_color($input['kategorie_farbe_dark'] ?? '') ?: '#DD9933';
    $speisen_farbe_dark = sanitize_hex_color($input['speisen_farbe_dark'] ?? '') ?: '#FFFFFF';
    $preis_farbe_dark = sanitize_hex_color($input['preis_farbe_dark'] ?? '') ?: '#FFFFFF';

    $options['bg_color'] = $bg_color;
    $options['active_color'] = $active_color;
    $options['font_color'] = $font_color;
    $options['bg_color_dark'] = $bg_color_dark;
    $options['active_color_dark'] = $active_color_dark;
    $options['font_color_dark'] = $font_color_dark;

    update_option('speisekarte_font_family', $font_family);
    update_option('speisekarte_item_font_family', $item_font_family);
    update_option('speisekarte_item_font_size', $item_font_size);
    update_option('speisekarte_item_font_weight', $item_font_weight);
    update_option('speisekarte_item_font_style', $item_font_style);
    update_option('speisekarte_font_kategorie', $font_kategorie);
    update_option('speisekarte_font_speise', $font_speise);
    update_option('speisekarte_font_preis', $font_preis);
    update_option('speisekarte_font_beschreibung', $font_beschreibung);
    update_option('speisekarte_font_inhalt_label', $font_inhalt_label);
    update_option('speisekarte_font_inhalt', $font_inhalt);

    $options['zusatz_farbe'] = $zusatz_farbe;
    $options['kategorie_farbe'] = $kategorie_farbe;
    $options['speisen_farbe'] = $speisen_farbe;
    $options['preis_farbe'] = $preis_farbe;
    $options['zusatz_farbe_dark'] = $zusatz_farbe_dark;
    $options['kategorie_farbe_dark'] = $kategorie_farbe_dark;
    $options['speisen_farbe_dark'] = $speisen_farbe_dark;
    $options['preis_farbe_dark'] = $preis_farbe_dark;
    update_option('wp_speisekarte_settings', $options);

    echo '<div class="updated notice"><p>Einstellungen gespeichert.</p></div>';
}

$fonts = [
    '' => 'Standard',
    'Arial, Helvetica, sans-serif' => 'Arial',
    '"Times New Roman", serif' => 'Times New Roman',
    'Georgia, serif' => 'Georgia',
    'Verdana, Geneva, sans-serif' => 'Verdana',
    '"Courier New", monospace' => 'Courier New'
];
?>
<div class="wrap speisekarte-admin">
    <h1>Design &amp; Schrift Einstellungen</h1>

    <form method="post">
        <?php wp_nonce_field('speisekarte_style_save'); ?>
        <h2>Schriftarten</h2>
        <h3>Allgemein</h3>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="font_family">Schriftart</label></th>
                <td>
                    <select id="font_family" name="font_family">
                        <?php foreach ($fonts as $val => $label): ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($font_family, $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="item_font_family">Schriftart Speisen</label></th>
                <td>
                    <select id="item_font_family" name="item_font_family">
                        <?php foreach ($fonts as $val => $label): ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($item_font_family, $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="item_font_size">Schriftgröße Speisen</label></th>
                <td><input type="text" id="item_font_size" name="item_font_size" value="<?php echo esc_attr($item_font_size); ?>" placeholder="z.B. 1em oder 16px"></td>
            </tr>
            <tr>
                <th scope="row">Formatierung Speisen</th>
                <td>
                    <label><input type="checkbox" name="item_font_weight" value="bold" <?php checked($item_font_weight, 'bold'); ?>> Fett</label>
                    <label style="margin-left:10px;"><input type="checkbox" name="item_font_style" value="italic" <?php checked($item_font_style, 'italic'); ?>> Kursiv</label>
                </td>
            </tr>
        </table>

        <h3>Schriftart nach Bereich</h3>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="font_kategorie">Kategorie-Namen</label></th>
                <td>
                    <select id="font_kategorie" name="font_kategorie">
                        <?php foreach ($fonts as $val => $label): ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($font_kategorie, $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="font_speise">Speisenname und Nummer</label></th>
                <td>
                    <select id="font_speise" name="font_speise">
                        <?php foreach ($fonts as $val => $label): ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($font_speise, $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="font_preis">Speisen Preis</label></th>
                <td>
                    <select id="font_preis" name="font_preis">
                        <?php foreach ($fonts as $val => $label): ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($font_preis, $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="font_beschreibung">Beschreibung</label></th>
                <td>
                    <select id="font_beschreibung" name="font_beschreibung">
                        <?php foreach ($fonts as $val => $label): ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($font_beschreibung, $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="font_inhalt_label">Überschrift Inhaltsstoffe</label></th>
                <td>
                    <select id="font_inhalt_label" name="font_inhalt_label">
                        <?php foreach ($fonts as $val => $label): ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($font_inhalt_label, $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="font_inhalt">Inhaltsstoffe</label></th>
                <td>
                    <select id="font_inhalt" name="font_inhalt">
                        <?php foreach ($fonts as $val => $label): ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($font_inhalt, $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>

        <h2>Farben</h2>
        <fieldset style="margin-top:1em;padding:1em;border:1px solid #ccc;">
            <legend><strong>White Mode Farben</strong></legend>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="bg_color">Kachel-Hintergrundfarbe</label></th>
                    <td><input type="text" id="bg_color" name="wp_speisekarte_settings[bg_color]" value="<?php echo esc_attr($bg_color); ?>" class="color-picker" data-default-color="#f1f1f1"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="active_color">Aktive Kategorie-Kachel</label></th>
                    <td><input type="text" id="active_color" name="wp_speisekarte_settings[active_color]" value="<?php echo esc_attr($active_color); ?>" class="color-picker" data-default-color="#e1e1e1"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="font_color">Allgemeine Schriftfarbe</label></th>
                    <td><input type="text" id="font_color" name="wp_speisekarte_settings[font_color]" value="<?php echo esc_attr($font_color); ?>" class="color-picker" data-default-color="#000000"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="zusatz_farbe">Farbe Zusatzinformationen</label></th>
                    <td><input type="text" id="zusatz_farbe" name="wp_speisekarte_settings[zusatz_farbe]" value="<?php echo esc_attr($zusatz_farbe); ?>" class="color-picker" data-default-color="#992766"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="kategorie_farbe">Farbe Kategoriebezeichnungen</label></th>
                    <td><input type="text" id="kategorie_farbe" name="wp_speisekarte_settings[kategorie_farbe]" value="<?php echo esc_attr($kategorie_farbe); ?>" class="color-picker" data-default-color="#000000"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="speisen_farbe">Farbe Speisennamen</label></th>
                    <td><input type="text" id="speisen_farbe" name="wp_speisekarte_settings[speisen_farbe]" value="<?php echo esc_attr($speisen_farbe); ?>" class="color-picker" data-default-color="#000000"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="preis_farbe">Farbe Preisangaben</label></th>
                    <td><input type="text" id="preis_farbe" name="wp_speisekarte_settings[preis_farbe]" value="<?php echo esc_attr($preis_farbe); ?>" class="color-picker" data-default-color="#000000"></td>
                </tr>
            </table>
        </fieldset>

        <fieldset style="margin-top:1em;padding:1em;border:1px solid #ccc;">
            <legend><strong>Dark Mode Farben</strong></legend>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="bg_color_dark">Kachel-Hintergrundfarbe (Dark)</label></th>
                    <td><input type="text" id="bg_color_dark" name="wp_speisekarte_settings[bg_color_dark]" value="<?php echo esc_attr($bg_color_dark); ?>" class="color-picker" data-default-color="#1b3b6f"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="active_color_dark">Aktive Kategorie-Kachel (Dark)</label></th>
                    <td><input type="text" id="active_color_dark" name="wp_speisekarte_settings[active_color_dark]" value="<?php echo esc_attr($active_color_dark); ?>" class="color-picker" data-default-color="#1e447c"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="font_color_dark">Allgemeine Schriftfarbe (Dark)</label></th>
                    <td><input type="text" id="font_color_dark" name="wp_speisekarte_settings[font_color_dark]" value="<?php echo esc_attr($font_color_dark); ?>" class="color-picker" data-default-color="#dddddd"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="zusatz_farbe_dark">Farbe Zusatzinformationen (Dark)</label></th>
                    <td><input type="text" id="zusatz_farbe_dark" name="wp_speisekarte_settings[zusatz_farbe_dark]" value="<?php echo esc_attr($zusatz_farbe_dark); ?>" class="color-picker" data-default-color="#DD9933"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="kategorie_farbe_dark">Farbe Kategoriebezeichnungen (Dark)</label></th>
                    <td><input type="text" id="kategorie_farbe_dark" name="wp_speisekarte_settings[kategorie_farbe_dark]" value="<?php echo esc_attr($kategorie_farbe_dark); ?>" class="color-picker" data-default-color="#DD9933"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="speisen_farbe_dark">Farbe Speisennamen (Dark)</label></th>
                    <td><input type="text" id="speisen_farbe_dark" name="wp_speisekarte_settings[speisen_farbe_dark]" value="<?php echo esc_attr($speisen_farbe_dark); ?>" class="color-picker" data-default-color="#FFFFFF"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="preis_farbe_dark">Farbe Preisangaben (Dark)</label></th>
                    <td><input type="text" id="preis_farbe_dark" name="wp_speisekarte_settings[preis_farbe_dark]" value="<?php echo esc_attr($preis_farbe_dark); ?>" class="color-picker" data-default-color="#FFFFFF"></td>
                </tr>
            </table>
        </fieldset>
        <p class="submit">
            <button class="button button-primary" name="style_save">Speichern</button>
        </p>
    </form>
</div>
