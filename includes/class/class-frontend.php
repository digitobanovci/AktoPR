<?php

class Auto_AktoPR_Frontend {

    public function __construct() {
        add_shortcode('auto_aktopr', [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
    }

    public function enqueue_frontend_assets(): void {
        if (!is_singular()) return;
        
        wp_enqueue_style(
            'auto-aktopr-frontend',
            AUTO_AKTOPR_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            AUTO_AKTOPR_VERSION
        );
        
        wp_enqueue_script(
            'auto-aktopr-frontend',
            AUTO_AKTOPR_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            AUTO_AKTOPR_VERSION,
            true
        );
        
        wp_localize_script('auto-aktopr-frontend', 'aaprFE', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aapr_frontend_nonce'),
            'strings' => [
                'next' => __('Dalje', 'auto-aktopr'),
                'prev' => __('Nazad', 'auto-aktopr'),
                'save' => __('Sačuvaj', 'auto-aktopr'),
                'loading' => __('Učitavanje...', 'auto-aktopr'),
            ]
        ]);
    }

    public function render_shortcode($atts): string {
        $atts = shortcode_atts([
            'dokument_id' => 0,
            'klijent_id' => 0,
            'koraci' => '1,2,3,4,5,6,7,8,9,10',
        ], $atts);
        
        ob_start();
        include AUTO_AKTOPR_PLUGIN_DIR . 'frontend/templates/wizard.php';
        return ob_get_clean();
    }

    public function render_wizard_page($dokument_id = 0, $klijent_id = 0): void {
        $this->enqueue_frontend_assets();
        include AUTO_AKTOPR_PLUGIN_DIR . 'frontend/templates/wizard.php';
    }

    public static function ajax_save_sekcija(): void {
        check_ajax_referer('aapr_frontend_nonce');
        
        $dokument_id = (int) ($_POST['dokument_id'] ?? 0);
        $sekcija = sanitize_text_field($_POST['sekcija'] ?? '');
        $podaci = $_POST['data'] ?? [];
        
        if ($dokument_id === 0) {
            wp_send_json_error(['message' => 'Nevažeći dokument']);
        }
        
        $sekcija_key = 'aapr_sekcija_' . str_replace('.', '_', $sekcija);
        update_post_meta($dokument_id, $sekcija_key, $podaci);
        
        wp_send_json_success(['message' => 'Sačuvano']);
    }

    public static function ajax_get_sekcija(): void {
        check_ajax_referer('aapr_frontend_nonce');
        
        $dokument_id = (int) ($_POST['dokument_id'] ?? 0);
        $sekcija = sanitize_text_field($_POST['sekcija'] ?? '');
        
        $sekcija_key = 'aapr_sekcija_' . str_replace('.', '_', $sekcija);
        $podaci = get_post_meta($dokument_id, $sekcija_key, true);
        
        wp_send_json_success(['data' => $podaci ?: []]);
    }

    public static function ajax_izracunaj_rizik(): void {
        check_ajax_referer('aapr_frontend_nonce');
        
        $verovatnoca = (float) ($_POST['verovatnoca'] ?? 1);
        $ucestalost = (float) ($_POST['ucestalost'] ?? 1);
        $tezina = (float) ($_POST['tezina'] ?? 1);
        
        $rizik = $verovatnoca * $ucestalost * $tezina;
        
        $nivo = 'zanemarljiv';
        $boja = '#22c55e';
        if ($rizik >= 100) {
            $nivo = 'kritičan';
            $boja = '#dc2626';
        } elseif ($rizik >= 40) {
            $nivo = 'visok';
            $boja = '#f97316';
        } elseif ($rizik >= 10) {
            $nivo = 'srednji';
            $boja = '#eab308';
        } elseif ($rizik >= 1) {
            $nivo = 'nizak';
            $boja = '#22c55e';
        }
        
        wp_send_json_success([
            'rizik' => round($rizik, 2),
            'nivo' => $nivo,
            'boja' => $boja,
            'formula' => sprintf('%.1f × %.1f × %.1f = %.2f', $verovatnoca, $ucestalost, $tezina, $rizik),
        ]);
    }

    public static function ajax_ai_generisi(): void {
        check_ajax_referer('aapr_frontend_nonce');
        
        $tip = sanitize_text_field($_POST['tip'] ?? '');
        $kontekst = wp_kses_post($_POST['kontekst'] ?? '');
        
        $api_keys = get_option('aapr_api_keys', []);
        $openai_key = $api_keys['openai'] ?? '';
        
        if (empty($openai_key)) {
            wp_send_json_error(['message' => 'AI nije konfigurisan']);
        }
        
        $system_prompt = 'Ti si stručnjak za bezbednost i zdravlje na radu u Srbiji. Pišeš tekst za Akt o proceni rizika prema Pravilniku o načinu i postupku procene rizika (Sl. glasnik RS br. 76/2024). Odgovor treba da bude u HTML formatu sa paragrafima, bez liste.';
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $openai_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'model' => 'gpt-4-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $system_prompt],
                    ['role' => 'user', 'content' => $kontekst],
                ],
                'max_tokens' => 2000,
                'temperature' => 0.7,
            ]),
            'timeout' => 60,
        ]);
        
        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $tekst = $body['choices'][0]['message']['content'] ?? '';
        
        wp_send_json_success(['tekst' => $tekst]);
    }
}

new Auto_AktoPR_Frontend();

add_action('wp_ajax_aapr_save_sekcija', ['Auto_AktoPR_Frontend', 'ajax_save_sekcija']);
add_action('wp_ajax_nopriv_aapr_save_sekcija', ['Auto_AktoPR_Frontend', 'ajax_save_sekcija']);
add_action('wp_ajax_aapr_get_sekcija', ['Auto_AktoPR_Frontend', 'ajax_get_sekcija']);
add_action('wp_ajax_nopriv_aapr_get_sekcija', ['Auto_AktoPR_Frontend', 'ajax_get_sekcija']);
add_action('wp_ajax_aapr_izracunaj_rizik', ['Auto_AktoPR_Frontend', 'ajax_izracunaj_rizik']);
add_action('wp_ajax_nopriv_aapr_izracunaj_rizik', ['Auto_AktoPR_Frontend', 'ajax_izracunaj_rizik']);
add_action('wp_ajax_aapr_ai_generisi', ['Auto_AktoPR_Frontend', 'ajax_ai_generisi']);
add_action('wp_ajax_nopriv_aapr_ai_generisi', ['Auto_AktoPR_Frontend', 'ajax_ai_generisi']);
