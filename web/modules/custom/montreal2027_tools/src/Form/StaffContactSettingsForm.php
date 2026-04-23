<?php

namespace Drupal\montreal2027_tools\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for the staff contact modal.
 */
class StaffContactSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'montreal2027_tools_staff_contact_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['montreal2027_tools.staff_contact_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('montreal2027_tools.staff_contact_settings');
    $failure_message = trim((string) ($config->get('failure_message') ?? ''));
    $success_message = trim((string) ($config->get('success_message') ?? ''));
    $subject_prefix = trim((string) ($config->get('subject_prefix') ?? ''));
    $heading_prefix = trim((string) ($config->get('heading_prefix') ?? ''));

    if ($failure_message === '') {
      $failure_message = 'There was a problem sending your message.  Please try again later.';
    }
    if ($success_message === '') {
      $success_message = 'Your message has been sent.  A member of our staff will reach out if appropriate.';
    }
    if ($subject_prefix === '') {
      $subject_prefix = 'Staff contact message for ';
    }
    if ($heading_prefix === '') {
      $heading_prefix = 'Send a message to the ';
    }

    $form['failure_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Failure message'),
      '#description' => $this->t('Shown in the status modal when the message cannot be sent.'),
      '#default_value' => $failure_message,
      '#required' => TRUE,
      '#rows' => 3,
    ];

    $form['success_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Success message'),
      '#description' => $this->t('Shown in the status modal after a successful send.'),
      '#default_value' => $success_message,
      '#required' => TRUE,
      '#rows' => 3,
    ];

    $form['subject_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject prefix'),
      '#description' => $this->t('Prepended to the division label in outgoing email subjects.'),
      '#default_value' => $subject_prefix,
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['heading_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form heading prefix'),
      '#description' => $this->t('Prepended to the division name in the contact modal heading.'),
      '#default_value' => $heading_prefix,
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->configFactory->getEditable('montreal2027_tools.staff_contact_settings')
      ->set('failure_message', trim((string) $form_state->getValue('failure_message')))
      ->set('success_message', trim((string) $form_state->getValue('success_message')))
      ->set('subject_prefix', trim((string) $form_state->getValue('subject_prefix')))
      ->set('heading_prefix', trim((string) $form_state->getValue('heading_prefix')))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
