<?php

namespace Drupal\Tests\admin_toolbar_search\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the Admin Toolbar Search settings form.
 *
 * @group admin_toolbar
 */
class AdminToolbarSearchSettingsFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'admin_toolbar_search',
  ];

  /**
   * A user with access to the Admin Toolbar settings form permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $permissions = [
      'access toolbar',
      'access administration pages',
      'administer site configuration',
      // This permission is needed to test the inclusion of the JS libraries.
      'use admin toolbar search',
    ];
    $this->adminUser = $this->drupalCreateUser($permissions);
  }

  /**
   * Test backend Admin Toolbar Tools settings form fields and submission.
   */
  public function testAdminToolbarToolsSettingsForm(): void {
    /** @var \Drupal\Tests\WebAssert $assert */
    $assert = $this->assertSession();

    // Log in as an admin user to test admin pages.
    $this->drupalLogin($this->adminUser);

    // Test the 'Admin Toolbar Search settings' page form submission and fields.
    $this->drupalGet('admin/config/user-interface/admin-toolbar-search-settings');

    /* Test default values to compare with the ones after the changes. */

    // Test 'enable_keyboard_shortcut'.
    $keyboard_shortcut_js = 'admin_toolbar_search/js/admin_toolbar_search.keyboard_shortcut.js';
    // Check the keyboard shortcut library is loaded by default.
    $assert->responseContains($keyboard_shortcut_js);
    // Check the display menu item is disabled by default.
    $assert->responseContains('"displayMenuItem":false');

    // Change the value of 'display_menu_item' and submit the form.
    $this->submitForm([
      'display_menu_item' => TRUE,
    ], 'Save configuration');
    // Check the form submission was successful.
    $assert->pageTextContains('The configuration options have been saved.');

    /* Test updated values. */

    // Check the keyboard shortcut library is loaded by default.
    $assert->responseContains($keyboard_shortcut_js);
    // Check the display menu item is disabled by default.
    $assert->responseContains('"displayMenuItem":true');

    // Change the value of 'enable_keyboard_shortcut' and submit the form.
    $this->submitForm([
      'enable_keyboard_shortcut' => FALSE,
    ], 'Save configuration');
    // Check the form submission was successful.
    $assert->pageTextContains('The configuration options have been saved.');

    // Check the keyboard shortcut library is not loaded.
    $assert->responseNotContains('admin_toolbar_search.keyboard_shortcut.js');
    // Check the display menu item JS is not found.
    $assert->responseNotContains('displayMenuItem');
  }

}
