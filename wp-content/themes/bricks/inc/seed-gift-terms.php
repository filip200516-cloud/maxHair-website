<?php
defined('ABSPATH') || exit;

/**
 * Vloží předdefinované termíny do gift_* taxonomií.
 * Volá se přes admin POST akci s noncem (viz admin stránka Filtry).
 */
function es_do_seed_terms(): void {
  // Audiences
  $audiences = ['Dámské','Pánské','Dětské','LGBT','Páry','Mazlíčci','Bez potisku','OUTLET %'];
  foreach ($audiences as $i=>$name){
    if (!term_exists($name,'gift_audience')) {
      $t = wp_insert_term($name,'gift_audience');
      if (!is_wp_error($t)) update_term_meta($t['term_id'],'order',$i);
    }
  }

  // Types
  $types = ['Oblečení','Produkty','Služby'];
  foreach ($types as $i=>$name){
    if (!term_exists($name,'gift_type')) {
      $t = wp_insert_term($name,'gift_type');
      if (!is_wp_error($t)) update_term_meta($t['term_id'],'order',$i);
    }
  }

  // Themes
  $themes = [
    'Dárky pro maminku','Dárky pro ségru','Dárky pro babičku','Dárky pro tátu','Dárky pro bráchu','Dárky pro dědu',
    'Dárky pro partnera','Dárky pro partnerku','Dárky pro přátele','Dárky pro učitele','Láska','Sport','Auta','Motorky',
    'Dětské','Hlášky','Humor','Hudba & Film','Grilování','Vodáci','Formule 1','Yoga a Fitness',
    'Rozlučka se svobodou','Rybáři','Cyklistická','Svatba','Politika','Koně','Pejskové','Kočičky',
    'Alkohol','Drogy','Hokej','Fotbal','Golf'
  ];
  foreach ($themes as $i=>$name){
    if (!term_exists($name,'gift_theme')) {
      $t = wp_insert_term($name,'gift_theme');
      if (!is_wp_error($t)) update_term_meta($t['term_id'],'order',$i);
    }
  }
}

/** Handler pro tlačítko "Seedovat" (admin stránka Filtry) */
add_action('admin_post_es_seed_filters', function () {
  if (!current_user_can('manage_options')) wp_die('Insufficient permissions.');
  check_admin_referer('es_filters_actions', 'es_nonce');
  es_do_seed_terms();
  wp_safe_redirect(add_query_arg(['page'=>'es-filters','seeded'=>'1'], admin_url('edit.php?post_type=product')));
  exit;
});
