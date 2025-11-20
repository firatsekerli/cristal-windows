# ACF Pro Setup Guide - Complete Implementation

This guide will help you set up the plugin with ACF Pro integration for the quotation form.

## ✅ What's Been Implemented

### 1. **Quotation CPT Integration** ✓
- All form submissions automatically save to your `quotation` custom post type
- Customer details stored in ACF fields
- Basket items stored in ACF repeater
- Quote management fields (status, price, notes, PDF)

### 2. **ACF Options Page** ✓
- Settings page registered: **Quote Settings**
- Manage all products, colours, and options from WordPress admin
- Tab-based interface for easy management

### 3. **ACF JSON Files** ✓
- Ready-to-import field group definitions
- Located in `/acf-json/` folder
- Auto-sync when imported

---

## 🚀 Installation Steps

### Step 1: Install ACF Pro

1. **Install ACF Pro plugin** (if not already installed)
   - Upload and activate ACF Pro
   - Enter your license key

### Step 2: Import ACF Field Groups

You have **2 options**:

#### **Option A: Automatic Sync (Recommended)**

1. Go to **Custom Fields > Tools**
2. Look for the **"Sync available"** tab
3. You should see:
   - `Quotation Submission Details`
   - `Quotation Form Settings`
4. Select both field groups
5. Click **"Sync"**

✅ **That's it!** Field groups are now active.

#### **Option B: Manual Import (if sync doesn't work)**

1. Go to **Custom Fields > Tools**
2. Click on **"Import"** tab
3. Upload these JSON files (one at a time):
   - `/acf-json/group_quotation_submission.json`
   - `/acf-json/group_quotation_settings.json`
4. Click **"Import JSON"**

---

## 📋 Field Groups Overview

### Field Group 1: **Quotation Submission Details**

**Location:** Quotation CPT (quotation post type)

**Fields:**

**Tab: Customer Information**
- Customer Name
- Customer Email
- Customer Phone
- Customer Address
- Postcode
- Preferred Contact Method
- Additional Notes
- Submission Date

**Tab: Basket Items** (Repeater)
- Category
- Product Type
- Material
- Style
- Width (mm)
- Height (mm)
- Cill
- Inside Colour
- Outside Colour
- Glazing Type
- Glazing Features
- Hardware Colour
- Location

**Tab: Quote Management**
- Quote Status (dropdown)
- Quote Price (£)
- Quote Sent Date
- Follow-up Date
- Admin Notes
- Quote PDF (file upload)

---

### Field Group 2: **Quotation Form Settings**

**Location:** Options Page (`Quote Settings`)

**Tabs:**

1. **Product Categories**
   - Repeater: Name, Slug, Image, Order

2. **Window Types**
   - Repeater: Name, Slug, Image, Requires Material, Material Type, Order

3. **Door Types**
   - Repeater: Name, Slug, Image, Requires Material, Material Type, Order

4. **Bay Window Types**
   - Repeater: Name, Slug, Image, Requires Material, Material Type, Order

5. **Materials**
   - Standard Materials (repeater)
   - DoorCo Materials (repeater)

6. **Styles**
   - Repeater: Style Code, Name, Image, Order

7. **Colours**
   - Repeater: Colour Name, Category, Hex Code

8. **Form Options**
   - Cill Options (repeater)
   - Glazing Types (repeater)
   - Glazing Features (repeater)
   - Hardware Colours (repeater)

9. **Email Settings**
   - Email Recipients (textarea - one per line)
   - Email Subject
   - Send Customer Confirmation (yes/no)

---

## 🎯 Next Steps

### 1. **Verify Installation**

Go to **Custom Fields** and check you see:
- ✅ Quotation Submission Details
- ✅ Quotation Form Settings

### 2. **Configure Settings** (Optional but Recommended)

Go to **Quote Settings** in WordPress admin sidebar:

1. **Add Product Categories:**
   - Click "Add Category"
   - Name: Windows
   - Slug: windows
   - Upload image
   - Order: 1
   - Repeat for Doors and Bay Windows

2. **Add Window Types:**
   - Add your window types with images
   - Set "Requires Material" to Yes/No
   - Choose Material Type (Standard/None)

3. **Add Materials:**
   - Add standard materials (PVCu Chamfered, Aluminium, etc.)
   - Add DoorCo materials if needed

4. **Add Styles:**
   - Style Code: W1, W2, etc.
   - Upload style diagrams

5. **Add Colours:**
   - Add all your colour options with hex codes

6. **Configure Email Settings:**
   - Add email recipients (one per line)
   - Customize subject line
   - Enable/disable customer confirmation

### 3. **Test Form Submission**

1. Visit your quote page
2. Fill out the form
3. Submit a test quotation
4. Check:
   - ✅ New post appears in **Quotations**
   - ✅ All fields populated correctly
   - ✅ Email received
   - ✅ Customer confirmation sent (if enabled)

---

