<?php

namespace Drupal\Tests\synonyms_autocomplete\Functional;

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
    'synonyms_autocomplete',
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
   * It should contain the default Autocomplete widget wording.
   */
  public function testSynonymsAdmin() {
    // Load the main admin page.
    $this->drupalGet('admin/structure/synonyms');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Synonyms configuration');
    $session->pageTextContains('Default wordings:');
    $session->pageTextContains('Synonyms-friendly autocomplete widget: @synonym is the @field_label of @entity_label');

    // Load the Synonyms settings page.
    $this->drupalGet('admin/structure/synonyms/settings');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Synonyms settings');
    $session->fieldValueEquals('autocomplete', '@synonym is the @field_label of @entity_label');
    $session->buttonExists('Save configuration');

    // Edit settings.
    $edit = [
      'wording_type' => 'default',
      'autocomplete' => 'Test wording',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Confirm the change.
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->addressEquals('admin/structure/synonyms/settings');
    $session->pageTextContains('Synonyms settings');
    $session->fieldValueEquals('autocomplete', 'Test wording');
    $session->buttonExists('Save configuration');

    // Load the Manage behaviors page for User entity type.
    $this->drupalGet('admin/structure/synonyms/behavior/user/user');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Manage behaviors of User');
    $session->pageTextContains('Autocomplete service');
    $session->checkboxNotChecked('autocomplete_status');
    $session->buttonExists('Save configuration');

    // Edit settings.
    $edit = [
      'autocomplete_status' => 1,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Confirm the change.
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->addressEquals('admin/structure/synonyms/behavior/user/user');
    $session->pageTextContains('Manage behaviors of User');
    $session->pageTextContains('Autocomplete service');
    $session->checkboxChecked('autocomplete_status');
    $session->buttonExists('Save configuration');
  }

}
