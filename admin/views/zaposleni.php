<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap aapr-wrap">
    <h1>
        <span class="dashicons dashicons-admin-users" style="font-size: 24px; width: 24px; height: 24px;"></span>
        Zaposleni
        <a href="#" class="page-title-action" id="aapr-novi-zaposleni">+ Dodaj novog</a>
    </h1>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th width="50">ID</th>
                <th>Ime i prezime</th>
                <th>JMBG</th>
                <th>Klijent</th>
                <th>Radno mesto</th>
                <th>Smenski rad</th>
                <th width="150">Akcije</th>
            </tr>
        </thead>
        <tbody id="aapr-zaposleni-body">
            <?php if (empty($zaposleni)): ?>
                <tr>
                    <td colspan="7" style="text-align: center;">
                        Nema zaposlenih. <a href="#" id="aapr-novi-zaposleni-inline">Dodajte prvog zaposlenog</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($zaposleni as $z): 
                    $klijent = get_post($z->post_parent);
                    $smenski = get_post_meta($z->ID, 'aapr_smenski_rad', true);
                ?>
                <tr data-id="<?php echo esc_attr($z->ID); ?>">
                    <td><?php echo esc_html($z->ID); ?></td>
                    <td><strong><?php echo esc_html($z->post_title); ?></strong></td>
                    <td><?php echo esc_html(get_post_meta($z->ID, 'aapr_jmbg', true)); ?></td>
                    <td><?php echo $klijent ? esc_html($klijent->post_title) : '-'; ?></td>
                    <td><?php echo esc_html(get_post_meta($z->ID, 'aapr_radno_mesto', true)); ?></td>
                    <td><?php echo $smenski ? '<span class="dashicons dashicons-yes-alt" style="color: green;"></span>' : '-'; ?></td>
                    <td>
                        <button type="button" class="button button-small aapr-edit-zaposleni" data-id="<?php echo esc_attr($z->ID); ?>">Uredi</button>
                        <button type="button" class="button button-small aapr-delete-zaposleni" data-id="<?php echo esc_attr($z->ID); ?>">Obriši</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="aapr-zaposleni-modal" class="aapr-modal" style="display: none;">
    <div class="aapr-modal-content">
        <h2 id="aapr-modal-title">Novi zaposleni</h2>
        <form id="aapr-zaposleni-form">
            <input type="hidden" name="id" id="zaposleni-id" value="">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="zaposleni-ime">Ime i prezime *</label></th>
                    <td><input type="text" name="ime_prezime" id="zaposleni-ime" required class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="zaposleni-klijent">Klijent</label></th>
                    <td>
                        <select name="klijent_id" id="zaposleni-klijent" class="regular-text">
                            <option value="">-- Izaberi klijenta --</option>
                            <?php foreach ($klijenti as $id => $naziv): ?>
                                <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($naziv); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="zaposleni-jmbg">JMBG</label></th>
                    <td><input type="text" name="jmbg" id="zaposleni-jmbg" maxlength="13" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="zaposleni-radno-mesto">Radno mesto</label></th>
                    <td><input type="text" name="radno_mesto" id="zaposleni-radno-mesto" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row">Način rada</th>
                    <td>
                        <label><input type="checkbox" name="smenski_rad" value="1" id="zaposleni-smenski"> Smenski rad</label>
                        <label><input type="checkbox" name="nocni_rad" value="1" id="zaposleni-nocni"> Noćni rad</label>
                        <label><input type="checkbox" name="terenski_rad" value="1" id="zaposleni-terenski"> Terenski rad</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="zaposleni-datum">Datum zaposlenja</label></th>
                    <td><input type="date" name="datum_zaposlenja" id="zaposleni-datum" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="zaposleni-napomene">Napomene</label></th>
                    <td><textarea name="napomene" id="zaposleni-napomene" rows="3" class="regular-text"></textarea></td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary">Sačuvaj</button>
                <button type="button" class="button aapr-modal-close">Otkaži</button>
            </p>
        </form>
    </div>
</div>
