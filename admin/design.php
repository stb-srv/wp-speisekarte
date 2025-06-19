<?php
if (!defined('ABSPATH')) exit;
$font_family = get_option('speisekarte_font_family', '');
$font_color = get_option('speisekarte_font_color', '#000000');
$tile_font_color = get_option('speisekarte_tile_font_color', '#000000');
$item_font_color = get_option('speisekarte_item_font_color', '#000000');
$item_font_family_item = get_option('speisekarte_item_font_family', '');
$item_font_size = get_option('speisekarte_item_font_size', '');
$item_font_weight = get_option('speisekarte_item_font_weight', '');
$item_font_style = get_option('speisekarte_item_font_style', '');
$bg_color = get_option('speisekarte_background_color', '#f1f1f1');
$active_color = get_option('speisekarte_active_color', '#e1e1e1');
if (isset($_POST['design_save'])) {
    check_admin_referer('speisekarte_design_save');
    $font_family = sanitize_text_field($_POST['font_family']);
    $font_color = sanitize_hex_color($_POST['font_color']) ?: '#000000';
    $tile_font_color = sanitize_hex_color($_POST['tile_font_color']) ?: '#000000';
    $item_font_color = sanitize_hex_color($_POST['item_font_color']) ?: '#000000';
    $item_font_family_item = sanitize_text_field($_POST['item_font_family']);
    $item_font_size = sanitize_text_field($_POST['item_font_size']);
    $item_font_weight = isset($_POST['item_font_weight']) ? 'bold' : '';
    $item_font_style = isset($_POST['item_font_style']) ? 'italic' : '';
    $bg_color = sanitize_hex_color($_POST['bg_color']) ?: '#f1f1f1';
    $active_color = sanitize_hex_color($_POST['active_color']) ?: '#e1e1e1';
    update_option('speisekarte_font_family', $font_family);
    update_option('speisekarte_font_color', $font_color);
    update_option('speisekarte_tile_font_color', $tile_font_color);
    update_option('speisekarte_item_font_color', $item_font_color);
    update_option('speisekarte_item_font_family', $item_font_family_item);
    update_option('speisekarte_item_font_size', $item_font_size);
    update_option('speisekarte_item_font_weight', $item_font_weight);
    update_option('speisekarte_item_font_style', $item_font_style);
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
                <th scope="row"><label for="tile_font_color">Schriftfarbe Kategorie</label></th>
                <td><input type="text" id="tile_font_color" name="tile_font_color" value="<?php echo esc_attr($tile_font_color); ?>" class="color-picker" data-default-color="#000000"></td>
            </tr>
            <tr>
                <th scope="row"><label for="item_font_color">Schriftfarbe Speisen</label></th>
                <td><input type="text" id="item_font_color" name="item_font_color" value="<?php echo esc_attr($item_font_color); ?>" class="color-picker" data-default-color="#000000"></td>
            </tr>
            <tr>
                <th scope="row"><label for="item_font_family">Schriftart Speisen</label></th>
                <td>
                    <select id="item_font_family" name="item_font_family">
                        <?php foreach ($fonts as $val => $label): ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($item_font_family_item, $val); ?>><?php echo esc_html($label); ?></option>
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
