<?php

namespace Drupal\montreal2027_tools\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for the staff page and contact modal.
 */
class StaffPageSettingsForm extends ConfigFormBase {

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
    return ['montreal2027_tools.staff_page_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('montreal2027_tools.staff_page_settings');
    
    // Page content settings
    $page_title = trim((string) ($config->get('page_title.value') ?? ''));
    $page_title_format = $config->get('page_title.format') ?? 'full_html';
    $page_body = trim((string) ($config->get('page_body.value') ?? ''));
    $page_body_format = $config->get('page_body.format') ?? 'full_html';
    $empty_position_link_title = trim((string) ($config->get('empty_position_link.title') ?? ''));
    $empty_position_link_uri = trim((string) ($config->get('empty_position_link.uri') ?? ''));
    
    if ($page_title === '') {
      $page_title = '<h1>Committee &amp; Staff</h1>';
    }
    if ($page_body === '') {
      $page_body = '<p>Phosfluorescently parallel task technically sound benefits without technically sound ideas. Seamlessly underwhelm principle-centered results rather than B2C infomediaries. Interactively disseminate market-driven platforms vis-a-vis out-of-the-box applications.</p>';
    }
    
    // Contact form settings
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

    // Page Content fieldset
    $form['page_content'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Page Content'),
      '#collapsible' => FALSE,
    ];

    $form['page_content']['page_title'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Page title'),
      '#description' => $this->t('The main heading displayed at the top of the staff page.'),
      '#default_value' => $page_title,
      '#required' => TRUE,
      '#format' => $page_title_format,
    ];

    $form['page_content']['page_body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Introduction text'),
      '#description' => $this->t('The introductory paragraph displayed below the page title.'),
      '#default_value' => $page_body,
      '#required' => TRUE,
      '#format' => $page_body_format,
    ];

    $form['page_content']['empty_position_link'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $form['page_content']['empty_position_link']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empty position link text'),
      '#description' => $this->t('Text for the link shown when no positions are found.'),
      '#default_value' => $empty_position_link_title,
      '#maxlength' => 255,
    ];

    $form['page_content']['empty_position_link']['uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empty position link URL'),
      '#description' => $this->t('URL for the empty position link. Use internal paths (e.g., /contact) or external URLs (e.g., https://example.com).'),
      '#default_value' => $empty_position_link_uri,
      '#maxlength' => 2048,
    ];

    // Contact Form Settings fieldset
    $form['contact_form'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Contact Form Settings'),
      '#collapsible' => FALSE,
    ];

    $form['contact_form']['failure_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Failure message'),
      '#description' => $this->t('Shown in the status modal when the message cannot be sent.'),
      '#default_value' => $failure_message,
      '#required' => TRUE,
      '#rows' => 3,
    ];

    $form['contact_form']['success_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Success message'),
      '#description' => $this->t('Shown in the status modal after a successful send.'),
      '#default_value' => $success_message,
      '#required' => TRUE,
      '#rows' => 3,
    ];

    $form['contact_form']['subject_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject prefix'),
      '#description' => $this->t('Prepended to the division label in outgoing email subjects.'),
      '#default_value' => $subject_prefix,
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['contact_form']['heading_prefix'] = [
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
    $page_title_value = $form_state->getValue('page_title');
    $page_body_value = $form_state->getValue('page_body');
    $empty_position_link = $form_state->getValue('empty_position_link');
    
    $this->configFactory->getEditable('montreal2027_tools.staff_page_settings')
      ->set('page_title.value', trim((string) ($page_title_value['value'] ?? '')))
      ->set('page_title.format', $page_title_value['format'] ?? 'full_html')
      ->set('page_body.value', trim((string) ($page_body_value['value'] ?? '')))
      ->set('page_body.format', $page_body_value['format'] ?? 'full_html')
      ->set('empty_position_link.title', trim((string) ($empty_position_link['title'] ?? '')))
      ->set('empty_position_link.uri', trim((string) ($empty_position_link['uri'] ?? '')))
      ->set('failure_message', trim((string) $form_state->getValue('failure_message')))
      ->set('success_message', trim((string) $form_state->getValue('success_message')))
      ->set('subject_prefix', trim((string) $form_state->getValue('subject_prefix')))
      ->set('heading_prefix', trim((string) $form_state->getValue('heading_prefix')))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
