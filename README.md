# Podcast Block

[![License: GPL v2 or later](https://img.shields.io/badge/License-GPL%20v2%20or%20later-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress Plugin](https://img.shields.io/badge/WordPress-6.7%2B-blue.svg)](https://wordpress.org/)

A WordPress Gutenberg block that fetches and displays podcasts from various platforms including Spotify, Overcast, Apple Podcasts, and RSS feeds. Automatically populates with WordPress Audio blocks for seamless podcast episode playback.

## Features

- **Multi-Platform Support**: Works with Spotify, Overcast, Apple Podcasts, Google Podcasts, Pocket Casts, and RSS feeds
- **InnerBlocks Integration**: Automatically populates with Audio blocks for each podcast episode
- **User-Friendly Interface**: Clean, intuitive editor interface with platform selection and URL input
- **Responsive Design**: Looks great on all devices
- **REST API Integration**: Secure backend processing for podcast fetching

## How to Use

1. **Add the Block**: In the WordPress editor, search for "Podcast Block" and add it to your post or page.

2. **Configure Settings**: 
   - Select the platform (Spotify, Overcast, Apple Podcasts, etc.)
   - Enter the podcast profile URL
   - Click "Fetch Podcasts"

3. **Automatic Population**: The block will automatically populate with Audio blocks for each podcast episode found at the provided URL.

4. **Customize**: You can add additional content blocks (headings, paragraphs) alongside the audio blocks.

## Supported Platforms

- **Spotify**: Enter a Spotify artist or show page URL
- **Overcast**: Enter an Overcast profile or show URL
- **Apple Podcasts**: Enter an Apple Podcasts show URL
- **Google Podcasts**: Enter a Google Podcasts show URL
- **Pocket Casts**: Enter a Pocket Casts show URL
- **RSS Feed**: Enter a direct RSS feed URL

## Technical Details

### Block Structure
- Uses WordPress InnerBlocks for flexible content management
- Integrates with the core Audio block for podcast playback
- Supports additional content blocks (headings, paragraphs)

### Backend Processing
- REST API endpoint: `/wp-json/podcast-block/v1/fetch-podcasts`
- Secure with user permission checks
- Handles various podcast platform formats
- Includes error handling and validation

### Styling
- Clean, modern interface design
- Responsive layout for all screen sizes
- Consistent with WordPress design patterns
- Customizable through CSS

## Development Notes

### Current Implementation
The current implementation includes placeholder functions for podcast scraping. For production use, you would need to:

1. **Implement Real Scraping**: Replace the placeholder functions with actual web scraping or API integration
2. **Add Authentication**: For platforms that require API keys (like Spotify)
3. **Handle Rate Limiting**: Implement proper rate limiting for external API calls
4. **Add Caching**: Cache podcast data to improve performance
5. **Error Handling**: Enhance error handling for various edge cases

### File Structure
```
podcast-block/
├── src/podcast-block/
│   ├── block.json          # Block metadata and configuration
│   ├── edit.js            # Editor component with UI and functionality
│   ├── save.js            # Frontend rendering component
│   ├── editor.scss        # Editor-specific styles
│   ├── style.scss         # Frontend styles
│   ├── index.js           # Block registration
│   └── view.js            # Frontend JavaScript
├── podcast-block.php      # Main plugin file with REST API
└── README.md             # This file
```

## Installation

### From Source (Development)

1. **Clone the repository**:
   ```bash
   git clone https://github.com/tomfinitely/podcast-block.git
   cd podcast-block
   ```

2. **Install dependencies**:
   ```bash
   npm install
   ```

3. **Build the plugin**:
   ```bash
   npm run build
   ```

4. **Upload to WordPress**:
   - Copy the entire plugin directory to `/wp-content/plugins/podcast-block/`
   - Or create a symlink for development: `ln -s /path/to/podcast-block /wp-content/plugins/podcast-block`

5. **Activate the plugin**:
   - Go to the WordPress admin dashboard
   - Navigate to Plugins → Installed Plugins
   - Activate "Podcast Block"

### Manual Installation

1. [Download the latest release](https://github.com/tomfinitely/podcast-block/releases)
2. Upload the plugin files to `/wp-content/plugins/podcast-block/`
3. Activate the plugin through the 'Plugins' screen in WordPress
4. The block will be available in the block editor

## Development

### Prerequisites

- WordPress 6.7 or higher
- PHP 7.4 or higher
- Node.js 16+ and npm
- Modern web browser with JavaScript enabled

### Development Setup

1. **Start development mode**:
   ```bash
   npm start
   ```
   This will start the webpack dev server with hot reloading.

2. **Build for production**:
   ```bash
   npm run build
   ```

3. **Linting and formatting**:
   ```bash
   npm run lint:js
   npm run lint:css
   npm run format
   ```

4. **Create plugin zip**:
   ```bash
   npm run plugin-zip
   ```

## License

GPL-2.0-or-later

## Contributing

This is a demonstration block. For production use, consider:
- Adding proper API integrations
- Implementing comprehensive error handling
- Adding unit tests
- Following WordPress coding standards
- Adding internationalization support
