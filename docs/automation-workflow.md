# AI Automation Workflow (Laravel)

## What this feature does
- Runs one automated Facebook Page post per day.
- Pipeline: Trigger -> AI Caption -> AI Image -> Facebook Post -> Log.
- Supports manual admin run, enable/disable, and attempt logging.

## Environment variables
Add to `.env`:

```env
AI_CAPTION_API_URL=
AI_CAPTION_API_KEY=
AI_CAPTION_MODEL=gpt-4o-mini

AI_IMAGE_API_URL=
AI_IMAGE_API_KEY=
AI_IMAGE_SIZE=1024x1024

FACEBOOK_PAGE_ID=
FACEBOOK_PAGE_ACCESS_TOKEN=
```

## Laravel setup
```bash
php artisan migrate
php artisan storage:link
php artisan optimize:clear
```

## Manual execution
```bash
php artisan automation:run-workflow --trigger=manual
```

## Cron example (Ubuntu)
Add this to crontab (`crontab -e`):

```cron
* * * * * cd /var/www/your-project && php artisan schedule:run >> /dev/null 2>&1
```

Scheduler is configured in `routes/console.php` to run the workflow daily at `09:00` server time.

## Safety controls implemented
- One successful Facebook post per day (`posts` table date check).
- Distributed lock to prevent duplicate concurrent runs (`Cache::lock`).
- Manual + cron runs share the same locked service path.
- Every attempt is logged in `posts` with status and timestamp.
