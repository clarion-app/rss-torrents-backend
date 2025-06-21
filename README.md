# RSS Torrents Backend

A Laravel-based backend service for monitoring and managing RSS torrent feeds. This package provides automated RSS feed parsing, torrent discovery, and series/episode management capabilities for the Clarion App ecosystem.

## Features

- **RSS Feed Monitoring**: Automated parsing of torrent RSS feeds (EZTV, Nyaa Subsplease)
- **Series Management**: Track and manage TV series with subscription capabilities
- **Episode Tracking**: Monitor individual episodes with metadata (resolution, size, magnet links)
- **Scheduled Processing**: Automatic feed checking every minute via Laravel scheduler
- **RESTful API**: Full CRUD operations for series and episodes

## Supported Feed Sources

- **EZTV** (`https://eztv.re/ezrss.xml`) - TV series torrents
- **Nyaa Subsplease** - Anime series torrents


## Configuration

The package automatically registers:
- Database migrations for series and episodes
- API routes under the configured route prefix
- Scheduled command for feed checking
- Console commands

## API Endpoints

All endpoints require authentication (`auth:api` middleware).

### Feeds
- `GET /feeds/urls` - Get list of available RSS feed URLs
- `GET /feeds/torrents` - Retrieve torrents from all feeds

### Series Management
- `GET /series` - List all series
- `POST /series` - Create a new series
- `GET /series/{id}` - Get specific series details
- `PUT /series/{id}` - Update series information
- `DELETE /series/{id}` - Delete a series
- `GET /series-subscribed` - Get subscribed series only
- `PATCH /series/{id}/toggle-subscription` - Toggle series subscription
- `PATCH /series/bulk-subscription` - Bulk update subscriptions

## Console Commands

### Check Feeds
```bash
php artisan feeds:check
```

Manually trigger RSS feed checking and database updates.

## Database Schema

### Series Table (`rss_torrents_series`)
- `id` - Primary key
- `name` - Series name (normalized)
- `title` - Display title
- `feed_url` - RSS feed URL
- `subscribed` - Subscription status (boolean)
- `created_at`, `updated_at` - Timestamps
- `deleted_at` - Soft delete timestamp

### Episodes Table (`rss_torrents_episodes`)
- `id` - Primary key
- `series_id` - Foreign key to series
- `episode_identifier` - Episode identifier (S01E01, date, etc.)
- `title` - Episode title
- `resolution` - Video resolution (SD, 720p, 1080p)
- `size` - File size
- `magnet_url` - Magnet link for download
- `created_at`, `updated_at` - Timestamps

## Architecture

### Core Classes

- **`RssTorrentsServiceProvider`** - Laravel service provider for package registration
- **`RssParser`** - Main RSS parsing coordinator
- **`Rss`** - Base RSS feed handler
- **`EZTV`** / **`NyaaSubsplease`** - Feed-specific parsers
- **`Series`** / **`Episode`** - Eloquent models with multi-chain support
- **`CheckFeeds`** - Console command for feed processing

### Feed Processing Flow

1. Scheduler runs `feeds:check` command every minute
2. `RssParser` retrieves feed URLs and delegates to appropriate parsers
3. Feed-specific parsers extract torrent information
4. Series and episodes are created/updated in database
5. API endpoints provide access to processed data

## Development

### Project Structure

```
src/
├── Commands/
│   └── CheckFeeds.php          # Console command for feed checking
├── Controllers/
│   ├── FeedsController.php     # Feed-related API endpoints
│   └── SeriesController.php    # Series management endpoints
├── Feeds/
│   ├── EZTV.php               # EZTV feed parser
│   └── NyaaSubsplease.php     # Nyaa feed parser
├── Models/
│   ├── Episode.php            # Episode model
│   └── Series.php             # Series model
├── HttpApiCall.php            # HTTP client wrapper
├── Rss.php                    # Base RSS feed class
├── RssParser.php              # Main RSS parsing coordinator
└── RssTorrentsServiceProvider.php  # Laravel service provider
```

### Adding New Feed Sources

1. Create a new class extending `Rss` in the `Feeds/` directory
2. Implement the `getTorrents()` method
3. Add the feed URL to the `$valid_feeds` static property
4. Register in `RssParser::getURLs()`

## License

MIT License

## Author

**Tim Schwartz**  
Email: tim@metaverse.systems

## Contributing

This package is part of the Clarion App ecosystem. Please follow the project's contribution guidelines when submitting changes.