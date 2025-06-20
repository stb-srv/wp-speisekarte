<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_kat   = $wpdb->prefix . 'speisekarte_kategorien';
$table_speise = $wpdb->prefix . 'speisekarte_speisen';
$table_inh   = $wpdb->prefix . 'speisekarte_inhaltsstoffe';
$columns     = max(1, intval(get_option('speisekarte_columns', 1)));

$kats = $wpdb->get_results("SELECT * FROM $table_kat ORDER BY sort, name");
if (!$kats) return;

$default_id = speisekarte_get_default_kategorie_id();
if (count($kats) > 1) {
    $kats = array_values(array_filter($kats, function($k) use ($default_id) {
        return $k->id != $default_id;
    }));
}

$inh_map = [];
$rows = $wpdb->get_results("SELECT code, name FROM $table_inh ORDER BY code");
foreach ($rows as $r) {
    $inh_map[$r->code] = $r->name;
}
?>
<style>
.speisekarte-wrapper{max-width:900px;margin:0 auto;padding:1rem;font-family:var(--font-family,Arial,sans-serif);color:var(--zusatz-weiss,#000);}
.speisekarte-search input{width:100%;padding:0.5rem;border:1px solid #ccc;border-radius:0.25rem;margin-bottom:1rem;}
.speisekarte-hint{margin-bottom:0.5rem;font-size:0.9rem;}
.toggle-all{margin-bottom:1rem;background:#eee;border:1px solid #ccc;border-radius:0.25rem;padding:0.5rem 1rem;cursor:pointer;}
.speisekarte-grid{display:grid;grid-template-columns:repeat(<?php echo $columns; ?>,1fr);gap:2rem;}
@media(max-width:600px){.speisekarte-grid{grid-template-columns:1fr;}}
.speisekarte-kategorie{border:1px solid #ddd;border-radius:0.5rem;padding:1rem;background:var(--toggle-bg,#f1f1f1);}
.kategorie-header{padding:0.75rem;background:var(--toggle-bg,#f1f1f1);color:var(--kategorie-weiss,#333);cursor:pointer;font-weight:normal;font-size:1rem;display:flex;align-items:center;font-family:var(--font-kategorie,var(--font-family,inherit));}
.kategorie-header.open{background:var(--toggle-active-bg,#e1e1e1);}
.kategorie-header .toggle-icon{margin-right:0.5rem;font-size:1.1rem;font-weight:bold;}
.kategorie-content{display:none;padding:1rem;}
.speisekarte-liste{font-size:0.98em;padding-left:1.1em;}
.speisekarte-liste li{margin-bottom:0.5em;line-height:1.3;}
.speisekarte-nummer,.speisekarte-titel{font-size:var(--item-font-size,1em);font-weight:var(--item-font-weight,bold);font-style:var(--item-font-style,normal);font-family:var(--font-speise,var(--item-font-family,var(--font-family,inherit)));color:var(--speisen-weiss,var(--zusatz-weiss,inherit));}
.speisekarte-preis{font-size:var(--item-font-size,0.98em);font-style:var(--item-font-style,italic);font-weight:var(--item-font-weight,bold);float:right;font-family:var(--font-preis,var(--item-font-family,var(--font-family,inherit)));color:var(--preis-weiss,var(--speisen-weiss,inherit));}
.speisekarte-beschreibung{font-size:var(--item-font-size,0.96em);margin-left:0.2em;font-weight:var(--item-font-weight,normal);font-style:var(--item-font-style,normal);font-family:var(--font-beschreibung,var(--item-font-family,var(--font-family,inherit)));color:var(--speisen-weiss,var(--zusatz-weiss,inherit));}
.speisekarte-inhalt{font-size:var(--item-font-size,0.93em);color:var(--zusatz-weiss,#888);font-style:var(--item-font-style,italic);margin-left:1.5em;font-weight:var(--item-font-weight,normal);font-family:var(--font-inhalt,var(--item-font-family,var(--font-family,inherit)));}
.speisekarte-inhalt-label{font-size:var(--item-font-size,0.93em);font-weight:var(--item-font-weight,bold);color:var(--zusatz-weiss,#888);margin-right:0.2em;font-style:var(--item-font-style,normal);font-family:var(--font-inhalt-label,var(--item-font-family,var(--font-family,inherit)));}
@media (prefers-color-scheme: dark){
  .speisekarte-kategorie{background:var(--toggle-bg,#1b3b6f);}
  .kategorie-header{background:var(--toggle-bg,#1b3b6f);color:var(--kategorie-dunkel,#ffd700);}
  .kategorie-header.open{background:var(--toggle-active-bg,#1e447c);}
  .kategorie-content,.speisekarte-nummer,.speisekarte-titel,.speisekarte-beschreibung{color:var(--speisen-dunkel,#fff);}
  .speisekarte-preis{color:var(--preis-dunkel,#ffd700);}
  .speisekarte-inhalt,.speisekarte-inhalt-label{color:var(--zusatz-dunkel,#dddddd);}
}
</style>
<div class="speisekarte-wrapper speisekarte-frontend">
  <div class="speisekarte-search">
    <input type="text" id="speisekarte_search" placeholder="Suche...">
  </div>
  <p class="speisekarte-hint">Klicke auf eine Kategorie, um die Speisen anzuzeigen.</p>
  <button type="button" id="speisekarte_toggle_all" class="toggle-all">Alle öffnen</button>
  <div class="speisekarte-grid">
<?php foreach ($kats as $kat): ?>
    <div class="speisekarte-kategorie" data-kat="<?php echo $kat->id; ?>">
      <div class="kategorie-header"><span class="toggle-icon">&#9654;</span><span class="kat-name"><?php echo esc_html($kat->name); ?></span></div>
      <div class="kategorie-content">
<?php
        $speisen = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_speise WHERE kategorie_id=%d ORDER BY sort, nr",
            $kat->id
        ));
        if ($speisen) :
            echo '<ul class="speisekarte-liste">';
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
        <li class="speise" data-nr="<?php echo esc_attr($sp->nr); ?>" data-name="<?php echo esc_attr($sp->name); ?>" data-beschreibung="<?php echo esc_attr($sp->beschreibung); ?>" data-inhaltsstoffe="<?php echo esc_attr($sp->inhaltsstoffe); ?>">
          <span class="speisekarte-nummer"><?php echo esc_html($sp->nr); ?></span>
          <span class="speisekarte-titel"><?php echo esc_html($sp->name); ?></span>
          <span class="speisekarte-preis">— <?php echo number_format($sp->preis, 2, ',', '.'); ?> €</span>
          <?php if ($sp->beschreibung) : ?>
          <div class="speisekarte-beschreibung"><?php echo esc_html($sp->beschreibung); ?></div>
          <?php endif; ?>
          <?php if ($inh_display) : ?>
          <div class="speisekarte-inhalt"><span class="speisekarte-inhalt-label">Inhaltsstoffe:</span> <?php echo esc_html($inh_display); ?></div>
          <?php endif; ?>
        </li>
<?php
            endforeach;
            echo '</ul>';
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
    if(icon) icon.textContent=open?'▼':'►';
    head.classList.toggle('open', open);
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
