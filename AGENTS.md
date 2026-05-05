# Montreal 2027 WorldCon Website

## Overview

Drupal 11 website for the 85th World Science Fiction Convention (Montreal, 2027). See [README.md](README.md) for detailed setup and Docker alternatives.

## Quick Start

**Docker Development:**
```bash
docker-compose up -d --build
docker-compose exec web composer install
docker-compose exec web drush site:install --existing-config
docker-compose exec web drush cr
# Site: http://localhost:8080
```

**Local Development:**
```bash
composer install
drush site:install --existing-config
drush cr
# Site: Configure web server to point to web/ directory
```

**Theme CSS (both environments):**
```bash
cd web/themes/custom/montreal2027
npm install
npm run watch:css  # or npm run build:css for one-time compile
```

## Technology Stack

- **CMS:** Drupal 11.2+ (config-managed via `config/sync/`)
- **PHP:** 8.3+ (see [docker/web/Dockerfile](docker/web/Dockerfile))
- **Database:** MariaDB 11.4
- **Container Runtime:** Docker (or Podman/Colima/Rancher - see README alternatives)
- **Theme Build:** Dart Sass (no bundler)
- **Package Management:** Composer + npm

## Project Structure

```
config/sync/           # Drupal configuration (export/import)
docker/web/           # Web container Dockerfile
patches/              # Composer patches for contrib modules
scripts/sync.sh       # Production-to-local sync script
web/
  modules/custom/montreal2027_tools/  # Staff directory, CSV import
  themes/custom/montreal2027/         # Custom subtheme
    src/scss/         # SCSS source (edit here)
    css/              # Compiled CSS (generated)
    templates/        # Twig overrides (organized by entity type)
```

## Common Commands

**Drupal/Drush:**
```bash
# Docker environment - prefix commands with: docker-compose exec web
docker-compose exec web drush cr                  # Clear cache
docker-compose exec web drush cex                 # Export config
docker-compose exec web drush cim                 # Import config
docker-compose exec web drush updb                # Run database updates
docker-compose exec web drush uli                 # Generate login link
docker-compose exec web drush sql:dump > dump.sql # Database backup

# Local environment - run drush directly
drush cr                  # Clear cache
drush cex                 # Export config
drush cim                 # Import config
drush updb                # Run database updates
drush uli                 # Generate login link
drush sql:dump > dump.sql # Database backup
```

**Docker-Specific:**
```bash
docker-compose logs -f web             # View logs
docker-compose exec web sh             # Shell access
docker-compose down                    # Stop (keeps DB volume)
docker-compose down -v                 # Stop and delete volumes
```

**Custom Module Commands:**
```bash
# Docker: docker-compose exec web drush montreal2027:import-contacts path/to/file.csv
# Local: drush montreal2027:import-contacts path/to/file.csv
```

## Development Conventions

### Configuration Management

- **Always export config:** Run `drush cex` after config changes
- **Commit config:** All `config/sync/*.yml` files are version-controlled
- **Install from config:** Use `drush site:install --existing-config` for fresh installs
- **Patches:** Store in `patches/` and reference in `composer.json` "extra.patches"

### Custom Theme (`montreal2027`)

**Base:** Subtheme of `clean_blog_theme` (Bootstrap-based)  
**Working Directory:** `web/themes/custom/montreal2027`

**SCSS Architecture:**
- **Entry Point:** `src/scss/montreal2027.scss` imports partials
- **Partials:** `_root.scss` (CSS custom properties), `_article.scss`, `_block.scss`, `_fonts.scss`, `_grid.scss`, `_navigation.scss`, `_page.scss`, `_paragraph.scss`, `_region.scss`, `_search.scss`, `_social_media.scss`, `_staff.scss`, `_views.scss`, `_mixins.scss`
- **Modern Sass:** Uses `@use` (not `@import`)
- **Output:** `css/montreal2027.min.css` (minified with source maps)
- **CSS Layers:** Cascade layers manage specificity (`external`, `clean_blog_theme`, `layout`, `theme`, `views-responsive-grid`)

**CSS Conventions:**
- **Custom Properties:** Extensive use in `_root.scss` (colors use `oklch()`, layout variables, breakpoints)
- **Naming:** BEM-like classes (`paragraph--guest-type`, `paragraph--guest-list`)
- **Modern Features:** Container queries, `clamp()` for fluid typography, `dvh` units
- **Mixins:** `drop-shadow-slight()`, `frosted-glass()`, `noto-font()`, `button` (see `_mixins.scss`)

**Template Conventions:**
- **Organization:** Templates in subdirectories by entity type (`block/`, `content/`, `field/`, `form/`, `menu/`, `paragraph/`)
- **Naming:** `entity-type--bundle--view-mode.html.twig`
- **Helper Function:** `get_image_info()` in `montreal2027.theme` extracts image alt text + styled URLs

