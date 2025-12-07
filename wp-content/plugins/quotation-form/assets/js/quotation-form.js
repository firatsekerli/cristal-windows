jQuery(document).ready(function($) {
    'use strict';

    // State Management
    const QuotationForm = {
        currentStep: 1,
        currentSubStep: '1a',
        basket: [],
        currentItem: {},
        editingItemId: null,

        // Available colours - Use dynamic colours from config if available, otherwise fallback to hardcoded
        colours: (typeof quotationFormAjax !== 'undefined' && quotationFormAjax.config && quotationFormAjax.config.colours)
            ? quotationFormAjax.config.colours
            : [
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

        // Available glazing features - Use dynamic features from config if available
        glazingFeatures: (typeof quotationFormAjax !== 'undefined' && quotationFormAjax.config && quotationFormAjax.config.glazingFeatures)
            ? quotationFormAjax.config.glazingFeatures
            : [],

        // Available hardware colours - Use dynamic colours from config if available
        hardwareColours: (typeof quotationFormAjax !== 'undefined' && quotationFormAjax.config && quotationFormAjax.config.hardwareColours)
            ? quotationFormAjax.config.hardwareColours
            : [
                { label: 'White', value: 'white', hex: '#FFFFFF' },
                { label: 'Chrome', value: 'chrome', hex: '#C0C0C0' },
                { label: 'Gold', value: 'gold', hex: '#FFD700' },
                { label: 'Black', value: 'black', hex: '#000000' }
            ],

        debugColours: function() {
            console.log('=== COLOUR DEBUG INFO ===');
            console.log('Total colours:', this.colours.length);
            console.log('Sample colour data:', this.colours[0]);
            console.log('Colours with images:', this.colours.filter(c => c.colour_image && c.colour_image.url).length);
            if (this.colours.length > 0) {
                this.colours.forEach((colour, index) => {
                    if (colour.colour_image && colour.colour_image.url) {
                        console.log(`Colour ${index} "${colour.name}" has image:`, colour.colour_image.url);
                    }
                });
            }
            console.log('=========================');
        },

        init: function() {
            this.debugColours(); // Debug colour data
            this.bindEvents();
            this.initializeColourPickers();
            this.initializeGlazingTypePicker();
            this.initializeGlazingFeaturesPicker();
            this.initializeHardwareColourPicker();
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

                self.currentItem.type = type;
                self.currentItem.typeName = $(this).find('h3').text();
                self.selectCard($(this));

                // Debug logging
                console.log('Selected type:', type);
                console.log('Config:', quotationFormAjax.config);
                console.log('Materials:', quotationFormAjax.config.materials);

                // Dynamically determine if material selection is needed
                const hasMaterialsAvailable = self.checkMaterialsAvailable(type);
                console.log('Has materials available:', hasMaterialsAvailable);

                if (hasMaterialsAvailable) {
                    // Navigate to material selection
                    self.navigateToSubStep('1c-material');
                } else {
                    // No materials available, skip to Step 2 (Style)
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

            // Glazing features search
            $('#glazing-features-search').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                self.filterGlazingFeatures('glazing-features-grid', searchTerm);
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

        /**
         * Check if materials are available for a given product type
         */
        checkMaterialsAvailable: function(productType) {
            const config = quotationFormAjax.config || {};
            const materials = config.materials || [];

            console.log('Checking materials for type:', productType);
            console.log('Total materials:', materials.length);

            // Check if any materials are available for this product type
            for (let i = 0; i < materials.length; i++) {
                const material = materials[i];
                const availableTypes = material.available_types || [];

                console.log('Material:', material.name, 'Available types:', availableTypes);

                // If no types specified, available for all
                if (availableTypes.length === 0 || availableTypes.includes(productType)) {
                    console.log('Found available material:', material.name);
                    return true;
                }
            }

            console.log('No materials available for this type');
            return false;
        },

        /**
         * Check if a material/style is available for current product type
         */
        isItemAvailableForProduct: function(availableTypes) {
            const currentType = this.currentItem.type;

            // Parse JSON if needed
            if (typeof availableTypes === 'string') {
                try {
                    availableTypes = JSON.parse(availableTypes);
                } catch (e) {
                    availableTypes = [];
                }
            }

            // If no restrictions, show for all
            if (!availableTypes || !Array.isArray(availableTypes) || availableTypes.length === 0) {
                return true;
            }

            // Check if current type is in the available types list
            return availableTypes.includes(currentType);
        },

        /**
         * Filter materials/styles based on current product selection
         */
        filterItemsForProduct: function($container) {
            const self = this;

            $container.find('.image-card').each(function() {
                const $card = $(this);
                const availableTypes = $card.data('available-types');

                if (self.isItemAvailableForProduct(availableTypes)) {
                    $card.show();
                } else {
                    $card.hide();
                }
            });
        },

        navigateToSubStep: function(substep) {
            this.currentSubStep = substep;

            // Hide all sub-steps in current step
            $('.form-step[data-step="1"] .sub-step').removeClass('active');

            // Show the target sub-step
            const $targetSubstep = $('.sub-step[data-substep="' + substep + '"]');
            $targetSubstep.addClass('active');

            // Filter materials if navigating to material selection
            if (substep.startsWith('1c-material')) {
                this.filterItemsForProduct($targetSubstep.find('.material-grid'));
            }

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
                const $targetStep = $('.form-step[data-step="' + step + '"]');
                $targetStep.addClass('active');

                // Filter styles if navigating to step 2
                if (step === 2) {
                    this.filterItemsForProduct($targetStep.find('.style-grid'));
                }
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

                    // Debug: Log colour data
                    console.log('Rendering colour:', colour.name, {
                        hasColourImage: !!(colour.colour_image),
                        hasColourImageUrl: !!(colour.colour_image && colour.colour_image.url),
                        colourImageData: colour.colour_image
                    });

                    // Check if colour has an image
                    if (colour.colour_image && colour.colour_image.url) {
                        // Use image instead of hex color
                        console.log('Using image for', colour.name, ':', colour.colour_image.url);
                        const $img = $('<img src="' + colour.colour_image.url + '" alt="' + colour.name + '" />');
                        $colourSwatch.addClass('has-image').append($img);
                    } else {
                        // Fall back to hex color
                        console.log('Using hex color for', colour.name, ':', colour.hex);
                        $colourSwatch.css('background-color', colour.hex);
                    }

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

        initializeHardwareColourPicker: function() {
            this.renderHardwareColourGrid('hardware-colour-grid');
        },

        renderHardwareColourGrid: function(gridId) {
            const self = this;
            const $grid = $('#' + gridId);

            $grid.empty();

            // Render hardware colours (no categories, just a simple grid)
            const $colourItems = $('<div class="colour-items"></div>');

            this.hardwareColours.forEach(colour => {
                const colourName = colour.label || colour.name;
                const colourValue = colour.value || colour.label;
                const colourHex = colour.hex || '#CCCCCC';

                const $colourSwatch = $('<div class="colour-swatch" data-colour="' + colourValue + '" data-hex="' + colourHex + '"></div>');

                // Use hex color (no images for hardware colours)
                $colourSwatch.css('background-color', colourHex);
                $colourSwatch.attr('title', colourName);

                const $colourLabel = $('<span class="colour-label">' + colourName + '</span>');

                const $colourItem = $('<div class="colour-item"></div>');
                $colourItem.append($colourSwatch).append($colourLabel);

                $colourItem.on('click', function() {
                    const value = $(this).find('.colour-swatch').data('colour');
                    self.selectHardwareColour(value, colourName);
                });

                $colourItems.append($colourItem);
            });

            $grid.append($colourItems);
        },

        selectHardwareColour: function(colourValue, colourName) {
            // Update hidden field
            $('#hardware-colour').val(colourValue);

            // Update display
            $('#hardware-colour-name').text(colourName);

            // Update visual selection
            $('#hardware-colour-grid .colour-item').removeClass('selected');
            $('#hardware-colour-grid .colour-item').filter(function() {
                return $(this).find('.colour-swatch').data('colour') === colourValue;
            }).addClass('selected');
        },

        initializeGlazingTypePicker: function() {
            const self = this;

            // Bind click events to glazing type cards
            $('.glazing-type-card').on('click', function() {
                const glazingType = $(this).data('glazing-type');
                const glazingTypeName = $(this).find('.glazing-type-label').text();
                const patterns = $(this).data('patterns');

                self.selectGlazingType(glazingType, glazingTypeName, patterns);
            });

            // Auto-select first glazing type (Low E Double) on page load
            const $firstGlazingType = $('.glazing-type-card').first();
            if ($firstGlazingType.length) {
                const glazingType = $firstGlazingType.data('glazing-type');
                const glazingTypeName = $firstGlazingType.find('.glazing-type-label').text();
                const patterns = $firstGlazingType.data('patterns');

                self.selectGlazingType(glazingType, glazingTypeName, patterns);
            }
        },

        selectGlazingType: function(glazingType, glazingTypeName, patterns) {
            // Update hidden field
            $('#glazing-type').val(glazingType);

            // Update display
            $('#glazing-type-name').text(glazingTypeName);

            // Update visual selection
            $('.glazing-type-card').removeClass('selected');
            $('.glazing-type-card[data-glazing-type="' + glazingType + '"]').addClass('selected');

            // Show/hide patterns based on availability
            if (patterns && patterns.length > 0) {
                this.renderGlazingPatterns(patterns);
                $('#glazing-pattern-group').show();
            } else {
                $('#glazing-pattern-group').hide();
                $('#glazing-pattern').val('');
            }
        },

        renderGlazingPatterns: function(patterns) {
            const self = this;
            const $grid = $('#glazing-pattern-grid');

            $grid.empty();

            patterns.forEach(pattern => {
                const patternName = pattern.name || '';
                const patternValue = pattern.value || '';
                const patternImage = pattern.image && pattern.image.url ? pattern.image.url : '';

                const $patternCard = $('<div class="glazing-pattern-card" data-pattern="' + patternValue + '"></div>');

                if (patternImage) {
                    const $patternImage = $('<div class="glazing-pattern-image"></div>');
                    $patternImage.css('background-image', 'url(' + patternImage + ')');
                    $patternCard.append($patternImage);
                }

                const $patternLabel = $('<div class="glazing-pattern-label">' + patternName + '</div>');
                $patternCard.append($patternLabel);

                $patternCard.on('click', function() {
                    const value = $(this).data('pattern');
                    self.selectGlazingPattern(value, patternName);
                });

                $grid.append($patternCard);
            });
        },

        selectGlazingPattern: function(patternValue, patternName) {
            // Update hidden field
            $('#glazing-pattern').val(patternValue);

            // Update visual selection
            $('.glazing-pattern-card').removeClass('selected');
            $('.glazing-pattern-card[data-pattern="' + patternValue + '"]').addClass('selected');
        },

        initializeGlazingFeaturesPicker: function() {
            this.renderGlazingFeaturesGrid('glazing-features-grid');
        },

        renderGlazingFeaturesGrid: function(gridId) {
            const self = this;
            const $grid = $('#' + gridId);

            $grid.empty();

            // Add "Not Required" option first
            const $notRequiredCategory = $('<div class="glazing-feature-category"></div>');
            $notRequiredCategory.append('<h5>Not Required</h5>');

            const $notRequiredItems = $('<div class="glazing-feature-items"></div>');
            const $notRequiredItem = $('<div class="glazing-feature-item" data-feature="not-required"></div>');
            const $notRequiredSwatch = $('<div class="glazing-feature-swatch"></div>');
            $notRequiredSwatch.text('None');
            const $notRequiredLabel = $('<span class="glazing-feature-label">Not Required</span>');
            $notRequiredItem.append($notRequiredSwatch).append($notRequiredLabel);

            $notRequiredItem.on('click', function() {
                self.selectGlazingFeature('glazing-features-grid', 'not-required', 'Not Required');
            });

            $notRequiredItems.append($notRequiredItem);
            $notRequiredCategory.append($notRequiredItems);
            $grid.append($notRequiredCategory);

            // Group by category
            const categories = {};
            this.glazingFeatures.forEach(feature => {
                if (!categories[feature.category]) {
                    categories[feature.category] = [];
                }
                categories[feature.category].push(feature);
            });

            // Render features by category
            Object.keys(categories).forEach(category => {
                const $categoryGroup = $('<div class="glazing-feature-category"></div>');
                $categoryGroup.append('<h5>' + category + '</h5>');

                const $featureItems = $('<div class="glazing-feature-items"></div>');
                categories[category].forEach(feature => {
                    const $featureSwatch = $('<div class="glazing-feature-swatch" data-feature="' + feature.value + '"></div>');

                    // Check if feature has an image
                    if (feature.feature_image && feature.feature_image.url) {
                        const $img = $('<img src="' + feature.feature_image.url + '" alt="' + feature.name + '" />');
                        $featureSwatch.addClass('has-image').append($img);
                    } else {
                        $featureSwatch.text(feature.name);
                    }

                    $featureSwatch.attr('title', feature.name);

                    const $featureLabel = $('<span class="glazing-feature-label">' + feature.name + '</span>');

                    const $featureItem = $('<div class="glazing-feature-item"></div>');
                    $featureItem.append($featureSwatch).append($featureLabel);

                    $featureItem.on('click', function() {
                        self.selectGlazingFeature('glazing-features-grid', feature.value, feature.name);
                    });

                    $featureItems.append($featureItem);
                });

                $categoryGroup.append($featureItems);
                $grid.append($categoryGroup);
            });
        },

        selectGlazingFeature: function(gridId, featureValue, featureName) {
            // Update hidden field
            $('#glazing-features').val(featureValue);

            // Update display
            $('#glazing-features-name').text(featureName);

            // Update visual selection
            $('#' + gridId + ' .glazing-feature-item').removeClass('selected');
            $('#' + gridId + ' .glazing-feature-item').filter(function() {
                return $(this).find('.glazing-feature-swatch').data('feature') === featureValue;
            }).addClass('selected');
        },

        filterGlazingFeatures: function(gridId, searchTerm) {
            $('#' + gridId + ' .glazing-feature-item').each(function() {
                const featureName = $(this).find('.glazing-feature-label').text().toLowerCase();
                if (featureName.includes(searchTerm)) {
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
                glazingTypeName: $('#glazing-type-name').text(),
                glazingPattern: $('#glazing-pattern').val(),
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
            $('#glazing-type-name').text('None');
            $('#glazing-pattern').val('');
            $('#glazing-pattern-group').hide();
            $('#glazing-features').val('not-required');
            $('#glazing-features-name').text('Not Required');
            $('#hardware-colour').val('');
            $('#hardware-colour-name').text('None');
            $('#frame-image').val('');
            $('.colour-item').removeClass('selected');
            $('.glazing-type-card').removeClass('selected');
            $('.glazing-pattern-card').removeClass('selected');
            $('.glazing-feature-item').removeClass('selected');
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

            const glazingDisplay = item.glazingTypeName || item.glazingType;
            const glazingValue = item.glazingPattern
                ? glazingDisplay + ' - ' + item.glazingPattern
                : glazingDisplay;

            const fields = [
                { label: 'Product Template', value: item.typeName + ' ' + (item.materialName || ''), field: 'product' },
                { label: 'Size', value: item.width + 'w x ' + item.height + 'h mm', field: 'size' },
                { label: 'Section Colour', value: item.insideColour + ' / ' + item.outsideColour, field: 'colour' },
                { label: 'Glazing', value: glazingValue, field: 'glazing' },
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
                // Store modal selected colors
                this.modalSelectedColors = {
                    inside: item.insideColour,
                    outside: item.outsideColour
                };

                // Create inside colour picker
                $content.append('<div class="edit-field-group modal-colour-picker">' +
                    '<label>Inside Colour:</label>' +
                    '<div class="colour-selection-display">Selected: <strong id="modal-inside-colour-name">' + item.insideColour + '</strong></div>' +
                    '<input type="text" id="modal-inside-colour-search" class="modal-colour-search" placeholder="Search colours...">' +
                    '<div id="modal-inside-colour-grid" class="colour-grid modal-colour-grid"></div>' +
                    '</div>');

                // Create outside colour picker
                $content.append('<div class="edit-field-group modal-colour-picker">' +
                    '<label>Outside Colour:</label>' +
                    '<div class="colour-selection-display">Selected: <strong id="modal-outside-colour-name">' + item.outsideColour + '</strong></div>' +
                    '<input type="text" id="modal-outside-colour-search" class="modal-colour-search" placeholder="Search colours...">' +
                    '<div id="modal-outside-colour-grid" class="colour-grid modal-colour-grid"></div>' +
                    '</div>');

                // Render colour grids after a brief delay to ensure DOM is ready
                setTimeout(function() {
                    self.renderModalColourGrid('modal-inside-colour-grid', item.insideColour);
                    self.renderModalColourGrid('modal-outside-colour-grid', item.outsideColour);
                }, 10);

                // Add search functionality
                $('#modal-inside-colour-search').on('input', function() {
                    self.filterColours('modal-inside-colour-grid', $(this).val().toLowerCase());
                });

                $('#modal-outside-colour-search').on('input', function() {
                    self.filterColours('modal-outside-colour-grid', $(this).val().toLowerCase());
                });
            } else if (field === 'glazing') {
                // Store modal selected glazing type and pattern
                this.modalSelectedGlazingType = item.glazingType;
                this.modalSelectedGlazingPattern = item.glazingPattern || '';

                // Create glazing type picker
                $content.append('<div class="edit-field-group modal-glazing-type-picker">' +
                    '<label>Glazing Type:</label>' +
                    '<div class="glazing-type-selection-display">Selected: <strong id="modal-glazing-type-name">' + (item.glazingTypeName || item.glazingType) + '</strong></div>' +
                    '<div id="modal-glazing-type-grid" class="glazing-type-grid modal-glazing-type-grid"></div>' +
                    '</div>');

                // Add pattern section (initially hidden if no patterns)
                $content.append('<div class="edit-field-group modal-glazing-pattern-picker" id="modal-glazing-pattern-group" style="display: none;">' +
                    '<label>Pattern:</label>' +
                    '<div id="modal-glazing-pattern-grid" class="glazing-pattern-grid modal-glazing-pattern-grid"></div>' +
                    '</div>');

                // Render glazing type grid after a brief delay to ensure DOM is ready
                setTimeout(function() {
                    self.renderModalGlazingTypeGrid('modal-glazing-type-grid', item.glazingType, item.glazingPattern);
                }, 10);
            } else if (field === 'glazingFeatures') {
                // Store modal selected glazing feature
                this.modalSelectedGlazingFeature = item.glazingFeatures || 'not-required';

                // Get the feature name for display
                let featureName = 'Not Required';
                if (item.glazingFeatures && item.glazingFeatures !== 'not-required') {
                    const feature = this.glazingFeatures.find(f => f.value === item.glazingFeatures);
                    if (feature) {
                        featureName = feature.name;
                    }
                }

                // Create glazing features picker
                $content.append('<div class="edit-field-group modal-glazing-features-picker">' +
                    '<label>Glazing Features:</label>' +
                    '<div class="glazing-features-selection-display">Selected: <strong id="modal-glazing-features-name">' + featureName + '</strong></div>' +
                    '<input type="text" id="modal-glazing-features-search" class="modal-glazing-features-search" placeholder="Search glazing features...">' +
                    '<div id="modal-glazing-features-grid" class="glazing-features-grid modal-glazing-features-grid"></div>' +
                    '</div>');

                // Render glazing features grid after a brief delay to ensure DOM is ready
                setTimeout(function() {
                    self.renderModalGlazingFeaturesGrid('modal-glazing-features-grid', item.glazingFeatures || 'not-required');
                }, 10);

                // Add search functionality
                $('#modal-glazing-features-search').on('input', function() {
                    self.filterGlazingFeatures('modal-glazing-features-grid', $(this).val().toLowerCase());
                });
            } else if (field === 'hardware') {
                // Store modal selected hardware colour
                this.modalSelectedHardwareColour = item.hardwareColour;

                // Get the colour name for display
                let colourName = item.hardwareColour;
                const colour = this.hardwareColours.find(c => (c.value || c.label) === item.hardwareColour);
                if (colour) {
                    colourName = colour.label || colour.name;
                }

                // Create hardware colour picker
                $content.append('<div class="edit-field-group modal-hardware-colour-picker">' +
                    '<label>Hardware Colour:</label>' +
                    '<div class="colour-selection-display">Selected: <strong id="modal-hardware-colour-name">' + colourName + '</strong></div>' +
                    '<div id="modal-hardware-colour-grid" class="hardware-colour-grid modal-hardware-colour-grid"></div>' +
                    '</div>');

                // Render hardware colour grid after a brief delay to ensure DOM is ready
                setTimeout(function() {
                    self.renderModalHardwareColourGrid('modal-hardware-colour-grid', item.hardwareColour);
                }, 10);
            }

            $modal.show();

            // Unbind previous click handler and bind new one
            $('.modal-apply').off('click').on('click', function() {
                if (field === 'size') {
                    item.width = $('#edit-width').val();
                    item.height = $('#edit-height').val();
                    item.cill = $('#edit-cill').val();
                } else if (field === 'colour') {
                    item.insideColour = self.modalSelectedColors.inside;
                    item.outsideColour = self.modalSelectedColors.outside;
                } else if (field === 'glazing') {
                    item.glazingType = self.modalSelectedGlazingType;
                    item.glazingTypeName = $('#modal-glazing-type-name').text();
                    item.glazingPattern = self.modalSelectedGlazingPattern;
                } else if (field === 'glazingFeatures') {
                    item.glazingFeatures = self.modalSelectedGlazingFeature;
                } else if (field === 'hardware') {
                    item.hardwareColour = self.modalSelectedHardwareColour;
                }

                $modal.hide();
                self.renderBasket();
            });
        },

        renderModalColourGrid: function(gridId, selectedColour) {
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

                    // Check if colour has an image
                    if (colour.colour_image && colour.colour_image.url) {
                        // Use image instead of hex color
                        const $img = $('<img src="' + colour.colour_image.url + '" alt="' + colour.name + '" />');
                        $colourSwatch.addClass('has-image').append($img);
                    } else {
                        // Fall back to hex color
                        $colourSwatch.css('background-color', colour.hex);
                    }

                    $colourSwatch.attr('title', colour.name);

                    const $colourLabel = $('<span class="colour-label">' + colour.name + '</span>');

                    const $colourItem = $('<div class="colour-item"></div>');
                    $colourItem.append($colourSwatch).append($colourLabel);

                    // Mark selected colour
                    if (colour.name === selectedColour) {
                        $colourItem.addClass('selected');
                    }

                    $colourItem.on('click', function() {
                        const colourName = $(this).find('.colour-swatch').data('colour');
                        self.selectModalColour(gridId, colourName, isInside);
                    });

                    $colourItems.append($colourItem);
                });

                $categoryGroup.append($colourItems);
                $grid.append($categoryGroup);
            });
        },

        selectModalColour: function(gridId, colourName, isInside) {
            const prefix = isInside ? 'inside' : 'outside';

            // Update the modalSelectedColors object
            if (isInside) {
                this.modalSelectedColors.inside = colourName;
            } else {
                this.modalSelectedColors.outside = colourName;
            }

            // Update display
            $('#modal-' + prefix + '-colour-name').text(colourName);

            // Update visual selection
            $('#' + gridId + ' .colour-item').removeClass('selected');
            $('#' + gridId + ' .colour-item').filter(function() {
                return $(this).find('.colour-swatch').data('colour') === colourName;
            }).addClass('selected');
        },

        renderModalGlazingFeaturesGrid: function(gridId, selectedFeature) {
            const self = this;
            const $grid = $('#' + gridId);

            $grid.empty();

            // Add "Not Required" option first
            const $notRequiredCategory = $('<div class="glazing-feature-category"></div>');
            $notRequiredCategory.append('<h5>Not Required</h5>');

            const $notRequiredItems = $('<div class="glazing-feature-items"></div>');
            const $notRequiredItem = $('<div class="glazing-feature-item" data-feature="not-required"></div>');
            const $notRequiredSwatch = $('<div class="glazing-feature-swatch"></div>');
            $notRequiredSwatch.text('None');
            const $notRequiredLabel = $('<span class="glazing-feature-label">Not Required</span>');
            $notRequiredItem.append($notRequiredSwatch).append($notRequiredLabel);

            // Mark selected if it matches
            if (selectedFeature === 'not-required') {
                $notRequiredItem.addClass('selected');
            }

            $notRequiredItem.on('click', function() {
                self.selectModalGlazingFeature('modal-glazing-features-grid', 'not-required', 'Not Required');
            });

            $notRequiredItems.append($notRequiredItem);
            $notRequiredCategory.append($notRequiredItems);
            $grid.append($notRequiredCategory);

            // Group by category
            const categories = {};
            this.glazingFeatures.forEach(feature => {
                if (!categories[feature.category]) {
                    categories[feature.category] = [];
                }
                categories[feature.category].push(feature);
            });

            // Render features by category
            Object.keys(categories).forEach(category => {
                const $categoryGroup = $('<div class="glazing-feature-category"></div>');
                $categoryGroup.append('<h5>' + category + '</h5>');

                const $featureItems = $('<div class="glazing-feature-items"></div>');
                categories[category].forEach(feature => {
                    const $featureSwatch = $('<div class="glazing-feature-swatch" data-feature="' + feature.value + '"></div>');

                    // Check if feature has an image
                    if (feature.feature_image && feature.feature_image.url) {
                        const $img = $('<img src="' + feature.feature_image.url + '" alt="' + feature.name + '" />');
                        $featureSwatch.addClass('has-image').append($img);
                    } else {
                        $featureSwatch.text(feature.name);
                    }

                    $featureSwatch.attr('title', feature.name);

                    const $featureLabel = $('<span class="glazing-feature-label">' + feature.name + '</span>');

                    const $featureItem = $('<div class="glazing-feature-item"></div>');
                    $featureItem.append($featureSwatch).append($featureLabel);

                    // Mark selected if it matches
                    if (feature.value === selectedFeature) {
                        $featureItem.addClass('selected');
                    }

                    $featureItem.on('click', function() {
                        self.selectModalGlazingFeature('modal-glazing-features-grid', feature.value, feature.name);
                    });

                    $featureItems.append($featureItem);
                });

                $categoryGroup.append($featureItems);
                $grid.append($categoryGroup);
            });
        },

        selectModalGlazingFeature: function(gridId, featureValue, featureName) {
            // Update the modalSelectedGlazingFeature value
            this.modalSelectedGlazingFeature = featureValue;

            // Update display
            $('#modal-glazing-features-name').text(featureName);

            // Update visual selection
            $('#' + gridId + ' .glazing-feature-item').removeClass('selected');
            $('#' + gridId + ' .glazing-feature-item').filter(function() {
                return $(this).find('.glazing-feature-swatch').data('feature') === featureValue;
            }).addClass('selected');
        },

        renderModalHardwareColourGrid: function(gridId, selectedColour) {
            const self = this;
            const $grid = $('#' + gridId);

            $grid.empty();

            // Render hardware colours (no categories, just a simple grid)
            const $colourItems = $('<div class="colour-items"></div>');

            this.hardwareColours.forEach(colour => {
                const colourName = colour.label || colour.name;
                const colourValue = colour.value || colour.label;
                const colourHex = colour.hex || '#CCCCCC';

                const $colourSwatch = $('<div class="colour-swatch" data-colour="' + colourValue + '" data-hex="' + colourHex + '"></div>');

                // Use hex color (no images for hardware colours)
                $colourSwatch.css('background-color', colourHex);
                $colourSwatch.attr('title', colourName);

                const $colourLabel = $('<span class="colour-label">' + colourName + '</span>');

                const $colourItem = $('<div class="colour-item"></div>');
                $colourItem.append($colourSwatch).append($colourLabel);

                // Mark selected colour
                if (colourValue === selectedColour) {
                    $colourItem.addClass('selected');
                }

                $colourItem.on('click', function() {
                    const value = $(this).find('.colour-swatch').data('colour');
                    self.selectModalHardwareColour(value, colourName);
                });

                $colourItems.append($colourItem);
            });

            $grid.append($colourItems);
        },

        selectModalHardwareColour: function(colourValue, colourName) {
            // Update the modalSelectedHardwareColour value
            this.modalSelectedHardwareColour = colourValue;

            // Update display
            $('#modal-hardware-colour-name').text(colourName);

            // Update visual selection
            $('#modal-hardware-colour-grid .colour-item').removeClass('selected');
            $('#modal-hardware-colour-grid .colour-item').filter(function() {
                return $(this).find('.colour-swatch').data('colour') === colourValue;
            }).addClass('selected');
        },

        renderModalGlazingTypeGrid: function(gridId, selectedType, selectedPattern) {
            const self = this;
            const $grid = $('#' + gridId);

            $grid.empty();

            // Get all glazing type cards from the main form
            const $mainCards = $('.glazing-type-card').clone();

            $mainCards.each(function() {
                const $card = $(this);
                const glazingType = $card.data('glazing-type');
                const patterns = $card.data('patterns');

                // Mark selected type
                if (glazingType === selectedType) {
                    $card.addClass('selected');
                }

                // Add click handler
                $card.on('click', function() {
                    const type = $(this).data('glazing-type');
                    const typeName = $(this).find('.glazing-type-label').text();
                    const patternsData = $(this).data('patterns');
                    self.selectModalGlazingType(type, typeName, patternsData, selectedPattern);
                });

                $grid.append($card);
            });

            // If there's a selected type with patterns, show the pattern grid
            if (selectedType) {
                const $selectedCard = $('.glazing-type-card[data-glazing-type="' + selectedType + '"]');
                if ($selectedCard.length > 0) {
                    const patterns = $selectedCard.data('patterns');
                    if (patterns && patterns.length > 0) {
                        self.renderModalGlazingPatternGrid(patterns, selectedPattern);
                        $('#modal-glazing-pattern-group').show();
                    }
                }
            }
        },

        selectModalGlazingType: function(glazingType, glazingTypeName, patterns, selectedPattern) {
            // Update the modal selected values
            this.modalSelectedGlazingType = glazingType;

            // Update display
            $('#modal-glazing-type-name').text(glazingTypeName);

            // Update visual selection
            $('#modal-glazing-type-grid .glazing-type-card').removeClass('selected');
            $('#modal-glazing-type-grid .glazing-type-card[data-glazing-type="' + glazingType + '"]').addClass('selected');

            // Show/hide patterns based on availability
            if (patterns && patterns.length > 0) {
                this.renderModalGlazingPatternGrid(patterns, selectedPattern);
                $('#modal-glazing-pattern-group').show();
            } else {
                $('#modal-glazing-pattern-group').hide();
                this.modalSelectedGlazingPattern = '';
            }
        },

        renderModalGlazingPatternGrid: function(patterns, selectedPattern) {
            const self = this;
            const $grid = $('#modal-glazing-pattern-grid');

            $grid.empty();

            patterns.forEach(pattern => {
                const patternName = pattern.name || '';
                const patternValue = pattern.value || '';
                const patternImage = pattern.image && pattern.image.url ? pattern.image.url : '';

                const $patternCard = $('<div class="glazing-pattern-card" data-pattern="' + patternValue + '"></div>');

                if (patternImage) {
                    const $patternImage = $('<div class="glazing-pattern-image"></div>');
                    $patternImage.css('background-image', 'url(' + patternImage + ')');
                    $patternCard.append($patternImage);
                }

                const $patternLabel = $('<div class="glazing-pattern-label">' + patternName + '</div>');
                $patternCard.append($patternLabel);

                // Mark selected pattern
                if (patternValue === selectedPattern) {
                    $patternCard.addClass('selected');
                }

                $patternCard.on('click', function() {
                    const value = $(this).data('pattern');
                    self.selectModalGlazingPattern(value);
                });

                $grid.append($patternCard);
            });
        },

        selectModalGlazingPattern: function(patternValue) {
            // Update the modal selected pattern
            this.modalSelectedGlazingPattern = patternValue;

            // Update visual selection
            $('#modal-glazing-pattern-grid .glazing-pattern-card').removeClass('selected');
            $('#modal-glazing-pattern-grid .glazing-pattern-card[data-pattern="' + patternValue + '"]').addClass('selected');
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
