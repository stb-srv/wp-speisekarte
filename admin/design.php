<?php
if (!defined('ABSPATH')) exit;
$options = get_option('wp_speisekarte_settings', []);
$bg_color = $options['bg_color'] ?? '#f1f1f1';
$active_color = $options['active_color'] ?? '#e1e1e1';
$font_color = $options['font_color'] ?? '#000000';
$bg_color_dark = $options['bg_color_dark'] ?? '#1b3b6f';
$active_color_dark = $options['active_color_dark'] ?? '#1e447c';
$font_color_dark = $options['font_color_dark'] ?? '#dddddd'; // ensure fallback color
if (isset($_POST['design_save'])) {
    check_admin_referer('speisekarte_design_save');
    $input = $_POST['wp_speisekarte_settings'] ?? [];
    $bg_color = sanitize_hex_color($input['bg_color'] ?? '') ?: '#f1f1f1';
    $active_color = sanitize_hex_color($input['active_color'] ?? '') ?: '#e1e1e1';
    $font_color = sanitize_hex_color($input['font_color'] ?? '') ?: '#000000';
    $bg_color_dark = sanitize_hex_color($input['bg_color_dark'] ?? '') ?: '#1b3b6f';
    $active_color_dark = sanitize_hex_color($input['active_color_dark'] ?? '') ?: '#1e447c';
    $font_color_dark = sanitize_hex_color($input['font_color_dark'] ?? '') ?: '#dddddd'; // keep default

    $options['bg_color'] = $bg_color;
    $options['active_color'] = $active_color;
    $options['font_color'] = $font_color;
    $options['bg_color_dark'] = $bg_color_dark;
    $options['active_color_dark'] = $active_color_dark;
    $options['font_color_dark'] = $font_color_dark;

    update_option('wp_speisekarte_settings', $options);
    echo '<div class="updated notice"><p>Einstellungen gespeichert.</p></div>';
}
?>
<div class="wrap speisekarte-admin">
    <h1>Design Einstellungen</h1>
    <form method="post">
        <?php wp_nonce_field('speisekarte_design_save'); ?>
        <fieldset style="margin-top:1em;padding:1em;border:1px solid #ccc;">
            <legend><strong>White Mode</strong></legend>
            <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="bg_color">Kachel-Hintergrund</label></th>
                <td><input type="text" id="bg_color" name="wp_speisekarte_settings[bg_color]" value="<?php echo esc_attr($bg_color); ?>" class="color-picker" data-default-color="#f1f1f1"></td>
            </tr>
            <tr>
                <th scope="row"><label for="active_color">Aktive Kachel</label></th>
                <td><input type="text" id="active_color" name="wp_speisekarte_settings[active_color]" value="<?php echo esc_attr($active_color); ?>" class="color-picker" data-default-color="#e1e1e1"></td>
            </tr>
            <tr>
                <th scope="row"><label for="font_color">Schriftfarbe</label></th>
                <td><input type="text" id="font_color" name="wp_speisekarte_settings[font_color]" value="<?php echo esc_attr($font_color); ?>" class="color-picker" data-default-color="#000000"></td>
            </tr>
            </table>
        </fieldset>
        <fieldset style="margin-top:1em;padding:1em;border:1px solid #ccc;">
            <legend><strong>Dark Mode</strong></legend>
            <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="bg_color_dark">Kachel-Hintergrund</label></th>
                <td><input type="text" id="bg_color_dark" name="wp_speisekarte_settings[bg_color_dark]" value="<?php echo esc_attr($bg_color_dark); ?>" class="color-picker" data-default-color="#1b3b6f"></td>
            </tr>
            <tr>
                <th scope="row"><label for="active_color_dark">Aktive Kachel</label></th>
                <td><input type="text" id="active_color_dark" name="wp_speisekarte_settings[active_color_dark]" value="<?php echo esc_attr($active_color_dark); ?>" class="color-picker" data-default-color="#1e447c"></td>
            </tr>
            <tr>
                <th scope="row"><label for="font_color_dark">Schriftfarbe (Dark Mode)</label></th>
                <td><input type="text" id="font_color_dark" name="wp_speisekarte_settings[font_color_dark]" value="<?php echo esc_attr($font_color_dark); ?>" class="color-picker" data-default-color="#dddddd"></td>
            </tr>
            </table>
        </fieldset>
        <p class="submit">
            <button class="button button-primary" name="design_save">Speichern</button>
        </p>
    </form>
</div>
