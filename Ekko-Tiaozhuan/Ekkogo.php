<?php
/*
Plugin Name: Ekko定向跳转助手
Plugin URI: http://ekkoblog.com/
Description: “定向跳转助手”插件能够根据您的配置，自动将未登录的用户重定向到您指定的页面。无论是希望引导用户访问特定内容，还是需要将他们重定向到登录页面，本插件都能轻松实现。通过简洁的后台设置，您可以选择对所有页面生效的全局重定向，或是为特定页面设置独立的跳转规则。此外，插件还提供了灵活的配置选项，如通过简单的URL路径指定，使得管理和维护变得更加便捷。无论是提升用户体验，还是保护私有内容，定向跳转助手都是您理想的选择。
Version: 1.0
Author: Ekkoblog
Author URI: http://ekkoblog.com/
*/
// 添加设置链接到插件页面
function ekko_tiaozhuan_add_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=your-custom-redirect-settings">设置</a>';
    array_push($links, $settings_link);
    return $links;
}

add_filter("plugin_action_links_Ekko-Tiaozhuan/Ekkogo.php", 'ekko_tiaozhuan_add_settings_link');

function your_custom_redirect_non_logged_in_users() {
    $redirect_page = get_option('your_custom_redirect_page');
    $all_pages_redirect = get_option('your_custom_redirect_all_pages') === 'on' ? true : false;
    $specific_pages_redirect = get_option('your_custom_redirect_specific_page');
    $current_page_url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    if (!is_user_logged_in() && !empty($redirect_page)) {
        $redirect_page_url = site_url() . '/' . ltrim($redirect_page, '/');
        if ($current_page_url !== $redirect_page_url) {
            if ($all_pages_redirect) {
                wp_redirect($redirect_page_url);
                exit;
            } elseif (!empty($specific_pages_redirect)) {
                $specific_pages = explode("\n", $specific_pages_redirect);
                foreach ($specific_pages as $specific_page) {
                    $specific_page_url = site_url() . '/' . ltrim(trim($specific_page), '/');
                    if (strpos($current_page_url, $specific_page_url) !== false) {
                        wp_redirect($redirect_page_url);
                        exit;
                    }
                }
            }
        }
    }
}
add_action('init', 'your_custom_redirect_non_logged_in_users');

function your_custom_redirect_settings_menu() {
    add_options_page('Ekko定向跳转助手设置', 'Ekko定向跳转助手', 'manage_options', 'your-custom-redirect-settings', 'your_custom_redirect_settings_page');
}
add_action('admin_menu', 'your_custom_redirect_settings_menu');

function your_custom_redirect_settings_page() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        function toggleSpecificPageInput() {
            if ($('#your_custom_redirect_all_pages').is(':checked')) {
                $('#specific-page-row').hide();
            } else {
                $('#specific-page-row').show();
            }
        }
        
        toggleSpecificPageInput();
        $('#your_custom_redirect_all_pages').change(toggleSpecificPageInput);
    });
    </script>
    
    <div class="wrap">
        <h2>Ekko定向跳转助手设置</h2>
        <form method="post" action="options.php">
            <?php settings_fields('your-custom-redirect-settings-group'); ?>
            <?php do_settings_sections('your-custom-redirect-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">落地链接URL</th>
                    <td>
                        <input type="text" name="your_custom_redirect_page" value="<?php echo esc_attr(get_option('your_custom_redirect_page')); ?>" />
                        <p class="description">如果是跳转到本站链接，请输入站点URL斜杠后面的地址即可。</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">无论任何页面都跳转到设置的落地链接</th>
                    <td><input type="checkbox" id="your_custom_redirect_all_pages" name="your_custom_redirect_all_pages" <?php checked('on', get_option('your_custom_redirect_all_pages'), true); ?> /></td>
                </tr>
                <tr valign="top" id="specific-page-row">
                    <th scope="row">只有以下连接进行跳转到落地链接</th>
                    <td>
                        <textarea name="your_custom_redirect_specific_page" rows="5" cols="50"><?php echo esc_textarea(get_option('your_custom_redirect_specific_page')); ?></textarea>
                        <p class="description">请输入站点URL斜杠后面的地址即可，一行一个，可设置多个。</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}


function your_custom_redirect_register_settings() {
    register_setting('your-custom-redirect-settings-group', 'your_custom_redirect_page');
    register_setting('your-custom-redirect-settings-group', 'your_custom_redirect_all_pages');
    register_setting('your-custom-redirect-settings-group', 'your_custom_redirect_specific_page');
}
add_action('admin_init', 'your_custom_redirect_register_settings');
