(function($) {
    'use strict';

    const AktoPRApp = {
        currentModule: null,
        klijentId: 0,

        init: function() {
            const $app = $('#aktopr-app');
            this.klijentId = parseInt($app.data('klijent-id')) || 0;
            this.bindEvents();
            this.calculateProgress();
            console.log('AktoPR App initialized, klijentId:', this.klijentId);
        },

        bindEvents: function() {
            const self = this;

            $(document).on('click', '.aktopr-nav-item[data-module]', function(e) {
                e.preventDefault();
                self.showModule($(this));
            });

            $(document).on('click', '.aktopr-nav-item[data-panel]', function(e) {
                e.preventDefault();
                self.showPanel($(this).data('panel'));
            });

            $(document).on('click', '[data-save-module]', function(e) {
                e.preventDefault();
                self.saveModule($(this).data('save-module'));
            });

            $(document).on('click', '[data-ai-generate]', function(e) {
                e.preventDefault();
                self.aiGenerate($(this).data('ai-generate'));
            });

            $(document).on('click', '#aktopr-welcome-new, #aktopr-new-client, #aktopr-edit-client, #aktopr-add-klijent', function(e) {
                e.preventDefault();
                self.showClientModal();
            });

            $(document).on('click', '.aktopr-btn-select-klijent', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                console.log('Select klijent clicked, id:', id);
                self.selectKlijent(id);
            });

            $(document).on('click', '.aktopr-btn-edit-klijent', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                console.log('Edit klijent clicked, id:', id);
                self.editKlijent(id);
            });

            $(document).on('click', '#aktopr-add-zaposleni, #aktopr-add-zaposleni-panel', function(e) {
                e.preventDefault();
                self.showZaposleniModal(0);
            });

            $(document).on('click', '#aktopr-add-radno-mesto, #aktopr-add-radno-mesto-panel', function(e) {
                e.preventDefault();
                self.showRadnoMestoModal(0);
            });

            $(document).on('click', '#aktopr-add-risik', function(e) {
                e.preventDefault();
                self.addRisik();
            });

            $(document).on('click', '#aktopr-add-mera', function(e) {
                e.preventDefault();
                self.addMera();
            });

            $(document).on('change', '.aktopr-koef-p, .aktopr-koef-f, .aktopr-koef-c', function(e) {
                self.calculateRisik($(this));
            });

            $(document).on('click', '.aktopr-btn-delete-z, .aktopr-btn-delete-rm', function(e) {
                e.preventDefault();
                self.deleteItem($(this));
            });

            $(document).on('click', '.aktopr-btn-edit-z', function(e) {
                e.preventDefault();
                self.editZaposleni($(this).data('id'));
            });

            $(document).on('click', '.aktopr-btn-edit-rm', function(e) {
                e.preventDefault();
                self.editRadnoMesto($(this).data('id'));
            });

            $(document).on('click', '#aktopr-generate-doc', function(e) {
                e.preventDefault();
                self.generateDocument();
            });

            $(document).on('click', '.aktopr-modal-close', function(e) {
                e.preventDefault();
                self.closeModal();
            });

            $(document).on('click', '#aktopr-modal', function(e) {
                if ($(e.target).is('#aktopr-modal')) {
                    self.closeModal();
                }
            });

            $(document).on('click', '.aktopr-tab-btn', function(e) {
                e.preventDefault();
                self.showLibraryTab($(this).data('tab'));
            });

            $(document).on('click', '#aktopr-save-module-1', function(e) {
                e.preventDefault();
                self.saveModule(1);
            });
        },

        showModule: function($btn) {
            const module = $btn.data('module');
            
            $('.aktopr-nav-item').removeClass('active');
            $btn.addClass('active');
            
            $('.aktopr-panel').hide();
            $(`.aktopr-panel[data-panel="module_${module}"]`).show();
            
            this.currentModule = module;
            console.log('Showing module:', module);
        },

        showPanel: function(panel) {
            $('.aktopr-nav-item').removeClass('active');
            $('.aktopr-panel').hide();
            $(`.aktopr-panel[data-panel="${panel}"]`).show();
            this.currentModule = null;
            console.log('Showing panel:', panel);
        },

        saveModule: function(module) {
            console.log('Saving module:', module);
            const $panel = $(`.aktopr-panel[data-panel="module_${module}"]`);
            const data = {};
            
            $panel.find('input, select, textarea').each(function() {
                const $el = $(this);
                const name = $el.attr('name');
                
                if (!name) return;
                
                if ($el.attr('type') === 'checkbox') {
                    data[name] = $el.is(':checked') ? '1' : '0';
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
                    console.log('Save module response:', response);
                    if (response.success) {
                        this.showToast('Modul sacuvan!');
                        this.markModuleComplete(module);
                        this.calculateProgress();
                    } else {
                        this.showToast('Greska: ' + (response.data?.message || 'Nepoznata greska'), true);
                    }
                }.bind(this),
                error: (xhr, status, error) => {
                    console.log('Save module error:', error);
                    this.showToast('Greska: ' + error, true);
                }.bind(this)
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
            console.log('AI generate for module:', module);
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
                    this.showToast('AI generise...');
                },
                success: (response) => {
                    if (response.success) {
                        $(`.aktopr-panel[data-panel="module_${module}"] textarea`).first().val(response.data.tekst);
                        this.showToast('Tekst generisan!');
                    } else {
                        this.showToast('Greska: ' + (response.data?.message || 'AI nije dostupan'), true);
                    }
                }
            });
        },

        showClientModal: function(id = 0, data = null) {
            const modal = $('#aktopr-modal');
            const isEdit = id > 0 || (data && data.id > 0);
            const clientData = data || {};
            const clientId = clientData.id || id || 0;
            
            let html = `
                <h2>${isEdit ? 'Uredi klijenta' : 'Novi klijent'}</h2>
                <form id="aktopr-client-form">
                    <input type="hidden" name="id" value="${clientId}">
                    <div class="aktopr-form-group">
                        <label>Pun naziv firme *</label>
                        <input type="text" name="naziv" required value="${clientData.naziv || ''}" placeholder="Naziv preduzeca">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>PIB</label>
                            <input type="text" name="pib" value="${clientData.pib || ''}" placeholder="9 cifara">
                        </div>
                        <div class="aktopr-form-group">
                            <label>Maticni broj (MB)</label>
                            <input type="text" name="maticni_broj" value="${clientData.maticni_broj || ''}" placeholder="8 cifara">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Adresa sedista</label>
                        <input type="text" name="adresa" value="${clientData.adresa || ''}" placeholder="Ulica i broj, Grad">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>Telefon 1</label>
                            <input type="tel" name="telefon" value="${clientData.telefon || ''}" placeholder="+381...">
                        </div>
                        <div class="aktopr-form-group">
                            <label>Telefon 2</label>
                            <input type="tel" name="telefon2" value="${clientData.telefon2 || ''}" placeholder="+381...">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="${clientData.email || ''}" placeholder="email@firma.rs">
                    </div>
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>Tip delatnosti</label>
                            <select name="tip_delatnosti">
                                <option value="kancelarijski" ${clientData.tip_delatnosti === 'kancelarijski' ? 'selected' : ''}>Kancelarijski</option>
                                <option value="gradjevinski" ${clientData.tip_delatnosti === 'gradjevinski' ? 'selected' : ''}>Gradjevinski</option>
                                <option value="proizvodnja" ${clientData.tip_delatnosti === 'proizvodnja' ? 'selected' : ''}>Proizvodnja</option>
                                <option value="usluge" ${clientData.tip_delatnosti === 'usluge' ? 'selected' : ''}>Usluge</option>
                                <option value="mesovito" ${clientData.tip_delatnosti === 'mesovito' ? 'selected' : ''}>Mesovito</option>
                            </select>
                        </div>
                        <div class="aktopr-form-group">
                            <label>Sifra delatnosti</label>
                            <input type="text" name="sifra_delatnosti" value="${clientData.sifra_delatnosti || ''}" placeholder="npr. 4120">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Odgovorno lice</label>
                        <input type="text" name="odgovorno_lice" value="${clientData.odgovorno_lice || ''}" placeholder="Ime i prezime">
                    </div>
                    <div class="aktopr-form-group">
                        <label>Web sajt</label>
                        <input type="url" name="web_sajt" value="${clientData.web_sajt || ''}" placeholder="https://...">
                    </div>
                    <button type="submit" class="aktopr-btn aktopr-btn-primary" style="width: 100%; margin-top: 15px;">
                        Sacuvaj
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
            const formData = $('#aktopr-client-form').serializeArray();
            console.log('Saving client, form data:', formData);
            
            const data = {
                action: 'aktopr_save_klijent',
                nonce: aktoprData.nonce
            };
            
            $.each(formData, function(i, field) {
                data[field.name] = field.value;
            });
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: data,
                success: (response) => {
                    console.log('Save client response:', response);
                    if (response.success) {
                        this.showToast('Klijent sacuvan!');
                        this.closeModal();
                        location.reload();
                    } else {
                        this.showToast('Greska: ' + (response.data?.message || 'Nepoznata greska'), true);
                    }
                }.bind(this),
                error: (xhr, status, error) => {
                    console.log('Save client error:', status, error, xhr.responseText);
                    this.showToast('Greska: ' + error, true);
                }.bind(this)
            });
        },

        selectKlijent: function(id) {
            console.log('selectKlijent called with id:', id);
            
            if (id === 0) {
                this.klijentId = 0;
                location.reload();
                return;
            }
            
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
                        this.showToast('Klijent izabran!');
                        location.reload();
                    } else {
                        this.showToast('Greska: ' + (response.data?.message || 'Klijent nije pronadjen'), true);
                    }
                }.bind(this),
                error: (xhr, status, error) => {
                    console.log('selectKlijent error:', status, error);
                    this.showToast('Greska: ' + error, true);
                }.bind(this)
            });
        },

        editKlijent: function(id) {
            console.log('editKlijent called with id:', id);
            
            if (id === 0) {
                this.showClientModal(0);
                return;
            }
            
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
                        this.showClientModal(id, response.data.data);
                    } else {
                        this.showToast('Greska: ' + (response.data?.message || 'Klijent nije pronadjen'), true);
                    }
                }.bind(this),
                error: (xhr, status, error) => {
                    console.log('editKlijent error:', status, error);
                    this.showToast('Greska: ' + error, true);
                }.bind(this)
            });
        },

        showZaposleniModal: function(id = 0, data = null) {
            const modal = $('#aktopr-modal');
            const zaposleniData = data || {};
            const zaposleniId = zaposleniData.ID || id || 0;
            const isEdit = zaposleniId > 0;
            
            let html = `
                <h2>${isEdit ? 'Uredi zaposlenog' : 'Novi zaposleni'}</h2>
                <form id="aktopr-zaposleni-form">
                    <input type="hidden" name="id" value="${zaposleniId}">
                    <input type="hidden" name="klijent_id" value="${this.klijentId}">
                    <div class="aktopr-form-group">
                        <label>Ime i prezime *</label>
                        <input type="text" name="ime_prezime" required value="${zaposleniData.ime_prezime || zaposleniData.post_title || ''}" placeholder="Ime i prezime">
                    </div>
                    <div class="aktopr-form-group">
                        <label>JMBG</label>
                        <input type="text" name="jmbg" maxlength="13" value="${zaposleniData.jmbg || ''}" placeholder="13 cifara">
                    </div>
                    <div class="aktopr-form-group">
                        <label>Radno mesto / Zanimanje</label>
                        <input type="text" name="radno_mesto" value="${zaposleniData.radno_mesto || ''}" placeholder="npr. Zidar, Elektricar">
                    </div>
                    <div class="aktopr-form-group">
                        <label>Nacin rada:</label>
                        <div style="display: flex; gap: 15px;">
                            <label><input type="checkbox" name="smenski_rad" value="1" ${zaposleniData.smenski_rad == 1 ? 'checked' : ''}> Smenski</label>
                            <label><input type="checkbox" name="nocni_rad" value="1" ${zaposleniData.nocni_rad == 1 ? 'checked' : ''}> Nocni</label>
                            <label><input type="checkbox" name="terenski_rad" value="1" ${zaposleniData.terenski_rad == 1 ? 'checked' : ''}> Teren</label>
                        </div>
                    </div>
                    <button type="submit" class="aktopr-btn aktopr-btn-primary" style="width: 100%; margin-top: 15px;">
                        Sacuvaj
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
            const formData = $('#aktopr-zaposleni-form').serializeArray();
            console.log('Saving zaposleni:', formData);
            
            const data = {
                action: 'aktopr_save_zaposleni',
                nonce: aktoprData.nonce
            };
            
            $.each(formData, function(i, field) {
                data[field.name] = field.value;
            });
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: data,
                success: (response) => {
                    console.log('Save zaposleni response:', response);
                    if (response.success) {
                        this.showToast('Zaposleni sacuvan!');
                        this.closeModal();
                        location.reload();
                    } else {
                        this.showToast('Greska: ' + (response.data?.message || 'Nepoznata greska'), true);
                    }
                }.bind(this),
                error: (xhr, status, error) => {
                    console.log('Save zaposleni error:', error);
                    this.showToast('Greska: ' + error, true);
                }.bind(this)
            });
        },

        editZaposleni: function(id) {
            console.log('editZaposleni called with id:', id);
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aktopr_get_zaposleni',
                    nonce: aktoprData.nonce,
                    id: id
                },
                success: (response) => {
                    console.log('editZaposleni response:', response);
                    if (response.success) {
                        this.showZaposleniModal(id, response.data);
                    } else {
                        this.showToast('Greska: ' + (response.data?.message || 'Zaposleni nije pronadjen'), true);
                    }
                }.bind(this),
                error: (xhr, status, error) => {
                    console.log('editZaposleni error:', error);
                    this.showToast('Greska: ' + error, true);
                }.bind(this)
            });
        },

        editRadnoMesto: function(id) {
            console.log('editRadnoMesto called with id:', id);
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aktopr_get_radno_mesto',
                    nonce: aktoprData.nonce,
                    id: id
                },
                success: (response) => {
                    console.log('editRadnoMesto response:', response);
                    if (response.success) {
                        this.showRadnoMestoModal(id, response.data);
                    } else {
                        this.showToast('Greska: ' + (response.data?.message || 'Radno mesto nije pronadjeno'), true);
                    }
                }.bind(this),
                error: (xhr, status, error) => {
                    console.log('editRadnoMesto error:', error);
                    this.showToast('Greska: ' + error, true);
                }.bind(this)
            });
        },

        showRadnoMestoModal: function(id = 0, data = null) {
            const modal = $('#aktopr-modal');
            const rmData = data || {};
            const rmId = rmData.id || id || 0;
            const isEdit = rmId > 0;
            
            let html = `
                <h2>${isEdit ? 'Uredi radno mesto' : 'Novo radno mesto'}</h2>
                <form id="aktopr-radnomesto-form">
                    <input type="hidden" name="id" value="${rmId}">
                    <input type="hidden" name="klijent_id" value="${this.klijentId}">
                    <div class="aktopr-form-grid">
                        <div class="aktopr-form-group">
                            <label>Naziv radnog mesta *</label>
                            <input type="text" name="naziv" required value="${rmData.naziv || ''}" placeholder="npr. Gradevinski radnik">
                        </div>
                        <div class="aktopr-form-group">
                            <label>Sifra</label>
                            <input type="text" name="sifra" value="${rmData.sifra || ''}" placeholder="npr. RM-001">
                        </div>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Opis posla</label>
                        <textarea name="opis_posla" rows="3" placeholder="Opis radnih zadataka...">${rmData.opis_posla || ''}</textarea>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Grupa delatnosti</label>
                        <select name="grupa">
                            <option value="ostalo" ${rmData.grupa === 'ostalo' ? 'selected' : ''}>Ostalo</option>
                            <option value="gradjevinski" ${rmData.grupa === 'gradjevinski' ? 'selected' : ''}>Gradjevinski</option>
                            <option value="elektro_masinski" ${rmData.grupa === 'elektro_masinski' ? 'selected' : ''}>Elektro-masinski</option>
                            <option value="administrativni" ${rmData.grupa === 'administrativni' ? 'selected' : ''}>Administrativni</option>
                            <option value="gradiliste" ${rmData.grupa === 'gradiliste' ? 'selected' : ''}>Gradiliste</option>
                        </select>
                    </div>
                    <div class="aktopr-form-group">
                        <label>Specificni uslovi rada:</label>
                        <div class="aktopr-checkbox-grid">
                            <label><input type="checkbox" name="rad_na_visini" value="1" ${rmData.rad_na_visini == 1 ? 'checked' : ''}> Rad na visini</label>
                            <label><input type="checkbox" name="rad_sa_hemikalijama" value="1" ${rmData.rad_sa_hemikalijama == 1 ? 'checked' : ''}> Rad sa hemikalijama</label>
                            <label><input type="checkbox" name="rad_za_racunarom" value="1" ${rmData.rad_za_racunarom == 1 ? 'checked' : ''}> Rad za racunarom</label>
                            <label><input type="checkbox" name="smenski_rad" value="1" ${rmData.smenski_rad == 1 ? 'checked' : ''}> Smenski rad</label>
                            <label><input type="checkbox" name="nocni_rad" value="1" ${rmData.nocni_rad == 1 ? 'checked' : ''}> Nocni rad</label>
                        </div>
                    </div>
                    <button type="submit" class="aktopr-btn aktopr-btn-primary" style="width: 100%; margin-top: 15px;">
                        Sacuvaj
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
            const formData = $('#aktopr-radnomesto-form').serializeArray();
            console.log('Saving radno mesto:', formData);
            
            const data = {
                action: 'aktopr_save_radno_mesto',
                nonce: aktoprData.nonce
            };
            
            $.each(formData, function(i, field) {
                data[field.name] = field.value;
            });
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: data,
                success: (response) => {
                    console.log('Save radno mesto response:', response);
                    if (response.success) {
                        this.showToast('Radno mesto sacuvano!');
                        this.closeModal();
                        location.reload();
                    } else {
                        this.showToast('Greska: ' + (response.data?.message || 'Nepoznata greska'), true);
                    }
                }.bind(this),
                error: (xhr, status, error) => {
                    console.log('Save radno mesto error:', error);
                    this.showToast('Greska: ' + error, true);
                }.bind(this)
            });
        },

        deleteItem: function($btn) {
            if (!confirm('Da li ste sigurni da zelite da obrisete?')) return;
            
            const id = $btn.data('id');
            const type = $btn.hasClass('aktopr-btn-delete-z') ? 'zaposleni' : 'radno_mesto';
            const action = type === 'zaposleni' ? 'aktopr_delete_zaposleni' : 'aktopr_delete_radno_mesto';
            
            console.log('Deleting', type, 'with id:', id);
            
            $.ajax({
                url: aktoprData.ajaxurl,
                type: 'POST',
                data: {
                    action: action,
                    nonce: aktoprData.nonce,
                    id: id
                },
                success: (response) => {
                    console.log('Delete response:', response);
                    if (response.success) {
                        this.showToast('Obrisano!');
                        $btn.closest('.aktopr-zaposleni-item, .aktopr-radno-mesto-item').fadeOut();
                    } else {
                        this.showToast('Greska: ' + (response.data?.message || 'Brisanje nije uspelo'), true);
                    }
                }.bind(this),
                error: (xhr, status, error) => {
                    console.log('Delete error:', error);
                    this.showToast('Greska: ' + error, true);
                }.bind(this)
            });
        },

        addRisik: function() {
            console.log('Adding risik');
            const $list = $('#aktopr-risici-list');
            const $item = $list.find('.aktopr-risik-item').first().clone();
            
            $item.find('select').val('');
            $item.find('input').val('');
            $item.find('.aktopr-r-value').text('1');
            $item.find('.aktopr-r-formula').text('1 x 1 x 1');
            
            $list.append($item);
            this.showToast('Dodat novi rizik');
        },

        addMera: function() {
            console.log('Adding mera');
            const $list = $('#aktopr-mere-list');
            const $item = $list.find('.aktopr-mera-item').first().clone();
            
            $item.find('input, textarea, select').val('');
            
            $list.append($item);
            this.showToast('Dodata nova mera');
        },

        calculateRisik: function($select) {
            const $item = $select.closest('.aktopr-risik-item');
            const p = parseFloat($item.find('.aktopr-koef-p').val()) || 1;
            const f = parseFloat($item.find('.aktopr-koef-f').val()) || 1;
            const c = parseFloat($item.find('.aktopr-koef-c').val()) || 1;
            
            const r = p * f * c;
            
            $item.find('.aktopr-r-formula').text(p + ' x ' + f + ' x ' + c);
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
            console.log('generateDocument called');
            if (this.klijentId === 0) {
                this.showToast('Prvo izaberite klijenta!', true);
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
                    console.log('Generate document response:', response);
                    if (response.success) {
                        const win = window.open('', '_blank');
                        win.document.write(response.data.html);
                        win.document.close();
                    } else {
                        this.showToast('Greska: ' + (response.data?.message || 'Generisanje nije uspelo'), true);
                    }
                }.bind(this),
                error: (xhr, status, error) => {
                    console.log('Generate document error:', error);
                    this.showToast('Greska: ' + error, true);
                }.bind(this)
            });
        },

        showLibraryTab: function(tab) {
            console.log('showLibraryTab:', tab);
            $('.aktopr-tab-btn').removeClass('active');
            $(`.aktopr-tab-btn[data-tab="${tab}"]`).addClass('active');
            
            $('#biblioteka-propisi, #biblioteka-koeficijenti, #biblioteka-mere').hide();
            $(`#biblioteka-${tab}`).show();
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
        }
    };

    $(document).ready(function() {
        if ($('#aktopr-app').length) {
            AktoPRApp.init();
        }
    });

    window.AktoPRApp = AktoPRApp;

})(jQuery);
