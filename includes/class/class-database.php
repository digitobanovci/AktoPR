<?php

class Auto_AktoPR_Database {

    public static function create_tables(): void {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $tables = [
            'propisi' => "CREATE TABLE {$wpdb->prefix}aapr_propisi (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                sifra VARCHAR(50) NOT NULL,
                naziv TEXT NOT NULL,
                vrsta ENUM('zakon','pravilnik','uredba','standard') NOT NULL,
                link TEXT,
                clanovi TEXT,
                datum_objave DATE,
                napomene TEXT,
                created_by BIGINT UNSIGNED,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset",
            
            'koeficijenti' => "CREATE TABLE {$wpdb->prefix}aapr_koeficijenti (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                naziv VARCHAR(255) NOT NULL,
                tip ENUM('kinney','pearson','matrica') DEFAULT 'kinney',
                kategorija VARCHAR(100) NOT NULL,
                vrednost_min DECIMAL(5,2) NOT NULL,
                vrednost_max DECIMAL(5,2) NOT NULL,
                opis TEXT,
                boja VARCHAR(7),
                redosled INT DEFAULT 0,
                aktivan TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset",
            
            'tekst_blokovi' => "CREATE TABLE {$wpdb->prefix}aapr_tekst_blokovi (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                sifra VARCHAR(50) NOT NULL,
                naslov VARCHAR(255) NOT NULL,
                sadrzaj LONGTEXT NOT NULL,
                sekcija VARCHAR(50),
                tip_uslova VARCHAR(100),
                vrednost_uslova VARCHAR(255),
                prioritet INT DEFAULT 0,
                aktivan TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset",
            
            'standardne_mere' => "CREATE TABLE {$wpdb->prefix}aapr_standardne_mere (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                sifra VARCHAR(50) NOT NULL,
                naziv TEXT NOT NULL,
                opis TEXT,
                tip ENUM('tehnicka','organizaciona','ppe','higijenska','osposobljavanje') NOT NULL,
                prioritet ENUM('visok','srednji','nizak') DEFAULT 'srednji',
                rok_dana INT DEFAULT 30,
                grupa_opasnosti VARCHAR(100),
                nivo_rizika_min DECIMAL(6,2),
                nivo_rizika_max DECIMAL(6,2),
                aktivan TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset",
            
            'audit_log' => "CREATE TABLE {$wpdb->prefix}aapr_audit_log (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT UNSIGNED NOT NULL,
                action VARCHAR(100) NOT NULL,
                object_type VARCHAR(50) NOT NULL,
                object_id BIGINT UNSIGNED,
                old_value LONGTEXT,
                new_value LONGTEXT,
                ip_address VARCHAR(45),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset",
            
            'risici' => "CREATE TABLE {$wpdb->prefix}aapr_risici (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                master_dokument_id BIGINT UNSIGNED NOT NULL,
                modul_id BIGINT UNSIGNED,
                zanimanje_id BIGINT UNSIGNED,
                opasnost_id BIGINT UNSIGNED,
                opis TEXT,
                verovatnoca DECIMAL(3,1) DEFAULT 1.0,
                ucestalost DECIMAL(3,1) DEFAULT 1.0,
                tezina DECIMAL(3,1) DEFAULT 1.0,
                rizik DECIMAL(6,2) DEFAULT 1.0,
                nivo_rizika ENUM('zanemarljiv','nizak','srednji','visok','kritican') DEFAULT 'nizak',
                komentar TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset",
            
            'zaposleni' => "CREATE TABLE {$wpdb->prefix}aapr_zaposleni (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                client_id BIGINT UNSIGNED NOT NULL,
                ime_prezime VARCHAR(255) NOT NULL,
                jmbg VARCHAR(13),
                radno_mesto_id BIGINT UNSIGNED,
                smenski_rad TINYINT(1) DEFAULT 0,
                broj_smena INT DEFAULT 1,
                nocni_rad TINYINT(1) DEFAULT 0,
                terenski_rad TINYINT(1) DEFAULT 0,
                zdravstveni_uslovi TEXT,
                datum_zaposlenja DATE,
                osposobljen TINYINT(1) DEFAULT 0,
                status ENUM('aktivno','neaktivno','na_bolovanju','otpusten') DEFAULT 'aktivno',
                napomene TEXT,
                created_by BIGINT UNSIGNED,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset",
            
            'radna_mesta' => "CREATE TABLE {$wpdb->prefix}aapr_radna_mesta (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                client_id BIGINT UNSIGNED NOT NULL,
                sifra VARCHAR(50),
                naziv VARCHAR(255) NOT NULL,
                opis_posla TEXT,
                grupa ENUM('gradjevinski','elektro_masinski','administrativni','ostalo','gradiliste') NOT NULL,
                rad_na_visini TINYINT(1) DEFAULT 0,
                rad_sa_hemikalijama TINYINT(1) DEFAULT 0,
                rad_za_racunarom TINYINT(1) DEFAULT 0,
                smenski_rad TINYINT(1) DEFAULT 0,
                nocni_rad TINYINT(1) DEFAULT 0,
                standardni_ppe TEXT,
                aktivan TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset",
            
            'opasnosti' => "CREATE TABLE {$wpdb->prefix}aapr_opasnosti (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                grupa ENUM('mehanicke','elektricne','hemijske','bioloske','fizicke','ergonomske','psihosocijalne','ostalo') NOT NULL,
                sifra VARCHAR(50),
                naziv VARCHAR(255) NOT NULL,
                opis TEXT,
                izvor TEXT,
                posledice TEXT,
                aktivan TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset",
            
            'zanimanja' => "CREATE TABLE {$wpdb->prefix}aapr_zanimanja (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                sifra VARCHAR(50) NOT NULL,
                naziv VARCHAR(255) NOT NULL,
                opis TEXT,
                grupa_delatnosti VARCHAR(100) DEFAULT 'gradjevinski',
                kategorija ENUM('radnik','tehnicar','inzenjer','rukovodilac','ostalo') DEFAULT 'radnik',
                aktivan TINYINT(1) DEFAULT 1,
                sistemski TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset",
            
            'aktivi' => "CREATE TABLE {$wpdb->prefix}aapr_aktivi (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                client_id BIGINT UNSIGNED NOT NULL,
                naziv VARCHAR(255) NOT NULL,
                broj VARCHAR(50),
                datum_izrade DATE,
                datum_stupanja DATE,
                verzija INT DEFAULT 1,
                status ENUM('nacrt','aktivan','arhiviran') DEFAULT 'nacrt',
                napomene TEXT,
                created_by BIGINT UNSIGNED,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                FOREIGN KEY (client_id) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE
            ) $charset",
        ];

