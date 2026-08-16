# CanteenMenu

A web application for managing and publishing a canteen's daily menu.

**Live application:** https://app.canteenmenu.be

---

## Purpose

Most canteens still communicate their menu through a printed sheet taped to a wall or a photo shared in a group chat. Both go stale immediately, neither is searchable, and neither tells you whether a dish is vegetarian or contains nuts for example.

CanteenMenu replaces that. Kitchen staff plan menus per day from a central dish catalogue, publish them when ready, and visitors see what is being served this week. There is also a filter ability by dietary preference, with prices and allergen labels on every dish.

## Features

### For visitors (no account needed)

- **Weekly menu** - Monday to Friday, dishes grouped by course, with prices and dietary labels
- **Week navigation** - previous/next buttons and a date picker, the selected week lives in the URL and is shareable
- **PDF export** - download any week as a printable A4 landscape sheet
- **Dish catalogue** - browse all dishes with photos, descriptions and prices
- **Search and multi-filter** - free-text search combined with category, dietary tags, maximum price and sort order, all applied simultaneously and all reflected in the URL
- **Dish detail pages** - full description, dietary information, upcoming menus the dish appears on, and related dishes
- **Light and dark theme**

### For registered users

Everything above, plus:

- **Favorites** - save dishes with one click and find them again on a dedicated page

### For administrators

- **Dashboard** - key figures, a two-week menu planning overview flagging missing or unpublished days, a dishes-per-category chart, and recent activity
- **Dish management** - full CRUD with category assignment, dietary tag multi-select, image upload with automatic resizing, and an availability toggle
- **Menu planner** - assign dishes to a specific day and course, reorder them, override the price for that day only, and copy another day's menu as a starting point
- **Category and dietary tag management** - full CRUD, with automatic reordering and a live badge preview for tags
- **User management** - full CRUD with admin and active toggles, protected against an administrator locking themselves out
- **Draft workflow** - plan menus ahead, only published menus are visible to visitors

## Technology

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Frontend | Livewire 4, Alpine.js, Tailwind CSS v4 |
| UI components | Flux UI (free tier) |
| Authentication | Laravel Fortify (Livewire starter kit) |
| Database | SQLite |
| Image processing | Intervention Image |
| PDF generation | barryvdh/laravel-dompdf |

## Setup

### Requirements

- PHP 8.3
- Composer
- Node.js 20 or higher, with npm

### Installation

**1. Clone the repository and enter the folder**

```bash
git clone https://github.com/itfactory-tm/2acs-2025-2026-webdev-personal-project-renzoschreppers.git
cd 2acs-2025-2026-webdev-personal-project-renzoschreppers
```

**2. Install dependencies**

```bash
composer install
npm install
```

**3. Create the environment file**

On macOS, Linux or Git Bash:

```bash
cp .env.example .env
```

On Windows PowerShell or CMD:

```
copy .env.example .env
```

**4. Generate the application key**

```bash
php artisan key:generate
```

**5. Create and fill the database**

```bash
php artisan migrate --seed
```

The project uses SQLite. If Artisan asks whether to create the database file, answer **yes**.

**6. Link the storage folder**

```bash
php artisan storage:link
```

This is required for dish images to be visible. On Windows this command needs Developer Mode enabled, or a terminal running with administrator rights.

**7. Build the frontend assets**

```bash
npm run build
```

### Running the application

```bash
php artisan serve
```

Then open **http://localhost:8000**.

If you use Laravel Herd and the project folder is parked, the application is also reachable at `<folder-name>.test` without running `php artisan serve`.

For development with hot reloading, run `npm run dev` in a second terminal instead of `npm run build`.

### Resetting the data

```bash
php artisan migrate:fresh --seed
```

This rebuilds the database and restores all dish images from `database/seeders/images/`, so the application always returns to a complete, demonstrable state.

## Test accounts

All accounts use the password **`user1234`**.

