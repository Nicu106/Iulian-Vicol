## Framework and routes
- Framework: Laravel (Blade views, Controllers, Eloquent)
- Routes: `/`, `/despre` (alias about), `/contact`, `/faq`, `/terms`, `/vehicles`, `/catalog` (alias listings), `/vehicles/{vehicle}`, `/vehicles-ajax`, `POST /contact`

## Where global styles were applied
- Previously: `public/css/app.css` mixed tokens, base, components, and page styles globally.
- Now: minimal globals split into `public/css/reset.css`, `public/css/tokens.css`, `public/css/base.css`. Shared components loaded via `public/css/components/*.css`. Per-page CSS loaded via Blade `@stack('styles')`.

## CSS risks found
- Global generic classes: `.btn`, `.card`, `.section`, etc., defined globally and reused across pages.
- Duplicate page styles across `public/css/app.css`, `public/css/base.css`, and `public/css/pages/*`.
- Page styles in global file caused cross-page leakage.

## What changed (high-level)
- Layout updated to load only reset + tokens + base + shared component CSS, and accept per-page styles via `@stack('styles')`.
- Added `@section('page-class', 'page-…')` so page CSS can scope via `body.page-…`.
- Listings, Detail, Contact, Home now push their own CSS: `public/css/pages/{catalog,vehicle-show,contact,home}.css`. About pushes `public/css/pages/about.css`.
- Filters extended: support `brands[]`, price/year ranges, fuel, transmission, color, type, keyword `q`, and `sort` (newest/oldest/price_asc/price_desc). Pagination preserves query.
- AJAX endpoint returns a safe Blade partial `resources/views/vehicles/partials/grid.blade.php`.
- Added basic smoke tests to ensure key routes render.

## Files changed/added
- Updated: `resources/views/layouts/app.blade.php`
- Updated: `resources/views/vehicles/index.blade.php`
- Updated: `resources/views/vehicles/show.blade.php`
- Updated: `resources/views/contact.blade.php`
- Updated: `resources/views/home.blade.php`
- Updated: `app/Http/Controllers/VehicleController.php`
- Added: `public/css/reset.css`, `public/css/tokens.css`
- Added: `public/css/pages/vehicle-show.css`, `public/css/pages/contact.css`, `public/css/pages/about.css`
- Added: `resources/views/vehicles/partials/grid.blade.php`
- Added: `tests/Feature/SmokeTest.php`

## Residual risks / next steps
- `public/css/base.css` still contains generic utility and some page-like styles. Next: move component/page-specific rules into pages/components CSS; keep base to only element defaults.
- Add Stylelint/ESLint + Husky pre-commit hooks to block global class additions in base/reset and to disallow importing globals in components (planned next commit).
- Consider consolidating duplicated `home`/`catalog` styles and removing `public/css/app.css` after audit.

## How to verify
- Edit `public/css/pages/contact.css` (e.g., change `.card-title` color) and observe that only contact page changes; home/listings remain stable.
- Run tests: `php artisan test`.
- Build assets (optional): `npm run build` (Vite present; CSS is served from `public/css`).

