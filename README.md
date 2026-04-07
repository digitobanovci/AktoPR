# Auto Akt oPR - Akt o Proceni Rizika

WordPress plugin za automatsko kreiranje Akta o proceni rizika na radnom mestu i u radnoj sredini.

## Zakonska osnova

- **Zakon o bezbednosti i zdravlju na radu** ("Sl. glasnik RS", br. 35/2023 i 96/2024)
- **Pravilnik o načinu i postupku procene rizika** ("Sl. glasnik RS", br. 76/2024)

## Funkcionalnosti

### Core funkcionalnosti
- [x] Multitenant arhitektura (više klijenata u jednoj instalaciji)
- [x] Upravljanje klijentima (CRUD)
- [x] Upravljanje zaposlenima
- [x] Kreiranje master dokumenata
- [x] Prateća baza podataka

### Biblioteka
- [x] Propisi i zakonska regulativa
- [x] Koeficijenti za Kinney metodu (P, F, C)
- [x] Tekst blokovi za generisanje dokumenata
- [x] Standardne mere zaštite

### Dokument
- [x] Generisanje sekcija dokumenta
- [x] Procena rizika (Kinney metoda: R = P × F × C)
- [x] Matrica rizika
- [x] Praćenje statusa dokumenta

### Export
- [x] DOCX export (Word)
- [x] PDF/Print verzijak

### AI Integracija
- [x] OpenAI (GPT-4)
- [x] Anthropic (Claude)
- [x] xAI (Grok)
- [x] Lokalni Ollama model

### Bezbednost
- [x] Nonce verifikacija na AJAX pozivima
- [x] Prepared statements za SQL
- [x] Sanitizacija ulaza
- [x] Audit log svih akcija

## Instalacija

1. Kopirajte folder `auto-aktopr` u `/wp-content/plugins/`
2. Aktivirajte plugin u WordPress admin panelu
3. Idite na **Akt oPR > Podešavanja** i unesite AI API ključeve

## Korišćenje

### 1. Dodajte klijenta
Idite na **Akt oPR > Klijenti > Dodaj novog**

### 2. Dodajte zaposlene
**Akt oPR > Zaposleni > Dodaj novog**

### 3. Kreirajte dokument
**Akt oPR > Dokumenti > Novi dokument**

### 4. Popunite sekcije
U editoru dokumenta popunite sve sekcije:
- 1.1 Odluka o pokretanju
- 1.2 Podaci o poslodavcu
- 1.3 Stručni tim
- 1.4 Zakonska osnova
- 2.x Opis radnog procesa
- 3.x Sistematizacija radnih mesta
- 4.x Identifikacija opasnosti
- 5.x Procena rizika
- 6.x Mere zaštite

### 5. Export
Kliknite **Export** za preuzimanje u DOCX ili PDF formatu.

## Struktura baze podataka

### Tabele
- `aapr_propisi` - Propisi i zakoni
- `aapr_koeficijenti` - Koeficijenti za izračunavanje rizika
- `aapr_tekst_blokovi` - Predlošci teksta za sekcije
- `aapr_standardne_mere` - Mere za smanjenje rizika
- `aapr_opasnosti` - Identifikovane opasnosti
- `aapr_risici` - Procenjeni rizici
- `aapr_zaposleni` - Zaposleni kod klijenta
- `aapr_radna_mesta` - Sistematizacija radnih mesta
- `aapr_audit_log` - Log svih izmena

## API Endpoints

```
GET  /wp-json/aapr/v1/klijenti
POST /wp-json/aapr/v1/klijenti
GET  /wp-json/aapr/v1/klijenti/{id}
PUT  /wp-json/aapr/v1/klijenti/{id}
DEL  /wp-json/aapr/v1/klijenti/{id}

GET  /wp-json/aapr/v1/zaposleni
POST /wp-json/aapr/v1/zaposleni

GET  /wp-json/aapr/v1/master-dokument
POST /wp-json/aapr/v1/master-dokument
GET  /wp-json/aapr/v1/master-dokument/{id}

GET  /wp-json/aapr/v1/risici
POST /wp-json/aapr/v1/risici

POST /wp-json/aapr/v1/izracunaj-rizik
```

## Razvoj

```bash
# Kloniranje
git clone https://github.com/digitobanovci/AktoPR.git

# Uključite debug mode u wp-config.php
define('WP_DEBUG', true);

# Plugin se automatski učitava
```

## Zahtevi

- WordPress 6.0+
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+

## Licenca

GPL v2 or later

## Autor

digitobanovci
