<?php

namespace Drupal\Tests\synonyms_select\Functional;

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
    'synonyms_select',
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
   * 
   * It should contain the default select widget wording.
   */
  public function testSynonymsAdmin() {
    // Load the main admin page.
    $this->drupalGet('admin/structure/synonyms');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Synonyms configuration');
    $session->pageTextContains('Default wordings:');
    $session->pageTextContains('Synonyms-friendly select widget: @synonym is the @field_label of @entity_label');

    // Load the Synonyms settings page.
    $this->drupalGet('admin/structure/synonyms/settings');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Synonyms settings');
    $session->fieldValueEquals('select', '@synonym is the @field_label of @entity_label');
    $session->buttonExists('Save configuration');

    // Edit settings.
    $edit = [
      'wording_type' => 'default',
      'select' => 'Test wording',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Confirm the change.
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->addressEquals('admin/structure/synonyms/settings');
    $session->pageTextContains('Synonyms settings');
    $session->fieldValueEquals('select', 'Test wording');
    $session->buttonExists('Save configuration');

    // Load the Manage behaviors page for User entity type.
    $this->drupalGet('admin/structure/synonyms/behavior/user/user');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Manage behaviors of User');
    $session->pageTextContains('Select service');
    $session->checkboxNotChecked('select_status');
    $session->buttonExists('Save configuration');

    // Edit settings.
    $edit = [
      'select_status' => 1,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Confirm the change.
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->addressEquals('admin/structure/synonyms/behavior/user/user');
    $session->pageTextContains('Manage behaviors of User');
    $session->pageTextContains('Select service');
    $session->checkboxChecked('select_status');
    $session->buttonExists('Save configuration');
  }

}
