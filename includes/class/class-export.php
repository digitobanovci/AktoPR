<?php

class Auto_AktoPR_Export {

    public function __construct() {
        add_action('wp_ajax_aapr_export_docx', [$this, 'export_docx']);
        add_action('wp_ajax_aapr_export_pdf', [$this, 'export_pdf']);
        add_action('wp_ajax_aapr_preview_dokument', [$this, 'preview_dokument']);
    }

    public function export_docx(): void {
        if (!current_user_can('edit_aapr_master_dokument')) {
            wp_die(__('Nemate dozvolu.', 'auto-aktopr'));
        }
        
        check_ajax_referer('aapr_nonce');
        
        $dokument_id = (int) ($_POST['dokument_id'] ?? 0);
        if ($dokument_id === 0) {
            wp_send_json_error(['message' => 'Nevažeći ID dokumenta']);
        }
        
        $dokument = get_post($dokument_id);
        if (!$dokument || $dokument->post_type !== 'aapr_master_dokument') {
            wp_send_json_error(['message' => 'Dokument nije pronađen']);
        }
        
        $filename = sanitize_file_name($dokument->post_title) . '_' . date('Y-m-d') . '.docx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        echo $this->generisi_docx_content($dokument_id);
        
        wp_die();
    }

    public function export_pdf(): void {
        if (!current_user_can('edit_aapr_master_dokument')) {
            wp_die(__('Nemate dozvolu.', 'auto-aktopr'));
        }
        
        check_ajax_referer('aapr_nonce');
        
        $dokument_id = (int) ($_POST['dokument_id'] ?? 0);
        if ($dokument_id === 0) {
            wp_send_json_error(['message' => 'Nevažeći ID dokumenta']);
        }
        
        $dokument = get_post($dokument_id);
        if (!$dokument || $dokument->post_type !== 'aapr_master_dokument') {
            wp_send_json_error(['message' => 'Dokument nije pronađen']);
        }
        
        $filename = sanitize_file_name($dokument->post_title) . '_' . date('Y-m-d') . '.html';
        
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo $this->generisi_pdf_html($dokument_id);
        
        wp_die();
    }

    public function preview_dokument(): void {
        if (!current_user_can('edit_aapr_master_dokument')) {
            wp_die(__('Nemate dozvolu.', 'auto-aktopr'));
        }
        
        check_ajax_referer('aapr_nonce');
        
        $dokument_id = (int) ($_POST['dokument_id'] ?? 0);
        
        wp_send_json_success([
            'html' => $this->generisi_pdf_html($dokument_id),
        ]);
    }

    private function generisi_docx_content(int $dokument_id): string {
        $dokument = get_post($dokument_id);
        $klijent = get_post($dokument->post_parent);
        
        $sekcije = get_post_meta($dokument_id, 'aapr_sekcije', true) ?: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18];
        
        $content = $this->generisi_html_za_dokument($dokument_id, $dokument, $klijent, $sekcije);
        
