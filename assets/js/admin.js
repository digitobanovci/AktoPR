(function($) {
    'use strict';

    $(document).ready(function() {
        initKlijenti();
        initZaposleni();
        initExport();
    });

    function initKlijenti() {
        $('#aapr-novi-klijent, #aapr-novi-klijent-inline').on('click', function(e) {
            e.preventDefault();
            $('#aapr-modal-title').text('Novi klijent');
            $('#aapr-klijent-form')[0].reset();
            $('#klijent-id').val('');
            $('#aapr-klijent-modal').show();
        });

        $(document).on('click', '.aapr-edit-klijent', function() {
            var id = $(this).data('id');
            $('#aapr-modal-title').text('Uredi klijenta');
            
            $.ajax({
                url: aaprAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aapr_get_klijent',
                    nonce: aaprAdmin.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        $('#klijent-id').val(response.data.id);
                        $('#klijent-naziv').val(response.data.naziv);
                        $('#klijent-pib').val(response.data.pib || '');
                        $('#klijent-adresa').val(response.data.adresa || '');
                        $('#klijent-telefon').val(response.data.telefon || '');
                        $('#klijent-email').val(response.data.email || '');
                        $('#klijent-delatnost').val(response.data.delatnost || '');
                        $('#klijent-broj-zaposlenih').val(response.data.broj_zaposlenih || '');
                        $('#klijent-napomene').val(response.data.napomene || '');
                        $('#aapr-klijent-modal').show();
                    }
                }
            });
        });

        $(document).on('click', '.aapr-delete-klijent', function() {
            if (!confirm(aaprAdmin.strings.confirm_delete)) return;
            
            var id = $(this).data('id');
            var row = $(this).closest('tr');
            
            $.ajax({
                url: aaprAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aapr_delete_klijent',
                    nonce: aaprAdmin.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        row.fadeOut(function() { $(this).remove(); });
                        showNotice('success', response.message);
                    } else {
                        showNotice('error', response.message);
                    }
                }
            });
        });

        $('#aapr-klijent-form').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            
            $.ajax({
                url: aaprAdmin.ajaxurl,
                type: 'POST',
                data: formData + '&action=aapr_save_klijent&nonce=' + aaprAdmin.nonce,
                success: function(response) {
                    if (response.success) {
                        $('#aapr-klijent-modal').hide();
                        location.reload();
                    } else {
                        showNotice('error', response.message);
                    }
                }
            });
        });

        $('.aapr-modal-close').on('click', function() {
            $(this).closest('.aapr-modal').hide();
        });
    }

    function initZaposleni() {
        $('#aapr-novi-zaposleni, #aapr-novi-zaposleni-inline').on('click', function(e) {
            e.preventDefault();
            $('#aapr-modal-title').text('Novi zaposleni');
            $('#aapr-zaposleni-form')[0].reset();
            $('#zaposleni-id').val('');
            $('#aapr-zaposleni-modal').show();
        });

        $(document).on('click', '.aapr-edit-zaposleni', function() {
            var id = $(this).data('id');
            $('#aapr-modal-title').text('Uredi zaposlenog');
            
            $.ajax({
                url: aaprAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aapr_get_zaposleni',
                    nonce: aaprAdmin.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        $('#zaposleni-id').val(response.data.id);
                        $('#zaposleni-ime').val(response.data.ime_prezime);
                        $('#zaposleni-klijent').val(response.data.klijent_id || '');
                        $('#zaposleni-jmbg').val(response.data.jmbg || '');
                        $('#zaposleni-radno-mesto').val(response.data.radno_mesto || '');
                        $('#zaposleni-smenski').prop('checked', response.data.smenski_rad == 1);
                        $('#zaposleni-nocni').prop('checked', response.data.nocni_rad == 1);
                        $('#zaposleni-terenski').prop('checked', response.data.terenski_rad == 1);
                        $('#zaposleni-datum').val(response.data.datum_zaposlenja || '');
                        $('#zaposleni-napomene').val(response.data.napomene || '');
                        $('#aapr-zaposleni-modal').show();
                    }
                }
            });
        });

        $(document).on('click', '.aapr-delete-zaposleni', function() {
            if (!confirm(aaprAdmin.strings.confirm_delete)) return;
            
            var id = $(this).data('id');
            var row = $(this).closest('tr');
            
            $.ajax({
                url: aaprAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aapr_delete_zaposleni',
                    nonce: aaprAdmin.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        row.fadeOut(function() { $(this).remove(); });
                        showNotice('success', response.message);
                    } else {
                        showNotice('error', response.message);
                    }
                }
            });
        });

        $('#aapr-zaposleni-form').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: aaprAdmin.ajaxurl,
                type: 'POST',
                data: $(this).serialize() + '&action=aapr_save_zaposleni&nonce=' + aaprAdmin.nonce,
                success: function(response) {
                    if (response.success) {
                        $('#aapr-zaposleni-modal').hide();
                        location.reload();
                    } else {
                        showNotice('error', response.message);
                    }
                }
            });
        });
    }

    function initExport() {
        $(document).on('click', '.aapr-export', function() {
            var id = $(this).data('id');
            
            var html = '<div style="padding: 20px;">';
            html += '<h3>Export dokumenta</h3>';
            html += '<p>Izaberite format:</p>';
            html += '<button class="button button-primary" id="export-docx" data-id="' + id + '">DOCX (Word)</button>';
            html += '<button class="button" id="export-pdf" data-id="' + id + '" style="margin-left: 10px;">PDF/Print</button>';
            html += '</div>';
            
            var dialog = $(html);
            dialog.appendTo('body').dialog({
                title: 'Export',
                width: 400,
                modal: true,
                close: function() { $(this).dialog('destroy').remove(); }
            });
        });

        $(document).on('click', '#export-docx', function() {
            var id = $(this).data('id');
            $('#export-docx, #export-pdf').prop('disabled', true).text('Generisanje...');
            
            var form = $('<form>', {
                action: aaprAdmin.ajaxurl,
                method: 'POST'
            }).hide();
            
            form.append($('<input>', { name: 'action', value: 'aapr_export_docx' }));
            form.append($('<input>', { name: 'nonce', value: aaprAdmin.nonce }));
            form.append($('<input>', { name: 'dokument_id', value: id }));
            
            $('body').append(form);
            form.submit();
        });
    }

    function showNotice(type, message) {
        var className = type === 'success' ? 'notice-success' : 'notice-error';
        var notice = $('<div class="notice ' + className + '"><p>' + message + '</p></div>');
        
        $('.wrap .aapr-wrap > h1').after(notice);
        
        setTimeout(function() {
            notice.fadeOut(function() { $(this).remove(); });
        }, 3000);
    }

})(jQuery);
