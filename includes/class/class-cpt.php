<?php

class Auto_AktoPR_CPT {
    
    public function __construct() {
        add_action('init', [$this, 'register_post_types']);
    }
    
    public function register_post_types(): void {
        $this->register_klijent();
        $this->register_zaposleni();
        $this->register_master_dokument();
        $this->register_modul();
    }
    
    private function register_klijent(): void {
        $labels = [
            'name' => __('Klijenti', 'auto-aktopr'),
            'singular_name' => __('Klijent', 'auto-aktopr'),
            'menu_name' => __('Klijenti', 'auto-aktopr'),
            'name_admin_bar' => __('Klijent', 'auto-aktopr'),
            'add_new' => __('Dodaj novog', 'auto-aktopr'),
            'add_new_item' => __('Dodaj novog klijenta', 'auto-aktopr'),
            'edit_item' => __('Izmeni klijenta', 'auto-aktopr'),
            'new_item' => __('Novi klijent', 'auto-aktopr'),
            'view_item' => __('Pregledaj klijenta', 'auto-aktopr'),
            'search_items' => __('Pretraži klijente', 'auto-aktopr'),
            'not_found' => __('Nema klijenata', 'auto-aktopr'),
            'not_found_in_trash' => __('Nema u smeću', 'auto-aktopr'),
        ];
        
        $args = [
            'label' => __('Klijent', 'auto-aktopr'),
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'aapr-klijent'],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 20,
            'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'],
            'show_in_rest' => true,
        ];
        
        register_post_type('aapr_klijent', $args);
    }
    
    private function register_zaposleni(): void {
        $labels = [
            'name' => __('Zaposleni', 'auto-aktopr'),
            'singular_name' => __('Zaposleni', 'auto-aktopr'),
            'menu_name' => __('Zaposleni', 'auto-aktopr'),
            'name_admin_bar' => __('Zaposleni', 'auto-aktopr'),
            'add_new' => __('Dodaj novog', 'auto-aktopr'),
            'add_new_item' => __('Dodaj novog zaposlenog', 'auto-aktopr'),
            'edit_item' => __('Izmeni zaposlenog', 'auto-aktopr'),
            'new_item' => __('Novi zaposleni', 'auto-aktopr'),
            'view_item' => __('Pregledaj zaposlenog', 'auto-aktopr'),
            'search_items' => __('Pretraži zaposlene', 'auto-aktopr'),
            'not_found' => __('Nema zaposlenih', 'auto-aktopr'),
            'not_found_in_trash' => __('Nema u smeću', 'auto-aktopr'),
        ];
        
        $args = [
            'label' => __('Zaposleni', 'auto-aktopr'),
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'aapr-zaposleni'],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => true,
            'menu_position' => 21,
            'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes'],
            'show_in_rest' => true,
        ];
        
        register_post_type('aapr_zaposleni', $args);
    }
    
    private function register_master_dokument(): void {
        $labels = [
            'name' => __('Master dokumenti', 'auto-aktopr'),
            'singular_name' => __('Master dokument', 'auto-aktopr'),
            'menu_name' => __('Master dokumenti', 'auto-aktopr'),
            'name_admin_bar' => __('Master dokument', 'auto-aktopr'),
            'add_new' => __('Dodaj novi', 'auto-aktopr'),
            'add_new_item' => __('Dodaj novi master dokument', 'auto-aktopr'),
            'edit_item' => __('Izmeni master dokument', 'auto-aktopr'),
            'new_item' => __('Novi master dokument', 'auto-aktopr'),
            'view_item' => __('Pregledaj master dokument', 'auto-aktopr'),
            'search_items' => __('Pretraži master dokumente', 'auto-aktopr'),
            'not_found' => __('Nema master dokumenata', 'auto-aktopr'),
            'not_found_in_trash' => __('Nema u smeću', 'auto-aktopr'),
        ];
        
        $args = [
            'label' => __('Master dokument', 'auto-aktopr'),
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'aapr-master-dokument'],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => true,
            'menu_position' => 22,
            'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes', 'revisions'],
            'show_in_rest' => true,
        ];
        
        register_post_type('aapr_master_dokument', $args);
    }
    
    private function register_modul(): void {
        $labels = [
            'name' => __('Moduli', 'auto-aktopr'),
            'singular_name' => __('Modul', 'auto-aktopr'),
            'menu_name' => __('Moduli', 'auto-aktopr'),
            'name_admin_bar' => __('Modul', 'auto-aktopr'),
            'add_new' => __('Dodaj novi', 'auto-aktopr'),
            'add_new_item' => __('Dodaj novi modul', 'auto-aktopr'),
            'edit_item' => __('Izmeni modul', 'auto-aktopr'),
            'new_item' => __('Novi modul', 'auto-aktopr'),
            'view_item' => __('Pregledaj modul', 'auto-aktopr'),
            'search_items' => __('Pretraži module', 'auto-aktopr'),
            'not_found' => __('Nema modula', 'auto-aktopr'),
            'not_found_in_trash' => __('Nema u smeću', 'auto-aktopr'),
        ];
        
        $args = [
            'label' => __('Modul', 'auto-aktopr'),
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'aapr-modul'],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => true,
            'menu_position' => 23,
            'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes'],
            'show_in_rest' => true,
        ];
        
        register_post_type('aapr_modul', $args);
    }
}
