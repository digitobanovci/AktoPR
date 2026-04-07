<?php

class Auto_AktoPR_AI {

    private array $api_keys;
    private string $provider;

    public function __construct() {
        $this->api_keys = get_option('aapr_api_keys', []);
        $this->provider = $this->detect_provider();
    }

    private function detect_provider(): string {
        if (!empty($this->api_keys['openai'])) {
            return 'openai';
        }
        if (!empty($this->api_keys['anthropic'])) {
            return 'anthropic';
        }
        if (!empty($this->api_keys['xai'])) {
            return 'xai';
        }
        if (!empty($this->api_keys['ollama_url'])) {
            return 'ollama';
        }
        return 'none';
    }

    public function is_configured(): bool {
        return $this->provider !== 'none';
    }

    public function get_provider(): string {
        return $this->provider;
    }

    public function generisi_tekst(string $tip, array $kontekst = []): array {
        if (!$this->is_configured()) {
            return ['success' => false, 'error' => 'AI nije konfigurisan'];
        }

        $prompt = $this->build_prompt($tip, $kontekst);
        
        switch ($this->provider) {
            case 'openai':
                return $this->call_openai($prompt);
            case 'anthropic':
                return $this->call_anthropic($prompt);
            case 'xai':
                return $this->call_xai($prompt);
            case 'ollama':
                return $this->call_ollama($prompt);
            default:
                return ['success' => false, 'error' => 'Nepoznat provider'];
        }
    }

    private function build_prompt(string $tip, array $kontekst): string {
        $system_prompt = $this->get_system_prompt($tip);
        
        $user_context = '';
        if (!empty($kontekst['klijent'])) {
            $user_context .= "Klijent: " . $kontekst['klijent'] . "\n";
        }
        if (!empty($kontekst['delatnost'])) {
            $user_context .= "Delatnost: " . $kontekst['delatnost'] . "\n";
        }
        if (!empty($kontekst['radna_mesta'])) {
            $user_context .= "Radna mesta: " . implode(', ', $kontekst['radna_mesta']) . "\n";
        }
        if (!empty($kontekst['opasnosti'])) {
            $user_context .= "Identifikovane opasnosti: " . implode(', ', $kontekst['opasnosti']) . "\n";
        }
        if (!empty($kontekst['dodatne_info'])) {
            $user_context .= "\nDodatne informacije:\n" . $kontekst['dodatne_info'] . "\n";
        }
        
        return $system_prompt . "\n\n" . $user_context;
    }

    private function get_system_prompt(string $tip): string {
        $prompts = [
            'uvod' => 'Napiši uvodni tekst za Akt o proceni rizika prema Pravilniku o načinu i postupku procene rizika (Sl. glasnik RS br. 76/2024). Tekst treba da bude formalni, pravnički stil, na srpskom jeziku. Ne koristi liste, samo paragrafi.',
            
            'opis_delatnosti' => 'Napiši opis delatnosti za Akt o proceni rizika. Opis treba da objasni čime se kompanija bavi, koje procese obavlja i koje su karakteristike radnog okruženja. Na srpskom jeziku, formalni stil.',
            
            'opis_radnog_procesa' => 'Napiši opis radnog procesa za Akt o proceni rizika. Detaljno opiši kako se poslovi obavljaju, koje aktivnosti su uključene, koje alate i opremu radnici koriste. Na srpskom jeziku.',
            
            'sistematizacija' => 'Kreiraj tabelarnu sistematizaciju radnih mesta za Akt o proceni rizika. Za svako radno mesto navedi: naziv, šifru, opis posla, broj izvršilaca, uslove rada i potrebnu kvalifikaciju.',
            
            'identifikacija_opasnosti' => 'Na osnovu opisa delatnosti i radnih procesa, identifikuj sve potencijalne opasnosti na radnom mestu. Kategorizuj ih po grupama (mehaničke, električne, hemijske, fizičke, ergonomske, psihosocijalne). Za svaku opasnost navedi izvor i moguće posledice.',
            
            'analiza_rizika' => 'Izvrši analizu rizika za identifikovane opasnosti. Koristi Kinney metodu (R = P × F × C). Proceni verovatnoću nastanka, učestalost izlaganja i težinu posledica za svaki rizik. Daj preporuke za prioritetne rizike.',
            
            'mere_zastite' => 'Predloži mere za sprečavanje i smanjenje rizika na osnovu prethodne analize. Kategorizuj mere: tehničke, organizacione, lična zaštitna oprema, higijenske i obuke. Navesti konkretne aktivnosti i rokove.',
            
            'zakljucak' => 'Napiši zaključak za Akt o proceni rizika. Sumiraj glavne nalaze, naglasi najvažnije rizike i mere, i definiši praćenje i ažuriranje dokumenta. Na srpskom jeziku, formalni stil.',
            
            'prilozi' => 'Predloži sadržaj priloga za Akt o proceni rizika (liste radnika, oprema, propisi, itd.).',
        ];
        
        return $prompts[$tip] ?? 'Generiši profesionalan tekst za dokument na srpskom jeziku.';
    }

