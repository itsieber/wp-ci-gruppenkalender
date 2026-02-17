<?php
// Admin-Menüpunkt
add_action('admin_menu', function () {
    // merken, damit wir später Skripte nur hier laden
    if (empty($GLOBALS['sieber_tools_menu_created'])) {
    $icon_url = plugins_url('assets/ciicon.png', __FILE__); // oder .png

    add_menu_page(
        'Sieber Engineering',         // Page-Title (oben links)
        'Sieber Engineering',                       // Menü-Text (linke Sidebar)
        'manage_options',                // Cap
        'sieber-tools',
        'sieber_tools_render_page',
        $icon_url,           // <-- Datei-URL statt data:
        58                               // Position (optional)
    );
    $GLOBALS['sieber_tools_menu_created'] = true;
    // NACH dem Aufbau des Menüs den Auto-Unterpunkt ausblenden
    add_action('admin_menu', function () {
        remove_submenu_page('sieber-tools', 'sieber-tools');
    }, 999);
    }
    add_submenu_page(
        'sieber-tools',
        'CI Gruppenkalender Einstellungen',
        'Gruppenkalender',
        'manage_options',
        'ci-Gruppenkalender',
        'ci_Gruppenkalender_render_settings_page'
    );
});

// Einstellungsseite HTML
function ci_Gruppenkalender_render_settings_page() {
    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/wp-ci-gruppenkalender/ci-gruppenkalender.php');
    $changelog_file = plugin_dir_path(__DIR__) . 'CHANGELOG.md';
    $changelog = file_exists($changelog_file) ? file_get_contents($changelog_file) : '';
    ?>
    <div class="wrap">
        <h1>CI Gruppenkalender Einstellungen</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('ci_Gruppenkalender_settings');
            do_settings_sections('ci-Gruppenkalender');
            submit_button();
            ?>
        </form>
        <hr>
        <h2>Plugin-Info</h2>
        <p><strong>Version:</strong> <?php echo esc_html($plugin_data['Version']); ?></p>
        <h3>Changelog</h3>
        <pre style="background:#f0f0f0;padding:15px;max-width:600px;overflow:auto;"><?php echo esc_html($changelog); ?></pre>
    </div>
    <?php
}

// Registrierung der Settings, Sektionen und Felder
add_action('admin_init', function () {
    register_setting('ci_Gruppenkalender_settings', 'ci_Gruppenkalender_api_key');

    add_settings_section(
        'ci_Gruppenkalender_main_section',
        'API Einstellungen',
        null,
        'ci-Gruppenkalender'
    );

    add_settings_field(
        'ci_Gruppenkalender_api_key',
        'API Key',
        function () {
            $value = esc_attr(get_option('ci_Gruppenkalender_api_key', ''));
            echo '<input type="text" name="ci_Gruppenkalender_api_key" value="' . $value . '" class="regular-text">';
        },
        'ci-Gruppenkalender',
        'ci_Gruppenkalender_main_section'
    );
});
