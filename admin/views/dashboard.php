<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap aapr-wrap">
    <h1>
        <span class="dashicons dashicons-shield-alt" style="font-size: 30px; width: 30px; height: 30px;"></span>
        Auto Akt oPR - Dashboard
    </h1>
    
    <div class="aapr-stats">
        <div class="aapr-stat-card">
            <div class="stat-icon" style="background: #2271b1;">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo esc_html($stats['klijenata']); ?></h3>
                <p>Klijenata</p>
            </div>
        </div>
        
        <div class="aapr-stat-card">
            <div class="stat-icon" style="background: #00a32a;">
                <span class="dashicons dashicons-admin-users"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo esc_html($stats['zaposlenih']); ?></h3>
                <p>Zaposlenih</p>
            </div>
        </div>
        
        <div class="aapr-stat-card">
            <div class="stat-icon" style="background: #f0c33c;">
                <span class="dashicons dashicons-media-document"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo esc_html($stats['dokumenata']); ?></h3>
                <p>Dokumenata</p>
            </div>
        </div>
        
        <div class="aapr-stat-card <?php echo $stats['neobradjeni_risici'] > 0 ? 'warning' : ''; ?>">
            <div class="stat-icon" style="background: #d63638;">
                <span class="dashicons dashicons-warning"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo esc_html($stats['neobradjeni_risici']); ?></h3>
                <p>Visokih rizika</p>
            </div>
        </div>
    </div>
    
    <div class="aapr-dashboard-grid">
        <div class="aapr-card">
            <h2>Brzi linkovi</h2>
            <div class="aapr-quick-actions">
                <a href="<?php echo admin_url('admin.php?page=auto-aktopr-klijenti'); ?>" class="button button-primary">
                    <span class="dashicons dashicons-plus-alt2"></span> Novi klijent
                </a>
                <a href="<?php echo admin_url('admin.php?page=auto-aktopr-zaposleni'); ?>" class="button">
                    <span class="dashicons dashicons-admin-network"></span> Novi zaposleni
                </a>
                <a href="<?php echo admin_url('admin.php?page=auto-aktopr-dokumenti'); ?>" class="button">
                    <span class="dashicons dashicons-media-interactive"></span> Novi dokument
                </a>
            </div>
        </div>
        
        <div class="aapr-card">
            <h2>O pluginu</h2>
            <table class="widefat">
                <tr>
                    <td><strong>Verzija:</strong></td>
                    <td><?php echo esc_html(AUTO_AKTOPR_VERSION); ?></td>
                </tr>
                <tr>
                    <td><strong>Tip licence:</strong></td>
                    <td>GPL v2+</td>
                </tr>
                <tr>
                    <td><strong>Zakon:</strong></td>
                    <td>Sl. glasnik RS br. 76/2024</td>
                </tr>
                <tr>
                    <td><strong>Metoda:</strong></td>
                    <td>Kinney, Pearson</td>
                </tr>
            </table>
        </div>
        
        <div class="aapr-card">
            <h2>Nedavni klijenti</h2>
            <?php 
            $recent_klijenti = get_posts([
                'post_type' => 'aapr_klijent',
                'posts_per_page' => 5,
                'orderby' => 'date',
                'order' => 'DESC',
            ]);
            ?>
            <?php if (empty($recent_klijenti)): ?>
                <p>Nema klijenata. <a href="<?php echo admin_url('admin.php?page=auto-aktopr-klijenti'); ?>">Dodajte prvog</a></p>
            <?php else: ?>
                <ul class="aapr-list">
                    <?php foreach ($recent_klijenti as $k): ?>
                        <li>
                            <a href="<?php echo admin_url('admin.php?page=auto-aktopr-klijenti&action=edit&id=' . $k->ID); ?>">
                                <?php echo esc_html($k->post_title); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        
        <div class="aapr-card">
            <h2>Nedavni dokumenti</h2>
            <?php 
            $recent_docs = get_posts([
                'post_type' => 'aapr_master_dokument',
                'posts_per_page' => 5,
                'orderby' => 'date',
                'order' => 'DESC',
            ]);
            ?>
            <?php if (empty($recent_docs)): ?>
                <p>Nema dokumenata. <a href="<?php echo admin_url('admin.php?page=auto-aktopr-dokumenti'); ?>">Kreirajte prvi</a></p>
            <?php else: ?>
                <ul class="aapr-list">
                    <?php foreach ($recent_docs as $d): 
                        $status = get_post_meta($d->ID, 'aapr_status', true);
                        $status_class = $status === 'odobren' ? 'green' : ($status === 'u_pripremi' ? 'orange' : 'gray');
                    ?>
                        <li>
                            <a href="<?php echo admin_url('post.php?post=' . $d->ID . '&action=edit'); ?>">
                                <?php echo esc_html($d->post_title); ?>
                            </a>
                            <span class="aapr-status-<?php echo esc_attr($status_class); ?>">
                                <?php echo esc_html(ucfirst($status)); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
