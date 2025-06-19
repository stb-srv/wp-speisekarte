<?php
global $wpdb;
$table_kat = $wpdb->prefix . 'speisekarte_kategorien';
$table_speise = $wpdb->prefix . 'speisekarte_speisen';
$table_inh = $wpdb->prefix . 'speisekarte_inhaltsstoffe';
$columns = intval(get_option('speisekarte_columns', 1));
$tile_height = intval(get_option('speisekarte_tile_height', 0));
$tile_width = intval(get_option('speisekarte_tile_width', 0));

$kats = $wpdb->get_results("SELECT * FROM $table_kat ORDER BY sort, name");
if(!$kats) return;

$inh_map = [];
$rows = $wpdb->get_results("SELECT code, name FROM $table_inh ORDER BY code");
foreach($rows as $r){
    $inh_map[$r->code] = $r->name;
}
$column_css = $columns <= 1 ? '1fr' : 'repeat('.$columns.', 1fr)';
?>
<style>
.speisekarte-wrapper{max-width:1200px;margin:0 auto;padding:1rem;}
.speisekarte-search input{width:100%;padding:0.75rem;background:#f4f4f4;border:1px solid #ccc;border-radius:0.5rem;color:#000;font-size:16px;}
.menu-grid{display:grid;gap:1rem;grid-template-columns:<?php echo $column_css; ?>;width:100%;}
@media (max-width:600px){.menu-grid{grid-template-columns:1fr;}}
.menu-item{background:#d9d9d9;color:#000;border-radius:0.5rem;padding:1rem;min-height:120px;overflow:visible;}
.speisekarte-kat{overflow:visible;min-height:120px;}
</style>
<div class="speisekarte-wrapper">
    <div class="speisekarte-search">
        <input type="text" id="speisekarte_search" placeholder="Suche...">
    </div>
    <div class="speisekarte-accordion menu-grid" style="--columns: <?php echo $columns; ?>;<?php if($tile_height) echo '--tile-height:'.$tile_height.'px;'; ?><?php if($tile_width) echo '--tile-width:'.$tile_width.'px;'; ?>" data-tile-height="<?php echo $tile_height; ?>" data-tile-width="<?php echo $tile_width; ?>">
<?php foreach($kats as $kat): ?>
    <div class="speisekarte-kat" data-kat="<?php echo $kat->id; ?>">
        <button class="speisekarte-toggle"><?php echo esc_html($kat->name); ?></button>
        <div class="speisekarte-content">
            <?php
            $speisen = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_speise WHERE kategorie_id=%d ORDER BY sort, nr",
                $kat->id
            ));
            if($speisen): ?>
            <div class="speisekarte-grid menu-grid">
                <?php foreach($speisen as $sp):
                    $inh_display = '';
                    if($sp->inhaltsstoffe){
                        $codes = array_filter(array_map('trim', explode(',', $sp->inhaltsstoffe)));
                        $names = [];
                        foreach($codes as $c){
                            if(isset($inh_map[$c])) $names[] = $inh_map[$c];
                            else $names[] = $c;
                        }
                        $inh_display = implode(',', $names);
                    }
                ?>
                    <div class="speisekarte-item menu-item"
                        data-nr="<?php echo esc_attr($sp->nr); ?>"
                        data-name="<?php echo esc_attr($sp->name); ?>"
                        data-beschreibung="<?php echo esc_attr($sp->beschreibung); ?>"
                        data-inhaltsstoffe="<?php echo esc_attr($sp->inhaltsstoffe); ?>">
                        <div class="nr"><?php echo esc_html($sp->nr); ?></div>
                        <div class="details">
                            <div class="title">
                                <b class="name"><?php echo esc_html($sp->name); ?></b>
                                <span class="preis"><?php echo number_format($sp->preis, 2, ',', '.'); ?> €</span>
                            </div>
                            <div class="desc"><?php echo esc_html($sp->beschreibung); ?></div>
                            <?php if($inh_display): ?>
                                <small class="inh"><?php echo esc_html($inh_display); ?></small>
                            <?php endif; ?>
                        </div>
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
</div>
