# Contributing to Podcast Block

Thank you for your interest in contributing to the Podcast Block WordPress plugin! This document provides guidelines and information for contributors.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for all contributors.

## How to Contribute

### Reporting Issues

1. **Search existing issues** first to avoid duplicates
2. **Use the issue templates** when available
3. **Provide detailed information**:
   - WordPress version
   - PHP version
   - Plugin version
   - Steps to reproduce
   - Expected vs actual behavior
   - Screenshots if applicable

### Suggesting Enhancements

1. **Check the roadmap** in the README and CHANGELOG
2. **Open a feature request** with:
   - Clear description of the enhancement
   - Use cases and benefits
   - Potential implementation approach

### Pull Requests

1. **Fork the repository** and create a feature branch
2. **Follow the development setup** outlined in README.md
3. **Make your changes**:
   - Write clean, well-documented code
   - Follow WordPress coding standards
   - Add or update tests when applicable
   - Update documentation as needed

4. **Test thoroughly**:
   - Test on multiple WordPress versions (6.7+)
   - Test with different themes
   - Verify block editor functionality
   - Check frontend display

5. **Submit the pull request**:
   - Use clear, descriptive commit messages
   - Reference any related issues
   - Provide a detailed description of changes

## Development Guidelines

### Code Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- Use [WordPress PHP Documentation Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/#documenting-the-code)
- Use ESLint and Prettier for JavaScript (configured in the project)
- Write semantic, accessible HTML
- Use BEM methodology for CSS classes when possible

### WordPress Specific

- Use WordPress functions and APIs when available
- Follow [WordPress security best practices](https://developer.wordpress.org/plugins/security/)
- Ensure compatibility with WordPress 6.7+
- Test with both Classic Editor and Gutenberg
- Follow [WordPress Internationalization guidelines](https://developer.wordpress.org/plugins/internationalization/)

### JavaScript/React

- Use modern JavaScript (ES6+)
- Follow React best practices for components
- Use WordPress components (`@wordpress/components`) when possible
- Implement proper error handling
- Use hooks and functional components

### PHP

- Use PHP 7.4+ features appropriately
- Implement proper error handling and validation
- Use WordPress hooks and filters
- Follow WordPress naming conventions
- Add PHPDoc comments for all functions

### CSS/SCSS

- Write mobile-first responsive styles
- Use WordPress admin color scheme variables
- Minimize specificity conflicts
- Follow accessibility guidelines (WCAG 2.1 AA)

## Development Setup

### Prerequisites

- WordPress 6.7+
- PHP 7.4+
- Node.js 16+
- npm or yarn

### Local Development

1. **Clone and setup**:
   ```bash
   git clone https://github.com/tomfinitely/podcast-block.git
   cd podcast-block
   npm install
   ```

2. **Development workflow**:
   ```bash
   # Start development server (hot reloading)
   npm start
   
   # Build for production
   npm run build
   
   # Lint and format code
   npm run lint:js
   npm run lint:css
   npm run format
   
   # Create plugin zip
   npm run plugin-zip
   ```

3. **Testing**:
   - Install in a local WordPress development environment
   - Test with different themes and plugins
   - Verify functionality across different browsers
   - Test accessibility features

### Areas for Contribution

We welcome contributions in these areas:

#### High Priority
- **API Integrations**: Real implementations for Spotify, Apple Podcasts, etc.
- **Performance**: Caching, rate limiting, optimization
- **Error Handling**: Comprehensive error states and user feedback
- **Testing**: Unit tests, integration tests, e2e tests

#### Medium Priority
- **Accessibility**: ARIA labels, keyboard navigation, screen reader support
- **Internationalization**: Translation support and language files
- **Documentation**: Code comments, user guides, developer docs
- **UI/UX**: Design improvements, user experience enhancements

#### Nice to Have
- **Additional Platforms**: Support for more podcast platforms
- **Advanced Features**: Playlist management, episode filtering
- **Admin Interface**: Settings page, configuration options
- **Performance Metrics**: Analytics, usage tracking

## Platform-Specific Development

When adding support for new podcast platforms:

1. **Research the platform's API or RSS feeds**
2. **Implement authentication if required**
3. **Add error handling for API rate limits**
4. **Update platform dropdown in the editor**
5. **Add documentation and examples**
6. **Test with real podcast URLs**

## Security Considerations

- Always sanitize and validate user input
- Use nonces for form submissions
- Implement proper capability checks
- Escape output appropriately
- Follow WordPress security guidelines
- Never expose API keys or sensitive data

## Getting Help

- **Issues**: Use GitHub issues for bugs and feature requests
- **Discussions**: Use GitHub discussions for questions and ideas
- **Documentation**: Check README.md and code comments

## License

By contributing to this project, you agree that your contributions will be licensed under the GPL-2.0-or-later license.

Thank you for contributing to make Podcast Block better for everyone! 🎙️
