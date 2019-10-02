<?php

namespace Drupal\moderation_note\Plugin\Mail;

use Drupal\Core\Mail\Plugin\Mail\PhpMail;

/**
 * Defines the Moderation Note mail backend.
 *
 * @Mail(
 *   id = "moderation_note",
 *   label = @Translation("Moderation note mailer"),
 *   description = @Translation("Sends HTML emails for Moderation Note.")
 * )
 */
class NoteMail extends PhpMail {

  /**
   * {@inheritdoc}
   */
  public function format(array $message) {
    // We have to override the parent method so that HTML is not escaped.
    $message['headers']['Content-Type'] = 'text/html; charset=UTF-8; format=flowed; delsp=yes';
    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function mail(array $message) {
    // We're running inside a functional test.
    if (\Drupal::config('system.mail')->get('interface.default') === 'test_mail_collector') {
      $captured_emails = \Drupal::state()->get('system.test_mail_collector') ?: [];
      $captured_emails[] = $message;
      \Drupal::state()->set('system.test_mail_collector', $captured_emails);
      return TRUE;
    }

    return parent::mail($message);
  }

}
