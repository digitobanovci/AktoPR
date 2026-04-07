<?php

class Auto_AktoPR_Roles {
    
    public static function add_roles(): void {
        $caps = [
            'edit_aapr_klijent', 'read_aapr_klijent', 'delete_aapr_klijent',
            'edit_aapr_zaposleni', 'read_aapr_zaposleni', 'delete_aapr_zaposleni',
            'edit_aapr_master_dokument', 'read_aapr_master_dokument',
            'manage_aapr_modules',
        ];
        
        add_role('strucno_lice_bzr', 'Stručno lice BZR', $caps);
        add_role('aapr_asistent', 'Asistent', [
            'read' => true,
            'edit_aapr_klijent' => true,
            'edit_aapr_zaposleni' => true,
        ]);
        add_role('aapr_klijent', 'Klijent', ['read' => true]);
    }
    
    public static function remove_roles(): void {
        remove_role('strucno_lice_bzr');
        remove_role('aapr_asistent');
        remove_role('aapr_klijent');
    }
}