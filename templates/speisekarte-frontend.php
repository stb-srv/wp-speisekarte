<?php
global $wpdb;
$table_kat = $wpdb->prefix . 'speisekarte_kategorien';
$table_speise = $wpdb->prefix . 'speisekarte_speisen';
$table_inh = $wpdb->prefix . 'speisekarte_inhaltsstoffe';
$columns = intval(get_option('speisekarte_columns', 1)); // not used but kept for compatibility
$tile_height = intval(get_option('speisekarte_tile_height', 0)); // not used
$tile_width = intval(get_option('speisekarte_tile_width', 0)); // not used

$kats = $wpdb->get_results("SELECT * FROM $table_kat ORDER BY sort, name");
if(!$kats) return;

$inh_map = [];
$rows = $wpdb->get_results("SELECT code, name FROM $table_inh ORDER BY code");
foreach($rows as $r){
    $inh_map[$r->code] = $r->name;
}
$column_css = $columns <= 1 ? '1fr' : 'repeat('.$columns.', 1fr)'; // not used
?>
<style>
/* Wrapper and search */
.speisekarte-wrapper{max-width:900px;margin:0 auto;padding:1rem;font-family:Arial, sans-serif;}
.speisekarte-search{margin-bottom:1rem;}
.speisekarte-search input{width:100%;padding:0.75rem;background:#f4f4f4;border:1px solid #ccc;border-radius:0.5rem;color:#000;font-size:16px;}

/* Category heading */
.speisekarte-kategorie{text-align:center;font-weight:normal;font-size:1.5rem;margin:2rem 0 1rem;}

/* Single dish block */
.speisekarte-speise{display:grid;grid-template-columns:40px 1fr auto;column-gap:10px;align-items:start;background:#fafafa;border:1px solid #ddd;border-radius:6px;padding:0.75rem;margin-bottom:1rem;box-shadow:0 2px 4px rgba(0,0,0,0.05);}
.speisekarte-speise .title{display:flex;justify-content:space-between;font-weight:bold;margin-bottom:2px;}
.speisekarte-speise .desc{display:block;margin-top:2px;font-weight:normal;}
.speisekarte-speise .inh{display:block;margin-top:2px;font-size:0.9em;font-style:italic;}
.speisekarte-speise .bild img{border-radius:6px;max-width:80px;max-height:80px;}
.speisekarte-speise .preis{margin-left:10px;white-space:nowrap;}
</style>
<div class="speisekarte-wrapper">
    <div class="speisekarte-search">
        <input type="text" id="speisekarte_search" placeholder="Suche...">
    </div>
    <?php foreach($kats as $kat): ?>
    <div class="speisekarte-kat" data-kat="<?php echo $kat->id; ?>">
        <div class="speisekarte-kategorie"><?php echo esc_html($kat->name); ?></div>
        <?php
        $speisen = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_speise WHERE kategorie_id=%d ORDER BY sort, nr",
            $kat->id
        ));
        if($speisen):
            foreach($speisen as $sp):
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
        <div class="speisekarte-item speisekarte-speise"
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
                    <img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($sp->name); ?>" />
                <?php endif;
            endif; ?>
            </div>
        </div>
        <?php
            endforeach;
        else:
        ?>
            <em>Keine Speisen in dieser Kategorie.</em>
        <?php
        endif;
        ?>
    </div>
    <?php endforeach; ?>
</div>

