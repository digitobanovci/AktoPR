(function($) {
    'use strict';

    $(document).ready(function() {
        initPortal();
    });

    function initPortal() {
        initLogin();
        initRegister();
        initTabs();
        initZaposleni();
        initRadnaMesta();
        initLogout();
    }

    function initLogin() {
        $('#aktopr-login-form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            
            $btn.find('.btn-text').hide();
            $btn.find('.btn-loading').show();
            $btn.prop('disabled', true);
            
            $.ajax({
                url: aaprPortal.ajaxurl,
                type: 'POST',
                data: $form.serialize() + '&action=aapr_portal_login&nonce=' + aaprPortal.nonce,
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data.message);
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 1000);
                    } else {
                        showMessage('error', response.data.message);
                        $btn.find('.btn-text').show();
                        $btn.find('.btn-loading').hide();
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    showMessage('error', 'Greška pri komunikaciji sa serverom.');
                    $btn.find('.btn-text').show();
                    $btn.find('.btn-loading').hide();
                    $btn.prop('disabled', false);
                }
            });
        });
        
        $('#aktopr-show-register').on('click', function(e) {
            e.preventDefault();
            $('.aktopr-login').hide();
            $('.aktopr-register').show();
        });
        
        $('#aktopr-show-login').on('click', function(e) {
            e.preventDefault();
            $('.aktopr-register').hide();
            $('.aktopr-login').show();
        });
    }

    function initRegister() {
        $('#aktopr-register-form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            
            $btn.find('.btn-text').hide();
            $btn.find('.btn-loading').show();
            $btn.prop('disabled', true);
            
            $.ajax({
                url: aaprPortal.ajaxurl,
                type: 'POST',
                data: $form.serialize() + '&action=aapr_portal_register&nonce=' + aaprPortal.nonce,
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data.message);
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 1000);
                    } else {
                        showMessage('error', response.data.message);
                        $btn.find('.btn-text').show();
                        $btn.find('.btn-loading').hide();
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    showMessage('error', 'Greška pri komunikaciji sa serverom.');
                    $btn.find('.btn-text').show();
                    $btn.find('.btn-loading').hide();
                    $btn.prop('disabled', false);
                }
            });
        });
    }

    function initTabs() {
        $('.aktopr-tab').on('click', function() {
            var tab = $(this).data('tab');
            
            $('.aktopr-tab').removeClass('active');
            $(this).addClass('active');
            
            $('.aktopr-tab-content').removeClass('active');
            $('#tab-' + tab).addClass('active');
        });
    }

    function initZaposleni() {
        $('#aktopr-novi-zaposleni').on('click', function() {
            $('#aktopr-z-modal-title').text('Novi zaposleni');
            $('#aktopr-zaposleni-form')[0].reset();
            $('#z-id').val('');
            $('#aktopr-zaposleni-modal').show();
        });
        
        $(document).on('click', '.aktopr-btn-edit-zaposleni', function() {
            var id = $(this).data('id');
            var $row = $(this).closest('tr');
            
            $('#aktopr-z-modal-title').text('Uredi zaposlenog');
            $('#z-id').val(id);
            $('#z-ime').val($row.find('td:nth-child(1) strong').text());
            $('#z-jmbg').val($row.find('td:nth-child(2)').text().trim());
            $('#z-radno').val($row.find('td:nth-child(3)').text().trim());
            $('#aktopr-zaposleni-modal').show();
        });
        
        $(document).on('click', '.aktopr-btn-delete-zaposleni', function() {
            if (!confirm(aaprPortal.strings.confirm_delete)) return;
            
            var id = $(this).data('id');
            var $row = $(this).closest('tr');
            
            $.ajax({
                url: aaprPortal.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aapr_portal_delete_zaposleni',
                    nonce: aaprPortal.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(function() {
                            $(this).remove();
                            updateCounts();
                        });
                        showMessage('success', response.data.message);
                    } else {
                        showMessage('error', response.data.message);
                    }
                }
            });
        });
        
        $('#aktopr-zaposleni-form').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: aaprPortal.ajaxurl,
                type: 'POST',
                data: $(this).serialize() + '&action=aapr_portal_save_zaposleni&nonce=' + aaprPortal.nonce,
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data.message);
                        $('#aktopr-zaposleni-modal').hide();
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showMessage('error', response.data.message);
                    }
                }
            });
        });
        
        $(document).on('click', '.aktopr-modal-close', function() {
            $(this).closest('.aktopr-modal').hide();
        });
        
        $(document).on('click', '.aktopr-modal', function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });
    }

    function initRadnaMesta() {
        $('#aktopr-novo-radno-mesto').on('click', function() {
            $('#aktopr-rm-modal-title').text('Novo radno mesto');
            $('#aktopr-radno-mesto-form')[0].reset();
            $('#rm-id').val('');
            $('#aktopr-radno-mesto-modal').show();
        });
        
        $(document).on('click', '.aktopr-btn-edit-radno', function() {
            var id = $(this).data('id');
            var $row = $(this).closest('tr');
            
            $('#aktopr-rm-modal-title').text('Uredi radno mesto');
            $('#rm-id').val(id);
            $('#rm-sifra').val($row.find('td:nth-child(1) strong').text());
            $('#rm-naziv').val($row.find('td:nth-child(2)').text());
            $('#aktopr-radno-mesto-modal').show();
        });
        
        $(document).on('click', '.aktopr-btn-delete-radno', function() {
            if (!confirm(aaprPortal.strings.confirm_delete)) return;
            
            var id = $(this).data('id');
            var $row = $(this).closest('tr');
            
            $.ajax({
                url: aaprPortal.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aapr_portal_delete_radno_mesto',
                    nonce: aaprPortal.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(function() {
                            $(this).remove();
                            updateCounts();
                        });
                        showMessage('success', response.data.message);
                    } else {
                        showMessage('error', response.data.message);
                    }
                }
            });
        });
        
        $('#aktopr-radno-mesto-form').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: aaprPortal.ajaxurl,
                type: 'POST',
                data: $(this).serialize() + '&action=aapr_portal_save_radno_mesto&nonce=' + aaprPortal.nonce,
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data.message);
                        $('#aktopr-radno-mesto-modal').hide();
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showMessage('error', response.data.message);
                    }
                }
            });
        });
    }

    function initLogout() {
        $('#aktopr-logout').on('click', function() {
            $.ajax({
                url: aaprPortal.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aapr_portal_logout',
                    nonce: aaprPortal.nonce
                },
                success: function() {
                    window.location.href = aaprPortal.home_url || '/';
                }
            });
        });
    }

    function updateCounts() {
        var zaposleniCount = $('#tab-zaposleni tbody tr').length;
        var radnaCount = $('#tab-radna_mesta tbody tr').length;
        
        $('.aktopr-tab[data-tab="zaposleni"] .aktopr-count').text('(' + zaposleniCount + ')');
        $('.aktopr-tab[data-tab="radna_mesta"] .aktopr-count').text('(' + radnaCount + ')');
    }

    function showMessage(type, message) {
        var $msg = $('.aktopr-message');
        $msg.removeClass('success error').addClass(type).text(message).show();
        
        setTimeout(function() {
            $msg.fadeOut(function() {
                $(this).hide();
            });
        }, 3000);
    }

})(jQuery);
