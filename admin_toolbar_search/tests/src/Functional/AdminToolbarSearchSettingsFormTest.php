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
    // Submit the form with default values.
    $this->submitForm([], 'Save configuration');
    // Check the form submission was successful.
    $assert->pageTextContains('The configuration options have been saved.');
  }

}
