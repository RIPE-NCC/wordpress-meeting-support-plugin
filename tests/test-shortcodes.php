<?php
/**
 * Class ShortcodeTest
 *
 * @package Meeting_Support
 */

/**
 * Sample test case.
 */
class ShortcodeTest extends WP_UnitTestCase {

    /**
     * [user_links]
     */

    /**
     * Helper function to execute do_shortcode()
     */
    private function do($shortcode) {
        return do_shortcode($shortcode);
    }

    public function test_user_links() {
        $shortcode_result = $this->do('[user_links]');
        $this->assertContains('Sign in', $shortcode_result);
        // [crowd_links] is a synonym
        $shortcode_result = $this->do('[crowd_links]');
        $this->assertContains('Sign in', $shortcode_result);
    }

    public function test_user_login() {
        $shortcode_result = $this->do('[user_login]');
        $this->assertContains('Sign in', $shortcode_result);
        $this->assertContains('Create account', $shortcode_result);
        $this->assertContains('Forgot your password?', $shortcode_result);
    }

}
