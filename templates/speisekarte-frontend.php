<?php
global $wpdb;
$table_kat = $wpdb->prefix . 'speisekarte_kategorien';
$table_speise = $wpdb->prefix . 'speisekarte_speisen';

$kats = $wpdb->get_results("SELECT * FROM $table_kat ORDER BY sort, name");
if(!$kats) return;
?>
<div class="speisekarte-accordion">
<?php foreach($kats as $kat): ?>
    <div class="speisekarte-kat">
        <button class="speisekarte-toggle"><?php echo esc_html($kat->name); ?></button>
        <div class="speisekarte-content">
            <?php
            $speisen = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_speise WHERE kategorie_id=%d ORDER BY sort, nr",
                $kat->id
            ));
            if($speisen): ?>
            <table>
                <tbody>
                <?php foreach($speisen as $sp): ?>
                    <tr>
                        <td class="nr"><?php echo esc_html($sp->nr); ?></td>
                        <td class="name">
                            <b><?php echo esc_html($sp->name); ?></b>
                            <div class="desc"><?php echo esc_html($sp->beschreibung); ?></div>
                            <?php if($sp->inhaltsstoffe): ?>
                                <small class="inh"><?php echo esc_html($sp->inhaltsstoffe); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="bild">
                        <?php if($sp->bild_id): 
                            $url = wp_get_attachment_url($sp->bild_id);
                            if($url): ?>
                                <img src="<?php echo esc_url($url); ?>" style="max-width:80px;max-height:80px;" />
                            <?php endif;
                        endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <em>Keine Speisen in dieser Kategorie.</em>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>