| Email | Name | Role | Status |
|---|---|---|---|
| `admin@canteen.test` | Anna Peeters | Administrator | Active |
| `manager@canteen.test` | Tom Willems | Administrator | Active |
| `sofie@canteen.test` | Sofie Janssens | User | Active |
| `lucas@canteen.test` | Lucas De Smet | User | Active |
| `emma@canteen.test` | Emma Claes | User | Active |
| `jonas@canteen.test` | Jonas Maes | User | **Inactive** |

Jonas is deliberately deactivated. Logging in as him demonstrates the `ActiveUser` middleware: authentication succeeds, then the session is terminated and a 403 is returned.

These accounts exist on both the local seeded database and the live application.

## Seeded data

- 6 categories, 6 dietary tags
- 49 dishes, each with a photo, description, price and dietary labels
- 30 weekday menus covering six weeks from the current Monday - the first two weeks published, the remaining four left as drafts
- Friday menus include a discounted main course, demonstrating the per-day price override

Menus are seeded relative to today's date, so the current week always has content.

## Data model

| Model | Key relationships |
|---|---|
| `Category` | one-to-many → `Dish` |
| `Dish` | many-to-one → `Category`; many-to-many ↔ `Menu`, `DietaryTag`, `User` |
| `Menu` | many-to-many ↔ `Dish` |
| `DietaryTag` | many-to-many ↔ `Dish` |
| `User` | many-to-many ↔ `Dish` (favorites) |

Three pivot tables:

- **`dish_menu`** — carries `course`, `sort_order` and `price_override`, so a dish can appear on different days in different courses at different prices
- **`dietary_tag_dish`** — a plain link table
- **`dish_user`** — favorites, timestamped so they can be ordered by when they were saved

## Project structure

### Full-page Livewire components (`app/Livewire/`)

| Component | Route |
|---|---|
| `Home` | `/` |
| `WeekMenu` | `/menu` |
| `DishBrowser` | `/dishes` |
| `DishDetail` | `/dishes/{dish}` |
| `Favorites` | `/favorites` |
| `Admin\Dashboard` | `/admin` |
| `Admin\Categories` | `/admin/categories` |
| `Admin\DietaryTags` | `/admin/dietary-tags` |
| `Admin\Dishes` | `/admin/dishes` |
| `Admin\Menus` | `/admin/menus` |
| `Admin\MenuPlanner` | `/admin/menus/{menu}/edit` |
| `Admin\Users` | `/admin/users` |

### Reusable Blade components (`resources/views/components/cm/`)

| Component | Purpose |
|---|---|
| `dietary-badge` | Renders a dietary tag with its own icon and color, in full or compact form |
| `dish-card` | Dish tile with image, price, tags and favorite button |
| `empty-state` | Consistent "nothing here" panel used across every list |
| `error-summary` | Collects all validation errors at the top of a form |
| `stat-card` | Dashboard figure with label, icon and hint |
| `tag-legend` | Explains what the dietary badges mean |

`FavoriteButton` is a nested Livewire component reused on the dish cards, the detail page and the favorites page.

### Form objects (`app/Livewire/Forms/`)

`CategoryForm`, `DietaryTagForm`, `DishForm`, `MenuForm`, `UserForm` - each holding its own properties, validation rules and persistence logic.

### Middleware (`app/Http/Middleware/`)

- **`Admin`** - restricts the admin area, returns 403 for non-administrators
- **`ActiveUser`** - logs out and blocks deactivated accounts

Applied in order as `['auth', ActiveUser::class, Admin::class]`, so a guest is redirected to login before any status or permission check runs.

## Deployment

The application is hosted on Combell at https://app.canteenmenu.be with a Let's Encrypt certificate and forced HTTPS. The document root points at `public/`, keeping the application code, configuration and database outside the web root. Mail is configured against a real SMTP mailbox, so password reset emails are delivered.

## Author

Schreppers Renzo - 2APPAI, Web Development, Thomas More
