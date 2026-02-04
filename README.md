# Multi-Step Quotation Form Plugin for WordPress

A comprehensive WordPress plugin providing a multi-step quotation form for Windows, Doors, and Bay Windows product configurations. Works with any WordPress theme!

## Features

- ✅ **4-Step Process**: Product → Style → Configuration → Review
- ✅ **Multi-Item Basket**: Add multiple products before submission
- ✅ **Dynamic Navigation**: Conditional sub-steps based on product selection
- ✅ **Inline Editing**: Click to edit basket items without losing data
- ✅ **Visual Preview**: Real-time preview of selected configurations
- ✅ **Colour Picker**: Searchable colour selection with visual swatches
- ✅ **Validation**: Built-in field validation for dimensions and required fields
- ✅ **Responsive Design**: Mobile-friendly, tablet-optimized layout
- ✅ **AJAX Submission**: No page reload, smooth user experience
- ✅ **Email Notifications**: Automatic emails to admin and customer
- ✅ **Shortcode Based**: Works with any WordPress theme

## Installation

### Method 1: Upload via WordPress Admin

1. Download the plugin ZIP file
2. Go to **Plugins > Add New** in WordPress admin
3. Click **Upload Plugin**
4. Choose the ZIP file and click **Install Now**
5. Click **Activate Plugin**

### Method 2: Manual Installation

1. Upload the `quotation-form` folder to `/wp-content/plugins/`
2. Go to **Plugins** in WordPress admin
3. Find **Multi-Step Quotation Form** and click **Activate**

## Usage

### Basic Usage

Add the shortcode to any page or post:

```
[quotation_form]
```

### Step-by-Step Setup

1. **Activate the plugin**

2. **Create or edit a page** where you want the form to appear

3. **Add the shortcode**:
   ```
   [quotation_form]
   ```

4. **Publish the page**

5. **Add product images** (optional but recommended):
   Upload images to `/wp-content/plugins/quotation-form/assets/images/`

That's it! Your quotation form is now live.

## Adding Product Images

Product images enhance the user experience. Add your images to the plugin's assets folder:

```
/wp-content/plugins/quotation-form/assets/images/
```

### Required Images

See `assets/images/IMAGE-LIST.md` for the complete list of required images.

**Quick Reference**:
- **Product categories**: `windows.jpg`, `doors.jpg`, `bay-windows.jpg`
- **Window types**: `casement-windows.jpg`, `flush-windows.jpg`, `tilt-turn.jpg`, etc.
- **Door types**: `bifold-doors.jpg`, `french-doors.jpg`, `sliding-doors.jpg`, etc.
- **Materials**: `pvcu-chamfered.jpg`, `pvcu-decorative.jpg`, `aluminium.jpg`, etc.
- **Styles**: `styles/w1.jpg` through `styles/w12.jpg`

**Recommended image dimensions**:
- Product categories: 400x300px
- Product types: 300x300px
- Materials: 300x300px
- Styles: 250x250px

**Note**: The form works without images - placeholders will be shown.

## Form Flow

### STEP 1: Product Selection

#### Sub-Step 1A: Category
- Windows
- Doors
- Bay Windows

#### Sub-Step 1B: Type Selection

**Windows Path**:
- Casement Windows → Material required
- Flush Windows → Material required
- Tilt & Turn → Material required
- Reversible Window → Skip material
- Sash Windows → Skip material

**Doors Path**:
- BiFold Doors → Skip material
- French Doors → Material required (standard)
- Glazed Doors → Material required (standard)
- Sliding Doors → Material required (standard)
- DoorCo → Material required (DoorCo-specific)

**Bay Windows Path**:
- Casement Bays → Material required
- Flush Bays → Skip material
- Aluminium Casement Bays → Skip material

#### Sub-Step 1C: Material Selection

**Standard Materials**: PVCu Chamfered, PVCu Decorative, Aluminium

**DoorCo Materials**: Traditional, Designer, Contemporary

### STEP 2: Style Configuration

Visual style diagrams (W1-W12, etc.)

### STEP 3: Configuration

- Width (200-4000mm)
- Height (200-3200mm)
- Cill options
- Inside & outside colours (searchable)
- Glazing type & features
- Hardware colour
- Image upload (optional)

**Actions**: Add to Basket | Restart

### BASKET REVIEW

- View all items
- Edit fields inline
- Set location
- Copy/delete items
- Add more items

### STEP 4: Customer Information

- Full name, email, phone
- Address & postcode
- Preferred contact method
- Additional notes

## Customization

### Modify Colours

Edit `/assets/js/quotation-form.js` and update the `colours` array:

```javascript
colours: [
    { name: 'White', category: 'Standard', hex: '#FFFFFF' },
    { name: 'Custom Grey', category: 'Colour', hex: '#A1A1A1' },
    // Add more colours
],
```

### Change Brand Colours

Edit `/assets/css/quotation-form.css` and modify CSS variables:

```css
:root {
    --primary-color: #0066cc;        /* Your brand colour */
    --primary-hover: #0052a3;        /* Hover state */
    --secondary-color: #6c757d;      /* Secondary buttons */
    /* ...more variables */
}
```

### Modify Form Options

Edit `/templates/form-template.php` to add or modify options:

```php
<!-- Example: Add more cill options -->
<select id="cill" name="cill">
    <option value="150mm">150mm Sill</option>
    <option value="200mm">200mm Sill</option>
    <option value="250mm">250mm Sill</option> <!-- New option -->
    <option value="none">No Sill</option>
</select>
```

