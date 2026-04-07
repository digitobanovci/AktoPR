<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap aapr-wrap">
    <h1>
        <span class="dashicons dashicons-groups" style="font-size: 24px; width: 24px; height: 24px;"></span>
        Klijenti
        <a href="#" class="page-title-action" id="aapr-novi-klijent">+ Dodaj novog</a>
    </h1>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th width="50">ID</th>
                <th>Naziv</th>
                <th>PIB</th>
                <th>Adresa</th>
                <th>Telefon</th>
                <th>Email</th>
                <th width="150">Akcije</th>
            </tr>
        </thead>
        <tbody id="aapr-klijenti-body">
            <?php if (empty($klijenti)): ?>
                <tr>
                    <td colspan="7" style="text-align: center;">
                        Nema klijenata. <a href="#" id="aapr-novi-klijent-inline">Dodajte prvog klijenta</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($klijenti as $k): ?>
                <tr data-id="<?php echo esc_attr($k->ID); ?>">
                    <td><?php echo esc_html($k->ID); ?></td>
                    <td><strong><?php echo esc_html($k->post_title); ?></strong></td>
                    <td><?php echo esc_html(get_post_meta($k->ID, 'aapr_pib', true)); ?></td>
                    <td><?php echo esc_html(get_post_meta($k->ID, 'aapr_adresa', true)); ?></td>
                    <td><?php echo esc_html(get_post_meta($k->ID, 'aapr_telefon', true)); ?></td>
                    <td><?php echo esc_html(get_post_meta($k->ID, 'aapr_email', true)); ?></td>
                    <td>
                        <button type="button" class="button button-small aapr-edit-klijent" data-id="<?php echo esc_attr($k->ID); ?>">Uredi</button>
                        <button type="button" class="button button-small aapr-delete-klijent" data-id="<?php echo esc_attr($k->ID); ?>">Obriši</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="aapr-klijent-modal" class="aapr-modal" style="display: none;">
    <div class="aapr-modal-content">
        <h2 id="aapr-modal-title">Novi klijent</h2>
        <form id="aapr-klijent-form">
            <input type="hidden" name="id" id="klijent-id" value="">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="klijent-naziv">Naziv *</label></th>
                    <td><input type="text" name="naziv" id="klijent-naziv" required class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="klijent-pib">PIB</label></th>
                    <td><input type="text" name="pib" id="klijent-pib" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="klijent-adresa">Adresa</label></th>
                    <td><input type="text" name="adresa" id="klijent-adresa" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="klijent-telefon">Telefon</label></th>
                    <td><input type="text" name="telefon" id="klijent-telefon" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="klijent-email">Email</label></th>
                    <td><input type="email" name="email" id="klijent-email" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="klijent-delatnost">Delatnost</label></th>
                    <td><input type="text" name="delatnost" id="klijent-delatnost" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="klijent-broj-zaposlenih">Broj zaposlenih</label></th>
                    <td><input type="number" name="broj_zaposlenih" id="klijent-broj-zaposlenih" class="regular-text" min="1"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="klijent-napomene">Napomene</label></th>
                    <td><textarea name="napomene" id="klijent-napomene" rows="3" class="regular-text"></textarea></td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary">Sačuvaj</button>
                <button type="button" class="button aapr-modal-close">Otkaži</button>
            </p>
        </form>
    </div>
</div>
