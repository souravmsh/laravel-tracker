# LaravelTracker

A robust Laravel package for tracking referrals and visitors with advanced analytics, widgets, and a dashboard. This package allows you to track referral codes, UTM parameters, visitor data, and provides a user-friendly interface to visualize referral performance.

## Features

- **Referral Tracking**: Track referral codes, UTM parameters, IP addresses, and user agents.
- **Visitor Tracking**: Log unique visitors with session and cookie-based persistence.
- **Dashboard**: Visualize referral performance with a Chart.js bar chart showing total and unique visitors.
- **Widgets**: Reusable Blade components for displaying visitor and referral lists.
- **API Support**: RESTful API for retrieving referral data and statistics.
- **Scalability**: Uses Redis for caching and rate limiting, and Laravel queues for asynchronous logging.
- **Security**: Input sanitization, rate limiting, and optional CAPTCHA support.
- **Real-Time Analytics**: Event broadcasting for live updates.
- **Extensibility**: Events and listeners for custom integrations.
- **Easy Installation**: Use `php artisan tracker:install` to set up the package.

## Requirements

- PHP >= 8.1
- Laravel >= 9.0
- Redis (optional, for caching and rate limiting)
- Composer
- Tailwind CSS (for widget styling)
- Chart.js (for dashboard visualization)

## Installation

1. **Install the Package**

   Install the package via Composer:

   ```bash
   composer require souravmsh/tracker
   ```

2. **Run the Install Command**

   Run the following Artisan command to publish assets and run migrations:

   ```bash
   php artisan tracker:install
   ```

   This command will:
   - Publish the configuration to `config/referral.php`.
   - Publish migrations to `database/migrations`.
   - Publish views to `resources/views/vendor/tracker`.
   - Publish assets to `public/vendor/tracker`.
   - Run migrations to create the `referrals` and `tracker_logs` tables.

3. **Configure Environment**

   Add the following to your `.env` file to customize the package behavior:

   ```env
   TRACKER_TRACKING_ENABLED=true
   TRACKER_QUEUE_ENABLED=true
   TRACKER_LOG_TO_DATABASE=true
   TRACKER_RATE_LIMIT=5
   TRACKER_SESSION_LIFETIME=1440
   TRACKER_GOOGLE_ANALYTICS=false
   ```

   Ensure Redis is configured if `TRACKER_CACHE_ENABLED=true`.

4. **Optional: Configure Horizon**

   For queue monitoring, install and configure Laravel Horizon:

   ```bash
   composer require laravel/horizon
   php artisan horizon:install
   php artisan horizon
   ```

5. **Optional: Configure Scout**

   For full-text search on referral codes, install Laravel Scout:

   ```bash
   composer require laravel/scout
   ```

   Configure Scout with Algolia or Meilisearch in `config/scout.php`.
 
## Usage

### Tracking Referrals

The package automatically tracks referrals via the `TrackerMiddleware`, which is applied to all `web` routes. To trigger tracking, include a referral code or UTM parameters in the URL:

```
http://your-app.com?ref=ABC123&utm_source=google&utm_medium=cpc&utm_campaign=spring_sale
```

This will:
- Log the visitor's ID, referral code, UTM parameters, IP address, and user agent.
- Store data in the session and a cookie for persistence.
- Optionally log to the database (synchronously or via queues).
- Fire a `TrackerEvent` event for real-time analytics.

### Viewing the Dashboard

Access the dashboard to visualize referral performance:

```
http://your-app.com/tracker/dashboard
```

The dashboard includes:
- A **Visitors Widget** showing visitor details (ID, referral code, IP, visits, etc.).
- A **Referral List Widget** listing all referrals with visit counts.
- A **Chart.js Bar Chart** comparing total and unique visitors per referral code.

### Viewing Widgets

- **Visitors Widget**:
  ```
  http://your-app.com/tracker/visitors
  ```
  Filter by referral code and date range, with pagination.

- **Referral List Widget**:
  ```
  http://your-app.com/tracker/list
  ```
  Filter by title and status, with pagination.

### Using the API

Retrieve referral data and statistics via the API:

- **List Referrals**:
  ```
  curl http://your-app.com/api/referrals
  ```
  Supports `status` and `search` query parameters.

- **Get Statistics**:
  ```
  curl http://your-app.com/api/referrals/stats?date_from=2025-01-01&date_to=2025-05-25
  ```
  Returns visits and unique visitors per referral code.

### Embedding Widgets

Use the Blade components in your own views:

```blade
<x-tracker-visitors :visitors="$visitors" />
<x-tracker-list :referrals="$referrals" />
```

Pass the `$visitors` and `$referrals` variables from your controller.

### Customizing Views

Edit the published views in `resources/views/vendor/tracker` to customize the dashboard and widgets. The views use Tailwind CSS for styling.

### Extending Analytics

To integrate with analytics services (e.g., Google Analytics, Mixpanel), implement the `sendToAnalytics` method in `TrackerService`. Enable in `config/referral.php`:

```php
'analytics' => [
    'google' => env('TRACKER_GOOGLE_ANALYTICS', true),
    'mixpanel' => env('TRACKER_MIXPANEL', false),
],
```

### Real-Time Analytics

Listen for the `TrackerEvent` event to process referral data in real-time (e.g., for live dashboards):

```php
use Souravmsh\LaravelTracker\Events\TrackerEvent;

Event::listen(function (TrackerEvent $event) {
    // Process $event->referralData
});
```

## Configuration

The configuration file (`config/referral.php`) allows customization of:

- `enabled`: Enable/disable referral tracking.
- `log_to_database`: Log referrals to the database.
- `rate_limit`: Maximum tracking requests per IP per minute.
- `session_lifetime`: Session duration for tracking (minutes).
- `cookie_l