<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_inh = $wpdb->prefix . 'speisekarte_inhaltsstoffe';

if (isset($_POST['inh_save'])) {
    $code = sanitize_text_field($_POST['code']);
    $name = sanitize_text_field($_POST['name']);
    if (isset($_POST['inh_id']) && $_POST['inh_id']) {
        $wpdb->update($table_inh, ['code' => $code, 'name' => $name], ['id' => intval($_POST['inh_id'])]);
    } else {
        $wpdb->insert($table_inh, ['code' => $code, 'name' => $name]);
    }
}

if (isset($_GET['inh_del'])) {
    $wpdb->delete($table_inh, ['id' => intval($_GET['inh_del'])]);
}
if (isset($_POST['bulk_del_inh']) && !empty($_POST['inh_ids']) && check_admin_referer('speisekarte_bulk_delete')) {
    foreach ((array)$_POST['inh_ids'] as $id) {
        $wpdb->delete($table_inh, ['id' => intval($id)]);
    }
}

$codes = $wpdb->get_results("SELECT * FROM $table_inh ORDER BY code");
?>
<div class="wrap speisekarte-admin">
    <h1>Inhaltsstoffe</h1>
    <form method="post" id="inh_form" style="margin-bottom:2em;">
        <input type="hidden" name="inh_id" value="">
        <input type="text" name="code" placeholder="Code" style="width:5em;" required>
        <input type="text" name="name" placeholder="Name" required>
        <button class="button button-primary" name="inh_save">Speichern</button>
    </form>
    <form method="post" id="inh_bulk_form">
        <?php wp_nonce_field('speisekarte_bulk_delete'); ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th style="width:30px;"><input type="checkbox" id="inh_all"></th>
                    <th>Code</th><th>Name</th><th>Aktion</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($codes as $c): ?>
                <tr data-id="<?php echo $c->id; ?>" data-code="<?php echo esc_attr($c->code); ?>" data-name="<?php echo esc_attr($c->name); ?>">
                    <td><input type="checkbox" class="inh_cb" name="inh_ids[]" value="<?php echo $c->id; ?>"></td>
                    <td><?php echo esc_html($c->code); ?></td>
                    <td><?php echo esc_html($c->name); ?></td>
                    <td>
                        <a href="#" class="inh_edit">Bearbeiten</a> |
                        <a href="?page=speisekarte-inhaltsstoffe&inh_del=<?php echo $c->id; ?>" onclick="return confirm('Wirklich löschen?')">Löschen</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p><button class="button" name="bulk_del_inh" onclick="return confirm('Ausgewählte Inhaltsstoffe löschen?')">Ausgewählte löschen</button></p>
    </form>
</div>
