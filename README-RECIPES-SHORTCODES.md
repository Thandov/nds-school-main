# NDS Recipes Shortcodes Documentation

## Overview

The NDS School plugin includes powerful shortcodes for displaying recipes that automatically adapt to your current theme's styling and work seamlessly with Elementor. These shortcodes provide multiple layout options and are designed to integrate naturally with any WordPress theme.

## Available Shortcodes

### 1. Main Recipes Grid Shortcode
```
[nds_recipes]
```

**Parameters:**
- `limit` (number) - Number of recipes to display (default: 12)
- `columns` (number) - Number of columns in grid (default: 4)
- `orderby` (string) - Sort by: 'created_at', 'recipe_name' (default: 'created_at')
- `order` (string) - Sort order: 'ASC', 'DESC' (default: 'DESC')
- `show_image` (boolean) - Show recipe images (default: 'true')
- `show_description` (boolean) - Show recipe descriptions (default: 'true')
- `show_time` (boolean) - Show cooking time (default: 'true')
- `show_servings` (boolean) - Show servings count (default: 'true')
- `layout` (string) - Layout type: 'grid', 'list', 'masonry', 'carousel' (default: 'grid')
- `theme_style` (string) - Style: 'auto', 'minimal', 'card', 'modern' (default: 'auto')
- `elementor_compatible` (boolean) - Elementor compatibility (default: 'true')
- `carousel` (boolean) - Enable carousel mode (default: 'false')

**Examples:**
```php
// Basic usage - 4 columns by default
[nds_recipes]

// Custom grid with 6 columns
[nds_recipes columns="6" limit="12"]

// List layout with minimal styling
[nds_recipes layout="list" theme_style="minimal"]

// Masonry layout for modern themes
[nds_recipes layout="masonry" theme_style="modern"]

// Carousel mode with 4 recipes per slide
[nds_recipes carousel="true" limit="8"]

// Carousel layout
[nds_recipes layout="carousel" limit="12"]
```

### 2. Recipe Grid Shortcode
```
[nds_recipe_grid]
```
Same as main shortcode but specifically for grid layout.

### 3. Single Recipe Shortcode
```
[nds_recipe_single id="1"]
```

**Parameters:**
- `id` (number) - Recipe ID (required)
- `show_image` (boolean) - Show recipe image (default: 'true')
- `show_ingredients` (boolean) - Show ingredients list (default: 'true')
- `show_steps` (boolean) - Show cooking steps (default: 'true')
- `show_meta` (boolean) - Show cooking time, prep time, servings (default: 'true')
- `theme_style` (string) - Style: 'auto', 'minimal', 'card', 'modern' (default: 'auto')

**Examples:**
```php
// Display recipe with ID 5
[nds_recipe_single id="5"]

// Display recipe without ingredients
[nds_recipe_single id="5" show_ingredients="false"]

// Display recipe with custom styling
[nds_recipe_single id="5" theme_style="modern"]
```

### 4. Recipe Carousel Shortcode
```
[nds_recipe_carousel]
```

**Parameters:**
- `limit` (number) - Number of recipes to display (default: 8)
- `autoplay` (boolean) - Enable autoplay (default: 'true')
- `dots` (boolean) - Show navigation dots (default: 'true')
- `arrows` (boolean) - Show navigation arrows (default: 'true')
- `theme_style` (string) - Style: 'auto', 'minimal', 'card', 'modern' (default: 'auto')
- `recipes_per_slide` (number) - Number of recipes per slide (default: 4)

**Examples:**
```php
// Basic carousel - 4 recipes per slide
[nds_recipe_carousel]

// Carousel without autoplay
[nds_recipe_carousel autoplay="false"]

// Carousel with custom styling and 6 recipes per slide
[nds_recipe_carousel theme_style="modern" limit="12" recipes_per_slide="6"]

// Carousel with 2 recipes per slide
[nds_recipe_carousel recipes_per_slide="2" limit="6"]
```

## Automatic Theme Detection

The shortcodes automatically detect your current theme and apply appropriate styling:

### Supported Themes:
- **Elementor** - Automatically detected, uses Elementor-compatible styling
- **Astra** - Modern, clean styling
- **OceanWP** - Modern styling with rounded corners
- **GeneratePress** - Minimal, clean styling
- **Divi** - Card-based styling
- **Avada** - Modern styling
- **Enfold** - Card-based styling
- **Twenty* themes** - Minimal styling

