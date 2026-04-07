(function($) {
    'use strict';

    $(document).ready(function() {
        AktoPRApp.init();
    });

    const AktoPRApp = {
        currentModule: null,
        klijentId: 0,

        init: function() {
            this.klijentId = parseInt($('#aktopr-app').data('klijent-id')) || 0;
            this.bindEvents();
            this.calculateProgress();
        },

        bindEvents: function() {
            const self = this;
            
            // Navigation - use document delegation
            $(document).on('click', '.aktopr-nav-item[data-module]', function(e) {
                e.preventDefault();
                self.showModule($(this));
            });
            
            $(document).on('click', '.aktopr-nav-item[data-panel]', function(e) {
                e.preventDefault();
                self.showPanel($(this).data('panel'));
            });

            // Save module
            $(document).on('click', '[data-save-module]', function(e) {
                e.preventDefault();
                self.saveModule($(this).data('save-module'));
            });

            // AI generate
            $(document).on('click', '[data-ai-generate]', function(e) {
                e.preventDefault();
                self.aiGenerate($(this).data('ai-generate'));
            });

            // New client buttons
            $(document).on('click', '#aktopr-welcome-new, #aktopr-new-client, #aktopr-edit-client, #aktopr-add-klijent', function(e) {
                e.preventDefault();
                self.showClientModal();
            });

            // Select klijent
            $(document).on('click', '.aktopr-btn-select-klijent', function(e) {
                e.preventDefault();
                self.selectKlijent($(this).data('id'));
            });
            
            $(document).on('click', '.aktopr-btn-edit-klijent', function(e) {
                e.preventDefault();
                self.editKlijent($(this).data('id'));
            });

            // Add buttons
            $(document).on('click', '#aktopr-add-zaposleni, #aktopr-add-zaposleni-panel', function(e) {
                e.preventDefault();
                self.showZaposleniModal();
            });
            
            $(document).on('click', '#aktopr-add-radno-mesto, #aktopr-add-radno-mesto-panel', function(e) {
                e.preventDefault();
                self.showRadnoMestoModal();
            });
            
            $(document).on('click', '#aktopr-add-risik', function(e) {
                e.preventDefault();
                self.addRisik();
            });
            
            $(document).on('click', '#aktopr-add-mera', function(e) {
                e.preventDefault();
                self.addMera();
            });

            // Risik calculation
            $(document).on('change', '.aktopr-koef-p, .aktopr-koef-f, .aktopr-koef-c', function(e) {
                self.calculateRisik($(this));
            });

            // Delete buttons
            $(document).on('click', '.aktopr-btn-delete-z, .aktopr-btn-delete-rm', function(e) {
                e.preventDefault();
                self.deleteItem($(this));
            });

            // Edit buttons
            $(document).on('click', '.aktopr-btn-edit-z', function(e) {
                e.preventDefault();
                self.editZaposleni($(this).data('id'));
            });
            
            $(document).on('click', '.aktopr-btn-edit-rm', function(e) {
                e.preventDefault();
                self.editRadnoMesto($(this).data('id'));
            });

            // Generate document
            $(document).on('click', '#aktopr-generate-doc', function(e) {
                e.preventDefault();
                self.generateDocument();
            });

            // Modal close
            $(document).on('click', '.aktopr-modal-close', function(e) {
                e.preventDefault();
                self.closeModal();
            });
            
            $(document).on('click', '#aktopr-modal', function(e) {
                if ($(e.target).is('#aktopr-modal')) {
                    self.closeModal();
                }
            });

            // Library tabs
            $(document).on('click', '.aktopr-tab-btn', function(e) {
                e.preventDefault();
                self.showLibraryTab($(this).data('tab'));
            });
            
            // Form submissions - use delegation
            $(document).on('submit', '#aktopr-client-form', function(e) {
                e.preventDefault();
                self.saveClient();
            });
            
            $(document).on('submit', '#aktopr-zaposleni-form', function(e) {
                e.preventDefault();
                self.saveZaposleni();
            });
            
            $(document).on('submit', '#aktopr-radnomesto-form', function(e) {
                e.preventDefault();
                self.saveRadnoMesto();
            });
            
            $(document).on('submit', '#aktopr-wizard-form', function(e) {
                e.preventDefault();
                self.kreirajAkt();
            });
        },

        showModule: function($btn) {
            const module = $btn.data('module');
            
            $('.aktopr-nav-item').removeClass('active');
            $btn.addClass('active');
            
            $('.aktopr-panel').hide();
            $(`.aktopr-panel[data-panel="module_${module}"]`).show();
            
            this.currentModule = module;
        },

        showPanel: function(panel) {
            $('.aktopr-nav-item').removeClass('active');
            $('.aktopr-panel').hide();
            $(`.aktopr-panel[data-panel="${panel}"]`).show();
            this.currentModule = null;
        },

        saveModule: function(module) {
            const $panel = $(`.aktopr-panel[data-panel="module_${module}"]`);
            const data = {};
            
            $panel.find('input, select, textarea').each(function() {
                const $el = $(this);
                const name = $el.attr('name');
                
                if (!name) return;
                
                if ($el.attr('type') === 'checkbox') {
                    if (!data[name]) data[name] = [];
                    if ($el.is(':checked')) {
                        data[name].push($el.val());
                    }
                } else {
                    data[name] = $el.val();
                }
            });

            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aktopr_save_module',
                    nonce: aktoprData.nonce,
                    klijent_id: this.klijentId,
                    module: module,
                    data: data
                },
                success: (response) => {
                    if (response.success) {
                        this.showToast('Modul sačuvan!');
                        this.markModuleComplete(module);
                        this.calculateProgress();
                    }
                }
            });
        },

        markModuleComplete: function(module) {
            const $navItem = $(`.aktopr-nav-item[data-module="${module}"]`);
            $navItem.addClass('completed');
            $navItem.find('.aktopr-nav-status').text('✓');
        },

        calculateProgress: function() {
            let completed = 0;
            let total = 10;

            $('.aktopr-nav-item[data-module]').each(function() {
                if ($(this).hasClass('completed')) {
                    completed++;
                }
            });

            const percent = Math.round((completed / total) * 100);
            $('.aktopr-progress-fill').css('width', percent + '%');
            $('.aktopr-progress-text').text(percent + '% kompletno');
        },

        aiGenerate: function(module) {
            const context = $(`.aktopr-panel[data-panel="module_${module}"] textarea`).first().val() || '';
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aktopr_ai_generate',
                    nonce: aktoprData.nonce,
                    module: module,
                    klijent_id: this.klijentId,
                    context: context
                },
                beforeSend: () => {
                    this.showToast('AI generiše...');
                },
                success: (response) => {
                    if (response.success) {
                        $(`.aktopr-panel[data-panel="module_${module}"] textarea`).first().val(response.data.tekst);
                        this.showToast('Tekst generisan!');
                    } else {
                        this.showToast('Greška: ' + response.data.message, true);
                    }
                }
            });
        },

        showClientModal: function() {
            const modal = $('#aktopr-modal');
            
            let html = `
                <h2>${this.klijentId > 0 ? 'Izmeni klijenta' : 'Novi klijent'}</h2>
                <form id="aktopr-client-form">
                    <input type="hidden" name="id" value="${this.klijentId}">
                    <div class="aktopr-form-group">
                        <label>Pun naziv firme *</label>
                        <input type="text" name="naziv" required placeholder="Naziv preduzeca">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>PIB</label>
                            <input type="text" name="pib" placeholder="9 cifara">
                        </div>
                        <div class="aktopr-form-group">
                            <label>Maticni broj (MB)</label>
                            <input type="text" name="maticni_broj" placeholder="8 cifara">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Adresa sedista</label>
                        <input type="text" name="adresa" placeholder="Ulica i broj, Postanski broj, Grad">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>Telefon 1</label>
                            <input type="tel" name="telefon" placeholder="+381...">
                        </div>
                        <div class="aktopr-form-group">
                            <label>Telefon 2</label>
                            <input type="tel" name="telefon2" placeholder="+381...">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="email@firma.rs">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>Tip delatnosti</label>
                            <select name="tip_delatnosti">
                                <option value="kancelarijski">Kancelarijski</option>
                                <option value="gradjevinski">Gradjevinski</option>
                                <option value="proizvodnja">Proizvodnja</option>
                                <option value="usluge">Usluge</option>
                                <option value="mesovito">Mesovito</option>
                            </select>
                        </div>
                        <div class="aktopr-form-group">
                            <label>Sifra delatnosti</label>
                            <input type="text" name="sifra_delatnosti" placeholder="npr. 4120">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Odgovorno lice</label>
                        <input type="text" name="odgovorno_lice" placeholder="Ime i prezime">
                    </div>
                    <div class="aktopr-form-group">
                        <label>Web sajt</label>
                        <input type="url" name="web_sajt" placeholder="https://...">
                    </div>
                    <button type="submit" class="aktopr-btn aktopr-btn-primary" style="width: 100%; margin-top: 15px;">
                        Sacuvaj
                    </button>
                </form>
            `;
            
            modal.find('.aktopr-modal-body').html(html);
            modal.show();
        },

        saveClient: function() {
            const formData = $('#aktopr-client-form').serializeArray();
            console.log('Form data:', formData);
            
            const data = {
                action: 'aktopr_save_klijent',
                nonce: aktoprData.nonce
            };
            
            $.each(formData, function(i, field) {
                data[field.name] = field.value;
            });
            
            console.log('Data to send:', data);
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: data,
                success: (response) => {
                    console.log('Success:', response);
                    if (response.success) {
                        this.showToast('Klijent sacuvan!');
                        this.closeModal();
                        location.reload();
                    } else {
                        this.showToast('Greska: ' + response.data.message, true);
                    }
                },
                error: (xhr, status, error) => {
                    console.log('Error:', status, error);
                    console.log('Response:', xhr.responseText);
                    this.showToast('Greska: ' + error, true);
                }
            });
        },

        showZaposleniModal: function(id = 0) {
            const modal = $('#aktopr-modal');
            
            let html = `
                <h2>${id > 0 ? 'Uredi zaposlenog' : 'Novi zaposleni'}</h2>
                <form id="aktopr-zaposleni-form">
                    <input type="hidden" name="id" value="${id}">
                    <input type="hidden" name="klijent_id" value="${this.klijentId}">
                    <div class="aktopr-form-group">
                        <label>Ime i prezime *</label>
                        <input type="text" name="ime_prezime" required placeholder="Ime i prezime">
                    </div>
                    <div class="aktopr-form-group">
                        <label>JMBG</label>
                        <input type="text" name="jmbg" maxlength="13" placeholder="13 cifara">
                    </div>
                    <div class="aktopr-form-group">
                        <label>Radno mesto / Zanimanje</label>
                        <input type="text" name="radno_mesto" placeholder="npr. Zidar, Električar, Programer">
                    </div>
                    <div class="aktopr-form-group">
                        <label>Način rada:</label>
                        <div style="display: flex; gap: 15px;">
                            <label><input type="checkbox" name="smenski_rad" value="1"> Smenski</label>
                            <label><input type="checkbox" name="nocni_rad" value="1"> Noćni</label>
                            <label><input type="checkbox" name="terenski_rad" value="1"> Teren</label>
                        </div>
                    </div>
                    <button type="submit" class="aktopr-btn aktopr-btn-primary" style="width: 100%; margin-top: 15px;">
                        Sačuvaj
                    </button>
                </form>
            `;
            
            modal.find('.aktopr-modal-body').html(html);
            modal.show();
        },

        saveZaposleni: function() {
            const formData = $('#aktopr-zaposleni-form').serialize();
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: formData + '&action=aktopr_save_zaposleni&nonce=' + aktoprData.nonce,
                success: (response) => {
                    if (response.success) {
                        this.showToast('Zaposleni sačuvan!');
                        this.closeModal();
                        location.reload();
                    }
                }
            });
        },

        editZaposleni: function(id) {
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aktopr_get_zaposleni',
                    nonce: aktoprData.nonce,
                    id: id
                },
                success: (response) => {
                    if (response.success) {
                        this.showZaposleniModalWithData(id, response.data);
                    }
                }
            });
        },

        editRadnoMesto: function(id) {
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aktopr_get_radno_mesto',
                    nonce: aktoprData.nonce,
                    id: id
                },
                success: (response) => {
                    if (response.success) {
                        this.showRadnoMestoModalWithData(id, response.data);
                    }
                }
            });
        },

        showRadnoMestoModal: function() {
            const modal = $('#aktopr-modal');
            
            let html = `
                <h2>Novo radno mesto</h2>
                <form id="aktopr-radnomesto-form">
                    <input type="hidden" name="id" value="0">
                    <input type="hidden" name="klijent_id" value="${this.klijentId}">
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>Naziv radnog mesta *</label>
                            <input type="text" name="naziv" required placeholder="npr. Građevinski radnik">
                        </div>
                        <div class="aktopr-form-group">
                            <label>Šifra</label>
                            <input type="text" name="sifra" placeholder="npr. RM-001">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Opis posla</label>
                        <textarea name="opis_posla" rows="3" placeholder="Opis radnih zadataka..."></textarea>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Grupa delatnosti</label>
                        <select name="grupa">
                            <option value="ostalo">Ostalo</option>
                            <option value="gradjevinski">Građevinski</option>
                            <option value="elektro_masinski">Elektro-mašinski</option>
                            <option value="administrativni">Administrativni</option>
                            <option value="gradiliste">Gradilište</option>
                        </select>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Specifični uslovi rada:</label>
                        <div class="aktopr-checkbox-grid">
                            <label><input type="checkbox" name="rad_na_visini" value="1"> Rad na visini</label>
                            <label><input type="checkbox" name="rad_sa_hemikalijama" value="1"> Rad sa hemikalijama</label>
                            <label><input type="checkbox" name="rad_za_racunarom" value="1"> Rad za računarom</label>
                            <label><input type="checkbox" name="smenski_rad" value="1"> Smenski rad</label>
                            <label><input type="checkbox" name="nocni_rad" value="1"> Noćni rad</label>
                        </div>
                    </div>
                    <button type="submit" class="aktopr-btn aktopr-btn-primary" style="width: 100%; margin-top: 15px;">
                        Sačuvaj
                    </button>
                </form>
            `;
            
            modal.find('.aktopr-modal-body').html(html);
            modal.show();
        },

        showRadnoMestoModalWithData: function(id, data) {
            const modal = $('#aktopr-modal');
            
            let html = `
                <h2>Uredi radno mesto</h2>
                <form id="aktopr-radnomesto-form">
                    <input type="hidden" name="id" value="${id}">
                    <input type="hidden" name="klijent_id" value="${this.klijentId}">
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>Naziv radnog mesta *</label>
                            <input type="text" name="naziv" required value="${data.naziv || ''}">
                        </div>
                        <div class="aktopr-form-group">
                            <label>Šifra</label>
                            <input type="text" name="sifra" value="${data.sifra || ''}">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Opis posla</label>
                        <textarea name="opis_posla" rows="3">${data.opis_posla || ''}</textarea>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Grupa delatnosti</label>
                        <select name="grupa">
                            <option value="ostalo" ${data.grupa === 'ostalo' ? 'selected' : ''}>Ostalo</option>
                            <option value="gradjevinski" ${data.grupa === 'gradjevinski' ? 'selected' : ''}>Građevinski</option>
                            <option value="elektro_masinski" ${data.grupa === 'elektro_masinski' ? 'selected' : ''}>Elektro-mašinski</option>
                            <option value="administrativni" ${data.grupa === 'administrativni' ? 'selected' : ''}>Administrativni</option>
                            <option value="gradiliste" ${data.grupa === 'gradiliste' ? 'selected' : ''}>Gradilište</option>
                        </select>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Specifični uslovi rada:</label>
                        <div class="aktopr-checkbox-grid">
                            <label><input type="checkbox" name="rad_na_visini" value="1" ${data.rad_na_visini == 1 ? 'checked' : ''}> Rad na visini</label>
                            <label><input type="checkbox" name="rad_sa_hemikalijama" value="1" ${data.rad_sa_hemikalijama == 1 ? 'checked' : ''}> Rad sa hemikalijama</label>
                            <label><input type="checkbox" name="rad_za_racunarom" value="1" ${data.rad_za_racunarom == 1 ? 'checked' : ''}> Rad za računarom</label>
                            <label><input type="checkbox" name="smenski_rad" value="1" ${data.smenski_rad == 1 ? 'checked' : ''}> Smenski rad</label>
                            <label><input type="checkbox" name="nocni_rad" value="1" ${data.nocni_rad == 1 ? 'checked' : ''}> Noćni rad</label>
                        </div>
                    </div>
                    <button type="submit" class="aktopr-btn aktopr-btn-primary" style="width: 100%; margin-top: 15px;">
                        Sačuvaj
                    </button>
                </form>
            `;
            
            modal.find('.aktopr-modal-body').html(html);
            modal.show();
        },

        saveRadnoMesto: function() {
            const formData = $('#aktopr-radnomesto-form').serialize();
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: formData + '&action=aktopr_save_radno_mesto&nonce=' + aktoprData.nonce,
                success: (response) => {
                    if (response.success) {
                        this.showToast('Radno mesto sačuvano!');
                        this.closeModal();
                        location.reload();
                    } else {
                        this.showToast('Greška: ' + response.data.message, true);
                    }
                }
            });
        },

        showZaposleniModalWithData: function(id, data) {
            const modal = $('#aktopr-modal');
            
            let html = `
                <h2>Uredi zaposlenog</h2>
                <form id="aktopr-zaposleni-form">
                    <input type="hidden" name="id" value="${id}">
                    <input type="hidden" name="klijent_id" value="${this.klijentId}">
                    <div class="aktopr-form-group">
                        <label>Ime i prezime *</label>
                        <input type="text" name="ime_prezime" required value="${data.ime_prezime || ''}">
                    </div>
                    <div class="aktopr-form-group">
                        <label>JMBG</label>
                        <input type="text" name="jmbg" maxlength="13" value="${data.jmbg || ''}">
                    </div>
                    <div class="aktopr-form-group">
                        <label>Radno mesto</label>
                        <input type="text" name="radno_mesto" value="${data.radno_mesto || ''}">
                    </div>
                    <div class="aktopr-form-group">
                        <label>Način rada:</label>
                        <div style="display: flex; gap: 15px;">
                            <label><input type="checkbox" name="smenski_rad" value="1" ${data.smenski_rad ? 'checked' : ''}> Smenski</label>
                            <label><input type="checkbox" name="nocni_rad" value="1" ${data.nocni_rad ? 'checked' : ''}> Noćni</label>
                            <label><input type="checkbox" name="terenski_rad" value="1" ${data.terenski_rad ? 'checked' : ''}> Teren</label>
                        </div>
                    </div>
                    <button type="submit" class="aktopr-btn aktopr-btn-primary" style="width: 100%; margin-top: 15px;">
                        Sačuvaj
                    </button>
                </form>
            `;
            
            modal.find('.aktopr-modal-body').html(html);
            modal.show();
        },

        deleteItem: function($btn) {
            if (!confirm('Da li ste sigurni?')) return;
            
            const id = $btn.data('id');
            const type = $btn.hasClass('aktopr-btn-delete-z') ? 'zaposleni' : 'radno_mesto';
            const action = type === 'zaposleni' ? 'aktopr_delete_zaposleni' : 'aktopr_delete_radno_mesto';
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: {
                    action: action,
                    nonce: aktoprData.nonce,
                    id: id
                },
                success: () => {
                    this.showToast('Obrisano!');
                    $btn.closest('.aktopr-zaposleni-item, .aktopr-radno-mesto-item').fadeOut();
                }
            });
        },

        addRisik: function() {
            const $list = $('#aktopr-risici-list');
            const $item = $list.find('.aktopr-risik-item').first().clone();
            
            $item.find('select').val('');
            $item.find('input').val('');
            $item.find('.aktopr-r-value').text('1');
            $item.find('.aktopr-r-formula').text('1 × 1 × 1');
            
            $list.append($item);
        },

        addMera: function() {
            const $list = $('#aktopr-mere-list');
            const $item = $list.find('.aktopr-mera-item').first().clone();
            
            $item.find('input, textarea, select').val('');
            
            $list.append($item);
        },

        calculateRisik: function($select) {
            const $item = $select.closest('.aktopr-risik-item');
            const p = parseFloat($item.find('.aktopr-koef-p').val()) || 1;
            const f = parseFloat($item.find('.aktopr-koef-f').val()) || 1;
            const c = parseFloat($item.find('.aktopr-koef-c').val()) || 1;
            
            const r = p * f * c;
            
            $item.find('.aktopr-r-formula').text(`${p} × ${f} × ${c}`);
            $item.find('.aktopr-r-value').text(r);
            
            const $nivo = $item.find('.aktopr-r-nivo');
            if (r >= 200) {
                $nivo.text('Visok').attr('class', 'aktopr-r-nivo aktopr-nivo-visok');
            } else if (r >= 70) {
                $nivo.text('Srednji').attr('class', 'aktopr-r-nivo aktopr-nivo-srednji');
            } else {
                $nivo.text('Nizak').attr('class', 'aktopr-r-nivo aktopr-nivo-nizak');
            }
        },

        generateDocument: function() {
            if (this.klijentId === 0) {
                this.showToast('Prvo kreirajte klijenta!', true);
                return;
            }
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aktopr_generate_document',
                    nonce: aktoprData.nonce,
                    klijent_id: this.klijentId
                },
                beforeSend: () => {
                    this.showToast('Generisanje dokumenta...');
                },
                success: (response) => {
                    if (response.success) {
                        const win = window.open('', '_blank');
                        win.document.write(response.data.html);
                        win.document.close();
                    }
                }
            });
        },

        showLibraryTab: function(tab) {
            $('.aktopr-tab-btn').removeClass('active');
            $(`.aktopr-tab-btn[data-tab="${tab}"]`).addClass('active');
        },

        closeModal: function() {
            $('#aktopr-modal').hide();
        },

        showToast: function(message, isError = false) {
            const $toast = $('#aktopr-toast');
            $toast.find('.aktopr-toast-message').text(message);
            $toast.css('background', isError ? '#d63638' : '#00a32a');
            $toast.show();
            
            setTimeout(() => {
                $toast.fadeOut();
            }, 3000);
        },

        selectKlijent: function(id) {
            console.log('selectKlijent called with id:', id);
            this.klijentId = id;
            $('#aktopr-app').data('klijent-id', id);
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aktopr_get_klijent',
                    nonce: aktoprData.nonce,
                    id: id
                },
                success: (response) => {
                    console.log('selectKlijent response:', response);
                    if (response.success) {
                        const data = response.data.data;
                        $('#aktopr-app').find('.aktopr-client-name').text(data.naziv || 'Klijent');
                        $('#aktopr-app').find('.aktopr-header-client').append(
                            '<button type="button" class="aktopr-btn aktopr-btn-sm" id="aktopr-edit-client"><span class="dashicons dashicons-edit"></span></button>'
                        );
                        
                        this.klijentId = data.id;
                        location.reload();
                    }
                },
                error: (xhr, status, error) => {
                    console.log('selectKlijent error:', status, error);
                }
            });
        },

        editKlijent: function(id) {
            console.log('editKlijent called with id:', id);
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aktopr_get_klijent',
                    nonce: aktoprData.nonce,
                    id: id
                },
                success: (response) => {
                    console.log('editKlijent response:', response);
                    if (response.success) {
                        this.showClientModalWithData(id, response.data.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.log('editKlijent error:', status, error);
                }
            });
        },

        showClientModalWithData: function(id, data) {
            const modal = $('#aktopr-modal');
            
            let html = `
                <h2>Uredi klijenta</h2>
                <form id="aktopr-client-form">
                    <input type="hidden" name="id" value="${id}">
                    <div class="aktopr-form-group">
                        <label>Pun naziv firme *</label>
                        <input type="text" name="naziv" required value="${data.naziv || ''}">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>PIB</label>
                            <input type="text" name="pib" value="${data.pib || ''}">
                        </div>
                        <div class="aktopr-form-group">
                            <label>Maticni broj (MB)</label>
                            <input type="text" name="maticni_broj" value="${data.maticni_broj || ''}">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Adresa sedista</label>
                        <input type="text" name="adresa" value="${data.adresa || ''}">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>Telefon 1</label>
                            <input type="tel" name="telefon" value="${data.telefon || ''}">
                        </div>
                        <div class="aktopr-form-group">
                            <label>Telefon 2</label>
                            <input type="tel" name="telefon2" value="${data.telefon2 || ''}">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="${data.email || ''}">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>Tip delatnosti</label>
                            <select name="tip_delatnosti">
                                <option value="kancelarijski" ${data.tip_delatnosti === 'kancelarijski' ? 'selected' : ''}>Kancelarijski</option>
                                <option value="gradjevinski" ${data.tip_delatnosti === 'gradjevinski' ? 'selected' : ''}>Gradjevinski</option>
                                <option value="proizvodnja" ${data.tip_delatnosti === 'proizvodnja' ? 'selected' : ''}>Proizvodnja</option>
                                <option value="usluge" ${data.tip_delatnosti === 'usluge' ? 'selected' : ''}>Usluge</option>
                                <option value="mesovito" ${data.tip_delatnosti === 'mesovito' ? 'selected' : ''}>Mesovito</option>
                            </select>
                        </div>
                        <div class="aktopr-form-group">
                            <label>Sifra delatnosti</label>
                            <input type="text" name="sifra_delatnosti" value="${data.sifra_delatnosti || ''}">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Odgovorno lice</label>
                        <input type="text" name="odgovorno_lice" value="${data.odgovorno_lice || ''}">
                    </div>
                    <div class="aktopr-form-group">
                        <label>Web sajt</label>
                        <input type="url" name="web_sajt" value="${data.web_sajt || ''}">
                    </div>
                    <button type="submit" class="aktopr-btn aktopr-btn-primary" style="width: 100%; margin-top: 15px;">
                        Sacuvaj
                    </button>
                </form>
            `;
            
            modal.find('.aktopr-modal-body').html(html);
            modal.show();
        },

        showWizardModal: function() {
            if (this.klijentId === 0) {
                this.showToast('Prvo izaberite klijenta!', true);
                return;
            }
            
            const modal = $('#aktopr-modal');
            
            let html = `
                <h2>Kreiraj novi Akt o proceni rizika</h2>
                <p class="aktopr-wizard-intro">Popunite osnovne podatke za novi akt.</p>
                <form id="aktopr-wizard-form">
                    <div class="aktopr-form-group">
                        <label>Naziv akta *</label>
                        <input type="text" name="naziv" required value="Akt o proceni rizika" placeholder="Naziv dokumenta">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>Broj akta</label>
                            <input type="text" name="broj" placeholder="npr. APR-001/2024">
                        </div>
                        <div class="aktopr-form-group">
                            <label>Datum izrade</label>
                            <input type="date" name="datum_izrade" value="${new Date().toISOString().split('T')[0]}">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Datum stupanja na snagu</label>
                        <input type="date" name="datum_stupanja" placeholder="Kada akt stupa na snagu">
                    </div>
                    <div class="aktopr-wizard-summary">
                        <h4>Podaci o klijentu:</h4>
                        <p>Klijent: <strong>${$('#aktopr-app').find('.aktopr-client-name').text()}</strong></p>
                        <p>Sistem ce automatski:</p>
                        <ul>
                            <li>Pretpostavljenje svih zaposlenih za ovog klijenta</li>
                            <li>Ucati delatnost i sifru delatnosti</li>
                            <li>Popuniti podatke iz prethodnih modula</li>
                        </ul>
                    </div>
                    <input type="hidden" name="klijent_id" value="${this.klijentId}">
                    <button type="submit" class="aktopr-btn aktopr-btn-primary" style="width: 100%; margin-top: 15px;">
                        Kreiraj akt
                    </button>
                </form>
            `;
            
            modal.find('.aktopr-modal-body').html(html);
            modal.show();
        },

        kreirajAkt: function() {
            const formData = $('#aktopr-wizard-form').serialize();
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: formData + '&action=aktopr_kreiraj_novi_akt&nonce=' + aktoprData.nonce,
                success: (response) => {
                    if (response.success) {
                        this.showToast('Akt kreiran!');
                        this.closeModal();
                        this.showModule($('.aktopr-nav-item[data-module="1"]'));
                    } else {
                        this.showToast('Greska: ' + response.data.message, true);
                    }
                }
            });
        }
    };

})(jQuery);
