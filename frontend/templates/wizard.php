<?php if (!defined('ABSPATH')) exit; ?>

<div class="aapr-wizard" id="aapr-wizard" data-dokument-id="<?php echo esc_attr($dokument_id); ?>" data-klijent-id="<?php echo esc_attr($klijent_id); ?>">
    
    <div class="aapr-wizard-header">
        <h1> Akt o proceni rizika</h1>
        <p class="aapr-subtitle">Popunite sve sekcije dokumenta korak po korak</p>
    </div>
    
    <div class="aapr-progress-bar">
        <div class="aapr-progress-fill" style="width: 10%;"></div>
    </div>
    
    <nav class="aapr-steps-nav">
        <button type="button" class="aapr-step-btn active" data-step="1">
            <span class="step-number">1</span>
            <span class="step-title">Osnovni podaci</span>
        </button>
        <button type="button" class="aapr-step-btn" data-step="2">
            <span class="step-number">2</span>
            <span class="step-title">Radni proces</span>
        </button>
        <button type="button" class="aapr-step-btn" data-step="3">
            <span class="step-number">3</span>
            <span class="step-title">Radna mesta</span>
        </button>
        <button type="button" class="aapr-step-btn" data-step="4">
            <span class="step-number">4</span>
            <span class="step-title">Opasnosti</span>
        </button>
        <button type="button" class="aapr-step-btn" data-step="5">
            <span class="step-number">5</span>
            <span class="step-title">Procena rizika</span>
        </button>
        <button type="button" class="aapr-step-btn" data-step="6">
            <span class="step-number">6</span>
            <span class="step-title">Mere zaštite</span>
        </button>
        <button type="button" class="aapr-step-btn" data-step="7">
            <span class="step-number">7</span>
            <span class="step-title">Izveštaji</span>
        </button>
        <button type="button" class="aapr-step-btn" data-step="8">
            <span class="step-number">8</span>
            <span class="step-title">Zaključak</span>
        </button>
    </nav>
    
    <div class="aapr-wizard-content">
        
        <!-- KORAK 1: Osnovni podaci -->
        <section class="aapr-step active" data-step="1">
            <h2>1. Osnovni podaci i uvod</h2>
            
            <div class="aapr-form-group">
                <label for="odluka_broj">Broj odluke *</label>
                <input type="text" id="odluka_broj" name="odluka_broj" required placeholder="npr. 01/2024">
            </div>
            
            <div class="aapr-form-group">
                <label for="odluka_datum">Datum odluke *</label>
                <input type="date" id="odluka_datum" name="odluka_datum" required>
            </div>
            
            <div class="aapr-form-group">
                <label for="firma_naziv">Naziv firme *</label>
                <input type="text" id="firma_naziv" name="firma_naziv" required placeholder="Pun naziv preduzeća">
            </div>
            
            <div class="aapr-form-group">
                <label for="firma_adresa">Adresa *</label>
                <input type="text" id="firma_adresa" name="firma_adresa" required placeholder="Ulica i broj, poštanski broj, grad">
            </div>
            
            <div class="aapr-form-group">
                <label for="firma_pib">PIB</label>
                <input type="text" id="firma_pib" name="firma_pib" placeholder="9 cifara">
            </div>
            
            <div class="aapr-form-group">
                <label for="firma_delatnost">Delatnost *</label>
                <input type="text" id="firma_delatnost" name="firma_delatnost" required placeholder="Opis delatnosti">
            </div>
            
            <div class="aapr-form-group">
                <label for="broj_zaposlenih">Ukupan broj zaposlenih *</label>
                <input type="number" id="broj_zaposlenih" name="broj_zaposlenih" min="1" required>
            </div>
            
            <div class="aapr-ai-helper">
                <button type="button" class="aapr-ai-btn" data-section="1">
                    <span class="dashicons dashicons-admin-generic"></span>
                    Generiši pomoću AI
                </button>
            </div>
        </section>
        
        <!-- KORAK 2: Radni proces -->
        <section class="aapr-step" data-step="2" style="display: none;">
            <h2>2. Opis radnog procesa</h2>
            
            <div class="aapr-form-group">
                <label for="opis_delatnosti">Opis delatnosti *</label>
                <textarea id="opis_delatnosti" name="opis_delatnosti" rows="5" required placeholder="Detaljan opis čime se firma bavi..."></textarea>
            </div>
            
            <div class="aapr-form-group">
                <label for="radni_proces">Opis radnog procesa *</label>
                <textarea id="radni_proces" name="radni_proces" rows="5" required placeholder="Kako se poslovi obavljaju, koje aktivnosti..."></textarea>
            </div>
            
            <div class="aapr-form-group">
                <label for="okruzenje">Radno okruženje</label>
                <select id="okruzenje" name="okruzenje">
                    <option value="zatvoreno">Zatvoreni prostor (kancelarija, hala)</option>
                    <option value="otvoreno">Otvoreni prostor (gradilište, spoljni radovi)</option>
                    <option value="kombinovano">Kombinovano</option>
                </select>
            </div>
            
            <div class="aapr-form-group">
                <label for="radno_vreme">Radno vreme</label>
                <select id="radno_vreme" name="radno_vreme">
                    <option value="redovno">Redovno (8h dnevno)</option>
                    <option value="smenski">Smenski rad</option>
                    <option value="prekovremeno">Sa prekovremenim</option>
                </select>
            </div>
        </section>
        
        <!-- KORAK 3: Radna mesta -->
        <section class="aapr-step" data-step="3" style="display: none;">
            <h2>3. Sistematizacija radnih mesta</h2>
            
            <div id="aapr-radna-mesta-list">
                <div class="aapr-radno-mesto" data-index="0">
                    <h4>Radno mesto #1</h4>
                    <div class="aapr-form-row">
                        <div class="aapr-form-group">
                            <label>Naziv radnog mesta *</label>
                            <input type="text" name="rm_naziv[]" required placeholder="npr. Rukovodilac građevinskih radova">
                        </div>
                        <div class="aapr-form-group">
                            <label>Šifra</label>
                            <input type="text" name="rm_sifra[]" placeholder="npr. RM-01">
                        </div>
                    </div>
                    <div class="aapr-form-group">
                        <label>Opis posla</label>
                        <textarea name="rm_opis[]" rows="3" placeholder="Šta radnik radi na ovom radnom mestu..."></textarea>
                    </div>
                    <div class="aapr-form-group">
                        <label>Broj izvršilaca</label>
                        <input type="number" name="rm_broj[]" min="1" value="1">
                    </div>
                </div>
            </div>
            
            <button type="button" class="button" id="aapr-dodaj-radno-mesto">+ Dodaj radno mesto</button>
        </section>
        
        <!-- KORAK 4: Opasnosti -->
        <section class="aapr-step" data-step="4" style="display: none;">
            <h2>4. Identifikacija opasnosti</h2>
            
            <div class="aapr-opasnosti-grid">
                <div class="aapr-opasnost-grupa">
                    <h4>Mehaničke opasnosti</h4>
                    <label><input type="checkbox" name="opasnosti[]" value="pad_sa_visine"> Pad sa visine</label>
                    <label><input type="checkbox" name="opasnosti[]" value="udar_predmeta"> Udar predmeta</label>
                    <label><input type="checkbox" name="opasnosti[]" value="secenje"> Sečenje/ubod</label>
                    <label><input type="checkbox" name="opasnosti[]" value="sruzavanje"> Sruzavanje</label>
                </div>
                
                <div class="aapr-opasnost-grupa">
                    <h4>Električne opasnosti</h4>
                    <label><input type="checkbox" name="opasnosti[]" value="el_udar"> Električni udar</label>
                    <label><input type="checkbox" name="opasnosti[]" value="kratki_spoj"> Kratki spoj</label>
                    <label><input type="checkbox" name="opasnosti[]" value="staticki_el"> Statički elektricitet</label>
                </div>
                
                <div class="aapr-opasnost-grupa">
                    <h4>Fizičke opasnosti</h4>
                    <label><input type="checkbox" name="opasnosti[]" value="buka"> Buka</label>
                    <label><input type="checkbox" name="opasnosti[]" value="vibracije"> Vibracije</label>
                    <label><input type="checkbox" name="opasnosti[]" value="temperatura"> Temperatura</label>
                    <label><input type="checkbox" name="opasnosti[]" value="prljavstina"> Prašina</label>
                </div>
                
                <div class="aapr-opasnost-grupa">
                    <h4>Hemijske opasnosti</h4>
                    <label><input type="checkbox" name="opasnosti[]" value="otrovi"> Otrovi</label>
                    <label><input type="checkbox" name="opasnosti[]" value="korozija"> Korozivne supstance</label>
                    <label><input type="checkbox" name="opasnosti[]" value="zapaljivo"> Zapaljive supstance</label>
                </div>
                
                <div class="aapr-opasnost-grupa">
                    <h4>Psihosocijalne</h4>
                    <label><input type="checkbox" name="opasnosti[]" value="stres"> Stres</label>
                    <label><input type="checkbox" name="opasnosti[]" value="ucesce"> Učesće javnosti</label>
                    <label><input type="checkbox" name="opasnosti[]" value="ucesce_javnosti"> Nasilje na radu</label>
                </div>
                
                <div class="aapr-opasnost-grupa">
                    <h4>Ergonomske</h4>
                    <label><input type="checkbox" name="opasnosti[]" value="dizanje_tereta"> Dizanje tereta</label>
                    <label><input type="checkbox" name="opasnosti[]" value="monotonija"> Monotonija</label>
                    <label><input type="checkbox" name="opasnosti[]" value="neaktivnost"> Dugotrajno sedenje</label>
                </div>
            </div>
            
            <div class="aapr-form-group">
                <label for="dodatne_opasnosti">Dodatne opasnosti (navedite)</label>
                <textarea id="dodatne_opasnosti" name="dodatne_opasnosti" rows="3" placeholder="Druge specifične opasnosti..."></textarea>
            </div>
        </section>
        
        <!-- KORAK 5: Procena rizika -->
        <section class="aapr-step" data-step="5" style="display: none;">
            <h2>5. Procena rizika (Kinney metoda)</h2>
            
            <p class="aapr-info-box">
                <strong>R = P × F × C</strong><br>
                R = Rizik | P = Verovatnoća | F = Učestalost | C = Težina posledica
            </p>
            
            <div id="aapr-risici-list">
                <div class="aapr-risik" data-index="0">
                    <h4>Rizik #1</h4>
                    <div class="aapr-form-row">
                        <div class="aapr-form-group">
                            <label>Opasnost *</label>
                            <select name="risik_opasnost[]" required>
                                <option value="">-- Izaberi --</option>
                                <option value="pad_sa_visine">Pad sa visine</option>
                                <option value="udar_predmeta">Udar predmeta</option>
                                <option value="el_udar">Električni udar</option>
                                <option value="buka">Buka</option>
                                <option value="prljavstina">Prašina</option>
                                <option value="dizanje_tereta">Dizanje tereta</option>
                                <option value="stres">Stres</option>
                            </select>
                        </div>
                        <div class="aapr-form-group">
                            <label>Radno mesto</label>
                            <input type="text" name="risik_rm[]" placeholder="Na kom radnom mestu">
                        </div>
                    </div>
                    
                    <div class="aapr-form-row">
                        <div class="aapr-form-group">
                            <label>P (Verovatnoća) *</label>
                            <select name="risik_p[]" required class="aapr-koef-p">
                                <option value="0.5">0.5 - Gotovo nemoguće</option>
                                <option value="1">1 - Moguće</option>
                                <option value="3">3 - Moguće</option>
                                <option value="6">6 - Verovatno</option>
                                <option value="10">10 - Gotovo sigurno</option>
                            </select>
                        </div>
                        <div class="aapr-form-group">
                            <label>F (Učestalost) *</label>
                            <select name="risik_f[]" required class="aapr-koef-f">
                                <option value="1">1 - Veoma retko</option>
                                <option value="2">2 - Retko</option>
                                <option value="3">3 - Povremeno</option>
                                <option value="6">6 - Često</option>
                                <option value="10">10 - Konstantno</option>
                            </select>
                        </div>
                        <div class="aapr-form-group">
                            <label>C (Težina) *</label>
                            <select name="risik_c[]" required class="aapr-koef-c">
                                <option value="1">1 - Bez povrede</option>
                                <option value="3">3 - Laka povreda</option>
                                <option value="7">7 - Teška povreda</option>
                                <option value="15">15 - Veoma teška</option>
                                <option value="40">40 - Smrt</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="aapr-risik-rezultat">
                        <span>R = <span class="aapr-r-formula">1 × 1 × 1</span> = </span>
                        <strong class="aapr-r-vrednost">1</strong>
                        <span class="aapr-r-nivo aapr-nivo-zanemarljiv">Zanemarljiv</span>
                    </div>
                </div>
            </div>
            
            <button type="button" class="button" id="aapr-dodaj-risik">+ Dodaj rizik</button>
        </section>
        
        <!-- KORAK 6: Mere zaštite -->
        <section class="aapr-step" data-step="6" style="display: none;">
            <h2>6. Mere zaštite</h2>
            
            <div id="aapr-mere-list">
                <div class="aapr-mera" data-index="0">
                    <h4>Mera #1</h4>
                    <div class="aapr-form-group">
                        <label>Naziv mere *</label>
                        <input type="text" name="mera_naziv[]" required placeholder="npr. Ugradnja zaštitne ograde">
                    </div>
                    <div class="aapr-form-row">
                        <div class="aapr-form-group">
                            <label>Tip mere</label>
                            <select name="mera_tip[]">
                                <option value="tehnicka">Tehnička</option>
                                <option value="organizaciona">Organizaciona</option>
                                <option value="ppe">Lična zaštitna oprema</option>
                                <option value="osposobljavanje">Osposobljavanje</option>
                            </select>
                        </div>
                        <div class="aapr-form-group">
                            <label>Rok (dana)</label>
                            <input type="number" name="mera_rok[]" min="1" value="30">
                        </div>
                    </div>
                    <div class="aapr-form-group">
                        <label>Prioritet</label>
                        <select name="mera_prioritet[]">
                            <option value="visok">Visok</option>
                            <option value="srednji" selected>Srednji</option>
                            <option value="nizak">Nizak</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <button type="button" class="button" id="aapr-dodaj-meru">+ Dodaj meru</button>
        </section>
        
        <!-- KORAK 7: Izveštaji -->
        <section class="aapr-step" data-step="7" style="display: none;">
            <h2>7. Evidencije i izveštaji</h2>
            
            <div class="aapr-form-group">
                <label for="evidencija_nesreca">Evidencija o povredama na radu</label>
                <textarea id="evidencija_nesreca" name="evidencija_nesreca" rows="4" placeholder="Evidencija o nezgodama i povredama na radu..."></textarea>
            </div>
            
            <div class="aapr-form-group">
                <label for="evidencija_bolesti">Evidencija o profesionalnim bolestima</label>
                <textarea id="evidencija_bolesti" name="evidencija_bolesti" rows="4" placeholder="Evidencija o profesionalnim bolestima..."></textarea>
            </div>
            
            <div class="aapr-form-group">
                <label for="zdravstveni_pregledi">Zdravstveni pregledi</label>
                <textarea id="zdravstveni_pregledi" name="zdravstveni_pregledi" rows="4" placeholder="Plan i realizacija zdravstvenih pregleda..."></textarea>
            </div>
        </section>
        
        <!-- KORAK 8: Zaključak -->
        <section class="aapr-step" data-step="8" style="display: none;">
            <h2>8. Zaključak</h2>
            
            <div class="aapr-form-group">
                <label for="zakljucak">Zaključak</label>
                <textarea id="zakljucak" name="zakljucak" rows="6" placeholder="Sažetak procene rizika i preporuke..."></textarea>
            </div>
            
            <div class="aapr-form-row">
                <div class="aapr-form-group">
                    <label for="izradio_ime">Izradio</label>
                    <input type="text" id="izradio_ime" name="izradio_ime" placeholder="Ime i prezime">
                </div>
                <div class="aapr-form-group">
                    <label for="pregledao_ime">Pregledao</label>
                    <input type="text" id="pregledao_ime" name="pregledao_ime" placeholder="Ime i prezime">
                </div>
            </div>
            
            <div class="aapr-form-group">
                <label for="odobrio_ime">Odobrio (poslodavac)</label>
                <input type="text" id="odobrio_ime" name="odobrio_ime" placeholder="Ime i prezime">
            </div>
        </section>
        
    </div>
    
    <div class="aapr-wizard-footer">
        <button type="button" class="button aapr-btn-prev" style="display: none;">← Nazad</button>
        <button type="button" class="button button-primary aapr-btn-next">Dalje →</button>
        <button type="button" class="button button-primary aapr-btn-save" style="display: none;">Sačuvaj dokument</button>
    </div>
    
</div>

<div id="aapr-ai-modal" style="display: none;">
    <div class="aapr-ai-content">
        <h3>AI Asistent</h3>
        <p>Generišem tekst za ovu sekciju...</p>
        <textarea id="aapr-ai-output" rows="10" readonly></textarea>
        <div class="aapr-ai-actions">
            <button type="button" class="button aapr-ai-insert">Umetni tekst</button>
            <button type="button" class="button aapr-ai-cancel">Otkaži</button>
        </div>
    </div>
</div>
