<?php

namespace Drupal\Tests\synonyms\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Checks if admin functionality works correctly.
 *
 * @group backup_migrate
 */
class AdminFunctionalityTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'synonyms',
    'synonyms_ui',
    'synonyms_autocomplete',
    'synonyms_select',
    'synonyms_search',
    'synonyms_views_filter',
    'synonyms_views_argument_validator',
  ];

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = TRUE;

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
      'access synonyms entity autocomplete',
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
    $session->pageTextContains('Synonyms');
    $session->pageTextContains('Wording type:');
    $session->pageTextContains('Default.');
    $session->pageTextContains('Default wordings:');
    $session->pageTextContains('Synonyms-friendly autocomplete widget: @synonym is the @field_label of @entity_label');
    $session->pageTextContains('Synonyms-friendly select widget: @synonym is the @field_label of @entity_label');
    $session->pageTextContains('ENTITY TYPE');
    $session->pageTextContains('BUNDLE');
    $session->pageTextContains('PROVIDERS');
    $session->pageTextContains('BEHAVIORS');
    $session->pageTextContains('ACTION');
    $session->pageTextContains('User');
    $session->pageTextContains('Manage providers');
    $session->pageTextContains('Manage behaviors');
  }

}
