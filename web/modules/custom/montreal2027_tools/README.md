# Montreal 2027 Tools

Custom utility module for Montreal 2027 operations.

## Taxonomy UI Label Overrides

This module customizes the `divisions` taxonomy vocabulary UI labels:

- Term add/edit form field label: `Name` -> `Division Name`
- Terms overview table column header: `Name` -> `Division Name`

Implementation is done with form alter hooks in [web/modules/custom/montreal2027_tools/montreal2027_tools.module](web/modules/custom/montreal2027_tools/montreal2027_tools.module).

## Contact Import Command

This module provides a Drush command that imports contacts from a CSV file and creates Drupal users when they do not already exist by email address.

New users are created with:

- Username = email address (lowercase)
- Email domain restricted to `montreal2027.ca`
- Last Name required

### Command

- `montreal2027:import-contacts`
- Alias: `m2027:ic`

### CSV format

The CSV must include these exact headers:

- `First Name`
- `Last Name`
- `E-mail 1 - Value`

You can override these header names with command options.

### Options

- `--file=/path/to/contacts.csv` (required)
- `--dry-run` (optional): reports actions but does not create users
- `--status=0|1` (optional): account status for newly created users
- `--first-name-column='First Name'` (optional)
- `--last-name-column='Last Name'` (optional)
- `--email-column='E-mail 1 - Value'` (optional)

Status values:
- `0` = blocked (default)
- `1` = active

### Examples

Dry-run preview with default blocked status:

```bash
drush montreal2027:import-contacts --file=/Users/daveweiner/Downloads/contacts.csv --dry-run
```

Live import with default blocked status:

```bash
drush montreal2027:import-contacts --file=/Users/daveweiner/Downloads/contacts.csv
```

Live import creating active users:

```bash
drush montreal2027:import-contacts --file=/Users/daveweiner/Downloads/contacts.csv --status=1
```

Using the alias:

```bash
drush m2027:ic --file=/Users/daveweiner/Downloads/contacts.csv --dry-run
```

Using custom column headers:

```bash
drush m2027:ic \
  --file=/Users/daveweiner/Downloads/contacts.csv \
  --first-name-column='Given Name' \
  --last-name-column='Surname' \
  --email-column='Primary Email' \
  --dry-run
```

### Output behavior

Each row prints a status:

- `EXISTS` when a user with the email already exists
- `WOULD CREATE` in dry-run mode
- `CREATING` followed by `SUCCESS` or `FAILED` in live mode
- `SKIP` for blank email rows

Rows are skipped when:

- Last Name is empty
- Email is empty
- Email is not in the `montreal2027.ca` domain

A summary is printed at the end:

- Existing
- Created (or Would create in dry-run)
- Skipped
- Failed
