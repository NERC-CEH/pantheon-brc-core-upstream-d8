<?php

namespace Drupal\Tests\synonyms_search\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Checks if admin functionality works correctly.
 *
 * @group synonyms
 */
class AdminFunctionalityTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'synonyms_ui',
    'synonyms_search',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->container->get('router.builder')->rebuild();

    // Log in an admin user.
    $account = $this->drupalCreateUser([
      'administer site configuration',
      'administer synonyms',
    ]);
    $this->drupalLogin($account);
  }

  /**
   * Make sure the Manage behaviors page loads correctly.
   *
   * It should have the un-checked search service checkbox.
   */
  public function testSynonymsAdmin() {
    // Load the Manage behaviors page for User entity type.
    $this->drupalGet('admin/structure/synonyms/behavior/user/user');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Manage behaviors of User');
    $session->pageTextContains('Search service');
    $session->checkboxNotChecked('search_status');
    $session->buttonExists('Save configuration');

    // Edit settings.
    $edit = [
      'search_status' => 1,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Confirm the change.
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->addressEquals('admin/structure/synonyms/behavior/user/user');
    $session->pageTextContains('Manage behaviors of User');
    $session->pageTextContains('Search service');
    $session->checkboxChecked('search_status');
    $session->buttonExists('Save configuration');
  }

}
