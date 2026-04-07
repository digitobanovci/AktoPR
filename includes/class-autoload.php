Auto_AktoPR_Autoload::register();

class Auto_AktoPR_Autoload {
    
    public static function register(): void {
        spl_autoload_register([self::class, 'load']);
    }
    
    public static function load(string $class): void {
        $prefix = 'Auto_AktoPR_';
        
        if (strpos($class, $prefix) !== 0) {
            return;
        }
        
        $class_name = str_replace($prefix, '', $class);
        
        $file = AUTO_AKTOPR_PLUGIN_DIR . 'includes/class/class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';
        
        if (file_exists($file)) {
            require_once $file;
            return;
        }
        
        $file = AUTO_AKTOPR_PLUGIN_DIR . 'includes/class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
