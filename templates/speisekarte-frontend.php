<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_kat   = $wpdb->prefix . 'speisekarte_kategorien';
$table_speise = $wpdb->prefix . 'speisekarte_speisen';
$table_inh   = $wpdb->prefix . 'speisekarte_inhaltsstoffe';
$columns     = max(1, intval(get_option('speisekarte_columns', 1)));

$kats = $wpdb->get_results("SELECT * FROM $table_kat ORDER BY sort, name");
if (!$kats) return;

$inh_map = [];
$rows = $wpdb->get_results("SELECT code, name FROM $table_inh ORDER BY code");
foreach ($rows as $r) {
    $inh_map[$r->code] = $r->name;
}
?>
<style>
.speisekarte-wrapper{max-width:900px;margin:0 auto;padding:1rem;font-family:Arial,sans-serif;}
.speisekarte-search input{width:100%;padding:0.5rem;border:1px solid #ccc;border-radius:0.25rem;margin-bottom:1rem;}
.speisekarte-hint{margin-bottom:0.5rem;font-size:0.9rem;}
.toggle-all{margin-bottom:1rem;background:#eee;border:1px solid #ccc;border-radius:0.25rem;padding:0.5rem 1rem;cursor:pointer;}
.speisekarte-grid{display:grid;grid-template-columns:repeat(<?php echo $columns; ?>,1fr);gap:2rem;}
@media(max-width:600px){.speisekarte-grid{grid-template-columns:1fr;}}
.speisekarte-kategorie{border:1px solid #ddd;border-radius:0.5rem;padding:1rem;}
.kategorie-header{padding:0.75rem;background:#f7f7f7;color:#333;cursor:pointer;font-weight:normal;font-size:1rem;display:flex;align-items:center;}
.kategorie-header .toggle-icon{margin-right:0.5rem;font-size:1.1rem;font-weight:bold;}
.kategorie-content{display:none;padding:1rem;}
.speise{padding:1rem 0;border-bottom:1px solid #eee;margin-bottom:1rem;}
.speise:last-child{border-bottom:none;margin-bottom:0;}
.speise .title{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;font-weight:normal;}
.speise .name{font-size:1rem;font-weight:500;}
.speise .preis{margin-left:1rem;white-space:nowrap;}
@media(max-width:600px){.speise .preis{width:100%;margin-left:0;text-align:right;margin-top:0.25rem;}}
.speise .desc{margin-top:0.25rem;font-size:0.9em;}
.speise .inh{display:block;margin-top:0.25rem;font-size:0.8em;font-style:italic;}
</style>
<div class="speisekarte-wrapper">
  <div class="speisekarte-search">
    <input type="text" id="speisekarte_search" placeholder="Suche...">
  </div>
  <p class="speisekarte-hint">Klicke auf eine Kategorie, um die Speisen anzuzeigen.</p>
  <button type="button" id="speisekarte_toggle_all" class="toggle-all">Alle öffnen</button>
  <div class="speisekarte-grid">
<?php foreach ($kats as $kat): ?>
    <div class="speisekarte-kategorie" data-kat="<?php echo $kat->id; ?>">
      <div class="kategorie-header"><span class="toggle-icon">\u25B6</span><span class="kat-name"><?php echo esc_html($kat->name); ?></span></div>
      <div class="kategorie-content">
<?php
        $speisen = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_speise WHERE kategorie_id=%d ORDER BY sort, nr",
            $kat->id
        ));
        if ($speisen) :
            foreach ($speisen as $sp) :
                $inh_display = '';
                if ($sp->inhaltsstoffe) {
                    $codes = array_filter(array_map('trim', explode(',', $sp->inhaltsstoffe)));
                    $names = [];
                    foreach ($codes as $c) {
                        $names[] = $inh_map[$c] ?? $c;
                    }
                    $inh_display = implode(',', $names);
                }
?>
        <div class="speise" data-nr="<?php echo esc_attr($sp->nr); ?>" data-name="<?php echo esc_attr($sp->name); ?>" data-beschreibung="<?php echo esc_attr($sp->beschreibung); ?>" data-inhaltsstoffe="<?php echo esc_attr($sp->inhaltsstoffe); ?>">
          <div class="title">
            <span class="name"><?php echo esc_html($sp->name); ?></span>
            <span class="preis"><?php echo number_format($sp->preis, 2, ',', '.'); ?> €</span>
          </div>
          <?php if ($sp->beschreibung) : ?>
          <div class="desc"><?php echo esc_html($sp->beschreibung); ?></div>
          <?php endif; ?>
          <?php if ($inh_display) : ?>
          <small class="inh"><?php echo esc_html($inh_display); ?></small>
          <?php endif; ?>
        </div>
<?php
            endforeach;
        else :
?>
        <em>Keine Speisen in dieser Kategorie.</em>
<?php
        endif;
?>
      </div>
    </div>
<?php endforeach; ?>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
  function toggleCategory(head, show){
    var content=head.nextElementSibling;
    var icon=head.querySelector('.toggle-icon');
    var open=typeof show==='boolean'?show:content.style.display!=='block';
    content.style.display=open?'block':'none';
    if(icon) icon.textContent=open?'\u25BC':'\u25B6';
  }

  document.querySelectorAll('.kategorie-header').forEach(function(head){
    head.style.cursor='pointer';
    head.addEventListener('click',function(){toggleCategory(head);});
  });

  var toggleAllBtn=document.getElementById('speisekarte_toggle_all');
  if(toggleAllBtn){
    toggleAllBtn.addEventListener('click',function(){
      var open=this.dataset.state!=='open';
      document.querySelectorAll('.kategorie-header').forEach(function(h){
        toggleCategory(h,open);
      });
      this.dataset.state=open?'open':'closed';
      this.textContent=open?'Alle schließen':'Alle öffnen';
    });
  }
  var search=document.getElementById('speisekarte_search');
  if(search){
    search.addEventListener('input',function(){
      var q=search.value.toLowerCase();
      document.querySelectorAll('.speisekarte-kategorie').forEach(function(cat){
        var any=false;
        cat.querySelectorAll('.speise').forEach(function(item){
          var text=(item.dataset.nr+' '+item.dataset.name+' '+item.dataset.beschreibung+' '+item.dataset.inhaltsstoffe).toLowerCase();
          var match=!q||text.indexOf(q)!==-1;
          item.style.display=match?'':'none';
          if(match) any=true;
        });
        var header=cat.querySelector('.kategorie-header');
        if(q){
          cat.style.display=any?'':'none';
          header && toggleCategory(header, any);
        }else{
          cat.style.display='';
          header && toggleCategory(header, false);
        }
      });
    });
  }
});
</script>
