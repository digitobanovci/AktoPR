<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap aapr-wrap">
    <h1>
        <span class="dashicons dashicons-media-document" style="font-size: 24px; width: 24px; height: 24px;"></span>
        Master dokumenti
        <a href="<?php echo admin_url('post-new.php?post_type=aapr_master_dokument'); ?>" class="page-title-action">+ Novi dokument</a>
    </h1>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th width="50">ID</th>
                <th>Naziv dokumenta</th>
                <th>Klijent</th>
                <th>Status</th>
                <th>Metoda</th>
                <th>Verzija</th>
                <th>Datum</th>
                <th width="180">Akcije</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($dokumenti)): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">
                        Nema dokumenata. <a href="<?php echo admin_url('post-new.php?post_type=aapr_master_dokument'); ?>">Kreirajte prvi dokument</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($dokumenti as $d): 
                    $klijent = get_post($d->post_parent);
                    $status = get_post_meta($d->ID, 'aapr_status', true) ?: 'u_pripremi';
                    $tip = get_post_meta($d->ID, 'aapr_tip_izracunavanja', true) ?: 'kinney';
                    $verzija = get_post_meta($d->ID, 'aapr_verzija', true) ?: '1.0';
                    
                    $status_colors = [
                        'u_pripremi' => '#f0c33c',
                        'na_pregledu' => '#2271b1',
                        'odobren' => '#00a32a',
                        'objavljen' => '#8b8b8b',
                    ];
                ?>
                <tr>
                    <td><?php echo esc_html($d->ID); ?></td>
                    <td><strong><?php echo esc_html($d->post_title); ?></strong></td>
                    <td><?php echo $klijent ? esc_html($klijent->post_title) : '-'; ?></td>
                    <td>
                        <span style="background: <?php echo esc_attr($status_colors[$status] ?? '#ccc'); ?>; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px;">
                            <?php echo esc_html(ucfirst(str_replace('_', ' ', $status))); ?>
                        </span>
                    </td>
                    <td><?php echo esc_html(ucfirst($tip)); ?></td>
                    <td><?php echo esc_html($verzija); ?></td>
                    <td><?php echo esc_html(date('d.m.Y.', strtotime($d->post_date))); ?></td>
                    <td>
                        <a href="<?php echo admin_url('post.php?post=' . $d->ID . '&action=edit'); ?>" class="button button-small">Uredi</a>
                        <button type="button" class="button button-small aapr-export" data-id="<?php echo esc_attr($d->ID); ?>">Export</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
