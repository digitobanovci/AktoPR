<?php if (!defined('ABSPATH')) exit; ?>

<div class="aktopr-portal aktopr-login">
    <div class="aktopr-portal-header">
        <h1>Prijavljivanje</h1>
        <p>Pristup vašem portalu za upravljanje</p>
    </div>
    
    <form id="aktopr-login-form" class="aktopr-form">
        <div class="aktopr-form-group">
            <label for="login-username">Korisničko ime *</label>
            <input type="text" id="login-username" name="username" required autocomplete="username">
        </div>
        
        <div class="aktopr-form-group">
            <label for="login-password">Lozinka *</label>
            <input type="password" id="login-password" name="password" required autocomplete="current-password">
        </div>
        
        <div class="aktopr-form-group">
            <label>
                <input type="checkbox" name="remember" value="1"> Zapamti me
            </label>
        </div>
        
        <div class="aktopr-form-actions">
            <button type="submit" class="aktopr-btn aktopr-btn-primary">
                <span class="btn-text">Prijavi se</span>
                <span class="btn-loading" style="display: none;">Učitavanje...</span>
            </button>
        </div>
        
        <div class="aktopr-form-links">
            <p>Nemate nalog? <a href="#" id="aktopr-show-register">Registrujte se</a></p>
        </div>
    </form>
    
    <div class="aktopr-message" style="display: none;"></div>
</div>

<div class="aktopr-portal aktopr-register" style="display: none;">
    <div class="aktopr-portal-header">
        <h1>Registracija</h1>
        <p>Kreirajte nalog za vašu firmu</p>
    </div>
    
    <form id="aktopr-register-form" class="aktopr-form">
        <div class="aktopr-form-group">
            <label for="reg-firma">Naziv firme *</label>
            <input type="text" id="reg-firma" name="firma_naziv" required placeholder="Pun naziv preduzeća">
        </div>
        
        <div class="aktopr-form-group">
            <label for="reg-username">Korisničko ime *</label>
            <input type="text" id="reg-username" name="username" required autocomplete="username">
        </div>
        
        <div class="aktopr-form-group">
            <label for="reg-email">Email *</label>
            <input type="email" id="reg-email" name="email" required autocomplete="email">
        </div>
        
        <div class="aktopr-form-group">
            <label for="reg-password">Lozinka *</label>
            <input type="password" id="reg-password" name="password" required minlength="8">
        </div>
        
        <div class="aktopr-form-group">
            <label for="reg-pib">PIB</label>
            <input type="text" id="reg-pib" name="pib" placeholder="9 cifara">
        </div>
        
        <div class="aktopr-form-group">
            <label for="reg-adresa">Adresa</label>
            <input type="text" id="reg-adresa" name="adresa" placeholder="Ulica i broj, grad">
        </div>
        
        <div class="aktopr-form-group">
            <label for="reg-telefon">Telefon</label>
            <input type="tel" id="reg-telefon" name="telefon">
        </div>
        
        <div class="aktopr-form-group">
            <label for="reg-delatnost">Delatnost</label>
            <input type="text" id="reg-delatnost" name="delatnost" placeholder="Čime se firma bavi">
        </div>
        
        <div class="aktopr-form-actions">
            <button type="submit" class="aktopr-btn aktopr-btn-primary">
                <span class="btn-text">Registruj se</span>
                <span class="btn-loading" style="display: none;">Učitavanje...</span>
            </button>
        </div>
        
        <div class="aktopr-form-links">
            <p>Već imate nalog? <a href="#" id="aktopr-show-login">Prijavite se</a></p>
        </div>
    </form>
    
    <div class="aktopr-message" style="display: none;"></div>
</div>