    private function call_openai(string $prompt): array {
        $api_key = $this->api_keys['openai'];
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'model' => 'gpt-4-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 4000,
                'temperature' => 0.7,
            ]),
            'timeout' => 120,
        ]);
        
        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return ['success' => false, 'error' => $body['error']['message']];
        }
        
        return [
            'success' => true,
            'text' => $body['choices'][0]['message']['content'] ?? '',
            'model' => $body['model'] ?? 'gpt-4-turbo',
            'usage' => $body['usage'] ?? [],
        ];
    }

    private function call_anthropic(string $prompt): array {
        $api_key = $this->api_keys['anthropic'];
        
        $system_prompt = 'Ti si stručnjak za bezbednost i zdravlje na radu u Srbiji. Pišeš tekst za Akt o proceni rizika prema Pravilniku o načinu i postupku procene rizika (Sl. glasnik RS br. 76/2024).';
        
        $response = wp_remote_post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'model' => 'claude-3-5-sonnet-20241022',
                'max_tokens' => 4000,
                'system' => $system_prompt,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]),
            'timeout' => 120,
        ]);
        
        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return ['success' => false, 'error' => $body['error']['message']];
        }
        
        return [
            'success' => true,
            'text' => $body['content'][0]['text'] ?? '',
            'model' => 'claude-3-5-sonnet',
            'usage' => $body['usage'] ?? [],
        ];
    }

    private function call_xai(string $prompt): array {
        $api_key = $this->api_keys['xai'];
        
        $response = wp_remote_post('https://api.x.ai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'model' => 'grok-2-1212',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 4000,
                'temperature' => 0.7,
            ]),
            'timeout' => 120,
        ]);
        
        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return ['success' => false, 'error' => $body['error']['message']];
        }
        
        return [
            'success' => true,
            'text' => $body['choices'][0]['message']['content'] ?? '',
            'model' => 'grok-2',
        ];
    }

    private function call_ollama(string $prompt): array {
        $url = $this->api_keys['ollama_url'] ?? 'http://localhost:11434';
        
        $response = wp_remote_post($url . '/api/generate', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode([
                'model' => 'llama3.2',
                'prompt' => $prompt,
                'stream' => false,
            ]),
            'timeout' => 180,
        ]);
        
        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return ['success' => false, 'error' => $body['error']];
        }
        
        return [
            'success' => true,
            'text' => $body['response'] ?? '',
            'model' => 'llama3.2 (lokalni)',
        ];
    }

    public function chat(string $user_message, array $history = []): array {
        if (!$this->is_configured()) {
            return ['success' => false, 'error' => 'AI nije konfigurisan'];
        }
        
        $system_prompt = 'Ti si stručnjak za bezbednost i zdravlje na radu u Srbiji. Odgovaraš na pitanja o Aktu o proceni rizika, zakonskoj regulativi (Zakon o BZR, Pravilnik o proceni rizika), merama zaštite i najboljim praksama. Odgovaraj na srpskom jeziku.';
        
        $messages = array_merge(
            [['role' => 'system', 'content' => $system_prompt]],
            $history,
            [['role' => 'user', 'content' => $user_message]]
        );
        
        switch ($this->provider) {
            case 'openai':
                return $this->chat_openai($messages);
            case 'anthropic':
                return $this->chat_anthropic($system_prompt, $history, $user_message);
            default:
                return $this->generisi_tekst('uvod', ['dodatne_info' => $user_message]);
        }
    }

    private function chat_openai(array $messages): array {
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_keys['openai'],
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'model' => 'gpt-4-turbo',
                'messages' => $messages,
                'max_tokens' => 2000,
                'temperature' => 0.7,
            ]),
            'timeout' => 60,
        ]);
        
        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        return [
            'success' => true,
            'text' => $body['choices'][0]['message']['content'] ?? '',
        ];
    }

    private function chat_anthropic(string $system, array $history, string $user_message): array {
        $messages = [['role' => 'user', 'content' => $user_message]];
        
        $response = wp_remote_post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $this->api_keys['anthropic'],
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'model' => 'claude-3-5-sonnet-20241022',
                'max_tokens' => 2000,
                'system' => $system,
                'messages' => $messages,
            ]),
            'timeout' => 60,
        ]);
        
        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        return [
            'success' => true,
            'text' => $body['content'][0]['text'] ?? '',
        ];
    }
}
