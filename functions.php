<?php

function enqueue_devextreme_assets() {
    wp_enqueue_style('devextreme-css', 'https://cdnjs.cloudflare.com/ajax/libs/devextreme-dist/25.1.3/css/dx.light.css');
    wp_enqueue_script('jquery'); // Pflicht für DevExtreme
    wp_enqueue_script('devextreme', 'https://cdnjs.cloudflare.com/ajax/libs/devextreme-dist/25.1.3/js/dx.all.js', array('jquery'), null, true);
    wp_enqueue_script('devextreme-lang-de', 'https://cdn3.devexpress.com/jslib/25.1.3/js/localization/dx.messages.de.js', array('devextreme'), null, true);
    wp_enqueue_script('devextreme-license', plugin_dir_url(__FILE__) . 'js/devextreme-license.js.php', array(), null, true);

    // Schweizer Lokalisierung aktivieren
    wp_add_inline_script('devextreme-lang-de', 'DevExpress.localization.locale("de");');
}
add_action('wp_enqueue_scripts', 'enqueue_devextreme_assets');

function ci_gruppenkalender_enqueue_styles() {
    wp_enqueue_style(
        'ci-gruppenkalender-style',
        plugin_dir_url(__FILE__) . 'css/style.css',
        array(),
        '1.0'
    );
    wp_enqueue_script(
        'ci-gruppenkalender',
        plugin_dir_url(__FILE__) . 'js/ci-gruppenkalender.js.php',
        array('jquery'),
        '1.0',
        true // → am Footer einfügen
    );
}
add_action('wp_enqueue_scripts', 'ci_gruppenkalender_enqueue_styles');
