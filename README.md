# School ERP Admin Panel

Laravel 11 multi-tenant admin panel for School ERP SaaS platform.

## Tech Stack

- **Framework:** Laravel 11
- **UI:** Livewire 3, Filament 3, Tailwind CSS
- **Auth:** Laravel built-in auth with remember me
- **Permissions:** Spatie Laravel Permission v6
- **Backend API:** NestJS (proxied via API routes)

## Architecture

### Multi-Tenancy
- School-scoped data isolation via `school_id` on all models
- `SchoolTenantMiddleware` automatically scopes queries per session
- `BelongsToSchool` trait provides global scope for Eloquent models

### Roles
| Role | Scope | Description |
|------|-------|-------------|
| `super-admin` | Platform | Manages all schools, subscriptions, revenue |
| `school-admin` | School | Full access within their school |
| `sub-admin` | School | Granular permissions (configurable) |
| `incharge` | Class | Class incharge teacher (limited scope) |

### Route Groups
- `/login` - Authentication
- `/super-admin/*` - Platform administration
- `/admin/*` - School-scoped administration
- `/api/*` - NestJS backend proxy (Sanctum protected)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install && npm run build
php artisan serve
```

## Default Super Admin
- Email: `superadmin@schoolerp.com`
- Password: Set via `SUPER_ADMIN_PASSWORD` in `.env`

## Key Features
- Student & Teacher CRUD with import/export
- Attendance marking for students AND staff (from admin panel)
- Fee structure, invoice generation, payment recording
- Exam scheduling, marks entry, report cards
- Class incharge system (teacher assigned to a class)
- Sub-admin with granular per-module permissions
- Platform revenue tracking for super admin
