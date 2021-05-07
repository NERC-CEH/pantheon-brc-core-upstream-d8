<?php

namespace Drupal\Tests\synonyms_list_field\Functional;

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
    'synonyms_list_field',
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
   * Make sure the main admin page loads correctly.
   */
  public function testSynonymsAdmin() {
    // Load the synonyms overview page.
    $this->drupalGet('admin/structure/synonyms');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Synonyms configuration');
    $session->pageTextContains('Include entity label');
    $session->pageTextContains('No');

    // Load the Synonyms list field settings page.
    $this->drupalGet('admin/structure/synonyms_list_field/settings');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Synonyms list field settings');
    $session->pageTextContains('Include entity label');
    $session->checkboxNotChecked('include_entity_label');
    $session->buttonExists('Save configuration');

    // Edit settings.
    $edit = [
      'include_entity_label' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Confirm the change.
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->addressEquals('admin/structure/synonyms_list_field/settings');
    $session->pageTextContains('Synonyms list field settings');
    $session->pageTextContains('Include entity label');
    $session->checkboxChecked('include_entity_label');
    $session->buttonExists('Save configuration');

    // Confirm the change at synonyms overview page.
    $this->drupalGet('admin/structure/synonyms');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Synonyms configuration');
    $session->pageTextContains('Include entity label');
    $session->pageTextContains('Yes');
  }

}
