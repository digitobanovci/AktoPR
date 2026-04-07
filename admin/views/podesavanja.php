<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap aapr-wrap">
    <h1>
        <span class="dashicons dashicons-admin-settings" style="font-size: 24px; width: 24px; height: 24px;"></span>
        Podešavanja
    </h1>
    
    <form id="aapr-podesavanja-form" method="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div class="postbox">
                        <h2 class="hndle"><span>AI Podešavanja</span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="openai_key">OpenAI API Key</label></th>
                                    <td>
                                        <input type="password" name="openai_key" id="openai_key" value="<?php echo esc_attr($api_keys['openai'] ?? ''); ?>" class="regular-text">
                                        <p class="description">Unesite API ključ sa platforme <a href="https://platform.openai.com" target="_blank">OpenAI</a></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="anthropic_key">Anthropic (Claude) API Key</label></th>
                                    <td>
                                        <input type="password" name="anthropic_key" id="anthropic_key" value="<?php echo esc_attr($api_keys['anthropic'] ?? ''); ?>" class="regular-text">
                                        <p class="description">Unesite API ključ sa <a href="https://console.anthropic.com" target="_blank">Anthropic console</a></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="xai_key">xAI (Grok) API Key</label></th>
                                    <td>
                                        <input type="password" name="xai_key" id="xai_key" value="<?php echo esc_attr($api_keys['xai'] ?? ''); ?>" class="regular-text">
                                        <p class="description">Unesite API ključ sa <a href="https://console.x.ai" target="_blank">xAI console</a></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="ollama_url">Lokalni Ollama URL</label></th>
                                    <td>
                                        <input type="url" name="ollama_url" id="ollama_url" value="<?php echo esc_attr($api_keys['ollama_url'] ?? 'http://localhost:11434'); ?>" class="regular-text">
                                        <p class="description">URL za lokalni Ollama server (bez API ključa)</p>
                                    </td>
                                </tr>
                            </table>
                            <p class="submit">
                                <button type="submit" name="save_api_keys" class="button button-primary">Sačuvaj API ključeve</button>
                            </p>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle"><span>Opšta podešavanja</span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="default_tip">Podrazumevana metoda</label></th>
                                    <td>
                                        <select name="default_tip" id="default_tip">
                                            <option value="kinney" <?php selected($settings['default_tip_izracunavanja'] ?? '', 'kinney'); ?>>Kinney metoda</option>
                                            <option value="pearson" <?php selected($settings['default_tip_izracunavanja'] ?? '', 'pearson'); ?>>Pearson matrica</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="default_firma">Podrazumevani naziv firme</label></th>
                                    <td>
                                        <input type="text" name="default_firma" id="default_firma" value="<?php echo esc_attr($settings['default_firma_naziv'] ?? ''); ?>" class="regular-text">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="default_adresa">Podrazumevana adresa</label></th>
                                    <td>
                                        <input type="text" name="default_adresa" id="default_adresa" value="<?php echo esc_attr($settings['default_firma_adresa'] ?? ''); ?>" class="regular-text">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="obavestenja_email">Email za obaveštenja</label></th>
                                    <td>
                                        <input type="email" name="obavestenja_email" id="obavestenja_email" value="<?php echo esc_attr($settings['obavestenja_email'] ?? ''); ?>" class="regular-text">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Auto-save</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="auto_save" value="1" <?php checked($settings['auto_save'] ?? false, true); ?>>
                                            Automatski čuvaj dokument svakih 60 sekundi
                                        </label>
                                    </td>
                                </tr>
                            </table>
                            <p class="submit">
                                <button type="submit" name="save_settings" class="button button-primary">Sačuvaj podešavanja</button>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div id="postbox-container-1" class="postbox-container">
                    <div class="postbox">
                        <h2 class="hndle"><span>Pomoć</span></h2>
                        <div class="inside">
                            <p><strong>Kako dobiti API ključ:</strong></p>
                            <ol>
                                <li>OpenAI: <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com</a></li>
                                <li>Anthropic: <a href="https://console.anthropic.com/settings/keys" target="_blank">console.anthropic.com</a></li>
                                <li>xAI: <a href="https://console.x.ai" target="_blank">console.x.ai</a></li>
                            </ol>
                            <p><strong>Lokalni model (Ollama):</strong></p>
                            <ol>
                                <li>Instalirajte <a href="https://ollama.com" target="_blank">Ollama</a></li>
                                <li>Pokrenite: <code>ollama serve</code></li>
                                <li>Preuzmite model: <code>ollama pull llama3.2</code></li>
                            </ol>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle"><span>Verzija</span></h2>
                        <div class="inside">
                            <p>Auto Akt oPR v<?php echo esc_html(AUTO_AKTOPR_VERSION); ?></p>
                            <p>WordPress <?php echo esc_html($GLOBALS['wp_version']); ?></p>
                            <p>PHP <?php echo esc_html(PHP_VERSION); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
