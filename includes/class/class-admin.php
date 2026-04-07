<?php

class Auto_AktoPR_Admin {

    private string $plugin_screen_hook_suffix;

    public function __construct() {
        add_action('admin_menu', [$this, 'add_plugin_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_filter('admin_footer_text', [$this, 'admin_footer_text']);
    }

    public function add_plugin_admin_menu(): void {
        $this->plugin_screen_hook_suffix = add_menu_page(
            'Auto Akt oPR',
            'Akt oPR',
            'manage_options',
            'auto-aktopr',
            [$this, 'display_admin_page'],
            'dashicons-shield-alt',
            30
        );
        
        add_submenu_page(
            'auto-aktopr',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'auto-aktopr',
            [$this, 'display_admin_page']
        );
        
        add_submenu_page(
            'auto-aktopr',
            'Klijenti',
            'Klijenti',
            'edit_aapr_klijent',
            'auto-aktopr-klijenti',
            [$this, 'display_klijenti_page']
        );
        
        add_submenu_page(
            'auto-aktopr',
            'Zaposleni',
            'Zaposleni',
            'edit_aapr_zaposleni',
            'auto-aktopr-zaposleni',
            [$this, 'display_zaposleni_page']
        );
        
        add_submenu_page(
            'auto-aktopr',
            'Dokumenti',
            'Dokumenti',
            'edit_aapr_master_dokument',
            'auto-aktopr-dokumenti',
            [$this, 'display_dokumenti_page']
        );
        
        add_submenu_page(
            'auto-aktopr',
            'Biblioteka',
            'Biblioteka',
            'manage_options',
            'auto-aktopr-biblioteka',
            [$this, 'display_biblioteka_page']
        );
        
        add_submenu_page(
            'auto-aktopr',
            'Podešavanja',
            'Podešavanja',
            'manage_options',
            'auto-aktopr-podesavanja',
            [$this, 'display_podesavanja_page']
        );
    }

    public function enqueue_styles(): void {
        wp_enqueue_style(
            'auto-aktopr-admin',
            AUTO_AKTOPR_PLUGIN_URL . 'assets/css/admin.css',
            [],
            AUTO_AKTOPR_VERSION
        );
    }

    public function enqueue_scripts(string $hook): void {
        if (strpos($hook, 'auto-aktopr') === false) {
            return;
        }
        
        wp_enqueue_style('wp-jquery-ui-dialog');
        
        wp_enqueue_script(
            'auto-aktopr-admin',
            AUTO_AKTOPR_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery', 'jquery-ui-core', 'jquery-ui-dialog'],
            AUTO_AKTOPR_VERSION,
            true
        );
        
        wp_localize_script('auto-aktopr-admin', 'aaprAdmin', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aapr_nonce'),
            'strings' => [
                'save' => __('Sačuvaj', 'auto-aktopr'),
                'cancel' => __('Otkaži', 'auto-aktopr'),
                'delete' => __('Obriši', 'auto-aktopr'),
                'confirm_delete' => __('Da li ste sigurni?', 'auto-aktopr'),
                'loading' => __('Učitavanje...', 'auto-aktopr'),
                'success' => __('Uspešno!', 'auto-aktopr'),
                'error' => __('Greška!', 'auto-aktopr'),
            ]
        ]);
    }

    public function display_admin_page(): void {
        $stats = $this->get_dashboard_stats();
        include AUTO_AKTOPR_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    public function display_klijenti_page(): void {
        $klijenti = $this->get_klijenti();
        include AUTO_AKTOPR_PLUGIN_DIR . 'admin/views/klijenti.php';
    }

    public function display_zaposleni_page(): void {
        $klijenti = $this->get_klijenti_for_select();
        $zaposleni = $this->get_zaposleni();
        include AUTO_AKTOPR_PLUGIN_DIR . 'admin/views/zaposleni.php';
    }

    public function display_dokumenti_page(): void {
        $dokumenti = $this->get_master_dokumenti();
        include AUTO_AKTOPR_PLUGIN_DIR . 'admin/views/dokumenti.php';
    }

    public function display_biblioteka_page(): void {
        $tab = sanitize_text_field($_GET['tab'] ?? 'propisi');
        $propisi = Auto_AktoPR_Database::get_propisi();
        $koeficijenti = Auto_AktoPR_Database::get_koeficijenti();
        $tekst_blokovi = Auto_AktoPR_Database::get_tekst_blokovi();
        $standardne_mere = Auto_AktoPR_Database::get_standardne_mere();
        include AUTO_AKTOPR_PLUGIN_DIR . 'admin/views/biblioteka.php';
    }

    public function display_podesavanja_page(): void {
        $api_keys = get_option('aapr_api_keys', []);
        $settings = get_option('aapr_settings', []);
        include AUTO_AKTOPR_PLUGIN_DIR . 'admin/views/podesavanja.php';
    }

    public function admin_footer_text(string $text): string {
        return 'Auto Akt oPR v' . AUTO_AKTOPR_VERSION . ' | Powered by Digito';
    }

    private function get_dashboard_stats(): array {
        $klijenata = wp_count_posts('aapr_klijent');
        $zaposlenih = wp_count_posts('aapr_zaposleni');
        $dokumenata = wp_count_posts('aapr_master_dokument');
        
        global $wpdb;
        $neobradjeni_risici = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}aapr_risici WHERE nivo_rizika IN ('visok','kritican')"
        );
        
        return [
            'klijenata' => $klijenata->publish ?? 0,
            'zaposlenih' => $zaposlenih->publish ?? 0,
            'dokumenata' => $dokumenata->publish ?? 0,
            'neobradjeni_risici' => (int) $neobradjeni_risici,
        ];
    }

    private function get_klijenti(): array {
        $args = [
            'post_type' => 'aapr_klijent',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ];
        return get_posts($args);
    }

    private function get_klijenti_for_select(): array {
        $klijenti = $this->get_klijenti();
        $options = ['' => '-- Izaberi klijenta --'];
        foreach ($klijenti as $k) {
            $options[$k->ID] = $k->post_title;
        }
        return $options;
    }

    private function get_zaposleni(): array {
        $args = [
            'post_type' => 'aapr_zaposleni',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ];
        return get_posts($args);
    }

    private function get_master_dokumenti(): array {
        $args = [
            'post_type' => 'aapr_master_dokument',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        return get_posts($args);
    }
}
