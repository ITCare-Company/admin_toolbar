<?php

namespace Drupal\Tests\admin_toolbar_search\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the Admin Toolbar Search settings form and module's permission.
 *
 * @group admin_toolbar
 * @group admin_toolbar_search
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
    // Enable the block module to be able to test module's local tasks.
    'block',
    // The admin toolbar tools module is required to check the display of the
    // three menu local tasks (tabs) on the admin settings form page.
    'admin_toolbar_tools',
  ];

  /**
   * A user with access to the Admin Toolbar settings form and search.
   *
   * Permissions to access the site's admin backend form pages and the 'use
   * admin toolbar search' permission defined by the module.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * A test user without the 'use admin toolbar search' permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $noAccessUser;

  /**
   * The HTML IDs used to test the display of the search in the toolbar.
   *
   * @var array<string>
   */
  protected $testHtmlIds = [
    'search_tab' => 'admin-toolbar-search-tab',
    'search_toolbar_item' => 'toolbar-item-administration-search',
    'search_tray' => 'toolbar-item-administration-search-tray',
    // HTML ID of the mobile search tab.
    'search_tab_mobile' => 'admin-toolbar-mobile-search-tab',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    /* Create custom objects. */

    // Create a block for testing module's primary local tasks.
    $this->createPrimaryLocalTasksBlock();

    /* Setup users for testing access and permissions. */

    // Access to the toolbar and site's configuration admin pages, so the 'Admin
    // Toolbar Search settings' form page could be tested.
    $permissions = [
      'access toolbar',
      'access administration pages',
      'administer site configuration',
    ];
    // Create a user with limited permissions.
    $this->noAccessUser = $this->drupalCreateUser($permissions);
    // This permission is needed to test the inclusion of the JS libraries.
    $permissions[] = 'use admin toolbar search';
    // Create a user with access to the admin toolbar search.
    $this->adminUser = $this->drupalCreateUser($permissions);
  }

  /**
   * Helper function to create a block to test module's primary local tasks.
   */
  private function createPrimaryLocalTasksBlock(): void {
    $values = [
      // A unique ID for the block instance.
      'id' => $this->defaultTheme . '_primary_local_tasks',
      // The plugin block id as defined in the class.
      'plugin' => 'local_tasks_block',
      // The machine name of the theme region.
      'region' => 'highlighted',
      'settings' => [
        'label' => 'Primary tabs',
        'label_display' => '0',
        'primary' => TRUE,
        'secondary' => FALSE,
      ],
      // The machine name of the theme.
      'theme' => $this->defaultTheme,
      'visibility' => [],
      'weight' => 100,
    ];

    \Drupal::entityTypeManager()->getStorage('block')
      ->create($values)
      ->save();
  }

  /**
   * Test backend Admin Toolbar Search settings form fields and submission.
   *
   * Login as an admin user and browse to module's settings form page.
   * Change the configuration values, save and check the results several times.
   * Logout and login with a user without access to the search to test the
   * permission 'use admin toolbar search'.
   */
  public function testAdminToolbarSearchSettingsForm(): void {
    /** @var \Drupal\Tests\WebAssert $assert */
    $assert = $this->assertSession();

    // Log in as an admin user to test module's settings form.
    $this->drupalLogin($this->adminUser);

    // Test the 'Admin Toolbar Search settings' page form submission and fields.
    $this->drupalGet('admin/config/user-interface/admin-toolbar-search');

    /* Test default values to compare with the ones after the changes. */

    // Test 'enable_keyboard_shortcut'.
    $keyboard_shortcut_js = 'admin_toolbar_search/js/admin_toolbar_search.keyboard_shortcut.js';
    // Check the keyboard shortcut library is loaded by default.
    $assert->responseContains($keyboard_shortcut_js);
    // Check the display menu item is disabled by default.
    $assert->responseContains('"displayMenuItem":false');
    // Check the HTML IDs of the search toolbar item are *not* displayed by
    // default, so the search field is directly in the toolbar
    // (display_menu_item: false).
    $assert->responseContains('id="' . $this->testHtmlIds['search_tab'] . '"');
    $assert->responseNotContains($this->testHtmlIds['search_toolbar_item']);
    $assert->responseNotContains($this->testHtmlIds['search_tray']);
    // Check the HTML ID of the mobile search tab is displayed by default.
    $assert->responseContains('id="' . $this->testHtmlIds['search_tab_mobile'] . '"');

    // Change the value of 'display_menu_item' and submit the form.
    $this->submitForm([
      'display_menu_item' => TRUE,
    ], 'Save configuration');
    // Check the form submission was successful.
    $assert->pageTextContains('The configuration options have been saved.');

    /* Test updated values. */

    // Check the keyboard shortcut library is loaded by default.
    $assert->responseContains($keyboard_shortcut_js);
    // Check the display menu item is enabled with the expected JS variable.
    $assert->responseContains('"displayMenuItem":true');
    // Check the HTML IDs are found on the page (display_menu_item: true), since
    // the search field is displayed in a toolbar tray.
    $assert->responseContains('id="' . $this->testHtmlIds['search_tab'] . '"');
    $assert->responseContains('id="' . $this->testHtmlIds['search_toolbar_item'] . '"');
    $assert->responseContains('id="' . $this->testHtmlIds['search_tray'] . '"');
    // Check the HTML ID of the mobile search tab is *not* displayed.
    $assert->responseNotContains($this->testHtmlIds['search_tab_mobile']);

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
    // Check the three modules local tasks (tabs) are displayed as expected.
    $local_tasks_regex = '/<div.*-primary-local-tasks.*>([\r\n].*)+<a.*>Toolbar settings<\/a>.*[\r\n].*<a.*>Search settings<\/a>.*[\r\n].*<a.*>Tools settings<\/a>.*[\r\n].*([\r\n].*)+<\/div>/';
    $assert->responseMatches($local_tasks_regex);

    /* Test the permission: 'use admin toolbar search'. */

    $admin_toolbar_search_css = 'admin_toolbar_search/css/admin.toolbar_search.css';
    $admin_toolbar_search_js = 'admin_toolbar_search/js/admin_toolbar_search.js';
    // Check the CSS and JS files of the module are loaded correctly.
    $assert->responseContains($admin_toolbar_search_css);
    $assert->responseContains($admin_toolbar_search_js);

    // Test the extra links search route does not return an access denied error,
    // but an empty array, even with the 'admin_toolbar_tools' module enabled,
    // since the modules adding the extra links are all disabled in this test
    // ('field_ui', 'node', 'media', 'menu_ui', etc...).
    $this->drupalGet('/admin/admin-toolbar-search');
    $assert->responseContains('[]');

    // Logout the current session and login with a user *without* search access.
    $this->drupalLogout();
    $this->drupalLogin($this->noAccessUser);

    // Check the 'Search' tab is *not* displayed, since the user does not have
    // the required permission.
    // Check the CSS and JS files of the module are *not* loaded.
    $assert->responseNotContains($admin_toolbar_search_css);
    $assert->responseNotContains($admin_toolbar_search_js);

    // Check none of the HTML IDs selectors are found on the page, which means
    // the admin toolbar search is not loaded.
    $assert->responseNotContains($this->testHtmlIds['search_tab']);
    $assert->responseNotContains($this->testHtmlIds['search_toolbar_item']);
    $assert->responseNotContains($this->testHtmlIds['search_tray']);
    $assert->responseNotContains($this->testHtmlIds['search_tab_mobile']);

    // Ensure the extra links search route is not accessible without the
    // required permission.
    $this->drupalGet('/admin/admin-toolbar-search');
    $assert->statusCodeEquals(403);
  }

}
