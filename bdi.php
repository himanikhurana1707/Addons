<?php
// Function to generate configuration for multiple dropdown fields with dependencies and dynamic options
function get_product_addon_config() {
    // Define the product configurations using a data-driven approach
    $products_config = [
        2911 => [
            'dropdowns' => [
                'size' => [
                    'label' => 'Size',
                    'multiple' => false,
                    'required' => true,
                    'options' => [
                        ['value' => 'small', 'price' => 100],
                        ['value' => 'medium', 'price' => 150],
                        ['value' => 'large', 'price' => 200],
                    ],
                ],
                'bread' => [
                    'label' => 'Bread',
                    'multiple' => false,
                    'required' => true,
                    'depends_on' => 'size', // Depends on the "Size" dropdown
                    'options' => generate_dynamic_options([
                        'whole_wheat' => [10, 20, 30],
                        'multigrain' => [15, 25, 35],
                        'multigrain_honey_oats' => [20, 30, 40],
                        'parmesan_oregano' => [25, 35, 45],
                        'roasted_garlic' => [30, 40, 50],
                    ], ['small', 'medium', 'large']),
                ],
                'fillings' => [
                    'label' => 'Fillings',
                    'multiple' => true,
                    'required' => true,
                    'depends_on' => 'size', // Depends on the "Size" dropdown
                    'options' => generate_dynamic_options([
                        'salt_and_pepper' => [5, 10, 15],
                        'onions' => [10, 20, 30],
                        'cucumber' => [10, 20, 30],
                        'tomato' => [10, 20, 30],
                        'lettuce' => [15, 25, 35],
                        'corns' => [15, 25, 35],
                        'capsicum' => [15, 25, 35],
                        'red_paprika' => [20, 30, 40],
                        'pickles' => [20, 30, 40],
                        'jalapenos' => [20, 30, 40],
                        'olives' => [25, 35, 45],
                        'paneer' => [30, 40, 50],
                    ], ['small', 'medium', 'large']),
                ],
                // Add more dropdowns as needed...
            ],
        ],
1041 => [
    'dropdowns' => [
        'Servings' => [
            'label' => 'Servings',
            'multiple' => false,
            'required' => true,
            'options' => [
                ['value' => 'Half', 'price' => 70],
                ['value' => 'Full', 'price' => 100],
            ],
        ],
    ],
],
893 => [
    'dropdowns' => [
        'Cheese' => [
            'label' => 'Cheese',
            'multiple' => false,
            'required' => true,
            'options' => [
                ['value' => 'Without Cheese', 'price' => 0],
                ['value' => 'With Cheese', 'price' => 20],
            ],
        ],
        'Fries' => [
            'label' => 'Fries',
            'multiple' => false,
            'required' => true,
            'options' => [
                ['value' => 'Plain Fries', 'price' => 0],
                ['value' => 'Peri Peri Fries', 'price' => 10],
            ],
        ],
    ],
]
4091 => [
    'dropdowns' => [
        'Add-ons' => [
            'label' => 'Add-ons',
            'multiple' => true,
            'required' => false,
            'options' => [
                ['value' => 'Cheese', 'price' => 20],
                ['value' => 'Peri Peri Masala', 'price' => 10],
            ],
        ],
    ],
],
    ];
    return $products_config;
}


// Helper function to dynamically generate dropdown options based on dependent values
function generate_dynamic_options($options, $dependencies) {
    $result = [];

    // Loop through each dependency (e.g., 'small', 'medium', 'large')
    foreach ($dependencies as $index => $dependency) {
        // Generate options for each dependency
        $result[$dependency] = array_map(
            fn($value, $prices) => ['value' => $value, 'price' => $prices[$index]],
            array_keys($options),
            $options
        );
    }

    return $result;
}




// Part 1: Enqueue scripts and styles
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts_and_styles');
function enqueue_custom_scripts_and_styles() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('update-price-script', get_template_directory_uri() . '/js/update-price.js', ['jquery', 'select2'], null, true);
    wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], null, true);
    wp_localize_script('update-price-script', 'product_addon_config', get_product_addon_config());
    
    wp_localize_script('jquery', 'wc_add_to_cart_params', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}

