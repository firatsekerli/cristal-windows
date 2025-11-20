# ACF Pro Field Groups Setup Guide

This document explains the ACF Pro field groups needed for the multi-step quotation form. The form is designed to work **without ACF fields** as it uses standard HTML inputs and JavaScript for state management. However, if you want to use ACF for additional customization or backend storage, follow this guide.

## Overview

The quotation form is built with **pure HTML, CSS, and JavaScript** and does not require ACF fields to function. All form state is managed in JavaScript and submitted via AJAX.

However, you may want to create ACF field groups for:
- **Admin configuration** (colours, product types, styles, etc.)
- **Backend data storage** for submissions
- **Custom options pages** for form settings

## Optional ACF Field Groups

### 1. Quotation Form Settings (Options Page)

**Purpose**: Configure available colours, product types, and other options from WordPress admin.

**Location**: Options Page > Quotation Form Settings

**Fields**:

```
Group: Quotation Form Settings
- Tab: Product Categories
  - Repeater: Categories
    - Text: Category Name
    - Text: Category Slug
    - Image: Category Image

- Tab: Window Types
  - Repeater: Window Types
    - Text: Type Name
    - Text: Type Slug
    - Image: Type Image
    - True/False: Requires Material Selection

- Tab: Door Types
  - Repeater: Door Types
    - Text: Type Name
    - Text: Type Slug
    - Image: Type Image
    - True/False: Requires Material Selection
    - Select: Material Type (standard, doorco, none)

- Tab: Bay Window Types
  - Repeater: Bay Window Types
    - Text: Type Name
    - Text: Type Slug
    - Image: Type Image
    - True/False: Requires Material Selection

- Tab: Materials
  - Repeater: Standard Materials
    - Text: Material Name
    - Text: Material Slug
    - Image: Material Image

  - Repeater: DoorCo Materials
    - Text: Material Name
    - Text: Material Slug
    - Image: Material Image

- Tab: Styles
  - Repeater: Configuration Styles
    - Text: Style Code (e.g., W1, W2, etc.)
    - Text: Style Name
    - Image: Style Diagram

- Tab: Colours
  - Repeater: Available Colours
    - Text: Colour Name
    - Select: Category (Base, Colour)
    - Color Picker: Hex Code

- Tab: Options
  - Repeater: Cill Options
    - Text: Cill Label
    - Text: Cill Value

  - Repeater: Glazing Types
    - Text: Glazing Type Label
    - Text: Glazing Type Value

  - Repeater: Glazing Features
    - Text: Feature Label
    - Text: Feature Value

  - Repeater: Hardware Colours
    - Text: Hardware Colour Label
    - Text: Hardware Colour Value
```

### 2. Quotation Submissions (Custom Post Type)

**Purpose**: Store submitted quotations in WordPress database.

**Location**: Custom Post Type: `quotation_submission`

**Fields**:

```
Group: Quotation Submission Details
- Tab: Customer Information
  - Text: Customer Name
  - Email: Customer Email
  - Text: Customer Phone
  - Textarea: Customer Address
  - Text: Postcode
  - Select: Preferred Contact Method (email, phone, either)
  - Textarea: Additional Notes
  - Date Picker: Submission Date

- Tab: Basket Items
  - Repeater: Items
    - Text: Product Category
    - Text: Product Type
    - Text: Material
    - Text: Style Code
    - Number: Width (mm)
    - Number: Height (mm)
    - Text: Cill Option
    - Text: Inside Colour
    - Text: Outside Colour
    - Text: Glazing Type
    - Text: Glazing Features
    - Text: Hardware Colour
    - Text: Location
    - Image: Uploaded Frame Image

- Tab: Status
  - Select: Quote Status (pending, in-progress, completed, sent, cancelled)
  - Wysiwyg: Admin Notes
  - Number: Quote Price
  - File: Quote PDF
```

## ACF JSON Export (Optional)

If you create the above field groups in ACF Pro, you can export them as JSON:

1. Go to **Custom Fields > Tools**
2. Select the field groups
3. Click **Export to PHP/JSON**
4. Save the JSON file to: `/wp-content/themes/quotation-form/acf-json/`

The JSON files will be automatically synced when you activate the theme on another site.

## Register Custom Post Type (Optional)

Add this to your theme's `functions.php` or a custom plugin:

```php
// Register Quotation Submissions CPT
function register_quotation_submissions_cpt() {
    $args = array(
        'labels' => array(
            'name' => 'Quotation Submissions',
            'singular_name' => 'Quotation',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Quotation',
            'edit_item' => 'Edit Quotation',
            'view_item' => 'View Quotation',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-list-view',
        'supports' => array('title'),
        'has_archive' => false,
        'rewrite' => false,
    );

    register_post_type('quotation_submission', $args);
}
add_action('init', 'register_quotation_submissions_cpt');
```

## Modifying the Form to Use ACF Options

If you create the ACF options page, you can modify the JavaScript to load options dynamically:

```javascript
// Example: Load colours from ACF instead of hardcoded array
colours: <?php echo json_encode(get_field('available_colours', 'option')); ?>,

// Example: Load product types from ACF
productTypes: <?php echo json_encode(get_field('window_types', 'option')); ?>,
```

## Notes

- The current implementation **does not require ACF** to work
- All data is currently handled in JavaScript and submitted via AJAX
- You can modify `functions.php` to save submissions to a custom database table or as custom posts
- ACF is **optional** and only needed if you want admin-configurable options or structured database storage

## Backend Storage Options

### Option 1: Email Only (Current Implementation)
- Submissions are sent via email
- No database storage
- Simple and lightweight

### Option 2: Save as Custom Posts (Recommended)
- Create custom post type `quotation_submission`
- Use ACF fields to store structured data
- Allows admin to manage quotes in WordPress
- Can generate PDF quotes from admin

### Option 3: Save to Custom Database Table
- Create custom database table
- Store submissions with full item details
- Better performance for high volume
- Requires custom database queries

Choose the option that best fits your needs!
