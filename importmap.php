<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'tailwind_app' => [
        'path' => './assets/tailwind/app.js',
        'entrypoint' => true,
    ],
    'app' => [
        'path' => './assets/js/app.js',
        'entrypoint' => true,
    ],
    'ea_dashboard' => [
        'path' => './assets/js/dashboard.js',
        'entrypoint' => true,
    ],
    'app_event_counter' => [
        'path' => './assets/js/EventCountDown.js',
        'entrypoint' => true,
    ],
    'app_map' => [
        'path' => './assets/js/map.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.12',
    ],
    'bootstrap' => [
        'version' => '5.3.3',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.3',
        'type' => 'css',
    ],
    '@fortawesome/fontawesome-free' => [
        'version' => '6.7.2',
    ],
    '@fortawesome/fontawesome-free/css/fontawesome.min.css' => [
        'version' => '6.7.2',
        'type' => 'css',
    ],
    'ol' => [
        'version' => '10.3.1',
    ],
    'color-space/lchuv.js' => [
        'version' => '2.0.1',
    ],
    'color-rgba' => [
        'version' => '3.0.0',
    ],
    'color-space/rgb.js' => [
        'version' => '2.0.1',
    ],
    'color-space/xyz.js' => [
        'version' => '2.0.1',
    ],
    'rbush' => [
        'version' => '4.0.1',
    ],
    'color-parse' => [
        'version' => '2.0.2',
    ],
    'color-space/hsl.js' => [
        'version' => '2.0.1',
    ],
    'quickselect' => [
        'version' => '3.0.0',
    ],
    'color-name' => [
        'version' => '2.0.0',
    ],
    'countdown-tmr' => [
        'version' => '1.0.0',
    ],
    'chart.js' => [
        'version' => '3.9.1',
    ],
    'stimulus-use' => [
        'version' => '0.52.3',
    ],
];