### Manual Style Override:
You can override the automatic detection by specifying a theme style:

```php
[nds_recipes theme_style="modern"]
[nds_recipes theme_style="minimal"]
[nds_recipes theme_style="card"]
```

## Elementor Integration

The shortcodes are fully compatible with Elementor:

### Using in Elementor:
1. Add a **Shortcode** widget to your Elementor page
2. Insert any of the recipe shortcodes
3. The styling will automatically adapt to your Elementor theme

### Elementor-Specific Features:
- Responsive design that works with Elementor breakpoints
- CSS that doesn't conflict with Elementor styles
- Automatic detection of Elementor theme
- Compatible with Elementor containers and sections

## Layout Options

### 1. Grid Layout (Default)
- **Always 4 columns by default** (configurable)
- Single recipe spans full width (col-span-4)
- Responsive grid with hover effects
- Perfect for showcasing multiple recipes

### 2. List Layout
- Horizontal layout with image on the left
- Compact design for space efficiency
- Good for recipe archives

### 3. Masonry Layout
- Pinterest-style layout
- Variable height cards
- Modern, dynamic appearance

### 4. Carousel Layout (Powered by Owl Carousel)
- **4 recipes per slide by default** (configurable)
- Smooth sliding transitions with Owl Carousel
- Navigation arrows and dots
- Autoplay functionality with pause on hover
- Touch/swipe support for mobile
- Responsive breakpoints (1 item on mobile, 2 on tablet, 4+ on desktop)
- Infinite loop functionality

## Responsive Design

All shortcodes are fully responsive:

- **Desktop**: 4 columns by default (configurable)
- **Tablet**: 2 columns
- **Mobile**: 1 column

### Grid Behavior:
- **Single Recipe**: Always spans full width (col-span-4)
- **Multiple Recipes**: Distributed across available columns
- **Dynamic Columns**: Use `columns="6"` for 6 columns, etc.

## Customization

### CSS Customization
You can override the default styles by adding custom CSS to your theme:

```css
/* Custom recipe card styling */
.nds-recipe-card {
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

/* Custom hover effects */
.nds-recipe-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

/* Custom colors */
.nds-style-modern {
    --primary-color: #your-color;
    --secondary-color: #your-secondary-color;
}
```

### JavaScript Customization
The carousel includes JavaScript for enhanced functionality:
- Touch/swipe support for mobile
- Keyboard navigation
- Autoplay with pause on hover
- Responsive behavior

## Performance Features

- **Lazy Loading**: Images load only when visible
- **Conditional Loading**: CSS/JS only loads when shortcodes are present
- **Optimized Queries**: Efficient database queries
- **Caching Ready**: Compatible with caching plugins
- **Owl Carousel**: Industry-standard carousel library for smooth performance

## Troubleshooting

### Common Issues:

1. **Shortcode not displaying**
   - Check if recipes exist in the database
   - Verify shortcode syntax
   - Check for JavaScript errors in browser console

2. **Styling conflicts**
   - Use `theme_style="minimal"` for basic styling
   - Add custom CSS to override conflicting styles
   - Check if your theme has aggressive CSS reset

3. **Carousel not working**
   - Ensure jQuery is loaded
   - Check for JavaScript errors
   - Verify that the shortcode is properly closed

### Debug Mode:
Add this to your theme's functions.php for debugging:
```php
add_filter('nds_recipes_debug', '__return_true');
```

## Advanced Usage

### Custom Recipe URLs
You can customize where recipe links point to by modifying the `get_recipe_url()` method in the shortcode class.

### Custom Styling Classes
Add custom CSS classes to your theme to create additional styling options:

```css
.nds-style-custom {
    --primary-color: #your-color;
    --secondary-color: #your-secondary-color;
    --text-color: #your-text-color;
    --border-color: #your-border-color;
}
```

Then use:
```php
[nds_recipes theme_style="custom"]
```

## Support

For support and feature requests, please refer to the plugin documentation or contact the development team.

---

**Note**: These shortcodes are designed to work with the NDS School plugin's recipe system. Ensure you have recipes added through the admin panel before using the shortcodes.
