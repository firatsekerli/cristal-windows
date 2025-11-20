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
                <h2>Select Product Category</h2>
                <div class="card-grid category-grid">
                    <div class="image-card" data-category="windows">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/windows.jpg" alt="Windows">
                        </div>
                        <h3>Windows</h3>
                    </div>
                    <div class="image-card" data-category="doors">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/doors.jpg" alt="Doors">
                        </div>
                        <h3>Doors</h3>
                    </div>
                    <div class="image-card" data-category="bay-windows">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/bay-windows.jpg" alt="Bay Windows">
                        </div>
                        <h3>Bay Windows</h3>
                    </div>
                </div>
            </div>

            <!-- Sub-Step 1B: Windows Type Selection -->
            <div class="sub-step" data-substep="1b-windows" data-parent-category="windows">
                <h2>Select Window Type</h2>
                <div class="card-grid type-grid">
                    <div class="image-card" data-type="casement" data-requires-material="true">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/casement-windows.jpg" alt="Casement Windows">
                        </div>
                        <h3>Casement Windows</h3>
                    </div>
                    <div class="image-card" data-type="flush" data-requires-material="true">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/flush-windows.jpg" alt="Flush Windows">
                        </div>
                        <h3>Flush Windows</h3>
                    </div>
                    <div class="image-card" data-type="tilt-turn" data-requires-material="true">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/tilt-turn.jpg" alt="Tilt & Turn">
                        </div>
                        <h3>Tilt & Turn</h3>
                    </div>
                    <div class="image-card" data-type="reversible" data-requires-material="false">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/reversible.jpg" alt="Reversible Window">
                        </div>
                        <h3>Reversible Window</h3>
                    </div>
                    <div class="image-card" data-type="sash" data-requires-material="false">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/sash-windows.jpg" alt="Sash Windows">
                        </div>
                        <h3>Sash Windows</h3>
                    </div>
                </div>
            </div>

            <!-- Sub-Step 1B: Doors Type Selection -->
            <div class="sub-step" data-substep="1b-doors" data-parent-category="doors">
                <h2>Select Door Type</h2>
                <div class="card-grid type-grid">
                    <div class="image-card" data-type="bifold" data-requires-material="false">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/bifold-doors.jpg" alt="BiFold Doors">
                        </div>
                        <h3>BiFold Doors</h3>
                    </div>
                    <div class="image-card" data-type="french" data-requires-material="true" data-material-type="standard">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/french-doors.jpg" alt="French Doors">
                        </div>
                        <h3>French Doors</h3>
                    </div>
                    <div class="image-card" data-type="glazed" data-requires-material="true" data-material-type="standard">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/glazed-doors.jpg" alt="Glazed Doors">
                        </div>
                        <h3>Glazed Doors</h3>
                    </div>
                    <div class="image-card" data-type="sliding" data-requires-material="true" data-material-type="standard">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/sliding-doors.jpg" alt="Sliding Doors">
                        </div>
                        <h3>Sliding Doors</h3>
                    </div>
                    <div class="image-card" data-type="doorco" data-requires-material="true" data-material-type="doorco">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/doorco.jpg" alt="DoorCo">
                        </div>
                        <h3>DoorCo</h3>
                    </div>
                </div>
            </div>

            <!-- Sub-Step 1B: Bay Windows Type Selection -->
            <div class="sub-step" data-substep="1b-bay-windows" data-parent-category="bay-windows">
                <h2>Select Bay Window Type</h2>
                <div class="card-grid type-grid">
                    <div class="image-card" data-type="casement-bays" data-requires-material="true" data-material-type="standard">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/casement-bays.jpg" alt="Casement Bays">
                        </div>
                        <h3>Casement Bays</h3>
                    </div>
                    <div class="image-card" data-type="flush-bays" data-requires-material="false">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/flush-bays.jpg" alt="Flush Bays">
                        </div>
                        <h3>Flush Bays</h3>
                    </div>
                    <div class="image-card" data-type="aluminium-casement-bays" data-requires-material="false">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/aluminium-casement-bays.jpg" alt="Aluminium Casement Bays">
                        </div>
                        <h3>Aluminium Casement Bays</h3>
                    </div>
                </div>
            </div>

            <!-- Sub-Step 1C: Material Selection (Standard) -->
            <div class="sub-step" data-substep="1c-material-standard">
                <h2>Select Material</h2>
                <div class="card-grid material-grid">
                    <div class="image-card" data-material="pvcu-chamfered">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/pvcu-chamfered.jpg" alt="PVCu Chamfered">
                        </div>
                        <h3>PVCu Chamfered</h3>
                    </div>
                    <div class="image-card" data-material="pvcu-decorative">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/pvcu-decorative.jpg" alt="PVCu Decorative">
                        </div>
                        <h3>PVCu Decorative</h3>
                    </div>
                    <div class="image-card" data-material="aluminium">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/aluminium.jpg" alt="Aluminium">
                        </div>
                        <h3>Aluminium</h3>
                    </div>
                </div>
            </div>

            <!-- Sub-Step 1C: Material Selection (DoorCo) -->
            <div class="sub-step" data-substep="1c-material-doorco">
                <h2>Select DoorCo Style</h2>
                <div class="card-grid material-grid">
                    <div class="image-card" data-material="traditional">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/doorco-traditional.jpg" alt="Traditional">
                        </div>
                        <h3>Traditional</h3>
                    </div>
                    <div class="image-card" data-material="designer">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/doorco-designer.jpg" alt="Designer">
                        </div>
                        <h3>Designer</h3>
                    </div>
                    <div class="image-card" data-material="contemporary">
                        <div class="card-image">
                            <img src="<?php echo $plugin_url; ?>assets/images/doorco-contemporary.jpg" alt="Contemporary">
                        </div>
                        <h3>Contemporary</h3>
                    </div>
                </div>
            </div>

        </div>

        <!-- STEP 2: STYLE -->
        <div class="form-step" data-step="2">
            <h2>Select Configuration Style</h2>
            <div class="card-grid style-grid">
                <?php for ($i = 1; $i <= 12; $i++): ?>
                <div class="image-card style-card" data-style="W<?php echo $i; ?>">
                    <div class="card-image">
                        <img src="<?php echo $plugin_url; ?>assets/images/styles/w<?php echo $i; ?>.jpg" alt="Style W<?php echo $i; ?>">
                    </div>
                    <h3>W<?php echo $i; ?></h3>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- STEP 3: CONFIGURATION -->
        <div class="form-step" data-step="3">
            <div class="configuration-container">

                <!-- Left Side: Visual Preview -->
                <div class="configuration-preview">
                    <h3>Preview</h3>
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
                    <h2>Configure Your Product</h2>

                    <div class="form-group">
                        <label for="width">Width (mm)</label>
                        <input type="number" id="width" name="width" min="200" max="4000" required>
                        <span class="field-hint">Min: 200mm - Max: 4000mm</span>
                    </div>

                    <div class="form-group">
                        <label for="height">Height (mm)</label>
                        <input type="number" id="height" name="height" min="200" max="3200" required>
                        <span class="field-hint">Min: 200mm - Max: 3200mm</span>
                    </div>

                    <div class="form-group">
                        <label for="cill">Cill</label>
                        <select id="cill" name="cill">
                            <option value="">Select Cill</option>
                            <option value="150mm">150mm Sill</option>
                            <option value="200mm">200mm Sill</option>
                            <option value="none">No Sill</option>
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
                        <label for="glazing-type">Glazing Type</label>
                        <select id="glazing-type" name="glazing_type">
                            <option value="">Select Glazing Type</option>
                            <option value="clear">Clear</option>
                            <option value="obscured">Obscured</option>
                            <option value="tinted">Tinted</option>
                            <option value="self-cleaning">Self Cleaning</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="glazing-features">Glazing Features</label>
                        <select id="glazing-features" name="glazing_features">
                            <option value="not-required">Not Required</option>
                            <option value="acoustic">Acoustic</option>
                            <option value="security">Security</option>
                            <option value="thermal">Thermal</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="hardware-colour">Hardware Colour</label>
                        <select id="hardware-colour" name="hardware_colour">
                            <option value="">Select Hardware Colour</option>
                            <option value="white">White</option>
                            <option value="chrome">Chrome</option>
                            <option value="gold">Gold</option>
                            <option value="black">Black</option>
                        </select>
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
            <h2>Your Basket</h2>
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
            <h2>Contact Information</h2>

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
                    <h3>Order Summary</h3>
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
            <h3>Edit Item</h3>
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
