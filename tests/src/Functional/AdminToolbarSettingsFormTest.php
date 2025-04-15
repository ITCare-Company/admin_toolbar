<?php

namespace Drupal\Tests\admin_toolbar\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the Admin Toolbar settings form.
 *
 * @group admin_toolbar
 */
class AdminToolbarSettingsFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'admin_toolbar',
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
   * Test backend admin toolbar settings form fields and submission.
   */
  public function testAdminToolbarSettingsForm(): void {
    /** @var \Drupal\Tests\WebAssert $assert */
    $assert = $this->assertSession();

    // Log in as an admin user to test admin pages.
    $this->drupalLogin($this->adminUser);

    // Test the 'Admin Toolbar settings' page form submission and fields.
    $this->drupalGet('admin/config/user-interface/admin-toolbar');
    // Submit the form with default values.
    $this->submitForm([], 'Save configuration');
    $assert->pageTextContains('The configuration options have been saved.');

    /* Test default values to compare with the ones after the changes. */

    // Test 'Menu depth' for the Admin Toolbar settings form, under:
    // Configuration > User interface > Admin Toolbar.
    // Default value '4': the menu should be displayed as level 3.
    $assert->elementExists('xpath', '//div[@class="toolbar-menu-administration"]//ul[contains(@class, "toolbar-menu")]//li[contains(@class, "menu-item")]//ul[@class="toolbar-menu"]//li[contains(@class, "menu-item")]//ul[@class="toolbar-menu"]//li[@class="menu-item"]//a[contains(@href, "/admin/config/user-interface/admin-toolbar") and contains(.,"Admin Toolbar")]');

    // Test Hoverintent and Sticky behavior.
    $hover_js = 'js/admin_toolbar.hover.js';
    $hoverintent_js = 'js/admin_toolbar.hoverintent.js';
    // Path to the libraries CSS files for sticky behavior, to be tested.
    $disable_sticky_css = 'css/admin_toolbar.disable_sticky.css';
    $sticky_behavior_css = 'css/admin_toolbar.sticky_behavior.css';

    // Check the default hoverintent library is not loaded.
    $assert->responseNotContains($hover_js);
    // Check the HoverIntent functionality is enabled.
    $assert->responseContains($hoverintent_js);
    // Check sticky behavior is 'enabled' by default (not disabled).
    $assert->responseNotContains($disable_sticky_css);
    $assert->responseNotContains($sticky_behavior_css);

    /* Change all the values of the settings form. */

    // Set the 'Menu depth' to '2', disable sticky and save the form.
    $edit = [
      'menu_depth' => '2',
      'enable_hoverintent' => FALSE,
      'sticky_behavior' => 'disabled',
    ];
    $this->submitForm($edit, 'Save configuration');
    $assert->pageTextContains('The configuration options have been saved.');

    /* Test updated values. */

    // Check the menu item 'Admin Toolbar' is not displayed.
    $assert->elementNotExists('xpath', '//div[@class="toolbar-menu-administration"]//ul[contains(@class, "toolbar-menu")]//li[contains(@class, "menu-item")]//ul[@class="toolbar-menu"]//li[contains(@class, "menu-item")]//ul[@class="toolbar-menu"]//li[@class="menu-item"]//a[contains(@href, "/admin/config/user-interface/admin-toolbar") and contains(.,"Admin Toolbar")]');
    // Check the menu item 'User interface' does not have a child 'ul'.
    $assert->elementNotExists('xpath', '//div[@class="toolbar-menu-administration"]//ul[contains(@class, "toolbar-menu")]//li[contains(@class, "menu-item")]//ul[@class="toolbar-menu"]//li[contains(@class, "menu-item")]//ul');
    // Check the menu item 'User interface' has a single child 'a' link tag.
    $assert->elementExists('xpath', '//div[@class="toolbar-menu-administration"]//ul[contains(@class, "toolbar-menu")]//li[contains(@class, "menu-item")]//ul[@class="toolbar-menu"]//li[contains(@class, "menu-item") and count(child::*)=1 and child::*=a]');

    // Check the default hoverintent library is loaded.
    $assert->responseContains($hover_js);
    // Check the HoverIntent functionality is not enabled.
    $assert->responseNotContains($hoverintent_js);
    // Check sticky behavior is disabled.
    $assert->responseContains($disable_sticky_css);
    $assert->responseNotContains($sticky_behavior_css);

    // Set the sticky behavior to 'Hide on scroll down' and save the form.
    $edit = [
      'sticky_behavior' => 'hide_on_scroll_down',
    ];
    $this->submitForm($edit, 'Save configuration');
    $assert->pageTextContains('The configuration options have been saved.');

    // Check sticky behavior 'hide_on_scroll_down' is displayed.
    $assert->responseNotContains($disable_sticky_css);
    $assert->responseContains($sticky_behavior_css);
    $assert->responseContains('js/admin_toolbar.sticky_behavior.js');

  }

}