<div class="aktopr-portal aktopr-modal" id="aktopr-zaposleni-modal" style="display: none;">
    <div class="aktopr-modal-content">
        <h2 id="aktopr-modal-title">Novi zaposleni</h2>
        <form id="aktopr-zaposleni-form">
            <input type="hidden" name="id" id="zaposleni-id" value="">
            <input type="hidden" name="klijent_id" id="zaposleni-klijent-id" value="">
            
            <div class="aktopr-form-group">
                <label for="zaposleni-ime">Ime i prezime *</label>
                <input type="text" id="zaposleni-ime" name="ime_prezime" required>
            </div>
            
            <div class="aktopr-form-group">
                <label for="zaposleni-jmbg">JMBG</label>
                <input type="text" id="zaposleni-jmbg" name="jmbg" maxlength="13" placeholder="13 cifara">
            </div>
            
            <div class="aktopr-form-group">
                <label for="zaposleni-radno">Radno mesto</label>
                <input type="text" id="zaposleni-radno" name="radno_mesto" placeholder="Na kojem radnom mestu radi">
            </div>
            
            <div class="aktopr-form-group">
                <label for="zaposleni-datum">Datum zaposlenja</label>
                <input type="date" id="zaposleni-datum" name="datum_zaposlenja">
            </div>
            
            <div class="aktopr-form-group">
                <label>Način rada:</label>
                <div class="aktopr-checkbox-group">
                    <label><input type="checkbox" name="smenski_rad" value="1"> Smenski rad</label>
                    <label><input type="checkbox" name="nocni_rad" value="1"> Noćni rad</label>
                    <label><input type="checkbox" name="terenski_rad" value="1"> Terenski rad</label>
                </div>
            </div>
            
            <div class="aktopr-form-group">
                <label for="zaposleni-napomene">Napomene</label>
                <textarea id="zaposleni-napomene" name="napomene" rows="3"></textarea>
            </div>
            
            <div class="aktopr-form-actions">
                <button type="button" class="aktopr-btn aktopr-btn-secondary aktopr-modal-close">Otkaži</button>
                <button type="submit" class="aktopr-btn aktopr-btn-primary">Sačuvaj</button>
            </div>
        </form>
    </div>
</div>

<div class="aktopr-portal aktopr-modal" id="aktopr-radno-mesto-modal" style="display: none;">
    <div class="aktopr-modal-content">
        <h2 id="aktopr-rm-modal-title">Novo radno mesto</h2>
        <form id="aktopr-radno-mesto-form">
            <input type="hidden" name="id" id="radno-mesto-id" value="">
            
            <div class="aktopr-form-group">
                <label for="rm-sifra">Šifra radnog mesta</label>
                <input type="text" id="rm-sifra" name="sifra" placeholder="npr. RM-01">
            </div>
            
            <div class="aktopr-form-group">
                <label for="rm-naziv">Naziv radnog mesta *</label>
                <input type="text" id="rm-naziv" name="naziv" required placeholder="npr. Građevinski radnik">
            </div>
            
            <div class="aktopr-form-group">
                <label for="rm-opis">Opis posla</label>
                <textarea id="rm-opis" name="opis_posla" rows="4" placeholder="Detaljan opis poslova na ovom radnom mestu..."></textarea>
            </div>
            
            <div class="aktopr-form-group">
                <label for="rm-grupa">Grupa:</label>
                <select id="rm-grupa" name="grupa">
                    <option value="gradjevinski">Građevinski</option>
                    <option value="elektro_masinski">Elektro/Mašinski</option>
                    <option value="administrativni">Administrativni</option>
                    <option value="ostalo">Ostalo</option>
                </select>
            </div>
            
            <div class="aktopr-form-group">
                <label>Specifični uslovi:</label>
                <div class="aktopr-checkbox-group">
                    <label><input type="checkbox" name="rad_na_visini" value="1"> Rad na visini</label>
                    <label><input type="checkbox" name="rad_sa_hemikalijama" value="1"> Rad sa hemikalijama</label>
                    <label><input type="checkbox" name="rad_za_racunarom" value="1"> Rad za računarom</label>
                    <label><input type="checkbox" name="smenski_rad" value="1"> Smenski rad</label>
                    <label><input type="checkbox" name="nocni_rad" value="1"> Noćni rad</label>
                </div>
            </div>
            
            <div class="aktopr-form-actions">
                <button type="button" class="aktopr-btn aktopr-btn-secondary aktopr-modal-close">Otkaži</button>
                <button type="submit" class="aktopr-btn aktopr-btn-primary">Sačuvaj</button>
            </div>
        </form>
    </div>
</div>
