// Part 2: Create shortcode for the product management table (modified)
function wpm_product_manager_shortcode() {
    if (!current_user_can('manage_woocommerce')) {
        return '';
    }

    ob_start();

    $categories = get_terms('product_cat', array('hide_empty' => false));
    ?>
    <div class="wpm-controls sticky">
        <div class="wpm-button-container">
            <div class="wpm-left-buttons">
                <button id="wpm-add-product" class="button button-primary">Add Product</button>
            </div>
            <div class="wpm-right-buttons">
                <button id="wpm-manage-categories" class="button button-secondary">Manage Categories</button>
            </div>
        </div>
    </div>

    <!-- Category Management Modal -->
    <div id="wpm-category-modal" class="wpm-modal">
        <div class="wpm-modal-content">
            <div class="wpm-category-form">
                <input type="text" id="wpm-new-category" placeholder="New Category Name">
                <button id="wpm-add-category" class="button button-primary">Add Category</button>
            </div>
            <div class="wpm-category-list">
                <table id="wpm-category-table">
                    <tbody>
                    <?php foreach ($categories as $category) :
                        if ($category->name !== 'All') : // Exclude 'All' category from editing
                    ?>
                        <tr data-category-id="<?php echo $category->term_id; ?>" data-original-name="<?php echo esc_attr($category->name); ?>">
                            <td class="category-name-cell">
                                <input type="text" class="category-name-input" value="<?php echo esc_attr($category->name); ?>">
                            </td>
                            <td class="category-actions">
                                <button class="wpm-delete-category" title="Delete category">×</button>
                            </td>
                        </tr>
                    <?php
                        endif;
                    endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="wpm-category-footer">
                <button id="wpm-save-categories" class="button button-primary">Save Changes</button>
            </div>
        </div>
    </div>
    <table id="wpm-product-table">
        <thead>
            <tr>
                <th class="wpm-col-dropdowns">
                    <span class="dropdown-emoji">❎️</span>
                </th>
                <th class="wpm-col-name">
                    <input type="text" id="wpm-search" placeholder="Product Name">
                </th>
                <th class="wpm-col-price">Price</th>
                <th class="wpm-col-offer">Offer</th>
                <th class="wpm-col-status">Status</th>
                <th class="wpm-col-category">
                    <select id="category-header-filter" multiple>
                        <option value="" disabled selected>Category</option>
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?php echo $category->term_id; ?>"><?php echo $category->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </th>
                <th class="wpm-col-delete">❌️</th>
                <th class="wpm-col-images">Images</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $products = wc_get_products(array('limit' => -1));
            foreach ($products as $product) :
                $product_categories = $product->get_category_ids();
                $product_images = array();
                $cover_image = $product->get_image_id();

                if ($cover_image) {
                    $product_images[] = $cover_image;
                }

                $gallery_images = $product->get_gallery_image_ids();
                $product_images = array_merge($product_images, $gallery_images);

                // Get cover image URL or placeholder
                $cover_image_url = $cover_image ? wp_get_attachment_image_url($cover_image, 'thumbnail') : '';
            ?>
            <tr data-product-id="<?php echo $product->get_id(); ?>"
                data-category-ids="<?php echo implode(',', $product_categories); ?>"
                data-image-ids="<?php echo implode(',', $product_images); ?>"
                data-original='<?php echo json_encode(array(
                    'name' => $product->get_name(),
                    'price' => $product->get_regular_price(),
                    'offer' => $product->get_sale_price(),
                    'status' => $product->get_stock_status(),
                    'categories' => $product_categories,
                    'images' => $product_images
                )); ?>'>
                <td><input type="text" class="wpm-name" value="<?php echo esc_attr($product->get_name()); ?>"></td>
                <td><input type="number" step="0.01" class="wpm-price" value="<?php echo $product->get_regular_price(); ?>"></td>
                <td><input type="number" step="0.01" class="wpm-offer" value="<?php echo $product->get_sale_price(); ?>"></td>
                <td>
                    <label class="wpm-status-toggle">
                        <input type="checkbox" class="wpm-status" <?php checked($product->get_stock_status(), 'instock'); ?>>
                        <span class="wpm-slider"></span>
                    </label>
                </td>
                <td>
                    <select class="wpm-category" multiple>
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?php echo $category->term_id; ?>" <?php selected(in_array($category->term_id, $product_categories), true); ?>><?php echo $category->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <button class="wpm-delete-btn" title="Delete product">❌️</button>
                </td>
                <td class="wpm-col-images">
                    <?php if ($cover_image_url) : ?>
                        <button class="wpm-image-btn" style="background-image: url('<?php echo esc_url($cover_image_url); ?>')"></button>
                    <?php else : ?>
                        <button class="wpm-image-btn no-image">No Image</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Image Management Modal -->
    <div id="wpm-image-modal" class="wpm-modal">
        <div class="wpm-modal-content">
            <div class="wpm-image-container">
                <div id="wpm-image-list" class="wpm-image-list"></div>
                <div class="wpm-image-upload" id="wpm-image-upload">
                    <input type="file" id="wpm-image-input" accept="image/*" multiple style="display: none;">
                    <button id="wpm-upload-btn" class="button button-primary">Upload Images</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Alert -->
    <div class="wpm-alert-overlay"></div>
    <div class="wpm-alert">
        <div class="wpm-alert-message"></div>
        <button class="wpm-alert-close">OK</button>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('product_manager', 'wpm_product_manager_shortcode');