**Build CSS:**
```bash
cd web/themes/custom/montreal2027
npm run watch:css   # Development (watch mode)
npm run build:css   # Production (one-time compile)
```

### Custom Module (`montreal2027_tools`)

**Purpose:** Staff directory, contact forms, CSV import utilities

**Key Components:**
- **Controllers:** `StaffPageController` (/staff page), `StaffContactController` (/staff/contact-submit)
- **Drush Commands:** `montreal2027:import-contacts` (alias: `m2027:ic`) - CSV import with dry-run mode
- **Forms:** Admin settings at `/admin/config/system/staff-page-settings`
- **Hooks:** Alters "divisions" taxonomy forms, handles staff contact emails
- **Template:** `montreal2027-staff-page.html.twig`

**Conventions:**
- Dependency injection in services/commands
- Type hints throughout (`string`, `array`, `void`)
- TranslatableMarkup for UI strings
- Consistent `montreal2027_tools` prefix (namespace, routes, config)

## Code Style

**PHP:**
- **Indentation:** 2 spaces (see [.editorconfig](.editorconfig))
- **Line Endings:** LF
- **composer.json:** 4-space indent
- **Standards:** Drupal coding standards (enforced via `.php-cs-fixer.php`)

**Twig:**
- Follow Drupal template naming conventions
- Use `{{ get_image_info() }}` helper for image fields

**SCSS:**
- Component-based partials (one file per concern)
- Use theme mixins for consistency
- Prefer CSS custom properties over hardcoded values

## Deployment

**CI/CD:** GitHub Actions (`.github/workflows/deploy.yml`)
- Triggers on PR merge to `develop` or push to `feature/**` branches
- Steps: Checkout code → Backup DB → `composer install` → `npm install` → `npm run build:css` → Drush updates → Clear cache
- Deploys to staging environment

**Deployment Flow:**
1. Code pushed/merged
2. GitHub Actions runs deployment
3. Database backed up (`/var/www/db_backups/`)
4. Dependencies installed
5. Assets built
6. Config imported (`drush cim`)
7. Database updates (`drush updb`)
8. Cache cleared

## Git Hooks

**Pre-Commit Validation:** Automatic check to ensure Drupal config is exported before commits
- **Script:** [.github/hooks/pre-commit-config-check.sh](.github/hooks/pre-commit-config-check.sh)
- **Setup:** Already installed (symlinked to `.git/hooks/pre-commit`)
- **Manual test:** `./.github/hooks/pre-commit-config-check.sh`
- **Bypass (emergency only):** `git commit --no-verify`

The hook blocks commits if config changes haven't been exported via `drush cex`.

## Common Pitfalls

- **Don't edit compiled CSS:** Always edit `src/scss/*.scss`, not `css/*.css`
- **Config sync required:** After entity/field changes, run `drush cex` before committing (pre-commit hook enforces this)
- **File permissions:** If permission errors occur, run `chmod -R u+w web/sites/default`
- **Environment detection:** Pre-commit hook auto-detects Docker vs local - ensure containers are running if using Docker
- **Node modules in deployment:** Deployment workflow removes `node_modules` after CSS build to reduce footprint
- **Workspace module:** Custom patches applied (see `patches/` directory)
- **Port conflicts (Docker):** Default is `8080:80` - change `WEB_EXTERNAL_PORT` in `.env` if needed

## Documentation

- **Setup Guide:** [README.md](README.md) (includes Docker alternatives)
- **Changelog:** [CHANGELOG.md](CHANGELOG.md) (version history)
- **Drush Docs:** `vendor/drush/drush/docs/`
- **Theme Parent:** [clean_blog_theme](https://github.com/omscllc/clean_blog_theme)
- **Drupal Docs:** [drupal.org/docs](https://www.drupal.org/docs/user_guide/en/index.html)

## Testing Changes

1. **Code Changes:** Make changes → `drush cr` (clear cache) → Test in browser
2. **CSS Changes:** `npm run watch:css` auto-rebuilds on save
3. **Config Changes:** `drush cex` → Commit YAML files → `drush cim` on other environments
4. **Database:** Regular backups via `drush sql:dump` before major changes

**Note:** For Docker, prefix drush commands with `docker-compose exec web`

## Key Files for AI Agents

- **Entry Points:** [web/index.php](web/index.php), [composer.json](composer.json)
- **Theme:** [web/themes/custom/montreal2027/montreal2027.info.yml](web/themes/custom/montreal2027/montreal2027.info.yml), [web/themes/custom/montreal2027/src/scss/montreal2027.scss](web/themes/custom/montreal2027/src/scss/montreal2027.scss)
- **Module:** [web/modules/custom/montreal2027_tools/montreal2027_tools.info.yml](web/modules/custom/montreal2027_tools/montreal2027_tools.info.yml)
- **Docker:** [docker-compose.yml](docker-compose.yml), [docker/web/Dockerfile](docker/web/Dockerfile)
- **Config:** [config/sync/](config/sync/) (all site configuration)
