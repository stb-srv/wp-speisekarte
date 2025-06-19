<?php
if (!defined('ABSPATH')) exit;
$font_family = get_option('speisekarte_font_family', '');
$font_color = get_option('speisekarte_font_color', '#000000');
$bg_color = get_option('speisekarte_background_color', '#f1f1f1');
$active_color = get_option('speisekarte_active_color', '#e1e1e1');
if (isset($_POST['design_save'])) {
    check_admin_referer('speisekarte_design_save');
    $font_family = sanitize_text_field($_POST['font_family']);
    $font_color = sanitize_hex_color($_POST['font_color']) ?: '#000000';
    $bg_color = sanitize_hex_color($_POST['bg_color']) ?: '#f1f1f1';
    $active_color = sanitize_hex_color($_POST['active_color']) ?: '#e1e1e1';
    update_option('speisekarte_font_family', $font_family);
    update_option('speisekarte_font_color', $font_color);
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
                <th scope="row"><label for="font_color">Schriftfarbe</label></th>
                <td><input type="text" id="font_color" name="font_color" value="<?php echo esc_attr($font_color); ?>" class="color-picker" data-default-color="#000000"></td>
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
