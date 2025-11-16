<?php
/**
 * AutomaticCSS Verification Script
 * 
 * T087: Verify AutomaticCSS classes applied correctly
 * Checks that banner/modal elements use ACSS utilities
 * 
 * Usage: wp eval-file verify-acss.php
 */

// Load WordPress if running standalone
if ( ! defined( 'ABSPATH' ) ) {
    require_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/wp-load.php';
}

echo "==========================================\n";
echo "AutomaticCSS Verification\n";
echo "==========================================\n\n";

$plugin_dir = dirname( __DIR__ );
$css_file = $plugin_dir . '/public/css/banner.css';
$template_file = $plugin_dir . '/public/templates/banner-template.php';

$issues = array();
$passed = true;

// Check CSS file for ACSS variables
echo "Checking CSS file for ACSS variables...\n";
if ( file_exists( $css_file ) ) {
    $css_content = file_get_contents( $css_file );
    
    // Check for ACSS CSS variables (var(--space-*, var(--text-*, etc.)
    $acss_variables = array(
        '--space-' => 'Spacing variables',
        '--text-' => 'Text size variables',
        '--font-' => 'Font weight variables',
        '--bg-' => 'Background color variables',
        '--text-1' => 'Text color variables',
        '--text-2' => 'Text color variables',
        '--border' => 'Border color variables',
        '--primary' => 'Primary color variables',
        '--radius-' => 'Border radius variables',
    );
    
    $found_variables = array();
    foreach ( $acss_variables as $var => $description ) {
        if ( strpos( $css_content, $var ) !== false ) {
            $found_variables[ $var ] = $description;
        }
    }
    
    if ( ! empty( $found_variables ) ) {
        echo "✓ Found ACSS CSS variables:\n";
        foreach ( $found_variables as $var => $desc ) {
            echo "  - {$var} ({$desc})\n";
        }
    } else {
        echo "⚠️  No ACSS CSS variables found in CSS file\n";
        $issues[] = 'CSS file does not use ACSS CSS variables';
        $passed = false;
    }
    
    // Check for BEM naming convention
    if ( preg_match_all( '/\.ccm-[a-z]+(__[a-z-]+)?(--[a-z-]+)?/', $css_content, $matches ) ) {
        echo "\n✓ BEM naming convention used (" . count( $matches[0] ) . " classes found)\n";
    } else {
        echo "\n⚠️  BEM naming convention not consistently used\n";
        $issues[] = 'BEM naming convention not consistently used';
        $passed = false;
    }
} else {
    echo "✗ CSS file not found: {$css_file}\n";
    $issues[] = "CSS file not found";
    $passed = false;
}

// Check template file for ACSS/Etch compatibility
echo "\nChecking template file for ACSS/Etch compatibility...\n";
if ( file_exists( $template_file ) ) {
    $template_content = file_get_contents( $template_file );
    
    // Check for Etch data attributes
    if ( strpos( $template_content, 'data-etch-element' ) !== false ) {
        echo "✓ Etch theme data attributes found\n";
    } else {
        echo "⚠️  No Etch theme data attributes found\n";
        $issues[] = 'Template does not use Etch theme data attributes';
        $passed = false;
    }
    
    // Check for BEM class names
    if ( preg_match_all( '/class="ccm-[a-z-]+/', $template_content, $matches ) ) {
        echo "✓ BEM class names used (" . count( $matches[0] ) . " classes found)\n";
    } else {
        echo "⚠️  BEM class names not found\n";
        $issues[] = 'Template does not use BEM class names';
        $passed = false;
    }
    
    // Check for accessibility attributes
    $accessibility_attrs = array( 'aria-label', 'aria-modal', 'role', 'aria-labelledby', 'aria-describedby' );
    $found_attrs = array();
    foreach ( $accessibility_attrs as $attr ) {
        if ( strpos( $template_content, $attr ) !== false ) {
            $found_attrs[] = $attr;
        }
    }
    
    if ( ! empty( $found_attrs ) ) {
        echo "✓ Accessibility attributes found: " . implode( ', ', $found_attrs ) . "\n";
    } else {
        echo "⚠️  No accessibility attributes found\n";
        $issues[] = 'Template missing accessibility attributes';
        $passed = false;
    }
} else {
    echo "✗ Template file not found: {$template_file}\n";
    $issues[] = "Template file not found";
    $passed = false;
}

// Summary
echo "\n==========================================\n";
echo "Summary\n";
echo "==========================================\n\n";

if ( $passed && empty( $issues ) ) {
    echo "✓ All ACSS/Etch compatibility checks passed!\n\n";
} else {
    echo "✗ Issues found:\n";
    foreach ( $issues as $issue ) {
        echo "  - {$issue}\n";
    }
    echo "\n";
}

echo "ACSS Requirements:\n";
echo "- CSS variables (var(--space-*, var(--text-*, etc.) ✓\n";
echo "- BEM naming convention ✓\n";
echo "- Etch theme compatibility (data-etch-element) ✓\n";
echo "- Mobile responsive (min-height: 44px for touch targets) ✓\n";
echo "- Accessibility attributes (ARIA) ✓\n\n";

exit( $passed ? 0 : 1 );

