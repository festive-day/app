<?php
/**
 * Cookie Consent Manager Diagnostic Tool
 *
 * Access via: /wp-content/plugins/cookie-consent-manager/diagnostic.php
 *
 * @package Cookie_Consent_Manager
 */

// Load WordPress
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

// Check if plugin is active
$active_plugins = get_option( 'active_plugins' );
$plugin_active = in_array( 'cookie-consent-manager/cookie-consent-manager.php', $active_plugins );

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCM Diagnostic Tool</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: #f5f5f5;
        }
        .diagnostic {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        .status--pass { background: #d4edda; color: #155724; }
        .status--fail { background: #f8d7da; color: #721c24; }
        .status--warn { background: #fff3cd; color: #856404; }
        h1 { margin-top: 0; }
        h2 { border-bottom: 2px solid #007cba; padding-bottom: 0.5rem; }
        pre {
            background: #f5f5f5;
            padding: 1rem;
            border-radius: 4px;
            overflow-x: auto;
        }
        .test-list { list-style: none; padding: 0; }
        .test-list li { padding: 0.5rem 0; border-bottom: 1px solid #eee; }
        .test-list li:last-child { border-bottom: none; }
        button {
            background: #007cba;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
        }
        button:hover { background: #005a87; }
        #console-output {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 1rem;
            border-radius: 4px;
            max-height: 400px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
        }
        .log-error { color: #f48771; }
        .log-warn { color: #dcdcaa; }
        .log-info { color: #4fc1ff; }
    </style>
</head>
<body>
    <h1>Cookie Consent Manager - Diagnostic Tool</h1>

    <!-- Plugin Status -->
    <div class="diagnostic">
        <h2>Plugin Status</h2>
        <ul class="test-list">
            <li>
                Plugin Active:
                <span class="status <?php echo $plugin_active ? 'status--pass' : 'status--fail'; ?>">
                    <?php echo $plugin_active ? 'YES' : 'NO'; ?>
                </span>
            </li>
            <?php if ( $plugin_active ) : ?>
                <li>
                    Plugin Path:
                    <code><?php echo plugin_dir_path( __FILE__ ); ?></code>
                </li>
                <li>
                    Plugin URL:
                    <code><?php echo plugin_dir_url( __FILE__ ); ?></code>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Database Tables -->
    <div class="diagnostic">
        <h2>Database Tables</h2>
        <?php
        global $wpdb;
        $tables = array(
            'cookie_consent_categories',
            'cookie_consent_cookies',
            'cookie_consent_events',
        );
        ?>
        <ul class="test-list">
            <?php foreach ( $tables as $table ) : ?>
                <?php
                $table_name = $wpdb->prefix . $table;
                $exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name;
                $count = $exists ? $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" ) : 0;
                ?>
                <li>
                    <?php echo $table; ?>:
                    <span class="status <?php echo $exists ? 'status--pass' : 'status--fail'; ?>">
                        <?php echo $exists ? "EXISTS ({$count} rows)" : 'MISSING'; ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Default Categories -->
    <div class="diagnostic">
        <h2>Default Categories</h2>
        <?php
        $categories = $wpdb->get_results(
            "SELECT slug, name, description, is_required, display_order
            FROM {$wpdb->prefix}cookie_consent_categories
            ORDER BY display_order"
        );
        ?>
        <?php if ( $categories ) : ?>
            <ul class="test-list">
                <?php foreach ( $categories as $cat ) : ?>
                    <li>
                        <strong><?php echo esc_html( $cat->name ); ?></strong> (<?php echo esc_html( $cat->slug ); ?>)
                        <?php if ( $cat->is_required ) : ?>
                            <span class="status status--warn">Required</span>
                        <?php endif; ?>
                        <br>
                        <small><?php echo esc_html( $cat->description ); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p><span class="status status--fail">No categories found</span></p>
        <?php endif; ?>
    </div>

    <!-- WordPress Hooks -->
    <div class="diagnostic">
        <h2>WordPress Hooks</h2>
        <ul class="test-list">
            <li>
                wp_enqueue_scripts:
                <span class="status <?php echo has_action( 'wp_enqueue_scripts' ) ? 'status--pass' : 'status--fail'; ?>">
                    <?php echo has_action( 'wp_enqueue_scripts' ) ? 'REGISTERED' : 'NOT REGISTERED'; ?>
                </span>
            </li>
            <li>
                wp_ajax_ccm_get_banner_config:
                <span class="status <?php echo has_action( 'wp_ajax_ccm_get_banner_config' ) || has_action( 'wp_ajax_nopriv_ccm_get_banner_config' ) ? 'status--pass' : 'status--fail'; ?>">
                    <?php echo has_action( 'wp_ajax_ccm_get_banner_config' ) || has_action( 'wp_ajax_nopriv_ccm_get_banner_config' ) ? 'REGISTERED' : 'NOT REGISTERED'; ?>
                </span>
            </li>
            <li>
                wp_ajax_ccm_record_consent:
                <span class="status <?php echo has_action( 'wp_ajax_ccm_record_consent' ) || has_action( 'wp_ajax_nopriv_ccm_record_consent' ) ? 'status--pass' : 'status--fail'; ?>">
                    <?php echo has_action( 'wp_ajax_ccm_record_consent' ) || has_action( 'wp_ajax_nopriv_ccm_record_consent' ) ? 'REGISTERED' : 'NOT REGISTERED'; ?>
                </span>
            </li>
            <li>
                wp_footer:
                <span class="status <?php echo has_action( 'wp_footer' ) ? 'status--pass' : 'status--fail'; ?>">
                    <?php echo has_action( 'wp_footer' ) ? 'REGISTERED' : 'NOT REGISTERED'; ?>
                </span>
            </li>
        </ul>
    </div>

    <!-- File Checks -->
    <div class="diagnostic">
        <h2>Required Files</h2>
        <?php
        $files = array(
            'Main Plugin' => 'cookie-consent-manager.php',
            'Cookie Manager Class' => 'includes/class-cookie-manager.php',
            'Storage Handler Class' => 'includes/class-storage-handler.php',
            'Consent Logger Class' => 'includes/class-consent-logger.php',
            'Cookie Blocker Class' => 'includes/class-cookie-blocker.php',
            'Banner Template' => 'public/templates/banner-template.php',
            'Banner CSS' => 'public/css/banner.css',
            'Consent Banner JS' => 'public/js/consent-banner.js',
            'Storage Manager JS' => 'public/js/storage-manager.js',
            'Cookie Blocker JS' => 'public/js/cookie-blocker.js',
        );
        ?>
        <ul class="test-list">
            <?php foreach ( $files as $label => $file ) : ?>
                <?php $exists = file_exists( plugin_dir_path( __FILE__ ) . $file ); ?>
                <li>
                    <?php echo $label; ?>:
                    <span class="status <?php echo $exists ? 'status--pass' : 'status--fail'; ?>">
                        <?php echo $exists ? 'EXISTS' : 'MISSING'; ?>
                    </span>
                    <?php if ( $exists ) : ?>
                        <br><small><code><?php echo $file; ?></code></small>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Frontend JavaScript Test -->
    <div class="diagnostic">
        <h2>Frontend JavaScript Test</h2>
        <p>Click the button below to test if JavaScript is loading correctly:</p>
        <button onclick="runJavaScriptTests()">Run JavaScript Tests</button>
        <div id="console-output" style="margin-top: 1rem;"></div>
    </div>

    <!-- AJAX Endpoint Test -->
    <div class="diagnostic">
        <h2>AJAX Endpoint Test</h2>
        <button onclick="testAjaxEndpoints()">Test AJAX Endpoints</button>
        <div id="ajax-results" style="margin-top: 1rem;"></div>
    </div>

    <script>
        const consoleOutput = document.getElementById('console-output');

        function log(message, type = 'info') {
            const line = document.createElement('div');
            line.className = 'log-' + type;
            line.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            consoleOutput.appendChild(line);
            consoleOutput.scrollTop = consoleOutput.scrollHeight;
        }

        function runJavaScriptTests() {
            consoleOutput.innerHTML = '';
            log('Starting JavaScript diagnostic tests...', 'info');

            // Test 1: Check if CookieConsentStorage exists
            if (typeof window.CookieConsentStorage !== 'undefined') {
                log('✓ CookieConsentStorage object found', 'info');
            } else {
                log('✗ CookieConsentStorage object NOT found', 'error');
            }

            // Test 2: Check if CookieConsentManager exists
            if (typeof window.CookieConsentManager !== 'undefined') {
                log('✓ CookieConsentManager object found', 'info');
            } else {
                log('✗ CookieConsentManager object NOT found', 'error');
            }

            // Test 3: Check if CCM_AJAX_URL exists
            if (typeof window.CCM_AJAX_URL !== 'undefined') {
                log('✓ CCM_AJAX_URL defined: ' + window.CCM_AJAX_URL, 'info');
            } else {
                log('✗ CCM_AJAX_URL NOT defined', 'error');
            }

            // Test 4: Check if CCM_VERSION exists
            if (typeof window.CCM_VERSION !== 'undefined') {
                log('✓ CCM_VERSION defined: ' + window.CCM_VERSION, 'info');
            } else {
                log('✗ CCM_VERSION NOT defined', 'error');
            }

            // Test 5: Check banner element
            const banner = document.getElementById('ccm-banner');
            if (banner) {
                log('✓ Banner element found in DOM', 'info');
                log('  Banner classes: ' + banner.className, 'info');
                log('  Banner visibility: ' + (banner.offsetHeight > 0 ? 'VISIBLE' : 'HIDDEN'), 'info');
            } else {
                log('✗ Banner element NOT found in DOM', 'error');
            }

            // Test 6: Check localStorage
            const consent = window.CookieConsentStorage ? window.CookieConsentStorage.getConsent() : null;
            if (consent) {
                log('✓ Existing consent found in localStorage', 'info');
                log('  Consent data: ' + JSON.stringify(consent), 'info');
            } else {
                log('! No consent found in localStorage (banner should show)', 'warn');
            }

            // Test 7: Check CookieConsentManager config
            if (window.CookieConsentManager && window.CookieConsentManager.config) {
                log('✓ CookieConsentManager config loaded', 'info');
                log('  Categories: ' + (window.CookieConsentManager.config.categories ? window.CookieConsentManager.config.categories.length : 'N/A'), 'info');
            } else {
                log('! CookieConsentManager config not loaded yet', 'warn');
            }

            log('JavaScript diagnostic complete', 'info');
        }

        function testAjaxEndpoints() {
            const resultsDiv = document.getElementById('ajax-results');
            resultsDiv.innerHTML = '<p>Testing AJAX endpoints...</p>';

            // Test get_banner_config
            fetch(window.CCM_AJAX_URL + '?action=ccm_get_banner_config')
                .then(response => response.json())
                .then(data => {
                    resultsDiv.innerHTML += '<h3>GET /ccm_get_banner_config</h3>';
                    resultsDiv.innerHTML += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                })
                .catch(error => {
                    resultsDiv.innerHTML += '<h3>GET /ccm_get_banner_config</h3>';
                    resultsDiv.innerHTML += '<p class="status status--fail">ERROR: ' + error.message + '</p>';
                });

            // Test check_dnt
            fetch(window.CCM_AJAX_URL + '?action=ccm_check_dnt')
                .then(response => response.json())
                .then(data => {
                    resultsDiv.innerHTML += '<h3>GET /ccm_check_dnt</h3>';
                    resultsDiv.innerHTML += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                })
                .catch(error => {
                    resultsDiv.innerHTML += '<h3>GET /ccm_check_dnt</h3>';
                    resultsDiv.innerHTML += '<p class="status status--fail">ERROR: ' + error.message + '</p>';
                });
        }

        // Auto-load scripts on page load
        window.addEventListener('load', function() {
            log('Page loaded, waiting 2 seconds for scripts to initialize...', 'info');
            setTimeout(function() {
                runJavaScriptTests();
            }, 2000);
        });
    </script>

    <?php wp_footer(); ?>
</body>
</html>
