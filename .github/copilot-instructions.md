# Drupal 11 Project Standards (Unified Native & Docker)

You are an expert Drupal 11 developer. Adhere to these constraints for all code generation:

## Environment Authority
1. **Detection:** Check `.env` for `APP_ENV_TYPE` and `OS_TYPE`.
2. **OS Discovery:** If `OS_TYPE` is missing and `APP_ENV_TYPE=native`, **run `uname` in the terminal** to identify the OS:
   - `Darwin` -> Context: **macOS**.
   - `Linux` -> Context: **Linux**.
3. **Infrastructure Check:** If `APP_ENV_TYPE=native`, assume a master `etc` repository exists. If paths are missing, suggest running the `etc` initialization script to sync symlinks and configs.
4. **Routing:**
   - `native` + `macos` -> **Native Stack (macOS)**
   - `native` + `linux` -> **Native Stack (Linux)**
   - `docker` -> **Docker Protocol**

## Native Stack Protocol (Unified macOS/Linux)
- **Unified Pathing:** Use `/var/run/brew_root` for all socket, config, and log paths. This is a symlink to `/` on Linux and the Homebrew root on macOS.
  - **PHP-FPM Socket:** `/var/run/brew_root/var/run/php-fpm@8.3.sock`
- **Service Management:**
  - **macOS:** Use `brew services [action] [service]` (e.g., `php@8.3`).
  - **Linux:** Use `sudo systemctl [action] [service]` (e.g., `php8.3-fpm`).
- **Commands:** Run `composer` and `vendor/bin/drush` directly.
- **Site URL:** `http://montreal2027.local:8080`.

## Docker Protocol
- **Execution:** Prefix all commands with `docker-compose exec web`.
- **Database:** Internal network (Host: `db`, Port: 3306).
- **Paths:** App root is `/var/www/html`. Ignore all host-specific symlinks like `/var/run/brew_root`.

## Core Drupal 11 & Coding Standards
- **PHP:** 8.3+ with `declare(strict_types=1);` and Constructor Property Promotion.
- **Dependency Injection:** Constructor-based only. No `\Drupal::service()`.
- **Plugin Discovery:** Use PHP 8 Attributes (No Annotations).
- **Theming:** Use Single Directory Components (SDC). Wrap UI strings in `$this->t()`.
- **Config:** Prioritize "Recipe-ready" configuration and `config/sync` management.
- **Standard:** Follow PSR-12 and Drupal Coding Standards.
- **Formatting:** 2-space indentation.


## Specific Patterns
- **Hooks:** Only use hooks if an Event Subscriber or Service Decorator is not available. 
- **Entity API:** Use `EntityTypeManagerInterface`. Avoid deprecated `entity_load()`.
- **Caching:** Implement Cacheability Metadata (`tags`, `contexts`, `max-age`) and `CacheableResponseInterface`.

## Project Structure
- `web/` - Document root.
- `web/modules/custom/` - Custom modules.
- `web/themes/custom/` - Custom themes.
- `config/sync/` - Configuration export directory.