## 🔍 Where to Find Things

### View Quotations
- Go to **Quotations** in WordPress admin sidebar
- You'll see all submissions with customer names and dates

### Edit a Quotation
- Click on any quotation
- You'll see 3 tabs:
  1. Customer Information
  2. Basket Items (all products configured)
  3. Quote Management (status, price, notes, PDF)

### Manage Products & Settings
- Go to **Quote Settings** in sidebar
- Use tabs to manage different aspects
- Changes reflect immediately on frontend

### Email Settings
- **Quote Settings > Email Settings** tab
- Add multiple recipients (one per line)
- Customize email subject
- Toggle customer confirmation emails

---

## 💡 Current Status

### ✅ What's Working Now

1. **Form submissions save to Quotation CPT** ✓
2. **All customer data stored in ACF fields** ✓
3. **Basket items stored in ACF repeater** ✓
4. **Email notifications with admin link** ✓
5. **Customer confirmation emails** ✓
6. **Quote management fields ready** ✓
7. **ACF Options page registered** ✓

### 🔧 What's Still Using Hardcoded Data

Currently, the **frontend form** still uses hardcoded product types, colours, and options.

**Next Phase:** Update frontend to pull from ACF Settings
- Template will be updated to loop through ACF repeaters
- JavaScript will use ACF config data
- This makes the form **fully dynamic** and **admin-manageable**

**For now:** Form works perfectly with hardcoded options, and all submissions save properly to your Quotation CPT with ACF fields.

---

## 📊 Database Storage

### How Data is Saved

When a form is submitted:

1. **Creates post** in `quotation` CPT
   - Title: `Customer Name - Date`
   - Status: Published

2. **Saves customer data** to ACF fields:
   ```
   customer_name
   customer_email
   customer_phone
   customer_address
   customer_postcode
   preferred_contact
   additional_notes
   submission_date
   ```

3. **Saves basket items** to ACF repeater:
   ```
   basket_items (repeater)
     ├── category
     ├── type_name
     ├── material_name
     ├── style_name
     ├── width
     ├── height
     ├── cill
     ├── inside_colour
     ├── outside_colour
     ├── glazing_type
     ├── glazing_features
     ├── hardware_colour
     └── location
   ```

4. **Sets quote status** to `pending`

### Viewing Data

**In WordPress Admin:**
- Go to **Quotations**
- Click any quotation
- All ACF fields display beautifully
- Edit any field inline
- Add quote price, notes, status, etc.

**Via ACF Functions:**
```php
// Get customer name
$name = get_field('customer_name', $post_id);

// Get all basket items
$items = get_field('basket_items', $post_id);

// Get quote status
$status = get_field('quote_status', $post_id);
```

---

## 🎨 Customization

### Add More Quote Statuses

1. Go to **Custom Fields**
2. Find **"Quotation Submission Details"**
3. Click **Edit**
4. Find **"Quote Status"** field
5. Add more options to the dropdown

### Add More Fields

1. Go to **Custom Fields**
2. Edit the field group
3. Add new fields as needed
4. They'll appear when editing quotations

### Customize Email Template

Edit the `send_email_notification()` method in `quotation-form.php` to customize email format.

---

## ❓ Troubleshooting

### Field groups don't appear

**Solution:**
- Make sure ACF Pro is activated
- Check `/acf-json/` folder exists
- Try manual import via Custom Fields > Tools > Import

### Submissions not saving to ACF fields

**Check:**
- ACF Pro is active
- Field groups are synced/imported
- Custom post type slug is `quotation` (not `quotations`)

### Can't see Quote Settings menu

**Solution:**
- Make sure ACF Pro is activated
- The menu appears after ACF Pro is active
- Try deactivating and reactivating the plugin

### Images not uploading

**Solution:**
- Check WordPress media library permissions
- Ensure you're uploading supported formats (JPG, PNG)
- Check file size limits in WordPress

---

## 🎉 Success Checklist

- [ ] ACF Pro installed and activated
- [ ] Field groups synced/imported
- [ ] Can see "Quotation Submission Details" field group
- [ ] Can see "Quotation Form Settings" field group
- [ ] Can see "Quote Settings" menu in admin
- [ ] Test form submission creates quotation post
- [ ] All ACF fields populate correctly
- [ ] Email notification received
- [ ] Can edit quotation in admin
- [ ] Can change quote status and add notes

---

## 📞 Need Help?

If you encounter any issues:

1. Check this guide thoroughly
2. Verify ACF Pro is active and licensed
3. Check WordPress debug log
4. Ensure custom post type is registered as `quotation`

---

## 🚀 Phase 2: Dynamic Frontend (Coming Next)

Once you're happy with the current setup, we can implement:

- Dynamic product loading from ACF Settings
- Admin-manageable product types and colours
- No code editing needed to add/remove products
- Fully client-ready admin interface

**For now, enjoy your fully functional form with ACF-powered backend storage!** 🎊
