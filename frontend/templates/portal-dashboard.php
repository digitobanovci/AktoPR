<?php if (!defined('ABSPATH')) exit; ?>

<div class="aktopr-portal aktopr-dashboard">
    <div class="aktopr-dashboard-header">
        <div class="aktopr-header-content">
            <h1>Dobrodošli, <?php echo esc_html($user->display_name ?: $user->user_login); ?></h1>
            <p>Upravljajte vašim podacima i dokumentima</p>
        </div>
        <div class="aktopr-header-actions">
            <a href="[auto_aktopr]" class="aktopr-btn aktopr-btn-secondary aktopr-wizard-link">Novi dokument</a>
            <button type="button" class="aktopr-btn aktopr-btn-outline" id="aktopr-logout">Odjava</button>
        </div>
    </div>
    
    <?php if ($klijent): ?>
    <div class="aktopr-klijent-info">
        <h2><?php echo esc_html($klijent->post_title); ?></h2>
        <div class="aktopr-info-grid">
            <?php if ($pib = get_post_meta($klijent->ID, 'aapr_pib', true)): ?>
            <div class="aktopr-info-item">
                <span class="aktopr-info-label">PIB:</span>
                <span class="aktopr-info-value"><?php echo esc_html($pib); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($adresa = get_post_meta($klijent->ID, 'aapr_adresa', true)): ?>
            <div class="aktopr-info-item">
                <span class="aktopr-info-label">Adresa:</span>
                <span class="aktopr-info-value"><?php echo esc_html($adresa); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($telefon = get_post_meta($klijent->ID, 'aapr_telefon', true)): ?>
            <div class="aktopr-info-item">
                <span class="aktopr-info-label">Telefon:</span>
                <span class="aktopr-info-value"><?php echo esc_html($telefon); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($delatnost = get_post_meta($klijent->ID, 'aapr_delatnost', true)): ?>
            <div class="aktopr-info-item">
                <span class="aktopr-info-label">Delatnost:</span>
                <span class="aktopr-info-value"><?php echo esc_html($delatnost); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="aktopr-dashboard-tabs">
        <button type="button" class="aktopr-tab active" data-tab="zaposleni">
            <span class="aktopr-tab-icon">👥</span>
            Zaposleni <span class="aktopr-count">(<?php echo count($zaposleni); ?>)</span>
        </button>
        <button type="button" class="aktopr-tab" data-tab="radna_mesta">
            <span class="aktopr-tab-icon">🏭</span>
            Radna mesta <span class="aktopr-count">(<?php echo count($radna_mesta); ?>)</span>
        </button>
        <button type="button" class="aktopr-tab" data-tab="dokumenti">
            <span class="aktopr-tab-icon">📄</span>
            Dokumenti <span class="aktopr-count">(<?php echo count($dokumenti); ?>)</span>
        </button>
    </div>
    
    <div class="aktopr-tab-content active" id="tab-zaposleni">
        <div class="aktopr-tab-header">
            <h3>Zaposleni</h3>
            <button type="button" class="aktopr-btn aktopr-btn-primary" id="aktopr-novi-zaposleni">+ Dodaj zaposlenog</button>
        </div>
        
        <?php if (empty($zaposleni)): ?>
        <div class="aktopr-empty">
            <p>Nemate nijednog zaposlenog.</p>
            <p>Dodajte prvog zaposlenog klikom na dugme iznad.</p>
        </div>
        <?php else: ?>
        <table class="aktopr-table">
            <thead>
                <tr>
                    <th>Ime i prezime</th>
                    <th>JMBG</th>
                    <th>Radno mesto</th>
                    <th>Način rada</th>
                    <th>Datum</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($zaposleni as $z): 
                    $smenski = get_post_meta($z->ID, 'aapr_smenski_rad', true);
                    $nocni = get_post_meta($z->ID, 'aapr_nocni_rad', true);
                    $terenski = get_post_meta($z->ID, 'aapr_terenski_rad', true);
                ?>
                <tr data-id="<?php echo esc_attr($z->ID); ?>">
                    <td><strong><?php echo esc_html($z->post_title); ?></strong></td>
                    <td><?php echo esc_html(get_post_meta($z->ID, 'aapr_jmbg', true) ?: '-'); ?></td>
                    <td><?php echo esc_html(get_post_meta($z->ID, 'aapr_radno_mesto', true) ?: '-'); ?></td>
                    <td>
                        <?php if ($smenski) echo '<span class="aktopr-badge">Smenski</span>'; ?>
                        <?php if ($nocni) echo '<span class="aktopr-badge">Noćni</span>'; ?>
                        <?php if ($terenski) echo '<span class="aktopr-badge">Teren</span>'; ?>
                        <?php if (!$smenski && !$nocni && !$terenski) echo '-'; ?>
                    </td>
                    <td><?php echo esc_html(get_post_meta($z->ID, 'aapr_datum_zaposlenja', true) ?: '-'); ?></td>
                    <td>
                        <button type="button" class="aktopr-btn-sm aktopr-btn-edit-zaposleni" data-id="<?php echo esc_attr($z->ID); ?>">Uredi</button>
                        <button type="button" class="aktopr-btn-sm aktopr-btn-delete-zaposleni" data-id="<?php echo esc_attr($z->ID); ?>">Obriši</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    
    <div class="aktopr-tab-content" id="tab-radna_mesta">
        <div class="aktopr-tab-header">
            <h3>Radna mesta</h3>
            <button type="button" class="aktopr-btn aktopr-btn-primary" id="aktopr-novo-radno-mesto">+ Dodaj radno mesto</button>
        </div>
        
        <?php if (empty($radna_mesta)): ?>
        <div class="aktopr-empty">
            <p>Nemate definisana radna mesta.</p>
            <p>Dodajte sistematizaciju radnih mesta klikom na dugme iznad.</p>
        </div>
        <?php else: ?>
        <table class="aktopr-table">
            <thead>
                <tr>
                    <th>Šifra</th>
                    <th>Naziv</th>
                    <th>Grupa</th>
                    <th>Uslovi</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($radna_mesta as $rm): ?>
                <tr data-id="<?php echo esc_attr($rm['id']); ?>">
                    <td><strong><?php echo esc_html($rm['sifra'] ?: '-'); ?></strong></td>
                    <td><?php echo esc_html($rm['naziv']); ?></td>
                    <td><span class="aktopr-badge"><?php echo esc_html(ucfirst(str_replace('_', ' ', $rm['grupa']))); ?></span></td>
                    <td>
                        <?php if ($rm['rad_na_visini']) echo '<span class="aktopr-badge">Na visini</span>'; ?>
                        <?php if ($rm['rad_sa_hemikalijama']) echo '<span class="aktopr-badge">Hemikalije</span>'; ?>
                        <?php if ($rm['rad_za_racunarom']) echo '<span class="aktopr-badge">Računar</span>'; ?>
                        <?php if ($rm['smenski_rad']) echo '<span class="aktopr-badge">Smenski</span>'; ?>
                        <?php if ($rm['nocni_rad']) echo '<span class="aktopr-badge">Noćni</span>'; ?>
                    </td>
                    <td>
                        <button type="button" class="aktopr-btn-sm aktopr-btn-edit-radno" data-id="<?php echo esc_attr($rm['id']); ?>">Uredi</button>
                        <button type="button" class="aktopr-btn-sm aktopr-btn-delete-radno" data-id="<?php echo esc_attr($rm['id']); ?>">Obriši</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    
    <div class="aktopr-tab-content" id="tab-dokumenti">
        <div class="aktopr-tab-header">
            <h3>Dokumenti</h3>
            <button type="button" class="aktopr-btn aktopr-btn-primary" id="aktopr-novi-dokument">+ Novi dokument</button>
        </div>
        
        <?php if (empty($dokumenti)): ?>
        <div class="aktopr-empty">
            <p>Nemate kreiranih dokumenata.</p>
            <p>Kreirajte Akt o proceni rizika klikom na dugme iznad.</p>
        </div>
        <?php else: ?>
        <table class="aktopr-table">
            <thead>
                <tr>
                    <th>Naziv dokumenta</th>
                    <th>Status</th>
                    <th>Verzija</th>
                    <th>Datum kreiranja</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dokumenti as $d): 
                    $status = get_post_meta($d->ID, 'aapr_status', true) ?: 'draft';
                    $verzija = get_post_meta($d->ID, 'aapr_verzija', true) ?: '1.0';
                    
                    $status_colors = [
                        'draft' => '#6c757d',
                        'u_pripremi' => '#ffc107',
                        'na_pregledu' => '#0d6efd',
                        'odobren' => '#28a745',
                        'objavljen' => '#6c757d',
                    ];
                ?>
                <tr>
                    <td><strong><?php echo esc_html($d->post_title); ?></strong></td>
                    <td>
                        <span class="aktopr-badge" style="background: <?php echo esc_attr($status_colors[$status] ?? '#6c757d'); ?>; color: white;">
                            <?php echo esc_html(ucfirst(str_replace('_', ' ', $status))); ?>
                        </span>
                    </td>
                    <td><?php echo esc_html($verzija); ?></td>
                    <td><?php echo esc_html(date('d.m.Y.', strtotime($d->post_date))); ?></td>
                    <td>
                        <a href="#" class="aktopr-btn-sm">Uredi</a>
                        <a href="#" class="aktopr-btn-sm">Preuzmi</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    
    <div class="aktopr-message" style="display: none;"></div>
