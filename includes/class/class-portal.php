<?php

class Auto_AktoPR_Portal {

    public function __construct() {
        add_shortcode('aktopr_portal', [$this, 'render_portal']);
        add_shortcode('aktopr_login', [$this, 'render_login']);
        add_shortcode('aktopr_register', [$this, 'render_register']);
        add_action('wp_ajax_aapr_portal_login', [$this, 'ajax_login']);
        add_action('wp_ajax_nopriv_aapr_portal_login', [$this, 'ajax_login']);
        add_action('wp_ajax_aapr_portal_register', [$this, 'ajax_register']);
        add_action('wp_ajax_nopriv_aapr_portal_register', [$this, 'ajax_register']);
        add_action('wp_ajax_aapr_portal_save_zaposleni', [$this, 'ajax_save_zaposleni']);
        add_action('wp_ajax_aapr_portal_delete_zaposleni', [$this, 'ajax_delete_zaposleni']);
        add_action('wp_ajax_aapr_portal_save_radno_mesto', [$this, 'ajax_save_radno_mesto']);
        add_action('wp_ajax_aapr_portal_delete_radno_mesto', [$this, 'ajax_delete_radno_mesto']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets(): void {
        wp_enqueue_style('auto-aktopr-portal', AUTO_AKTOPR_PLUGIN_URL . 'assets/css/portal.css', [], AUTO_AKTOPR_VERSION);
        wp_enqueue_script('auto-aktopr-portal', AUTO_AKTOPR_PLUGIN_URL . 'assets/js/portal.js', ['jquery'], AUTO_AKTOPR_VERSION, true);
        wp_localize_script('auto-aktopr-portal', 'aaprPortal', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aapr_portal_nonce'),
            'is_logged_in' => is_user_logged_in(),
            'strings' => [
                'login' => __('Prijava', 'auto-aktopr'),
                'register' => __('Registracija', 'auto-aktopr'),
                'logout' => __('Odjava', 'auto-aktopr'),
                'save' => __('Sačuvaj', 'auto-aktopr'),
                'delete' => __('Obriši', 'auto-aktopr'),
                'cancel' => __('Otkaži', 'auto-aktopr'),
                'confirm_delete' => __('Da li ste sigurni?', 'auto-aktopr'),
                'loading' => __('Učitavanje...', 'auto-aktopr'),
            ]
        ]);
    }

    public function render_login($atts): string {
        if (is_user_logged_in()) {
            return $this->render_dashboard();
        }
        
        ob_start();
        include AUTO_AKTOPR_PLUGIN_DIR . 'frontend/templates/portal-login.php';
        return ob_get_clean();
    }

    public function render_register($atts): string {
        if (is_user_logged_in()) {
            return $this->render_dashboard();
        }
        
        ob_start();
        include AUTO_AKTOPR_PLUGIN_DIR . 'frontend/templates/portal-register.php';
        return ob_get_clean();
    }

    public function render_portal($atts): string {
        $atts = shortcode_atts([
            'klijent_id' => 0,
        ], $atts);

        if (!is_user_logged_in()) {
            return $this->render_login_form();
        }

        return $this->render_dashboard((int)$atts['klijent_id']);
    }

    private function render_login_form(): string {
        ob_start();
        include AUTO_AKTOPR_PLUGIN_DIR . 'frontend/templates/portal-login.php';
        return ob_get_clean();
    }

    private function render_dashboard(int $klijent_id = 0): string {
        $user = wp_get_current_user();
        $klijent = $this->get_or_create_klijent_for_user($user);
        
        if ($klijent_id === 0 && $klijent) {
            $klijent_id = $klijent->ID;
        }

        $zaposleni = $this->get_zaposleni_for_klijent($klijent_id);
        $radna_mesta = $this->get_radna_mesta_for_klijent($klijent_id);
        $dokumenti = $this->get_dokumenti_for_klijent($klijent_id);
        
        ob_start();
        include AUTO_AKTOPR_PLUGIN_DIR . 'frontend/templates/portal-dashboard.php';
        return ob_get_clean();
    }

    private function get_or_create_klijent_for_user($user): ?\WP_Post {
        $posts = get_posts([
            'post_type' => 'aapr_klijent',
            'author' => $user->ID,
            'posts_per_page' => 1,
        ]);
        
        if (!empty($posts)) {
            return $posts[0];
        }
        
        return null;
    }

    private function get_zaposleni_for_klijent(int $klijent_id): array {
        return get_posts([
            'post_type' => 'aapr_zaposleni',
            'post_parent' => $klijent_id,
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);
    }

    private function get_radna_mesta_for_klijent(int $klijent_id): array {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}aapr_radna_mesta WHERE client_id = %d ORDER BY id DESC",
                $klijent_id
            ),
            ARRAY_A
        );
    }

    private function get_dokumenti_for_klijent(int $klijent_id): array {
        return get_posts([
            'post_type' => 'aapr_master_dokument',
            'post_parent' => $klijent_id,
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft'],
        ]);
    }

    public function ajax_login(): void {
        check_ajax_referer('aapr_portal_nonce');
        
        $username = sanitize_text_field($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = (bool)($_POST['remember'] ?? false);
        
        if (empty($username) || empty($password)) {
            wp_send_json_error(['message' => 'Unesite korisničko ime i lozinku.']);
        }
        
        $creds = [
            'user_login' => $username,
            'user_password' => $password,
            'remember' => $remember,
        ];
        
        $user = wp_signon($creds, false);
        
        if (is_wp_error($user)) {
            wp_send_json_error(['message' => 'Pogrešno korisničko ime ili lozinka.']);
        }
        
        wp_send_json_success([
            'message' => 'Uspešno ste prijavljeni!',
            'redirect' => $_POST['redirect_to'] ?? get_permalink(),
        ]);
    }

    public function ajax_register(): void {
        check_ajax_referer('aapr_portal_nonce');
        
        $username = sanitize_user($_POST['username'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $firma_naziv = sanitize_text_field($_POST['firma_naziv'] ?? '');
        
        if (empty($username) || empty($email) || empty($password) || empty($firma_naziv)) {
            wp_send_json_error(['message' => 'Sva polja su obavezna.']);
        }
        
        if (!is_email($email)) {
            wp_send_json_error(['message' => 'Neispravna email adresa.']);
        }
        
        if (username_exists($username)) {
            wp_send_json_error(['message' => 'Korisničko ime već postoji.']);
        }
        
        if (email_exists($email)) {
            wp_send_json_error(['message' => 'Email adresa već postoji.']);
        }
        
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => $user_id->get_error_message()]);
        }
        
        wp_update_user([
            'ID' => $user_id,
            'role' => 'aapr_klijent',
            'display_name' => $firma_naziv,
        ]);
        
        $klijent_id = wp_insert_post([
            'post_type' => 'aapr_klijent',
            'post_title' => $firma_naziv,
            'post_status' => 'publish',
            'post_author' => $user_id,
        ]);
        
        if (!is_wp_error($klijent_id)) {
            update_post_meta($klijent_id, 'aapr_pib', sanitize_text_field($_POST['pib'] ?? ''));
            update_post_meta($klijent_id, 'aapr_adresa', sanitize_text_field($_POST['adresa'] ?? ''));
            update_post_meta($klijent_id, 'aapr_telefon', sanitize_text_field($_POST['telefon'] ?? ''));
            update_post_meta($klijent_id, 'aapr_email', $email);
            update_post_meta($klijent_id, 'aapr_delatnost', sanitize_text_field($_POST['delatnost'] ?? ''));
        }
        
        wp_signon([
            'user_login' => $username,
            'user_password' => $password,
        ], false);
        
        wp_send_json_success([
            'message' => 'Uspešno ste registrovan!',
            'redirect' => get_permalink(),
        ]);
    }

    public function ajax_save_zaposleni(): void {
        check_ajax_referer('aapr_portal_nonce');
        
        if (!current_user_can('edit_aapr_zaposleni') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Nemate dozvolu.']);
        }
        
        $id = (int) ($_POST['id'] ?? 0);
        $klijent_id = (int) ($_POST['klijent_id'] ?? 0);
        
        $user = wp_get_current_user();
        $klijent = $this->get_or_create_klijent_for_user($user);
        
        if ($klijent) {
            $klijent_id = $klijent->ID;
        }
        
        $data = [
            'post_type' => 'aapr_zaposleni',
            'post_title' => sanitize_text_field($_POST['ime_prezime'] ?? ''),
            'post_status' => 'publish',
            'post_author' => $user->ID,
        ];
        
        if ($klijent_id > 0) {
            $data['post_parent'] = $klijent_id;
        }
        
        if ($id > 0) {
            $data['ID'] = $id;
            $post_id = wp_update_post($data);
        } else {
            $post_id = wp_insert_post($data);
        }
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => $post_id->get_error_message()]);
        }
        
        $meta_fields = ['jmbg', 'radno_mesto', 'smenski_rad', 'nocni_rad', 'terenski_rad', 'datum_zaposlenja', 'zdravstveni_uslovi', 'napomene'];
        foreach ($meta_fields as $field) {
            if (isset($_POST[$field])) {
                if (in_array($field, ['smenski_rad', 'nocni_rad', 'terenski_rad'])) {
                    update_post_meta($post_id, 'aapr_' . $field, 1);
                } else {
                    update_post_meta($post_id, 'aapr_' . $field, sanitize_text_field($_POST[$field]));
                }
            }
        }
        
        wp_send_json_success([
            'message' => 'Zaposleni uspešno sačuvan!',
            'id' => $post_id,
        ]);
    }

    public function ajax_delete_zaposleni(): void {
        check_ajax_referer('aapr_portal_nonce');
        
        $id = (int) ($_POST['id'] ?? 0);
        
        if ($id === 0) {
            wp_send_json_error(['message' => 'Nevažeći ID.']);
        }
        
        $post = get_post($id);
        if (!$post || $post->post_type !== 'aapr_zaposleni') {
            wp_send_json_error(['message' => 'Zaposleni ne postoji.']);
        }
        
        if ($post->post_author !== get_current_user_id() && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Nemate dozvolu za brisanje.']);
        }
        
        wp_delete_post($id, true);
        
        wp_send_json_success(['message' => 'Zaposleni obrisan!']);
    }

    public function ajax_save_radno_mesto(): void {
        check_ajax_referer('aapr_portal_nonce');
        
        global $wpdb;
        
        $id = (int) ($_POST['id'] ?? 0);
        $user = wp_get_current_user();
        $klijent = $this->get_or_create_klijent_for_user($user);
        
        $data = [
            'client_id' => $klijent ? $klijent->ID : 0,
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
            wp_send_json_success(['message' => 'Radno mesto ažurirano!', 'id' => $id]);
        } else {
            $wpdb->insert($wpdb->prefix . 'aapr_radna_mesta', $data);
            wp_send_json_success(['message' => 'Radno mesto dodato!', 'id' => $wpdb->insert_id]);
        }
    }

    public function ajax_delete_radno_mesto(): void {
        check_ajax_referer('aapr_portal_nonce');
        
        global $wpdb;
        $id = (int) ($_POST['id'] ?? 0);
        
        if ($id === 0) {
            wp_send_json_error(['message' => 'Nevažeći ID.']);
        }
        
        $wpdb->delete($wpdb->prefix . 'aapr_radna_mesta', ['id' => $id]);
        
        wp_send_json_success(['message' => 'Radno mesto obrisano!']);
    }

    public function ajax_logout(): void {
        wp_logout();
        wp_send_json_success(['redirect' => home_url()]);
    }
}

new Auto_AktoPR_Portal();
