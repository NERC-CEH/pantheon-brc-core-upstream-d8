<?php

namespace Drupal\Tests\synonyms_ui\Functional;

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
    // Load the main admin page.
    $this->drupalGet('admin/structure/synonyms');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Synonyms configuration');
    $session->pageTextContains('Wording type');
    $session->pageTextContains('Default');
    $session->pageTextContains('ENTITY TYPE');
    $session->pageTextContains('BUNDLE');
    $session->pageTextContains('PROVIDERS');
    $session->pageTextContains('BEHAVIORS');
    $session->pageTextContains('ACTION');
    $session->pageTextContains('User');
    $session->pageTextContains('Manage providers');
    $session->pageTextContains('Manage behaviors');

    // Load the Manage providers page for Users entity type.
    $this->drupalGet('admin/structure/synonyms/synonym/user/user');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Manage providers of User');
    $session->pageTextContains('PROVIDER');
    $session->pageTextContains('OPERATIONS');
    $session->pageTextNotContains('User ID');

    // Load the Add provider page for Users entity type.
    $this->drupalGet('admin/structure/synonyms/user/user/add');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Add provider');
    $session->pageTextContains('Synonyms provider');
    $session->selectExists('provider_plugin');
    $session->optionExists('provider_plugin', 'User ID');
    $session->buttonExists('Save');

    // Load the Manage behaviors page for User entity type.
    $this->drupalGet('admin/structure/synonyms/behavior/user/user');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Manage behaviors of User');
    $session->buttonExists('Save configuration');

    // Load the Synonyms settings page.
    $this->drupalGet('admin/structure/synonyms/settings');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Synonyms settings');
    $session->pageTextContains('Wording type');
    $session->selectExists('wording_type');
    $session->fieldValueEquals('wording_type', 'default');
    $session->buttonExists('Save configuration');

    // Edit settings.
    $edit = [
      'wording_type' => 'none',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Confirm the change.
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->addressEquals('admin/structure/synonyms/settings');
    $session->pageTextContains('Synonyms settings');
    $session->pageTextContains('Wording type');
    $session->selectExists('wording_type');
    $session->fieldValueEquals('wording_type', 'none');
    $session->buttonExists('Save configuration');
  }

}
