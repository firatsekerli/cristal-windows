jQuery(document).ready(function($) {
    'use strict';

    // State Management
    const QuotationForm = {
        currentStep: 1,
        currentSubStep: '1a',
        basket: [],
        currentItem: {},
        editingItemId: null,

        // Available colours
        colours: [
            { name: 'White', category: 'Base', hex: '#FFFFFF' },
            { name: 'Cream', category: 'Colour', hex: '#FFFDD0' },
            { name: 'Agate Grey', category: 'Colour', hex: '#B5B5B5' },
            { name: 'Anthracite Grey', category: 'Colour', hex: '#3E3E3E' },
            { name: 'Anthracite Grey Smooth', category: 'Colour', hex: '#383838' },
            { name: 'Balmoral', category: 'Colour', hex: '#8B4513' },
            { name: 'Basalt Grey', category: 'Colour', hex: '#4A4A4A' },
            { name: 'Black', category: 'Colour', hex: '#000000' },
            { name: 'Blue', category: 'Colour', hex: '#0066CC' },
            { name: 'Chartwell Green', category: 'Colour', hex: '#3C4F3B' },
            { name: 'Dark Green', category: 'Colour', hex: '#013220' },
            { name: 'Golden Oak', category: 'Colour', hex: '#B8860B' },
            { name: 'Grey', category: 'Colour', hex: '#808080' },
            { name: 'Irish Oak', category: 'Colour', hex: '#C19A6B' },
            { name: 'Light Oak', category: 'Colour', hex: '#D4A76A' },
            { name: 'Rosewood', category: 'Colour', hex: '#65000B' },
        ],

        init: function() {
            this.bindEvents();
            this.initializeColourPickers();
            this.updateNavigationButtons();
            this.updateProgressIndicator();
        },

        bindEvents: function() {
            const self = this;

            // Category selection (Step 1A)
            $('.card-grid.category-grid .image-card').on('click', function() {
                const category = $(this).data('category');
                self.currentItem.category = category;
                self.selectCard($(this));
                self.navigateToSubStep('1b-' + category);
            });

            // Type selection (Step 1B)
            $('.card-grid.type-grid .image-card').on('click', function() {
                const type = $(this).data('type');
                const requiresMaterial = $(this).data('requires-material');
                const materialType = $(this).data('material-type') || 'standard';

                self.currentItem.type = type;
                self.currentItem.typeName = $(this).find('h3').text();
                self.selectCard($(this));

                if (requiresMaterial) {
                    // Navigate to material selection
                    if (materialType === 'doorco') {
                        self.navigateToSubStep('1c-material-doorco');
                    } else {
                        self.navigateToSubStep('1c-material-standard');
                    }
                } else {
                    // Skip material, go to Step 2 (Style)
                    self.currentItem.material = 'N/A';
                    self.navigateToStep(2);
                }
            });

            // Material selection (Step 1C)
            $('.card-grid.material-grid .image-card').on('click', function() {
                const material = $(this).data('material');
                self.currentItem.material = material;
                self.currentItem.materialName = $(this).find('h3').text();
                self.selectCard($(this));
                self.navigateToStep(2);
            });

            // Style selection (Step 2)
            $('.card-grid.style-grid .image-card').on('click', function() {
                const style = $(this).data('style');
                self.currentItem.style = style;
                self.currentItem.styleName = $(this).find('h3').text();
                self.currentItem.styleImage = $(this).find('img').attr('src');
                self.selectCard($(this));

                // Auto-navigate to configuration after brief delay
                setTimeout(function() {
                    self.navigateToStep(3);
                    self.updateConfigurationPreview();
                }, 300);
            });

            // Configuration: Add to Basket
            $('#add-to-basket-btn').on('click', function() {
                if (self.validateConfiguration()) {
                    self.addItemToBasket();
                    self.showBasketReview();
                }
            });

            // Configuration: Restart
            $('#restart-btn').on('click', function() {
                if (confirm('Are you sure you want to restart? All current item data will be lost.')) {
                    self.resetForm();
                }
            });

            // Basket: Add more items
            $('#add-more-items-btn').on('click', function() {
                self.currentItem = {};
                self.navigateToStep(1);
                self.navigateToSubStep('1a');
            });

            // Basket: Proceed to review
            $('#proceed-to-review-btn').on('click', function() {
                if (self.basket.length === 0) {
                    alert('Please add at least one item to your basket.');
                    return;
                }
                self.navigateToStep(4);
                self.updateFinalSummary();
            });

            // Navigation buttons
            $('#prev-btn').on('click', function() {
                self.navigatePrevious();
            });

            $('#next-btn').on('click', function() {
                self.navigateNext();
            });

            // Form submission
            $('#quotation-form').on('submit', function(e) {
                e.preventDefault();
                self.submitForm();
            });

            // Colour picker search
            $('#inside-colour-search, #outside-colour-search').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                const gridId = $(this).attr('id').replace('-search', '-grid');
                self.filterColours(gridId, searchTerm);
            });

            // Modal close
            $('.modal-close, .modal-cancel').on('click', function() {
                $('#edit-item-modal').hide();
            });

            // Progress indicator click
            $('.progress-step').on('click', function() {
                const step = parseInt($(this).data('step'));
                if (step < self.currentStep) {
                    self.navigateToStep(step);
                }
            });
        },

        selectCard: function($card) {
            $card.addClass('selected').siblings().removeClass('selected');
        },

        navigateToSubStep: function(substep) {
            this.currentSubStep = substep;

            // Hide all sub-steps in current step
            $('.form-step[data-step="1"] .sub-step').removeClass('active');

            // Show the target sub-step
            $('.sub-step[data-substep="' + substep + '"]').addClass('active');

            this.updateNavigationButtons();
        },

        navigateToStep: function(step) {
            this.currentStep = step;

            // Handle basket vs regular steps
            if (step === 'basket') {
                $('.form-step').removeClass('active');
                $('.form-step.basket-review').addClass('active');
            } else {
                $('.form-step').removeClass('active');
                $('.form-step[data-step="' + step + '"]').addClass('active');
            }

            this.updateProgressIndicator();
            this.updateNavigationButtons();

            // Scroll to top
            $('html, body').animate({ scrollTop: 0 }, 300);
        },

        navigatePrevious: function() {
            if (this.currentStep === 'basket') {
                this.navigateToStep(3);
            } else if (this.currentStep === 4) {
                this.showBasketReview();
            } else if (this.currentStep === 3) {
                this.navigateToStep(2);
            } else if (this.currentStep === 2) {
                this.navigateToStep(1);
            } else if (this.currentStep === 1) {
                // Handle sub-steps backward navigation
                this.navigateSubStepBackward();
            }
        },

        navigateNext: function() {
            // Next is typically automatic via card selection
            // This can be used for validation steps if needed
        },

        navigateSubStepBackward: function() {
            const currentSubstep = $('.sub-step.active').data('substep');

            if (currentSubstep && currentSubstep.startsWith('1c')) {
                // From material back to type
                const category = this.currentItem.category;
                this.navigateToSubStep('1b-' + category);
            } else if (currentSubstep && currentSubstep.startsWith('1b')) {
                // From type back to category
                this.navigateToSubStep('1a');
            }
        },

        updateProgressIndicator: function() {
            const step = this.currentStep === 'basket' ? 3 : this.currentStep;

            $('.progress-step').each(function() {
                const stepNum = parseInt($(this).data('step'));
                if (stepNum < step) {
                    $(this).addClass('completed').removeClass('active');
                } else if (stepNum === step) {
                    $(this).addClass('active').removeClass('completed');
                } else {
                    $(this).removeClass('active completed');
                }
            });
        },

        updateNavigationButtons: function() {
            const $prevBtn = $('#prev-btn');
            const $nextBtn = $('#next-btn');
            const $submitBtn = $('#submit-btn');

            // Hide all by default
            $prevBtn.hide();
            $nextBtn.hide();
            $submitBtn.hide();

            if (this.currentStep === 1) {
                // Show back button if not on first sub-step
                if (this.currentSubStep !== '1a') {
                    $prevBtn.show();
                }
            } else if (this.currentStep === 'basket') {
                $prevBtn.show();
            } else if (this.currentStep === 4) {
                $prevBtn.show();
                $submitBtn.show();
            } else {
                $prevBtn.show();
            }
        },

        initializeColourPickers: function() {
            this.renderColourGrid('inside-colour-grid');
            this.renderColourGrid('outside-colour-grid');
        },

        renderColourGrid: function(gridId) {
            const self = this;
            const $grid = $('#' + gridId);
            const isInside = gridId.includes('inside');

            $grid.empty();

            // Group by category
            const categories = {};
            this.colours.forEach(colour => {
                if (!categories[colour.category]) {
                    categories[colour.category] = [];
                }
                categories[colour.category].push(colour);
            });

            // Render colours by category
            Object.keys(categories).forEach(category => {
                const $categoryGroup = $('<div class="colour-category"></div>');
                $categoryGroup.append('<h5>' + category + '</h5>');

                const $colourItems = $('<div class="colour-items"></div>');
                categories[category].forEach(colour => {
                    const $colourSwatch = $('<div class="colour-swatch" data-colour="' + colour.name + '" data-hex="' + colour.hex + '"></div>');
                    $colourSwatch.css('background-color', colour.hex);
                    $colourSwatch.attr('title', colour.name);

                    const $colourLabel = $('<span class="colour-label">' + colour.name + '</span>');

                    const $colourItem = $('<div class="colour-item"></div>');
                    $colourItem.append($colourSwatch).append($colourLabel);

                    $colourItem.on('click', function() {
                        const colourName = $(this).find('.colour-swatch').data('colour');
                        self.selectColour(gridId, colourName, isInside);
                    });

                    $colourItems.append($colourItem);
                });

                $categoryGroup.append($colourItems);
                $grid.append($categoryGroup);
            });
        },

        selectColour: function(gridId, colourName, isInside) {
            const prefix = isInside ? 'inside' : 'outside';

            // Update hidden field
            $('#' + prefix + '-colour').val(colourName);

            // Update display
            $('#' + prefix + '-colour-name').text(colourName);

            // Update visual selection
            $('#' + gridId + ' .colour-item').removeClass('selected');
            $('#' + gridId + ' .colour-item').filter(function() {
                return $(this).find('.colour-swatch').data('colour') === colourName;
            }).addClass('selected');
        },

        filterColours: function(gridId, searchTerm) {
            $('#' + gridId + ' .colour-item').each(function() {
                const colourName = $(this).find('.colour-swatch').data('colour').toLowerCase();
                if (colourName.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        },

        updateConfigurationPreview: function() {
            $('#style-preview').attr('src', this.currentItem.styleImage);
            $('#preview-category').text(this.currentItem.category || '');
            $('#preview-type').text(this.currentItem.typeName || '');
            $('#preview-material').text(this.currentItem.materialName || 'N/A');
            $('#preview-style').text(this.currentItem.styleName || '');
        },

        validateConfiguration: function() {
            const width = parseInt($('#width').val());
            const height = parseInt($('#height').val());
            const cill = $('#cill').val();
            const insideColour = $('#inside-colour').val();
            const outsideColour = $('#outside-colour').val();
            const glazingType = $('#glazing-type').val();
            const hardwareColour = $('#hardware-colour').val();

            if (!width || width < 200 || width > 4000) {
                alert('Please enter a valid width (200-4000mm)');
                return false;
            }

            if (!height || height < 200 || height > 3200) {
                alert('Please enter a valid height (200-3200mm)');
                return false;
            }

            if (!cill) {
                alert('Please select a cill option');
                return false;
            }

            if (!insideColour) {
                alert('Please select an inside colour');
                return false;
            }

            if (!outsideColour) {
                alert('Please select an outside colour');
                return false;
            }

            if (!glazingType) {
                alert('Please select a glazing type');
                return false;
            }

            if (!hardwareColour) {
                alert('Please select a hardware colour');
                return false;
            }

            return true;
        },

        addItemToBasket: function() {
            const item = {
                id: this.editingItemId || 'item-' + Date.now(),
                timestamp: Date.now(),
                category: this.currentItem.category,
                type: this.currentItem.type,
                typeName: this.currentItem.typeName,
                material: this.currentItem.material,
                materialName: this.currentItem.materialName,
                style: this.currentItem.style,
                styleName: this.currentItem.styleName,
                styleImage: this.currentItem.styleImage,
                width: $('#width').val(),
                height: $('#height').val(),
                cill: $('#cill').val(),
                insideColour: $('#inside-colour').val(),
                outsideColour: $('#outside-colour').val(),
                glazingType: $('#glazing-type').val(),
                glazingFeatures: $('#glazing-features').val(),
                hardwareColour: $('#hardware-colour').val(),
                location: '' // Can be set later
            };

            if (this.editingItemId) {
                // Update existing item
                const index = this.basket.findIndex(i => i.id === this.editingItemId);
                if (index !== -1) {
                    this.basket[index] = item;
                }
                this.editingItemId = null;
            } else {
                // Add new item
                this.basket.push(item);
            }

            // Reset current item
            this.currentItem = {};
            this.resetConfigurationForm();
        },

        resetConfigurationForm: function() {
            $('#width').val('');
            $('#height').val('');
            $('#cill').val('');
            $('#inside-colour').val('');
            $('#outside-colour').val('');
            $('#inside-colour-name').text('None');
            $('#outside-colour-name').text('None');
            $('#glazing-type').val('');
            $('#glazing-features').val('not-required');
            $('#hardware-colour').val('');
            $('#frame-image').val('');
            $('.colour-item').removeClass('selected');
        },

        showBasketReview: function() {
            this.renderBasket();
            this.navigateToStep('basket');
        },

        renderBasket: function() {
            const $container = $('#basket-items-container');
            $container.empty();

            $('#basket-item-count').text(this.basket.length);

            if (this.basket.length === 0) {
                $container.append('<p class="empty-basket">Your basket is empty. Add items to continue.</p>');
                return;
            }

            this.basket.forEach((item, index) => {
                const $item = this.createBasketItemElement(item, index);
                $container.append($item);
            });
        },

        createBasketItemElement: function(item, index) {
            const self = this;

            const $itemDiv = $('<div class="basket-item" data-item-id="' + item.id + '"></div>');

            const $thumbnail = $('<div class="item-thumbnail"></div>');
            $thumbnail.append('<img src="' + item.styleImage + '" alt="' + item.styleName + '">');

            const $details = $('<div class="item-details"></div>');
            $details.append('<h3>Item ' + (index + 1) + '</h3>');

            if (item.location) {
                $details.append('<p class="item-location"><strong>Location:</strong> ' + item.location + '</p>');
            } else {
                const $locationLink = $('<a href="#" class="set-location-link">set location</a>');
                $locationLink.on('click', function(e) {
                    e.preventDefault();
                    const location = prompt('Enter location for this item:');
                    if (location) {
                        item.location = location;
                        self.renderBasket();
                    }
                });
                $details.append($locationLink);
            }

            const $table = $('<table class="item-summary"></table>');

            const fields = [
                { label: 'Product Template', value: item.typeName + ' ' + (item.materialName || ''), field: 'product' },
                { label: 'Size', value: item.width + 'w x ' + item.height + 'h mm', field: 'size' },
                { label: 'Section Colour', value: item.insideColour + ' / ' + item.outsideColour, field: 'colour' },
                { label: 'Glazing', value: item.glazingType, field: 'glazing' },
                { label: 'Glazing Features', value: item.glazingFeatures, field: 'glazingFeatures' },
                { label: 'Hardware Colour', value: item.hardwareColour, field: 'hardware' }
            ];

            fields.forEach(field => {
                const $row = $('<tr></tr>');
                $row.append('<td><strong>' + field.label + ':</strong></td>');

                const $valueCell = $('<td class="editable-field" data-field="' + field.field + '">' + field.value + '</td>');
                $valueCell.on('click', function() {
                    self.editItemField(item.id, field.field);
                });

                $row.append($valueCell);
                $table.append($row);
            });

            $details.append($table);

            const $actions = $('<div class="item-actions"></div>');

            const $copyBtn = $('<button type="button" class="btn btn-sm btn-secondary">Copy</button>');
            $copyBtn.on('click', function() {
                self.copyBasketItem(item.id);
            });

            const $deleteBtn = $('<button type="button" class="btn btn-sm btn-danger">Delete</button>');
            $deleteBtn.on('click', function() {
                if (confirm('Are you sure you want to delete this item?')) {
                    self.deleteBasketItem(item.id);
                }
            });

            $actions.append($copyBtn).append($deleteBtn);

            $itemDiv.append($thumbnail).append($details).append($actions);

            return $itemDiv;
        },

        editItemField: function(itemId, field) {
            const item = this.basket.find(i => i.id === itemId);
            if (!item) return;

            const self = this;
            const $modal = $('#edit-item-modal');
            const $content = $('#edit-item-content');

            $content.empty();

            if (field === 'size') {
                $content.append('<div class="edit-field-group"><label>Width (mm):</label><input type="number" id="edit-width" value="' + item.width + '" min="200" max="4000"></div>');
                $content.append('<div class="edit-field-group"><label>Height (mm):</label><input type="number" id="edit-height" value="' + item.height + '" min="200" max="3200"></div>');
                $content.append('<div class="edit-field-group"><label>Cill:</label><select id="edit-cill"><option value="150mm"' + (item.cill === '150mm' ? ' selected' : '') + '>150mm Sill</option><option value="200mm"' + (item.cill === '200mm' ? ' selected' : '') + '>200mm Sill</option><option value="none"' + (item.cill === 'none' ? ' selected' : '') + '>No Sill</option></select></div>');
            } else if (field === 'colour') {
                $content.append('<div class="edit-field-group"><label>Inside Colour:</label><input type="text" id="edit-inside-colour" value="' + item.insideColour + '"></div>');
                $content.append('<div class="edit-field-group"><label>Outside Colour:</label><input type="text" id="edit-outside-colour" value="' + item.outsideColour + '"></div>');
            } else if (field === 'glazing') {
                $content.append('<div class="edit-field-group"><label>Glazing Type:</label><select id="edit-glazing-type"><option value="clear"' + (item.glazingType === 'clear' ? ' selected' : '') + '>Clear</option><option value="obscured"' + (item.glazingType === 'obscured' ? ' selected' : '') + '>Obscured</option><option value="tinted"' + (item.glazingType === 'tinted' ? ' selected' : '') + '>Tinted</option><option value="self-cleaning"' + (item.glazingType === 'self-cleaning' ? ' selected' : '') + '>Self Cleaning</option></select></div>');
            } else if (field === 'glazingFeatures') {
                $content.append('<div class="edit-field-group"><label>Glazing Features:</label><select id="edit-glazing-features"><option value="not-required"' + (item.glazingFeatures === 'not-required' ? ' selected' : '') + '>Not Required</option><option value="acoustic"' + (item.glazingFeatures === 'acoustic' ? ' selected' : '') + '>Acoustic</option><option value="security"' + (item.glazingFeatures === 'security' ? ' selected' : '') + '>Security</option><option value="thermal"' + (item.glazingFeatures === 'thermal' ? ' selected' : '') + '>Thermal</option></select></div>');
            } else if (field === 'hardware') {
                $content.append('<div class="edit-field-group"><label>Hardware Colour:</label><select id="edit-hardware-colour"><option value="white"' + (item.hardwareColour === 'white' ? ' selected' : '') + '>White</option><option value="chrome"' + (item.hardwareColour === 'chrome' ? ' selected' : '') + '>Chrome</option><option value="gold"' + (item.hardwareColour === 'gold' ? ' selected' : '') + '>Gold</option><option value="black"' + (item.hardwareColour === 'black' ? ' selected' : '') + '>Black</option></select></div>');
            }

            $modal.show();

            // Unbind previous click handler and bind new one
            $('.modal-apply').off('click').on('click', function() {
                if (field === 'size') {
                    item.width = $('#edit-width').val();
                    item.height = $('#edit-height').val();
                    item.cill = $('#edit-cill').val();
                } else if (field === 'colour') {
                    item.insideColour = $('#edit-inside-colour').val();
                    item.outsideColour = $('#edit-outside-colour').val();
                } else if (field === 'glazing') {
                    item.glazingType = $('#edit-glazing-type').val();
                } else if (field === 'glazingFeatures') {
                    item.glazingFeatures = $('#edit-glazing-features').val();
                } else if (field === 'hardware') {
                    item.hardwareColour = $('#edit-hardware-colour').val();
                }

                $modal.hide();
                self.renderBasket();
            });
        },

        copyBasketItem: function(itemId) {
            const item = this.basket.find(i => i.id === itemId);
            if (!item) return;

            const newItem = Object.assign({}, item);
            newItem.id = 'item-' + Date.now();
            newItem.timestamp = Date.now();

            this.basket.push(newItem);
            this.renderBasket();
        },

        deleteBasketItem: function(itemId) {
            this.basket = this.basket.filter(i => i.id !== itemId);
            this.renderBasket();
        },

        updateFinalSummary: function() {
            const $summary = $('#final-basket-summary');
            $summary.empty();

            this.basket.forEach((item, index) => {
                const $itemSummary = $('<div class="summary-item"></div>');
                $itemSummary.append('<h4>Item ' + (index + 1) + '</h4>');
                $itemSummary.append('<p><strong>Product:</strong> ' + item.typeName + ' ' + (item.materialName || '') + '</p>');
                $itemSummary.append('<p><strong>Size:</strong> ' + item.width + 'w x ' + item.height + 'h mm</p>');
                $itemSummary.append('<p><strong>Style:</strong> ' + item.styleName + '</p>');
                $itemSummary.append('<p><strong>Colours:</strong> ' + item.insideColour + ' / ' + item.outsideColour + '</p>');
                if (item.location) {
                    $itemSummary.append('<p><strong>Location:</strong> ' + item.location + '</p>');
                }
                $summary.append($itemSummary);
            });
        },

        submitForm: function() {
            const self = this;

            // Validate customer details
            const customerName = $('#customer-name').val();
            const customerEmail = $('#customer-email').val();
            const customerPhone = $('#customer-phone').val();

            if (!customerName || !customerEmail || !customerPhone) {
                alert('Please fill in all required fields (Name, Email, Phone)');
                return;
            }

            // Prepare data
            const customerData = {
                name: customerName,
                email: customerEmail,
                phone: customerPhone,
                address: $('#customer-address').val(),
                postcode: $('#customer-postcode').val(),
                preferred_contact: $('#preferred-contact').val(),
                additional_notes: $('#additional-notes').val()
            };

            // Show loading
            $('#submit-btn').prop('disabled', true).text('Submitting...');

            // Submit via AJAX
            $.ajax({
                url: quotationFormAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'submit_quotation_form',
                    nonce: quotationFormAjax.nonce,
                    basket_items: JSON.stringify(self.basket),
                    customer_data: customerData
                },
                success: function(response) {
                    if (response.success) {
                        alert('Thank you! Your quotation request has been submitted successfully. We will contact you soon.');
                        // Reset form
                        self.resetForm();
                        self.basket = [];
                        self.navigateToStep(1);
                        self.navigateToSubStep('1a');
                    } else {
                        alert('There was an error submitting your request. Please try again.');
                    }
                },
                error: function() {
                    alert('There was an error submitting your request. Please try again.');
                },
                complete: function() {
                    $('#submit-btn').prop('disabled', false).text('Submit Quote Request');
                }
            });
        },

        resetForm: function() {
            this.currentItem = {};
            this.editingItemId = null;
            this.resetConfigurationForm();
            $('.image-card').removeClass('selected');
            $('#customer-name').val('');
            $('#customer-email').val('');
            $('#customer-phone').val('');
            $('#customer-address').val('');
            $('#customer-postcode').val('');
            $('#additional-notes').val('');
            this.navigateToStep(1);
            this.navigateToSubStep('1a');
        }
    };

    // Initialize the form
    QuotationForm.init();
});