// Part 2: Add inline script
add_action('wp_enqueue_scripts', 'add_custom_inline_script');
function add_custom_inline_script() {
    wp_add_inline_script('jquery', '
        jQuery(document).ready(function($) {
            $(".single_add_to_cart_button").on("click", function(e) {
                e.preventDefault();

                var $thisbutton = $(this),
                    $form = $thisbutton.closest("form.cart"),
                    id = $thisbutton.val(),
                    product_qty = $form.find("input[name=quantity]").val() || 1,
                    product_id = $form.find("input[name=product_id]").val() || id,
                    variation_id = $form.find("input[name=variation_id]").val() || 0;

                var data = {
                    action: "woocommerce_ajax_add_to_cart",
                    product_id: product_id,
                    product_sku: "",
                    quantity: product_qty,
                    variation_id: variation_id,
                };

                // Add custom dropdown fields to the data
                $form.find("select").each(function() {
                    var $select = $(this);
                    data[$select.attr("name")] = $select.val();
                });

                // Validate required fields
                var isValid = true;
                $form.find("select[required]").each(function() {
                    var $select = $(this);
                    var $container = $select.closest(".dropdown-container");
                    var $errorMessage = $container.find(".error-message");
                    var $parentSelect = $form.find("select[name=\"" + $select.data("depends-on") + "\"]");

                    if ($parentSelect.length === 0 || $parentSelect.val()) {
                        if (!$select.val() || $select.val().length === 0) {
                            isValid = false;
                            $errorMessage.text("Please select the required option(s).").show();
                        } else {
                            $errorMessage.hide();
                        }
                    }
                });

                if (!isValid) {
                    return;
                }

                $.post(wc_add_to_cart_params.ajax_url, data, function(response) {
                    if (!response) return;

                    if (response.error && response.product_url) {
                        window.location = response.product_url;
                        return;
                    } else {
                        $thisbutton.removeClass("loading");
                        $(document.body).trigger("added_to_cart", [response.fragments, response.cart_hash, $thisbutton]);
                        showTickmark();
                    }
                });

                return false;
            });

            function showTickmark() {
                var tickmark = $("<div>", {
                    "id": "custom-tickmark",
                    "html": "&#10004;"
                }).appendTo("body");

                tickmark.css({
                    "position": "fixed",
                    "top": "50%",
                    "left": "50%",
                    "transform": "translate(-50%, -50%)",
                    "font-size": "50px",
                    "color": "#d8a744",
                    "background-color": "#000000",
                    "border": "4px solid #d8a744",
                    "padding": "10px 20px",
                    "border-radius": "0 100px 0 100px",
                    "box-shadow": "0 0 15px #d8a744",
                    "z-index": "9999",
                    "display": "none"
                });

                tickmark.fadeIn(500).delay(1000).fadeOut(500, function() {
                    $(this).remove();
                });
            }
        });
    ');
}



// Handle AJAX request
function woocommerce_ajax_add_to_cart() {
    $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
    $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
    $variation_id = absint($_POST['variation_id']);
    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
    $product_status = get_post_status($product_id);

    if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id) && 'publish' === $product_status) {
        do_action('woocommerce_ajax_added_to_cart', $product_id);

        if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
            wc_add_to_cart_message([$product_id => $quantity], true);
        }

        WC_AJAX::get_refreshed_fragments();
    } else {
        $data = array(
            'error' => true,
            'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id));

        wp_send_json($data);
    }

    wp_die();
}
add_action('wp_ajax_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart');
add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart');

// Update cart fragments
function woocommerce_header_add_to_cart_fragment($fragments) {
    ob_start();
    ?>
    <a class="cart-contents" href="<?php echo wc_get_cart_url(); ?>" title="<?php _e('View your shopping cart'); ?>">
        <?php echo sprintf(_n('%d item', '%d items', WC()->cart->get_cart_contents_count(), 'woocommerce'), WC()->cart->get_cart_contents_count()); ?> - <?php echo WC()->cart->get_cart_total(); ?>
    </a>
    <?php
    $fragments['a.cart-contents'] = ob_get_clean();
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'woocommerce_header_add_to_cart_fragment');

