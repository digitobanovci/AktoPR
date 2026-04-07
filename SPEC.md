<?php
/**
 * Auto AktoPR - Akt o Proceni Rizika
 * SPEC.md - Tehnička specifikacija
 * 
 * Verzija: 1.0.0
 * Datum: 2026-04-07
 * 
 * ## 1. Pregled Projekta
 * 
 * **Ime:** Auto AktoPR
 * **Tip:** WordPress Plugin (produkcijski-ready)
 * **Opis:** Kompletan sistem za izradu Akta o proceni rizika na radnom mestu i u radnoj sredini
 * **Zakon:** Pravilnik o načinu i postupku procene rizika (Sl. glasnik RS br. 76/2024)
 * 
 * ## 2. Arhitektura
 * 
 * ### 2.1 Multitenant Model
 * - Jedna WordPress instanca = jedan glavni korisnik licence
 * - Glavni korisnik kreira tim (pod-korisnike)
 * - Pod-korisnici imaju odvojene podatke (klijenti, zaposleni)
 * - Svi podaci su vlasništvo glavnog korisnika
 * 
 * ### 2.2 Korisnički Rolovi
 * 
 * | Rol | Opis | Capabilities |
 * |-----|------|--------------|
 * | administrator | Pun pristup | sve |
 * | strucno_lice_bzr | Stručno lice za BZR | edit_clients, edit_employees, edit_positions, manage_akt |
 * | asistent | Pomoćni radnik | read_clients, edit_own_clients |
 * | klijent | Read-only pristup | read_own_data |
 * 
 * ## 3. Custom Post Types
 * 
 * ### 3.1 klijent (Client)
 * - **Slug:** aapr_klijent
 * - **Capabilities:** 
 *   - Zasebni podaci po korisniku (author)
 *   - Svi podaci vidljivi administratoru
 * 
 * ### 3.2 zaposleni (Employee)
 * - **Slug:** aapr_zaposleni
 * - **Post Parent:** klijent
 * - **Capabilities:** Many-to-many sa klijentima
 * 
 * ### 3.3 master_dokument (Master Document)
 * - **Slug:** aapr_master_dokument
 * - **Post Parent:** klijent
 * - **Sadrži:** Kompletan Akt o proceni rizika
 * 
 * ### 3.4 modul (Module Data)
 * - **Slug:** aapr_modul
 * - **Post Parent:** master_dokument
 * - **Tipovi:** modul_1 do modul_10
 * 
 * ## 4. Custom Database Tables
 * 
 * ### 4.1 aapr_propisi
 * ```
 * id (BIGINT, PK, AUTO_INCREMENT)
 * sifra (VARCHAR 50)
 * naziv (TEXT)
 * vrsta (ENUM: zakon, pravilnik, uredba, standard)
 * link (TEXT)
 * clanovi (TEXT - JSON)
 * datum_objave (DATE)
 * napomene (TEXT)
 * created_by (BIGINT)
 * created_at (DATETIME)
 * updated_at (DATETIME)
 * ```
 * 
 * ### 4.2 aapr_koeficijenti
 * ```
 * id (BIGINT, PK, AUTO_INCREMENT)
 * naziv (VARCHAR 255)
 * tip (ENUM: kinney, pearson, matrica)
 * kategorija (VARCHAR 100)
 * vrednost_min (DECIMAL 5,2)
 * vrednost_max (DECIMAL 5,2)
 * opis (TEXT)
 * boja (VARCHAR 7) - hex boja za UI
 * created_at (DATETIME)
 * updated_at (DATETIME)
 * ```
 * 
 * ### 4.3 aapr_tekst_blokovi
 * ```
 * id (BIGINT, PK, AUTO_INCREMENT)
 * sifra (VARCHAR 50)
 * naslov (VARCHAR 255)
 * sadrzaj (LONGTEXT)
 * sekcija (VARCHAR 50) - npr: 1.1, 2.1, 3.2.1
 * tip_uslova (VARCHAR 100) - npr: delatnost, zanimanje, tip_okruzenja
 * vrednost_uslova (VARCHAR 255)
 * prioritet (INT)
 * aktivan (BOOLEAN)
 * created_at (DATETIME)
 * updated_at (DATETIME)
 * ```
 * 
 * ### 4.4 aapr_standardne_mere
 * ```
 * id (BIGINT, PK, AUTO_INCREMENT)
 * sifra (VARCHAR 50)
 * naziv (TEXT)
 * opis (TEXT)
 * tip (ENUM: tehnicka, organizaciona, ppe, higijenska, osposobljavanje)
 * prioritet (ENUM: visok, srednji, nizak)
 * rok_dana (INT)
 * grupa_opasnosti (VARCHAR 100)
 * created_at (DATETIME)
 * updated_at (DATETIME)
 * ```
 * 
 * ### 4.5 aapr_audit_log
 * ```
 * id (BIGINT, PK, AUTO_INCREMENT)
 * user_id (BIGINT)
 * action (VARCHAR 100)
 * object_type (VARCHAR 50)
 * object_id (BIGINT)
 * old_value (LONGTEXT)
 * new_value (LONGTEXT)
 * ip_address (VARCHAR 45)
 * user_agent (TEXT)
 * created_at (DATETIME)
 * ```
 * 
 * ### 4.6 aapr_risici
 * ```
 * id (BIGINT, PK, AUTO_INCREMENT)
 * master_dokument_id (BIGINT)
 * modul_id (BIGINT)
 * zanimanje_id (BIGINT)
 * opasnost_id (BIGINT)
 * verovatnoca (DECIMAL 3,1)
 * ucestalost (DECIMAL 3,1)
 * tezina (DECIMAL 3,1)
 * rizik (DECIMAL 6,2) - izracunato
 * nivo_rizika (ENUM: nizak, srednji, visok, kritican)
 * created_at (DATETIME)
 * updated_at (DATETIME)
 * ```
 * 
 * ## 5. Master Dokument Struktura (Sadržaj)
 * 
 * Stranica | Sekcija | Opis
 * ---------|---------|------
 * 1 | 1 | Naslovna strana
 * 2 | 1.1 | Odluka o pokretanju
 * 3 | 1.2 | Podaci o poslodavcu
 * 4 | 1.3 | Podaci o timu
 * 5 | 1.4 | Zakonska osnova
 * 7-17 | 2 | Opis radnog procesa
 * 18-49 | 3 | Sistematizacija radnih mesta
 * 50-79 | 4 | Identifikacija opasnosti
 * 80-124 | 5 | Procena rizika (Kinney)
 * 125-141 | 6 | Mere zaštite
 * 142-159 | 7 | Izveštaji i evidencije
 * 160-164 | 8 | Zaključak
 * 165-179 | 9 | Prilozi
 * 180-182 | 10 | Izmene i dopune
 * 
 * ## 6. Moduli (Čarobnjaci)
 * 
 * | # | Naziv | Sekcije | Prioritet |
 * |---|-------|---------|-----------|
 * | 1 | Osnovni podaci i Uvod | 1.1-1.4 | 1 |
 * | 2 | Opis radnog procesa | 2.1-2.4 | 2 |
 * | 3 | Sistematizacija radnih mesta | 3.1-3.2.5 | 1 |
 * | 4 | Identifikacija opasnosti | 4.1-4.4 | 2 |
 * | 5 | Procena rizika | 5.1-5.3 | 1 |
 * | 6 | Mere zaštite i rokovi | 6.1-6.4 | 2 |
 * | 7 | Izveštaji i evidencije | 7.1-7.4 | 3 |
 * | 8 | Zaključak | 8.1-8.2 | 3 |
 * | 9 | Prilozi | 9.1-9.4 | 3 |
 * | 10 | Istorija izmena | 10.1-10.2 | 2 |
 * 
 * ## 7. AI Integracija
 * 
 * ### 7.1 Podržani provajderi
 * - OpenAI (GPT-4)
 * - Anthropic (Claude)
 * - xAI (Grok)
 * - Lokalni model (Ollama)
 * 
 * ### 7.2 System Prompts
 * Svaki modul ima zaseban system prompt sa:
 * - Kontekstom klijenta
 * - Podacima iz prethodnih modula
 * - Relevantnim propisima
 * - Koeficijentima
 * - Generičkim tekst blokovima
 * 
 * ## 8. API Endpoints
 * 
 * ### 8.1 REST API
 * - GET/POST /wp-json/aapr/v1/klijenti
 * - GET/POST /wp-json/aapr/v1/zaposleni
 * - GET/POST /wp-json/aapr/v1/master-dokument
 * - GET/POST /wp-json/aapr/v1/sekcija/{id}
 * - POST /wp-json/aapr/v1/generate
 * - GET /wp-json/aapr/v1/export/{id}
 * 
 * ## 9. Export Formati
 * 
 * - **.docx** - Microsoft Word (PhpSpreadsheet/PhpWord)
 * - **.pdf** - TCPDF ili DomPDF
 * 
 * ## 10. Bezbednost
 * 
 * - Svi AJAX pozivi zahtevaju nonce verifikaciju
 * - CSRF zaštita na svim formama
 * - SQL injection prevencija (prepared statements)
 * - XSS prevencija (esc_html, esc_attr)
 * - Kapacitije zasnovane na rolama
 * - Audit log svih izmena
 * 
 * ## 11. Performanse
 * 
 * - Lazy loading za velike tabele
 * - Caching API odgovora
 * - Indexed database kolone
 * - Pagination za dokumente >100 strana
 * 
 * ## 12. TODO Lista
 * 
 * [ ] Core: Database setup + migrations
 * [ ] Core: CPT Registration
 * [ ] Core: Roles + Capabilities
 * [ ] Admin: Dashboard layout
 * [ ] Admin: Klijenti CRUD
 * [ ] Admin: Zaposleni CRUD
 * [ ] Admin: Master Document Engine
 * [ ] Module 1: Osnovni podaci
 * [ ] Module 3: Sistematizacija (prioritet!)
 * [ ] Module 5: Procena rizika (prioritet!)
 * [ ] AI: OpenAI integration
 * [ ] Export: DOCX generation
 * [ ] Export: PDF generation
 * [ ] Biblioteke: Propisi seed
 * [ ] Biblioteke: Koeficijenti seed
 * [ ] Biblioteke: Tekst blokovi seed
 * [ ] Biblioteke: Standardne mere seed
