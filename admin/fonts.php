<?php
if (!defined('ABSPATH')) exit;
$options            = get_option('wp_speisekarte_settings', []);
$font_family       = get_option('speisekarte_font_family', '');
$item_font_family  = get_option('speisekarte_item_font_family', '');
$item_font_size    = get_option('speisekarte_item_font_size', '');
$item_font_weight  = get_option('speisekarte_item_font_weight', '');
$item_font_style   = get_option('speisekarte_item_font_style', '');

$zusatz_farbe         = $options['zusatz_farbe'] ?? '#992766';
$kategorie_farbe      = $options['kategorie_farbe'] ?? '#000000';
$speisen_farbe        = $options['speisen_farbe'] ?? '#000000';
$preis_farbe          = $options['preis_farbe'] ?? '#000000';
$zusatz_farbe_dark    = $options['zusatz_farbe_dark'] ?? '#DD9933';
$kategorie_farbe_dark = $options['kategorie_farbe_dark'] ?? '#DD9933';
$speisen_farbe_dark   = $options['speisen_farbe_dark'] ?? '#FFFFFF';
$preis_farbe_dark     = $options['preis_farbe_dark'] ?? '#FFFFFF';
if (isset($_POST['fonts_save'])) {
    check_admin_referer('speisekarte_fonts_save');
    $input = $_POST['wp_speisekarte_settings'] ?? [];
    $font_family = sanitize_text_field($_POST['font_family']);
    $zusatz_farbe      = sanitize_hex_color($input['zusatz_farbe'] ?? '') ?: '#992766';
    $kategorie_farbe   = sanitize_hex_color($input['kategorie_farbe'] ?? '') ?: '#000000';
    $speisen_farbe     = sanitize_hex_color($input['speisen_farbe'] ?? '') ?: '#000000';
    $preis_farbe       = sanitize_hex_color($input['preis_farbe'] ?? '') ?: '#000000';
    $item_font_family  = sanitize_text_field($_POST['item_font_family']);
    $item_font_size    = sanitize_text_field($_POST['item_font_size']);
    $zusatz_farbe_dark    = sanitize_hex_color($input['zusatz_farbe_dark'] ?? '') ?: '#DD9933';
    $kategorie_farbe_dark = sanitize_hex_color($input['kategorie_farbe_dark'] ?? '') ?: '#DD9933';
    $speisen_farbe_dark   = sanitize_hex_color($input['speisen_farbe_dark'] ?? '') ?: '#FFFFFF';
    $preis_farbe_dark     = sanitize_hex_color($input['preis_farbe_dark'] ?? '') ?: '#FFFFFF';
    $item_font_weight = isset($_POST['item_font_weight']) ? 'bold' : '';
    $item_font_style  = isset($_POST['item_font_style']) ? 'italic' : '';

    update_option('speisekarte_font_family', $font_family);
    update_option('speisekarte_item_font_family', $item_font_family);
    update_option('speisekarte_item_font_size', $item_font_size);
    update_option('speisekarte_item_font_weight', $item_font_weight);
    update_option('speisekarte_item_font_style', $item_font_style);

    $options['zusatz_farbe']      = $zusatz_farbe;
    $options['kategorie_farbe']   = $kategorie_farbe;
    $options['speisen_farbe']     = $speisen_farbe;
    $options['preis_farbe']       = $preis_farbe;
    $options['zusatz_farbe_dark']    = $zusatz_farbe_dark;
    $options['kategorie_farbe_dark'] = $kategorie_farbe_dark;
    $options['speisen_farbe_dark']   = $speisen_farbe_dark;
    $options['preis_farbe_dark']     = $preis_farbe_dark;
    update_option('wp_speisekarte_settings', $options);
    echo '<div class="updated notice"><p>Einstellungen gespeichert.</p></div>';
}
?>
<div class="wrap speisekarte-admin">
    <h1>Schrift Einstellungen</h1>
    <form method="post">
        <?php wp_nonce_field('speisekarte_fonts_save'); ?>
        <h2>Allgemeine Einstellungen</h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="font_family">Schriftart</label></th>
                <td>
                    <?php
                    $fonts = [
                        '' => 'Standard',
                        'Arial, Helvetica, sans-serif' => 'Arial',
                        '"Times New Roman", serif' => 'Times New Roman',
                        'Georgia, serif' => 'Georgia',
                        'Verdana, Geneva, sans-serif' => 'Verdana',
                        '"Courier New", monospace' => 'Courier New'
                    ];
                    ?>
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

        <fieldset style="margin-top:1em;padding:1em;border:1px solid #ccc;">
            <legend><strong>White Mode Farben</strong></legend>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="zusatz_farbe">Zusatzinformationen</label></th>
                    <td><input type="text" id="zusatz_farbe" name="wp_speisekarte_settings[zusatz_farbe]" value="<?php echo esc_attr($zusatz_farbe); ?>" class="color-picker" data-default-color="#992766"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="kategorie_farbe">Kategorie</label></th>
                    <td><input type="text" id="kategorie_farbe" name="wp_speisekarte_settings[kategorie_farbe]" value="<?php echo esc_attr($kategorie_farbe); ?>" class="color-picker" data-default-color="#000000"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="speisen_farbe">Speisen</label></th>
                    <td><input type="text" id="speisen_farbe" name="wp_speisekarte_settings[speisen_farbe]" value="<?php echo esc_attr($speisen_farbe); ?>" class="color-picker" data-default-color="#000000"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="preis_farbe">Preisfarbe</label></th>
                    <td><input type="text" id="preis_farbe" name="wp_speisekarte_settings[preis_farbe]" value="<?php echo esc_attr($preis_farbe); ?>" class="color-picker" data-default-color="#000000"></td>
                </tr>
            </table>
        </fieldset>

        <fieldset style="margin-top:1em;padding:1em;border:1px solid #ccc;">
            <legend><strong>Dark Mode Farben</strong></legend>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="zusatz_farbe_dark">Zusatzinformationen (Dark Mode)</label></th>
                    <td><input type="text" id="zusatz_farbe_dark" name="wp_speisekarte_settings[zusatz_farbe_dark]" value="<?php echo esc_attr($zusatz_farbe_dark); ?>" class="color-picker" data-default-color="#DD9933"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="kategorie_farbe_dark">Kategorie (Dark Mode)</label></th>
                    <td><input type="text" id="kategorie_farbe_dark" name="wp_speisekarte_settings[kategorie_farbe_dark]" value="<?php echo esc_attr($kategorie_farbe_dark); ?>" class="color-picker" data-default-color="#DD9933"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="speisen_farbe_dark">Speisen (Dark Mode)</label></th>
                    <td><input type="text" id="speisen_farbe_dark" name="wp_speisekarte_settings[speisen_farbe_dark]" value="<?php echo esc_attr($speisen_farbe_dark); ?>" class="color-picker" data-default-color="#FFFFFF"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="preis_farbe_dark">Preisfarbe (Dark Mode)</label></th>
                    <td><input type="text" id="preis_farbe_dark" name="wp_speisekarte_settings[preis_farbe_dark]" value="<?php echo esc_attr($preis_farbe_dark); ?>" class="color-picker" data-default-color="#FFFFFF"></td>
                </tr>
            </table>
        </fieldset>
        <p class="submit">
            <button class="button button-primary" name="fonts_save">Speichern</button>
        </p>
    </form>
</div>
