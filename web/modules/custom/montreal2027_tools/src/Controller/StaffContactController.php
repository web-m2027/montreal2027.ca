<?php

namespace Drupal\montreal2027_tools\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles AJAX submission for staff contact messages.
 */
class StaffContactController extends ControllerBase {

  /**
   * Constructs the contact controller.
   */
  public function __construct(
    private readonly MailManagerInterface $mailManager,
    private readonly EntityTypeManagerInterface $typedEntityManager,
    private readonly LanguageManagerInterface $siteLanguageManager,
    private readonly LoggerChannelFactoryInterface $logChannelFactory,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('plugin.manager.mail'),
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('logger.factory'),
    );
  }

  /**
   * Submits a contact message to the staff term's email address.
   */
  public function submit(Request $request): JsonResponse {
    $config = $this->config('montreal2027_tools.staff_page_settings');

    $failure_message = trim((string) ($config->get('failure_message') ?? ''));
    if ($failure_message === '') {
      $failure_message = 'There was a problem sending your message.  Please try again later.';
    }

    $success_message = trim((string) ($config->get('success_message') ?? ''));
    if ($success_message === '') {
      $success_message = 'Your message has been sent.  A member of our staff will reach out if appropriate.';
    }

    $subject_prefix = trim((string) ($config->get('subject_prefix') ?? ''));
    if ($subject_prefix === '') {
      $subject_prefix = 'Staff contact message for ';
    }

    $tid = (int) $request->request->get('tid', 0);
    $name = trim((string) $request->request->get('name', ''));
    $email = trim((string) $request->request->get('email', ''));
    $message = trim((string) $request->request->get('message', ''));

    if ($tid <= 0 || $name === '' || $email === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => $failure_message,
      ], 400);
    }

    /** @var \Drupal\taxonomy\TermInterface|null $term */
    $term = $this->typedEntityManager->getStorage('taxonomy_term')->load($tid);
    if (!$term || !$term->hasField('field_email_address') || $term->get('field_email_address')->isEmpty()) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => $failure_message,
      ], 404);
    }

    $recipient = '';
    foreach ($term->get('field_email_address') as $item) {
      $candidate = trim((string) $item->value);
      if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
        $recipient = $candidate;
        break;
      }
    }

    if ($recipient === '') {
      return new JsonResponse([
        'success' => FALSE,
        'message' => $failure_message,
      ], 404);
    }

    // Prevent header injection from line breaks in user-entered name.
    $safe_name = preg_replace('/[\r\n]+/', ' ', $name) ?? '';
    $safe_name = trim($safe_name);
    if ($safe_name === '') {
      return new JsonResponse([
        'success' => FALSE,
        'message' => $failure_message,
      ], 400);
    }

    $langcode = $this->siteLanguageManager->getCurrentLanguage()->getId();

    /** @var \Drupal\taxonomy\TermStorageInterface $term_storage */
    $term_storage = $this->typedEntityManager->getStorage('taxonomy_term');
    $all_parents = $term_storage->loadAllParents($tid);
    $division_label = (string) $term->label();
    foreach ($all_parents as $ancestor_term) {
      $division_label = (string) $ancestor_term->label();
    }

    $subject = $subject_prefix !== ''
      ? rtrim($subject_prefix) . ' ' . $division_label
      : $division_label;

    $result = $this->mailManager->mail(
      'montreal2027_tools',
      'staff_contact',
      $recipient,
      $langcode,
      [
        'subject' => $subject,
        'body' => $message,
        'from_name' => $safe_name,
        'from_email' => $email,
      ],
      $email,
      TRUE,
    );

    if (empty($result['result'])) {
      $this->logChannelFactory->get('montreal2027_tools')->error('Failed to send staff contact email for term ID @tid.', [
        '@tid' => $tid,
      ]);
      return new JsonResponse([
        'success' => FALSE,
        'message' => $failure_message,
      ], 500);
    }

    return new JsonResponse([
      'success' => TRUE,
      'message' => $success_message,
    ]);
  }

}