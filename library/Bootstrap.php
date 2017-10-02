<?php

/**
 * Composer autoloader from municipio
 */
if (file_exists(MUNICIPIO_PATH . 'vendor/autoload.php')) {
    require_once MUNICIPIO_PATH . 'vendor/autoload.php';
}

/**
 * Composer autoloader from abspath
 */
if (file_exists(dirname(ABSPATH) . '/vendor/autoload.php')) {
    require_once dirname(ABSPATH) . '/vendor/autoload.php';
}

/**
 * Psr4ClassLoader
 */
require_once MUNICIPIO_PATH . 'library/Vendor/Psr4ClassLoader.php';
require_once MUNICIPIO_PATH . 'library/Public.php';

/**
 * ACF
 */
require_once ABSPATH . 'wp-admin/includes/plugin.php';
if (file_exists(MUNICIPIO_PATH . 'vendor/acf/acf.php') && !is_plugin_active('advanced-custom-fields-pro/acf.php')) {
    new \Municipio\Acf();

    if (!class_exists('acf')) {
        require_once MUNICIPIO_PATH . 'vendor/acf/acf.php';
    }
}

/**
 * ACF Addon
 */
require_once ABSPATH . 'wp-admin/includes/plugin.php';
if (file_exists(MUNICIPIO_PATH . 'plugins/advanced-custom-fields-font-awesome/acf-font-awesome.php')) {
    require_once MUNICIPIO_PATH . 'plugins/advanced-custom-fields-font-awesome/acf-font-awesome.php';
}

/**
 * Initialize autoloader (psr4)
 */
$loader = new Municipio\Vendor\Psr4ClassLoader();
$loader->addPrefix('Municipio', MUNICIPIO_PATH . 'library');
$loader->addPrefix('Municipio', MUNICIPIO_PATH . 'source/php/');
$loader->register();

/**
 * Acf auto import and export
 */
add_action('init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('municipio');
    $acfExportManager->setExportFolder(MUNICIPIO_PATH . 'library/AcfFields');
    $acfExportManager->autoExport(array(
        'options-page-display'             => 'group_56c33cf1470dc',
        'options-page-navigation'          => 'group_56d83cff12bb3',
        'options-theme-404'                => 'group_56d41dbd7e501',
        'options-theme-author'             => 'group_56caee123c53f',
        'options-theme-color-scheme'       => 'group_56a0a7dcb5c09',
        'options-theme-cookie-consent'     => 'group_56bc6b6466df1',
        'options-theme-custom-css'         => 'group_57148a9b42fd5',
        'options-theme-custom-js'          => 'group_573184999aa2c',
        'options-theme-custom-post-type'   => 'group_56b34353ef1eb',
        'options-theme-custom-taxonomy'    => 'group_56c5e23aa271c',
        'options-theme-editor-formats'     => 'group_57ff4f49ac8c1',
        'options-theme-favicon'            => 'group_56cc39aba8782',
        'options-theme-footer-icons'       => 'group_56af210c945f0',
        'options-theme-footer-layout'      => 'group_56e804931bd1e',
        'options-theme-footer-logotype'    => 'group_56c5d41852a31',
        'options-theme-google-analytics'   => 'group_56c5c4d919d29',
        'options-theme-google-translate'   => 'group_56cc6f8fe86d3',
        'options-theme-header'             => 'group_56a22a9c78e54',
        'options-theme-header-logotype'    => 'group_56c5d34a261f5',
        'options-theme-header-subtitle'    => 'group_584923bd30bfe',
        'options-theme-header-tagline'     => 'group_56f25b68658cd',
        'options-theme-logotype'           => 'group_56a0f1f7826dd',
        'options-theme-mobile-navigation'  => 'group_5885cbc79fc34',
        'options-theme-post-timestamps'    => 'group_56cacd2f1873f',
        'options-theme-post-types'         => 'group_56c6ba934d682',
        'options-theme-primary-navigation' => 'group_56e935ea546ce',
        'options-theme-scroll-elevator'    => 'group_5825be470579f',
        'options-theme-search'             => 'group_569fa7edcdd6b',
        'options-theme-search-display'     => 'group_56a72f6430912',
        'options-theme-search-result'      => 'group_586df81d53d0f',
        'options-theme-share'              => 'group_56c431971df46',
        'options-theme-sub-navigation'     => 'group_56e941cae1ed2',
        'user-author-image'                => 'group_56c714b46105e',
        'widget-contact'                   => 'group_56c58bade87dc'
    ));
    $acfExportManager->import();
});

/**
 * Initialize app
 */
if (function_exists('get_field')) {
    new Municipio\App();
} else {
    if (!(defined('WP_CLI') && WP_CLI)) {

        //Be shure to enable ACF
        if (!is_admin()) {
            if (is_user_logged_in()) {
                wp_redirect(admin_url('plugins.php'));
                exit;
            } else {
                if (!in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) {
                    auth_redirect();
                }
            }
        } else {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p>Please active ACF (PRO) to proceed.</p></div>';
            });
        }
    }
}
