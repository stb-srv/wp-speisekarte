<?php
global $wpdb;
$table_kat = $wpdb->prefix . 'speisekarte_kategorien';
$table_speise = $wpdb->prefix . 'speisekarte_speisen';
$columns = intval(get_option('speisekarte_columns', 1));
$tile_height = intval(get_option('speisekarte_tile_height', 0));
$tile_width = intval(get_option('speisekarte_tile_width', 0));

$kats = $wpdb->get_results("SELECT * FROM $table_kat ORDER BY sort, name");
if(!$kats) return;
?>
<div class="speisekarte-accordion" style="--columns: <?php echo $columns; ?>;<?php if($tile_height) echo '--tile-height:'.$tile_height.'px;'; ?><?php if($tile_width) echo '--tile-width:'.$tile_width.'px;'; ?>" data-tile-height="<?php echo $tile_height; ?>" data-tile-width="<?php echo $tile_width; ?>">
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
            <div class="speisekarte-grid" style="grid-template-columns: repeat(<?php echo $columns; ?>, 1fr);">
                <?php foreach($speisen as $sp): ?>
                    <div class="speisekarte-item">
                        <div class="nr"><?php echo esc_html($sp->nr); ?></div>
                        <div class="name">
                            <b><?php echo esc_html($sp->name); ?></b>
                            <div class="desc"><?php echo esc_html($sp->beschreibung); ?></div>
                            <?php if($sp->inhaltsstoffe): ?>
                                <small class="inh"><?php echo esc_html($sp->inhaltsstoffe); ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="preis"><?php echo number_format($sp->preis, 2, ',', '.'); ?> €</div>
                        <div class="bild">
                        <?php if($sp->bild_id):
                            $url = wp_get_attachment_url($sp->bild_id);
                            if($url): ?>
                                <img src="<?php echo esc_url($url); ?>" style="max-width:80px;max-height:80px;" />
                            <?php endif;
                        endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <em>Keine Speisen in dieser Kategorie.</em>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>
