<?php

namespace Municipio\Theme;

class Enqueue
{
    public $defaultPrimeName = 'hbg-prime';
    public $styleguideUri;

    public function __construct()
    {

        //Set stylegudei url
        add_action('init', array($this, 'init'));

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'style'), 5);
        add_action('wp_enqueue_scripts', array($this, 'script'), 5);

        // Admin style
        add_action('admin_enqueue_scripts', array($this, 'adminStyle'), 999);

        add_action('wp_enqueue_scripts', array($this, 'googleTagManager'), 999);
        add_action('wp_enqueue_scripts', array($this, 'googleReCaptcha'), 999);

        // Removes version querystring from scripts and styles
        add_filter('script_loader_src', array($this, 'removeScriptVersion'), 15, 1);
        add_filter('style_loader_src', array($this, 'removeScriptVersion'), 15, 1);

        // Removes generator tag
        add_filter('the_generator', create_function('', 'return "";'));

        //Move scripts to footer
        add_action('wp_print_scripts', array($this, 'moveScriptsToFooter'));

        //Enable defered loading
        add_action('clean_url', array($this, 'deferedLoadingJavascript'));

        // Plugin filters (script/style related)
        add_filter('gform_init_scripts_footer', '__return_true');
        add_filter('gform_cdata_open', array($this, 'wrapGformCdataOpen'));
        add_filter('gform_cdata_close', array($this, 'wrapGformCdataClose'));
    }


    /**
     * Set url to styleguide
     * @return void
     */
    public function init()
    {
        $this->styleguideUri = rtrim(apply_filters('Municipio/theme/styleguide_uri', "//helsingborg-stad.github.io/styleguide-web/dist/"), '/') . '/';

        if ($this->styleguideUri && !defined('MUNICIPIO_STYLEGUIDE_URI')) {
            define('MUNICIPIO_STYLEGUIDE_URI', $this->styleguideUri);
        }
    }

    /**
     * Set current theme from db.
     * @return bool
     */
    public static function getStyleguideTheme()
    {
        return apply_filters('Municipio/theme/key', get_field('color_scheme', 'option'));
    }

    public function wrapGformCdataOpen($content)
    {
        $content = 'document.addEventListener( "DOMContentLoaded", function() { ';
        return $content;
    }

    public function wrapGformCdataClose($content)
    {
        $content = ' }, false );';
        return $content;
    }

    /**
     * Enqueue admin style
     * @return void
     */
    public function adminStyle()
    {
        wp_register_style('helsingborg-se-admin', get_template_directory_uri(). '/assets/dist/' . \Municipio\Helper\CacheBust::name('css/admin.min.css'), '', null);
        wp_enqueue_style('helsingborg-se-admin');

        wp_register_script('helsingborg-se-admin', get_template_directory_uri() . '/assets/dist/' . \Municipio\Helper\CacheBust::name('js/admin.min.js'), '', null, true);
        wp_enqueue_script('helsingborg-se-admin');
    }

    /**
     * Enqueue styles
     * @return void
     */
    public function style()
    {

        // Tell jquery dependents to wait for prime instead.
        if (!apply_filters('Municipio/load-wp-jquery', false)) {
            wp_deregister_script('jquery');
            add_action('wp_enqueue_scripts', array($this, 'waitForPrime'));
        }

        //Load from local developement enviroment
        if ((defined('DEV_MODE') && DEV_MODE === true) || (isset($_GET['DEV_MODE']) && $_GET['DEV_MODE'] === 'true')) {
            wp_register_style($this->defaultPrimeName, '//hbgprime.dev/dist/css/hbg-prime-' . self::getStyleguideTheme() . '.dev.css', '', '1.0.0');
        } else {
            //Check for version number lock files.
            if (defined('STYLEGUIDE_VERSION') && STYLEGUIDE_VERSION != "") {
                wp_register_style($this->defaultPrimeName, $this->styleguideUri . STYLEGUIDE_VERSION . '/css/hbg-prime-' . self::getStyleguideTheme() . '.min.css', '', STYLEGUIDE_VERSION);
            } else {
                wp_register_style($this->defaultPrimeName, $this->styleguideUri . 'css/hbg-prime-' . self::getStyleguideTheme() . '.min.css', '', 'latest');
            }
        }

        wp_enqueue_style($this->defaultPrimeName);

        wp_register_style('municipio', get_template_directory_uri(). '/assets/dist/' . \Municipio\Helper\CacheBust::name('css/app.min.css'), '', null);
        wp_enqueue_style('municipio');
    }

    /**
     * Enqueue scripts
     * @return void
     */
    public function script()
    {
        //Load from local developement enviroment
        if ((defined('DEV_MODE') && DEV_MODE === true) || (isset($_GET['DEV_MODE']) && $_GET['DEV_MODE'] === 'true')) {
            wp_register_script($this->defaultPrimeName, '//hbgprime.dev/dist/js/hbg-prime.min.js', '', '1.0.0', true);
        } else {

            //Check for version number lock files.
            if (defined('STYLEGUIDE_VERSION') && STYLEGUIDE_VERSION != "") {
                wp_register_script($this->defaultPrimeName, '//helsingborg-stad.github.io/styleguide-web/dist/' . STYLEGUIDE_VERSION . '/js/hbg-prime.min.js', '', STYLEGUIDE_VERSION);
            } else {
                wp_register_script($this->defaultPrimeName, '//helsingborg-stad.github.io/styleguide-web/dist/js/hbg-prime.min.js', '', 'latest');
            }
        }

        //Localization
        wp_localize_script($this->defaultPrimeName, 'HbgPrimeArgs', array(
            'api' => array(
                'root' => esc_url_raw(rest_url()),
                'nonce' => wp_create_nonce('wp_rest')
            ),
            'cookieConsent' => array(
                'show'      => get_field('cookie_consent_active', 'option'),
                'message'   => get_field('cookie_consent_message', 'option'),
                'button'    => get_field('cookie_consent_button', 'option'),
                'placement' => get_field('cookie_consent_placement', 'option')
            ),
            'googleTranslate' => array(
                'gaTrack' => get_field('google_translate_ga_track', 'option'),
                'gaUA'    => get_field('google_analytics_ua', 'option')
            ),
            'scrollElevator' => array(
                'cta' => get_field('scroll_elevator_text', 'option'),
                'tooltip' => get_field('scroll_elevator_tooltio', 'option'),
                'tooltipPosition' => get_field('scroll_elevator_tooltio_position', 'option')
            ),
            'tableFilter' => array(
                'empty' => apply_filters('municipio/tablefilter/empty', __('No matching content found…', 'municipio'))
            )
        ));
        wp_enqueue_script($this->defaultPrimeName);

        wp_register_script('municipio', get_template_directory_uri() . '/assets/dist/js/packaged.min.js', '', '1.0.0', true);
        wp_localize_script('municipio', 'MunicipioLang', array(
            'printbreak' => array(
                'tooltip' => __('Insert Print Page Break tag', 'municipio')
            )
        ));
        wp_enqueue_script('municipio');

        //Load polyfill SAAS
        wp_enqueue_script('polyfill', 'https://cdn.polyfill.io/v2/polyfill.min.js', 'municipio');

        //Comment reply
        if (is_singular() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
    }

    public function moveScriptsToFooter()
    {
        global $wp_scripts;
        $notInFooter = array_diff($wp_scripts->queue, $wp_scripts->in_footer);
        $wp_scripts->in_footer = array_merge($wp_scripts->in_footer, $notInFooter);
    }

    /**
     * Enqueue Google reCAPTCHA
     * @return void
     */
    public function googleReCaptcha()
    {
        if(is_single() && comments_open() && !is_user_logged_in() && get_option('comment_registration') == 0) {
            wp_register_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', '', '1.0.0', true);
            wp_enqueue_script('google-recaptcha');
        }
    }

    /**
     * Enqueues Google Tag Manager
     * @return void
     */
    public function googleTagManager()
    {
        $user = get_field('google_tag_manager_id', 'option');

        if (empty($user)) {
            return;
        }

        add_action('wp_footer', function () use ($user) {
            echo "<noscript><iframe src=\"//www.googletagmanager.com/ns.html?id={$user}\"
                    height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>
                <script>
                    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                    '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                    })(window,document,'script','dataLayer','{$user}');
                </script>";
        }, 999);
    }

    /**
     * Removes querystring from any scripts/styles loaded from "helsingborg" or "localhost"
     * @param  string $src The soruce path
     * @return string      The source path without any querystring
     */
    public function removeScriptVersion($src)
    {
        $parts = explode('?', $src);
        if (strpos($parts[0], 'helsingborg') > -1 || strpos($parts[0], 'localhost') > -1) {
            return $parts[0];
        } else {
            return $src;
        }
    }

    /**
     * Making deffered loading of scripts a posibillity (remnoves unwanted renderblocking js)
     * @param  string $src The soruce path
     * @return string      The source path without any querystring
     */
    public function deferedLoadingJavascript($url)
    {
        if (is_admin() || false !== strpos($url, 'readspeaker.com') || false === strpos($url, '.js')) {
            return $url;
        }

        if (isset($_GET['gf_page']) && $_GET['gf_page'] == 'preview') {
            return $url;
        }

        return $url . "' defer='defer";
    }

    /**
     * Change jquery deps to hbgprime deps
     * @return void
     */
    public function waitForPrime()
    {
        $wp_scripts = wp_scripts();

        if (!is_admin() && isset($wp_scripts->registered)) {
            foreach ($wp_scripts->registered as $key => $item) {
                if (is_array($item->deps) && !empty($item->deps)) {
                    foreach ($item->deps as $depkey => $depencency) {
                        $item->deps[$depkey] = str_replace("jquery", $this->defaultPrimeName, strtolower($depencency));
                    }
                }
            }
        }
    }
}
