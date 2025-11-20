# Multi-Step Quotation Form for WordPress

A comprehensive, multi-step quotation form built with ACF Pro compatibility for Windows, Doors, and Bay Windows product configurations.

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

## Installation

### 1. Upload Theme Files

Upload the `quotation-form` folder to:
```
/wp-content/themes/quotation-form/
```

### 2. Activate Theme

Go to **Appearance > Themes** and activate **Quotation Form**.

Alternatively, if you want to use it as a template within your existing theme:
- Copy `quotation-form.php`, `quotation-form.js`, `quotation-form.css` to your active theme folder
- Copy the relevant functions from `functions.php` to your theme's functions file

### 3. Create a Page

1. Go to **Pages > Add New**
2. Give it a title (e.g., "Get a Quote")
3. From the **Page Attributes** panel, select **Template: Quotation Form**
4. Publish the page

### 4. Add Product Images

Create the following folder structure and add your product images:

```
/wp-content/themes/quotation-form/images/
├── windows.jpg
├── doors.jpg
├── bay-windows.jpg
├── casement-windows.jpg
├── flush-windows.jpg
├── tilt-turn.jpg
├── reversible.jpg
├── sash-windows.jpg
├── bifold-doors.jpg
├── french-doors.jpg
├── glazed-doors.jpg
├── sliding-doors.jpg
├── doorco.jpg
├── casement-bays.jpg
├── flush-bays.jpg
├── aluminium-casement-bays.jpg
├── pvcu-chamfered.jpg
├── pvcu-decorative.jpg
├── aluminium.jpg
├── doorco-traditional.jpg
├── doorco-designer.jpg
├── doorco-contemporary.jpg
└── styles/
    ├── w1.jpg
    ├── w2.jpg
    ├── w3.jpg
    ├── ... (up to w12.jpg)
```

**Image Dimensions Recommendations**:
- Product categories: 400x300px
- Product types: 300x300px
- Materials: 300x300px
- Styles: 250x250px

## Form Flow

### STEP 1: Product Selection

#### Sub-Step 1A: Category
User selects from:
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
- Aluminium Casement Bays → Skip material (already aluminium)

#### Sub-Step 1C: Material Selection

**Standard Materials**:
- PVCu Chamfered
- PVCu Decorative
- Aluminium

**DoorCo Materials**:
- Traditional
- Designer
- Contemporary

### STEP 2: Style Configuration

User selects from visual style diagrams (W1-W12, etc.)

### STEP 3: Configuration

**Left Side**: Visual preview with selected product details

**Right Side**: Configuration form
- Width (200-4000mm)
- Height (200-3200mm)
- Cill options
- Inside colour (searchable)
- Outside colour (searchable)
- Glazing type
- Glazing features
- Hardware colour
- Image upload (optional)

**Actions**:
- **Add to Basket**: Saves item and shows basket review
- **Restart**: Clears form and starts over

### BASKET REVIEW

- View all added items
- Edit any field by clicking on it
- Set location for each item
- Copy items
- Delete items
- Add more items
- Proceed to final review

### STEP 4: Customer Information & Submission

User provides:
- Full name
- Email
- Phone
- Address
- Postcode
- Preferred contact method
- Additional notes

Final order summary is displayed before submission.

## Customization

### Modify Colours

Edit the `colours` array in `quotation-form.js`:

```javascript
colours: [
    { name: 'White', category: 'Base', hex: '#FFFFFF' },
    { name: 'Custom Grey', category: 'Colour', hex: '#A1A1A1' },
    // Add more colours
],
```

### Modify Options

Edit the select options in `quotation-form.php`:

```php
<!-- Example: Add more cill options -->
<select id="cill" name="cill">
    <option value="150mm">150mm Sill</option>
    <option value="200mm">200mm Sill</option>
    <option value="250mm">250mm Sill</option>
    <option value="none">No Sill</option>
</select>
```

### Modify Email Notification

Edit the `handle_quotation_form_submission()` function in `functions.php`:

```php
function handle_quotation_form_submission() {
    // Modify email content, recipients, etc.
}
```

### Add Database Storage

Instead of just emailing, save to custom post type:

```php
function handle_quotation_form_submission() {
    check_ajax_referer('quotation_form_nonce', 'nonce');

    $basket_items = isset($_POST['basket_items']) ? json_decode(stripslashes($_POST['basket_items']), true) : array();
    $customer_data = isset($_POST['customer_data']) ? $_POST['customer_data'] : array();

    // Create custom post
    $post_id = wp_insert_post(array(
        'post_title' => 'Quote: ' . $customer_data['customer_name'],
        'post_type' => 'quotation_submission',
        'post_status' => 'publish'
    ));

    // Save customer data as meta
    foreach ($customer_data as $key => $value) {
        update_post_meta($post_id, $key, sanitize_text_field($value));
    }

    // Save basket items
    update_post_meta($post_id, 'basket_items', $basket_items);

    wp_send_json_success(array(
        'message' => 'Quotation request submitted successfully!',
        'quote_id' => $post_id
    ));
}
```

### Styling Customization

All styles are in `quotation-form.css`. Key CSS variables:

```css
:root {
    --primary-color: #0066cc;        /* Main brand colour */
    --primary-hover: #0052a3;        /* Hover state */
    --secondary-color: #6c757d;      /* Secondary buttons */
    --success-color: #28a745;        /* Success indicators */
    --danger-color: #dc3545;         /* Delete/danger actions */
    /* ... more variables */
}
```

## ACF Pro Integration (Optional)

While the form works without ACF Pro, you can use it for:
- Admin-configurable product options
- Backend storage with structured fields
- Custom options pages

See `ACF-SETUP-GUIDE.md` for details.

## Validation Rules

### Width Field
- Minimum: 200mm
- Maximum: 4000mm
- Required: Yes

### Height Field
- Minimum: 200mm
- Maximum: 3200mm
- Required: Yes

### Required Fields (Configuration)
- Width
- Height
- Cill
- Inside Colour
- Outside Colour
- Glazing Type
- Hardware Colour

### Required Fields (Customer Info)
- Full Name
- Email
- Phone

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Form doesn't appear
- Check if the page template is set to "Quotation Form"
- Check if JavaScript is loading (browser console)
- Verify theme is activated

### Styles not loading
- Check if `quotation-form.css` is enqueued
- Clear browser cache
- Check for CSS conflicts

### AJAX submission fails
- Check WordPress REST API is enabled
- Verify nonce is being passed correctly
- Check server error logs
- Ensure `admin-ajax.php` is accessible

### Images not displaying
- Verify images are uploaded to `/images/` folder
- Check image filenames match exactly
- Use correct image extensions (.jpg, .png, etc.)

## Security Features

- ✅ Nonce verification for AJAX submissions
- ✅ Input sanitization on server side
- ✅ XSS protection
- ✅ SQL injection protection (when using database)
- ✅ File upload validation (image types only)

## Performance Optimization

- Minify CSS and JavaScript for production
- Optimize images (use WebP format)
- Enable browser caching
- Use CDN for assets
- Lazy load images in style selection

## Support & Documentation

For issues or questions:
1. Check this README
2. Review `ACF-SETUP-GUIDE.md`
3. Check browser console for JavaScript errors
4. Enable WordPress debug mode to see PHP errors

## Changelog

### Version 1.0
- Initial release
- Multi-step form with basket functionality
- Inline item editing
- AJAX submission
- Responsive design
- Email notifications

## License

This theme is proprietary to Cristal Windows.

## Credits

Built with:
- jQuery
- WordPress
- Modern CSS Grid & Flexbox
- Vanilla JavaScript (ES6+)