// Add dropdown fields to the product page
add_action('woocommerce_before_add_to_cart_button', 'add_custom_dropdown_fields');
add_action('woocommerce_after_shop_loop_item', 'add_custom_dropdown_fields');
function add_custom_dropdown_fields() {
    global $product;
    $config = get_product_addon_config();
    $product_id = $product->get_id();

    if (isset($config[$product_id])) {
        $product_config = json_encode($config[$product_id]);
        echo "<div class='product-addons-dropdown-wrapper' data-product-id='{$product_id}' data-product-config='{$product_config}'>";

        foreach ($config[$product_id]['dropdowns'] as $name => $dropdown) {
            $class = $dropdown['multiple'] ? 'multi-select-dropdown' : 'single-select-dropdown';
            $multiple = $dropdown['multiple'] ? ' multiple' : '';
            $required = $dropdown['required'] ? ' required' : '';
            $depends_on = isset($dropdown['depends_on']) ? " data-depends-on='{$dropdown['depends_on']}'" : '';

            echo "<div class='dropdown-container {$class} {$name}_field'>";
            echo "<label class='dropdown-label' for='{$name}_{$product_id}'>" . esc_html($dropdown['label']) . ($dropdown['required'] ? ' <span class="required">*</span>' : '') . "</label>";
            echo "<div class='error-message' style='display:none;'></div>"; // Add error message container
            echo "<select id='{$name}_{$product_id}' class='select-input' name='{$name}" . ($dropdown['multiple'] ? '[]' : '') . "'{$multiple}{$required}{$depends_on}>";

            if (!isset($dropdown['depends_on'])) {
                foreach ($dropdown['options'] as $option) {
                    $label = isset($option['label']) ? $option['label'] : ucwords(str_replace('_', ' ', $option['value']));
                    echo "<option value='" . esc_attr($option['value']) . "' data-price='" . esc_attr($option['price']) . "'>" . esc_html($label) . " - ₹" . esc_html($option['price']) . "</option>";
                }
            } else {
                echo "<option value=''>Select " . esc_html($config[$product_id]['dropdowns'][$dropdown['depends_on']]['label']) . " first</option>";
            }

            echo "</select></div>";
        }

        echo "<div class='updated-price' style='font-size: 1.5em; margin-top: 10px;'>₹" . $product->get_price() . "</div>";
        echo "</div>";
    }
}

// Add dropdown data to the cart item
add_filter('woocommerce_add_cart_item_data', 'add_custom_cart_item_data', 10, 3);
function add_custom_cart_item_data($cart_item_data, $product_id, $variation_id) {
    $config = get_product_addon_config();
    if (isset($config[$product_id])) {
        foreach ($config[$product_id]['dropdowns'] as $name => $dropdown) {
            // Check if the field is present in POST data
            if (isset($_POST[$name])) {
                $cart_item_data[$name] = $_POST[$name];
            } else {
                // If the field is not in POST data, add it with an empty value
                $cart_item_data[$name] = '';
            }
        }
        $cart_item_data['unique_key'] = md5(microtime() . rand());
    }
    return $cart_item_data;
}

// Validate dropdown fields
add_filter('woocommerce_add_to_cart_validation', 'validate_custom_fields', 10, 3);
function validate_custom_fields($passed, $product_id, $quantity) {
    $config = get_product_addon_config();
    if (isset($config[$product_id])) {
        foreach ($config[$product_id]['dropdowns'] as $name => $dropdown) {
            if ($dropdown['required'] && empty($_POST[$name])) {
                // wc_add_notice(sprintf(__('Select the required %s.', 'woocommerce'), format_label($name)), 'error');
                return false;
            }
        }
    }
    return $passed;
}

