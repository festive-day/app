<?php
/**
 * Test Database Setup
 *
 * @package Cookie_Consent_Manager
 */

class Test_Database_Setup extends WP_UnitTestCase {

    /**
     * Test database tables exist
     */
    public function test_tables_exist() {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'cookie_consent_categories',
            $wpdb->prefix . 'cookie_consent_cookies',
            $wpdb->prefix . 'cookie_consent_events',
        );

        foreach ( $tables as $table ) {
            $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" );
            $this->assertEquals( $table, $table_exists, "Table {$table} should exist" );
        }
    }

    /**
     * Test default categories inserted
     */
    public function test_default_categories_exist() {
        global $wpdb;

        $table = $wpdb->prefix . 'cookie_consent_categories';

        $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
        $this->assertEquals( 4, $count, 'Should have 4 default categories' );

        $slugs = $wpdb->get_col( "SELECT slug FROM {$table} ORDER BY display_order ASC" );
        $expected_slugs = array( 'essential', 'functional', 'analytics', 'marketing' );
        $this->assertEquals( $expected_slugs, $slugs, 'Default category slugs should match' );
    }

    /**
     * Test essential category is required
     */
    public function test_essential_category_required() {
        global $wpdb;

        $table = $wpdb->prefix . 'cookie_consent_categories';

        $is_required = $wpdb->get_var(
            "SELECT is_required FROM {$table} WHERE slug = 'essential'"
        );

        $this->assertEquals( 1, $is_required, 'Essential category should be required' );
    }

    /**
     * Test table indexes exist
     */
    public function test_table_indexes() {
        global $wpdb;

        $table = $wpdb->prefix . 'cookie_consent_categories';

        $indexes = $wpdb->get_results( "SHOW INDEX FROM {$table}" );
        $index_names = wp_list_pluck( $indexes, 'Key_name' );

        $this->assertContains( 'idx_slug', $index_names, 'idx_slug index should exist' );
        $this->assertContains( 'idx_display_order', $index_names, 'idx_display_order index should exist' );
    }

    /**
     * Test foreign key constraint
     */
    public function test_foreign_key_constraint() {
        global $wpdb;

        $cookies_table = $wpdb->prefix . 'cookie_consent_cookies';

        // Try to insert cookie with invalid category_id
        $result = $wpdb->insert(
            $cookies_table,
            array(
                'name'        => 'test_cookie',
                'category_id' => 99999,
                'purpose'     => 'Test',
                'expiration'  => '1 day',
            )
        );

        // Should fail due to foreign key constraint
        $this->assertFalse( $result, 'Should fail to insert cookie with invalid category_id' );
    }
}
