# Montreal 2027 WorldCon Website

Official website for the 85th World Science Fiction Convention (Worldcon), taking place in Montreal, Quebec, Canada in 2027.

## About

This Drupal 11 website serves as the central hub for Montreal 2027 WorldCon, providing:

- Event information and schedules
- Guest of Honor biographies and photos
- Staff directory with contact functionality
- Hotel and venue information
- Membership registration and management
- News and updates for attendees

The site is built on Drupal 11 with a custom theme and includes specialized modules for convention management, including a staff contact form.

## Technology Stack

- **CMS**: Drupal 11.2+
- **PHP**: 8.3+
- **Database**: MariaDB 11.4
- **Web Server**: Nginx with PHP-FPM
- **Frontend**: Custom Sass/CSS, vanilla JavaScript
- **Search**: SearchAPI
- **Email**: PHPMailer with SMTP

## Getting Started with Docker

### Prerequisites

- Docker Desktop (or Docker Engine + Docker Compose) - see alternatives below
- Git

### Open Source Container Runtime Alternatives

While Docker Desktop is a convenient option, there are several open source alternatives available:

#### Linux

**Docker Engine (Open Source)**
- The original open source Docker runtime
- Install via your package manager or from docker.io
- Fully compatible with docker-compose
- **Installation**: 
  ```bash
  # Ubuntu/Debian
  sudo apt-get update
  sudo apt-get install docker.io docker-compose
  
  # Fedora/RHEL
  sudo dnf install docker docker-compose
  ```

**Podman**
- Daemonless container engine
- Drop-in replacement for Docker (alias docker=podman)
- Rootless containers by default
- **Installation**:
  ```bash
  # Ubuntu/Debian
  sudo apt-get install podman podman-compose
  
  # Fedora/RHEL
  sudo dnf install podman podman-compose
  ```
- **Usage**: Replace `docker` with `podman` and `docker-compose` with `podman-compose` in commands

#### macOS

**Colima**
- Lightweight container runtime with minimal resource usage
- Uses Lima VM underneath
- Compatible with docker-compose
- **Installation**:
  ```bash
  brew install colima docker docker-compose
  colima start
  ```

**Podman**
- Same benefits as Linux version
- Requires Podman Desktop or podman machine
- **Installation**:
  ```bash
  brew install podman podman-compose
  podman machine init
  podman machine start
  ```

