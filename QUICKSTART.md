# Quick Start Guide - Multi-Step Quotation Form Plugin

Get your quotation form up and running in 3 minutes!

## Step 1: Install & Activate (1 minute)

### Via WordPress Admin (Recommended)
1. Go to **Plugins > Add New**
2. Click **Upload Plugin**
3. Choose the plugin ZIP file
4. Click **Install Now**
5. Click **Activate**

### Manual Installation
1. Upload `quotation-form` folder to `/wp-content/plugins/`
2. Go to **Plugins** in WordPress admin
3. Find "Multi-Step Quotation Form" and click **Activate**

## Step 2: Add to Your Page (1 minute)

1. Create a new page or edit an existing one
   - Go to **Pages > Add New** (or edit existing)
   - Give it a title like "Get a Quote"

2. Add the shortcode in the content area:
   ```
   [quotation_form]
   ```

3. Click **Publish** or **Update**

4. Visit the page to see your form! 🎉

## Step 3: Test the Form (1 minute)

1. Visit your page with the form
2. Click through a few steps
3. Try adding an item to the basket
4. Complete and submit a test quote
5. Check your email for the notification

## Done! Your Form is Live

The form is now working with default settings.

## Optional Enhancements

### Add Product Images (Recommended)

Upload your product images to improve the visual experience:

**Location**: `/wp-content/plugins/quotation-form/assets/images/`

**Required files** (see `assets/images/IMAGE-LIST.md` for full list):
- `windows.jpg`, `doors.jpg`, `bay-windows.jpg`
- `casement-windows.jpg`, `french-doors.jpg`, etc.
- `pvcu-chamfered.jpg`, `aluminium.jpg`, etc.
- `styles/w1.jpg`, `styles/w2.jpg`, etc.

**Quick tip**: The form works without images - gray placeholders will be shown.

### Customize Brand Colors

Edit `/assets/css/quotation-form.css` and change:

```css
:root {
    --primary-color: #0066cc;        /* Change to your brand color */
    --primary-hover: #0052a3;        /* Darker shade */
}
```

### Change Email Recipient

By default, quotes go to your WordPress admin email.

To change it, edit `quotation-form.php` and find line ~155:

```php
$admin_email = get_option('admin_email');
```

Change to:

```php
$admin_email = 'quotes@yourdomain.com';
```

## Common Customizations

### Add More Colors

Edit `/assets/js/quotation-form.js` around line 15:

```javascript
colours: [
    { name: 'White', category: 'Base', hex: '#FFFFFF' },
    { name: 'Your New Color', category: 'Colour', hex: '#123456' },
    // Add more...
],
```

### Add More Cill Options

Edit `/templates/form-template.php` around line 250:

```php
<select id="cill" name="cill">
    <option value="150mm">150mm Sill</option>
    <option value="200mm">200mm Sill</option>
    <option value="250mm">250mm Sill</option> <!-- NEW -->
    <option value="none">No Sill</option>
</select>
```

### Change Size Limits

Edit `/assets/js/quotation-form.js` around line 280:

```javascript
// Change width max from 4000 to 5000
if (!width || width < 200 || width > 5000) {
    alert('Please enter a valid width (200-5000mm)');
    return false;
}
```

Also update the HTML in `/templates/form-template.php`:

```php
<input type="number" id="width" name="width" min="200" max="5000" required>
```

## Troubleshooting

### Form not showing?
- ✅ Plugin activated?
- ✅ Shortcode spelled correctly? `[quotation_form]`
- ✅ Try different browser or clear cache

### Styles broken?
- ✅ Clear browser cache (Ctrl+Shift+Delete)
- ✅ Try disabling other plugins
- ✅ Check browser console for errors (F12)

### Can't submit?
- ✅ Check site can send emails (use WP Mail SMTP plugin)
- ✅ Check browser console for errors
- ✅ Enable debug mode (see below)

### Images not showing?
- ✅ Upload to `/wp-content/plugins/quotation-form/assets/images/`
- ✅ Check exact filenames (case-sensitive!)
- ✅ Use .jpg or .png format

## Enable Debug Mode

If you're having issues, enable debug mode to see errors.

Add to `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Then check `/wp-content/debug.log` for errors.

## Testing Checklist

- [ ] Plugin activated
- [ ] Shortcode added to page
- [ ] Form displays correctly
- [ ] Can navigate through steps
- [ ] Can select products
- [ ] Can add item to basket
- [ ] Can edit basket items
- [ ] Can submit form
- [ ] Email received
- [ ] Looks good on mobile

## Next Steps

✅ Read full **README.md** for detailed documentation

✅ Add your product images

✅ Customize colors to match your brand

✅ Test on mobile devices

✅ Set up email notifications

✅ (Optional) Review **ACF-SETUP-GUIDE.md** for advanced features

## Example Page Layout

Here's a complete example page setup:

**Page Title**: Get a Free Quote

**Page Content**:
```
<h1>Get Your Custom Quote</h1>

<p>Configure your perfect windows, doors, or bay windows in just 4 easy steps. Get a detailed quote instantly!</p>

[quotation_form]

<p><em>Need help? Call us at 123-456-7890 or email quotes@yourcompany.com</em></p>
```

**That's it!** Your form will appear between the intro text and contact info.

## Performance Tips

1. **Optimize images** before uploading (compress to 100-200KB each)
2. **Use caching** plugin (WP Super Cache, W3 Total Cache)
3. **Enable lazy loading** for images
4. **Use CDN** for faster asset delivery

## Success! 🎉

Your multi-step quotation form is now ready to collect inquiries from customers!

---

**Pro Tip**: Set up a thank you page and redirect customers there after submission for better tracking!

**Questions?** Check the full `README.md` or enable debug mode to troubleshoot issues.
