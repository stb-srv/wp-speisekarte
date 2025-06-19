<?php
if (!defined('ABSPATH')) exit;
$font_family = get_option('speisekarte_font_family', '');
$bg_color = get_option('speisekarte_background_color', '#f1f1f1');
$active_color = get_option('speisekarte_active_color', '#e1e1e1');
if (isset($_POST['design_save'])) {
    check_admin_referer('speisekarte_design_save');
    $font_family = sanitize_text_field($_POST['font_family']);
    $bg_color = sanitize_hex_color($_POST['bg_color']) ?: '#f1f1f1';
    $active_color = sanitize_hex_color($_POST['active_color']) ?: '#e1e1e1';
    update_option('speisekarte_font_family', $font_family);
    update_option('speisekarte_background_color', $bg_color);
    update_option('speisekarte_active_color', $active_color);
    echo '<div class="updated notice"><p>Einstellungen gespeichert.</p></div>';
}
?>
<div class="wrap">
    <h1>Design Einstellungen</h1>
    <form method="post">
        <?php wp_nonce_field('speisekarte_design_save'); ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="font_family">Schriftart</label></th>
                <td><input type="text" id="font_family" name="font_family" value="<?php echo esc_attr($font_family); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="bg_color">Kachel-Hintergrund</label></th>
                <td><input type="text" id="bg_color" name="bg_color" value="<?php echo esc_attr($bg_color); ?>" class="color-picker" data-default-color="#f1f1f1"></td>
            </tr>
            <tr>
                <th scope="row"><label for="active_color">Aktive Kachel</label></th>
                <td><input type="text" id="active_color" name="active_color" value="<?php echo esc_attr($active_color); ?>" class="color-picker" data-default-color="#e1e1e1"></td>
            </tr>
        </table>
        <p class="submit">
            <button class="button button-primary" name="design_save">Speichern</button>
        </p>
    </form>
</div>