        return $this->html_to_docx($content);
    }

    private function generisi_pdf_html(int $dokument_id): string {
        $dokument = get_post($dokument_id);
        $klijent = get_post($dokument->post_parent);
        
        $sekcije = get_post_meta($dokument_id, 'aapr_sekcije', true) ?: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18];
        
        return $this->generisi_html_za_dokument($dokument_id, $dokument, $klijent, $sekcije);
    }

    private function generisi_html_za_dokument(int $dokument_id, \WP_Post $dokument, ?\WP_Post $klijent, array $sekcije): string {
        global $wpdb;
        
        $propisi = Auto_AktoPR_Database::get_propisi();
        $koeficijenti = Auto_AktoPR_Database::get_koeficijenti();
        $risici = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT r.*, o.naziv as opasnost_naziv, o.grupa as opasnost_grupa 
                FROM {$wpdb->prefix}aapr_risici r 
                LEFT JOIN {$wpdb->prefix}aapr_opasnosti o ON r.opasnost_id = o.id 
                WHERE r.master_dokument_id = %d",
                $dokument_id
            ),
            ARRAY_A
        );
        
        $status = get_post_meta($dokument_id, 'aapr_status', true);
        $tip = get_post_meta($dokument_id, 'aapr_tip_izracunavanja', true);
        $verzija = get_post_meta($dokument_id, 'aapr_verzija', true);
        $odluka_broj = get_post_meta($dokument_id, 'aapr_odluka_broj', true);
        $odluka_datum = get_post_meta($dokument_id, 'aapr_odluka_datum', true);
        
        ob_start();
        ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo esc_html($dokument->post_title); ?></title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.6; color: #333; padding: 20mm 25mm; }
    h1 { font-size: 18pt; text-align: center; margin-bottom: 30px; }
    h2 { font-size: 14pt; margin: 25px 0 15px; border-bottom: 1px solid #333; padding-bottom: 5px; }
    h3 { font-size: 12pt; margin: 20px 0 10px; }
    p { margin-bottom: 10px; text-align: justify; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 11pt; }
    th, td { border: 1px solid #666; padding: 8px; text-align: left; }
    th { background: #f0f0f0; font-weight: bold; }
    .header { text-align: center; margin-bottom: 40px; }
    .meta { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 3px solid #333; }
    .meta p { margin: 5px 0; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 10pt; }
    .badge-kritican { background: #dc2626; color: white; }
    .badge-visok { background: #f97316; color: white; }
    .badge-srednji { background: #eab308; color: black; }
    .badge-nizak { background: #22c55e; color: white; }
    .badge-zanemarljiv { background: #9ca3af; color: white; }
    .risici-header { display: flex; justify-content: space-between; margin: 20px 0; }
    .signature-block { margin-top: 60px; page-break-inside: avoid; }
    .signature-block table { border: none; }
    .signature-block td { border: none; padding: 30px 20px 10px; vertical-align: bottom; }
    @media print { body { padding: 0; } }
</style>
</head>
<body>
<div class="header">
    <h1>AKT O PROCENI RIZIKA</h1>
    <p><strong><?php echo esc_html($dokument->post_title); ?></strong></p>
    <p>Verzija <?php echo esc_html($verzija); ?> | Status: <?php echo esc_html(ucfirst($status)); ?></p>
</div>

<div class="meta">
    <p><strong>Poslodavac:</strong> <?php echo esc_html($klijent ? $klijent->post_title : 'Nije definisano'); ?></p>
    <p><strong>PIB:</strong> <?php echo esc_html($klijent ? get_post_meta($klijent->ID, 'aapr_pib', true) : ''); ?></p>
    <p><strong>Adresa:</strong> <?php echo esc_html($klijent ? get_post_meta($klijent->ID, 'aapr_adresa', true) : ''); ?></p>
    <p><strong>Odluka br.:</strong> <?php echo esc_html($odluka_broj); ?> <strong>od:</strong> <?php echo esc_html($odluka_datum); ?></p>
    <p><strong>Datum izrade:</strong> <?php echo date('d.m.Y.'); ?></p>
</div>

<?php if (in_array(1, $sekcije)): ?>
<h2>1.1 Одлука о покретању поступка</h2>
<p>На основу Одлуке послодавца бр. <?php echo esc_html($odluka_broj); ?> од <?php echo esc_html($odluka_datum); ?>, покреће се поступак процене ризика на радном месту и у радној средини у складу са Правилником о начину и поступку процене ризика („Службени гласник РС", бр. 76/2024).</p>
<p>Овај акт о процени ризика израђен је у складу са Законом о безбедности и здрављу на раду („Службени гласник РС", бр. 35/2023 и 96/2024) и Правилником о начину и поступку процене ризика.</p>
<?php endif; ?>

<?php if (in_array(2, $sekcije) && $klijent): ?>
<h2>1.2 Подаци о послодавцу</h2>
<table>
    <tr><th>Назив</th><td><?php echo esc_html($klijent->post_title); ?></td></tr>
    <tr><th>Матични број/ПИБ</th><td><?php echo esc_html(get_post_meta($klijent->ID, 'aapr_pib', true)); ?></td></tr>
    <tr><th>Адреса</th><td><?php echo esc_html(get_post_meta($klijent->ID, 'aapr_adresa', true)); ?></td></tr>
    <tr><th>Телефон</th><td><?php echo esc_html(get_post_meta($klijent->ID, 'aapr_telefon', true)); ?></td></tr>
    <tr><th>E-mail</th><td><?php echo esc_html(get_post_meta($klijent->ID, 'aapr_email', true)); ?></td></tr>
    <tr><th> делатност</th><td><?php echo esc_html(get_post_meta($klijent->ID, 'aapr_delatnost', true)); ?></td></tr>
</table>
<?php endif; ?>

<?php if (in_array(4, $sekcije)): ?>
<h2>1.4 Zakonska osnova</h2>
<p>Ovaj akt o proceni rizika izrađen je u skladu sa:</p>
<ul>
<?php foreach ($propisi as $propis): ?>
    <li><?php echo esc_html($propis['sifra']); ?> - <?php echo esc_html($propis['naziv']); ?> (<?php echo esc_html($propis['vrsta']); ?>, <?php echo esc_html($propis['datum_objave']); ?>)</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<?php if (in_array(11, $sekcije) || in_array(12, $sekcije)): ?>
<h2>5. Procena rizika - <?php echo esc_html(ucfirst($tip)); ?> metoda</h2>
<p>Procena rizika vrši se prema formuli: <strong>R = P × F × C</strong></p>
<p> gde je: P - verovatnoća nastanka, F - učestalost izlaganja, C - težina posledica</p>

<table>
    <thead>
        <tr>
            <th>Rb.</th>
            <th>Opasnost</th>
            <th>Grupa</th>
            <th>P</th>
            <th>F</th>
            <th>C</th>
            <th>R</th>
            <th>Nivo</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($risici as $i => $risik): ?>
        <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo esc_html($risik['opasnost_naziv'] ?: $risik['opis']); ?></td>
            <td><?php echo esc_html($risik['opasnost_grupa']); ?></td>
            <td><?php echo esc_html($risik['verovatnoca']); ?></td>
            <td><?php echo esc_html($risik['ucestalost']); ?></td>
            <td><?php echo esc_html($risik['tezina']); ?></td>
            <td><strong><?php echo esc_html($risik['rizik']); ?></strong></td>
            <td><span class="badge badge-<?php echo esc_attr($risik['nivo_rizika']); ?>"><?php echo esc_html(ucfirst($risik['nivo_rizika'])); ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($risici)): ?>
        <tr><td colspan="8" style="text-align: center;">Nema unetih rizika</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php endif; ?>

<div class="signature-block">
    <table>
        <tr>
            <td style="text-align: center;">
                <p>Izradio:</p>
                <br><br>
                <p>_________________________</p>
                <p><small>Ime i prezime</small></p>
            </td>
            <td style="text-align: center;">
                <p>Pregledao:</p>
                <br><br>
                <p>_________________________</p>
                <p><small>Stručno lice za BZR</small></p>
            </td>
            <td style="text-align: center;">
                <p>Odobrio:</p>
                <br><br>
                <p>_________________________</p>
                <p><small>Poslodavac</small></p>
            </td>
        </tr>
    </table>
</div>

<p style="margin-top: 30px; text-align: center; font-size: 10pt; color: #666;">
    Dokument generisan automatski | Auto Akt oPR | <?php echo date('d.m.Y. H:i'); ?>
</p>
</body>
</html>
        <?php
        return ob_get_clean();
    }

    private function html_to_docx(string $html): string {
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $docx = new \PhpOffice\PhpWord\PhpWord();
        $section = $docx->addSection();
        
        foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
            $this->process_node($section, $node, $docx);
        }
        
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($docx, 'Word2007');
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    private function process_node(\PhpOffice\PhpWord\Element\Section $section, \DOMNode $node, \PhpOffice\PhpWord\PhpWord $docx): void {
        if ($node->nodeName === 'h1') {
            $section->addTitle(html_entity_decode($node->textContent), 1);
        } elseif ($node->nodeName === 'h2') {
            $section->addTitle(html_entity_decode($node->textContent), 2);
        } elseif ($node->nodeName === 'h3') {
            $section->addTitle(html_entity_decode($node->textContent), 3);
        } elseif ($node->nodeName === 'p') {
            $section->addText(html_entity_decode($node->textContent));
        } elseif ($node->nodeName === 'table') {
            $table = $section->addTable();
            foreach ($node->getElementsByTagName('tr') as $tr) {
                $row = $table->addRow();
                foreach ($tr->getElementsByTagName('td') as $td) {
                    $row->addCell()->addText(html_entity_decode($td->textContent));
                }
            }
        }
    }
}
