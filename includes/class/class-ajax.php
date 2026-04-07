<?php

class Auto_AktoPR_Ajax {

    public function __construct() {
        add_action('wp_ajax_aapr_save_klijent', [$this, 'save_klijent']);
        add_action('wp_ajax_aapr_delete_klijent', [$this, 'delete_klijent']);
        add_action('wp_ajax_aapr_get_klijent', [$this, 'get_klijent']);
        
        add_action('wp_ajax_aapr_save_zaposleni', [$this, 'save_zaposleni']);
        add_action('wp_ajax_aapr_delete_zaposleni', [$this, 'delete_zaposleni']);
        add_action('wp_ajax_aapr_get_zaposleni', [$this, 'get_zaposleni']);
        
        add_action('wp_ajax_aapr_save_propis', [$this, 'save_propis']);
        add_action('wp_ajax_aapr_delete_propis', [$this, 'delete_propis']);
        
        add_action('wp_ajax_aapr_save_koeficijent', [$this, 'save_koeficijent']);
        add_action('wp_ajax_aapr_delete_koeficijent', [$this, 'delete_koeficijent']);
        
        add_action('wp_ajax_aapr_save_tekst_blok', [$this, 'save_tekst_blok']);
        add_action('wp_ajax_aapr_delete_tekst_blok', [$this, 'delete_tekst_blok']);
        
        add_action('wp_ajax_aapr_save_standardna_mera', [$this, 'save_standardna_mera']);
        add_action('wp_ajax_aapr_delete_standardna_mera', [$this, 'delete_standardna_mera']);
        
        add_action('wp_ajax_aapr_save_api_keys', [$this, 'save_api_keys']);
        add_action('wp_ajax_aapr_save_settings', [$this, 'save_settings']);
        
        add_action('wp_ajax_aapr_izracunaj_rizik', [$this, 'izracunaj_rizik']);
        add_action('wp_ajax_aapr_ai_generisi', [$this, 'ai_generisi']);
    }