</div>

<div class="aktopr-portal aktopr-modal" id="aktopr-zaposleni-modal" style="display: none;">
    <div class="aktopr-modal-content">
        <h2 id="aktopr-z-modal-title">Novi zaposleni</h2>
        <form id="aktopr-zaposleni-form">
            <input type="hidden" name="id" id="z-id" value="">
            <input type="hidden" name="klijent_id" id="z-klijent-id" value="<?php echo esc_attr($klijent ? $klijent->ID : 0); ?>">
            
            <div class="aktopr-form-group">
                <label for="z-ime">Ime i prezime *</label>
                <input type="text" id="z-ime" name="ime_prezime" required>
            </div>
            
            <div class="aktopr-form-group">
                <label for="z-jmbg">JMBG</label>
                <input type="text" id="z-jmbg" name="jmbg" maxlength="13" placeholder="13 cifara">
            </div>
            
            <div class="aktopr-form-group">
                <label for="z-radno">Radno mesto</label>
                <input type="text" id="z-radno" name="radno_mesto" placeholder="Na kojem radnom mestu radi">
            </div>
            
            <div class="aktopr-form-group">
                <label for="z-datum">Datum zaposlenja</label>
                <input type="date" id="z-datum" name="datum_zaposlenja">
            </div>
            
            <div class="aktopr-form-group">
                <label>Način rada:</label>
                <div class="aktopr-checkbox-group">
                    <label><input type="checkbox" name="smenski_rad" value="1"> Smenski rad</label>
                    <label><input type="checkbox" name="nocni_rad" value="1"> Noćni rad</label>
                    <label><input type="checkbox" name="terenski_rad" value="1"> Terenski rad</label>
                </div>
            </div>
            
            <div class="aktopr-form-group">
                <label for="z-napomene">Napomene</label>
                <textarea id="z-napomene" name="napomene" rows="3"></textarea>
            </div>
            
            <div class="aktopr-form-actions">
                <button type="button" class="aktopr-btn aktopr-btn-secondary aktopr-modal-close">Otkaži</button>
                <button type="submit" class="aktopr-btn aktopr-btn-primary">Sačuvaj</button>
            </div>
        </form>
    </div>
