<?php

namespace Drupal\Tests\entity_legal\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Test\AssertMailTrait;
use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\entity_legal\Traits\EntityLegalTestTrait;
use Drupal\user\Entity\User;

/**
 * Tests the 'redirect' plugin.
 *
 * @group entity_legal
 */
class RedirectMethodTest extends BrowserTestBase {

  use AssertMailTrait;
  use EntityLegalTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'entity_legal_test',
  ];

  /**
   * Redirection method test.
   */
  public function _testRedirectMethod() {
    $this->drupalPlaceBlock('system_menu_block:account');
    $document = $this->createDocument(TRUE, TRUE, [
      'existing_users' => [
        'require_method' => 'redirect',
      ],
    ]);
    $this->createDocumentVersion($document, TRUE);

    $account = $this->createUserWithAcceptancePermissions($document);
    $this->drupalLogin($account);

    $document_path = $document->toUrl()->setAbsolute()->toString();

    /** @var \Drupal\user\UserInterface $user */
    $user = \Drupal::entityTypeManager()->getStorage('user')->load($account->id());

    $this->assertUrl($document_path);

    $this->drupalGet('');

    $this->assertUrl($document_path);

    // Check that users are able to logout even they don't accept the agreement.
    $this->clickLink('Log out');
    // Check that the user has been logged out.
    $this->assertLink('Log in');

    $this->drupalPostForm(NULL, [
      'name' => $account->getAccountName(),
      'pass' => $account->pass_raw,
    ], 'Log in');

    $this->assertText('You must accept this agreement before continuing.');
    $this->assertText('I agree to the document');

    // Agree with the terms.
    $this->drupalPostForm(NULL, ['agree' => TRUE], 'Submit');

    // Refresh the page.
    $this->drupalGet('');

    $this->assertUrl($user->toUrl()->setAbsolute()->toString());

    $this->clickLink('Log out');

    // Release a new document version.
    $this->createDocumentVersion($document, TRUE);

    $this->drupalPostForm(Url::fromRoute('user.pass'), [
      'name' => $account->getAccountName(),
    ], 'Submit');
    $this->assertText('Further instructions have been sent to your email address.');

    // Click the one-time login link received by mail and login.
    $this->clickMailOneTimeLoginLink();
    $this->assertText("This is a one-time login for {$account->getAccountName()} and will expire on");
    $this->drupalPostForm(NULL, [], 'Log in');

    // Check that we've landed on the password change user account edit page.
    $this->assertText('You have just used your one-time login link. It is no longer necessary to use this link to log in. Please change your password.');
    $this->assertFieldByName('mail');
    $this->assertText('A valid email address. All emails from the system will be sent to this address. The email address is not made public and will only be used if you wish to receive a new password or wish to receive certain news or notifications by email.');
    $this->assertFieldByName('pass[pass1]');
    $this->assertFieldByName('pass[pass2]');

    // Set a new password.
    $new_password = $this->randomString();
    $account->pass_raw = $new_password;
    $this->drupalPostForm(NULL, [
      'pass[pass1]' => $new_password,
      'pass[pass2]' => $new_password,
    ], 'Save');

    // The new password is set but user has to accept the new document version.
    $this->assertText('The changes have been saved.');
    $this->assertText('You must accept this agreement before continuing.');
    $this->assertText('I agree to the document');

    // Agree with the terms.
    $this->drupalPostForm(NULL, ['agree' => TRUE], 'Submit');

    // Check that we're on the user account edit form page.
    $this->assertFieldByName('mail');
    $this->assertFieldByName('pass[pass1]');
    $this->assertFieldByName('pass[pass2]');

    // Release a new document version.
    $newest_version = $this->createDocumentVersion($document, TRUE);

    // Try to bypass by faking a user reset URL.
    $this->drupalGet("/user/{$account->id()}/edit", [
      'query' => [
        'pass-reset-token' => 'arbitrary-invalid-token',
      ],
    ]);
    // Check that the approval gate cannot be bypassed.
    $this->assertText('You must accept this agreement before continuing.');
    $this->assertText('I agree to the document');

    $this->clickLink('Log out');

    // Checks the CSRF protecting session token route.
    $this->drupalGet(Url::fromRoute('system.csrftoken'));

    // Testing a non-HTML request.
    \Drupal::service('module_installer')->install(['jsonapi']);
    $http_client = \Drupal::httpClient();
    $url = Url::fromRoute("jsonapi.entity_legal_document_version--{$document->id()}.collection")
      ->setAbsolute()
      ->toString();
    $response = $http_client->get($url, [
      'headers' => [
        'Accept' => 'application/vnd.api+json',
      ],
    ]);
    $jsonapi = Json::decode($response->getBody()->getContents());
    $this->assertTrue(isset($jsonapi['jsonapi']['version']) && isset($jsonapi['data']));
  }

  /**
   * Tests the case when the origin pages has set messages.
   *
   * An origin page might have set status, warning or error messages to be
   * displayed on the destination page. Test that such messages are not
   * shown on the entity legal acceptance page.
   */
  public function testMessageDisplay() {
    $document = $this->createDocument(TRUE, TRUE, [
      'existing_users' => [
        'require_method' => 'redirect',
      ],
    ]);
    $this->createDocumentVersion($document, TRUE);
    $this->drupalLogin($this->createUserWithAcceptancePermissions($document));

    // Check that the acceptance form doesn't contain the messages.
    $this->assertSession()->pageTextContains('You must accept this agreement before continuing.');
    $this->assertSession()->pageTextNotContains('A status message sample');
    $this->assertSession()->pageTextNotContains('A warning message sample');
    $this->assertSession()->pageTextNotContains('An error message sample');

    $page = $this->getSession()->getPage();
    $page->checkField('I agree to the document');
    $page->pressButton('Submit');

    // Check that messages are shown on the destination page.
    $account = User::load(\Drupal::currentUser()->id());
    $this->assertUrl($account->toUrl());
    $this->assertSession()->pageTextContains('A status message sample');
    $this->assertSession()->pageTextContains('A warning message sample');
    $this->assertSession()->pageTextContains('An error message sample');
  }

  /**
   * Clicks on the email password reset one-time-login link.
   */
  protected function clickMailOneTimeLoginLink() {
    // Assume the most recent email.
    $emails = $this->getMails();
    $email = end($emails);
    $urls = [];
    preg_match('#.+user/reset/.+#', $email['body'], $urls);
    $this->drupalGet($urls[0]);
  }

}
