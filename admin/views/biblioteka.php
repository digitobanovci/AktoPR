<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap aapr-wrap">
    <h1>
        <span class="dashicons dashicons-book-alt" style="font-size: 24px; width: 24px; height: 24px;"></span>
        Biblioteka
    </h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="?page=auto-aktopr-biblioteka&tab=propisi" class="nav-tab <?php echo $tab === 'propisi' ? 'nav-tab-active' : ''; ?>">Propisi</a>
        <a href="?page=auto-aktopr-biblioteka&tab=koeficijenti" class="nav-tab <?php echo $tab === 'koeficijenti' ? 'nav-tab-active' : ''; ?>">Koeficijenti</a>
        <a href="?page=auto-aktopr-biblioteka&tab=tekst_blokovi" class="nav-tab <?php echo $tab === 'tekst_blokovi' ? 'nav-tab-active' : ''; ?>">Tekst blokovi</a>
        <a href="?page=auto-aktopr-biblioteka&tab=standardne_mere" class="nav-tab <?php echo $tab === 'standardne_mere' ? 'nav-tab-active' : ''; ?>">Standardne mere</a>
    </h2>
    
    <?php if ($tab === 'propisi'): ?>
    <div class="aapr-section">
        <h3>Propisi <button type="button" class="button" id="aapr-novi-prop">+ Dodaj</button></h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Šifra</th>
                    <th>Naziv</th>
                    <th>Vrsta</th>
                    <th>Datum</th>
                    <th>Napomene</th>
                    <th width="100">Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($propisi as $p): ?>
                <tr data-id="<?php echo esc_attr($p['id']); ?>">
                    <td><?php echo esc_html($p['sifra']); ?></td>
                    <td><?php echo esc_html($p['naziv']); ?></td>
                    <td><?php echo esc_html($p['vrsta']); ?></td>
                    <td><?php echo esc_html($p['datum_objave']); ?></td>
                    <td><?php echo esc_html($p['napomene']); ?></td>
                    <td>
                        <button type="button" class="button button-small aapr-edit-prop">Uredi</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($propisi)): ?>
                <tr><td colspan="6" style="text-align: center;">Nema propisa</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php elseif ($tab === 'koeficijenti'): ?>
    <div class="aapr-section">
        <h3>Koeficijenti za Kinney metodu</h3>
        
        <h4>Verovatnoća (P)</h4>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Naziv</th><th>Vrednost</th><th>Opis</th><th>Boja</th></tr></thead>
            <tbody>
                <?php foreach ($koeficijenti as $k): if ($k['kategorija'] === 'verovatnoca'): ?>
                <tr>
                    <td><?php echo esc_html($k['naziv']); ?></td>
                    <td><?php echo esc_html($k['vrednost_min'] . ' - ' . $k['vrednost_max']); ?></td>
                    <td><?php echo esc_html($k['opis']); ?></td>
                    <td><span style="background: <?php echo esc_attr($k['boja']); ?>; color: white; padding: 2px 8px;"><?php echo esc_html($k['boja']); ?></span></td>
                </tr>
                <?php endif; endforeach; ?>
            </tbody>
        </table>
        
        <h4>Učestalost (F)</h4>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Naziv</th><th>Vrednost</th><th>Opis</th><th>Boja</th></tr></thead>
            <tbody>
                <?php foreach ($koeficijenti as $k): if ($k['kategorija'] === 'ucestalost'): ?>
                <tr>
                    <td><?php echo esc_html($k['naziv']); ?></td>
                    <td><?php echo esc_html($k['vrednost_min'] . ' - ' . $k['vrednost_max']); ?></td>
                    <td><?php echo esc_html($k['opis']); ?></td>
                    <td><span style="background: <?php echo esc_attr($k['boja']); ?>; color: white; padding: 2px 8px;"><?php echo esc_html($k['boja']); ?></span></td>
                </tr>
                <?php endif; endforeach; ?>
            </tbody>
        </table>
        
        <h4>Težina (C)</h4>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Naziv</th><th>Vrednost</th><th>Opis</th><th>Boja</th></tr></thead>
            <tbody>
                <?php foreach ($koeficijenti as $k): if ($k['kategorija'] === 'tezina'): ?>
                <tr>
                    <td><?php echo esc_html($k['naziv']); ?></td>
                    <td><?php echo esc_html($k['vrednost_min'] . ' - ' . $k['vrednost_max']); ?></td>
                    <td><?php echo esc_html($k['opis']); ?></td>
                    <td><span style="background: <?php echo esc_attr($k['boja']); ?>; color: white; padding: 2px 8px;"><?php echo esc_html($k['boja']); ?></span></td>
                </tr>
                <?php endif; endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php elseif ($tab === 'tekst_blokovi'): ?>
    <div class="aapr-section">
        <h3>Tekst blokovi</h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr><th>Šifra</th><th>Naslov</th><th>Sekcija</th><th>Prioritet</th><th width="100">Akcije</th></tr>
            </thead>
            <tbody>
                <?php foreach ($tekst_blokovi as $tb): ?>
                <tr>
                    <td><?php echo esc_html($tb['sifra']); ?></td>
                    <td><?php echo esc_html($tb['naslov']); ?></td>
                    <td><?php echo esc_html($tb['sekcija']); ?></td>
                    <td><?php echo esc_html($tb['prioritet']); ?></td>
                    <td>
                        <button type="button" class="button button-small">Uredi</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($tekst_blokovi)): ?>
                <tr><td colspan="5" style="text-align: center;">Nema tekst blokova</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php elseif ($tab === 'standardne_mere'): ?>
    <div class="aapr-section">
        <h3>Standardne mere zaštite</h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr><th>Šifra</th><th>Naziv</th><th>Tip</th><th>Prioritet</th><th>Rok (dana)</th><th>Grupa</th><th width="100">Akcije</th></tr>
            </thead>
            <tbody>
                <?php foreach ($standardne_mere as $sm): ?>
                <tr>
                    <td><?php echo esc_html($sm['sifra']); ?></td>
                    <td><?php echo esc_html($sm['naziv']); ?></td>
                    <td><?php echo esc_html($sm['tip']); ?></td>
                    <td><?php echo esc_html($sm['prioritet']); ?></td>
                    <td><?php echo esc_html($sm['rok_dana']); ?></td>
                    <td><?php echo esc_html($sm['grupa_opasnosti']); ?></td>
                    <td>
                        <button type="button" class="button button-small">Uredi</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($standardne_mere)): ?>
                <tr><td colspan="7" style="text-align: center;">Nema standardnih mera</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
