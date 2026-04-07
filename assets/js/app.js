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
            // Navigation
            $('.aktopr-nav-item[data-module]').on('click', (e) => this.showModule($(e.currentTarget)));
            $('.aktopr-nav-item[data-panel]').on('click', (e) => this.showPanel($(e.currentTarget).data('panel')));

            // Save module
            $('[data-save-module]').on('click', (e) => this.saveModule($(e.currentTarget).data('save-module')));

            // AI generate
            $('[data-ai-generate]').on('click', (e) => this.aiGenerate($(e.currentTarget).data('ai-generate')));

            // New client buttons
            $('#aktopr-welcome-new, #aktopr-new-client, #aktopr-edit-client, #aktopr-add-klijent').on('click', () => this.showClientModal());

            // Select klijent
            $(document).on('click', '.aktopr-btn-select-klijent', (e) => this.selectKlijent($(e.currentTarget).data('id')));
            $(document).on('click', '.aktopr-btn-edit-klijent', (e) => this.editKlijent($(e.currentTarget).data('id')));

            // Add buttons
            $('#aktopr-add-zaposleni').on('click', () => this.showZaposleniModal());
            $('#aktopr-add-radno-mesto, #aktopr-add-radno-mesto-panel').on('click', () => this.showRadnoMestoModal());
            $('#aktopr-add-risik').on('click', () => this.addRisik());
            $('#aktopr-add-mera').on('click', () => this.addMera());

            // Risik calculation
            $(document).on('change', '.aktopr-koef-p, .aktopr-koef-f, .aktopr-koef-c', (e) => this.calculateRisik($(e.target)));

            // Delete buttons
            $(document).on('click', '.aktopr-btn-delete-z, .aktopr-btn-delete-rm', (e) => this.deleteItem($(e.currentTarget)));

            // Edit buttons
            $(document).on('click', '.aktopr-btn-edit-z', (e) => this.editZaposleni($(e.currentTarget).data('id')));
            $(document).on('click', '.aktopr-btn-edit-rm', (e) => this.editRadnoMesto($(e.currentTarget).data('id')));

            // Generate document
            $('#aktopr-generate-doc').on('click', () => this.generateDocument());

            // Modal close
            $('.aktopr-modal-close, .aktopr-modal').on('click', (e) => {
                if (e.target === e.currentTarget) this.closeModal();
            });

            // Library tabs
            $('.aktopr-tab-btn').on('click', (e) => this.showLibraryTab($(e.currentTarget).data('tab')));
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
                        <input type="text" name="naziv" required placeholder="Naziv preduzeća">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>PIB</label>
                            <input type="text" name="pib" placeholder="9 cifara">
                        </div>
                        <div class="aktopr-form-group">
                            <label>Matični broj (MB)</label>
                            <input type="text" name="maticni_broj" placeholder="8 cifara">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Adresa sedišta</label>
                        <input type="text" name="adresa" placeholder="Улица и број, Поштански број, Град">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>Телефон 1</label>
                            <input type="tel" name="telefon" placeholder="+381...">
                        </div>
                        <div class="aktopr-form-group">
                            <label>Телефон 2</label>
                            <input type="tel" name="telefon2" placeholder="+381...">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="email@firma.rs">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>Тип делатности</label>
                            <select name="tip_delatnosti">
                                <option value="kancelarijski">Канцеларијски</option>
                                <option value="gradjevinski">Грађевински</option>
                                <option value="proizvodnja">Производња</option>
                                <option value="usluge">Услуге</option>
                                <option value="mesovito">Мешовито</option>
                            </select>
                        </div>
                        <div class="aktopr-form-group">
                            <label>Шифра делатности</label>
                            <input type="text" name="sifra_delatnosti" placeholder="npr. 4120">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Одговорно лице</label>
                        <input type="text" name="odgovorno_lice" placeholder="Име и презиме">
                    </div>
                    <div class="aktopr-form-group">
                        <label>Веб сајт</label>
                        <input type="url" name="web_sajt" placeholder="https://...">
                    </div>
                    <button type="submit" class="aktopr-btn aktopr-btn-primary" style="width: 100%; margin-top: 15px;">
                        Сачувај
                    </button>
                </form>
            `;
            
            modal.find('.aktopr-modal-body').html(html);
            modal.show();

            $('#aktopr-client-form').on('submit', (e) => {
                e.preventDefault();
                this.saveClient();
            });
        },

        saveClient: function() {
            const formData = $('#aktopr-client-form').serialize();
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: formData + '&action=aktopr_save_klijent&nonce=' + aktoprData.nonce,
                success: (response) => {
                    if (response.success) {
                        this.showToast('Klijent sačuvan!');
                        this.closeModal();
                        location.reload();
                    } else {
                        this.showToast('Greška: ' + response.data.message, true);
                    }
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

            $('#aktopr-zaposleni-form').on('submit', (e) => {
                e.preventDefault();
                this.saveZaposleni();
            });
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

            $('#aktopr-radnomesto-form').on('submit', (e) => {
                e.preventDefault();
                this.saveRadnoMesto();
            });
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

            $('#aktopr-radnomesto-form').on('submit', (e) => {
                e.preventDefault();
                this.saveRadnoMesto();
            });
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

            $('#aktopr-zaposleni-form').on('submit', (e) => {
                e.preventDefault();
                this.saveZaposleni();
            });
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
                    if (response.success) {
                        const data = response.data.data;
                        $('#aktopr-app').find('.aktopr-client-name').text(data.naziv || 'Klijent');
                        $('#aktopr-app').find('.aktopr-header-client').append(
                            '<button type="button" class="aktopr-btn aktopr-btn-sm" id="aktopr-edit-client"><span class="dashicons dashicons-edit"></span></button>'
                        );
                        
                        this.klijentId = data.id;
                        location.reload();
                    }
                }
            });
        },

        editKlijent: function(id) {
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aktopr_get_klijent',
                    nonce: aktoprData.nonce,
                    id: id
                },
                success: (response) => {
                    if (response.success) {
                        this.showClientModalWithData(id, response.data.data);
                    }
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
                            <label>Matični broj (MB)</label>
                            <input type="text" name="maticni_broj" value="${data.maticni_broj || ''}">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Adresa sedišta</label>
                        <input type="text" name="adresa" value="${data.adresa || ''}">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>Телефон 1</label>
                            <input type="tel" name="telefon" value="${data.telefon || ''}">
                        </div>
                        <div class="aktopr-form-group">
                            <label>Телефон 2</label>
                            <input type="tel" name="telefon2" value="${data.telefon2 || ''}">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="${data.email || ''}">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>Тип делатности</label>
                            <select name="tip_delatnosti">
                                <option value="kancelarijski" ${data.tip_delatnosti === 'kancelarijski' ? 'selected' : ''}>Канцеларијски</option>
                                <option value="gradjevinski" ${data.tip_delatnosti === 'gradjevinski' ? 'selected' : ''}>Грађевински</option>
                                <option value="proizvodnja" ${data.tip_delatnosti === 'proizvodnja' ? 'selected' : ''}>Производња</option>
                                <option value="usluge" ${data.tip_delatnosti === 'usluge' ? 'selected' : ''}>Услуге</option>
                                <option value="mesovito" ${data.tip_delatnosti === 'mesovito' ? 'selected' : ''}>Мешовито</option>
                            </select>
                        </div>
                        <div class="aktopr-form-group">
                            <label>Шифра делатности</label>
                            <input type="text" name="sifra_delatnosti" value="${data.sifra_delatnosti || ''}">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Одговорно лице</label>
                        <input type="text" name="odgovorno_lice" value="${data.odgovorno_lice || ''}">
                    </div>
                    <div class="aktopr-form-group">
                        <label>Веб сајт</label>
                        <input type="url" name="web_sajt" value="${data.web_sajt || ''}">
                    </div>
                    <button type="submit" class="aktopr-btn aktopr-btn-primary" style="width: 100%; margin-top: 15px;">
                        Сачувај
                    </button>
                </form>
            `;
            
            modal.find('.aktopr-modal-body').html(html);
            modal.show();

            $('#aktopr-client-form').on('submit', (e) => {
                e.preventDefault();
                this.saveClient();
            });
        },

        showWizardModal: function() {
            if (this.klijentId === 0) {
                this.showToast('Prvo izaberite klijenta!', true);
                return;
            }
            
            const modal = $('#aktopr-modal');
            
            let html = `
                <h2>Креирај нови Акт о процени ризика</h2>
                <p class="aktopr-wizard-intro">Попуните основне податке за нови акт.</p>
                <form id="aktopr-wizard-form">
                    <div class="aktopr-form-group">
                        <label>Назив акта *</label>
                        <input type="text" name="naziv" required value="Акт о процени ризика" placeholder="Назив документа">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>Број акта</label>
                            <input type="text" name="broj" placeholder="нпр. АПР-001/2024">
                        </div>
                        <div class="aktopr-form-group">
                            <label>Датум израде</label>
                            <input type="date" name="datum_izrade" value="${new Date().toISOString().split('T')[0]}">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Датум ступања на снагу</label>
                        <input type="date" name="datum_stupanja" placeholder="Када акт ступа на снагу">
                    </div>
                    <div class="aktopr-wizard-summary">
                        <h4>Подаци о клијенту:</h4>
                        <p>Клијент: <strong>${$('#aktopr-app').find('.aktopr-client-name').text()}</strong></p>
                        <p>Систем ће аутоматски:</p>
                        <ul>
                            <li>Преузети све запослене за овог клијента</li>
                            <li>Учитати делатност и шифру делатности</li>
                            <li>Попунити податке из претходних модула</li>
                        </ul>
                    </div>
                    <input type="hidden" name="klijent_id" value="${this.klijentId}">
                    <button type="submit" class="aktopr-btn aktopr-btn-primary" style="width: 100%; margin-top: 15px;">
                        Креирај акт
                    </button>
                </form>
            `;
            
            modal.find('.aktopr-modal-body').html(html);
            modal.show();

            $('#aktopr-wizard-form').on('submit', (e) => {
                e.preventDefault();
                this.kreirajAkt();
            });
        },

        kreirajAkt: function() {
            const formData = $('#aktopr-wizard-form').serialize();
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: formData + '&action=aktopr_kreiraj_novi_akt&nonce=' + aktoprData.nonce,
                success: (response) => {
                    if (response.success) {
                        this.showToast('Акт креиран!');
                        this.closeModal();
                        this.showModule($('.aktopr-nav-item[data-module="1"]'));
                    } else {
                        this.showToast('Грешка: ' + response.data.message, true);
                    }
                }
            });
        }
    };

})(jQuery);
