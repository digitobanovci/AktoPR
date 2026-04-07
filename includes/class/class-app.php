<?php

class Auto_AktoPR_App {

    private array $modules = [
        1 => ['name' => 'Osnovni podaci', 'sections' => '1.1-1.4', 'icon' => '📋'],
        2 => ['name' => 'Radni proces', 'sections' => '2.1-2.4', 'icon' => '⚙️'],
        3 => ['name' => 'Radna mesta', 'sections' => '3.1-3.2.5', 'icon' => '🏭'],
        4 => ['name' => 'Opasnosti', 'sections' => '4.1-4.4', 'icon' => '⚠️'],
        5 => ['name' => 'Procena rizika', 'sections' => '5.1-5.3', 'icon' => '📊'],
        6 => ['name' => 'Mere zaštite', 'sections' => '6.1-6.4', 'icon' => '🛡️'],
        7 => ['name' => 'Izveštaji', 'sections' => '7.1-7.4', 'icon' => '📄'],
        8 => ['name' => 'Zaključak', 'sections' => '8.1-8.2', 'icon' => '✅'],
        9 => ['name' => 'Prilozi', 'sections' => '9.1-9.4', 'icon' => '📎'],
        10 => ['name' => 'Izmene', 'sections' => '10.1-10.2', 'icon' => '📝'],
    ];

