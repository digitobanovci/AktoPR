<?php
/**
 * Plugin Name: Auto Akt oPR - Akt o Proceni Rizika
 * Plugin URI: https://github.com/digitobanovci/AktoPR
 * Description: Kompletan sistem za izradu Akta o proceni rizika na radnom mestu i u radnoj sredini. Multitenant WordPress plugin sa 10 modula, AI podrškom i Word/PDF exportom.
 * Version: 1.0.0
 * Author: Vaš Tim
 * Author URI: https://vašsajt.com
 * Text Domain: auto-aktopr
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AUTO_AKTOPR_VERSION', '1.0.0');
define('AUTO_AKTOPR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AUTO_AKTOPR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AUTO_AKTOPR_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once AUTO_AKTOPR_PLUGIN_DIR . 'includes/class-autoload.php';

final class Auto_AktoPR {
    
    private static ?Auto_AktoPR $instance = null;
    
    public static function instance(): Auto_AktoPR {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies(): void {
        // Klase se automatski učitavaju preko autoload-a
    }
    
    private function init_hooks(): void {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('init', [$this, 'init_classes']);
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }
    
    public function load_textdomain(): void {
        load_plugin_textdomain(
            'auto-aktopr',
            false,
            dirname(AUTO_AKTOPR_PLUGIN_BASENAME) . '/languages/'
        );
    }
    
    public function init_classes(): void {
        new Auto_AktoPR_CPT();
        new Auto_AktoPR_Roles();
        new Auto_AktoPR_Frontend();
        
        if (is_admin()) {
            new Auto_AktoPR_Admin();
            new Auto_AktoPR_Ajax();
        }
        
        new Auto_AktoPR_API();
        new Auto_AktoPR_Document();
        new Auto_AktoPR_Export();
        new Auto_AktoPR_AI();
    }
    
    public static function activate(): void {
        require_once AUTO_AKTOPR_PLUGIN_DIR . 'includes/class/class-database.php';
        require_once AUTO_AKTOPR_PLUGIN_DIR . 'includes/class/class-roles.php';
        require_once AUTO_AKTOPR_PLUGIN_DIR . 'includes/class/class-cpt.php';
        
        Auto_AktoPR_Database::create_tables();
        Auto_AktoPR_Roles::add_roles();
        flush_rewrite_rules();
    }
    
    public static function deactivate(): void {
        flush_rewrite_rules();
    }
}

function Auto_AktoPR(): Auto_AktoPR {
    return Auto_AktoPR::instance();
}

Auto_AktoPR();
