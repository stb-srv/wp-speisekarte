<?php
if (!defined('ABSPATH')) exit;
$bg_color = get_option('speisekarte_background_color', '#f1f1f1');
$active_color = get_option('speisekarte_active_color', '#e1e1e1');
$bg_color_dark = get_option('speisekarte_background_color_dark', '#1b3b6f');
$active_color_dark = get_option('speisekarte_active_color_dark', '#1e447c');
if (isset($_POST['design_save'])) {
    check_admin_referer('speisekarte_design_save');
    $bg_color = sanitize_hex_color($_POST['bg_color']) ?: '#f1f1f1';
    $active_color = sanitize_hex_color($_POST['active_color']) ?: '#e1e1e1';
    $bg_color_dark = sanitize_hex_color($_POST['bg_color_dark']) ?: '#1b3b6f';
    $active_color_dark = sanitize_hex_color($_POST['active_color_dark']) ?: '#1e447c';
    update_option('speisekarte_background_color', $bg_color);
    update_option('speisekarte_active_color', $active_color);
    update_option('speisekarte_background_color_dark', $bg_color_dark);
    update_option('speisekarte_active_color_dark', $active_color_dark);
    echo '<div class="updated notice"><p>Einstellungen gespeichert.</p></div>';
}
?>
<div class="wrap">
    <h1>Design Einstellungen</h1>
    <form method="post">
        <?php wp_nonce_field('speisekarte_design_save'); ?>
        <h2>White Mode</h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="bg_color">Kachel-Hintergrund</label></th>
                <td><input type="text" id="bg_color" name="bg_color" value="<?php echo esc_attr($bg_color); ?>" class="color-picker" data-default-color="#f1f1f1"></td>
            </tr>
            <tr>
                <th scope="row"><label for="active_color">Aktive Kachel</label></th>
                <td><input type="text" id="active_color" name="active_color" value="<?php echo esc_attr($active_color); ?>" class="color-picker" data-default-color="#e1e1e1"></td>
            </tr>
        </table>
        <h2>Dark Mode</h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="bg_color_dark">Kachel-Hintergrund</label></th>
                <td><input type="text" id="bg_color_dark" name="bg_color_dark" value="<?php echo esc_attr($bg_color_dark); ?>" class="color-picker" data-default-color="#1b3b6f"></td>
            </tr>
            <tr>
                <th scope="row"><label for="active_color_dark">Aktive Kachel</label></th>
                <td><input type="text" id="active_color_dark" name="active_color_dark" value="<?php echo esc_attr($active_color_dark); ?>" class="color-picker" data-default-color="#1e447c"></td>
            </tr>
        </table>
        <p class="submit">
            <button class="button button-primary" name="design_save">Speichern</button>
        </p>
    </form>
</div>
