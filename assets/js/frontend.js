(function($) {
    'use strict';

    $(document).ready(function() {
        initWizard();
    });

    function initWizard() {
        var $wizard = $('#aapr-wizard');
        if ($wizard.length === 0) return;

        var currentStep = 1;
        var totalSteps = 8;
        var dokumentId = $wizard.data('dokument-id') || 0;
        var klijentId = $wizard.data('klijent-id') || 0;

        updateProgress();

        $('.aapr-step-btn').on('click', function() {
            var step = $(this).data('step');
            goToStep(step);
        });

        $('.aapr-btn-next').on('click', function() {
            if (currentStep < totalSteps) {
                markCompleted(currentStep);
                goToStep(currentStep + 1);
            }
        });

        $('.aapr-btn-prev').on('click', function() {
            if (currentStep > 1) {
                goToStep(currentStep - 1);
            }
        });

        $('.aapr-btn-save').on('click', function() {
            saveAll();
        });

        $('#aapr-dodaj-radno-mesto').on('click', function() {
            dodajRadnoMesto();
        });

        $('#aapr-dodaj-risik').on('click', function() {
            dodajRisik();
        });

        $('#aapr-dodaj-meru').on('click', function() {
            dodajMeru();
        });

        $(document).on('change', '.aapr-koef-p, .aapr-koef-f, .aapr-koef-c', function() {
            izracunajRizik($(this).closest('.aapr-risik'));
        });

        $(document).on('click', '.aapr-ai-btn', function() {
            openAiModal($(this).data('section'));
        });

        $('#aapr-ai-insert').on('click', function() {
            var textareaId = $(this).data('target');
            var tekst = $('#aapr-ai-output').val();
            $('#' + textareaId).val(tekst).trigger('input');
            $('#aapr-ai-modal').hide();
        });

        $('#aapr-ai-cancel, #aapr-ai-modal').on('click', function(e) {
            if (e.target === this) {
                $('#aapr-ai-modal').hide();
            }
        });

        function goToStep(step) {
            currentStep = step;
            
            $('.aapr-step').hide();
            $('.aapr-step[data-step="' + step + '"]').show();
            
            $('.aapr-step-btn').removeClass('active');
            $('.aapr-step-btn[data-step="' + step + '"]').addClass('active');
            
            var progress = (step / totalSteps) * 100;
            $('.aapr-progress-fill').css('width', progress + '%');
            
            $('.aapr-btn-prev').toggle(step > 1);
            $('.aapr-btn-next').toggle(step < totalSteps);
            $('.aapr-btn-save').toggle(step === totalSteps);
            
            $('html, body').animate({
                scrollTop: $wizard.offset().top - 20
            }, 300);
        }

        function markCompleted(step) {
            $('.aapr-step-btn[data-step="' + step + '"]').addClass('completed');
        }

        function updateProgress() {
            goToStep(1);
        }

        function dodajRadnoMesto() {
            var $list = $('#aapr-radna-mesta-list');
            var index = $list.children().length;
            var $novo = $list.children().first().clone();
            
            $novo.attr('data-index', index);
            $novo.find('h4').text('Radno mesto #' + (index + 1));
            $novo.find('input').val('');
            
            $list.append($novo);
        }

        function dodajRisik() {
            var $list = $('#aapr-risici-list');
            var index = $list.children().length;
            var $novo = $list.children().first().clone();
            
            $novo.attr('data-index', index);
            $novo.find('h4').text('Rizik #' + (index + 1));
            $novo.find('select').val('');
            $novo.find('input').val('');
            $novo.find('.aapr-r-vrednost').text('1');
            
            $list.append($novo);
        }

        function dodajMeru() {
            var $list = $('#aapr-mere-list');
            var index = $list.children().length;
            var $novo = $list.children().first().clone();
            
            $novo.attr('data-index', index);
            $novo.find('h4').text('Mera #' + (index + 1));
            $novo.find('input').val('');
            $novo.find('select').val('srednji');
            
            $list.append($novo);
        }

        function izracunajRizik($risik) {
            var p = parseFloat($risik.find('.aapr-koef-p').val()) || 1;
            var f = parseFloat($risik.find('.aapr-koef-f').val()) || 1;
            var c = parseFloat($risik.find('.aapr-koef-c').val()) || 1;
            
            var r = p * f * c;
            
            $risik.find('.aapr-r-formula').text(p + ' × ' + f + ' × ' + c);
            $risik.find('.aapr-r-vrednost').text(r.toFixed(1));
            
            var nivo = 'zanemarljiv';
            var nivoClass = 'aapr-nivo-zanemarljiv';
            
            if (r >= 100) { nivo = 'Kritičan'; nivoClass = 'aapr-nivo-kritican'; }
            else if (r >= 40) { nivo = 'Visok'; nivoClass = 'aapr-nivo-visok'; }
            else if (r >= 10) { nivo = 'Srednji'; nivoClass = 'aapr-nivo-srednji'; }
            else if (r >= 1) { nivo = 'Nizak'; nivoClass = 'aapr-nivo-nizak'; }
            
            var $nivoSpan = $risik.find('.aapr-r-nivo');
            $nivoSpan.text(nivo);
            $nivoSpan.attr('class', 'aapr-r-nivo ' + nivoClass);
        }

        function openAiModal(section) {
            $('#aapr-ai-output').val('Generišem tekst...');
            $('#aapr-ai-modal').show();
            
            $.ajax({
                url: aaprFE.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aapr_ai_generisi',
                    nonce: aaprFE.nonce,
                    tip: 'general',
                    kontekst: 'Generiši profesionalan tekst za sekciju ' + section + ' Akta o proceni rizika prema srpskom pravilniku. Tekst treba da bude formalni, pravnički stil.'
                },
                success: function(response) {
                    if (response.success) {
                        $('#aapr-ai-output').val(response.data.tekst);
                    } else {
                        $('#aapr-ai-output').val('Greška: ' + response.data.message);
                    }
                },
                error: function() {
                    $('#aapr-ai-output').val('Greška pri komunikaciji sa serverom.');
                }
            });
        }

        function saveAll() {
            var data = {};
            
            $wizard.find('input, select, textarea').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    if ($(this).attr('type') === 'checkbox') {
                        if (!data[name]) data[name] = [];
                        if ($(this).is(':checked')) {
                            data[name].push($(this).val());
                        }
                    } else {
                        data[name] = $(this).val();
                    }
                }
            });
            
            $.ajax({
                url: aaprFE.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aapr_save_sekcija',
                    nonce: aaprFE.nonce,
                    dokument_id: dokumentId,
                    sekcija: 'all',
                    data: data
                },
                success: function(response) {
                    if (response.success) {
                        showSaved();
                    } else {
                        alert('Greška pri čuvanju: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Greška pri komunikaciji sa serverom.');
                }
            });
        }

        function showSaved() {
            var $saved = $('<div class="aapr-saved-indicator">✓ Sačuvano</div>');
            $('body').append($saved);
            $saved.fadeIn();
            
            setTimeout(function() {
                $saved.fadeOut(function() {
                    $(this).remove();
                });
            }, 2000);
        }
    }

})(jQuery);