</div>

<div class="aktopr-portal aktopr-modal" id="aktopr-radno-mesto-modal" style="display: none;">
    <div class="aktopr-modal-content">
        <h2 id="aktopr-rm-modal-title">Novo radno mesto</h2>
        <form id="aktopr-radno-mesto-form">
            <input type="hidden" name="id" id="rm-id" value="">
            
            <div class="aktopr-form-group">
                <label for="rm-sifra">Šifra radnog mesta</label>
                <input type="text" id="rm-sifra" name="sifra" placeholder="npr. RM-01">
            </div>
            
            <div class="aktopr-form-group">
                <label for="rm-naziv">Naziv radnog mesta *</label>
                <input type="text" id="rm-naziv" name="naziv" required placeholder="npr. Građevinski radnik">
            </div>
            
            <div class="aktopr-form-group">
                <label for="rm-opis">Opis posla</label>
                <textarea id="rm-opis" name="opis_posla" rows="4" placeholder="Detaljan opis poslova na ovom radnom mestu..."></textarea>
            </div>
            
            <div class="aktopr-form-group">
                <label for="rm-grupa">Grupa:</label>
                <select id="rm-grupa" name="grupa">
                    <option value="gradjevinski">Građevinski</option>
                    <option value="elektro_masinski">Elektro/Mašinski</option>
                    <option value="administrativni">Administrativni</option>
                    <option value="ostalo">Ostalo</option>
                </select>
            </div>
            
            <div class="aktopr-form-group">
                <label>Specifični uslovi:</label>
                <div class="aktopr-checkbox-group">
                    <label><input type="checkbox" name="rad_na_visini" value="1"> Rad na visini</label>
                    <label><input type="checkbox" name="rad_sa_hemikalijama" value="1"> Rad sa hemikalijama</label>
                    <label><input type="checkbox" name="rad_za_racunarom" value="1"> Rad za računarom</label>
                    <label><input type="checkbox" name="smenski_rad" value="1"> Smenski rad</label>
                    <label><input type="checkbox" name="nocni_rad" value="1"> Noćni rad</label>
                </div>
            </div>
            
            <div class="aktopr-form-actions">
                <button type="button" class="aktopr-btn aktopr-btn-secondary aktopr-modal-close">Otkaži</button>
                <button type="submit" class="aktopr-btn aktopr-btn-primary">Sačuvaj</button>
            </div>
        </form>
    </div>
</div>
