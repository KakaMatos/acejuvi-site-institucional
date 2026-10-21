<?php
/**
 * Plugin Name: ACEJUVI Landing Page Shortcode
 * Description: Exibe a landing page institucional da ACEJUVI por shortcode.
 * Version: 1.0.71
 * Author: MKTDumont
 * License: GPL-2.0-or-later
 * Text Domain: acejuvi-shortcode
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ACEJUVI_SHORTCODE_VERSION', '1.0.71');
define('ACEJUVI_SHORTCODE_FILE', __FILE__);
define('ACEJUVI_SHORTCODE_URL', plugin_dir_url(__FILE__));
define('ACEJUVI_WHATSAPP_NUMBER', '5564992846885');
define('ACEJUVI_LEAD_EMAIL', 'cejuvi@gmail.com');

function acejuvi_whatsapp_url($message) {
    return 'https://wa.me/' . ACEJUVI_WHATSAPP_NUMBER . '?text=' . rawurlencode($message);
}

function acejuvi_shortcode_instagram_feed_markup() {
    $shortcode = '[instagram-feed feed=1]';

    if (!shortcode_exists('instagram-feed')) {
        return '';
    }

    $feed = do_shortcode($shortcode);

    if (!is_string($feed) || trim($feed) === '' || trim($feed) === $shortcode) {
        return '';
    }

    return '<div class="acejuvi-instagram-live-feed">' . $feed . '</div>';
}

function acejuvi_shortcode_frontend_config() {
    $config = array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('acejuvi_lead_form'),
    );

    return '<script id="acejuvi-wordpress-config">window.ACEJUVI_WORDPRESS=' . wp_json_encode($config) . ';</script>';
}

function acejuvi_shortcode_submit_lead() {
    check_ajax_referer('acejuvi_lead_form', 'nonce');

    $type = isset($_POST['lead_type']) ? sanitize_key(wp_unslash($_POST['lead_type'])) : '';
    $labels = array(
        'voluntario' => 'Voluntário',
        'associado' => 'Associado',
        'patrocinador' => 'Patrocinador',
    );

    if (!isset($labels[$type])) {
        wp_send_json_error(array('message' => 'Tipo de cadastro inválido.'), 400);
    }

    $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';

    if ($name === '' || $email === '' || !is_email($email) || $phone === '') {
        wp_send_json_error(array('message' => 'Preencha nome, e-mail válido e WhatsApp.'), 400);
    }

    $field_labels = array(
        'name' => 'Nome',
        'email' => 'E-mail',
        'phone' => 'WhatsApp',
        'city' => 'Cidade',
        'availability' => 'Disponibilidade',
        'skills' => 'Habilidades / oficinas de interesse',
        'profession' => 'Profissão / área de atuação',
        'contribution' => 'Como deseja contribuir',
        'company' => 'Empresa / marca',
        'document' => 'CNPJ',
        'sponsorship' => 'Tipo de patrocínio de interesse',
        'message' => 'Mensagem',
    );

    $lines = array(
        'Novo cadastro pelo site ACEJUVI',
        'Tipo: ' . $labels[$type],
        '',
    );

    foreach ($field_labels as $key => $label) {
        if (!isset($_POST[$key])) {
            continue;
        }

        $value = sanitize_textarea_field(wp_unslash($_POST[$key]));
        if ($value !== '') {
            $lines[] = $label . ': ' . $value;
        }
    }

    $subject = 'Novo lead ACEJUVI - ' . $labels[$type];
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $name . ' <' . $email . '>',
    );

    $sent = wp_mail(ACEJUVI_LEAD_EMAIL, $subject, implode("\n", $lines), $headers);

    if (!$sent) {
        wp_send_json_error(array('message' => 'Não foi possível enviar agora. Tente novamente em instantes.'), 500);
    }

    wp_send_json_success(array('message' => 'Cadastro enviado com sucesso. A ACEJUVI entrará em contato em breve.'));
}
add_action('wp_ajax_acejuvi_submit_lead', 'acejuvi_shortcode_submit_lead');
add_action('wp_ajax_nopriv_acejuvi_submit_lead', 'acejuvi_shortcode_submit_lead');

function acejuvi_shortcode_critical_style() {
    return '<style id="acejuvi-wordpress-override-fix">
        @media (min-width: 1200px) {
            #acejuvi-site-header .acejuvi-mobile-toggle {
                display: none !important;
                visibility: hidden !important;
                pointer-events: none !important;
            }
        }

        #acejuvi-site-header .acejuvi-mobile-toggle {
            border: 0 !important;
            outline: 0 !important;
            color: #00236f !important;
            background: #eff4ff !important;
            box-shadow: 0 8px 20px rgba(30, 58, 138, 0.12) !important;
        }

        #acejuvi-site-header nav button,
        #acejuvi-site-header nav button:hover,
        #acejuvi-site-header nav button:focus,
        #acejuvi-site-header nav button:active,
        #acejuvi-site-header nav a,
        #acejuvi-site-header nav a:hover,
        #acejuvi-site-header nav a:focus,
        #acejuvi-site-header nav a:active {
            border-color: transparent !important;
            outline: 0 !important;
            box-shadow: none !important;
        }

        #acejuvi-site-header nav button,
        #acejuvi-site-header nav button:focus,
        #acejuvi-site-header nav button:active {
            background: transparent !important;
            color: #4a5568 !important;
        }

        #acejuvi-site-header nav button:hover,
        #acejuvi-site-header nav a:hover,
        #acejuvi-site-header nav a[aria-current="page"],
        #acejuvi-site-header a.is-section-active {
            background: transparent !important;
            color: #00236f !important;
        }

        body button,
        body a[role="button"],
        body .hero-dot,
        body .about-dot {
            outline: 0 !important;
            border-color: transparent !important;
            -webkit-tap-highlight-color: transparent !important;
        }

        body button:hover,
        body button:focus,
        body button:active,
        body a[role="button"]:hover,
        body a[role="button"]:focus,
        body a[role="button"]:active,
        body .hero-dot:hover,
        body .hero-dot:focus,
        body .hero-dot:active,
        body .about-dot:hover,
        body .about-dot:focus,
        body .about-dot:active {
            outline: 0 !important;
            border-color: transparent !important;
        }

        body button[aria-label*="foto"],
        body button[aria-label*="Foto"],
        body button[aria-label*="banner"],
        body button[aria-label*="Banner"],
        body button[aria-label*="Slide"],
        body button[aria-label*="slide"] {
            border: 0 !important;
            outline: 0 !important;
            color: #00236f !important;
        }

        body button[aria-label*="foto"]:hover,
        body button[aria-label*="foto"]:focus,
        body button[aria-label*="foto"]:active,
        body button[aria-label*="Foto"]:hover,
        body button[aria-label*="Foto"]:focus,
        body button[aria-label*="Foto"]:active,
        body button[aria-label*="banner"]:hover,
        body button[aria-label*="banner"]:focus,
        body button[aria-label*="banner"]:active,
        body button[aria-label*="Banner"]:hover,
        body button[aria-label*="Banner"]:focus,
        body button[aria-label*="Banner"]:active,
        body button[aria-label*="Slide"]:hover,
        body button[aria-label*="Slide"]:focus,
        body button[aria-label*="Slide"]:active,
        body button[aria-label*="slide"]:hover,
        body button[aria-label*="slide"]:focus,
        body button[aria-label*="slide"]:active {
            background: #e6eeff !important;
            color: #00236f !important;
            border: 0 !important;
            outline: 0 !important;
            box-shadow: 0 10px 24px rgba(30, 58, 138, 0.12) !important;
        }

        button[data-inscricoes-open],
        button[data-inscricoes-open]:hover,
        button[data-inscricoes-open]:focus,
        button[data-inscricoes-open]:active {
            background: #00236f !important;
            color: #ffffff !important;
            border-color: transparent !important;
            outline: 0 !important;
            box-shadow: 0 8px 18px rgba(0, 35, 111, 0.18) !important;
        }


        button[data-inscricoes-open] {
            min-height: 32px !important;
            padding: 0.45rem 1rem !important;
            gap: 0.4rem !important;
            font-size: 0.78rem !important;
            line-height: 1.1 !important;
            white-space: nowrap !important;
        }
        button[data-inscricoes-open] .material-symbols-outlined {
            font-size: 15px !important;
        }

        #pix-interactive-box .pix-amt-btn,
        #pix-interactive-box a,
        #pix-interactive-box button,
        .hero-dot,
        .about-dot {
            border-color: transparent !important;
            outline: 0 !important;
        }

        #pix-interactive-box .pix-amt-btn.bg-secondary,
        #pix-interactive-box .pix-amt-btn.bg-secondary:hover,
        #pix-interactive-box .pix-amt-btn.bg-secondary:focus,
        #pix-interactive-box .pix-amt-btn.bg-secondary:active {
            background: #0051d5 !important;
            color: #ffffff !important;
        }



          body { isolation: isolate; } main, footer { position: relative; z-index: 1; } header { position: relative; z-index: 50; } body::before, body::after { display: none !important; } #acejuvi-final-cta { min-height: 0 !important; height: auto !important; display: block !important; padding-top: 34px !important; padding-bottom: 34px !important; } #acejuvi-final-cta > .max-w-container-max { min-height: 0 !important; align-items: center !important; } @media (max-width: 767px) { #acejuvi-final-cta { padding-top: 28px !important; padding-bottom: 28px !important; } }
          section[data-acejuvi-youth-decorated] { position: relative; overflow: hidden; isolation: isolate; }
          .acejuvi-youth-bg { display: none !important; }
          .acejuvi-youth-section-bg { --mx: 0; --my: 0; position: absolute; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; opacity: .38; }
          .acejuvi-youth-float { position: absolute; left: var(--x); top: var(--y); width: var(--s, 62px); height: var(--s, 62px); border-radius: var(--br, 999px); display: flex; align-items: center; justify-content: center; color: var(--fg); background: radial-gradient(circle at 28% 22%, rgba(255,255,255,.96) 0 18%, rgba(255,255,255,.54) 19% 34%, var(--bg) 70%, rgba(255,255,255,.16) 100%); border: 1px solid rgba(255,255,255,.72); box-shadow: 0 20px 44px -30px rgba(0,35,111,.45), inset 0 1px 7px rgba(255,255,255,.68); transform: translate3d(calc(var(--mx) * var(--d) * 1px), calc(var(--my) * var(--d) * 1px), 0) rotate(var(--r)) scale(calc(1 + (var(--active, 0) * .08))); animation: acejuvi-youth-bubble 11s ease-in-out infinite; animation-delay: var(--delay); transition: transform 160ms ease-out, filter 160ms ease-out; will-change: transform; backdrop-filter: blur(2px); }
          .acejuvi-youth-float::before { content: ""; position: absolute; width: 28%; height: 28%; left: 18%; top: 16%; border-radius: 999px; background: rgba(255,255,255,.72); filter: blur(.2px); }
          .acejuvi-youth-float::after { content: ""; position: absolute; inset: 3px; border-radius: inherit; border: 1px solid rgba(255,255,255,.44); }
          .acejuvi-youth-float .material-symbols-outlined { position: relative; z-index: 1; font-size: calc(var(--s, 62px) * .42); font-variation-settings: "FILL" 0, "wght" 500; filter: drop-shadow(0 1px 0 rgba(255,255,255,.5)); }
          .acejuvi-youth-blue { --bg: rgba(49,107,243,.20); --fg: #0051d5; }
          .acejuvi-youth-green { --bg: rgba(0,151,91,.18); --fg: #005a38; }
          .acejuvi-youth-gold { --bg: rgba(255,178,0,.22); --fg: #9a6200; }
          .acejuvi-youth-red { --bg: rgba(255,96,96,.16); --fg: #b91c1c; }
          .acejuvi-youth-small { --s: 46px; }
          .acejuvi-youth-wide { --s: 72px; }
          @keyframes acejuvi-youth-bubble { 0%,100% { margin-top: 0; margin-left: 0; } 35% { margin-top: -13px; margin-left: 8px; } 70% { margin-top: 8px; margin-left: -7px; } }
          @media (max-width: 767px) { .acejuvi-youth-section-bg { opacity: .20; } .acejuvi-youth-float { --s: 42px; } .acejuvi-youth-float:nth-child(n+3) { display: none; } }
  
        .acejuvi-social-icon { box-shadow: 0 8px 20px -14px rgba(30,58,138,.5); }
        .acejuvi-social-icon:hover { transform: translateY(-3px) scale(1.04); box-shadow: 0 16px 28px -18px rgba(30,58,138,.65); }
        .acejuvi-social-youtube { color: #ff0033 !important; }
        .acejuvi-social-instagram { color: #e1306c !important; }
        .acejuvi-social-whatsapp { color: #25D366 !important; }

        #acejuvi-site-header > .animate-subtle-pulse p::first-letter { font-size: 0; }
          #acejuvi-site-header > .animate-subtle-pulse .material-symbols-outlined:first-child { color: #69f0ae !important; animation: acejuvi-single-blink 1.15s ease-in-out infinite; }
          a[href*="wa.me"] svg, a[href*="wa.me"] .material-symbols-outlined { color: #25D366 !important; }
          #acejuvi-em-numeros { min-height: 340px; display: flex; align-items: center; }
          #acejuvi-em-numeros > .flex { width: 100%; }
          #acejuvi-em-numeros .grid.grid-cols-2 { align-items: start; row-gap: 1.35rem !important; }
          #acejuvi-em-numeros .grid.grid-cols-2 > div { min-height: 96px; justify-content: flex-start; }
          #acejuvi-em-numeros .grid.grid-cols-2 > div > span:first-child { min-height: 42px; display: inline-flex; align-items: flex-end; justify-content: center; }
          #acejuvi-em-numeros .grid.grid-cols-2 > div > span:last-child { min-height: 32px; align-items: center; justify-content: center; line-height: 1.05; }
          
  #acejuvi-em-numeros .grid.grid-cols-2 > div:last-child > span:first-child { font-size: 25px !important; letter-spacing: 0 !important; }
  @media (min-width: 1280px) { #acejuvi-em-numeros [data-acejuvi-counter-card]:last-child > span:first-child { font-size: clamp(1.28rem, 1.2vw, 1.58rem) !important; } }
  .acejuvi-values-list { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .85rem; max-width: 1160px; margin: 0 auto; }
          .acejuvi-value-item { display: grid; grid-template-columns: 38px 1fr; gap: .7rem; align-items: start; padding: .85rem .9rem; border-radius: 18px; background: rgba(255,255,255,.72); border: 1px solid rgba(126,145,181,.18); box-shadow: 0 10px 26px -24px rgba(30,58,138,.35); transition: transform 260ms ease, box-shadow 260ms ease, background 260ms ease; }
          .acejuvi-value-item:hover { transform: translateY(-4px); background: rgba(255,255,255,.94); box-shadow: 0 18px 34px -24px rgba(30,58,138,.5); }
          .acejuvi-value-icon { width: 38px; height: 38px; border-radius: 14px; background: #e6eeff; color: #00236f; display: flex; align-items: center; justify-content: center; }
          .acejuvi-value-icon.is-green { background: #005a38; color: #66ffb3; }
          .acejuvi-value-icon.is-blue { background: #0051d5; color: #fff; }
          .acejuvi-floating-card { animation: acejuvi-card-float 7s ease-in-out infinite; will-change: transform; }
          .acejuvi-floating-card:nth-child(2n) { animation-delay: -1.4s; }
          .acejuvi-floating-card:nth-child(3n) { animation-delay: -2.7s; }
          .acejuvi-floating-card:hover { transform: translateY(-10px) rotate(.25deg) !important; box-shadow: 0 22px 42px -28px rgba(30,58,138,.55) !important; }
          .acejuvi-hero-motion { animation: acejuvi-photo-drift 12s ease-in-out infinite; transform-origin: center; }
          .acejuvi-hero-motion-wrap { animation: acejuvi-photo-frame 9s ease-in-out infinite; }
          .acejuvi-process-timeline .acejuvi-step-card { position: relative; min-height: 260px; transition: transform 260ms ease, box-shadow 260ms ease; }
          .acejuvi-process-timeline .acejuvi-step-card:hover { transform: translateY(-6px); box-shadow: 0 20px 34px -28px rgba(30,58,138,.55); }
          @media (min-width: 1024px) { .acejuvi-process-timeline .acejuvi-step-card:not(:last-child)::after { content: ""; position: absolute; top: 50%; right: -1.4rem; width: 2rem; height: 2rem; transform: translateY(-50%); background: linear-gradient(135deg, transparent 45%, #8fb4ff 46% 55%, transparent 56%); opacity: .85; } }
          @media (max-width: 1023px) { .acejuvi-values-list { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
  @media (max-width: 767px) { .acejuvi-values-list { grid-template-columns: 1fr; } #acejuvi-em-numeros { min-height: auto; } }
          @keyframes acejuvi-single-blink { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: .32; transform: scale(1.16); } }
          @keyframes acejuvi-card-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-7px); } }
          @keyframes acejuvi-photo-drift { 0%,100% { transform: scale(1.02) translate3d(0,0,0); } 50% { transform: scale(1.07) translate3d(-8px,-5px,0); } }
          @keyframes acejuvi-photo-frame { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
          
           .acejuvi-entry-ready { opacity: 0; transform: translateY(28px); transition: opacity 520ms ease, transform 520ms ease; } .acejuvi-entry-visible { opacity: 1; transform: translateY(0); } #acejuvi-final-cta { min-height: 0 !important; height: auto !important; max-height: none !important; display: block !important; padding-top: 34px !important; padding-bottom: 34px !important; align-items: initial !important; justify-content: initial !important; } #acejuvi-final-cta > .max-w-container-max { min-height: 0 !important; } @media (max-width: 767px) { #acejuvi-final-cta { padding-top: 28px !important; padding-bottom: 28px !important; } .acejuvi-entry-ready { transform: translateY(18px); } } @media (prefers-reduced-motion: reduce) { .acejuvi-youth-section-bg, body::before, body::after, .acejuvi-floating-card, .acejuvi-hero-motion, .acejuvi-hero-motion-wrap, #acejuvi-site-header > .animate-subtle-pulse .material-symbols-outlined:first-child { animation: none !important; } }

        @media (max-width: 640px) {
            #pix-interactive-box {
                border-radius: 1.75rem !important;
                gap: 0.875rem !important;
                padding: 1.25rem !important;
            }

            #pix-interactive-box > div:first-child {
                align-items: flex-start !important;
                gap: 0.75rem !important;
            }

            #pix-interactive-box > div:first-child .font-headline-sm {
                font-size: 1.45rem !important;
                line-height: 1.15 !important;
            }

            #pix-interactive-box > div:first-child > span:last-child {
                line-height: 1.1 !important;
                text-align: center !important;
                white-space: normal !important;
            }

            #pix-interactive-box .grid.grid-cols-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            #pix-interactive-box .pix-amt-btn {
                min-height: 44px !important;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
                white-space: nowrap !important;
            }

            #pix-interactive-box #pix-key-val {
                overflow: visible !important;
                text-overflow: clip !important;
                white-space: normal !important;
                word-break: break-word !important;
            }

            #pix-interactive-box .bg-surface-container-lowest.p-space-md,
    #pix-interactive-box .bg-surface-container-low.p-space-md {
                padding: 1rem !important;
            }

            #pix-interactive-box .bg-surface-container-lowest.p-space-md > div:first-child,
    #pix-interactive-box .bg-surface-container-low.p-space-md > div:first-child {
                align-items: flex-start !important;
                flex-direction: column !important;
                gap: 0.25rem !important;
            }
        }
    </style>';
}

function acejuvi_shortcode_render_page_endpoint() {
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : 'landing';
    $allowed_pages = array(
        'landing' => 'landing.html',
    );

    if (!isset($allowed_pages[$page])) {
        status_header(404);
        wp_die('Pagina nao encontrada.');
    }

    $file = plugin_dir_path(ACEJUVI_SHORTCODE_FILE) . 'public/' . $allowed_pages[$page];
    if (!is_readable($file)) {
        status_header(404);
        wp_die('Arquivo da pagina nao encontrado.');
    }

    show_admin_bar(false);

    $html = file_get_contents($file);
    if (!is_string($html)) {
        status_header(500);
        wp_die('Nao foi possivel carregar a pagina.');
    }

    $feed = acejuvi_shortcode_instagram_feed_markup();
    if ($feed !== '') {
        $html = preg_replace(
            '/<!-- ACEJUVI_INSTAGRAM_FEED_START -->.*?<!-- ACEJUVI_INSTAGRAM_FEED_END -->/s',
            $feed,
            $html,
            1
        );
    }

    ob_start();
    wp_head();
    $wp_head = ob_get_clean();

    ob_start();
    wp_footer();
    $wp_footer = ob_get_clean();

    $base = '<base href="' . esc_url(ACEJUVI_SHORTCODE_URL . 'public/') . '">';
    $html = str_replace('</head>', $base . $wp_head . acejuvi_shortcode_frontend_config() . acejuvi_shortcode_critical_style() . '</head>', $html);
    $html = str_replace('</body>', $wp_footer . '</body>', $html);

    status_header(200);
    nocache_headers();
    header('Content-Type: text/html; charset=' . get_option('blog_charset'));
    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    exit;
}
add_action('wp_ajax_acejuvi_render_page', 'acejuvi_shortcode_render_page_endpoint');
add_action('wp_ajax_nopriv_acejuvi_render_page', 'acejuvi_shortcode_render_page_endpoint');

function acejuvi_shortcode_render($atts = array()) {
    $atts = shortcode_atts(
        array(
            'height' => '1200',
            'title' => 'ACEJUVI - Associação Centro Juvenil Pela Vida',
        ),
        $atts,
        'acejuvi_site'
    );

    $height = absint($atts['height']);
    if ($height < 320) {
        $height = 1200;
    }

    $iframe_id = 'acejuvi-site-' . wp_unique_id();
    $src = add_query_arg(
        array(
            'action' => 'acejuvi_render_page',
            'page' => 'landing',
            'ver' => ACEJUVI_SHORTCODE_VERSION,
        ),
        admin_url('admin-ajax.php')
    );

    ob_start();
    ?>
    <div class="acejuvi-shortcode-wrap" data-acejuvi-shortcode>
        <iframe
            id="<?php echo esc_attr($iframe_id); ?>"
            class="acejuvi-shortcode-frame"
            src="<?php echo esc_url($src); ?>"
            title="<?php echo esc_attr($atts['title']); ?>"
            loading="eager"
            height="<?php echo esc_attr($height); ?>"
            fetchpriority="high"
            style="width:100%;height:100%;border:0;display:block;overflow:auto;background:transparent;"
            scrolling="yes"
        ></iframe>
        <a
            class="acejuvi-whatsapp-floating"
            href="<?php echo esc_url(acejuvi_whatsapp_url('Olá, vim pelo site da ACEJUVI.')); ?>"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Abrir WhatsApp da ACEJUVI"
        >
            <svg aria-hidden="true" viewBox="0 0 32 32" focusable="false">
                <path fill="currentColor" d="M16.04 3.2A12.73 12.73 0 0 0 5.1 22.44L3.5 28.8l6.53-1.56A12.72 12.72 0 1 0 16.04 3.2Zm0 2.34a10.38 10.38 0 1 1-5.3 19.3l-.45-.27-3.62.86.88-3.48-.3-.47A10.38 10.38 0 0 1 16.04 5.54Zm-5.17 5.36c-.22 0-.57.08-.87.4-.3.33-1.15 1.13-1.15 2.75s1.18 3.19 1.35 3.41c.17.22 2.28 3.65 5.63 4.97 2.79 1.1 3.36.88 3.96.83.6-.06 1.96-.8 2.24-1.58.28-.78.28-1.45.2-1.59-.08-.14-.3-.22-.62-.38-.33-.17-1.96-.97-2.26-1.08-.3-.11-.52-.17-.74.17-.22.33-.85 1.08-1.04 1.3-.19.22-.38.25-.71.08-.33-.17-1.38-.51-2.63-1.62-.97-.87-1.63-1.94-1.82-2.27-.19-.33-.02-.51.14-.68.15-.15.33-.38.49-.57.17-.19.22-.33.33-.55.11-.22.06-.41-.03-.57-.08-.17-.74-1.79-1.02-2.45-.27-.64-.54-.55-.74-.56h-.63Z"/>
            </svg>
        </a>
    </div>
    <style>
        .acejuvi-shortcode-wrap {
            height: calc(100vh - var(--wp-admin--admin-bar--height, 0px));
            min-height: 620px;
            overflow: hidden;
            position: relative;
            width: 100%;
        }

        .acejuvi-shortcode-frame {
            background: transparent;
            border: 0;
            display: block;
            height: 100% !important;
            min-height: 620px;
            overflow: auto;
            width: 100%;
        }

        .acejuvi-whatsapp-floating {
            align-items: center;
            background: #25d366;
            border-radius: 9999px;
            bottom: 24px;
            box-shadow: 0 16px 32px rgba(0, 49, 31, 0.28);
            color: #fff;
            display: flex;
            height: 58px;
            justify-content: center;
            position: fixed;
            right: 24px;
            text-decoration: none;
            transition: transform 180ms ease, box-shadow 180ms ease, background-color 180ms ease;
            width: 58px;
            z-index: 99999;
        }

        .acejuvi-whatsapp-floating::before {
            background: #0d1c2e;
            border-radius: 9999px;
            color: #fff;
            content: "Fale com a Acejuvi";
            font: 600 14px/1.2 Arial, sans-serif;
            opacity: 0;
            padding: 10px 14px;
            pointer-events: none;
            position: absolute;
            right: 70px;
            top: 50%;
            transform: translate(8px, -50%);
            transition: opacity 180ms ease, transform 180ms ease;
            white-space: nowrap;
        }

        .acejuvi-whatsapp-floating::after {
            border: 7px solid transparent;
            border-left-color: #0d1c2e;
            content: "";
            opacity: 0;
            pointer-events: none;
            position: absolute;
            right: 57px;
            top: 50%;
            transform: translate(8px, -50%);
            transition: opacity 180ms ease, transform 180ms ease;
        }

        .acejuvi-whatsapp-floating:hover,
        .acejuvi-whatsapp-floating:focus {
            background: #1fb85a;
            box-shadow: 0 20px 38px rgba(0, 49, 31, 0.34);
            color: #fff;
            transform: translateY(-3px) scale(1.04);
        }

        .acejuvi-whatsapp-floating:hover::before,
        .acejuvi-whatsapp-floating:focus::before,
        .acejuvi-whatsapp-floating:hover::after,
        .acejuvi-whatsapp-floating:focus::after {
            opacity: 1;
            transform: translate(0, -50%);
        }

        .acejuvi-whatsapp-floating svg {
            color: #fff;
            fill: #fff;
            height: 32px;
            width: 32px;
        }

        .acejuvi-whatsapp-floating svg path {
            fill: #fff;
        }

        @media (max-width: 640px) {
            .acejuvi-shortcode-wrap,
            .acejuvi-shortcode-frame {
                min-height: 560px;
            }

            .acejuvi-whatsapp-floating {
                bottom: 16px;
                height: 54px;
                right: 16px;
                width: 54px;
            }
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('acejuvi_site', 'acejuvi_shortcode_render');
add_shortcode('acejuvi_landing', 'acejuvi_shortcode_render');

function acejuvi_shortcode_admin_menu() {
    add_options_page(
        'ACEJUVI Shortcode',
        'ACEJUVI Shortcode',
        'manage_options',
        'acejuvi-shortcode',
        'acejuvi_shortcode_admin_page'
    );
}
add_action('admin_menu', 'acejuvi_shortcode_admin_menu');

function acejuvi_shortcode_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>ACEJUVI Landing Page Shortcode</h1>
        <p>Use o shortcode abaixo em uma página ou post do WordPress:</p>
        <p><code>[acejuvi_site]</code></p>
        <p>Também está disponível o alias:</p>
        <p><code>[acejuvi_landing]</code></p>
        <h2>Altura inicial</h2>
        <p>A altura é ajustada automaticamente depois que a página carrega. Se quiser definir uma altura mínima inicial:</p>
        <p><code>[acejuvi_site height="1200"]</code></p>
    </div>
    <?php
}