// Part 4: Add inline JavaScript
function wpm_add_inline_script() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Get the "All" category ID
        var allCategoryId = <?php echo get_term_by('name', 'All', 'product_cat')->term_id; ?>;

        // Function to filter products by category
        function filterProductsByCategory(selectedCategories) {
            $('#wpm-product-table tbody tr').each(function() {
                var row = $(this);
                var categoryIds = row.data('category-ids').toString().split(',');
                if (!selectedCategories || selectedCategories.length === 0 || categoryIds.some(id => selectedCategories.includes(id))) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        }

        // Category header filter change event
        $('#category-header-filter').on('change', function() {
            var selectedCategories = $(this).val();
            filterProductsByCategory(selectedCategories);
        });

        function updateProduct(row) {
            var productId = row.data('product-id');
            var name = row.find('.wpm-name').val();
            var price = row.find('.wpm-price').val();
            var offer = row.find('.wpm-offer').val();
            var status = row.find('.wpm-status').is(':checked') ? 'instock' : 'outofstock';
            var categories = row.find('.wpm-category').val();

            // Check if categories is null or empty
            if (!categories) {
                categories = [];
            }

            // Ensure "All" category is always included
            if (!categories.includes(allCategoryId.toString())) {
                categories.push(allCategoryId.toString());
            }

            var originalData = row.data('original');

            if (
                productId === 0 ||
                name !== originalData.name ||
                price !== originalData.price ||
                offer !== originalData.offer ||
                status !== originalData.status ||
                !arraysEqual(categories, originalData.categories)
            ) {
                $.ajax({
                    url: wpm_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wpm_update_products',
                        products: [{
                            id: productId,
                            name: name,
                            price: price,
                            offer: offer,
                            status: status,
                            categories: categories
                        }]
                    },
                    success: function(response) {
                        if (response.success) {
                            showSuccessMessage();
                            row.data('original', {
                                name: name,
                                price: price,
                                offer: offer,
                                status: status,
                                categories: categories
                            });
                            row.data('category-ids', categories.join(','));
                            
                            if (productId === 0) {
                                row.data('product-id', response.data.new_product_id);
                                row.attr('data-product-id', response.data.new_product_id);
                            }

                            // Reapply current category filters
                            var currentCategoryFilter = $('#category-header-filter').val();
                            if (currentCategoryFilter && currentCategoryFilter.length > 0) {
                                filterProductsByCategory(currentCategoryFilter);
                            }
                        } else {
                            showAlertMessage('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        showAlertMessage('An error occurred while updating the product');
                    }
                });
            }
        }

        // Instant update for text and number inputs
        $('#wpm-product-table').on('change', '.wpm-name, .wpm-price, .wpm-offer', function() {
            updateProduct($(this).closest('tr'));
        });

        // Instant update for status toggle
        $('#wpm-product-table').on('change', '.wpm-status', function() {
            updateProduct($(this).closest('tr'));
        });

        // Modified category select handler
        $('#wpm-product-table').on('change', '.wpm-category', function() {
            var row = $(this).closest('tr');
            var selectedCategories = $(this).val();
            
            // If no categories are selected, set to empty array
            if (!selectedCategories) {
                selectedCategories = [];
            }

            // Check if "All" category is being unselected
            if (selectedCategories.indexOf(allCategoryId.toString()) === -1) {
                showAlertMessage('The "All" category must always be selected. Your changes have been reverted.');
                // Reset to original categories
                var originalData = row.data('original');
                $(this).val(originalData.categories);
                return false;
            }

            updateProduct(row);
        });

        // Modified search functionality
        $('#wpm-search').on('input', function() {
            var searchTerm = $(this).val().toLowerCase();
            $('#wpm-product-table tbody tr').each(function() {
                var row = $(this);
                var name = row.find('.wpm-name').val().toLowerCase();
                if (name.includes(searchTerm)) {
                    // Only show if it also matches category filter
                    var currentCategoryFilter = $('#category-header-filter').val();
                    if (!currentCategoryFilter || currentCategoryFilter.length === 0) {
                        row.show();
                    } else {
                        var categoryIds = row.data('category-ids').toString().split(',');
                        if (categoryIds.some(id => currentCategoryFilter.includes(id))) {
                            row.show();
                        } else {
                            row.hide();
                        }
                    }
                } else {
                    row.hide();
                }
            });
        });

        // Delete product button handler
        $('#wpm-product-table').on('click', '.wpm-delete-btn', function(e) {
            e.preventDefault();
            var row = $(this).closest('tr');
            var productId = row.data('product-id');
            var productName = row.find('.wpm-name').val();
            
            // Don't show delete confirmation for new unsaved products
            if (productId === 0) {
                row.remove();
                return;
            }
            
            showDeleteConfirmation(productName, function() {
                $.ajax({
                    url: wpm_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wpm_delete_product',
                        product_id: productId
                    },
                    success: function(response) {
                        if (response.success) {
                            row.remove();
                            showSuccessMessage('Product deleted');
                        } else {
                            showAlertMessage('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        showAlertMessage('An error occurred while deleting the product');
                    }
                });
            });
        });

        // Modified Add Product button handler
$('#wpm-add-product').on('click', function() {
    var newRow = $('<tr data-product-id="0" data-category-ids="' + allCategoryId + '" data-original=\'{"name":"","price":"","offer":"","status":"outofstock","categories":["' + allCategoryId + '"]}\'>' +
        '<td class="wpm-col-dropdowns"><span class="dropdown-emoji">❎️</span></td>' + // Added dropdown emoji
        '<td class="wpm-col-name"><input type="text" class="wpm-name" value=""></td>' +
        '<td class="wpm-col-price"><input type="number" step="0.01" class="wpm-price" value=""></td>' +
        '<td class="wpm-col-offer"><input type="number" step="0.01" class="wpm-offer" value=""></td>' +
        '<td class="wpm-col-status">' +
            '<label class="wpm-status-toggle">' +
                '<input type="checkbox" class="wpm-status">' +
                '<span class="wpm-slider"></span>' +
            '</label>' +
        '</td>' +
        '<td class="wpm-col-category">' +
            '<select class="wpm-category" multiple>' +
                '<?php 
                $categories = get_terms('product_cat', array('hide_empty' => false));
                foreach ($categories as $category) {
                    $selected = ($category->name === 'All') ? ' selected' : '';
                    echo '<option value="' . $category->term_id . '"' . $selected . '>' . $category->name . '</option>';
                }
                ?>' +
            '</select>' +
        '</td>' +
        '<td class="wpm-col-delete"><button class="wpm-delete-btn" title="Delete product">❌️</button></td>' +
    '</tr>');

    $('#wpm-product-table tbody').prepend(newRow);
    newRow.find('.wpm-name').focus();

    // Apply current category filters to new row
    var currentCategoryFilter = $('#category-header-filter').val();
    if (currentCategoryFilter && currentCategoryFilter.length > 0) {
        filterProductsByCategory(currentCategoryFilter);
    }
});

        function showSuccessMessage(text = 'Updated') {
            var message = $('<div class="wpm-success-message">' + text + '</div>');
            $('body').append(message);
            setTimeout(function() {
                message.remove();
            }, 1000);
        }

        function showAlertMessage(text) {
            var overlay = $('<div class="wpm-alert-overlay"></div>');
            var message = $('<div class="wpm-alert-message">' + text + '</div>');
            
            $('body').append(overlay);
            $('body').append(message);
            
            overlay.fadeIn();
            message.fadeIn();

            function removeAlert() {
                overlay.fadeOut(function() {
                    $(this).remove();
                });
                message.fadeOut(function() {
                    $(this).remove();
                });
                $(document).off('click', removeAlert);
            }

            // Add the click event listener after a short delay
            setTimeout(function() {
                $(document).on('click', removeAlert);
            }, 100);
        }

        function showDeleteConfirmation(productName, onConfirm) {
            var overlay = $('<div class="wpm-alert-overlay"></div>');
            var message = $(
                '<div class="wpm-alert-message">' +
                'Do you want to delete "' + productName + '" permanently?' +
                '<div class="wpm-alert-buttons">' +
                '<button class="wpm-alert-button yes">Yes</button>' +
                '<button class="wpm-alert-button no">No</button>' +
                '</div>' +
                '</div>'
            );
            
            $('body').append(overlay);
            $('body').append(message);
            
            overlay.fadeIn();
            message.fadeIn();
            
            message.find('.wpm-alert-button.yes').on('click', function() {
                overlay.remove();
                message.remove();
                onConfirm();
            });
            
            message.find('.wpm-alert-button.no').on('click', function() {
                overlay.remove();
                message.remove();
            });
        }

        function arraysEqual(arr1, arr2) {
            if (!arr1 || !arr2) return false;
            if (arr1.length !== arr2.length) return false;
            for (var i = 0; i < arr1.length; i++) {
                if (arr1[i] !== arr2[i]) return false;
            }
            return true;
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'wpm_add_inline_script');
