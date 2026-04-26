<?php

namespace Drupal\montreal2027_tools\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Component\Utility\Html;
use Drupal\taxonomy\TermStorageInterface;

/**
 * Builds the staff listing page.
 */
class StaffPageController extends ControllerBase {

  /**
   * Builds the /staff page output.
   */
  public function build(): array {
    $config = $this->config('montreal2027_tools.staff_page_settings');
    $heading_prefix = trim((string) ($config->get('heading_prefix') ?? ''));
    if ($heading_prefix === '') {
      $heading_prefix = 'Send a message to the ';
    }

    $page_title_value = trim((string) ($config->get('page_title.value') ?? ''));
    $page_title_format = $config->get('page_title.format') ?? 'full_html';
    if ($page_title_value === '') {
      $page_title_value = '<h1>Committee &amp; Staff</h1>';
    }

    $page_body_value = trim((string) ($config->get('page_body.value') ?? ''));
    $page_body_format = $config->get('page_body.format') ?? 'full_html';
    if ($page_body_value === '') {
      $page_body_value = '<p>Phosfluorescently parallel task technically sound benefits without technically sound ideas. Seamlessly underwhelm principle-centered results rather than B2C infomediaries. Interactively disseminate market-driven platforms vis-a-vis out-of-the-box applications.</p>';
    }

    $empty_position_link_title = trim((string) ($config->get('empty_position_link.title') ?? ''));
    $empty_position_link_uri = trim((string) ($config->get('empty_position_link.uri') ?? ''));

    $term_storage = $this->getTermStorage();
    $top_level_terms = $term_storage->loadTree('divisions', 0, 1, TRUE);

    $divisions = [];
    foreach ($top_level_terms as $term) {
      if (!$term) {
        continue;
      }
      $is_vacant_position = FALSE;
      if ($term->hasField('field_vacant_position') && !$term->get('field_vacant_position')->isEmpty()) {
        $is_vacant_position = (bool) $term->get('field_vacant_position')->value;
      }
      $divisions[] = [
        'term' => $term,
        'is_vacant_position' => $is_vacant_position,
        'children' => $this->buildChildData((int) $term->id(), 1),
      ];
    }

    return [
      '#theme' => 'montreal2027_staff_page',
      '#page_title' => [
        '#type' => 'processed_text',
        '#text' => $page_title_value,
        '#format' => $page_title_format,
      ],
      '#page_body' => [
        '#type' => 'processed_text',
        '#text' => $page_body_value,
        '#format' => $page_body_format,
      ],
      '#divisions' => $divisions,
      '#empty_position_link' => [
        'title' => $empty_position_link_title,
        'uri' => $empty_position_link_uri,
      ],
      '#contact_modal_markup' => Markup::create($this->buildContactModalMarkup()),
      '#attached' => [
        'library' => [
          'montreal2027_tools/staff_contact_modal',
        ],
        'drupalSettings' => [
          'montreal2027Tools' => [
            'staffContactHeadingPrefix' => $heading_prefix,
          ],
        ],
      ],
      '#cache' => [
        'contexts' => ['languages:language_interface'],
        'tags' => ['taxonomy_term_list', 'taxonomy_vocabulary:divisions', 'config:montreal2027_tools.staff_page_settings'],
      ],
    ];
  }

  /**
   * Builds recursive child data for a parent term.
   */
  private function buildChildData(int $parent_tid, int $indent): array {
    $term_storage = $this->getTermStorage();
    $children = $term_storage->loadTree('divisions', $parent_tid, 1, TRUE);

    $child_data = [];
    foreach ($children as $term) {
      if (!$term) {
        continue;
      }
      $field_names = [];
      if ($term->hasField('field_name') && !$term->get('field_name')->isEmpty()) {
        foreach ($term->get('field_name') as $field_item) {
          $value = trim((string) $field_item->value);
          if ($value !== '') {
            $field_names[] = $value;
          }
        }
      }

      $has_email_address = FALSE;
      if ($term->hasField('field_email_address') && !$term->get('field_email_address')->isEmpty()) {
        foreach ($term->get('field_email_address') as $email_item) {
          if (trim((string) $email_item->value) !== '') {
            $has_email_address = TRUE;
            break;
          }
        }
      }

      $is_vacant_position = FALSE;
      if ($term->hasField('field_vacant_position') && !$term->get('field_vacant_position')->isEmpty()) {
        $is_vacant_position = (bool) $term->get('field_vacant_position')->value;
      }

      $child_data[] = [
        'term' => $term,
        'indent' => $indent,
        'field_names' => $field_names,
        'has_email_address' => $has_email_address,
        'is_vacant_position' => $is_vacant_position,
        'children' => $this->buildChildData((int) $term->id(), $indent + 1),
      ];
    }

    return $child_data;
  }

  /**
   * Gets taxonomy term storage with the proper interface.
   */
  private function getTermStorage(): TermStorageInterface {
    /** @var \Drupal\taxonomy\TermStorageInterface $term_storage */
    $term_storage = $this->entityTypeManager()->getStorage('taxonomy_term');
    return $term_storage;
  }

  /**
   * Builds the modal markup for staff contact.
   */
  private function buildContactModalMarkup(): string {
    $action = Url::fromRoute('montreal2027_tools.staff_contact_submit')->toString();
    $config = $this->config('montreal2027_tools.staff_page_settings');
    $heading_prefix = trim((string) ($config->get('heading_prefix') ?? ''));
    if ($heading_prefix === '') {
      $heading_prefix = 'Send a message';
    }

    return '<dialog id="staff-contact-modal">'
      . '<form method="dialog" class="staff-contact-modal__close-row">'
      . '<button type="submit" class="staff-contact-modal__close" aria-label="Close">&times;</button>'
      . '</form>'
      . '<h2 id="staff-contact-modal-title">' . Html::escape($heading_prefix) . '</h2>'
      . '<form id="staff-contact-form" method="post" action="' . Html::escape($action) . '">'
      . '<input type="hidden" name="tid" id="staff-contact-tid" value="">'
      . '<label for="staff-contact-name">Your name</label>'
      . '<input id="staff-contact-name" name="name" type="text" required>'
      . '<label for="staff-contact-email">Email address</label>'
      . '<input id="staff-contact-email" name="email" type="email" required>'
      . '<label for="staff-contact-message">Message</label>'
      . '<textarea id="staff-contact-message" name="message" rows="6" required></textarea>'
      . '</form>'
      . '<div class="staff-contact-modal__footer">'
      . '<button id="staff-contact-send" type="submit" form="staff-contact-form">Send</button>'
      . '<form method="dialog" class="staff-contact-modal__cancel">'
      . '<button type="submit">Cancel</button>'
      . '</form>'
      . '</div>'
      . '</dialog>'
      . '<dialog id="staff-contact-status-modal">'
      . '<p id="staff-contact-status-message" role="status" aria-live="polite"></p>'
      . '<button type="button" id="staff-contact-continue">Continue</button>'
      . '</dialog>';
  }

}