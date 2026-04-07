<?php

class Auto_AktoPR_Document {

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_aapr_master_dokument', [$this, 'save_dokument_meta'], 10, 2);
    }

    public function add_meta_boxes(): void {
        add_meta_box(
            'aapr_dokument_info',
            'Informacije o dokumentu',
            [$this, 'render_dokument_info_box'],
            'aapr_master_dokument',
            'side',
            'default'
        );
        
        add_meta_box(
            'aapr_dokument_sekcije',
            'Sekcije dokumenta',
            [$this, 'render_sekcije_box'],
            'aapr_master_dokument',
            'normal',
            'high'
        );
        
        add_meta_box(
            'aapr_risici_box',
            'Rizici',
            [$this, 'render_risici_box'],
            'aapr_master_dokument',
            'normal',
            'default'
        );
    }

    public function render_dokument_info_box(\WP_Post $post): void {
        wp_nonce_field('aapr_dokument_save', 'aapr_dokument_nonce');
        
        $status = get_post_meta($post->ID, 'aapr_status', true) ?: 'u_pripremi';
        $tip = get_post_meta($post->ID, 'aapr_tip_izracunavanja', true) ?: 'kinney';
        $verzija = get_post_meta($post->ID, 'aapr_verzija', true) ?: '1.0';
        $odluka_broj = get_post_meta($post->ID, 'aapr_odluka_broj', true) ?: '';
        $odluka_datum = get_post_meta($post->ID, 'aapr_odluka_datum', true) ?: '';
        ?>
        <p>
            <label for="aapr_status">Status:</label>
            <select id="aapr_status" name="aapr_status" style="width: 100%;">
                <option value="u_pripremi" <?php selected($status, 'u_pripremi'); ?>>U pripremi</option>
                <option value="na_pregledu" <?php selected($status, 'na_pregledu'); ?>>Na pregledu</option>
                <option value="odobren" <?php selected($status, 'odobren'); ?>>Odobren</option>
                <option value="objavljen" <?php selected($status, 'objavljen'); ?>>Objavljen</option>
            </select>
        </p>
        <p>
            <label for="aapr_tip_izracunavanja">Tip izračunavanja rizika:</label>
            <select id="aapr_tip_izracunavanja" name="aapr_tip_izracunavanja" style="width: 100%;">
                <option value="kinney" <?php selected($tip, 'kinney'); ?>>Kinney metoda</option>
                <option value="pearson" <?php selected($tip, 'pearson'); ?>>Pearson matrica</option>
            </select>
        </p>
        <p>
            <label for="aapr_verzija">Verzija:</label>
            <input type="text" id="aapr_verzija" name="aapr_verzija" value="<?php echo esc_attr($verzija); ?>" class="widefat">
        </p>
        <p>
            <label for="aapr_odluka_broj">Broj odluke:</label>
            <input type="text" id="aapr_odluka_broj" name="aapr_odluka_broj" value="<?php echo esc_attr($odluka_broj); ?>" class="widefat">
        </p>
        <p>
            <label for="aapr_odluka_datum">Datum odluke:</label>
            <input type="date" id="aapr_odluka_datum" name="aapr_odluka_datum" value="<?php echo esc_attr($odluka_datum); ?>" class="widefat">
        </p>
        <?php
    }

    public function render_sekcije_box(\WP_Post $post): void {
        $sekcije = $this->get_dokument_sekcije();
        $sacuvani = get_post_meta($post->ID, 'aapr_sekcije', true) ?: [];
        ?>
        <div class="aapr-sekcije-wrapper">
            <p><strong>Članovi dokumenta (Pravilnik 76/2024):</strong></p>
            <table class="widefat">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Sekcija</th>
                        <th>Opis</th>
                        <th style="width: 80px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sekcije as $br => $sekcija): ?>
                    <tr>
                        <td><?php echo $br; ?></td>
                        <td><?php echo esc_html($sekcija['naziv']); ?></td>
                        <td><?php echo esc_html($sekcija['opis']); ?></td>
                        <td>
                            <?php 
                            $checked = in_array($br, $sacuvani) ? 'checked' : '';
                            ?>
                            <input type="checkbox" name="aapr_sekcije[]" value="<?php echo $br; ?>" <?php echo $checked; ?>>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function render_risici_box(\WP_Post $post): void {
        global $wpdb;
        
        $risici = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT r.*, o.naziv as opasnost_naziv, o.grupa as opasnost_grupa 
                FROM {$wpdb->prefix}aapr_risici r 
                LEFT JOIN {$wpdb->prefix}aapr_opasnosti o ON r.opasnost_id = o.id 
                WHERE r.master_dokument_id = %d 
                ORDER BY r.rizik DESC",
                $post->ID
            ),
            ARRAY_A
        );
        
        $koeficijenti = Auto_AktoPR_Database::get_koeficijenti();
        ?>
        <div id="aapr-risici-app">
            <div class="aapr-risici-header">
                <button type="button" class="button" id="aapr-dodaj-risik">+ Dodaj rizik</button>
                <select id="aapr-filter-nivo">
                    <option value="">Svi nivoi</option>
                    <option value="kritican">Kritičan</option>
                    <option value="visok">Visok</option>
                    <option value="srednji">Srednji</option>
                    <option value="nizak">Nizak</option>
                </select>
            </div>
            
            <table class="widefat aapr-risici-tabela">
                <thead>
                    <tr>
                        <th>Opasnost</th>
                        <th>Verovatnoća</th>
                        <th>Učestalost</th>
                        <th>Težina</th>
                        <th>Rizik</th>
                        <th>Nivo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="aapr-risici-body">
                    <?php if (empty($risici)): ?>
                    <tr class="no-items">
                        <td colspan="7">Nema unetih rizika. Kliknite "Dodaj rizik" za početak.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($risici as $risik): ?>
                    <tr data-id="<?php echo esc_attr($risik['id']); ?>" data-nivo="<?php echo esc_attr($risik['nivo_rizika']); ?>">
                        <td><?php echo esc_html($risik['opasnost_naziv'] ?: $risik['opis']); ?></td>
                        <td><?php echo esc_html($risik['verovatnoca']); ?></td>
                        <td><?php echo esc_html($risik['ucestalost']); ?></td>
                        <td><?php echo esc_html($risik['tezina']); ?></td>
                        <td><strong><?php echo esc_html($risik['rizik']); ?></strong></td>
                        <td>
                            <span class="aapr-badge aapr-badge-<?php echo esc_attr($risik['nivo_rizika']); ?>">
                                <?php echo esc_html(ucfirst($risik['nivo_rizika'])); ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="button-link aapr-edit-risik">Uredi</button>
                            <button type="button" class="button-link aapr-delete-risik">Obriši</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div id="aapr-risik-modal" style="display: none;">
            <div class="aapr-modal-content">
                <h3>Dodaj/Uredi rizik</h3>
                <p>
                    <label>Opasnost:</label>
                    <select name="opasnost_id" id="modal-opasnost" style="width: 100%;">
                        <option value="">-- Izaberi opasnost --</option>
                        <?php 
                        $opasnosti = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aapr_opasnosti WHERE aktivan = 1", ARRAY_A);
                        foreach ($opasnosti as $opasnost): 
                        ?>
                        <option value="<?php echo esc_attr($opasnost['id']); ?>">
                            <?php echo esc_html($opasnost['grupa'] . ' - ' . $opasnost['naziv']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p>
                    <label>Opis (dodatno):</label>
                    <textarea name="opis" id="modal-opis" rows="2" class="widefat"></textarea>
                </p>
                <div style="display: flex; gap: 10px;">
                    <p style="flex: 1;">
                        <label>Verovatnoća (P):</label>
                        <select name="verovatnoca" id="modal-verovatnoca" style="width: 100%;">
                            <?php foreach ($this->get_koeficijenti_by_kategorija($koeficijenti, 'verovatnoca') as $k): ?>
                            <option value="<?php echo esc_attr($k['vrednost_min']); ?>">
                                <?php echo esc_html($k['naziv'] . ' (' . $k['vrednost_min'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                    <p style="flex: 1;">
                        <label>Učestalost (F):</label>
                        <select name="ucestalost" id="modal-ucestalost" style="width: 100%;">
                            <?php foreach ($this->get_koeficijenti_by_kategorija($koeficijenti, 'ucestalost') as $k): ?>
                            <option value="<?php echo esc_attr($k['vrednost_min']); ?>">
                                <?php echo esc_html($k['naziv'] . ' (' . $k['vrednost_min'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                    <p style="flex: 1;">
                        <label>Težina (C):</label>
                        <select name="tezina" id="modal-tezina" style="width: 100%;">
                            <?php foreach ($this->get_koeficijenti_by_kategorija($koeficijenti, 'tezina') as $k): ?>
                            <option value="<?php echo esc_attr($k['vrednost_min']); ?>">
                                <?php echo esc_html($k['naziv'] . ' (' . $k['vrednost_min'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                </div>
                <p>
                    <label>Rizik:</label>
                    <input type="text" id="modal-rizik-preview" readonly class="widefat" value="1.0">
                </p>
                <div style="text-align: right; margin-top: 15px;">
                    <button type="button" class="button" id="aapr-modal-cancel">Otkaži</button>
                    <button type="button" class="button button-primary" id="aapr-modal-save">Sačuvaj</button>
                </div>
            </div>
        </div>
        <?php
    }

    public function save_dokument_meta(int $post_id, \WP_Post $post): void {
        if (!isset($_POST['aapr_dokument_nonce']) || !wp_verify_nonce($_POST['aapr_dokument_nonce'], 'aapr_dokument_save')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if ($post->post_type !== 'aapr_master_dokument') {
            return;
        }
        
        $fields = [
            'aapr_status', 'aapr_tip_izracunavanja', 'aapr_verzija',
            'aapr_odluka_broj', 'aapr_odluka_datum'
        ];
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
        
        if (isset($_POST['aapr_sekcije']) && is_array($_POST['aapr_sekcije'])) {
            update_post_meta($post_id, 'aapr_sekcije', array_map('intval', $_POST['aapr_sekcije']));
        }
    }

    private function get_dokument_sekcije(): array {
        return [
            1 => ['naziv' => '1.1', 'opis' => 'Odluka o pokretanju postupka'],
            2 => ['naziv' => '1.2', 'opis' => 'Podaci o poslodavcu'],
            3 => ['naziv' => '1.3', 'opis' => 'Podaci o stručnom timu'],
            4 => ['naziv' => '1.4', 'opis' => 'Zakonska osnova'],
            5 => ['naziv' => '2.1', 'opis' => 'Opis delatnosti'],
            6 => ['naziv' => '2.2', 'opis' => 'Opis radnog procesa'],
            7 => ['naziv' => '3.1', 'opis' => 'Sistematizacija radnih mesta'],
            8 => ['naziv' => '3.2', 'opis' => 'Opis radnih mesta'],
            9 => ['naziv' => '4.1', 'opis' => 'Identifikacija opasnosti'],
            10 => ['naziv' => '4.2', 'opis' => 'Procena izloženosti'],
            11 => ['naziv' => '5.1', 'opis' => 'Analiza rizika'],
            12 => ['naziv' => '5.2', 'opis' => 'Matrica procene'],
            13 => ['naziv' => '6.1', 'opis' => 'Mere za sprečavanje'],
            14 => ['naziv' => '6.2', 'opis' => 'Plan implementacije'],
            15 => ['naziv' => '7', 'opis' => 'Evidencije i izveštaji'],
            16 => ['naziv' => '8', 'opis' => 'Zaključak'],
            17 => ['naziv' => '9', 'opis' => 'Prilozi'],
            18 => ['naziv' => '10', 'opis' => 'Izmene i dopune'],
        ];
    }

    private function get_koeficijenti_by_kategorija(array $koeficijenti, string $kategorija): array {
        return array_filter($koeficijenti, fn($k) => $k['kategorija'] === $kategorija);
    }

    public function generisi_dokument(int $dokument_id, array $sekcije = []): string {
        $post = get_post($dokument_id);
        if (!$post || $post->post_type !== 'aapr_master_dokument') {
            return '';
        }
        
        $klijent = get_post($post->post_parent);
        $sadrzaj = '';
        
        foreach ($sekcije as $sekcija_br) {
            $sadrzaj .= $this->generisi_sekciju($dokument_id, $sekcija_br, $klijent);
        }
        
        return $sadrzaj;
    }

    private function generisi_sekciju(int $dokument_id, int $sekcija_br, \WP_Post $klijent): string {
        $sekcija_info = $this->get_dokument_sekcije()[$sekcija_br] ?? [];
        $tekst_blokovi = Auto_AktoPR_Database::get_tekst_blokovi($sekcija_info['naziv'] ?? '');
        
        $sadrzaj = '<h2>Sekcija ' . esc_html($sekcija_info['naziv'] ?? '') . '</h2>';
        $sadrzaj .= '<p><em>' . esc_html($sekcija_info['opis'] ?? '') . '</em></p>';
        
        if (!empty($tekst_blokovi)) {
            foreach ($tekst_blokovi as $blok) {
                $tekst = str_replace(
                    ['{{odluka_broj}}', '{{odluka_datum}}', '{{klijent_naziv}}'],
                    [
                        get_post_meta($dokument_id, 'aapr_odluka_broj', true),
                        get_post_meta($dokument_id, 'aapr_odluka_datum', true),
                        $klijent->post_title ?? ''
                    ],
                    $blok['sadrzaj']
                );
                $sadrzaj .= wpautop($tekst);
            }
        }
        
        return $sadrzaj;
    }
}
