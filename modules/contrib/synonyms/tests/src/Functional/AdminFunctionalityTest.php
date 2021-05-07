<?php

namespace Drupal\Tests\synonyms\Functional;

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
    'synonyms',
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
    $account = $this->drupalCreateUser();
    $this->drupalLogin($account);
  }

  /**
   * Make sure the main admin page does not load.
   *
   * It should load if Synonyms UI module is installed only.
   */
  public function testSynonymsAdmin() {
    // Try to load the main admin page.
    $this->drupalGet('admin/structure/synonyms');
    $session = $this->assertSession();
    $session->statusCodeEquals(404);
  }

}