### Email Configuration

By default, notifications go to the WordPress admin email. To customize:

Edit `quotation-form.php` and modify the `send_email_notification()` method:

```php
private function send_email_notification($basket_items, $customer_data) {
    // Change recipient
    $admin_email = 'custom@yourdomain.com'; // Instead of get_option('admin_email')

    // Customize email content
    $subject = 'Custom subject line';
    // ...
}
```

### Save to Database

To save submissions to the database, uncomment the `save_to_database()` method in `quotation-form.php`:

```php
// In handle_form_submission method, add:
$this->save_to_database($basket_items, $customer_data);
```

Then customize the `save_to_database()` method to save as custom post type or custom table.

## Advanced Usage

### Custom Styling

Override plugin styles in your theme's `style.css`:

```css
/* Override primary color */
.quotation-form-container {
    --primary-color: #your-color;
}

/* Customize form container */
.quotation-form-container {
    max-width: 1400px; /* Wider form */
}
```

### Programmatic Usage

Display the form programmatically in your theme:

```php
<?php echo do_shortcode('[quotation_form]'); ?>
```

### Hooks & Filters (Coming Soon)

Future versions will include WordPress hooks for developers:

```php
// Example (not yet implemented)
add_filter('quotation_form_colours', 'custom_colours');
add_action('quotation_form_after_submission', 'custom_action');
```

## Validation Rules

### Configuration Step
- **Width**: 200-4000mm (required)
- **Height**: 200-3200mm (required)
- **Cill**: Required
- **Inside Colour**: Required
- **Outside Colour**: Required
- **Glazing Type**: Required
- **Hardware Colour**: Required

### Customer Information
- **Full Name**: Required
- **Email**: Required (valid email format)
- **Phone**: Required

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.2 or higher
- **jQuery**: Included with WordPress

## Troubleshooting

### Form not displaying
**Issue**: Shortcode shows as text instead of form

**Solution**:
- Check plugin is activated
- Verify shortcode is spelled correctly: `[quotation_form]`
- Try switching to a default WordPress theme temporarily

### Styles not loading
**Issue**: Form looks unstyled or broken

**Solution**:
- Clear browser cache (Ctrl+Shift+Delete)
- Clear WordPress cache (if using caching plugin)
- Check browser console for JavaScript errors (F12)
- Try disabling other plugins temporarily

### Form not submitting
**Issue**: Submit button doesn't work or shows error

**Solution**:
- Check WordPress can send emails (install WP Mail SMTP plugin)
- Verify `admin-ajax.php` is accessible
- Check browser console for JavaScript errors
- Enable WordPress debug mode to see errors

### Images not showing
**Issue**: Placeholder boxes instead of product images

**Solution**:
- Upload images to `/wp-content/plugins/quotation-form/assets/images/`
- Check image filenames match exactly (case-sensitive)
- Verify image formats are .jpg or .png
- Check file permissions (should be 644)

### Enable Debug Mode

Add to `wp-config.php` to see detailed errors:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check `/wp-content/debug.log` for errors.

## Security Features

- ✅ Nonce verification for AJAX
- ✅ Input sanitization
- ✅ XSS protection
- ✅ SQL injection protection
- ✅ File upload validation

## Performance

### Optimization Tips

1. **Optimize images**:
   - Use WebP format
   - Compress images before upload
   - Use consistent dimensions

2. **Enable caching**:
   - Use a caching plugin (WP Super Cache, W3 Total Cache)
   - Enable browser caching

3. **Minify assets** (for production):
   - Minify CSS and JavaScript files
   - Use a minification plugin

4. **Use a CDN**:
   - Serve static assets via CDN

## Compatibility

### Theme Compatibility
Works with any properly coded WordPress theme.

**Tested with**:
- Twenty Twenty-Three
- Twenty Twenty-Two
- Astra
- GeneratePress
- OceanWP

### Plugin Compatibility
Compatible with most WordPress plugins.

**Known compatible plugins**:
- Contact Form 7
- WooCommerce
- Yoast SEO
- ACF Pro (optional integration)

## ACF Pro Integration (Optional)

While the plugin works standalone, you can optionally use ACF Pro for:
- Admin-configurable options
- Custom post type storage
- Additional field customization

See `ACF-SETUP-GUIDE.md` for details.

## File Structure

```
quotation-form/
├── quotation-form.php          # Main plugin file
├── assets/
│   ├── css/
│   │   └── quotation-form.css  # Styles
│   ├── js/
│   │   └── quotation-form.js   # JavaScript
│   └── images/                 # Product images
│       ├── .gitkeep
│       ├── IMAGE-LIST.md
│       └── styles/             # Style diagrams
├── templates/
│   └── form-template.php       # Form HTML template
├── README.md                   # This file
├── QUICKSTART.md               # Quick setup guide
└── ACF-SETUP-GUIDE.md         # ACF integration guide
```

## Changelog

### Version 1.0.0
- Initial plugin release
- Multi-step form with basket functionality
- Inline item editing
- AJAX submission
- Email notifications
- Responsive design
- Shortcode support

## Support

For issues or questions:
1. Check this README
2. Review `QUICKSTART.md`
3. Check browser console for errors (F12)
4. Enable WordPress debug mode

## License

Proprietary - Cristal Windows

## Credits

Built with:
- jQuery
- WordPress Plugin API
- Modern CSS Grid & Flexbox
- Vanilla JavaScript (ES6+)

---

**Need help getting started?** Check out `QUICKSTART.md` for a 5-minute setup guide!