        foreach ($tables as $sql) {
            dbDelta($sql);
        }

        self::seed_data();
    }

    private static function seed_data(): void {
        global $wpdb;
        if ($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aapr_propisi") > 0) return;

        $wpdb->query("INSERT INTO {$wpdb->prefix}aapr_propisi (sifra, naziv, vrsta, clanovi, napomene, datum_objave) VALUES 
            ('ZZR-2023', 'Zakon o bezbednosti i zdravlju na radu', 'zakon', '[\"Član 14 - Procena rizika\"]', 'Sl. glasnik RS br. 35/2023 i 96/2024', '2023-05-11'),
            ('PPR-76-2024', 'Pravilnik o proceni rizika', 'pravilnik', '[\"Član 4-15 - Kompletan sadržaj akta\"]', 'Sl. glasnik RS br. 76/2024', '2024-07-22')");

        $wpdb->query("INSERT INTO {$wpdb->prefix}aapr_koeficijenti (naziv, tip, kategorija, vrednost_min, vrednost_max, opis, boja, redosled) VALUES 
            ('Gotovo nemoguće', 'kinney', 'verovatnoca', 0.1, 0.2, 'Skoro nikad', '#22c55e', 1),
            ('Malo verovatno', 'kinney', 'verovatnoca', 0.5, 1.0, 'Retko', '#84cc16', 2),
            ('Moguće', 'kinney', 'verovatnoca', 1.5, 3.0, 'Može', '#eab308', 3),
            ('Verovatno', 'kinney', 'verovatnoca', 4.0, 6.0, 'Višekrat godišnje', '#f97316', 4),
            ('Gotovo sigurno', 'kinney', 'verovatnoca', 10.0, 10.0, 'Stalno', '#dc2626', 5),
            ('Veoma retko', 'kinney', 'ucestalost', 1.0, 2.0, 'Godišnje', '#22c55e', 10),
            ('Povremeno', 'kinney', 'ucestalost', 5.0, 6.0, 'Nekoliko puta mesečno', '#eab308', 12),
            ('Često', 'kinney', 'ucestalost', 7.0, 10.0, 'Svakodnevno', '#ef4444', 14),
            ('Bez povrede', 'kinney', 'tezina', 1.0, 2.0, 'Bez posledica', '#22c55e', 20),
            ('Laka povreda', 'kinney', 'tezina', 3.0, 4.0, 'Prva pomoć', '#84cc16', 21),
            ('Srednja povreda', 'kinney', 'tezina', 5.0, 6.0, 'Odsustvo do 15 dana', '#eab308', 22),
            ('Teška povreda', 'kinney', 'tezina', 7.0, 8.0, 'Bolničko lečenje', '#f97316', 23),
            ('Smrt ili invaliditet', 'kinney', 'tezina', 9.0, 10.0, 'Smrt', '#dc2626', 24)");

        $wpdb->query("INSERT INTO {$wpdb->prefix}aapr_tekst_blokovi (sifra, naslov, sadrzaj, sekcija, tip_uslova, vrednost_uslova, prioritet) VALUES 
            ('UVOD-01', 'Uvodna odredba', 'Na osnovu Odluke poslodavca br. {{odluka_broj}} od {{odluka_datum}}, pokreće se postupak procene rizika.', '1.1', 'default', NULL, 1),
            ('DEL-KANC', 'Kancelarijski rad', 'Poslodavac obavlja kancelarijske i administrativne poslove u zatvorenom prostoru.', '2.1', 'tip_delatnosti', 'kancelarijski', 1),
            ('DEL-GRAD', 'Građevinski radovi', 'Radovi se izvode na otvorenom prostoru, izloženi atmosferskim uticajima.', '2.1', 'tip_delatnosti', 'gradjevina', 1),
            ('DEL-PROIZ', 'Proizvodni procesi', 'Poslodavac obavlja proizvodne poslove uz primenu mera bezbednosti.', '2.1', 'tip_delatnosti', 'proizvodnja', 1)");

        $wpdb->query("INSERT INTO {$wpdb->prefix}aapr_standardne_mere (sifra, naziv, opis, tip, prioritet, rok_dana, grupa_opasnosti) VALUES 
            ('M-PAD-VISINA', 'Zaštita od pada sa visine', 'Postavljanje zaštitnih ograda i sigurnosnih pojasa.', 'tehnicka', 'visok', 7, 'mehanicke'),
            ('M-STRUJNI', 'Zaštita od električnog udara', 'Ugradnja FI zaštite i provera instalacija.', 'tehnicka', 'visok', 15, 'elektricne'),
            ('M-OBUKA', 'Obuka zaposlenih', 'Sprovođenje redovnih obuka za bezbedan rad.', 'osposobljavanje', 'visok', 30, 'ostalo'),
            ('M-PPE', 'Lična zaštitna oprema', 'Nabavka i zamena PPE.', 'ppe', 'visok', 15, 'ostalo')");

        $wpdb->query("INSERT INTO {$wpdb->prefix}aapr_opasnosti (grupa, sifra, naziv, opis, izvor, posledice) VALUES 
            ('mehanicke', 'OP-PAD', 'Pad sa visine', 'Opasnost od pada sa visine.', 'Rad na visini', 'Teške povrede'),
            ('mehanicke', 'OP-UDAR', 'Udar predmeta', 'Opasnost od udarca.', 'Alati, materijali', 'Povrede'),
            ('elektricne', 'OP-STRUJ', 'Električni udar', 'Opasnost od strujnog udara.', 'Elektroinstalacije', 'Smrt'),
            ('fizicke', 'OP-BUKA', 'Buka', 'Štetno dejstvo buke.', 'Mašine', 'Oštećenje sluha'),
            ('fizicke', 'OP-PRAS', 'Prašina', 'Udisanje prašine.', 'Građevinski materijali', 'Respiratorne bolesti'),
            ('ergonomske', 'OP-ERGO', 'Loša ergonomija', 'Neodgovarajući položaj.', 'Dugotrajan rad', 'Bolovi u leđima'),
            ('psihosocijalne', 'OP-STRES', 'Stres', 'Psihički pritisak.', 'Radni uslovi', 'Anksioznost')");

        if ($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aapr_zanimanja") == 0) {
            $zanimanja = [
                ['sifra' => 'GRD-RAD', 'naziv' => 'Građevinski radnik', 'kategorija' => 'radnik', 'opis' => 'Opšti građevinski radovi'],
                ['sifra' => 'GRD-ZID', 'naziv' => 'Zidar', 'kategorija' => 'radnik', 'opis' => 'Zidarski radovi, malterisanje, gips'],
                ['sifra' => 'GRD-TES', 'naziv' => 'Tesar', 'kategorija' => 'radnik', 'opis' => 'Drvena oplata, krovne konstrukcije'],
                ['sifra' => 'GRD-ARM', 'naziv' => 'Armirač', 'kategorija' => 'radnik', 'opis' => 'Armiranje betonskih konstrukcija'],
                ['sifra' => 'GRD-BET', 'naziv' => 'Betonski radnik', 'kategorija' => 'radnik', 'opis' => 'Betoniranje, ugradnja betona'],
                ['sifra' => 'GRD-MOL', 'naziv' => 'Moler', 'kategorija' => 'radnik', 'opis' => 'Farbanje, krečenje, gipskartonski radovi'],
                ['sifra' => 'GRD-KER', 'naziv' => 'Keramičar', 'kategorija' => 'radnik', 'opis' => 'Postavljanje keramike, pločica'],
                ['sifra' => 'GRD-VOD', 'naziv' => 'Vodoinstalater', 'kategorija' => 'radnik', 'opis' => 'Vodovod i kanalizacija'],
                ['sifra' => 'GRD-ELE', 'naziv' => 'Električar', 'kategorija' => 'radnik', 'opis' => 'Elektroinstalacije'],
                ['sifra' => 'GRD-GAS', 'naziv' => 'Monter gasnih instalacija', 'kategorija' => 'radnik', 'opis' => 'Gasne instalacije'],
                ['sifra' => 'GRD-KLI', 'naziv' => 'Klimatizacija i ventilacija', 'kategorija' => 'radnik', 'opis' => 'HVAC sistemi'],
                ['sifra' => 'GRD-ALU', 'naziv' => 'Aluminijumski stolarija', 'kategorija' => 'radnik', 'opis' => 'Aluminijumska stolarija'],
                ['sifra' => 'GRD-VOZ', 'naziv' => 'Vozač', 'kategorija' => 'radnik', 'opis' => 'Transport vozilima'],
                ['sifra' => 'GRD-OPR', 'naziv' => 'Operator građevinskih mašina', 'kategorija' => 'radnik', 'opis' => 'Bager, utovarivač, dizalica'],
                ['sifra' => 'GRD-BET-MIX', 'naziv' => 'Betonski mešač', 'kategorija' => 'radnik', 'opis' => 'Priprema betona'],
                ['sifra' => 'GRD-SIG', 'naziv' => 'Sigar', 'kategorija' => 'radnik', 'opis' => 'Postavljanje izolacije'],
                ['sifra' => 'GRD-RUK', 'naziv' => 'Rukovodilac gradilišta', 'kategorija' => 'rukovodilac', 'opis' => 'Rukovodi građevinskim radovima'],
                ['sifra' => 'GRD-GRA', 'naziv' => 'Građevinski inspektor', 'kategorija' => 'inzenjer', 'opis' => 'Nadzor nad radovima'],
                ['sifra' => 'GRD-TEH', 'naziv' => 'Građevinski tehničar', 'kategorija' => 'tehnicar', 'opis' => 'Tehnička dokumentacija'],
                ['sifra' => 'GRD-EL-VOD', 'naziv' => 'Elektro-vodoinstalater', 'kategorija' => 'radnik', 'opis' => 'Kombinovani elektro i vodovodni radovi'],
                ['sifra' => 'GRD-SAN', 'naziv' => 'Soboslikar', 'kategorija' => 'radnik', 'opis' => 'Farbanje i bojenje'],
                ['sifra' => 'GRD-POD', 'naziv' => 'Podopolagač', 'kategorija' => 'radnik', 'opis' => 'Postavljanje podova'],
                ['sifra' => 'GRD-LIM', 'naziv' => 'Limar', 'kategorija' => 'radnik', 'opis' => 'Limarski radovi na krovovima'],
                ['sifra' => 'GRD-KOV', 'naziv' => 'Kovač', 'kategorija' => 'radnik', 'opis' => 'Kovački radovi'],
                ['sifra' => 'GRD-NAD', 'naziv' => 'Nadzornik BZR', 'kategorija' => 'rukovodilac', 'opis' => 'Nadzor bezbednosti i zdravlja na radu'],
            ];
            
            foreach ($zanimanja as $z) {
                $wpdb->insert($wpdb->prefix . 'aapr_zanimanja', [
                    'sifra' => $z['sifra'],
                    'naziv' => $z['naziv'],
                    'opis' => $z['opis'],
                    'kategorija' => $z['kategorija'],
                    'sistemski' => 1,
                ]);
            }
        }
    }

    public static function log_action(string $action, string $object_type, ?int $object_id = null, $old_value = null, $new_value = null): void {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'aapr_audit_log', [
            'user_id' => get_current_user_id(),
            'action' => $action,
            'object_type' => $object_type,
            'object_id' => $object_id,
            'old_value' => is_array($old_value) ? json_encode($old_value) : $old_value,
            'new_value' => is_array($new_value) ? json_encode($new_value) : $new_value,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
    }

    public static function get_propisi(): array {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aapr_propisi ORDER BY vrsta, datum_objave DESC", ARRAY_A);
    }

    public static function get_koeficijenti(string $tip = '', string $kategorija = ''): array {
        global $wpdb;
        $where = ['aktivan = 1'];
        if ($tip) $where[] = $wpdb->prepare("tip = %s", $tip);
        if ($kategorija) $where[] = $wpdb->prepare("kategorija = %s", $kategorija);
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aapr_koeficijenti WHERE " . implode(' AND ', $where) . " ORDER BY redosled", ARRAY_A);
    }

    public static function get_tekst_blokovi(string $sekcija = '', string $tip_uslova = ''): array {
        global $wpdb;
        $where = ['aktivan = 1'];
        if ($sekcija) $where[] = $wpdb->prepare("sekcija = %s", $sekcija);
        if ($tip_uslova) $where[] = $wpdb->prepare("(tip_uslova = %s OR tip_uslova = 'default' OR tip_uslova IS NULL)", $tip_uslova);
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aapr_tekst_blokovi WHERE " . implode(' AND ', $where) . " ORDER BY prioritet", ARRAY_A);
    }

    public static function get_standardne_mere(string $grupa_opasnosti = '', string $tip = ''): array {
        global $wpdb;
        $where = ['aktivan = 1'];
        if ($grupa_opasnosti) $where[] = $wpdb->prepare("grupa_opasnosti = %s", $grupa_opasnosti);
        if ($tip) $where[] = $wpdb->prepare("tip = %s", $tip);
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aapr_standardne_mere WHERE " . implode(' AND ', $where) . " ORDER BY prioritet", ARRAY_A);
    }

    public static function get_zanimanja(string $grupa_delatnosti = '', bool $samo_aktivna = true): array {
        global $wpdb;
        $where = [];
        if ($samo_aktivna) $where[] = 'aktivan = 1';
        if ($grupa_delatnosti) $where[] = $wpdb->prepare("grupa_delatnosti = %s", $grupa_delatnosti);
        $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aapr_zanimanja $where_sql ORDER BY kategorija, naziv", ARRAY_A);
    }

    public static function get_svi_klijenti(): array {
        return get_posts([
            'post_type' => 'aapr_klijent',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ]);
    }

    public static function get_aktivi_za_klijenta(int $klijent_id): array {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}aapr_aktivi WHERE client_id = %d ORDER BY created_at DESC", $klijent_id),
            ARRAY_A
        );
    }

    public static function kreiraj_novi_akt(int $klijent_id, array $data): int {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'aapr_aktivi', [
            'client_id' => $klijent_id,
            'naziv' => sanitize_text_field($data['naziv'] ?? 'Akt o proceni rizika'),
            'broj' => sanitize_text_field($data['broj'] ?? ''),
            'datum_izrade' => sanitize_text_field($data['datum_izrade'] ?? date('Y-m-d')),
            'datum_stupanja' => sanitize_text_field($data['datum_stupanja'] ?? ''),
            'status' => 'nacrt',
            'created_by' => get_current_user_id(),
        ]);
        return (int) $wpdb->insert_id;
    }
}
