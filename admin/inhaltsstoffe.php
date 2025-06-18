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

$codes = $wpdb->get_results("SELECT * FROM $table_inh ORDER BY code");
?>
<div class="wrap">
    <h1>Inhaltsstoffe</h1>
    <form method="post" id="inh_form" style="margin-bottom:2em;">
        <input type="hidden" name="inh_id" value="">
        <input type="text" name="code" placeholder="Code" style="width:5em;" required>
        <input type="text" name="name" placeholder="Name" required>
        <button class="button button-primary" name="inh_save">Speichern</button>
    </form>
    <table class="widefat">
        <thead><tr><th>Code</th><th>Name</th><th>Aktion</th></tr></thead>
        <tbody>
        <?php foreach($codes as $c): ?>
            <tr data-id="<?php echo $c->id; ?>" data-code="<?php echo esc_attr($c->code); ?>" data-name="<?php echo esc_attr($c->name); ?>">
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
</div>
