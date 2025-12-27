jQuery(document).ready(function($) {
    'use strict';

    // State Management
    const QuotationForm = {
        currentStep: 1,
        currentSubStep: '1a',
        basket: [],
        currentItem: {},
        editingItemId: null,
        uploadedFiles: [], // Store uploaded files as base64

        // Available colours - Use dynamic colours from config if available, otherwise fallback to hardcoded
        colours: (function() {
            const colours = (typeof quotationFormAjax !== 'undefined' && quotationFormAjax.config && quotationFormAjax.config.colours)
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
            ];

            // Debug logging
            console.log('=== COLOURS LOADED ===');
            console.log('Total colours:', colours.length);
            const colour1007 = colours.find(c => c.name === '1007');
            console.log('Colour 1007:', colour1007);
            if (colour1007) {
                console.log('1007 category:', colour1007.category);
                console.log('1007 finish_type:', colour1007.finish_type);
            }
            console.log('======================');

            return colours;
        })(),

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

        // Available cill options - Use dynamic options from config if available
        cillOptions: (typeof quotationFormAjax !== 'undefined' && quotationFormAjax.config && quotationFormAjax.config.cillOptions)
            ? quotationFormAjax.config.cillOptions
            : [
                { label: '150mm Sill', value: '150mm' },
                { label: '200mm Sill', value: '200mm' },
                { label: 'No Sill', value: 'none' }
            ],

        // Available openings - Use dynamic openings from config if available
        openings: (typeof quotationFormAjax !== 'undefined' && quotationFormAjax.config && quotationFormAjax.config.openings)
            ? quotationFormAjax.config.openings
            : [],

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

        // Capitalize text values for display (converts "low-e-double" to "Low E Double")
        capitalizeValue: function(text) {
            if (!text || text === '') {
                return text;
            }

            // Replace hyphens and underscores with spaces
            text = text.replace(/[-_]/g, ' ');

            // Capitalize each word
            text = text.toLowerCase().replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            });

            return text;
        },

        saveState: function() {
            const state = {
                currentStep: this.currentStep,
                currentSubStep: this.currentSubStep,
                basket: this.basket,
                currentItem: this.currentItem
            };
            try {
                localStorage.setItem('quotationFormState', JSON.stringify(state));
            } catch (e) {
                console.error('Failed to save state:', e);
            }
        },

        loadState: function() {
            try {
                const savedState = localStorage.getItem('quotationFormState');
                if (savedState) {
                    const state = JSON.parse(savedState);
                    this.currentStep = state.currentStep || 1;
                    this.currentSubStep = state.currentSubStep || '1a';
                    this.basket = state.basket || [];
                    this.currentItem = state.currentItem || {};
                    return true;
                }
            } catch (e) {
                console.error('Failed to load state:', e);
            }
            return false;
        },

        clearState: function() {
            try {
                localStorage.removeItem('quotationFormState');
            } catch (e) {
                console.error('Failed to clear state:', e);
            }
        },

        // Postcode validation for service area
        postcodeValidation: {
            // Service area outward codes
            allowedPostcodes: new Set([
                // Berkshire
                'SL4','SL5','RG40','RG41','RG45',
                // Hampshire
                'RG21','RG22','RG23','RG24','RG25','RG27','RG29',
                'GU11','GU12','GU14','GU35','GU46','GU47','GU51','GU52',
                'GU30','GU31','GU32','GU33',
                // Surrey
                'GU6','GU7','GU8','GU9','GU10',
                'GU15','GU16','GU18','GU19','GU20',
                'GU21','GU22','GU23','GU24','GU25',
                'GU26','GU27','GU1','GU2','GU3','GU4','GU5',
                'KT11','KT12','KT13','KT14','KT15','KT16'
            ]),

            // UK postcode regex
            ukPostcodeRegex: /^(GIR\s?0AA|[A-Z]{1,2}\d[A-Z\d]?\s?\d[A-Z]{2})$/i,

            normalize: function(value) {
                return (value || '').toUpperCase().replace(/\s+/g, '').trim();
            },

            getOutwardCode: function(value) {
                const cleaned = this.normalize(value);
                return cleaned.length >= 4 ? cleaned.slice(0, cleaned.length - 3) : '';
            },

            validate: function(value) {
                const trimmed = (value || '').trim();
                if (!trimmed) {
                    return 'Please enter your postcode.';
                }
                if (!this.ukPostcodeRegex.test(trimmed)) {
                    return 'Please enter a valid UK postcode (e.g. GU21 4AA).';
                }
                const outward = this.getOutwardCode(trimmed);
                const isAllowed = Array.from(this.allowedPostcodes).some(code => outward.startsWith(code));
                if (!isAllowed) {
                    return 'Sorry—this postcode is outside our service area.';
                }
                return ''; // valid
            }
        },

        setupPostcodeValidation: function() {
            const self = this;
            const input = $('#customer-postcode')[0];
            if (!input || input.dataset.pcBound) return;

            input.dataset.pcBound = '1';

            const validatePostcode = function() {
                const errorMsg = self.postcodeValidation.validate(input.value);
                input.setCustomValidity(errorMsg);
                input.reportValidity();
            };

            // Real-time validation
            $(input).on('input blur', validatePostcode);

            // Prevent form submission if invalid
            $('#quotation-form').on('submit', function(e) {
                const errorMsg = self.postcodeValidation.validate(input.value);
                if (errorMsg) {
                    e.preventDefault();
                    e.stopPropagation();
                    input.setCustomValidity(errorMsg);
                    input.reportValidity();
                    return false;
                }
            });
        },

        setupFileUpload: function() {
            const self = this;
            const $fileInput = $('#frame-images');
            const $filePreview = $('#file-preview');

            $fileInput.on('change', function(e) {
                const files = Array.from(e.target.files);

                // Only allow 1 file
                if (files.length === 0) {
                    return;
                }

                // Validate file size (max 1MB)
                const maxFileSize = 1 * 1024 * 1024; // 1MB in bytes
                const file = files[0]; // Only take the first file

                if (file.size > maxFileSize) {
                    alert('File is too large. Maximum file size is 1MB.');
                    this.value = '';
                    return;
                }

                // Convert file to base64
                self.uploadedFiles = [];
                const reader = new FileReader();
                reader.onload = function(e) {
                    self.uploadedFiles.push({
                        name: file.name,
                        type: file.type,
                        size: file.size,
                        data: e.target.result
                    });
                    self.renderFilePreview();
                };
                reader.readAsDataURL(file);
            });
        },

        renderFilePreview: function() {
            const $preview = $('#file-preview');
            $preview.empty();

            this.uploadedFiles.forEach((file, index) => {
                const $fileItem = $('<div class="file-preview-item"></div>');
                $fileItem.append('<span>' + file.name + '</span>');
                $fileItem.append('<span class="remove-file" data-index="' + index + '">×</span>');
                $preview.append($fileItem);
            });

            // Bind remove file events
            const self = this;
            $('.remove-file').on('click', function() {
                const index = $(this).data('index');
                self.removeFile(index);
            });
        },

        removeFile: function(index) {
            this.uploadedFiles.splice(index, 1);
            this.renderFilePreview();

            // Clear the file input if no files remain
            if (this.uploadedFiles.length === 0) {
                $('#frame-images').val('');
            }
        },

        init: function() {
            this.debugColours(); // Debug colour data
            this.loadState(); // Restore saved state if available
            this.bindEvents();
            this.initializeColourPickers();
            this.initializeGlazingTypePicker();
            this.initializeGlazingFeaturesPicker();
            this.initializeHardwareColourPicker();
            this.setupPostcodeValidation(); // Setup postcode validation
            this.setupFileUpload(); // Setup file upload handling

            // Render basket first (updates count before showing)
            if (this.basket.length > 0) {
                this.renderBasket();
            }

            // Then show basket (already has correct count)
            this.showBasketReview();

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

            // Aluminium colour type selection (Stock vs Special)
            $('.aluminium-type-card').on('click', function() {
                const aluminiumType = $(this).data('aluminium-type');
                self.currentItem.aluminiumColourType = aluminiumType;
                $('#aluminium-colour-type').val(aluminiumType);

                // Visual selection
                $('.aluminium-type-card').removeClass('selected');
                $(this).addClass('selected');

                // Handle Stock vs Special
                self.handleAluminiumColourType(aluminiumType);
            });

            // Style selection (Step 2)
            $('.card-grid.style-grid .image-card').on('click', function() {
                const style = $(this).data('style');
                self.currentItem.style = style;
                self.currentItem.styleName = $(this).find('h3').text();
                self.currentItem.styleImage = $(this).find('img').attr('src');
                self.selectCard($(this));

                // Check if current product type has openings
                setTimeout(function() {
                    if (self.hasOpeningsForCurrentType()) {
                        self.showOpeningSelection();
                    } else {
                        self.navigateToStep(3);
                        self.updateConfigurationPreview();
                    }
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
                const step = $(this).data('step');

                // Allow clicking on basket at any time
                if (step === 'basket') {
                    self.showBasketReview();
                    return;
                }

                const stepNum = parseInt(step);
                if (stepNum < self.currentStep) {
                    self.navigateToStep(stepNum);
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
            this.saveState(); // Save state after navigation

            // Scroll disabled per user request
            // setTimeout(function() {
            //     $('html, body').animate({ scrollTop: 0 }, 300);
            // }, 50);
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

                // Handle aluminium material in configuration step
                if (step === 3) {
                    this.handleAluminiumMaterialConfiguration();
                }
            }

            this.updateProgressIndicator();
            this.updateNavigationButtons();
            this.saveState(); // Save state after navigation

            // Scroll disabled per user request
            // setTimeout(function() {
            //     $('html, body').animate({ scrollTop: 0 }, 300);
            // }, 50);
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
            const currentStep = this.currentStep;

            $('.progress-step').each(function() {
                const stepData = $(this).data('step');

                // Handle basket step separately - it's always clickable
                if (stepData === 'basket') {
                    // Always keep clickable class
                    $(this).addClass('clickable');

                    if (currentStep === 'basket') {
                        $(this).addClass('active').removeClass('completed');
                    } else {
                        $(this).removeClass('active completed');
                    }
                    return;
                }

                const stepNum = parseInt(stepData);
                if (isNaN(stepNum)) return; // Skip if not a valid number

                if (stepNum < currentStep && currentStep !== 'basket') {
                    $(this).addClass('completed').removeClass('active');
                } else if (stepNum === currentStep) {
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
                // Hide back button on basket step
                $prevBtn.hide();
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

            // Filter colours by selected material (use material slug for comparison)
            const selectedMaterial = this.currentItem.material || '';
            const filteredColours = this.filterColoursByMaterial(selectedMaterial);

            // Group by category
            const categories = {};
            filteredColours.forEach(colour => {
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

        selectColour: function(gridId, colourName, isInside, colourCategory, clickedFinishType) {
            const prefix = isInside ? 'inside' : 'outside';

            // Update hidden field
            $('#' + prefix + '-colour').val(colourName);

            // Find the colour object - for Aluminium Special Colours, also match finish type
            let colour;
            if (colourCategory === 'Aluminium Special Colours' && clickedFinishType) {
                // Match by name, category AND finish type to get the correct variant
                colour = this.colours.find(c =>
                    c.name === colourName &&
                    c.category === colourCategory &&
                    c.finish_type === clickedFinishType
                );
            } else if (colourCategory) {
                // Match by name and category
                colour = this.colours.find(c => c.name === colourName && c.category === colourCategory);
            } else {
                // Match by name only
                colour = this.colours.find(c => c.name === colourName);
            }
            let displayText = colourName;

            // Debug logging
            console.log('Selected colour:', colourName, 'Category:', colourCategory, 'Finish:', clickedFinishType);
            console.log('Colour object:', colour);
            if (colour) {
                console.log('Category:', colour.category);
                console.log('Finish type:', colour.finish_type);
            }

            // For aluminium special colours, prepend finish type (now single value from backend)
            if (colour && colour.category === 'Aluminium Special Colours' && colour.finish_type) {
                displayText = colour.finish_type + ' ' + colourName;
                console.log('Display text with finish type:', displayText);
            }

            // Update display
            $('#' + prefix + '-colour-name').text(displayText);

            // Update visual selection
            $('#' + gridId + ' .colour-item').removeClass('selected');
            $('#' + gridId + ' .colour-item').filter(function() {
                return $(this).find('.colour-swatch').data('colour') === colourName;
            }).addClass('selected');
        },

        filterColours: function(gridId, searchTerm) {
            const $grid = $('#' + gridId);

            // Filter individual colour items
            $grid.find('.colour-item').each(function() {
                const colourData = $(this).find('.colour-swatch').data('colour');
                const colourName = String(colourData || '').toLowerCase();
                if (colourName.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            // Hide/show finish type groups based on whether they have visible items
            $grid.find('.colour-category').each(function() {
                const $category = $(this);
                const visibleItems = $category.find('.colour-item:visible').length;

                if (visibleItems > 0) {
                    $category.show();
                } else {
                    $category.hide();
                }
            });
        },

        filterColoursByMaterial: function(material) {
            const typeSlug = (this.currentItem.type || '').toLowerCase();
            return this.filterColoursByMaterialAndType(material, typeSlug);
        },

        filterColoursByMaterialAndType: function(material, type) {
            const materialSlug = (material || '').toLowerCase();
            const typeSlug = (type || '').toLowerCase();

            return this.colours.filter(colour => {
                // Check material availability
                let materialMatches = true;
                if (colour.available_materials && colour.available_materials.length > 0) {
                    materialMatches = colour.available_materials.some(m => m.toLowerCase() === materialSlug);
                }

                // Check finish-specific exclusions (for fine-grained control)
                let finishMatches = true;
                if (colour.finish_exclusions && colour.finish_exclusions.length > 0 && typeSlug) {
                    // Check if there's a rule for this product type
                    const exclusionRule = colour.finish_exclusions.find(rule =>
                        rule.product_type && rule.product_type.toLowerCase() === typeSlug
                    );

                    if (exclusionRule && exclusionRule.excluded_finishes && exclusionRule.excluded_finishes.length > 0) {
                        // If this colour's finish type is in the excluded list, exclude it
                        const colourFinish = (colour.finish_type || '').toLowerCase();
                        const isExcluded = exclusionRule.excluded_finishes.some(f => f.toLowerCase() === colourFinish);
                        finishMatches = !isExcluded;
                    }
                }

                // Both conditions must be true
                return materialMatches && finishMatches;
            });
        },

        handleAluminiumColourType: function(aluminiumType) {
            // Reset colour selections when switching between Stock/Special
            $('#inside-colour').val('');
            $('#outside-colour').val('');
            $('#inside-colour-name').text('None');
            $('#outside-colour-name').text('None');
            $('.colour-item').removeClass('selected');

            // Clear currentItem colour values
            this.currentItem.insideColour = '';
            this.currentItem.outsideColour = '';

            // Clear search inputs
            $('#inside-colour-search').val('');
            $('#outside-colour-search').val('');

            if (aluminiumType === 'stock') {
                // Stock colours: Show form group and first colour section
                $('.form-group:has(.colour-selection)').show();
                $('.colour-selection').first().show(); // Show first (inside)
                $('.colour-selection').last().hide(); // Hide second (outside)
                $('.inside-colour-label').text('Select Colour');

                // Re-render grid with only stock colours
                this.renderAluminiumStockColours();
            } else if (aluminiumType === 'special') {
                // Special colours: Show both Inside/Outside sections, change labels
                $('.form-group:has(.colour-selection)').show();
                $('.colour-selection').show();
                $('.inside-colour-label').text('External');
                $('.outside-colour-label').text('Internal');

                // Re-render grids with only special colours
                this.renderAluminiumSpecialColours();
            }
        },

        renderAluminiumStockColours: function() {
            const self = this;
            const $grid = $('#inside-colour-grid');
            $grid.empty();

            // Filter colours by selected material first, then by category (use material slug for comparison)
            const selectedMaterial = this.currentItem.material || '';
            const materialFilteredColours = this.filterColoursByMaterial(selectedMaterial);
            const stockColours = materialFilteredColours.filter(c => c.category === 'Aluminium Stock Colours');

            const $colourItems = $('<div class="colour-items aluminium-stock-items"></div>');
            stockColours.forEach(colour => {
                const $colourSwatch = $('<div class="colour-swatch" data-colour="' + colour.name + '" data-category="' + colour.category + '" data-hex="' + colour.hex + '"></div>');

                if (colour.colour_image && colour.colour_image.url) {
                    const $img = $('<img src="' + colour.colour_image.url + '" alt="' + colour.name + '" />');
                    $colourSwatch.addClass('has-image').append($img);
                } else {
                    $colourSwatch.css('background-color', colour.hex);
                }

                $colourSwatch.attr('title', colour.name);
                const $colourLabel = $('<span class="colour-label">' + colour.name + '</span>');
                const $colourItem = $('<div class="colour-item"></div>');
                $colourItem.append($colourSwatch).append($colourLabel);

                $colourItem.on('click', function() {
                    const colourName = String($(this).find('.colour-swatch').data('colour'));
                    const colourCategory = $(this).find('.colour-swatch').data('category');
                    // Apply to both inside and outside
                    self.selectColour('inside-colour-grid', colourName, true, colourCategory);
                    self.selectColour('outside-colour-grid', colourName, false, colourCategory);
                    $('#inside-colour').val(colourName);
                    $('#outside-colour').val(colourName);
                    $('#inside-colour-name').text(colourName);
                    $('#outside-colour-name').text(colourName);
                });

                $colourItems.append($colourItem);
            });

            $grid.append($colourItems);
        },

        renderAluminiumSpecialColours: function() {
            const self = this;

            // Filter colours by selected material first, then by category (use material slug for comparison)
            const selectedMaterial = this.currentItem.material || '';
            const materialFilteredColours = this.filterColoursByMaterial(selectedMaterial);
            const specialColours = materialFilteredColours.filter(c => c.category === 'Aluminium Special Colours');

            // Render for both inside and outside grids
            ['inside-colour-grid', 'outside-colour-grid'].forEach(gridId => {
                const $grid = $('#' + gridId);
                const isInside = gridId.includes('inside');
                $grid.empty();

                // Group colours by finish type
                const finishTypes = {
                    'Matt': [],
                    'Gloss': [],
                    'Metallic': [],
                    'Satin': []
                };

                specialColours.forEach(colour => {
                    let finishTypesArray = [];

                    // Handle both array (multi-select) and string (old single select)
                    if (Array.isArray(colour.finish_type)) {
                        finishTypesArray = colour.finish_type.length > 0 ? colour.finish_type : ['Matt'];
                    } else if (colour.finish_type) {
                        finishTypesArray = [colour.finish_type];
                    } else {
                        finishTypesArray = ['Matt']; // Default
                    }

                    // Add colour to all selected finish types
                    finishTypesArray.forEach(ft => {
                        if (finishTypes[ft]) {
                            finishTypes[ft].push(colour);
                        }
                    });
                });

                // Render colours by finish type (similar to category grouping)
                Object.keys(finishTypes).forEach(finishType => {
                    if (finishTypes[finishType].length === 0) return; // Skip empty groups

                    const $finishGroup = $('<div class="colour-category"></div>');
                    $finishGroup.append('<h5>' + finishType.toUpperCase() + '</h5>');

                    const $colourItems = $('<div class="colour-items"></div>');
                    finishTypes[finishType].forEach(colour => {
                        const $colourSwatch = $('<div class="colour-swatch" data-colour="' + colour.name + '" data-category="' + colour.category + '" data-finish-type="' + finishType + '" data-hex="' + colour.hex + '"></div>');

                        // Use hex color only (no image for aluminium special colours)
                        $colourSwatch.css('background-color', colour.hex);
                        $colourSwatch.attr('title', colour.name);

                        const $colourLabel = $('<span class="colour-label">' + colour.name + '</span>');
                        const $colourItem = $('<div class="colour-item"></div>');
                        $colourItem.append($colourSwatch).append($colourLabel);

                        $colourItem.on('click', function() {
                            const colourName = String($(this).find('.colour-swatch').data('colour'));
                            const colourCategory = $(this).find('.colour-swatch').data('category');
                            const clickedFinishType = $(this).find('.colour-swatch').data('finish-type');
                            self.selectColour(gridId, colourName, isInside, colourCategory, clickedFinishType);
                        });

                        $colourItems.append($colourItem);
                    });

                    $finishGroup.append($colourItems);
                    $grid.append($finishGroup);
                });
            });
        },

        handleAluminiumMaterialConfiguration: function() {
            const material = this.currentItem.material || '';
            console.log('handleAluminiumMaterialConfiguration - Material:', material);

            // Check if aluminium material is selected
            if (material.toLowerCase().includes('aluminium') || material.toLowerCase().includes('aluminum')) {
                console.log('Aluminium detected - showing aluminium colour type selection');
                // Show aluminium colour type selection
                $('.aluminium-colour-type-selection').show();

                // Default to Stock colours
                this.currentItem.aluminiumColourType = 'stock';
                $('#aluminium-colour-type').val('stock');

                // Select stock card by default
                $('.aluminium-type-card').removeClass('selected');
                $('.aluminium-type-card[data-aluminium-type="stock"]').addClass('selected');

                // Automatically show stock colours
                this.handleAluminiumColourType('stock');
            } else {
                console.log('Non-aluminium material - showing both colour sections');
                // Hide aluminium colour type selection
                $('.aluminium-colour-type-selection').hide();

                // Show regular colour selection with normal labels
                $('.form-group:has(.colour-selection)').show();
                $('.colour-selection').show(); // Ensure both colour sections are visible

                console.log('Colour sections visibility:', {
                    insideVisible: $('.colour-selection').first().is(':visible'),
                    outsideVisible: $('.colour-selection').last().is(':visible'),
                    count: $('.colour-selection').length
                });

                $('.inside-colour-label').text('Inside Colour');
                $('.outside-colour-label').text('Outside Colour');

                // Render normal colours
                this.initializeColourPickers();
            }
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

            // Store current glazing type name for pattern display
            this.currentGlazingTypeName = glazingTypeName;

            // Update display (will be updated again if pattern is selected)
            $('#glazing-type-name').text(glazingTypeName);

            // Update visual selection
            $('.glazing-type-card').removeClass('selected');
            $('.glazing-type-card[data-glazing-type="' + glazingType + '"]').addClass('selected');

            // Show/hide patterns based on availability
            if (patterns && patterns.length > 0) {
                this.renderGlazingPatterns(patterns);
                $('#glazing-pattern-group').show();
                // Reset pattern selection when changing glazing type
                $('#glazing-pattern').val('');
                $('.glazing-pattern-card').removeClass('selected');
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

            // Update display to include pattern name
            if (this.currentGlazingTypeName && patternName) {
                $('#glazing-type-name').text(this.currentGlazingTypeName + ' - ' + patternName);
            }

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
            $('#preview-category').text(this.capitalizeValue(this.currentItem.category) || '');
            $('#preview-type').text(this.capitalizeValue(this.currentItem.typeName) || '');
            $('#preview-material').text(this.currentItem.materialName || 'N/A');
            $('#preview-style').text(this.capitalizeValue(this.currentItem.styleName) || '');
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
                glazingFeaturesName: $('#glazing-features-name').text(),
                hardwareColour: $('#hardware-colour').val(),
                hardwareColourName: $('#hardware-colour-name').text(),
                location: '', // Can be set later
                attachedFiles: this.uploadedFiles.length > 0 ? [...this.uploadedFiles] : [] // Copy uploaded files
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
            this.saveState(); // Save state after adding/updating item
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
            $('#frame-images').val('');
            this.uploadedFiles = [];
            $('#file-preview').empty();
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
            $('.basket-counter').text(this.basket.length);

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
            $thumbnail.append('<img src="' + item.styleImage + '" alt="' + item.styleName + '" loading="lazy">');
            $thumbnail.append('<p class="style-name">' + item.styleName + '</p>');

            const $details = $('<div class="item-details"></div>');
            $details.append('<h3>Item ' + (index + 1) + '</h3>');

            // Location - always editable
            const $locationContainer = $('<p class="item-location"></p>');
            const locationText = item.location ? item.location : 'set location';
            const locationClass = item.location ? 'edit-location-link' : 'set-location-link';
            const $locationLink = $('<a href="#" class="' + locationClass + '">' + (item.location ? '<strong>Location:</strong> ' + locationText : locationText) + '</a>');

            $locationLink.on('click', function(e) {
                e.preventDefault();
                const currentLocation = item.location || '';
                const location = prompt('Enter location for this item:', currentLocation);
                if (location !== null && location !== currentLocation) {
                    item.location = location;
                    self.renderBasket();
                    self.saveState(); // Save state after updating location
                }
            });

            $locationContainer.append($locationLink);
            $details.append($locationContainer);

            const $table = $('<table class="item-summary"></table>');

            // For glazing display: if we have glazingTypeName, use it (already includes pattern)
            // Otherwise combine glazingType with pattern
            const glazingValue = item.glazingTypeName ||
                (item.glazingPattern
                    ? item.glazingType + ' - ' + item.glazingPattern
                    : item.glazingType);

            // Use display names for Glazing Features and Hardware Colour
            const glazingFeaturesDisplay = item.glazingFeaturesName || item.glazingFeatures;
            const hardwareColourDisplay = item.hardwareColourName || item.hardwareColour;

            const fields = [
                { label: 'Product', value: (item.materialName || '') + ' ' + item.typeName, field: 'product' },
                { label: 'Size', value: item.width + 'w x ' + item.height + 'h mm', field: 'size' },
                { label: 'Colours', value: item.insideColour + ' / ' + item.outsideColour, field: 'colour' },
                { label: 'Glazing Type', value: glazingValue, field: 'glazing' },
                { label: 'Glazing Feature', value: glazingFeaturesDisplay, field: 'glazingFeatures' },
                { label: 'Hardware Colour', value: hardwareColourDisplay, field: 'hardware' }
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

            // Display attached file if any
            if (item.attachedFiles && item.attachedFiles.length > 0) {
                const $filesSection = $('<div class="item-attached-files"></div>');
                $filesSection.append('<p><strong>Attached Image:</strong></p>');
                const $filesList = $('<ul class="attached-files-list"></ul>');
                item.attachedFiles.forEach(file => {
                    $filesList.append('<li>' + file.name + ' (' + Math.round(file.size / 1024) + 'KB)</li>');
                });
                $filesSection.append($filesList);
                $details.append($filesSection);
            }

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

                // Build cill options dynamically from settings
                let cillOptionsHtml = '<div class="edit-field-group"><label>Cill:</label><select id="edit-cill">';
                this.cillOptions.forEach(function(option) {
                    const selected = item.cill === option.value ? ' selected' : '';
                    cillOptionsHtml += '<option value="' + option.value + '"' + selected + '>' + option.label + '</option>';
                });
                cillOptionsHtml += '</select></div>';
                $content.append(cillOptionsHtml);
            } else if (field === 'colour') {
                // Store modal selected colors
                this.modalSelectedColors = {
                    inside: item.insideColour,
                    outside: item.outsideColour
                };

                // Store item material and type for filtering
                this.modalItemMaterial = item.material || '';
                this.modalItemType = item.type || '';

                // Check if material is aluminium
                const isAluminium = this.modalItemMaterial.toLowerCase().includes('aluminium') ||
                                   this.modalItemMaterial.toLowerCase().includes('aluminum');

                // Add aluminium type selection if aluminium material
                if (isAluminium) {
                    // Get or default aluminium type
                    this.modalAluminiumType = item.aluminiumColourType || 'stock';

                    $content.append('<div class="edit-field-group">' +
                        '<label>Select Colour Type</label>' +
                        '<div class="aluminium-type-grid">' +
                            '<div class="aluminium-type-card' + (this.modalAluminiumType === 'stock' ? ' selected' : '') + '" data-aluminium-type="stock">' +
                                '<h4>Aluminium Stock Colours</h4>' +
                                '<p>Choose from 4 standard colours</p>' +
                            '</div>' +
                            '<div class="aluminium-type-card' + (this.modalAluminiumType === 'special' ? ' selected' : '') + '" data-aluminium-type="special">' +
                                '<h4>Aluminium Special Colours</h4>' +
                                '<p>Choose custom external and internal colours</p>' +
                            '</div>' +
                        '</div>' +
                        '</div>');
                }

                // Create inside colour picker
                const insideLabel = isAluminium && this.modalAluminiumType === 'stock' ? 'Select Colour' :
                                   isAluminium && this.modalAluminiumType === 'special' ? 'External' : 'Inside Colour';

                $content.append('<div class="edit-field-group modal-colour-picker">' +
                    '<label id="modal-inside-colour-label">' + insideLabel + ':</label>' +
                    '<div class="colour-selection-display">Selected: <strong id="modal-inside-colour-name">' + item.insideColour + '</strong></div>' +
                    '<input type="text" id="modal-inside-colour-search" class="modal-colour-search" placeholder="Search colours...">' +
                    '<div id="modal-inside-colour-grid" class="colour-grid modal-colour-grid"></div>' +
                    '</div>');

                // Create outside colour picker (hide for aluminium stock)
                const outsideLabel = isAluminium && this.modalAluminiumType === 'special' ? 'Internal' : 'Outside Colour';
                const outsideDisplay = isAluminium && this.modalAluminiumType === 'stock' ? 'style="display:none;"' : '';

                $content.append('<div class="edit-field-group modal-colour-picker" id="modal-outside-colour-picker" ' + outsideDisplay + '>' +
                    '<label id="modal-outside-colour-label">' + outsideLabel + ':</label>' +
                    '<div class="colour-selection-display">Selected: <strong id="modal-outside-colour-name">' + item.outsideColour + '</strong></div>' +
                    '<input type="text" id="modal-outside-colour-search" class="modal-colour-search" placeholder="Search colours...">' +
                    '<div id="modal-outside-colour-grid" class="colour-grid modal-colour-grid"></div>' +
                    '</div>');

                // Handle aluminium type selection
                if (isAluminium) {
                    $('.aluminium-type-card').on('click', function() {
                        const alType = $(this).data('aluminium-type');
                        self.modalAluminiumType = alType;

                        $('.aluminium-type-card').removeClass('selected');
                        $(this).addClass('selected');

                        // Reset selections
                        self.modalSelectedColors = { inside: '', outside: '' };
                        $('#modal-inside-colour-name').text('None');
                        $('#modal-outside-colour-name').text('None');

                        // Update labels and visibility
                        if (alType === 'stock') {
                            $('#modal-inside-colour-label').text('Select Colour');
                            $('#modal-outside-colour-picker').hide();
                        } else {
                            $('#modal-inside-colour-label').text('External');
                            $('#modal-outside-colour-label').text('Internal');
                            $('#modal-outside-colour-picker').show();
                        }

                        // Re-render grids
                        self.renderModalColourGrid('modal-inside-colour-grid', '', self.modalItemMaterial, alType);
                        self.renderModalColourGrid('modal-outside-colour-grid', '', self.modalItemMaterial, alType);
                    });
                }

                // Render colour grids after a brief delay to ensure DOM is ready
                setTimeout(function() {
                    self.renderModalColourGrid('modal-inside-colour-grid', item.insideColour, self.modalItemMaterial, self.modalAluminiumType);
                    self.renderModalColourGrid('modal-outside-colour-grid', item.outsideColour, self.modalItemMaterial, self.modalAluminiumType);
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

                // Create glazing type picker (display text will be set by renderModalGlazingTypeGrid)
                $content.append('<div class="edit-field-group modal-glazing-type-picker">' +
                    '<label>Glazing Type:</label>' +
                    '<div class="glazing-type-selection-display">Selected: <strong id="modal-glazing-type-name"></strong></div>' +
                    '<div id="modal-glazing-type-grid" class="glazing-type-grid modal-glazing-type-grid"></div>' +
                    '</div>');

                // Add pattern section (initially hidden if no patterns)
                $content.append('<div class="edit-field-group modal-glazing-pattern-picker" id="modal-glazing-pattern-group" style="display: none;">' +
                    '<label>Glazing Pattern:</label>' +
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
                    item.glazingFeaturesName = $('#modal-glazing-features-name').text();
                } else if (field === 'hardware') {
                    item.hardwareColour = self.modalSelectedHardwareColour;
                    item.hardwareColourName = $('#modal-hardware-colour-name').text();
                }

                $modal.hide();
                self.renderBasket();
                self.saveState(); // Save state after editing
            });
        },

        renderModalColourGrid: function(gridId, selectedColour, material, aluminiumType) {
            const self = this;
            const $grid = $('#' + gridId);
            const isInside = gridId.includes('inside');

            $grid.empty();

            // Filter colours by material and type (use modal item type for proper filtering)
            let filteredColours = material ? this.filterColoursByMaterialAndType(material, this.modalItemType) : this.colours;

            // Check if aluminium material
            const isAluminium = material && (material.toLowerCase().includes('aluminium') || material.toLowerCase().includes('aluminum'));

            // For aluminium, filter by colour type (stock/special)
            if (isAluminium && aluminiumType) {
                if (aluminiumType === 'stock') {
                    filteredColours = filteredColours.filter(c => c.category === 'Aluminium Stock Colours');
                } else if (aluminiumType === 'special') {
                    filteredColours = filteredColours.filter(c => c.category === 'Aluminium Special Colours');
                }
            } else if (isAluminium) {
                // Default to stock if no type specified
                filteredColours = filteredColours.filter(c => c.category === 'Aluminium Stock Colours');
            } else {
                // For non-aluminium, exclude aluminium colours
                filteredColours = filteredColours.filter(c =>
                    c.category !== 'Aluminium Stock Colours' &&
                    c.category !== 'Aluminium Special Colours'
                );
            }

            // For aluminium special colours, group by finish type
            if (isAluminium && aluminiumType === 'special') {
                const finishTypes = {
                    'Matt': [],
                    'Gloss': [],
                    'Metallic': [],
                    'Satin': []
                };

                filteredColours.forEach(colour => {
                    let finishTypesArray = [];

                    if (Array.isArray(colour.finish_type)) {
                        finishTypesArray = colour.finish_type.length > 0 ? colour.finish_type : ['Matt'];
                    } else if (colour.finish_type) {
                        finishTypesArray = [colour.finish_type];
                    } else {
                        finishTypesArray = ['Matt'];
                    }

                    finishTypesArray.forEach(ft => {
                        if (finishTypes[ft]) {
                            finishTypes[ft].push(colour);
                        }
                    });
                });

                // Render by finish type
                Object.keys(finishTypes).forEach(finishType => {
                    if (finishTypes[finishType].length === 0) return;

                    const $finishGroup = $('<div class="colour-category"></div>');
                    $finishGroup.append('<h5>' + finishType.toUpperCase() + '</h5>');

                    const $colourItems = $('<div class="colour-items"></div>');
                    finishTypes[finishType].forEach(colour => {
                        this.renderModalColourItem($colourItems, colour, selectedColour, gridId, isInside, finishType);
                    });

                    $finishGroup.append($colourItems);
                    $grid.append($finishGroup);
                });
            } else {
                // Group by category for regular colours
                const categories = {};
                filteredColours.forEach(colour => {
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
                        this.renderModalColourItem($colourItems, colour, selectedColour, gridId, isInside);
                    });

                    $categoryGroup.append($colourItems);
                    $grid.append($categoryGroup);
                });
            }
        },

        renderModalColourItem: function($container, colour, selectedColour, gridId, isInside, finishType) {
            const self = this;
            const $colourSwatch = $('<div class="colour-swatch" data-colour="' + colour.name + '" data-category="' + colour.category + '" data-hex="' + colour.hex + '"></div>');

            // Add finish type if provided (for aluminium special colours)
            if (finishType) {
                $colourSwatch.attr('data-finish-type', finishType);
            }

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
                const colourName = String($(this).find('.colour-swatch').data('colour'));
                const colourCategory = $(this).find('.colour-swatch').data('category');
                const clickedFinishType = $(this).find('.colour-swatch').data('finish-type');
                self.selectModalColour(gridId, colourName, isInside, colourCategory, clickedFinishType);
            });

            $container.append($colourItem);
        },

        selectModalColour: function(gridId, colourName, isInside, colourCategory, clickedFinishType) {
            const prefix = isInside ? 'inside' : 'outside';

            // Update the modalSelectedColors object
            if (isInside) {
                this.modalSelectedColors.inside = colourName;
            } else {
                this.modalSelectedColors.outside = colourName;
            }

            // Find the colour object - for Aluminium Special Colours, also match finish type
            let colour;
            if (colourCategory === 'Aluminium Special Colours' && clickedFinishType) {
                // Match by name, category AND finish type to get the correct variant
                colour = this.colours.find(c =>
                    c.name === colourName &&
                    c.category === colourCategory &&
                    c.finish_type === clickedFinishType
                );
            } else if (colourCategory) {
                // Match by name and category
                colour = this.colours.find(c => c.name === colourName && c.category === colourCategory);
            } else {
                // Match by name only
                colour = this.colours.find(c => c.name === colourName);
            }
            let displayText = colourName;

            // For aluminium special colours, prepend finish type (now single value from backend)
            if (colour && colour.category === 'Aluminium Special Colours' && colour.finish_type) {
                displayText = colour.finish_type + ' ' + colourName;
            }

            // Update display
            $('#modal-' + prefix + '-colour-name').text(displayText);

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
                    const typeName = $selectedCard.find('.glazing-type-label').text().trim();
                    self.modalSelectedGlazingTypeName = typeName;

                    // Set initial display to just the type name
                    $('#modal-glazing-type-name').text(typeName);

                    const patterns = $selectedCard.data('patterns');
                    if (patterns && patterns.length > 0) {
                        self.renderModalGlazingPatternGrid(patterns, selectedPattern);
                        $('#modal-glazing-pattern-group').show();

                        // Update display if there's a selected pattern
                        if (selectedPattern) {
                            const selectedPatternObj = patterns.find(p => p.value === selectedPattern);
                            if (selectedPatternObj && selectedPatternObj.name) {
                                // Extract just the pattern name, in case it contains the full glazing type name
                                let patternDisplayName = selectedPatternObj.name;
                                // If the pattern name starts with the type name, extract just the pattern part
                                if (patternDisplayName.startsWith(typeName)) {
                                    patternDisplayName = patternDisplayName.substring(typeName.length).replace(/^\s*-\s*/, '').trim();
                                }
                                $('#modal-glazing-type-name').text(typeName + ' - ' + patternDisplayName);
                            }
                        }
                    }
                }
            }
        },

        selectModalGlazingType: function(glazingType, glazingTypeName, patterns, selectedPattern) {
            // Update the modal selected values
            this.modalSelectedGlazingType = glazingType;
            this.modalSelectedGlazingTypeName = glazingTypeName;

            // Update display
            $('#modal-glazing-type-name').text(glazingTypeName);

            // Update visual selection
            $('#modal-glazing-type-grid .glazing-type-card').removeClass('selected');
            $('#modal-glazing-type-grid .glazing-type-card[data-glazing-type="' + glazingType + '"]').addClass('selected');

            // Show/hide patterns based on availability
            if (patterns && patterns.length > 0) {
                this.renderModalGlazingPatternGrid(patterns, selectedPattern);
                $('#modal-glazing-pattern-group').show();
                // Reset pattern selection when changing glazing type
                this.modalSelectedGlazingPattern = '';
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
                    self.selectModalGlazingPattern(value, patternName);
                });

                $grid.append($patternCard);
            });
        },

        selectModalGlazingPattern: function(patternValue, patternName) {
            // Update the modal selected pattern
            this.modalSelectedGlazingPattern = patternValue;

            // Update display to include pattern name
            if (this.modalSelectedGlazingTypeName && patternName) {
                $('#modal-glazing-type-name').text(this.modalSelectedGlazingTypeName + ' - ' + patternName);
            }

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
            this.saveState(); // Save state after copying item
        },

        deleteBasketItem: function(itemId) {
            this.basket = this.basket.filter(i => i.id !== itemId);
            this.renderBasket();
            this.saveState(); // Save state after deleting item
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
                street: $('#customer-street').val(),
                town: $('#customer-town').val(),
                county: $('#customer-county').val(),
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
                        // Reset form and basket
                        self.basket = [];
                        self.renderBasket(); // Update basket display and counter
                        self.resetForm();
                        self.navigateToStep(1);
                        self.navigateToSubStep('1a');
                        self.clearState(); // Clear saved state after successful submission
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

        hasOpeningsForCurrentType: function() {
            if (!this.openings || this.openings.length === 0) {
                console.log('No openings configured');
                return false;
            }

            const currentTypeSlug = (this.currentItem.type || '').toLowerCase();
            if (!currentTypeSlug) {
                console.log('No current type selected');
                return false;
            }

            console.log('Checking openings for type:', currentTypeSlug);
            console.log('Available openings:', this.openings);

            // Check if any opening is available for current type
            const hasOpenings = this.openings.some(opening => {
                console.log('Checking opening:', opening.name, 'available_types:', opening.available_types);

                // If no types specified, don't show opening for any type
                if (!opening.available_types || opening.available_types.length === 0) {
                    console.log('  -> No types specified for this opening');
                    return false;
                }

                const typeMatch = opening.available_types.some(type => {
                    const typeSlug = (type || '').toLowerCase();
                    console.log('  -> Comparing:', typeSlug, 'with', currentTypeSlug, '=', typeSlug === currentTypeSlug);
                    return typeSlug === currentTypeSlug;
                });

                return typeMatch;
            });

            console.log('Has openings for current type:', hasOpenings);
            return hasOpenings;
        },

        showOpeningSelection: function() {
            const self = this;

            // Hide style grid and show opening selection
            $('.style-grid').hide();
            $('.form-step[data-step="2"] h2').text('Select Opening Direction');

            // Filter openings for current type
            const currentTypeSlug = (this.currentItem.type || '').toLowerCase();
            const availableOpenings = this.openings.filter(opening => {
                if (!opening.available_types || opening.available_types.length === 0) {
                    return false;
                }
                return opening.available_types.some(type => type.toLowerCase() === currentTypeSlug);
            });

            // Create opening grid
            let openingGridHtml = '<div class="card-grid opening-grid">';
            availableOpenings.forEach(opening => {
                const imageUrl = opening.image && opening.image.url ? opening.image.url : '';
                const slug = opening.slug || '';
                const name = opening.name || '';

                openingGridHtml += `
                    <div class="image-card" data-opening="${slug}">
                        ${imageUrl ? `<img src="${imageUrl}" alt="${name}">` : ''}
                        <h3 class="opening-card-title">${name}</h3>
                    </div>
                `;
            });
            openingGridHtml += '</div>';

            // Add back button
            openingGridHtml += '<div class="form-navigation" style="margin-top: 20px;"><button type="button" class="btn btn-secondary opening-back-btn">Back to Styles</button></div>';

            // Insert opening grid after heading
            $('.form-step[data-step="2"] h2').after(openingGridHtml);

            // Handle opening selection
            $('.opening-grid .image-card').on('click', function() {
                const opening = $(this).data('opening');
                const openingName = $(this).find('h3').text();
                const openingImage = $(this).find('img').attr('src');

                self.currentItem.opening = opening;
                self.currentItem.openingName = openingName;
                self.currentItem.openingImage = openingImage;

                self.selectCard($(this));

                // Navigate to configuration after brief delay
                setTimeout(function() {
                    self.navigateToStep(3);
                    self.updateConfigurationPreview();
                }, 300);
            });

            // Handle back button
            $('.opening-back-btn').on('click', function() {
                self.hideOpeningSelection();
            });
        },

        hideOpeningSelection: function() {
            // Remove opening grid
            $('.opening-grid').remove();
            $('.opening-back-btn').parent().remove();

            // Show style grid again
            $('.style-grid').show();
            $('.form-step[data-step="2"] h2').text('Select Configuration Style');

            // Clear opening selection
            delete this.currentItem.opening;
            delete this.currentItem.openingName;
            delete this.currentItem.openingImage;
        },

        resetForm: function() {
            this.currentItem = {};
            this.editingItemId = null;
            this.resetConfigurationForm();
            $('.image-card').removeClass('selected');
            $('#customer-name').val('');
            $('#customer-email').val('');
            $('#customer-phone').val('');
            $('#customer-street').val('');
            $('#customer-town').val('');
            $('#customer-county').val('');
            $('#customer-postcode').val('');
            $('#additional-notes').val('');
            this.navigateToStep(1);
            this.navigateToSubStep('1a');
        }
    };

    // Initialize the form
    QuotationForm.init();
});
