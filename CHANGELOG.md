# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2025-01-03

### Added
- Initial release of Podcast Block
- Support for fetching podcasts from multiple platforms:
  - Spotify show pages
  - Overcast profiles
  - Apple Podcasts shows
  - RSS feeds directly
  - Acast, Castos, Libsyn, Transistor, and Pocket Casts (basic support)
- WordPress Gutenberg block with InnerBlocks integration
- Automatic population with WordPress Audio blocks
- REST API endpoint for secure podcast fetching (`/wp-json/podcast-block/v1/fetch-podcasts`)
- Clean, responsive user interface in the block editor
- Platform selection dropdown
- URL input field with validation
- Error handling and user feedback
- Placeholder podcast data for demonstration
- WordPress 6.7+ block registration using `blocks-manifest.php`
- Support for WordPress 6.8 improved block registration APIs

### Technical Features
- Built with `@wordpress/scripts` for modern WordPress development
- Uses WordPress InnerBlocks for flexible content management
- Secure REST API with permission checks (`edit_posts` capability)
- RSS feed parsing with XML validation
- Multi-method podcast fetching (RSS detection, API fallbacks)
- Responsive CSS styling
- Clean separation of editor and frontend styles

### Development
- Modern WordPress block development setup
- npm scripts for building, linting, and development
- ESLint and Prettier configuration
- Hot reloading in development mode
- Plugin zip creation for distribution

## [Unreleased]

### Planned
- Enhanced API integrations for major platforms
- Caching system for podcast data
- Rate limiting for external API calls
- Comprehensive error handling
- Unit tests
- Internationalization (i18n) support
- WordPress.org plugin directory submission
- Performance optimizations
- More robust RSS feed parsing
- User preferences and settings
