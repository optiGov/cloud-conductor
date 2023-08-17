# Cloud-Conductor

A UI for self-hosting docker containers. Focused on security and usability. CI/CD Support. Built for Ubuntu servers. Uses Ansible as backend and can be extended with playbooks.

## Installation

Clone this repository and follow these steps to install the Conductor.

### 1. Environment File

Copy the environment file and update the values to match your environment.

```bash
cp .env.example .env
```

### 2. Composer

Install the composer dependencies.

```bash
composer install
```

### 3. Migrate Database

Run the database migrations.

```bash
php artisan migrate
```
