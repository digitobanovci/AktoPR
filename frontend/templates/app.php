<?php if (!defined('ABSPATH')) exit; ?>

<div class="aktopr-app" id="aktopr-app" data-klijent-id="<?php echo esc_attr($klijent_id); ?>">
    
    <!-- Header -->
    <header class="aktopr-header">
        <div class="aktopr-header-brand">
            <h1> Akt oPR</h1>
            <span class="aktopr-brand-subtitle">Akt o Proceni Rizika</span>
        </div>
        
        <div class="aktopr-header-client">
            <?php if ($klijent): ?>
            <span class="aktopr-client-name"><?php echo esc_html($klijent->post_title); ?></span>
            <button type="button" class="aktopr-btn aktopr-btn-sm" id="aktopr-edit-client">
                <span class="dashicons dashicons-edit"></span>
            </button>
            <?php else: ?>
            <button type="button" class="aktopr-btn aktopr-btn-primary" id="aktopr-new-client">
                + Novi klijent
            </button>
            <?php endif; ?>
        </div>
        
        <div class="aktopr-header-actions">
            <button type="button" class="aktopr-btn" id="aktopr-preview">
                <span class="dashicons dashicons-visibility"></span>
                Pregled
            </button>
            <button type="button" class="aktopr-btn aktopr-btn-primary" id="aktopr-generate-doc">
                <span class="dashicons dashicons-download"></span>
                Word
            </button>
        </div>
    </header>
    
    <!-- Main Layout -->
    <div class="aktopr-layout">
        
        <!-- Sidebar -->
        <aside class="aktopr-sidebar">
            <nav class="aktopr-nav">
                <div class="aktopr-nav-section">
                    <h3>Moduli</h3>
                    <?php foreach ($this->modules as $num => $module): 
                        $status_class = '';
                        $status_icon = '○';
                        if (!empty($modules_status[$num])) {
                            if ($modules_status[$num] === 'completed') {
                                $status_class = 'completed';
                                $status_icon = '✓';
                            } elseif ($modules_status[$num] === 'in_progress') {
                                $status_class = 'in-progress';
                                $status_icon = '◐';
                            }
                        }
                    ?>
                    <button type="button" 
                            class="aktopr-nav-item <?php echo esc_attr($status_class); ?>" 
                            data-module="<?php echo esc_attr($num); ?>"
                            data-sections="<?php echo esc_attr($module['sections']); ?>"
                            <?php echo ($klijent_id === 0) ? 'disabled title="Prvo izaberite klijenta"' : ''; ?>>
                        <span class="aktopr-nav-icon"><?php echo esc_html($module['icon']); ?></span>
                        <span class="aktopr-nav-text">
                            <span class="aktopr-nav-num"><?php echo esc_html($num); ?>.</span>
                            <?php echo esc_html($module['name']); ?>
                        </span>
                        <span class="aktopr-nav-status"><?php echo esc_html($status_icon); ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
                
                <div class="aktopr-nav-section">
                    <h3>Klijenti</h3>
                    <button type="button" class="aktopr-nav-item" data-panel="klijenti">
                        <span class="aktopr-nav-icon">🏢</span>
                        <span class="aktopr-nav-text">Klijenti</span>
                    </button>
                    <button type="button" class="aktopr-nav-item" data-panel="zaposleni">
                        <span class="aktopr-nav-icon">👥</span>
                        <span class="aktopr-nav-text">Zaposleni (<?php echo count($zaposleni); ?>)</span>
                    </button>
                    <button type="button" class="aktopr-nav-item" data-panel="radna_mesta">
                        <span class="aktopr-nav-icon">🏭</span>
                        <span class="aktopr-nav-text">Radna mesta (<?php echo count($radna_mesta); ?>)</span>
                    </button>
                    <button type="button" class="aktopr-nav-item" data-panel="biblioteka">
                        <span class="aktopr-nav-icon">📚</span>
                        <span class="aktopr-nav-text">Biblioteka</span>
                    </button>
                </div>
            </nav>
            
            <div class="aktopr-sidebar-footer">
                <div class="aktopr-progress-overview">
                    <div class="aktopr-progress-bar">
                        <div class="aktopr-progress-fill" style="width: 0%"></div>
                    </div>
                    <span class="aktopr-progress-text">0% kompletno</span>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="aktopr-main">
            
            <!-- Welcome / No Client -->
            <?php if (!$klijent): ?>
            <div class="aktopr-welcome">
                <div class="aktopr-welcome-content">
                    <h2>Dobrodošli u Auto AoPR</h2>
                    <p>Kreirajte novog klijenta ili izaberite postojećeg da biste započeli izradu Akta o proceni rizika.</p>
                    <button type="button" class="aktopr-btn aktopr-btn-primary aktopr-btn-lg" id="aktopr-welcome-new">
                        + Kreiraj novog klijenta
                    </button>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Module Panels -->
            <div class="aktopr-panels">
                
                <!-- MODUL 1: Osnovni podaci -->
                <section class="aktopr-panel" data-panel="module_1" style="<?php echo $klijent ? 'display: block;' : 'display: none;'; ?>">
                    <div class="aktopr-panel-header">
                        <h2>1. Osnovni podaci i Uvod <span class="aktopr-section-tag">1.1-1.4</span></h2>
                    </div>
                    <div class="aktopr-panel-content">
                        <div class="aktopr-form-grid">
                            <div class="aktopr-form-group">
                                <label>Pun naziv firme *</label>
                                <input type="text" name="podaci[naziv]" value="<?php echo esc_attr($klijent_data['naziv']); ?>" required>
                            </div>
                            <div class="aktopr-form-group">
                                <label>PIB</label>
                                <input type="text" name="podaci[pib]" value="<?php echo esc_attr($klijent_data['pib']); ?>">
                            </div>
                            <div class="aktopr-form-group">
                                <label>Adresa</label>
                                <input type="text" name="podaci[adresa]" value="<?php echo esc_attr($klijent_data['adresa']); ?>">
                            </div>
                            <div class="aktopr-form-group">
                                <label>Telefon</label>
                                <input type="tel" name="podaci[telefon]" value="<?php echo esc_attr($klijent_data['telefon']); ?>">
                            </div>
                            <div class="aktopr-form-group">
                                <label>Email</label>
                                <input type="email" name="podaci[email]" value="<?php echo esc_attr($klijent_data['email']); ?>">
                            </div>
                            <div class="aktopr-form-group">
                                <label>Tip delatnosti</label>
                                <select name="podaci[tip_delatnosti]">
                                    <option value="kancelarijski" <?php selected($klijent_data['tip_delatnosti'], 'kancelarijski'); ?>>Kancelarijski</option>
                                    <option value="gradjevinski" <?php selected($klijent_data['tip_delatnosti'], 'gradjevinski'); ?>>Građevinski</option>
                                    <option value="proizvodnja" <?php selected($klijent_data['tip_delatnosti'], 'proizvodnja'); ?>>Proizvodnja</option>
                                    <option value="usluge" <?php selected($klijent_data['tip_delatnosti'], 'usluge'); ?>>Usluge</option>
                                    <option value="mesovito" <?php selected($klijent_data['tip_delatnosti'], 'mesovito'); ?>>Mešovito</option>
                                </select>
                            </div>
                        </div>
                        
                        <h3>Odluka o pokretanju</h3>
                        <div class="aktopr-form-grid">
                            <div class="aktopr-form-group">
                                <label>Broj odluke</label>
                                <input type="text" name="podaci[odluka_broj]" value="<?php echo esc_attr($klijent_data['odluka_broj']); ?>">
                            </div>
                            <div class="aktopr-form-group">
                                <label>Datum odluke</label>
                                <input type="date" name="podaci[odluka_datum]" value="<?php echo esc_attr($klijent_data['odluka_datum']); ?>">
                            </div>
                        </div>
                        
                        <h3>Stručno lice za procenu rizika</h3>
                        <div class="aktopr-form-grid">
                            <div class="aktopr-form-group">
                                <label>Ime i prezime</label>
                                <input type="text" name="podaci[strucno_lice]" value="<?php echo esc_attr($klijent_data['strucno_lice']); ?>">
                            </div>
                            <div class="aktopr-form-group">
                                <label>Broj licence</label>
                                <input type="text" name="podaci[broj_licence]" value="<?php echo esc_attr($klijent_data['broj_licence']); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="aktopr-panel-footer">
                        <button type="button" class="aktopr-btn aktopr-btn-primary" data-save-module="1">
                            <span class="dashicons dashicons-saved"></span> Sačuvaj modul
                        </button>
                        <button type="button" class="aktopr-btn aktopr-btn-ai" data-ai-generate="1">
                            <span class="dashicons dashicons-art"></span> AI Generiši
                        </button>
                    </div>
                </section>
                
                <!-- MODUL 2: Radni proces -->
                <section class="aktopr-panel" data-panel="module_2" style="display: none;">
                    <div class="aktopr-panel-header">
                        <h2>2. Opis radnog procesa <span class="aktopr-section-tag">2.1-2.4</span></h2>
                    </div>
                    <div class="aktopr-panel-content">
                        <h3>Opis delatnosti</h3>
                        <div class="aktopr-form-group">
                            <label>Opis delatnosti i radnih procesa</label>
                            <textarea name="modul2[opis_delatnosti]" rows="4" placeholder="Opisite čime se firma bavi..."></textarea>
                        </div>
                        
                        <h3>Radno okruženje</h3>
                        <div class="aktopr-form-grid">
                            <div class="aktopr-form-group">
                                <label>Tip okruženja</label>
                                <select name="modul2[tip_okruzenja]">
                                    <option value="kancelarijski">Kancelarijski prostor</option>
                                    <option value="gradiliste">Gradilište</option>
                                    <option value="proizvodni">Proizvodni pogon</option>
                                    <option value="magacin">Magacin/Skladište</option>
                                    <option value="terenski">Terenski rad</option>
                                </select>
                            </div>
                            <div class="aktopr-form-group">
                                <label>Smenski rad</label>
                                <select name="modul2[smenski_rad]">
                                    <option value="ne">Ne</option>
                                    <option value="da_2">Da - 2 smene</option>
                                    <option value="da_3">Da - 3 smene</option>
                                </select>
                            </div>
                        </div>
                        
                        <h3>Sredstva za rad</h3>
                        <div class="aktopr-form-group">
                            <label>Mašine, alati i oprema koja se koristi</label>
                            <textarea name="modul2[sredstva_za_rad]" rows="3" placeholder="Navedite sredstva za rad..."></textarea>
                        </div>
                        
                        <h3>Lična zaštitna oprema (PPE)</h3>
                        <div class="aktopr-checkbox-grid">
                            <label><input type="checkbox" name="modul2[ppe][]" value="kaciga"> Zaštitna kaciga</label>
                            <label><input type="checkbox" name="modul2[ppe][]" value="naocare"> Zaštitne naočare</label>
                            <label><input type="checkbox" name="modul2[ppe][]" value="rukavice"> Zaštitne rukavice</label>
                            <label><input type="checkbox" name="modul2[ppe][]" value="cipele"> Zaštitna obuća</label>
                            <label><input type="checkbox" name="modul2[ppe][]" value="pojas"> Zaštitni pojas</label>
                            <label><input type="checkbox" name="modul2[ppe][]" value="maska"> Zaštitna maska</label>
                            <label><input type="checkbox" name="modul2[ppe][]" value="odelo"> Radno odelo</label>
                            <label><input type="checkbox" name="modul2[ppe][]" value="prsluk"> Prsluk vidljivosti</label>
                        </div>
                    </div>
                    <div class="aktopr-panel-footer">
                        <button type="button" class="aktopr-btn aktopr-btn-primary" data-save-module="2">
                            <span class="dashicons dashicons-saved"></span> Sačuvaj modul
                        </button>
                        <button type="button" class="aktopr-btn aktopr-btn-ai" data-ai-generate="2">
                            <span class="dashicons dashicons-art"></span> AI Generiši
                        </button>
                    </div>
                </section>
                
                <!-- MODUL 3: Radna mesta (NAJVAŽNIJI) -->
                <section class="aktopr-panel" data-panel="module_3" style="display: none;">
                    <div class="aktopr-panel-header">
                        <h2>3. Sistematizacija radnih mesta <span class="aktopr-section-tag">3.1-3.2.5</span></h2>
                        <p class="aktopr-panel-desc">Automatski presek zanimanja na osnovu unetih zaposlenih</p>
                    </div>
                    <div class="aktopr-panel-content">
                        
                        <div class="aktopr-zaposleni-summary">
                            <h3>Pregled zaposlenih</h3>
                            <div class="aktopr-zaposleni-tags">
                                <?php foreach ($zaposleni as $z): ?>
                                <span class="aktopr-tag">
                                    <?php echo esc_html($z['radno_mesto'] ?: $z['ime_prezime']); ?>
                                    <button type="button" class="aktopr-tag-remove" data-id="<?php echo esc_attr($z['id']); ?>">×</button>
                                </span>
                                <?php endforeach; ?>
                                <?php if (empty($zaposleni)): ?>
                                <span class="aktopr-empty-text">Nema unetih zaposlenih. Dodajte ih u panelu sa leve strane.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="aktopr-radna-mesta-section">
                            <div class="aktopr-section-header">
                                <h3>Definicija radnih mesta</h3>
                                <button type="button" class="aktopr-btn" id="aktopr-add-radno-mesto">+ Dodaj radno mesto</button>
                            </div>
                            
                            <div class="aktopr-radna-mesta-list">
                                <?php foreach ($radna_mesta as $rm): ?>
                                <div class="aktopr-radno-mesto-item" data-id="<?php echo esc_attr($rm['id']); ?>">
                                    <div class="aktopr-rm-header">
                                        <strong><?php echo esc_html($rm['naziv']); ?></strong>
                                        <span class="aktopr-rm-sifra"><?php echo esc_html($rm['sifra']); ?></span>
                                        <span class="aktopr-rm-grupa"><?php echo esc_html(ucfirst($rm['grupa'])); ?></span>
                                    </div>
                                    <div class="aktopr-rm-tags">
                                        <?php if ($rm['rad_na_visini']) echo '<span class="aktopr-tag-sm">Na visini</span>'; ?>
                                        <?php if ($rm['rad_sa_hemikalijama']) echo '<span class="aktopr-tag-sm">Hemikalije</span>'; ?>
                                        <?php if ($rm['rad_za_racunarom']) echo '<span class="aktopr-tag-sm">Računar</span>'; ?>
                                        <?php if ($rm['smenski_rad']) echo '<span class="aktopr-tag-sm">Smenski</span>'; ?>
                                        <?php if ($rm['nocni_rad']) echo '<span class="aktopr-tag-sm">Noćni</span>'; ?>
                                    </div>
                                    <div class="aktopr-rm-actions">
                                        <button type="button" class="aktopr-btn-sm aktopr-btn-edit-rm" data-id="<?php echo esc_attr($rm['id']); ?>">Uredi</button>
                                        <button type="button" class="aktopr-btn-sm aktopr-btn-delete-rm" data-id="<?php echo esc_attr($rm['id']); ?>">Obriši</button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <?php if (empty($radna_mesta)): ?>
                                <div class="aktopr-empty-state">
                                    <p>Nemate definisanih radnih mesta.</p>
                                    <p>Kliknite "Dodaj radno mesto" da započnete.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                    </div>
                    <div class="aktopr-panel-footer">
                        <button type="button" class="aktopr-btn aktopr-btn-primary" data-save-module="3">
                            <span class="dashicons dashicons-saved"></span> Sačuvaj modul
                        </button>
                        <button type="button" class="aktopr-btn aktopr-btn-generate" data-generate-section="3">
                            <span class="dashicons dashicons-update"></span> Generiši tabele
                        </button>
                    </div>
                </section>
                
                <!-- MODUL 4: Opasnosti -->
                <section class="aktopr-panel" data-panel="module_4" style="display: none;">
                    <div class="aktopr-panel-header">
                        <h2>4. Identifikacija opasnosti <span class="aktopr-section-tag">4.1-4.4</span></h2>
                    </div>
                    <div class="aktopr-panel-content">
                        <p>Izaberite grupe opasnosti koje su prisutne na radnim mestima:</p>
                        
                        <div class="aktopr-opasnosti-grid">
                            <div class="aktopr-opasnost-grupa">
                                <h4>Mehaničke</h4>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="pad_sa_visinom"> Pad sa visinom</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="udar_predmeta"> Udar predmeta</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="secenje"> Sečenje/ubod</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="gnjecenje"> Gnječenje</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="sruzavanje"> Surzavanje</label>
                            </div>
                            
                            <div class="aktopr-opasnost-grupa">
                                <h4>Električne</h4>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="el_udar"> Električni udar</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="kratki_spaj"> Kratki spoj</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="el_luk"> Električni luk</label>
                            </div>
                            
                            <div class="aktopr-opasnost-grupa">
                                <h4>Fizičke</h4>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="buka"> Buka</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="vibracije"> Vibracije</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="temperatura"> Temperatura</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="prljavstina"> Prašina</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="vlaga"> Neodgovarajuća vlaga</label>
                            </div>
                            
                            <div class="aktopr-opasnost-grupa">
                                <h4>Hemijske</h4>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="otrovi"> Otrovi</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="korozija"> Korozivne supstance</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="zapaljivo"> Zapaljive supstance</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="prašina_h"> Štetna prašina</label>
                            </div>
                            
                            <div class="aktopr-opasnost-grupa">
                                <h4>Ergonomske</h4>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="dizanje_tereta"> Dizanje tereta</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="monotonija"> Monotonija</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="sedanje"> Dugotrajno sedanje</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="staticki_napor"> Statički napor</label>
                            </div>
                            
                            <div class="aktopr-opasnost-grupa">
                                <h4>Psihosocijalne</h4>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="stres"> Stres</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="nocni_rad"> Noćni rad</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="smenski_rad_p"> Smenski rad</label>
                                <label><input type="checkbox" name="modul4[opasnosti][]" value="nasilje"> Nasilje na radu</label>
                            </div>
                        </div>
                    </div>
                    <div class="aktopr-panel-footer">
                        <button type="button" class="aktopr-btn aktopr-btn-primary" data-save-module="4">
                            <span class="dashicons dashicons-saved"></span> Sačuvaj modul
                        </button>
                        <button type="button" class="aktopr-btn aktopr-btn-ai" data-ai-generate="4">
                            <span class="dashicons dashicons-art"></span> AI Generiši
                        </button>
                    </div>
                </section>
                
                <!-- MODUL 5: Procena rizika -->
                <section class="aktopr-panel" data-panel="module_5" style="display: none;">
                    <div class="aktopr-panel-header">
                        <h2>5. Procena rizika <span class="aktopr-section-tag">5.1-5.3</span></h2>
                        <p class="aktopr-panel-desc">Kinney metoda: R = P × F × C</p>
                    </div>
                    <div class="aktopr-panel-content">
                        
                        <div class="aktopr-kinney-info">
                            <div class="aktopr-kinney-legend">
                                <span><strong>P</strong> = Verovatnoća (0.1-10)</span>
                                <span><strong>F</strong> = Učestalost (1-10)</span>
                                <span><strong>C</strong> = Težina posledica (1-40)</span>
                                <span><strong>R</strong> = Rizik (= P × F × C)</span>
                            </div>
                        </div>
                        
                        <div class="aktopr-risici-list" id="aktopr-risici-list">
                            <div class="aktopr-risik-item" data-index="0">
                                <div class="aktopr-form-grid">
                                    <div class="aktopr-form-group">
                                        <label>Opasnost</label>
                                        <select name="risik_opasnost[]">
                                            <option value="">-- Izaberi --</option>
                                            <option value="pad_sa_visinom">Pad sa visinom</option>
                                            <option value="udar_predmeta">Udar predmeta</option>
                                            <option value="el_udar">Električni udar</option>
                                            <option value="buka">Buka</option>
                                            <option value="prljavstina">Prašina</option>
                                            <option value="dizanje_tereta">Dizanje tereta</option>
                                            <option value="stres">Stres</option>
                                            <option value="vibracije">Vibracije</option>
                                            <option value="temperatura">Temperatura</option>
                                        </select>
                                    </div>
                                    <div class="aktopr-form-group">
                                        <label>Radno mesto</label>
                                        <input type="text" name="risik_radno_mesto[]" placeholder="Na kojem radnom mestu">
                                    </div>
                                </div>
                                <div class="aktopr-form-grid-3">
                                    <div class="aktopr-form-group">
                                        <label>P (Verovatnoća)</label>
                                        <select name="risik_p[]" class="aktopr-koef-p">
                                            <option value="0.5">0.5 - Gotovo nemoguće</option>
                                            <option value="1">1 - Moguće</option>
                                            <option value="3" selected>3 - Moguće</option>
                                            <option value="6">6 - Verovatno</option>
                                            <option value="10">10 - Gotovo sigurno</option>
                                        </select>
                                    </div>
                                    <div class="aktopr-form-group">
                                        <label>F (Učestalost)</label>
                                        <select name="risik_f[]" class="aktopr-koef-f">
                                            <option value="1">1 - Veoma retko</option>
                                            <option value="2">2 - Retko</option>
                                            <option value="3" selected>3 - Povremeno</option>
                                            <option value="6">6 - Često</option>
                                            <option value="10">10 - Konstantno</option>
                                        </select>
                                    </div>
                                    <div class="aktopr-form-group">
                                        <label>C (Težina)</label>
                                        <select name="risik_c[]" class="aktopr-koef-c">
                                            <option value="1">1 - Bez povrede</option>
                                            <option value="3" selected>3 - Laka povreda</option>
                                            <option value="7">7 - Teška povreda</option>
                                            <option value="15">15 - Veoma teška</option>
                                            <option value="40">40 - Smrt</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="aktopr-risik-result">
                                    <span>R = <span class="aktopr-r-formula">3 × 3 × 3</span> = </span>
                                    <strong class="aktopr-r-value">27</strong>
                                    <span class="aktopr-r-nivo aktopr-nivo-srednji">Srednji</span>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="aktopr-btn" id="aktopr-add-risik">+ Dodaj rizik</button>
                        
                    </div>
                    <div class="aktopr-panel-footer">
                        <button type="button" class="aktopr-btn aktopr-btn-primary" data-save-module="5">
                            <span class="dashicons dashicons-saved"></span> Sačuvaj modul
                        </button>
                    </div>
                </section>
                
                <!-- MODUL 6: Mere zaštite -->
                <section class="aktopr-panel" data-panel="module_6" style="display: none;">
                    <div class="aktopr-panel-header">
                        <h2>6. Mere zaštite <span class="aktopr-section-tag">6.1-6.4</span></h2>
                    </div>
                    <div class="aktopr-panel-content">
                        
                        <div id="aktopr-mere-list">
                            <div class="aktopr-mera-item" data-index="0">
                                <div class="aktopr-form-grid">
                                    <div class="aktopr-form-group">
                                        <label>Opasnost koju rešava</label>
                                        <select name="mera_opasnost[]">
                                            <option value="">-- Izaberi --</option>
                                            <option value="pad_sa_visinom">Pad sa visinom</option>
                                            <option value="udar_predmeta">Udar predmeta</option>
                                            <option value="el_udar">Električni udar</option>
                                            <option value="buka">Buka</option>
                                            <option value="dizanje_tereta">Dizanje tereta</option>
                                        </select>
                                    </div>
                                    <div class="aktopr-form-group">
                                        <label>Tip mere</label>
                                        <select name="mera_tip[]">
                                            <option value="tehnicka">Tehnička</option>
                                            <option value="organizaciona">Organizaciona</option>
                                            <option value="ppe">Lična zaštitna oprema</option>
                                            <option value="osposobljavanje">Osposobljavanje</option>
                                            <option value="higijenska">Higijensko-zdravstvena</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="aktopr-form-group">
                                    <label>Opis mere</label>
                                    <textarea name="mera_opis[]" rows="2" placeholder="Detaljan opis mere koju treba sprovesti..."></textarea>
                                </div>
                                <div class="aktopr-form-grid-3">
                                    <div class="aktopr-form-group">
                                        <label>Prioritet</label>
                                        <select name="mera_prioritet[]">
                                            <option value="visok">Visok</option>
                                            <option value="srednji" selected>Srednji</option>
                                            <option value="nizak">Nizak</option>
                                        </select>
                                    </div>
                                    <div class="aktopr-form-group">
                                        <label>Rok (dana)</label>
                                        <input type="number" name="mera_rok[]" value="30" min="1">
                                    </div>
                                    <div class="aktopr-form-group">
                                        <label>Zaduženo lice</label>
                                        <input type="text" name="mera_zaduzeno[]" placeholder="Ime ili funkcija">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="aktopr-btn" id="aktopr-add-mera">+ Dodaj meru</button>
                        
                    </div>
                    <div class="aktopr-panel-footer">
                        <button type="button" class="aktopr-btn aktopr-btn-primary" data-save-module="6">
                            <span class="dashicons dashicons-saved"></span> Sačuvaj modul
                        </button>
                    </div>
                </section>
                
                <!-- MODUL 7-10 (Simplified) -->
                <?php for ($m = 7; $m <= 10; $m++): ?>
                <section class="aktopr-panel" data-panel="module_<?php echo $m; ?>" style="display: none;">
                    <div class="aktopr-panel-header">
                        <h2><?php echo $m; ?>. <?php echo $this->modules[$m]['name']; ?> <span class="aktopr-section-tag"><?php echo $this->modules[$m]['sections']; ?></span></h2>
                    </div>
                    <div class="aktopr-panel-content">
                        <div class="aktopr-form-group">
                            <textarea name="modul<?php echo $m; ?>[sadrzaj]" rows="15" placeholder="Unesite sadržaj za ovu sekciju..."></textarea>
                        </div>
                    </div>
                    <div class="aktopr-panel-footer">
                        <button type="button" class="aktopr-btn aktopr-btn-primary" data-save-module="<?php echo $m; ?>">
                            <span class="dashicons dashicons-saved"></span> Sačuvaj modul
                        </button>
                        <button type="button" class="aktopr-btn aktopr-btn-ai" data-ai-generate="<?php echo $m; ?>">
                            <span class="dashicons dashicons-art"></span> AI Generiši
                        </button>
                    </div>
                </section>
                <?php endfor; ?>
                
                <!-- ZAPOSLENI PANEL -->
                <section class="aktopr-panel" data-panel="zaposleni" style="display: none;">
                    <div class="aktopr-panel-header">
                        <h2>Zaposleni</h2>
                        <button type="button" class="aktopr-btn aktopr-btn-primary" id="aktopr-add-zaposleni">+ Dodaj zaposlenog</button>
                    </div>
                    <div class="aktopr-panel-content">
                        <div class="aktopr-zaposleni-list">
                            <?php foreach ($zaposleni as $z): ?>
                            <div class="aktopr-zaposleni-item" data-id="<?php echo esc_attr($z['id']); ?>">
                                <div class="aktopr-z-info">
                                    <strong><?php echo esc_html($z['ime_prezime']); ?></strong>
                                    <span class="aktopr-z-radno"><?php echo esc_html($z['radno_mesto'] ?: '-'); ?></span>
                                </div>
                                <div class="aktopr-z-tags">
                                    <?php if ($z['smenski_rad']) echo '<span class="aktopr-tag-sm">Smenski</span>'; ?>
                                    <?php if ($z['nocni_rad']) echo '<span class="aktopr-tag-sm">Noćni</span>'; ?>
                                    <?php if ($z['terenski_rad']) echo '<span class="aktopr-tag-sm">Teren</span>'; ?>
                                </div>
                                <div class="aktopr-z-actions">
                                    <button type="button" class="aktopr-btn-sm aktopr-btn-edit-z" data-id="<?php echo esc_attr($z['id']); ?>">Uredi</button>
                                    <button type="button" class="aktopr-btn-sm aktopr-btn-delete-z" data-id="<?php echo esc_attr($z['id']); ?>">Obriši</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($zaposleni)): ?>
                            <div class="aktopr-empty-state">
                                <p>Nemate nijednog zaposlenog.</p>
                                <p>Dodajte prvog zaposlenog.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
                
                <!-- RADNA MESTA PANEL -->
                <section class="aktopr-panel" data-panel="radna_mesta" style="display: none;">
                    <div class="aktopr-panel-header">
                        <h2>Radna mesta</h2>
                        <button type="button" class="aktopr-btn aktopr-btn-primary" id="aktopr-add-radno-mesto-panel">+ Dodaj radno mesto</button>
                    </div>
                    <div class="aktopr-panel-content">
                        <div class="aktopr-radna-mesta-list">
                            <?php foreach ($radna_mesta as $rm): ?>
                            <div class="aktopr-radno-mesto-item" data-id="<?php echo esc_attr($rm['id']); ?>">
                                <div class="aktopr-rm-header">
                                    <strong><?php echo esc_html($rm['naziv']); ?></strong>
                                    <span class="aktopr-rm-sifra"><?php echo esc_html($rm['sifra']); ?></span>
                                    <span class="aktopr-rm-grupa"><?php echo esc_html(ucfirst($rm['grupa'])); ?></span>
                                </div>
                                <?php if (!empty($rm['opis_posla'])): ?>
                                <p class="aktopr-rm-opis"><?php echo esc_html(wp_trim_words($rm['opis_posla'], 20)); ?></p>
                                <?php endif; ?>
                                <div class="aktopr-rm-tags">
                                    <?php if ($rm['rad_na_visini']) echo '<span class="aktopr-tag-sm">Na visini</span>'; ?>
                                    <?php if ($rm['rad_sa_hemikalijama']) echo '<span class="aktopr-tag-sm">Hemikalije</span>'; ?>
                                    <?php if ($rm['rad_za_racunarom']) echo '<span class="aktopr-tag-sm">Računar</span>'; ?>
                                    <?php if ($rm['smenski_rad']) echo '<span class="aktopr-tag-sm">Smenski</span>'; ?>
                                    <?php if ($rm['nocni_rad']) echo '<span class="aktopr-tag-sm">Noćni</span>'; ?>
                                </div>
                                <div class="aktopr-rm-actions">
                                    <button type="button" class="aktopr-btn-sm aktopr-btn-edit-rm" data-id="<?php echo esc_attr($rm['id']); ?>">Uredi</button>
                                    <button type="button" class="aktopr-btn-sm aktopr-btn-delete-rm" data-id="<?php echo esc_attr($rm['id']); ?>">Obriši</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($radna_mesta)): ?>
                            <div class="aktopr-empty-state">
                                <p>Nemate definisanih radnih mesta.</p>
                                <p>Dodajte prvo radno mesto.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
                
                <!-- KLIJENTI PANEL -->
                <section class="aktopr-panel" data-panel="klijenti" style="display: none;">
                    <div class="aktopr-panel-header">
                        <h2>Klijenti</h2>
                        <button type="button" class="aktopr-btn aktopr-btn-primary" id="aktopr-add-klijent">+ Novi klijent</button>
                    </div>
                    <div class="aktopr-panel-content">
                        <div class="aktopr-klijenti-list" id="aktopr-klijenti-list">
                            <div class="aktopr-klijent-item" data-id="0">
                                <div class="aktopr-klijent-info">
                                    <strong>Demo klijent</strong>
                                    <span class="aktopr-klijent-delatnost">Građevinarstvo</span>
                                </div>
                                <div class="aktopr-klijent-meta">
                                    <span class="aktopr-klijent-pib">PIB: 123456789</span>
                                    <span class="aktopr-klijent-aktivi">0 aktova</span>
                                </div>
                                <div class="aktopr-klijent-actions">
                                    <button type="button" class="aktopr-btn-sm aktopr-btn-select-klijent" data-id="0">Izaberi</button>
                                    <button type="button" class="aktopr-btn-sm aktopr-btn-edit-klijent" data-id="0">Uredi</button>
                                </div>
                            </div>
                            <?php foreach (Auto_AktoPR_Database::get_svi_klijenti() as $k): ?>
                            <div class="aktopr-klijent-item" data-id="<?php echo esc_attr($k->ID); ?>">
                                <div class="aktopr-klijent-info">
                                    <strong><?php echo esc_html($k->post_title); ?></strong>
                                    <span class="aktopr-klijent-delatnost"><?php echo esc_html(get_post_meta($k->ID, 'aapr_tip_delatnosti', true) ?: 'Nije definisano'); ?></span>
                                </div>
                                <div class="aktopr-klijent-meta">
                                    <span class="aktopr-klijent-pib">PIB: <?php echo esc_html(get_post_meta($k->ID, 'aapr_pib', true) ?: '-'); ?></span>
                                    <span class="aktopr-klijent-aktivi"><?php echo count(Auto_AktoPR_Database::get_aktivi_za_klijenta($k->ID)); ?> aktova</span>
                                </div>
                                <div class="aktopr-klijent-actions">
                                    <button type="button" class="aktopr-btn-sm aktopr-btn-select-klijent" data-id="<?php echo esc_attr($k->ID); ?>">Izaberi</button>
                                    <button type="button" class="aktopr-btn-sm aktopr-btn-edit-klijent" data-id="<?php echo esc_attr($k->ID); ?>">Uredi</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
                
                <!-- BIBLIOTEKA PANEL -->
                <section class="aktopr-panel" data-panel="biblioteka" style="display: none;">
                    <div class="aktopr-panel-header">
                        <h2>Biblioteka</h2>
                    </div>
                    <div class="aktopr-panel-content">
                        <div class="aktopr-biblioteka-tabs">
                            <button type="button" class="aktopr-tab-btn active" data-tab="propisi">Propisi</button>
                            <button type="button" class="aktopr-tab-btn" data-tab="koeficijenti">Koeficijenti</button>
                            <button type="button" class="aktopr-tab-btn" data-tab="mere">Standardne mere</button>
                        </div>
                        
                        <div class="aktopr-biblioteka-content" id="biblioteka-propisi">
                            <table class="aktopr-table">
                                <thead>
                                    <tr>
                                        <th>Šifra</th>
                                        <th>Naziv</th>
                                        <th>Vrsta</th>
                                        <th>Datum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($propisi as $p): ?>
                                    <tr>
                                        <td><?php echo esc_html($p['sifra']); ?></td>
                                        <td><?php echo esc_html($p['naziv']); ?></td>
                                        <td><?php echo esc_html($p['vrsta']); ?></td>
                                        <td><?php echo esc_html($p['datum_objave']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
                
            </div>
        </main>
    </div>
    
    <!-- Modals -->
    <div class="aktopr-modal" id="aktopr-modal" style="display: none;">
        <div class="aktopr-modal-content">
            <button type="button" class="aktopr-modal-close">&times;</button>
            <div class="aktopr-modal-body"></div>
        </div>
    </div>
    
    <!-- Toast -->
    <div class="aktopr-toast" id="aktopr-toast" style="display: none;">
        <span class="aktopr-toast-message"></span>
    </div>
    
</div>