    public function __construct() {
        add_shortcode('aktopr_dashboard', [$this, 'render_app']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_aktopr_save_module', [$this, 'ajax_save_module']);
        add_action('wp_ajax_aktopr_get_module', [$this, 'ajax_get_module']);
        add_action('wp_ajax_aktopr_generate_document', [$this, 'ajax_generate_document']);
        add_action('wp_ajax_aktopr_ai_generate', [$this, 'ajax_ai_generate']);
        add_action('wp_ajax_aktopr_save_zaposleni', [$this, 'ajax_save_zaposleni']);
        add_action('wp_ajax_aktopr_delete_zaposleni', [$this, 'ajax_delete_zaposleni']);
        add_action('wp_ajax_aktopr_get_zaposleni', [$this, 'ajax_get_zaposleni']);
        add_action('wp_ajax_aktopr_save_radno_mesto', [$this, 'ajax_save_radno_mesto']);
        add_action('wp_ajax_aktopr_get_radno_mesto', [$this, 'ajax_get_radno_mesto']);
        add_action('wp_ajax_aktopr_delete_radno_mesto', [$this, 'ajax_delete_radno_mesto']);
        add_action('wp_ajax_aktopr_get_klijent', [$this, 'ajax_get_klijent']);
        add_action('wp_ajax_aktopr_get_svi_klijenti', [$this, 'ajax_get_svi_klijenti']);
        add_action('wp_ajax_aktopr_kreiraj_novi_akt', [$this, 'ajax_kreiraj_novi_akt']);
        add_action('wp_ajax_aktopr_get_aktivi', [$this, 'ajax_get_aktivi']);
        add_action('wp_ajax_aktopr_save_klijent', [$this, 'ajax_save_klijent']);
    }

    public function enqueue_assets(): void {
        if (!is_singular() && !is_page()) return;
        
        wp_enqueue_style('aktopr-app', AUTO_AKTOPR_PLUGIN_URL . 'assets/css/app.css', [], AUTO_AKTOPR_VERSION);
        wp_enqueue_script('aktopr-app', AUTO_AKTOPR_PLUGIN_URL . 'assets/js/app.js', ['jquery'], AUTO_AKTOPR_VERSION, true);
        
        wp_localize_script('aktopr-app', 'aktoprData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aktopr_nonce'),
            'modules' => $this->modules,
            'strings' => [
                'save' => __('Sačuvaj', 'aktopr'),
                'saved' => __('Sačuvano!', 'aktopr'),
                'generate' => __('Generiši', 'aktopr'),
                'regenerate' => __('Regeneriši', 'aktopr'),
                'loading' => __('Učitavanje...', 'aktopr'),
                'ai_generate' => __('AI Generiši', 'aktopr'),
                'next' => __('Dalje', 'aktopr'),
                'prev' => __('Nazad', 'aktopr'),
                'finish' => __('Završi', 'aktopr'),
                'delete' => __('Obriši', 'aktopr'),
                'confirm' => __('Potvrdi', 'aktopr'),
                'cancel' => __('Otkaži', 'aktopr'),
            ],
            'api_keys' => get_option('aktopr_api_keys', []),
        ]);
    }

    public function render_app($atts): string {
        $atts = shortcode_atts([
            'klijent_id' => 0,
        ], $atts);

        $klijent_id = (int) $atts['klijent_id'];
        
        if ($klijent_id === 0 && is_user_logged_in()) {
            $klijent_id = $this->get_current_user_klijent_id();
        }

        $klijent = $klijent_id > 0 ? get_post($klijent_id) : null;
        $klijent_data = $klijent ? $this->get_klijent_data($klijent) : $this->get_default_klijent_data();
        $modules_status = $this->get_modules_status($klijent_id);
        $zaposleni = $this->get_zaposleni($klijent_id);
        $radna_mesta = $this->get_radna_mesta($klijent_id);
        $propisi = Auto_AktoPR_Database::get_propisi();
        $koeficijenti = Auto_AktoPR_Database::get_koeficijenti();
        
        ob_start();
        include AUTO_AKTOPR_PLUGIN_DIR . 'frontend/templates/app.php';
        return ob_get_clean();
    }

    private function get_current_user_klijent_id(): int {
        $user_id = get_current_user_id();
        $klijenti = get_posts([
            'post_type' => 'aapr_klijent',
            'author' => $user_id,
            'posts_per_page' => 1,
        ]);
        return !empty($klijenti) ? $klijenti[0]->ID : 0;
    }

    private function get_klijent_data($klijent): array {
        return [
            'id' => $klijent->ID,
            'naziv' => $klijent->post_title,
            'pib' => get_post_meta($klijent->ID, 'aapr_pib', true),
            'adresa' => get_post_meta($klijent->ID, 'aapr_adresa', true),
            'telefon' => get_post_meta($klijent->ID, 'aapr_telefon', true),
            'telefon2' => get_post_meta($klijent->ID, 'aapr_telefon2', true),
            'email' => get_post_meta($klijent->ID, 'aapr_email', true),
            'delatnost' => get_post_meta($klijent->ID, 'aapr_delatnost', true),
            'tip_delatnosti' => get_post_meta($klijent->ID, 'aapr_tip_delatnosti', true),
            'broj_zaposlenih' => get_post_meta($klijent->ID, 'aapr_broj_zaposlenih', true),
            'odluka_broj' => get_post_meta($klijent->ID, 'aapr_odluka_broj', true),
            'odluka_datum' => get_post_meta($klijent->ID, 'aapr_odluka_datum', true),
            'strucno_lice' => get_post_meta($klijent->ID, 'aapr_strucno_lice', true),
            'broj_licence' => get_post_meta($klijent->ID, 'aapr_broj_licence', true),
            'maticni_broj' => get_post_meta($klijent->ID, 'aapr_maticni_broj', true),
            'sifra_delatnosti' => get_post_meta($klijent->ID, 'aapr_sifra_delatnosti', true),
            'odgovorno_lice' => get_post_meta($klijent->ID, 'aapr_odgovorno_lice', true),
            'web_sajt' => get_post_meta($klijent->ID, 'aapr_web_sajt', true),
        ];
    }

    private function get_default_klijent_data(): array {
        return [
            'id' => 0,
            'naziv' => '',
            'pib' => '',
            'adresa' => '',
            'telefon' => '',
            'telefon2' => '',
            'email' => '',
            'delatnost' => '',
            'tip_delatnosti' => 'kancelarijski',
            'broj_zaposlenih' => 0,
            'odluka_broj' => '',
            'odluka_datum' => date('Y-m-d'),
            'strucno_lice' => '',
            'broj_licence' => '',
            'maticni_broj' => '',
            'sifra_delatnosti' => '',
            'odgovorno_lice' => '',
            'web_sajt' => '',
        ];
    }

    private function get_modules_status(int $klijent_id): array {
        $status = [];
        for ($i = 1; $i <= 10; $i++) {
            $meta_key = 'aapr_modul_' . $i . '_status';
            $status[$i] = $klijent_id > 0 ? get_post_meta($klijent_id, $meta_key, true) : '';
        }
        return $status;
    }

    private function get_zaposleni(int $klijent_id): array {
        if ($klijent_id === 0) return [];
        
        $posts = get_posts([
            'post_type' => 'aapr_zaposleni',
            'post_parent' => $klijent_id,
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);

        $zaposleni = [];
        foreach ($posts as $post) {
            $zaposleni[] = [
                'id' => $post->ID,
                'ime_prezime' => $post->post_title,
                'jmbg' => get_post_meta($post->ID, 'aapr_jmbg', true),
                'radno_mesto' => get_post_meta($post->ID, 'aapr_radno_mesto', true),
                'smenski_rad' => (bool) get_post_meta($post->ID, 'aapr_smenski_rad', true),
                'nocni_rad' => (bool) get_post_meta($post->ID, 'aapr_nocni_rad', true),
                'terenski_rad' => (bool) get_post_meta($post->ID, 'aapr_terenski_rad', true),
                'datum_zaposlenja' => get_post_meta($post->ID, 'aapr_datum_zaposlenja', true),
            ];
        }
        return $zaposleni;
    }

    private function get_radna_mesta(int $klijent_id): array {
        if ($klijent_id === 0) return [];
        
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}aapr_radna_mesta WHERE client_id = %d",
                $klijent_id
            ),
            ARRAY_A
        );
    }

    public function ajax_save_module(): void {
        check_ajax_referer('aktopr_nonce');
        
        $klijent_id = (int) ($_POST['klijent_id'] ?? 0);
        $module = (int) ($_POST['module'] ?? 0);
        $data = $_POST['data'] ?? [];
        
        if ($klijent_id === 0 || $module === 0) {
            wp_send_json_error(['message' => 'Nevažeći parametri']);
        }
        
        $module_key = 'aapr_modul_' . $module . '_data';
        update_post_meta($klijent_id, $module_key, $data);
        update_post_meta($klijent_id, 'aapr_modul_' . $module . '_status', 'completed');
        update_post_meta($klijent_id, 'aapr_modul_' . $module . '_updated', current_time('mysql'));
        
        wp_send_json_success([
            'message' => 'Modul sačuvan',
            'module' => $module,
            'status' => 'completed',
        ]);
    }

    public function ajax_get_module(): void {
        check_ajax_referer('aktopr_nonce');
        
        $klijent_id = (int) ($_POST['klijent_id'] ?? 0);
        $module = (int) ($_POST['module'] ?? 0);
        
        $module_key = 'aapr_modul_' . $module . '_data';
        $data = $klijent_id > 0 ? get_post_meta($klijent_id, $module_key, true) : [];
        
        wp_send_json_success(['data' => $data ?: []]);
    }

    public function ajax_save_klijent(): void {
        check_ajax_referer('aktopr_nonce');
        
        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'post_type' => 'aapr_klijent',
            'post_title' => sanitize_text_field($_POST['naziv'] ?? 'Novi klijent'),
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
            wp_send_json_error(['message' => $post_id->get_error_message()]);
        }
        
        $fields = [
            'pib', 'adresa', 'telefon', 'email', 'delatnost', 'tip_delatnosti', 
            'broj_zaposlenih', 'odluka_broj', 'odluka_datum', 'strucno_lice', 'broj_licence',
            'telefon2', 'maticni_broj', 'sifra_delatnosti', 'odgovorno_lice', 'web_sajt'
        ];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, 'aapr_' . $field, sanitize_text_field($_POST[$field]));
            }
        }
        
        wp_send_json_success(['id' => $post_id, 'message' => 'Klijent sačuvan']);
    }

    public function ajax_get_klijent(): void {
        check_ajax_referer('aktopr_nonce');
        
        $id = (int) ($_POST['id'] ?? 0);
        $post = get_post($id);
        
        if (!$post || $post->post_type !== 'aapr_klijent') {
            wp_send_json_error(['message' => 'Klijent nije pronađen']);
        }
        
        $data = $this->get_klijent_data($post);
        wp_send_json_success(['data' => $data]);
    }

    public function ajax_save_zaposleni(): void {
        check_ajax_referer('aktopr_nonce');
        
        $klijent_id = (int) ($_POST['klijent_id'] ?? 0);
        $id = (int) ($_POST['id'] ?? 0);
        
        $data = [
            'post_type' => 'aapr_zaposleni',
            'post_title' => sanitize_text_field($_POST['ime_prezime'] ?? ''),
            'post_status' => 'publish',
            'post_parent' => $klijent_id,
        ];
        
        if ($id > 0) {
            $data['ID'] = $id;
            $post_id = wp_update_post($data);
        } else {
            $data['post_author'] = get_current_user_id();
            $post_id = wp_insert_post($data);
        }
        
        $fields = ['jmbg', 'radno_mesto', 'smenski_rad', 'nocni_rad', 'terenski_rad', 'datum_zaposlenja'];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $value = in_array($field, ['smenski_rad', 'nocni_rad', 'terenski_rad']) 
                    ? 1 
                    : sanitize_text_field($_POST[$field]);
                update_post_meta($post_id, 'aapr_' . $field, $value);
            }
        }
        
        wp_send_json_success(['id' => $post_id, 'message' => 'Zaposleni sačuvan']);
    }

    public function ajax_delete_zaposleni(): void {
        check_ajax_referer('aktopr_nonce');
        
        $id = (int) ($_POST['id'] ?? 0);
        wp_delete_post($id, true);
        
        wp_send_json_success(['message' => 'Zaposleni obrisan']);
    }

    public function ajax_get_zaposleni(): void {
        check_ajax_referer('aktopr_nonce');
        
        $id = (int) ($_POST['id'] ?? 0);
        $post = get_post($id);
        
        if (!$post || $post->post_type !== 'aapr_zaposleni') {
            wp_send_json_error(['message' => 'Zaposleni nije pronađen']);
        }
        
        $data = [
            'id' => $post->ID,
            'ime_prezime' => $post->post_title,
            'jmbg' => get_post_meta($post->ID, 'aapr_jmbg', true),
            'radno_mesto' => get_post_meta($post->ID, 'aapr_radno_mesto', true),
            'smenski_rad' => (bool) get_post_meta($post->ID, 'aapr_smenski_rad', true),
            'nocni_rad' => (bool) get_post_meta($post->ID, 'aapr_nocni_rad', true),
            'terenski_rad' => (bool) get_post_meta($post->ID, 'aapr_terenski_rad', true),
            'datum_zaposlenja' => get_post_meta($post->ID, 'aapr_datum_zaposlenja', true),
        ];
        
        wp_send_json_success(['data' => $data]);
    }

    public function ajax_save_radno_mesto(): void {
        check_ajax_referer('aktopr_nonce');
        
        global $wpdb;
        $klijent_id = (int) ($_POST['klijent_id'] ?? 0);
        $id = (int) ($_POST['id'] ?? 0);
        
        $data = [
            'client_id' => $klijent_id,
            'sifra' => sanitize_text_field($_POST['sifra'] ?? ''),
            'naziv' => sanitize_text_field($_POST['naziv'] ?? ''),
            'opis_posla' => sanitize_textarea_field($_POST['opis_posla'] ?? ''),
            'grupa' => sanitize_text_field($_POST['grupa'] ?? 'ostalo'),
            'rad_na_visini' => isset($_POST['rad_na_visini']) ? 1 : 0,
            'rad_sa_hemikalijama' => isset($_POST['rad_sa_hemikalijama']) ? 1 : 0,
            'rad_za_racunarom' => isset($_POST['rad_za_racunarom']) ? 1 : 0,
            'smenski_rad' => isset($_POST['smenski_rad']) ? 1 : 0,
            'nocni_rad' => isset($_POST['nocni_rad']) ? 1 : 0,
        ];
        
        if ($id > 0) {
            $wpdb->update($wpdb->prefix . 'aapr_radna_mesta', $data, ['id' => $id]);
            wp_send_json_success(['id' => $id, 'message' => 'Radno mesto ažurirano']);
        } else {
            $wpdb->insert($wpdb->prefix . 'aapr_radna_mesta', $data);
            wp_send_json_success(['id' => $wpdb->insert_id, 'message' => 'Radno mesto dodato']);
        }
    }

    public function ajax_get_radno_mesto(): void {
        check_ajax_referer('aktopr_nonce');
        
        global $wpdb;
        $id = (int) ($_POST['id'] ?? 0);
        
        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}aapr_radna_mesta WHERE id = %d", $id),
            ARRAY_A
        );
        
        if (!$result) {
            wp_send_json_error(['message' => 'Radno mesto nije pronađeno']);
        }
        
        wp_send_json_success(['data' => $result]);
    }

    public function ajax_delete_radno_mesto(): void {
        check_ajax_referer('aktopr_nonce');
        
        global $wpdb;
        $id = (int) ($_POST['id'] ?? 0);
        
        $wpdb->delete($wpdb->prefix . 'aapr_radna_mesta', ['id' => $id]);
        
        wp_send_json_success(['message' => 'Radno mesto obrisano']);
    }

    public function ajax_get_svi_klijenti(): void {
        check_ajax_referer('aktopr_nonce');
        
        $klijenti = Auto_AktoPR_Database::get_svi_klijenti();
        $data = [];
        
        foreach ($klijenti as $k) {
            $aktivi_count = count(Auto_AktoPR_Database::get_aktivi_za_klijenta($k->ID));
            $data[] = [
                'id' => $k->ID,
                'naziv' => $k->post_title,
                'pib' => get_post_meta($k->ID, 'aapr_pib', true),
                'tip_delatnosti' => get_post_meta($k->ID, 'aapr_tip_delatnosti', true),
                'adresa' => get_post_meta($k->ID, 'aapr_adresa', true),
                'telefon' => get_post_meta($k->ID, 'aapr_telefon', true),
                'email' => get_post_meta($k->ID, 'aapr_email', true),
                'broj_zaposlenih' => count($this->get_zaposleni($k->ID)),
                'aktivi_count' => $aktivi_count,
            ];
        }
        
        wp_send_json_success(['data' => $data]);
    }

    public function ajax_kreiraj_novi_akt(): void {
        check_ajax_referer('aktopr_nonce');
        
        $klijent_id = (int) ($_POST['klijent_id'] ?? 0);
        $data = [
            'naziv' => sanitize_text_field($_POST['naziv'] ?? 'Akt o proceni rizika'),
            'broj' => sanitize_text_field($_POST['broj'] ?? ''),
            'datum_izrade' => sanitize_text_field($_POST['datum_izrade'] ?? date('Y-m-d')),
            'datum_stupanja' => sanitize_text_field($_POST['datum_stupanja'] ?? ''),
        ];
        
        if ($klijent_id === 0) {
            wp_send_json_error(['message' => 'Nije izabran klijent']);
        }
        
        $akt_id = Auto_AktoPR_Database::kreiraj_novi_akt($klijent_id, $data);
        
        wp_send_json_success([
            'id' => $akt_id,
            'message' => 'Akt kreiran',
            'redirect' => add_query_arg(['akt_id' => $akt_id], get_permalink())
        ]);
    }

    public function ajax_get_aktivi(): void {
        check_ajax_referer('aktopr_nonce');
        
        $klijent_id = (int) ($_POST['klijent_id'] ?? 0);
        
        if ($klijent_id === 0) {
            wp_send_json_error(['message' => 'Nevažeći klijent']);
        }
        
        $aktivi = Auto_AktoPR_Database::get_aktivi_za_klijenta($klijent_id);
        wp_send_json_success(['data' => $aktivi]);
    }

    public function ajax_ai_generate(): void {
        check_ajax_referer('aktopr_nonce');
        
        $module = (int) ($_POST['module'] ?? 0);
        $klijent_id = (int) ($_POST['klijent_id'] ?? 0);
        $context = wp_kses_post($_POST['context'] ?? '');
        
        $api_keys = get_option('aktopr_api_keys', []);
        $openai_key = $api_keys['openai'] ?? '';
        
        if (empty($openai_key)) {
            wp_send_json_error(['message' => 'AI nije konfigurisan']);
        }
        
        $klijent = get_post($klijent_id);
        $system_prompt = $this->get_ai_system_prompt($module, $klijent);
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $openai_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'model' => 'gpt-4-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $system_prompt],
                    ['role' => 'user', 'content' => $context],
                ],
                'max_tokens' => 3000,
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

    private function get_ai_system_prompt(int $module, $klijent): string {
        $prompts = [
            1 => 'Ti si stručnjak za bezbednost i zdravlje na radu u Srbiji. Pišeš uvodni deo Akta o proceni rizika prema Pravilniku o načinu i postupku procene rizika (Sl. glasnik RS br. 76/2024). Piši formalnim pravničkim stilom na srpskom jeziku.',
            2 => 'Ti si stručnjak za bezbednost i zdravlju na radu. Pišeš opis radnog procesa za Akt o proceni rizika. Koristi uslovne tekst blokove zavisno od tipa delatnosti (kancelarijski, građevinski, proizvodni).',
            3 => 'Pišeš sistematizaciju radnih mesta za Akt o proceni rizika. Za svako radno mesto navedi: naziv, broj izvršilaca, opis posla, uslove rada.',
            4 => 'Identifikuješ opasnosti i štetnosti za Akt o proceni rizika. Kategorizuj po grupama: mehaničke, električne, hemijske, fizičke, ergonomske, psihosocijalne.',
            5 => 'Vršiš procenu rizika Kinney metodom (R = P × F × C). Za svaku opasnost proceni verovatnoću, učestalost i težinu posledica.',
            6 => 'Predlažeš mere zaštite za Akt o proceni rizika. Kategorizuj: tehničke, organizacione, PPE, osposobljavanje. Navedi rokove i zadužena lica.',
            7 => 'Pišeš evidencije i izveštaje za Akt o proceni rizika. Ti si stručnjak za BZR.',
            8 => 'Pišeš zaključak Akta o proceni rizika. Sumiraj stanje bezbednosti i daj preporuke.',
            9 => 'Pišeš priloge za Akt o proceni rizika.',
            10 => 'Vodiš istoriju izmena za Akt o proceni rizika.',
        ];
        
        $prompt = $prompts[$module] ?? 'Pišeš tekst za Akt o proceni rizika prema srpskom pravilniku.';
        
        if ($klijent) {
            $prompt .= "\n\nKlijent: " . $klijent->post_title;
            $tip = get_post_meta($klijent->ID, 'aapr_tip_delatnosti', true);
            if ($tip) {
                $prompt .= "\nTip delatnosti: " . $tip;
            }
        }
        
        return $prompt;
    }

    public function ajax_generate_document(): void {
        check_ajax_referer('aktopr_nonce');
        
        $klijent_id = (int) ($_POST['klijent_id'] ?? 0);
        
        if ($klijent_id === 0) {
            wp_send_json_error(['message' => 'Nevažeći klijent']);
        }
        
        $html = $this->generate_document_html($klijent_id);
        
        wp_send_json_success(['html' => $html]);
    }

    private function generate_document_html(int $klijent_id): string {
        $klijent = get_post($klijent_id);
        if (!$klijent) return '';
        
        $modul1 = get_post_meta($klijent_id, 'aapr_modul_1_data', true) ?: [];
        $zaposleni = $this->get_zaposleni($klijent_id);
        $radna_mesta = $this->get_radna_mesta($klijent_id);
        
        ob_start();
        ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Akt o proceni rizika - <?php echo esc_html($klijent->post_title); ?></title>
<style>
    body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.6; padding: 20mm; }
    h1 { font-size: 18pt; text-align: center; margin-bottom: 30px; }
    h2 { font-size: 14pt; margin: 25px 0 10px; border-bottom: 1px solid #000; padding-bottom: 5px; }
    h3 { font-size: 12pt; margin: 20px 0 8px; }
    p { text-align: justify; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    th, td { border: 1px solid #000; padding: 8px; }
    th { background: #f0f0f0; font-weight: bold; }
    .header { text-align: center; margin-bottom: 40px; }
    .meta { background: #f9f9f9; padding: 15px; border-left: 3px solid #000; margin: 20px 0; }
    @media print { body { padding: 0; } }
</style>
</head>
<body>
<div class="header">
    <h1>AKT O PROCENI RIZIKA<br>NA RADNOM MESTU I U RADNOJ SREDINI</h1>
    <p><strong><?php echo esc_html($klijent->post_title); ?></strong></p>
    <p>Verzija 1.0 | <?php echo date('d.m.Y.'); ?></p>
</div>

<div class="meta">
    <p><strong>Poslodavac:</strong> <?php echo esc_html($klijent->post_title); ?></p>
    <p><strong>PIB:</strong> <?php echo esc_html(get_post_meta($klijent_id, 'aapr_pib', true)); ?></p>
    <p><strong>Adresa:</strong> <?php echo esc_html(get_post_meta($klijent_id, 'aapr_adresa', true)); ?></p>
    <p><strong>Delatnost:</strong> <?php echo esc_html(get_post_meta($klijent_id, 'aapr_delatnost', true)); ?></p>
    <p><strong>Broj zaposlenih:</strong> <?php echo count($zaposleni); ?></p>
    <p><strong>Odluka br.:</strong> <?php echo esc_html(get_post_meta($klijent_id, 'aapr_odluka_broj', true)); ?></p>
</div>

<?php if (!empty($modul1)): ?>
<h2>1. UVOD I OPŠTI PODACI</h2>

<h3>1.1. Odluka o pokretanju postupka procene rizika</h3>
<p>Na osnovu Odluke poslodavca br. <?php echo esc_html($modul1['odluka_broj'] ?? ''); ?> od <?php echo esc_html($modul1['odluka_datum'] ?? ''); ?>, pokreće se postupak procene rizika na radnom mestu i u radnoj sredini u skladu sa Pravilnikom o načinu i postupku procene rizika ("Sl. glasnik RS", br. 76/2024).</p>

<h3>1.2. Podaci o poslodavcu</h3>
<table>
    <tr><th>Naziv</th><td><?php echo esc_html($klijent->post_title); ?></td></tr>
    <tr><th>PIB</th><td><?php echo esc_html(get_post_meta($klijent_id, 'aapr_pib', true)); ?></td></tr>
    <tr><th>Adresa</th><td><?php echo esc_html(get_post_meta($klijent_id, 'aapr_adresa', true)); ?></td></tr>
    <tr><th>Delatnost</th><td><?php echo esc_html(get_post_meta($klijent_id, 'aapr_delatnost', true)); ?></td></tr>
</table>

<h3>1.3. Podaci o licima koja vrše procenu rizika</h3>
<p><?php echo esc_html($modul1['strucno_lice'] ?? ''); ?>, licenca br. <?php echo esc_html($modul1['broj_licence'] ?? ''); ?></p>

<h3>1.4. Zakonska osnova</h3>
<p>Ovaj Akt je sačinjen u skladu sa:</p>
<ul>
    <li>Zakon o bezbednosti i zdravlju na radu ("Sl. glasnik RS", br. 35/2023 i 96/2024)</li>
    <li>Pravilnik o načinu i postupku procene rizika ("Sl. glasnik RS", br. 76/2024)</li>
</ul>
<?php endif; ?>

<?php if (!empty($radna_mesta)): ?>
<h2>3. SISTEMATIZACIJA RADNIH MESTA</h2>
<table>
    <thead>
        <tr>
            <th>Rb.</th>
            <th>Naziv radnog mesta</th>
            <th>Šifra</th>
            <th>Broj izvršilaca</th>
            <th>Grupa</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($radna_mesta as $i => $rm): ?>
        <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo esc_html($rm['naziv']); ?></td>
            <td><?php echo esc_html($rm['sifra']); ?></td>
            <td>1</td>
            <td><?php echo esc_html(ucfirst($rm['grupa'])); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<div style="margin-top: 60px;">
    <p><strong>Datum:</strong> <?php echo date('d.m.Y.'); ?></p>
    <p><strong>Potpis:</strong> _______________________</p>
</div>

<p style="margin-top: 30px; text-align: center; font-size: 10pt; color: #666;">
    Dokument generisan automatski | Auto AoPR | <?php echo date('d.m.Y. H:i'); ?>
</p>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}

new Auto_AktoPR_App();