**Rancher Desktop**
- Open source Kubernetes and container management
- Supports both dockerd and containerd
- GUI application with built-in docker-compose
- **Installation**: Download from [rancherdesktop.io](https://rancherdesktop.io)

#### Windows

**Podman**
- Windows native via WSL2
- **Installation**:
  ```powershell
  winget install RedHat.Podman
  podman machine init
  podman machine start
  ```

**Rancher Desktop**
- Same as macOS version
- Works with WSL2
- **Installation**: Download from [rancherdesktop.io](https://rancherdesktop.io)

**Docker Engine on WSL2** (Open Source)
- Install Docker Engine directly in WSL2 distribution
- Completely free and open source
- **Installation**: Follow Docker Engine installation for your WSL2 distro (Ubuntu/Debian)

> **Note**: When using alternatives, you may need to replace `docker` with `podman` or configure aliases. Most commands in this README work with any OCI-compliant container runtime.

### Initial Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/Montreal-2027/montreal2027.ca.git
   cd montreal2027.ca
   ```

2. **Environment configuration (optional)**
   
   The `docker-compose.yml` file includes sensible defaults for local development. A `.env` file is **only needed if you want to override these defaults**.
   
   **Default values:**
   - Web accessible at: `http://localhost:8080`
   - Database: `drupal` / `drupal` / `drupal`
   - Container names: `montreal2027-web`, `montreal2027-db`
   - PHP version: 8.3
   - MariaDB version: 11.4
   
   **To customize**, create a `.env` file with any of these variables:
   ```bash
   # Web server ports (default: 8080 -> 80)
   WEB_EXTERNAL_PORT=8080
   WEB_INTERNAL_PORT=80

   # Database connectivity (defaults: drupal/drupal/drupal)
   DB_HOST=db
   DB_PORT=3306
   DB_NAME=drupal
   DB_USER=drupal
   DB_PASSWORD=drupal
   DB_ROOT_PASSWORD=root

   # Container and volume names
   WEB_CONTAINER_NAME=montreal2027-web
   DB_CONTAINER_NAME=montreal2027-db
   DB_VOLUME_NAME=montreal2027_db_data
   FRONTEND_NETWORK_NAME=montreal2027_frontend
   BACKEND_NETWORK_NAME=montreal2027_backend
   
   # PHP/MariaDB versions
   PHP_VERSION=8.3
   MARIADB_VERSION=11.4

   # Hash Salt (change for production!)
   HASH_SALT=RandomHashSaltForDevelopmentPurposesOnly
   ```

3. **Build and start containers**
   ```bash
   docker-compose up -d --build
   ```

   This will:
   - Build the web container with PHP 8.3, Nginx, and all required extensions
   - Start a MariaDB 11.4 database container
   - Create persistent volumes for the database
   - Set up isolated networks for frontend and backend communication

4. **Install Drupal dependencies**
   ```bash
   docker-compose exec web composer install
   ```

5. **Import configuration**
   
   If you have a database dump:
   ```bash
   docker-compose exec -T web bash -c "$(drush sql:connect)" < your-database-dump.sql
   docker-compose exec web drush cr
   ```

   Or install Drupal from scratch and import config:
   ```bash
   docker-compose exec web drush site:install --existing-config -y
   docker-compose exec web drush cr
   ```

6. **Access the site**
   
   Open your browser to: `http://localhost:8080`

### Common Docker Commands

```bash
# View logs
docker-compose logs -f web
docker-compose logs -f db

# Stop containers
docker-compose stop

# Start containers
docker-compose start

# Restart containers
docker-compose restart

# Stop and remove containers (preserves database volume)
docker-compose down

# Stop and remove everything including volumes
docker-compose down -v

# Access web container shell
docker-compose exec web sh

# Access database (password: drupal)
docker-compose exec db mysql -u drupal -p drupal

# Run Drush commands
docker-compose exec web drush [command]

# Clear cache
docker-compose exec web drush cr

# Rebuild containers after Dockerfile changes
docker-compose up -d --build
```

### Development Workflow

1. **CSS Development**
   
   The theme uses Sass. For local development outside Docker:
   ```bash
   cd web/themes/custom/montreal2027
   npm install
   npm run watch
   ```

2. **Configuration Management**
   
   Export configuration changes:
   ```bash
   docker-compose exec web drush config:export
   ```

   Import configuration:
   ```bash
   docker-compose exec web drush config:import
   ```

3. **Database Backups**
   ```bash
   docker-compose exec web drush sql:dump --gzip > backup-$(date +%Y%m%d-%H%M%S).sql.gz
   ```

### Troubleshooting

**Port conflicts**: If port 8080 is already in use, change `WEB_EXTERNAL_PORT` in `.env`

**Permission issues**: The web container runs as the `nginx` user. If you encounter permission issues:
```bash
docker-compose exec web chown -R nginx:nginx /var/www/html/web/sites/default/files
```

**Database connection errors**: Ensure the `DB_HOST` in `.env` is set to `db` (the service name)

**Configuration not found**: Import the module configurations:
```bash
docker-compose exec web drush config:import --partial --source=modules/custom/montreal2027_tools/config/install
```

**Clear all caches**:
```bash
docker-compose exec web drush cr
```

## Project Structure

```
.
├── config/sync/                 # Drupal configuration
├── docker/                      # Docker configuration files
│   └── web/                     # Web container configs (Nginx, PHP-FPM, etc.)
├── scripts/                     # Build and deployment scripts
├── web/                         # Drupal web root
│   ├── modules/custom/          # Custom modules
│   │   └── montreal2027_tools/  # Staff contact & utilities
│   ├── themes/custom/           # Custom themes
│   │   └── montreal2027/        # Main site theme
│   └── sites/default/           # Site-specific settings
├── .env                         # Environment variables (not in git)
├── composer.json                # PHP dependencies
└── docker-compose.yml           # Docker service definitions
```

## Custom Modules

### montreal2027_tools

Provides utilities for convention management:
- **Staff Directory**: Hierarchical taxonomy-based staff listing with contact forms
- **Contact Import**: Drush command for importing contacts from CSV
- **AJAX Contact Forms**: Modal contact forms with email validation and spam protection

## Contributing

1. Create a feature branch from `develop`. Naming convention is `feature/[short_token]`
2. Make your changes
3. Test thoroughly in the Docker environment
4. Commit with descriptive messages
5. Push to your branch.  This will auto-deploy to https://dev.montreal2027.ca. Test throughly
6. Create a pull request for your feature branch against `develop`.  Assign the PR to yourself. Add meaningful labels. Add the PR to the `Website` project, and link it to the issue under `Development`

## License

Copyright © 2025, CanSMOF. All rights reserved.

## Support

For questions or issues, contact the Technology & Production Division via the staff directory on the website.
