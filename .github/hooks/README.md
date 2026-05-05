# Git Hooks for Montreal 2027 Project

This directory contains validation scripts and AI agent hooks for the project.

## Git Pre-Commit Hook

### Setup

Install the pre-commit hook to automatically validate Drupal configuration before commits:

```bash
# Make the script executable
chmod +x .github/hooks/pre-commit-config-check.sh

# Create symlink in .git/hooks/
ln -sf ../../.github/hooks/pre-commit-config-check.sh .git/hooks/pre-commit
```

### What It Does

The pre-commit hook (`pre-commit-config-check.sh`):
- Runs automatically before each `git commit`
- Auto-detects environment (Docker vs local Drush)
- Checks if Drupal configuration is in sync with the database
- Blocks commits if config changes haven't been exported
- Shows the correct `drush cex` command for your environment

**Environment Detection:**
1. Checks if Docker containers are running → uses `docker-compose exec web drush`
2. Falls back to local Drush if available → uses `drush`
3. Provides clear error if neither is found

### Manual Usage

You can also run the validation manually:

```bash
./.github/hooks/pre-commit-config-check.sh
```

### Bypassing the Hook

In rare cases where you need to commit without exporting config:

```bash
git commit --no-verify -m "Your message"
```

**Warning:** Only use `--no-verify` when absolutely necessary (e.g., emergency hotfix).

## AI Agent Hook

### What It Does

The `drupal-config-validation.json` file is a VS Code Copilot hook that:
- Monitors when the AI agent tries to run git commands
- Sends a reminder message about exporting config before commits
- Helps prevent forgotten config exports in AI-assisted workflows

### How It Works

When GitHub Copilot detects `git commit` or `git push` commands, it automatically reminds the agent (and you) to validate configuration first.

No setup needed - VS Code automatically reads `.github/hooks/*.json` files.

## Troubleshooting

### "Cannot find Drush" error

The hook auto-detects your environment. If you see this error:
- **For Docker:** Ensure containers are running: `docker-compose up -d`
- **For local:** Install Drush globally or ensure it's in your PATH

### Hook not running

Check if the symlink is correct:
```bash
ls -la .git/hooks/pre-commit
```

Should show: `.git/hooks/pre-commit -> ../../.github/hooks/pre-commit-config-check.sh`

### Permission denied

Make the script executable:
```bash
chmod +x .github/hooks/pre-commit-config-check.sh
```

## Best Practices

1. **Always export config after admin UI changes:** `docker-compose exec web drush cex` (Docker) or `drush cex` (local)
2. **Commit config with related code:** Config and code changes should be in the same commit
3. **Test config import:** Before pushing, verify `drush cim` works cleanly
4. **Document breaking changes:** Update README if config requires manual steps
