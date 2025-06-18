<?php
/*
Plugin Name: Code Block Enhancer
Description: Adds a copy button and customizable styling to code blocks.
Version: 1.2
Author: WPPremium
Author URI: https://profiles.wordpress.org/wppremium/
License: GPLv2
*/

// Enqueue scripts and dynamic styles
function wp_code_copy_enhancer_enqueue_assets() {
    wp_enqueue_script(
        'code-block-enhancer-script',
        plugin_dir_url(__FILE__) . 'assets/script.js',
        [],
        '1.2',
        true
    );

    $bg         = get_option('cc_bg_color', '#000000');
    $text       = get_option('cc_text_color', '#ffffff');
    $border     = get_option('cc_border_color', '#444444');
    $sel_bg     = get_option('cc_sel_bg_color', '#666666');
    $sel_text   = get_option('cc_sel_text_color', '#ffffff');
    $btn        = get_option('cc_btn_color', '#1a1a1a');
    $btn_text   = get_option('cc_btn_text_color', '#ffffff');
    $btn_hover  = get_option('cc_btn_hover_color', '#333333');
    $btn_hover_text = get_option('cc_btn_hover_text_color', '#ffffff');

    $dynamic_css = "
    .wp-block-code {
        position: relative;
        background-color: {$bg} !important;
        color: {$text} !important;
        border: 1px solid {$border};
        padding: 1.2em;
        border-radius: 8px;
        font-family: monospace;
        overflow-x: auto;
        margin-bottom: 1.5em;
    }
    .wp-block-code code,
    .wp-block-code pre,
    pre.wp-block-code,
    pre.wp-block-code code,
    pre code {
        color: {$text} !important;
        background-color: {$bg} !important;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .wp-block-code code::selection {
        background-color: {$sel_bg};
        color: {$sel_text};
    }
    .copy-code-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: {$btn};
        color: {$btn_text};
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        z-index: 10;
        transition: background-color 0.2s ease, color 0.2s ease;
    }
    .copy-code-btn:hover {
        background-color: {$btn_hover};
        color: {$btn_hover_text};
    }";

    wp_add_inline_style('wp-block-library', $dynamic_css);
}
add_action('wp_enqueue_scripts', 'wp_code_copy_enhancer_enqueue_assets');

// Add copy button
function wp_code_copy_enhancer_add_button($block_content, $block) {
    if ($block['blockName'] === 'core/code') {
        $button = '<button class="copy-code-btn" onclick="copyCodeBlock(this)">Copy</button>';
        $block_content = preg_replace('/(<pre.*?>)/', '$1' . $button, $block_content, 1);
    }
    return $block_content;
}
add_filter('render_block', 'wp_code_copy_enhancer_add_button', 10, 2);

// Add admin menu
function cc_admin_menu() {
    add_options_page('Code Block Customizer', 'Code Block Customizer', 'manage_options', 'cc-settings', 'cc_settings_page');
}
add_action('admin_menu', 'cc_admin_menu');

// Register settings
function cc_register_settings() {
    $fields = [
        'cc_bg_color', 'cc_text_color', 'cc_border_color',
        'cc_sel_bg_color', 'cc_sel_text_color',
        'cc_btn_color', 'cc_btn_text_color',
        'cc_btn_hover_color', 'cc_btn_hover_text_color'
    ];
    foreach ($fields as $field) {
        add_option($field, '');
        register_setting('cc_settings_group', $field, [
    'type' => 'string',
    'sanitize_callback' => 'sanitize_hex_color',
    'default' => ''
]);
    }
}
add_action('admin_init', 'cc_register_settings');

// Admin settings page
function cc_settings_page() {
    ?>
    <div class="wrap">
        <h1>Code Block Customizer</h1>
        <form method="post" action="options.php">
            <?php settings_fields('cc_settings_group'); ?>
            <table class="form-table">
                <?php
                $fields = [
                    'cc_bg_color' => 'Code Background Color',
                    'cc_text_color' => 'Code Text Color',
                    'cc_border_color' => 'Code Border Color',
                    'cc_sel_bg_color' => 'Text Selection Background',
                    'cc_sel_text_color' => 'Text Selection Color',
                    'cc_btn_color' => 'Button Background Color',
                    'cc_btn_text_color' => 'Button Text Color',
                    'cc_btn_hover_color' => 'Button Hover Background',
                    'cc_btn_hover_text_color' => 'Button Hover Text Color',
                ];
                foreach ($fields as $key => $label) {
					echo '<tr>';
					echo '<th scope="row"><label for="' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
					echo '<td><input type="color" name="' . esc_attr($key) . '" id="' . esc_attr($key) . '" value="' . esc_attr(get_option($key)) . '" /></td>';
					echo '</tr>';
                }
                ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function wpcc_render_settings_page() {
    $options = get_option('wpcc_settings', []);
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Code Block Customizer', 'code-block-enhancer'); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('wpcc_settings_group'); ?>
            <table class="form-table">
                <?php
                $fields = [
                    'code_bg_color' => 'Code Background Color',
                    'code_text_color' => 'Code Text Color',
                    'code_border_color' => 'Code Border Color',
                    'code_selection_bg' => 'Selection Background Color',
                    'code_selection_color' => 'Selection Text Color',
                    'button_bg_color' => 'Button Background Color',
                    'button_text_color' => 'Button Text Color',
                    'button_hover_color' => 'Button Hover Background Color',
                    'button_hover_text_color' => 'Button Hover Text Color'
                ];
                foreach ($fields as $key => $label) :
                    $value = isset($options[$key]) ? $options[$key] : '';
                    ?>
                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
                        <td>
                            <input type="color"
                                   id="<?php echo esc_attr($key); ?>"
                                   name="wpcc_settings[<?php echo esc_attr($key); ?>]"
                                   value="<?php echo esc_attr($value); ?>" />
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function wpcc_sanitize_settings($input) {
    $output = [];
    $color_fields = [
        'code_bg_color',
        'code_text_color',
        'code_border_color',
        'code_selection_bg',
        'code_selection_color',
        'button_bg_color',
        'button_text_color',
        'button_hover_color',
        'button_hover_text_color'
    ];
    foreach ($color_fields as $field) {
        if (isset($input[$field])) {
            $output[$field] = sanitize_hex_color($input[$field]);
        }
    }
    return $output;
}
