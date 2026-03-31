<?php

namespace Drupal\montreal2027_tools\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\TranslatableMarkup;
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
    $term_storage = $this->getTermStorage();
    $top_level_terms = $term_storage->loadTree('divisions', 0, 1, TRUE);

    $staff_output = '';
    foreach ($top_level_terms as $term) {
      if (!$term) {
        continue;
      }
      $staff_output .= '<div class="division">' . Html::escape($term->label()) . '</div>';
      $staff_output .= $this->buildChildRows((int) $term->id(), 1);
    }

    if ($staff_output === '') {
      $staff_output = '<div>' . (string) new TranslatableMarkup('No staff entries found.') . '</div>';
    }

    $output = '<h1 class="staff">Committee &amp; Staff</h1><p>Phosfluorescently parallel task technically sound benefits without technically sound ideas. Seamlessly underwhelm principle-centered results rather than B2C infomediaries. Interactively disseminate market-driven platforms vis-a-vis out-of-the-box applications.</p>' . $staff_output;

    return [
      '#markup' => $output,
      '#allowed_tags' => ['h1', 'p', 'div', 'a', 'button', 'br'],
      '#cache' => [
        'tags' => ['taxonomy_term_list', 'taxonomy_vocabulary:divisions'],
      ],
    ];
  }

  /**
   * Builds recursive child rows for a parent term.
   */
  private function buildChildRows(int $parent_tid, int $indent): string {
    $term_storage = $this->getTermStorage();
    $children = $term_storage->loadTree('divisions', $parent_tid, 1, TRUE);

    $output = '';
    foreach ($children as $term) {
      if (!$term) {
        continue;
      }
      $field_names = [];
      if ($term->hasField('field_name') && !$term->get('field_name')->isEmpty()) {
        foreach ($term->get('field_name') as $field_item) {
          $value = trim((string) $field_item->value);
          if ($value !== '') {
            $field_names[] = Html::escape($value);
          }
        }
      }

      $field_name_output = implode('<br>', $field_names);
      $has_email_address = FALSE;
      if ($term->hasField('field_email_address') && !$term->get('field_email_address')->isEmpty()) {
        foreach ($term->get('field_email_address') as $email_item) {
          if (trim((string) $email_item->value) !== '') {
            $has_email_address = TRUE;
            break;
          }
        }
      }

      $output .= '<div class="staff-row indent-' . $indent . '">';
      $output .= '<div>' . Html::escape($term->label()) . '</div>';
      $output .= '<div>' . $field_name_output . '</div>';
      
      if ($has_email_address) {
        $contact_url = Url::fromUserInput('/contact-staff', [
          'query' => ['tid' => (int) $term->id()],
        ])->toString();
        $output .= '<a href="' . Html::escape($contact_url) . '"><button type="button" class="contact-button">Contact</button></a>';
      } else {
$output .= '<div>';
      }
      $output .= '</div>';
      $output .= '</div>';

      $output .= $this->buildChildRows((int) $term->id(), $indent + 1);
    }

    return $output;
  }

  /**
   * Gets taxonomy term storage with the proper interface.
   */
  private function getTermStorage(): TermStorageInterface {
    /** @var \Drupal\taxonomy\TermStorageInterface $term_storage */
    $term_storage = $this->entityTypeManager()->getStorage('taxonomy_term');
    return $term_storage;
  }

}
