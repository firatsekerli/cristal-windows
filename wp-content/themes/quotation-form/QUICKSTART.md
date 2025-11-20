# Quick Start Guide

Get your multi-step quotation form up and running in 5 minutes!

## Step 1: Install Theme

1. Upload the `quotation-form` folder to `/wp-content/themes/`
2. Go to **Appearance > Themes** in WordPress admin
3. Activate **Quotation Form** theme

## Step 2: Create Quote Page

1. Go to **Pages > Add New**
2. Title: "Get a Quote" (or your preference)
3. **Page Attributes** > **Template**: Select "Quotation Form"
4. Click **Publish**
5. Copy the page URL

## Step 3: Add Images (Optional but Recommended)

Upload your product images to `/wp-content/themes/quotation-form/images/`

See `images/IMAGE-LIST.md` for the complete list of required images.

**Don't have images yet?** The form will still work, but image areas will show placeholders.

## Step 4: Test the Form

1. Visit the quote page you created
2. Click through the form steps
3. Add items to basket
4. Submit a test quote

## Step 5: Configure Email Settings

By default, quote submissions are sent to the WordPress admin email.

To change the recipient:

1. Edit `functions.php`
2. Find the `handle_quotation_form_submission()` function
3. Change this line:
```php
$admin_email = get_option('admin_email'); // Change to your email
```

To:
```php
$admin_email = 'quotes@yourdomain.com'; // Your custom email
```

## Common Customizations

### Change Brand Colours

Edit `quotation-form.css` and modify these variables:

```css
:root {
    --primary-color: #0066cc;        /* Your brand colour */
    --primary-hover: #0052a3;        /* Darker shade */
}
```

### Add More Colour Options

Edit `quotation-form.js` and add to the `colours` array:

```javascript
colours: [
    { name: 'White', category: 'Base', hex: '#FFFFFF' },
    { name: 'Your Custom Colour', category: 'Colour', hex: '#123456' },
]
```

### Add More Cill Options

Edit `quotation-form.php` around line 250:

```php
<select id="cill" name="cill">
    <option value="">Select Cill</option>
    <option value="150mm">150mm Sill</option>
    <option value="200mm">200mm Sill</option>
    <option value="250mm">250mm Sill</option> <!-- Add this -->
    <option value="none">No Sill</option>
</select>
```

### Change Dimension Limits

Edit `quotation-form.js` around line 280:

```javascript
if (!width || width < 200 || width > 4000) { // Change max to 5000
    alert('Please enter a valid width (200-5000mm)');
    return false;
}
```

Also update the HTML validation in `quotation-form.php`:

```php
<input type="number" id="width" name="width" min="200" max="5000" required>
```

## Troubleshooting

### Form not showing?
- Check page template is set to "Quotation Form"
- Try a different browser
- Check browser console for errors (F12)

### Styles look broken?
- Clear browser cache (Ctrl+Shift+Delete)
- Check if CSS file is loading
- Disable other plugins temporarily

### Can't submit form?
- Check WordPress admin email settings
- Verify site can send emails (use WP Mail SMTP plugin)
- Check server error logs

### Images not displaying?
- Verify images are in `/images/` folder
- Check filename spelling matches exactly
- Try different image format (JPG instead of PNG)

## Next Steps

✅ Read full `README.md` for detailed documentation
✅ Review `ACF-SETUP-GUIDE.md` if using ACF Pro
✅ Customize colours and branding
✅ Add your product images
✅ Test on mobile devices
✅ Set up email notifications

## Need Help?

1. Check `README.md` for detailed docs
2. Review browser console for JS errors
3. Enable WordPress debug mode:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

## Testing Checklist

- [ ] All product categories are clickable
- [ ] Type selection works for each category
- [ ] Material selection appears when required
- [ ] Style selection displays all options
- [ ] Configuration form validates inputs
- [ ] Colours can be selected (inside/outside)
- [ ] Add to basket works
- [ ] Basket shows added items
- [ ] Edit item fields work
- [ ] Copy/delete items work
- [ ] Customer information form appears
- [ ] Form submits successfully
- [ ] Email notification received

## Success!

Your multi-step quotation form is now ready! 🎉

Visit your quote page and start collecting product inquiries from customers.