// Add script to update price dynamically and handle conditional dropdowns
add_action('wp_footer', 'add_price_update_script');
function add_price_update_script() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        function initProductAddons($context) {
            $context.find('.product-addons-dropdown-wrapper').each(function() {
                var $wrapper = $(this);

                // Check if the dropdown has been initialized already
                if ($wrapper.data('initialized')) return;
                $wrapper.data('initialized', true);

                var productId = $wrapper.data('product-id');
                var config = $wrapper.data('product-config');
                var basePrice = parseFloat($wrapper.find('.updated-price').text().replace('₹', ''));

                // Initialize Select2 Dropdowns
                function initializeDropdowns() {
                    $wrapper.find('.multi-select-dropdown select, .single-select-dropdown select').each(function() {
                        $(this).select2({
                            dropdownParent: $(this).closest('.dropdown-container'),
                            width: '100%',
                            closeOnSelect: false,
                            minimumResultsForSearch: Infinity,
                            templateSelection: function(data) {
                                return $(this).hasClass('multi-select-dropdown') ?
                                    $('<span class="multi-select-selection">' + data.text + '</span><br>') :
                                    data.text;
                            }
                        }).val($(this).data('selected') || null).trigger('change');
                    });
                }

                // Update the price dynamically based on selections
                function updatePrice() {
                    var extraCost = $wrapper.find('select').get().reduce(function(sum, select) {
                        return sum + Array.from(select.selectedOptions).reduce(function(optionSum, option) {
                            return optionSum + (parseFloat(option.dataset.price) || 0);
                        }, 0);
                    }, 0);
                    $wrapper.find('.updated-price').text('₹' + (basePrice + extraCost).toFixed(2));
                }

                // Handle dependent dropdowns and update based on parent selection
                function updateDependentDropdowns(parentName, parentValue) {
                    $wrapper.find('select[data-depends-on="' + parentName + '"]').each(function() {
                        var $select = $(this);
                        var dropdownName = $select.attr('name').replace('[]', '');
                        var dropdownConfig = config.dropdowns[dropdownName];

                        // Store the current scroll position
                        var scrollPosition = $select.closest('.dropdown-container').scrollTop();

                        $select.empty().prop('disabled', !parentValue);

                        if (parentValue && dropdownConfig.options[parentValue]) {
                            dropdownConfig.options[parentValue].forEach(function(option) {
                                var label = option.label || option.value.replace(/_/g, ' ').replace(/\w\S*/g, function(txt) {
                                    return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
                                });
                                $select.append($('<option>', {
                                    value: option.value,
                                    text: label + ' - ₹' + option.price,
                                    'data-price': option.price
                                }));
                            });
                        }

                        $select.val(null).trigger('change');

                        // Restore the scroll position
                        $select.closest('.dropdown-container').scrollTop(scrollPosition);

                        // Show error message for dependent dropdown if parent has a value
                        var $errorMessage = $select.closest('.dropdown-container').find('.error-message');
                        if (parentValue) {
                            if ($select.prop('required') && (!$select.val() || $select.val().length === 0)) {
                                $errorMessage.text('Please select the required option(s).').show();
                            } else {
                                $errorMessage.hide();
                            }
                        } else {
                            $errorMessage.hide();
                        }
                    });
                }

                // Setup event listeners for dropdowns
                function setupEventListeners() {
                    $wrapper.find('select').on('change', function() {
                        var $select = $(this);
                        var dropdownName = $select.attr('name').replace('[]', '');
                        var selectedValue = $select.val();

                        // Remove error message when an option is selected
                        $select.closest('.dropdown-container').find('.error-message').hide();

                        if (config.dropdowns[dropdownName] && !config.dropdowns[dropdownName].depends_on) {
                            updateDependentDropdowns(dropdownName, selectedValue);
                        }

                        updatePrice();

                        // Adjust Select2 dropdown position
                        var select2Instance = $select.data('select2');
                        if (select2Instance && select2Instance.dropdown) {
                            select2Instance.dropdown._positionDropdown();
                        }
                    });
                }

                initializeDropdowns();
                setupEventListeners();
            });
        }

        // Initialize product addons within the current context (document or new content)
        initProductAddons($(document));

        // Reinitialize product addons after AJAX calls
        $(document).on('ajaxComplete', function(event, xhr, settings) {
            initProductAddons($(document));
        });

        // Add to cart validation for required fields
        $(document).on('click', '.single_add_to_cart_button', function(e) {
            var $form = $(this).closest('form');
            var $wrapper = $form.find('.product-addons-dropdown-wrapper');
            var isValid = true;

            $wrapper.find('select[required]').each(function() {
                var $select = $(this);
                var $container = $select.closest('.dropdown-container');
                var $errorMessage = $container.find('.error-message');
                var $parentSelect = $wrapper.find('select[name="' + $select.data('depends-on') + '"]');

                if ($parentSelect.length === 0 || $parentSelect.val()) {
                    if (!$select.val() || $select.val().length === 0) {
                        e.preventDefault();
                        isValid = false;
                        $errorMessage.text('Please select the required option(s).').show();
                    } else {
                        $errorMessage.hide();
                    }
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    </script>
    <?php
}

// Update cart item price based on dropdown selections
add_action('woocommerce_before_calculate_totals', 'update_custom_price', 10, 1);
function update_custom_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart->get_cart() as $cart_item) {
        $product_id = $cart_item['product_id'];
        $config = get_product_addon_config();

        if (isset($config[$product_id])) {
            $extra_cost = 0;

            foreach ($config[$product_id]['dropdowns'] as $name => $dropdown) {
                if (isset($cart_item[$name])) {
                    $selected_values = is_array($cart_item[$name]) ? $cart_item[$name] : [$cart_item[$name]];

                    foreach ($selected_values as $selected_value) {
                        if (isset($dropdown['depends_on'])) {
                            $parent_value = $cart_item[$dropdown['depends_on']];
                            $options = $dropdown['options'][$parent_value];
                        } else {
                            $options = $dropdown['options'];
                        }

                        foreach ($options as $option) {
                            if ($option['value'] == $selected_value) {
                                $extra_cost += $option['price'];
                                break;
                            }
                        }
                    }
                }
            }

            $cart_item['data']->set_price($cart_item['data']->get_price('edit') + $extra_cost);
        }
    }
}

// Display dropdown data in the cart
add_filter('woocommerce_get_item_data', 'display_custom_item_data', 10, 2);
function display_custom_item_data($item_data, $cart_item) {
    $config = get_product_addon_config();
    $product_id = $cart_item['product_id'];
    if (isset($config[$product_id])) {
        foreach ($config[$product_id]['dropdowns'] as $name => $dropdown) {
            if (isset($cart_item[$name]) && !empty($cart_item[$name])) {
                $label = format_label($name);
                $value = is_array($cart_item[$name]) ? implode(', ', array_map('format_label', $cart_item[$name])) : format_label($cart_item[$name]);
                if (!empty($value)) {
                    $item_data[] = array(
                        'key' => $label,
                        'value' => wc_clean($value),
                    );
                }
            }
        }
    }
    return $item_data;
}

// Add dropdown data to order items
add_action('woocommerce_checkout_create_order_line_item', 'add_custom_order_item_meta', 10, 4);
function add_custom_order_item_meta($item, $cart_item_key, $values, $order) {
    $config = get_product_addon_config();
    $product_id = $values['product_id'];
    if (isset($config[$product_id])) {
        foreach ($config[$product_id]['dropdowns'] as $name => $dropdown) {
            if (isset($values[$name]) && !empty($values[$name])) {
                $label = format_label($name);
                $value = is_array($values[$name]) ? implode(', ', array_map('format_label', $values[$name])) : format_label($values[$name]);
                if (!empty($value)) {
                    $item->add_meta_data($label, $value, true);
                }
            }
        }
    }
}

// Display dropdown data in order details and emails
add_filter('woocommerce_order_item_product', 'display_custom_order_item_meta', 10, 2);
function display_custom_order_item_meta($cart_item, $order_item) {
    $config = get_product_addon_config();
    $product_id = $order_item['product_id'];
    if (isset($config[$product_id])) {
        foreach ($config[$product_id]['dropdowns'] as $name => $dropdown) {
            if (isset($order_item[$name])) {
                $label = format_label($name); // Use the formatting function for the label
                $cart_item->add_meta_data($label, implode(', ', array_map('format_label', (array) $order_item[$name])), true);
            }
        }
    }
    return $cart_item;
}

// Merge products with the same options in the cart
add_filter('woocommerce_add_to_cart', 'merge_custom_cart_items', 10, 6);
function merge_custom_cart_items($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    $config = get_product_addon_config();
    if (isset($config[$product_id])) {
        $cart = WC()->cart->get_cart();
        foreach ($cart as $key => $item) {
            $is_same = $item['product_id'] == $product_id;
            foreach ($config[$product_id]['dropdowns'] as $name => $dropdown) {
                if ($is_same && isset($cart_item_data[$name]) && isset($item[$name]) && $cart_item_data[$name] != $item[$name]) {
                    $is_same = false;
                    break;
                }
            }
            if ($is_same && $key != $cart_item_key) {
                WC()->cart->remove_cart_item($cart_item_key);
                WC()->cart->set_quantity($key, $item['quantity'] + $quantity);
                break;
            }
        }
    }
    return $cart_item_key;
}

// Helper function to format labels using a regular expression
function format_label($value) {
    return preg_replace_callback('/_(.)/', function($matches) {
        return ' ' . strtoupper($matches[1]);
    }, ucfirst($value));
}
