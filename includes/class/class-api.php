<?php

class Auto_AktoPR_API {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void {
        register_rest_route('aapr/v1', '/klijenti', [
            'methods' => 'GET',
            'callback' => [$this, 'get_klijenti'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
        
        register_rest_route('aapr/v1', '/klijenti', [
            'methods' => 'POST',
            'callback' => [$this, 'create_klijent'],
            'permission_callback' => [$this, 'check_permission_edit'],
        ]);
        
        register_rest_route('aapr/v1', '/klijenti/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_klijent'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => ['id' => ['validate_callback' => function($param) { return is_numeric($param); }]],
        ]);
        
        register_rest_route('aapr/v1', '/klijenti/(?P<id>\d+)', [
            'methods' => ['PUT', 'PATCH'],
            'callback' => [$this, 'update_klijent'],
            'permission_callback' => [$this, 'check_permission_edit'],
        ]);
        
        register_rest_route('aapr/v1', '/klijenti/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete_klijent'],
            'permission_callback' => [$this, 'check_permission_edit'],
        ]);
        
        register_rest_route('aapr/v1', '/zaposleni', [
            'methods' => 'GET',
            'callback' => [$this, 'get_zaposleni'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
        
        register_rest_route('aapr/v1', '/zaposleni', [
            'methods' => 'POST',
            'callback' => [$this, 'create_zaposleni'],
            'permission_callback' => [$this, 'check_permission_edit'],
        ]);
        
        register_rest_route('aapr/v1', '/master-dokument', [
            'methods' => 'GET',
            'callback' => [$this, 'get_master_dokumenti'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
        
        register_rest_route('aapr/v1', '/master-dokument', [
            'methods' => 'POST',
            'callback' => [$this, 'create_master_dokument'],
            'permission_callback' => [$this, 'check_permission_edit'],
        ]);
        
        register_rest_route('aapr/v1', '/master-dokument/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_master_dokument'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
        
        register_rest_route('aapr/v1', '/master-dokument/(?P<id>\d+)/sekcija/(?P<sekcija>[a-z0-9_-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_sekcija'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
        
        register_rest_route('aapr/v1', '/risici', [
            'methods' => 'GET',
            'callback' => [$this, 'get_risici'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
        
        register_rest_route('aapr/v1', '/risici', [
            'methods' => 'POST',
            'callback' => [$this, 'create_risik'],
            'permission_callback' => [$this, 'check_permission_edit'],
        ]);
        
        register_rest_route('aapr/v1', '/biblioteka/propisi', [
            'methods' => 'GET',
            'callback' => [$this, 'get_propisi'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
        
        register_rest_route('aapr/v1', '/biblioteka/koeficijenti', [
            'methods' => 'GET',
            'callback' => [$this, 'get_koeficijenti'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
        
        register_rest_route('aapr/v1', '/biblioteka/standardne-mere', [
            'methods' => 'GET',
            'callback' => [$this, 'get_standardne_mere'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
        
        register_rest_route('aapr/v1', '/izracunaj-rizik', [
            'methods' => 'POST',
            'callback' => [$this, 'izracunaj_rizik'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
    }

    public function check_permission(): bool {
        return current_user_can('read') || current_user_can('edit_aapr_klijent');
    }

    public function check_permission_edit(): bool {
        return current_user_can('edit_aapr_klijent') || current_user_can('manage_options');
    }

    public function get_klijenti(\WP_REST_Request $request): \WP_REST_Response {
        $posts = get_posts([
            'post_type' => 'aapr_klijent',
            'posts_per_page' => $request->get_param('per_page') ?? 100,
            'paged' => $request->get_param('page') ?? 1,
        ]);
        
        $data = [];
        foreach ($posts as $post) {
            $data[] = $this->format_klijent($post);
        }
        
        return new \WP_REST_Response($data, 200);
    }

    public function get_klijent(\WP_REST_Request $request): \WP_REST_Response {
        $id = (int) $request->get_param('id');
        $post = get_post($id);
        
        if (!$post || $post->post_type !== 'aapr_klijent') {
            return new \WP_REST_Response(['error' => 'Klijent nije pronađen'], 404);
        }
        
        return new \WP_REST_Response($this->format_klijent($post), 200);
    }

    public function create_klijent(\WP_REST_Request $request): \WP_REST_Response {
        $params = $request->get_json_params();
        
        $post_id = wp_insert_post([
            'post_title' => sanitize_text_field($params['naziv'] ?? ''),
            'post_type' => 'aapr_klijent',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ]);
        
        if (is_wp_error($post_id)) {
            return new \WP_REST_Response(['error' => $post_id->get_error_message()], 400);
        }
        
        $meta_fields = ['pib', 'adresa', 'telefon', 'email', 'delatnost', 'broj_zaposlenih'];
        foreach ($meta_fields as $field) {
            if (isset($params[$field])) {
                update_post_meta($post_id, 'aapr_' . $field, sanitize_text_field($params[$field]));
            }
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'id' => $post_id,
            'data' => $this->format_klijent(get_post($post_id)),
        ], 201);
    }

    public function update_klijent(\WP_REST_Request $request): \WP_REST_Response {
        $id = (int) $request->get_param('id');
        $params = $request->get_json_params();
        
        $post_id = wp_update_post([
            'ID' => $id,
            'post_title' => sanitize_text_field($params['naziv'] ?? ''),
        ]);
        
        if (is_wp_error($post_id)) {
            return new \WP_REST_Response(['error' => $post_id->get_error_message()], 400);
        }
        
        $meta_fields = ['pib', 'adresa', 'telefon', 'email', 'delatnost', 'broj_zaposlenih'];
        foreach ($meta_fields as $field) {
            if (isset($params[$field])) {
                update_post_meta($id, 'aapr_' . $field, sanitize_text_field($params[$field]));
            }
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $this->format_klijent(get_post($id)),
        ], 200);
    }

    public function delete_klijent(\WP_REST_Request $request): \WP_REST_Response {
        $id = (int) $request->get_param('id');
        $result = wp_delete_post($id, true);
        
        if (!$result) {
            return new \WP_REST_Response(['error' => 'Greška pri brisanju'], 400);
        }
        
        return new \WP_REST_Response(['success' => true], 200);
    }

    public function get_zaposleni(\WP_REST_Request $request): \WP_REST_Response {
        $args = [
            'post_type' => 'aapr_zaposleni',
            'posts_per_page' => $request->get_param('per_page') ?? 100,
            'paged' => $request->get_param('page') ?? 1,
        ];
        
        if ($klijent_id = $request->get_param('klijent_id')) {
            $args['post_parent'] = (int) $klijent_id;
        }
        
        $posts = get_posts($args);
        
        $data = [];
        foreach ($posts as $post) {
            $data[] = $this->format_zaposleni($post);
        }
        
        return new \WP_REST_Response($data, 200);
    }

    public function create_zaposleni(\WP_REST_Request $request): \WP_REST_Response {
        $params = $request->get_json_params();
        
        $post_data = [
            'post_title' => sanitize_text_field($params['ime_prezime'] ?? ''),
            'post_type' => 'aapr_zaposleni',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ];
        
        if (!empty($params['klijent_id'])) {
            $post_data['post_parent'] = (int) $params['klijent_id'];
        }
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return new \WP_REST_Response(['error' => $post_id->get_error_message()], 400);
        }
        
        $meta_fields = ['jmbg', 'radno_mesto', 'smenski_rad', 'nocni_rad', 'datum_zaposlenja'];
        foreach ($meta_fields as $field) {
            if (isset($params[$field])) {
                update_post_meta($post_id, 'aapr_' . $field, sanitize_text_field($params[$field]));
            }
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'id' => $post_id,
            'data' => $this->format_zaposleni(get_post($post_id)),
        ], 201);
    }

    public function get_master_dokumenti(\WP_REST_Request $request): \WP_REST_Response {
        $posts = get_posts([
            'post_type' => 'aapr_master_dokument',
            'posts_per_page' => $request->get_param('per_page') ?? 50,
        ]);
        
        $data = [];
        foreach ($posts as $post) {
            $data[] = $this->format_master_dokument($post);
        }
        
        return new \WP_REST_Response($data, 200);
    }

    public function get_master_dokument(\WP_REST_Request $request): \WP_REST_Response {
        $id = (int) $request->get_param('id');
        $post = get_post($id);
        
        if (!$post || $post->post_type !== 'aapr_master_dokument') {
            return new \WP_REST_Response(['error' => 'Dokument nije pronađen'], 404);
        }
        
        $data = $this->format_master_dokument($post);
        
        global $wpdb;
        $data['sekcije'] = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}aapr_tekst_blokovi WHERE sekcija LIKE %s ORDER BY sekcija, prioritet",
                $id . '-%'
            ),
            ARRAY_A
        );
        
        return new \WP_REST_Response($data, 200);
    }

    public function create_master_dokument(\WP_REST_Request $request): \WP_REST_Response {
        $params = $request->get_json_params();
        
        $post_data = [
            'post_title' => sanitize_text_field($params['naziv'] ?? 'Novi Akt o proceni rizika'),
            'post_type' => 'aapr_master_dokument',
            'post_status' => 'draft',
            'post_author' => get_current_user_id(),
        ];
        
        if (!empty($params['klijent_id'])) {
            $post_data['post_parent'] = (int) $params['klijent_id'];
        }
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return new \WP_REST_Response(['error' => $post_id->get_error_message()], 400);
        }
        
        update_post_meta($post_id, 'aapr_status', 'u_pripremi');
        update_post_meta($post_id, 'aapr_tip_izracunavanja', 'kinney');
        update_post_meta($post_id, 'aapr_verzija', '1.0');
        
        return new \WP_REST_Response([
            'success' => true,
            'id' => $post_id,
        ], 201);
    }

    public function get_sekcija(\WP_REST_Request $request): \WP_REST_Response {
        $dokument_id = (int) $request->get_param('id');
        $sekcija = sanitize_text_field($request->get_param('sekcija'));
        
        $tekst_blokovi = Auto_AktoPR_Database::get_tekst_blokovi($sekcija);
        
        return new \WP_REST_Response([
            'dokument_id' => $dokument_id,
            'sekcija' => $sekcija,
            'blokovi' => $tekst_blokovi,
        ], 200);
    }

    public function get_risici(\WP_REST_Request $request): \WP_REST_Response {
        global $wpdb;
        
        $where = ['1=1'];
        $params = [];
        
        if ($dokument_id = $request->get_param('dokument_id')) {
            $where[] = 'master_dokument_id = %d';
            $params[] = (int) $dokument_id;
        }
        
        $sql = "SELECT * FROM {$wpdb->prefix}aapr_risici WHERE " . implode(' AND ', $where);
        
        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }
        
        $risici = $wpdb->get_results($sql, ARRAY_A);
        
        return new \WP_REST_Response($risici, 200);
    }

    public function create_risik(\WP_REST_Request $request): \WP_REST_Response {
        global $wpdb;
        $params = $request->get_json_params();
        
        $verovatnoca = (float) ($params['verovatnoca'] ?? 1);
        $ucestalost = (float) ($params['ucestalost'] ?? 1);
        $tezina = (float) ($params['tezina'] ?? 1);
        $rizik = $verovatnoca * $ucestalost * $tezina;
        
        $nivo = 'zanemarljiv';
        if ($rizik >= 100) $nivo = 'kritican';
        elseif ($rizik >= 40) $nivo = 'visok';
        elseif ($rizik >= 10) $nivo = 'srednji';
        elseif ($rizik >= 1) $nivo = 'nizak';
        
        $data = [
            'master_dokument_id' => (int) $params['master_dokument_id'],
            'opasnost_id' => (int) ($params['opasnost_id'] ?? 0),
            'opis' => sanitize_textarea_field($params['opis'] ?? ''),
            'verovatnoca' => $verovatnoca,
            'ucestalost' => $ucestalost,
            'tezina' => $tezina,
            'rizik' => $rizik,
            'nivo_rizika' => $nivo,
        ];
        
        $wpdb->insert($wpdb->prefix . 'aapr_risici', $data);
        
        return new \WP_REST_Response([
            'success' => true,
            'id' => $wpdb->insert_id,
            'rizik' => $rizik,
            'nivo_rizika' => $nivo,
        ], 201);
    }

    public function get_propisi(): \WP_REST_Response {
        return new \WP_REST_Response(Auto_AktoPR_Database::get_propisi(), 200);
    }

    public function get_koeficijenti(\WP_REST_Request $request): \WP_REST_Response {
        $tip = $request->get_param('tip') ?? '';
        $kategorija = $request->get_param('kategorija') ?? '';
        
        return new \WP_REST_Response(
            Auto_AktoPR_Database::get_koeficijenti($tip, $kategorija),
            200
        );
    }

    public function get_standardne_mere(\WP_REST_Request $request): \WP_REST_Response {
        $grupa = $request->get_param('grupa') ?? '';
        $tip = $request->get_param('tip') ?? '';
        
        return new \WP_REST_Response(
            Auto_AktoPR_Database::get_standardne_mere($grupa, $tip),
            200
        );
    }

    public function izracunaj_rizik(\WP_REST_Request $request): \WP_REST_Response {
        $verovatnoca = (float) $request->get_param('verovatnoca');
        $ucestalost = (float) $request->get_param('ucestalost');
        $tezina = (float) $request->get_param('tezina');
        
        $rizik = $verovatnoca * $ucestalost * $tezina;
        
        $nivo = 'zanemarljiv';
        if ($rizik >= 100) $nivo = 'kritican';
        elseif ($rizik >= 40) $nivo = 'visok';
        elseif ($rizik >= 10) $nivo = 'srednji';
        elseif ($rizik >= 1) $nivo = 'nizak';
        
        return new \WP_REST_Response([
            'rizik' => round($rizik, 2),
            'nivo_rizika' => $nivo,
            'verovatnoca' => $verovatnoca,
            'ucestalost' => $ucestalost,
            'tezina' => $tezina,
        ], 200);
    }

    private function format_klijent(\WP_Post $post): array {
        return [
            'id' => $post->ID,
            'naziv' => $post->post_title,
            'pib' => get_post_meta($post->ID, 'aapr_pib', true),
            'adresa' => get_post_meta($post->ID, 'aapr_adresa', true),
            'telefon' => get_post_meta($post->ID, 'aapr_telefon', true),
            'email' => get_post_meta($post->ID, 'aapr_email', true),
            'delatnost' => get_post_meta($post->ID, 'aapr_delatnost', true),
            'broj_zaposlenih' => get_post_meta($post->ID, 'aapr_broj_zaposlenih', true),
            'datum_kreiranja' => $post->post_date,
        ];
    }

    private function format_zaposleni(\WP_Post $post): array {
        return [
            'id' => $post->ID,
            'ime_prezime' => $post->post_title,
            'klijent_id' => $post->post_parent,
            'jmbg' => get_post_meta($post->ID, 'aapr_jmbg', true),
            'radno_mesto' => get_post_meta($post->ID, 'aapr_radno_mesto', true),
            'smenski_rad' => (bool) get_post_meta($post->ID, 'aapr_smenski_rad', true),
            'datum_zaposlenja' => get_post_meta($post->ID, 'aapr_datum_zaposlenja', true),
        ];
    }

    private function format_master_dokument(\WP_Post $post): array {
        return [
            'id' => $post->ID,
            'naziv' => $post->post_title,
            'klijent_id' => $post->post_parent,
            'status' => get_post_meta($post->ID, 'aapr_status', true),
            'tip_izracunavanja' => get_post_meta($post->ID, 'aapr_tip_izracunavanja', true),
            'verzija' => get_post_meta($post->ID, 'aapr_verzija', true),
            'datum_kreiranja' => $post->post_date,
            'datum_azuriranja' => $post->post_modified,
        ];
    }
}
