<?php

namespace Drupal\montreal2027_tools\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\UserInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for contact CSV imports.
 */
class ContactImportCommands extends DrushCommands {

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * Constructs the command class.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct();
    $this->userStorage = $entityTypeManager->getStorage('user');
  }

  /**
   * Imports contacts from CSV and creates users when they do not exist.
   *
   * Expected CSV columns:
   * - First Name
   * - Last Name
   * - E-mail 1 - Value
   *
   * @command montreal2027:import-contacts
   * @aliases m2027:ic
   * @option file Absolute or relative path to CSV file.
   * @option dry-run Report actions without creating users.
   * @option status User account status for created users (0=blocked, 1=active).
   * @option first-name-column CSV header name for first name column.
   * @option last-name-column CSV header name for last name column.
   * @option email-column CSV header name for email column.
   * @usage drush montreal2027:import-contacts --file=/Users/daveweiner/Downloads/contacts.csv --dry-run
   *   Preview import results without creating users.
   * @usage drush montreal2027:import-contacts --file=/Users/daveweiner/Downloads/contacts.csv
   *   Import CSV and create missing users as blocked accounts (default).
   * @usage drush montreal2027:import-contacts --file=/Users/daveweiner/Downloads/contacts.csv --status=1
   *   Import CSV and create missing users as active accounts.
   * @usage drush m2027:ic --file=/tmp/contacts.csv --email-column='Primary Email'
   *   Override CSV email column header.
   */
  public function importContacts(array $options = [
    'file' => NULL,
    'dry-run' => FALSE,
    'status' => 0,
    'first-name-column' => 'First Name',
    'last-name-column' => 'Last Name',
    'email-column' => 'E-mail 1 - Value',
  ]): int {
    $file = $options['file'] ?? NULL;
    $dryRun = (bool) ($options['dry-run'] ?? FALSE);
    $status = isset($options['status']) ? (int) $options['status'] : 0;
    $firstNameColumn = trim((string) ($options['first-name-column'] ?? 'First Name'));
    $lastNameColumn = trim((string) ($options['last-name-column'] ?? 'Last Name'));
    $emailColumn = trim((string) ($options['email-column'] ?? 'E-mail 1 - Value'));

    if (!in_array($status, [0, 1], TRUE)) {
      $this->logger()->error('Invalid --status value. Allowed values are 0 (blocked) or 1 (active).');
      return self::EXIT_FAILURE;
    }

    if (empty($file)) {
      $this->logger()->error('Missing required option: --file=/path/to/contacts.csv');
      return self::EXIT_FAILURE;
    }

    $path = $this->resolvePath($file);
    if (!is_readable($path)) {
      $this->logger()->error(sprintf('Cannot read CSV file: %s', $path));
      return self::EXIT_FAILURE;
    }

    $handle = fopen($path, 'rb');
    if ($handle === FALSE) {
      $this->logger()->error(sprintf('Failed to open CSV file: %s', $path));
      return self::EXIT_FAILURE;
    }

    $header = fgetcsv($handle);
    if (empty($header)) {
      fclose($handle);
      $this->logger()->error('CSV appears to be empty.');
      return self::EXIT_FAILURE;
    }

    $requiredColumns = [$firstNameColumn, $lastNameColumn, $emailColumn];
    foreach ($requiredColumns as $requiredColumn) {
      if (!in_array($requiredColumn, $header, TRUE)) {
        fclose($handle);
        $this->logger()->error(sprintf('CSV is missing required header: %s', $requiredColumn));
        return self::EXIT_FAILURE;
      }
    }

    $line = 1;
    $summary = [
      'existing' => 0,
      'created' => 0,
      'skipped' => 0,
      'failed' => 0,
    ];

    while (($row = fgetcsv($handle)) !== FALSE) {
      $line++;
      $data = $this->combineHeaderAndRow($header, $row);
      if ($data === NULL) {
        $summary['failed']++;
        $this->output()->writeln("[Row {$line}] FAILED: Header/data column mismatch.");
        continue;
      }

      $firstName = trim((string) ($data[$firstNameColumn] ?? ''));
      $lastName = trim((string) ($data[$lastNameColumn] ?? ''));
      $email = trim((string) ($data[$emailColumn] ?? ''));
      $displayName = trim($firstName . ' ' . $lastName);

      if ($lastName === '') {
        $summary['skipped']++;
        $this->output()->writeln("[Row {$line}] SKIP: No last name.");
        continue;
      }

      if ($email === '') {
        $summary['skipped']++;
        $this->output()->writeln("[Row {$line}] SKIP: No email.");
        continue;
      }

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $summary['failed']++;
        $this->output()->writeln("[Row {$line}] FAILED: Invalid email ({$email}).");
        continue;
      }

      if (!preg_match('/@montreal2027\.ca$/i', $email)) {
        $summary['skipped']++;
        $this->output()->writeln("[Row {$line}] SKIP: Email is not in montreal2027.ca domain ({$email}).");
        continue;
      }

      $existing = $this->userStorage->loadByProperties(['mail' => $email]);
      if (!empty($existing)) {
        $summary['existing']++;
        /** @var UserInterface $account */
        $account = reset($existing);
        $this->output()->writeln("[Row {$line}] EXISTS: {$email} (uid {$account->id()}, username {$account->getAccountName()}).");
        continue;
      }

      $username = strtolower($email);

      $usernameMatch = $this->userStorage->loadByProperties(['name' => $username]);
      if (!empty($usernameMatch)) {
        $summary['failed']++;
        $this->output()->writeln("[Row {$line}] FAILED: Username already exists ({$username}) for a different account.");
        continue;
      }

      if ($dryRun) {
        $summary['created']++;
        $statusText = $status === 1 ? 'active' : 'blocked';
        $this->output()->writeln("[Row {$line}] WOULD CREATE: {$email} (username {$username}, status {$statusText}, display name {$displayName}).");
        continue;
      }

      $this->output()->write("[Row {$line}] CREATING: {$email} ... ");

      try {
        /** @var UserInterface $account */
        $account = $this->userStorage->create([
          'name' => $username,
          'mail' => $email,
          'init' => $email,
          'status' => $status,
        ]);

        if ($account->hasField('field_display_name')) {
          $account->set('field_display_name', $displayName);
        }

        $account->setPassword(bin2hex(random_bytes(16)));
        $account->save();

        $summary['created']++;
        $this->output()->writeln("SUCCESS (uid {$account->id()}, username {$username}).");
      }
      catch (\Throwable $e) {
        $summary['failed']++;
        $this->output()->writeln("FAILED ({$e->getMessage()}).");
      }
    }

    fclose($handle);

    $mode = $dryRun ? 'DRY-RUN' : 'LIVE';
    $createdLabel = $dryRun ? 'Would create' : 'Created';
    $this->output()->writeln("---- Summary ({$mode}) ----");
    $this->output()->writeln("Existing: {$summary['existing']}");
    $this->output()->writeln("{$createdLabel}: {$summary['created']}");
    $this->output()->writeln("Skipped: {$summary['skipped']}");
    $this->output()->writeln("Failed: {$summary['failed']}");

    return self::EXIT_SUCCESS;
  }

  /**
   * Resolves a file path relative to the current working directory.
   */
  protected function resolvePath(string $file): string {
    if (str_starts_with($file, '/')) {
      return $file;
    }

    $cwd = getcwd() ?: '';
    return rtrim($cwd, '/') . '/' . ltrim($file, '/');
  }

  /**
   * Combines a CSV header and row; returns NULL if columns are mismatched.
   */
  protected function combineHeaderAndRow(array $header, array $row): ?array {
    if (count($header) !== count($row)) {
      return NULL;
    }

    $combined = array_combine($header, $row);
    return $combined === FALSE ? NULL : $combined;
  }

}
