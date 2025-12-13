<?php

declare(strict_types=1);

namespace Drupal\Tests\admin_toolbar_search\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\admin_toolbar_search\Constants\AdminToolbarSearchConstants;

/**
 * Test the keyboard shortcut functionality of Admin Toolbar Search.
 *
 * Ensure the search input field is focused when the keyboard shortcut 'Alt + a'
 * is used.
 *
 * @see admin_toolbar_search/js/admin_toolbar_search.keyboard_shortcut.js
 *
 * @group admin_toolbar
 * @group admin_toolbar_search
 */
class AdminToolbarSearchKeyboardShortcutTest extends WebDriverTestBase {

  /**
   * A user with access to the Admin Toolbar Search.
   *
   * An admin user with the permissions to access the toolbar and the search
   * functionality defined by the module.
   *
   * @var \Drupal\user\UserInterface
   */
  public $adminUser;

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
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    /* Setup custom configuration. */

    // Set 'display_menu_item' to TRUE to display the search input field in a
    // tray in the toolbar and not directly as a toolbar item (default).
    $this->config('admin_toolbar_search.settings')
      ->set('display_menu_item', TRUE)
      ->save();

    /* Setup users for the tests. */

    // Access to the toolbar and the admin toolbar search.
    $permissions = [
      'access toolbar',
      // This permission is needed to test the search keyboard shortcut.
      'use admin toolbar search',
    ];

    // Create an admin user with access to the admin toolbar search.
    $this->adminUser = $this->drupalCreateUser($permissions);

    // Login with an admin user with access to the search in the toolbar.
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test the search keyboard shortcut functionality.
   *
   * Ensure the search input field is focused when the keyboard shortcut
   * 'Alt + a' is used:
   * - Check that the search tray and input field are initially *not* visible.
   * - Trigger the keyboard shortcut 'Alt + a'.
   * - Check that the search tray and input field are now visible.
   * - Check that the search input field has focus.
   *
   * This test assumes the 'display_menu_item' setting is enabled, so the
   * search input field is displayed in a tray in the toolbar, thus initially
   * not visible when the page loads.
   *
   * @return void
   *   Nothing to return.
   */
  public function testAdminToolbarSearchKeyboardShortcut() {
    // Get the current test session.
    $test_session = $this->getSession();
    // Get the current page.
    $page = $test_session->getPage();
    // Get the search tray and input field elements.
    $search_tray_element = $page->find('css', '#' . AdminToolbarSearchConstants::ADMIN_TOOLBAR_SEARCH_HTML_IDS['search_tray']);
    $search_input_element = $page->find('css', '#' . AdminToolbarSearchConstants::ADMIN_TOOLBAR_SEARCH_HTML_IDS['search_input']);

    // Check the search tray and input field are initially *not* visible.
    $this->assertFalse($search_tray_element->isVisible());
    $this->assertFalse($search_input_element->isVisible());

    // Trigger the keyboard shortcut 'Alt + a' to focus the search input field.
    $test_session->executeScript("document.dispatchEvent(new KeyboardEvent('keydown', { keyCode: 65, altKey: true }));");

    // Check the search tray and input field are now visible.
    $this->assertTrue($search_tray_element->isVisible());
    $this->assertTrue($search_input_element->isVisible());

    // Check that the search input field has focus.
    $search_input_has_focus = $test_session->evaluateScript('document.activeElement.getAttribute("id") === "' . AdminToolbarSearchConstants::ADMIN_TOOLBAR_SEARCH_HTML_IDS['search_input'] . '";');
    $this->assertTrue($search_input_has_focus);
  }

}
