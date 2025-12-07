<?php
/**
 * Quotation Form Template
 * Used by the [quotation_form] shortcode
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$plugin_url = QUOTATION_FORM_PLUGIN_URL;

// Get ACF data
$categories = function_exists('get_field') ? get_field('product_categories', 'option') : array();
$product_types = function_exists('get_field') ? get_field('product_types', 'option') : array();
$materials = function_exists('get_field') ? get_field('materials', 'option') : array();
$styles = function_exists('get_field') ? get_field('styles', 'option') : array();
$cill_options = function_exists('get_field') ? get_field('cill_options', 'option') : array();
$patterns_library = function_exists('get_field') ? get_field('patterns', 'option') : array();
$glazing_types_raw = function_exists('get_field') ? get_field('glazing_types', 'option') : array();
$glazing_features = function_exists('get_field') ? get_field('glazing_features', 'option') : array();
$hardware_colours = function_exists('get_field') ? get_field('hardware_colours', 'option') : array();

// Process glazing types to map pattern values to full pattern data
$plugin_instance = Quotation_Form_Plugin::get_instance();
$glazing_types = method_exists($plugin_instance, 'process_glazing_types_with_patterns')
    ? $plugin_instance->process_glazing_types_with_patterns($glazing_types_raw, $patterns_library)
    : $glazing_types_raw;

// Group product types by category
$window_types = array();
$door_types = array();
$bay_types = array();

if (!empty($product_types) && is_array($product_types)) {
    foreach ($product_types as $type) {
        $category = isset($type['category']) ? $type['category'] : 'windows';
        if ($category === 'windows') {
            $window_types[] = $type;
        } elseif ($category === 'doors') {
            $door_types[] = $type;
        } elseif ($category === 'bay-windows') {
            $bay_types[] = $type;
        }
    }
}

// Materials are now unified - no grouping needed

// Fallback to hardcoded if ACF data empty
$use_acf = !empty($categories);

// Hardcoded fallbacks
if (!$use_acf) {
    $categories = array(
        array('name' => 'Windows', 'slug' => 'windows', 'image' => array('url' => $plugin_url . 'assets/images/windows.jpg')),
        array('name' => 'Doors', 'slug' => 'doors', 'image' => array('url' => $plugin_url . 'assets/images/doors.jpg')),
        array('name' => 'Bay Windows', 'slug' => 'bay-windows', 'image' => array('url' => $plugin_url . 'assets/images/bay-windows.jpg')),
    );

    $window_types = array(
        array('name' => 'Casement Windows', 'slug' => 'casement', 'requires_material' => true, 'material_type' => 'standard', 'image' => array('url' => $plugin_url . 'assets/images/casement-windows.jpg')),
        array('name' => 'Flush Windows', 'slug' => 'flush', 'requires_material' => true, 'material_type' => 'standard', 'image' => array('url' => $plugin_url . 'assets/images/flush-windows.jpg')),
        array('name' => 'Tilt & Turn', 'slug' => 'tilt-turn', 'requires_material' => true, 'material_type' => 'standard', 'image' => array('url' => $plugin_url . 'assets/images/tilt-turn.jpg')),
        array('name' => 'Reversible Window', 'slug' => 'reversible', 'requires_material' => false, 'image' => array('url' => $plugin_url . 'assets/images/reversible.jpg')),
        array('name' => 'Sash Windows', 'slug' => 'sash', 'requires_material' => false, 'image' => array('url' => $plugin_url . 'assets/images/sash-windows.jpg')),
    );

    $door_types = array(
        array('name' => 'BiFold Doors', 'slug' => 'bifold', 'requires_material' => false, 'image' => array('url' => $plugin_url . 'assets/images/bifold-doors.jpg')),
        array('name' => 'French Doors', 'slug' => 'french', 'requires_material' => true, 'material_type' => 'standard', 'image' => array('url' => $plugin_url . 'assets/images/french-doors.jpg')),
        array('name' => 'Glazed Doors', 'slug' => 'glazed', 'requires_material' => true, 'material_type' => 'standard', 'image' => array('url' => $plugin_url . 'assets/images/glazed-doors.jpg')),
        array('name' => 'Sliding Doors', 'slug' => 'sliding', 'requires_material' => true, 'material_type' => 'standard', 'image' => array('url' => $plugin_url . 'assets/images/sliding-doors.jpg')),
        array('name' => 'DoorCo', 'slug' => 'doorco', 'requires_material' => true, 'material_type' => 'doorco', 'image' => array('url' => $plugin_url . 'assets/images/doorco.jpg')),
    );

    $bay_types = array(
        array('name' => 'Casement Bays', 'slug' => 'casement-bays', 'requires_material' => true, 'material_type' => 'standard', 'image' => array('url' => $plugin_url . 'assets/images/casement-bays.jpg')),
        array('name' => 'Flush Bays', 'slug' => 'flush-bays', 'requires_material' => false, 'image' => array('url' => $plugin_url . 'assets/images/flush-bays.jpg')),
        array('name' => 'Aluminium Casement Bays', 'slug' => 'aluminium-casement-bays', 'requires_material' => false, 'image' => array('url' => $plugin_url . 'assets/images/aluminium-casement-bays.jpg')),
    );

    $materials = array(
        array('name' => 'PVCu Chamfered', 'slug' => 'pvcu-chamfered', 'image' => array('url' => $plugin_url . 'assets/images/pvcu-chamfered.jpg')),
        array('name' => 'PVCu Decorative', 'slug' => 'pvcu-decorative', 'image' => array('url' => $plugin_url . 'assets/images/pvcu-decorative.jpg')),
        array('name' => 'Aluminium', 'slug' => 'aluminium', 'image' => array('url' => $plugin_url . 'assets/images/aluminium.jpg')),
        array('name' => 'Traditional', 'slug' => 'traditional', 'image' => array('url' => $plugin_url . 'assets/images/doorco-traditional.jpg')),
        array('name' => 'Designer', 'slug' => 'designer', 'image' => array('url' => $plugin_url . 'assets/images/doorco-designer.jpg')),
        array('name' => 'Contemporary', 'slug' => 'contemporary', 'image' => array('url' => $plugin_url . 'assets/images/doorco-contemporary.jpg')),
    );

    $styles = array();
    for ($i = 1; $i <= 12; $i++) {
        $styles[] = array('name' => 'Style W' . $i, 'slug' => 'w' . $i, 'image' => array('url' => $plugin_url . 'assets/images/styles/w' . $i . '.jpg'));
    }
}
?>

<div class="quotation-form-container">

    <!-- Progress Indicator -->
    <div class="progress-indicator">
        <div class="progress-step active" data-step="1">
            <span class="step-number">1</span>
            <span class="step-title">Product</span>
        </div>
        <div class="progress-step" data-step="2">
            <span class="step-number">2</span>
            <span class="step-title">Style</span>
        </div>
        <div class="progress-step" data-step="3">
            <span class="step-number">3</span>
            <span class="step-title">Configuration</span>
        </div>
        <div class="progress-step" data-step="4">
            <span class="step-number">4</span>
            <span class="step-title">Review</span>
        </div>
    </div>

    <!-- Multi-Step Form -->
    <form id="quotation-form" class="multi-step-form">

        <!-- STEP 1: PRODUCT -->
        <div class="form-step active" data-step="1">

            <!-- Sub-Step 1A: Product Category -->
            <div class="sub-step active" data-substep="1a">
                <h2 class="quotation-heading">Select Product Category</h2>
                <div class="card-grid category-grid">
                    <?php foreach ($categories as $category):
                        $image_url = isset($category['image']['url']) ? $category['image']['url'] : '';
                        $name = isset($category['name']) ? $category['name'] : '';
                        $slug = isset($category['slug']) ? $category['slug'] : '';
                    ?>
                    <div class="image-card" data-category="<?php echo esc_attr($slug); ?>">
                        <div class="card-image">
                            <?php if ($image_url): ?>
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($name); ?>">
                            <?php endif; ?>
                        </div>
                        <h3 class="quotation-subheading"><?php echo esc_html($name); ?></h3>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sub-Step 1B: Windows Type Selection -->
            <div class="sub-step" data-substep="1b-windows" data-parent-category="windows">
                <h2 class="quotation-heading">Select Type</h2>
                <div class="card-grid type-grid">
                    <?php foreach ($window_types as $type):
                        $image_url = isset($type['image']['url']) ? $type['image']['url'] : '';
                        $name = isset($type['name']) ? $type['name'] : '';
                        $slug = isset($type['slug']) ? $type['slug'] : '';
                    ?>
                    <div class="image-card" data-type="<?php echo esc_attr($slug); ?>">
                        <div class="card-image">
                            <?php if ($image_url): ?>
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($name); ?>">
                            <?php endif; ?>
                        </div>
                        <h3 class="quotation-subheading"><?php echo esc_html($name); ?></h3>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sub-Step 1B: Doors Type Selection -->
            <div class="sub-step" data-substep="1b-doors" data-parent-category="doors">
                <h2 class="quotation-heading">Select Type</h2>
                <div class="card-grid type-grid">
                    <?php foreach ($door_types as $type):
                        $image_url = isset($type['image']['url']) ? $type['image']['url'] : '';
                        $name = isset($type['name']) ? $type['name'] : '';
                        $slug = isset($type['slug']) ? $type['slug'] : '';
                    ?>
                    <div class="image-card" data-type="<?php echo esc_attr($slug); ?>">
                        <div class="card-image">
                            <?php if ($image_url): ?>
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($name); ?>">
                            <?php endif; ?>
                        </div>
                        <h3 class="quotation-subheading"><?php echo esc_html($name); ?></h3>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sub-Step 1B: Bay Windows Type Selection -->
            <div class="sub-step" data-substep="1b-bay-windows" data-parent-category="bay-windows">
                <h2 class="quotation-heading">Select Type</h2>
                <div class="card-grid type-grid">
                    <?php foreach ($bay_types as $type):
                        $image_url = isset($type['image']['url']) ? $type['image']['url'] : '';
                        $name = isset($type['name']) ? $type['name'] : '';
                        $slug = isset($type['slug']) ? $type['slug'] : '';
                    ?>
                    <div class="image-card" data-type="<?php echo esc_attr($slug); ?>">
                        <div class="card-image">
                            <?php if ($image_url): ?>
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($name); ?>">
                            <?php endif; ?>
                        </div>
                        <h3 class="quotation-subheading"><?php echo esc_html($name); ?></h3>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sub-Step 1C: Material Selection -->
            <div class="sub-step" data-substep="1c-material">
                <h2 class="quotation-heading">Select Material</h2>
                <div class="card-grid material-grid">
                    <?php foreach ($materials as $material):
                        $image_url = isset($material['image']['url']) ? $material['image']['url'] : '';
                        $name = isset($material['name']) ? $material['name'] : '';
                        $slug = isset($material['slug']) ? $material['slug'] : '';

                        // Get availability data (array of type slugs)
                        $available_types = isset($material['available_types']) ? $material['available_types'] : array();
                    ?>
                    <div class="image-card"
                         data-material="<?php echo esc_attr($slug); ?>"
                         data-available-types="<?php echo esc_attr(json_encode($available_types)); ?>">
                        <div class="card-image">
                            <?php if ($image_url): ?>
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($name); ?>">
                            <?php endif; ?>
                        </div>
                        <h3 class="quotation-subheading"><?php echo esc_html($name); ?></h3>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- STEP 2: STYLE -->
        <div class="form-step" data-step="2">
            <h2 class="quotation-heading">Select Configuration Style</h2>
            <div class="card-grid style-grid">
                <?php
                if (!empty($styles)) {
                    foreach ($styles as $style):
                        $image_url = isset($style['image']['url']) ? $style['image']['url'] : '';
                        $slug = isset($style['slug']) ? $style['slug'] : '';
                        $name = isset($style['name']) ? $style['name'] : '';

                        // Get availability data (array of type slugs)
                        $available_types = isset($style['available_types']) ? $style['available_types'] : array();
                    ?>
                    <div class="image-card style-card"
                         data-style="<?php echo esc_attr($slug); ?>"
                         data-available-types="<?php echo esc_attr(json_encode($available_types)); ?>">
                        <div class="card-image">
                            <?php if ($image_url): ?>
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($name); ?>">
                            <?php endif; ?>
                        </div>
                        <h3 class="quotation-subheading"><?php echo esc_html($name); ?></h3>
                    </div>
                    <?php
                    endforeach;
                } else {
                    // Fallback to W1-W12 if no styles defined
                    for ($i = 1; $i <= 12; $i++): ?>
                    <div class="image-card style-card" data-style="w<?php echo $i; ?>">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/styles/w<?php echo $i; ?>.jpg" alt="Style W<?php echo $i; ?>">
                        </div>
                        <h3 class="quotation-subheading">Style W<?php echo $i; ?></h3>
                    </div>
                    <?php endfor;
                }
                ?>
            </div>
        </div>

        <!-- STEP 3: CONFIGURATION -->
        <div class="form-step" data-step="3">
            <div class="configuration-container">

                <!-- Left Side: Visual Preview -->
                <div class="configuration-preview">
                    <h3 class="quotation-subheading">Preview</h3>
                    <div class="preview-image">
                        <img id="style-preview" src="" alt="Selected Style">
                    </div>
                    <div class="preview-details">
                        <p><strong>Category:</strong> <span id="preview-category"></span></p>
                        <p><strong>Type:</strong> <span id="preview-type"></span></p>
                        <p><strong>Material:</strong> <span id="preview-material"></span></p>
                        <p><strong>Style:</strong> <span id="preview-style"></span></p>
                    </div>
                </div>

                <!-- Right Side: Configuration Form -->
                <div class="configuration-form">
                    <h2 class="quotation-heading">Configure Your Product</h2>

                    <div class="form-group">
                        <label for="width">Width (mm)</label>
                        <input type="number" id="width" name="width" min="200" max="4000">
                        <span class="field-hint">Min: 200mm - Max: 4000mm</span>
                    </div>

                    <div class="form-group">
                        <label for="height">Height (mm)</label>
                        <input type="number" id="height" name="height" min="200" max="3200">
                        <span class="field-hint">Min: 200mm - Max: 3200mm</span>
                    </div>

                    <div class="form-group">
                        <label for="cill">Cill</label>
                        <select id="cill" name="cill">
                            <option value="">Select Cill</option>
                            <?php
                            if (!empty($cill_options) && is_array($cill_options)) {
                                foreach ($cill_options as $option) {
                                    $label = isset($option['label']) ? $option['label'] : '';
                                    $value = isset($option['value']) ? $option['value'] : '';
                                    if ($label && $value) {
                                        echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
                                    }
                                }
                            } else {
                                // Fallback hardcoded options
                                echo '<option value="85mm">85 mm</option>';
                                echo '<option value="150mm">150 mm</option>';
                                echo '<option value="180mm">180 mm</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Section Colour</label>

                        <div class="colour-selection">
                            <h4>Inside Colour</h4>
                            <div class="colour-picker-container">
                                <input type="text" id="inside-colour-search" placeholder="Search colours...">
                                <div class="colour-grid" id="inside-colour-grid">
                                    <!-- Colours will be populated by JavaScript -->
                                </div>
                                <input type="hidden" id="inside-colour" name="inside_colour">
                                <p class="colour-selection-display">You have chosen: <strong id="inside-colour-name">None</strong></p>
                            </div>
                        </div>

                        <div class="colour-selection">
                            <h4>Outside Colour</h4>
                            <div class="colour-picker-container">
                                <input type="text" id="outside-colour-search" placeholder="Search colours...">
                                <div class="colour-grid" id="outside-colour-grid">
                                    <!-- Colours will be populated by JavaScript -->
                                </div>
                                <input type="hidden" id="outside-colour" name="outside_colour">
                                <p class="colour-selection-display">You have chosen: <strong id="outside-colour-name">None</strong></p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Glazing Type</label>
                        <div class="glazing-type-selection">
                            <div class="glazing-type-grid" id="glazing-type-grid">
                                <?php
                                if (!empty($glazing_types) && is_array($glazing_types)) {
                                    foreach ($glazing_types as $type) {
                                        $label = isset($type['label']) ? $type['label'] : '';
                                        $value = isset($type['value']) ? $type['value'] : '';
                                        $icon = isset($type['icon']['url']) ? $type['icon']['url'] : '';
                                        $patterns = isset($type['patterns']) ? $type['patterns'] : array();

                                        if ($label && $value) {
                                            ?>
                                            <div class="glazing-type-card" data-glazing-type="<?php echo esc_attr($value); ?>" data-patterns='<?php echo esc_attr(json_encode($patterns)); ?>'>
                                                <div class="glazing-type-icon">
                                                    <?php if ($icon): ?>
                                                        <img src="<?php echo esc_url($icon); ?>" alt="<?php echo esc_attr($label); ?>">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="glazing-type-label"><?php echo esc_html($label); ?></div>
                                            </div>
                                            <?php
                                        }
                                    }
                                } else {
                                    // Fallback hardcoded options
                                    $fallback_types = array(
                                        array('label' => 'Low E (Double)', 'value' => 'low-e-double', 'color' => '#7CB342'),
                                        array('label' => 'Low E (Triple)', 'value' => 'low-e-triple', 'color' => '#7CB342'),
                                        array('label' => 'High Security', 'value' => 'high-security', 'color' => '#2196F3'),
                                        array('label' => 'Acoustic Glazing', 'value' => 'acoustic', 'color' => '#E53935'),
                                        array('label' => 'Self Cleaning', 'value' => 'self-cleaning', 'color' => '#00BCD4'),
                                    );
                                    foreach ($fallback_types as $type) {
                                        ?>
                                        <div class="glazing-type-card" data-glazing-type="<?php echo esc_attr($type['value']); ?>" data-patterns="[]">
                                            <div class="glazing-type-icon" style="background-color: <?php echo esc_attr($type['color']); ?>;"></div>
                                            <div class="glazing-type-label"><?php echo esc_html($type['label']); ?></div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            <input type="hidden" id="glazing-type" name="glazing_type">
                            <p class="glazing-type-selection-display">You have chosen: <strong id="glazing-type-name">None</strong></p>
                        </div>
                    </div>

                    <div class="form-group" id="glazing-pattern-group" style="display: none;">
                        <label>Select a Pattern</label>
                        <div class="glazing-pattern-selection">
                            <div class="glazing-pattern-grid" id="glazing-pattern-grid">
                                <!-- Patterns will be populated by JavaScript based on selected type -->
                            </div>
                            <input type="hidden" id="glazing-pattern" name="glazing_pattern">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="glazing-features">Glazing Features</label>
                        <div class="glazing-features-selection">
                            <div class="glazing-features-picker-container">
                                <input type="text" id="glazing-features-search" placeholder="Search glazing features...">
                                <div class="glazing-features-grid" id="glazing-features-grid">
                                    <!-- Glazing features will be populated by JavaScript -->
                                </div>
                                <input type="hidden" id="glazing-features" name="glazing_features">
                                <p class="glazing-features-selection-display">Selected: <strong id="glazing-features-name">Not Required</strong></p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Hardware Colour</label>
                        <div class="hardware-colour-selection">
                            <div class="hardware-colour-grid" id="hardware-colour-grid">
                                <!-- Hardware colours will be populated by JavaScript -->
                            </div>
                            <input type="hidden" id="hardware-colour" name="hardware_colour">
                            <p class="colour-selection-display">You have chosen: <strong id="hardware-colour-name">None</strong></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="frame-image">Attach Image (Optional)</label>
                        <input type="file" id="frame-image" name="frame_image" accept="image/*">
                        <span class="field-hint">Upload frame image for more accurate pricing</span>
                    </div>

                    <div class="configuration-actions">
                        <button type="button" class="btn btn-secondary" id="restart-btn">Restart</button>
                        <button type="button" class="btn btn-primary" id="add-to-basket-btn">Add to Basket</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- BASKET REVIEW -->
        <div class="form-step basket-review" data-step="basket">
            <h2 class="quotation-heading">Your Basket</h2>
            <p class="basket-count">Your basket contains <strong id="basket-item-count">0</strong> item(s)</p>

            <div id="basket-items-container">
                <!-- Basket items will be populated by JavaScript -->
            </div>

            <div class="basket-actions">
                <button type="button" class="btn btn-secondary" id="add-more-items-btn">Add Item</button>
                <button type="button" class="btn btn-primary" id="proceed-to-review-btn">Next</button>
            </div>
        </div>

        <!-- STEP 4: REVIEW & SUBMISSION -->
        <div class="form-step" data-step="4">
            <h2 class="quotation-heading">Contact Information</h2>

            <div class="review-container">
                <div class="customer-details-form">
                    <div class="form-group">
                        <label for="customer-name">Full Name *</label>
                        <input type="text" id="customer-name" name="customer_name" required>
                    </div>

                    <div class="form-group">
                        <label for="customer-email">Email *</label>
                        <input type="email" id="customer-email" name="customer_email" required>
                    </div>

                    <div class="form-group">
                        <label for="customer-phone">Phone *</label>
                        <input type="tel" id="customer-phone" name="customer_phone" required>
                    </div>

                    <div class="form-group">
                        <label for="customer-address">Address</label>
                        <textarea id="customer-address" name="customer_address" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="customer-postcode">Postcode</label>
                        <input type="text" id="customer-postcode" name="customer_postcode">
                    </div>

                    <div class="form-group">
                        <label for="preferred-contact">Preferred Contact Method</label>
                        <select id="preferred-contact" name="preferred_contact">
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                            <option value="either">Either</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="additional-notes">Additional Notes</label>
                        <textarea id="additional-notes" name="additional_notes" rows="4" placeholder="Any special requirements or questions?"></textarea>
                    </div>
                </div>

                <div class="order-summary">
                    <h3 class="quotation-subheading">Order Summary</h3>
                    <div id="final-basket-summary">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="form-navigation">
            <button type="button" class="btn btn-secondary" id="prev-btn" style="display: none;">Back</button>
            <button type="button" class="btn btn-primary" id="next-btn" style="display: none;">Next</button>
            <button type="submit" class="btn btn-primary" id="submit-btn" style="display: none;">Submit Quote Request</button>
        </div>

    </form>

    <!-- Edit Item Modal -->
    <div id="edit-item-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h3 class="quotation-subheading">Edit Item</h3>
            <div id="edit-item-content">
                <!-- Will be populated dynamically -->
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="button" class="btn btn-primary modal-apply">Apply Changes</button>
            </div>
        </div>
    </div>

</div>