    private function verify_nonce(): void {
        if (!current_user_can('manage_options') && !current_user_can('edit_aapr_klijent')) {
            wp_die(__('Nemate dozvolu za ovu akciju.', 'auto-aktopr'));
        }
        
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'aapr_nonce')) {
            wp_die(__('Nevažeći nonce token.', 'auto-aktopr'));
        }
    }

    private function json_response($data, int $status = 200): void {
        wp_send_json($data, $status);
    }

    public function save_klijent(): void {
        $this->verify_nonce();
        
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $data = [
            'post_title' => sanitize_text_field($_POST['naziv'] ?? ''),
            'post_type' => 'aapr_klijent',
            'post_status' => 'publish',
        ];
        
        if ($id > 0) {
            $data['ID'] = $id;
            $post_id = wp_update_post($data);
        } else {
            $data['post_author'] = get_current_user_id();
            $post_id = wp_insert_post($data);
        }
        
        if (is_wp_error($post_id)) {
            $this->json_response(['success' => false, 'message' => $post_id->get_error_message()], 400);
        }
        
        $meta_fields = ['pib', 'adresa', 'telefon', 'email', 'delatnost', 'broj_zaposlenih', 'napomene'];
        foreach ($meta_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, 'aapr_' . $field, sanitize_text_field($_POST[$field]));
            }
        }
        
        Auto_AktoPR_Database::log_action('save', 'klijent', $post_id, null, $data);
        
        $this->json_response([
            'success' => true,
            'message' => __('Klijent uspešno sačuvan.', 'auto-aktopr'),
            'id' => $post_id,
        ]);
    }

    public function delete_klijent(): void {
        $this->verify_nonce();
        
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === 0) {
            $this->json_response(['success' => false, 'message' => __('Nevažeći ID.'), 'auto-aktopr'], 400);
        }
        
        $result = wp_delete_post($id, true);
        if ($result === false) {
            $this->json_response(['success' => false, 'message' => __('Greška pri brisanju.'), 'auto-aktopr'], 400);
        }
        
        Auto_AktoPR_Database::log_action('delete', 'klijent', $id);
        
        $this->json_response([
            'success' => true,
            'message' => __('Klijent uspešno obrisan.', 'auto-aktopr'),
        ]);
    }

    public function get_klijent(): void {
        $this->verify_nonce();
        
        $id = (int) ($_POST['id'] ?? 0);
        $post = get_post($id);
        
        if (!$post || $post->post_type !== 'aapr_klijent') {
            $this->json_response(['success' => false, 'message' => __('Klijent nije pronađen.')], 404);
        }
        
        $data = [
            'id' => $post->ID,
            'naziv' => $post->post_title,
        ];
        
        $meta_fields = ['pib', 'adresa', 'telefon', 'email', 'delatnost', 'broj_zaposlenih', 'napomene'];
        foreach ($meta_fields as $field) {
            $data[$field] = get_post_meta($post->ID, 'aapr_' . $field, true);
        }
        
        $this->json_response(['success' => true, 'data' => $data]);
    }

    public function save_zaposleni(): void {
        $this->verify_nonce();
        
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $data = [
            'post_title' => sanitize_text_field($_POST['ime_prezime'] ?? ''),
            'post_type' => 'aapr_zaposleni',
            'post_status' => 'publish',
        ];
        
        if (isset($_POST['klijent_id']) && (int) $_POST['klijent_id'] > 0) {
            $data['post_parent'] = (int) $_POST['klijent_id'];
        }
        
        if ($id > 0) {
            $data['ID'] = $id;
            $post_id = wp_update_post($data);
        } else {
            $data['post_author'] = get_current_user_id();
            $post_id = wp_insert_post($data);
        }
        
        if (is_wp_error($post_id)) {
            $this->json_response(['success' => false, 'message' => $post_id->get_error_message()], 400);
        }
        
        $meta_fields = ['jmbg', 'jmbg', 'radno_mesto', 'smenski_rad', 'nocni_rad', 'terenski_rad', 'datum_zaposlenja', 'zdravstveni_uslovi', 'napomene'];
        foreach ($meta_fields as $field) {
            if (isset($_POST[$field])) {
                if (in_array($field, ['smenski_rad', 'nocni_rad', 'terenski_rad'])) {
                    update_post_meta($post_id, 'aapr_' . $field, (int) $_POST[$field]);
                } else {
                    update_post_meta($post_id, 'aapr_' . $field, sanitize_text_field($_POST[$field]));
                }
            }
        }
        
        Auto_AktoPR_Database::log_action('save', 'zaposleni', $post_id);
        
        $this->json_response([
            'success' => true,
            'message' => __('Zaposleni uspešno sačuvan.', 'auto-aktopr'),
            'id' => $post_id,
        ]);
    }

    public function delete_zaposleni(): void {
        $this->verify_nonce();
        
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === 0) {
            $this->json_response(['success' => false, 'message' => __('Nevažeći ID.')], 400);
        }
        
        $result = wp_delete_post($id, true);
        if ($result === false) {
            $this->json_response(['success' => false, 'message' => __('Greška pri brisanju.')], 400);
        }
        
        Auto_AktoPR_Database::log_action('delete', 'zaposleni', $id);
        
        $this->json_response([
            'success' => true,
            'message' => __('Zaposleni uspešno obrisan.', 'auto-aktopr'),
        ]);
    }

    public function get_zaposleni(): void {
        $this->verify_nonce();
        
        $id = (int) ($_POST['id'] ?? 0);
        $post = get_post($id);
        
        if (!$post || $post->post_type !== 'aapr_zaposleni') {
            $this->json_response(['success' => false, 'message' => __('Zaposleni nije pronađen.')], 404);
        }
        
        $data = [
            'id' => $post->ID,
            'ime_prezime' => $post->post_title,
            'klijent_id' => $post->post_parent,
        ];
        
        $meta_fields = ['jmbg', 'radno_mesto', 'smenski_rad', 'nocni_rad', 'terenski_rad', 'datum_zaposlenja', 'zdravstveni_uslovi', 'napomene'];
        foreach ($meta_fields as $field) {
            $data[$field] = get_post_meta($post->ID, 'aapr_' . $field, true);
        }
        
        $this->json_response(['success' => true, 'data' => $data]);
    }

    public function save_propis(): void {
        $this->verify_nonce();
        
        global $wpdb;
        $table = $wpdb->prefix . 'aapr_propisi';
        
        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'sifra' => sanitize_text_field($_POST['sifra'] ?? ''),
            'naziv' => sanitize_textarea_field($_POST['naziv'] ?? ''),
            'vrsta' => sanitize_text_field($_POST['vrsta'] ?? 'zakon'),
            'link' => esc_url_raw($_POST['link'] ?? ''),
            'clanovi' => sanitize_textarea_field($_POST['clanovi'] ?? ''),
            'datum_objave' => sanitize_text_field($_POST['datum_objave'] ?? ''),
            'napomene' => sanitize_textarea_field($_POST['napomene'] ?? ''),
        ];
        
        if ($id > 0) {
            $wpdb->update($table, $data, ['id' => $id]);
            $propis_id = $id;
        } else {
            $data['created_by'] = get_current_user_id();
            $wpdb->insert($table, $data);
            $propis_id = $wpdb->insert_id;
        }
        
        Auto_AktoPR_Database::log_action('save', 'propis', $propis_id);
        
        $this->json_response([
            'success' => true,
            'message' => __('Propis uspešno sačuvan.', 'auto-aktopr'),
            'id' => $propis_id,
        ]);
    }

    public function delete_propis(): void {
        $this->verify_nonce();
        
        global $wpdb;
        $id = (int) ($_POST['id'] ?? 0);
        
        $wpdb->delete($wpdb->prefix . 'aapr_propisi', ['id' => $id]);
        Auto_AktoPR_Database::log_action('delete', 'propis', $id);
        
        $this->json_response(['success' => true, 'message' => __('Propis obrisan.')]);
    }

    public function save_koeficijent(): void {
        $this->verify_nonce();
        
        global $wpdb;
        $table = $wpdb->prefix . 'aapr_koeficijenti';
        
        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'naziv' => sanitize_text_field($_POST['naziv'] ?? ''),
            'tip' => sanitize_text_field($_POST['tip'] ?? 'kinney'),
            'kategorija' => sanitize_text_field($_POST['kategorija'] ?? ''),
            'vrednost_min' => (float) ($_POST['vrednost_min'] ?? 0),
            'vrednost_max' => (float) ($_POST['vrednost_max'] ?? 0),
            'opis' => sanitize_textarea_field($_POST['opis'] ?? ''),
            'boja' => sanitize_hex_color($_POST['boja'] ?? '#000000'),
        ];
        
        if ($id > 0) {
            $wpdb->update($table, $data, ['id' => $id]);
        } else {
            $data['redosled'] = $wpdb->get_var("SELECT MAX(redosled) FROM $table") + 1;
            $wpdb->insert($table, $data);
        }
        
        $this->json_response(['success' => true, 'message' => __('Koeficijent sačuvan.')]);
    }

    public function delete_koeficijent(): void {
        $this->verify_nonce();
        
        global $wpdb;
        $id = (int) ($_POST['id'] ?? 0);
        
        $wpdb->update(
            $wpdb->prefix . 'aapr_koeficijenti',
            ['aktivan' => 0],
            ['id' => $id]
        );
        
        $this->json_response(['success' => true, 'message' => __('Koeficijent obrisan.')]);
    }

    public function save_tekst_blok(): void {
        $this->verify_nonce();
        
        global $wpdb;
        $table = $wpdb->prefix . 'aapr_tekst_blokovi';
        
        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'sifra' => sanitize_text_field($_POST['sifra'] ?? ''),
            'naslov' => sanitize_text_field($_POST['naslov'] ?? ''),
            'sadrzaj' => wp_kses_post($_POST['sadrzaj'] ?? ''),
            'sekcija' => sanitize_text_field($_POST['sekcija'] ?? ''),
            'tip_uslova' => sanitize_text_field($_POST['tip_uslova'] ?? ''),
            'prioritet' => (int) ($_POST['prioritet'] ?? 0),
        ];
        
        if ($id > 0) {
            $wpdb->update($table, $data, ['id' => $id]);
        } else {
            $wpdb->insert($table, $data);
        }
        
        $this->json_response(['success' => true, 'message' => __('Tekst blok sačuvan.')]);
    }

    public function delete_tekst_blok(): void {
        $this->verify_nonce();
        
        global $wpdb;
        $id = (int) ($_POST['id'] ?? 0);
        
        $wpdb->update(
            $wpdb->prefix . 'aapr_tekst_blokovi',
            ['aktivan' => 0],
            ['id' => $id]
        );
        
        $this->json_response(['success' => true, 'message' => __('Tekst blok obrisan.')]);
    }

    public function save_standardna_mera(): void {
        $this->verify_nonce();
        
        global $wpdb;
        $table = $wpdb->prefix . 'aapr_standardne_mere';
        
        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'sifra' => sanitize_text_field($_POST['sifra'] ?? ''),
            'naziv' => sanitize_text_field($_POST['naziv'] ?? ''),
            'opis' => sanitize_textarea_field($_POST['opis'] ?? ''),
            'tip' => sanitize_text_field($_POST['tip'] ?? 'tehnicka'),
            'prioritet' => sanitize_text_field($_POST['prioritet'] ?? 'srednji'),
            'rok_dana' => (int) ($_POST['rok_dana'] ?? 30),
            'grupa_opasnosti' => sanitize_text_field($_POST['grupa_opasnosti'] ?? ''),
        ];
        
        if ($id > 0) {
            $wpdb->update($table, $data, ['id' => $id]);
        } else {
            $wpdb->insert($table, $data);
        }
        
        $this->json_response(['success' => true, 'message' => __('Standardna mera sačuvana.')]);
    }

    public function delete_standardna_mera(): void {
        $this->verify_nonce();
        
        global $wpdb;
        $id = (int) ($_POST['id'] ?? 0);
        
        $wpdb->update(
            $wpdb->prefix . 'aapr_standardne_mere',
            ['aktivan' => 0],
            ['id' => $id]
        );
        
        $this->json_response(['success' => true, 'message' => __('Standardna mera obrisana.')]);
    }

    public function save_api_keys(): void {
        $this->verify_nonce();
        
        $api_keys = [
            'openai' => sanitize_text_field($_POST['openai_key'] ?? ''),
            'anthropic' => sanitize_text_field($_POST['anthropic_key'] ?? ''),
            'xai' => sanitize_text_field($_POST['xai_key'] ?? ''),
            'ollama_url' => esc_url_raw($_POST['ollama_url'] ?? 'http://localhost:11434'),
        ];
        
        update_option('aapr_api_keys', $api_keys);
        
        $this->json_response([
            'success' => true,
            'message' => __('API ključevi sačuvani.', 'auto-aktopr'),
        ]);
    }

    public function save_settings(): void {
        $this->verify_nonce();
        
        $settings = [
            'default_tip_izracunavanja' => sanitize_text_field($_POST['default_tip'] ?? 'kinney'),
            'default_firma_naziv' => sanitize_text_field($_POST['default_firma'] ?? ''),
            'default_firma_adresa' => sanitize_text_field($_POST['default_adresa'] ?? ''),
            'obavestenja_email' => sanitize_email($_POST['obavestenja_email'] ?? ''),
            'auto_save' => (int) isset($_POST['auto_save']),
        ];
        
        update_option('aapr_settings', $settings);
        
        $this->json_response([
            'success' => true,
            'message' => __('Podešavanja sačuvana.', 'auto-aktopr'),
        ]);
    }

    public function izracunaj_rizik(): void {
        $this->verify_nonce();
        
        $verovatnoca = (float) ($_POST['verovatnoca'] ?? 1);
        $ucestalost = (float) ($_POST['ucestalost'] ?? 1);
        $tezina = (float) ($_POST['tezina'] ?? 1);
        
        $rizik = $verovatnoca * $ucestalost * $tezina;
        
        $nivo = 'zanemarljiv';
        if ($rizik >= 100) {
            $nivo = 'kritican';
        } elseif ($rizik >= 40) {
            $nivo = 'visok';
        } elseif ($rizik >= 10) {
            $nivo = 'srednji';
        } elseif ($rizik >= 1) {
            $nivo = 'nizak';
        }
        
        $this->json_response([
            'success' => true,
            'rizik' => round($rizik, 2),
            'nivo_rizika' => $nivo,
        ]);
    }

    public function ai_generisi(): void {
        $this->verify_nonce();
        
        $tip = sanitize_text_field($_POST['tip'] ?? '');
        $kontekst = wp_kses_post($_POST['kontekst'] ?? '');
        
        $api_keys = get_option('aapr_api_keys', []);
        $default_key = $api_keys['openai'] ?? '';
        
        if (empty($default_key)) {
            $this->json_response([
                'success' => false,
                'message' => __('AI nije konfigurisan. Unesite API ključ u Podešavanja.'),
            ], 400);
        }
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $default_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Ti si stručnjak za bezbednost i zdravlje na radu u Srbiji. Pišeš tekst za Akt o proceni rizika prema Pravilniku o načinu i postupku procene rizika (Sl. glasnik RS br. 76/2024).',
                    ],
                    [
                        'role' => 'user',
                        'content' => $kontekst,
                    ],
                ],
                'max_tokens' => 2000,
                'temperature' => 0.7,
            ]),
            'timeout' => 60,
        ]);
        
        if (is_wp_error($response)) {
            $this->json_response([
                'success' => false,
                'message' => $response->get_error_message(),
            ], 500);
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $tekst = $body['choices'][0]['message']['content'] ?? '';
        
        $this->json_response([
            'success' => true,
            'tekst' => $tekst,
        ]);
    }
}
