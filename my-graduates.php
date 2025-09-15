<?php

/**
 * Plugin Name: My Graduates
 * Description: A plugin to manage and display graduates.
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://janczyk.dev
 * Text Domain: my-graduates
 * Domain Path: /languages
 */

namespace KJanczyk\MyGraduates;

if (! defined('ABSPATH')) {
    exit;
}

define('MY_GRADUATES_VERSION', '1.0.0');
define('MY_GRADUATES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MY_GRADUATES_PLUGIN_URL', plugin_dir_url(__FILE__));

class MyGraduates
{
    private static ?MyGraduates $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->init_hooks();
    }

    private function init_hooks(): void
    {
        add_action('init', [$this, 'register_my_graduate_cpt']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_fields']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        add_filter('manage_graduate_posts_columns', [$this, 'custom_columns']);
        add_action('manage_graduate_posts_custom_column', [$this, 'custom_column_content'], 10, 2);
        add_filter('manage_edit-graduate_sortable_columns', [$this, 'sortable_columns']);

        add_action('pre_get_posts', [$this, 'handle_custom_sorting']);
        add_filter('wp_insert_post_data', [$this, 'update_post_title'], 10, 2);
    }

    public function register_my_graduate_cpt(): void
    {
        $labels = [
                'name' => __('Graduates', 'my-graduates'),
                'singular_name' => __('Graduate', 'my-graduates'),
                'add_new' => __('Add New', 'my-graduates'),
                'add_new_item' => __('Add New Graduate', 'my-graduates'),
                'edit_item' => __('Edit Graduate', 'my-graduates'),
                'all_items' => __('All Graduates', 'my-graduates'),
        ];

        $args = [
                'labels' => $labels,
                'public' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'menu_icon' => 'dashicons-admin-users',
                'supports' => ['title'],
                'show_in_rest' => true,
        ];

        register_post_type('graduate', $args);
    }

    public function add_meta_boxes(): void
    {
        add_meta_box(
            'graduate_details',
            __('Graduate Details', 'my-graduates'),
            [$this, 'render_meta_box'],
            'graduate',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post): void
    {
        wp_enqueue_media();
        wp_nonce_field('save_graduate_meta', 'graduate_meta_nonce');

        $first_name = get_post_meta($post->ID, '_graduate_first_name', true);
        $last_name = get_post_meta($post->ID, '_graduate_last_name', true);
        $description = get_post_meta($post->ID, '_graduate_description', true);
        $photo = get_post_meta($post->ID, '_graduate_photo', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="graduate_first_name"><?php _e('First Name', 'my-graduates'); ?> <span class="required">*</span></label></th>
                <td>
                    <input type="text" id="graduate_first_name" name="graduate_first_name"
                           value="<?php echo esc_attr($first_name); ?>" class="regular-text" required />
                </td>
            </tr>
            <tr>
                <th><label for="graduate_last_name"><?php _e('Last Name', 'my-graduates'); ?> <span class="required">*</span></label></th>
                <td>
                    <input type="text" id="graduate_last_name" name="graduate_last_name"
                           value="<?php echo esc_attr($last_name); ?>" class="regular-text" required />
                </td>
            </tr>
            <tr>
                <th><label for="graduate_description"><?php _e('Description', 'my-graduates'); ?></label></th>
                <td>
                    <textarea id="graduate_description" name="graduate_description" rows="5"
                              class="large-text"><?php echo esc_textarea($description); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label for="graduate_photo"><?php _e('Photo', 'my-graduates'); ?></label></th>
                <td>
                    <input type="hidden" id="graduate_photo" name="graduate_photo"
                           value="<?php echo esc_url($photo); ?>" />
                    <button type="button" class="button js-select-media">
                        <?php _e('Select Media', 'my-graduates'); ?>
                    </button>
                    <div id="photo-preview" class="media-preview">
                        <?php if ($photo): ?>
                            <img src="<?php echo esc_url($photo); ?>"
                                 alt="<?php _e('Graduate Photo', 'my-graduates'); ?>" />
                            <br><button type="button" class="button-link js-remove-media">
                                <?php _e('Remove Media', 'my-graduates'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>

        <style>
            .required { color: #dc3232; }
            .media-preview img {
                max-width: 200px;
                height: auto;
                border: 1px solid #ddd;
                padding: 5px;
                margin-top: 10px;
            }
        </style>
        <?php
    }

    public function save_meta_fields($post_id): void
    {
        if (!isset($_POST['graduate_meta_nonce']) ||
                !wp_verify_nonce($_POST['graduate_meta_nonce'], 'save_graduate_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        if (get_post_type($post_id) !== 'graduate') {
            return;
        }

        $errors = [];
        if (empty($_POST['graduate_first_name'])) {
            $errors[] = __('First name is required', 'my-graduates');
        }
        if (empty($_POST['graduate_last_name'])) {
            $errors[] = __('Last name is required', 'my-graduates');
        }

        if (!empty($errors)) {
            add_settings_error('graduate_meta', 'required_fields', implode('<br>', $errors));
            return;
        }

        $fields = ['graduate_first_name', 'graduate_last_name', 'graduate_description', 'graduate_photo'];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
                $value = match ($field) {
                    'graduate_description' => sanitize_textarea_field($value),
                    'graduate_photo' => esc_url_raw($value),
                    default => sanitize_text_field($value),
                };
                update_post_meta($post_id, '_'.$field, $value);
            }
        }
    }

    public function enqueue_admin_scripts($hook): void
    {
        global $post_type;

        if ($post_type !== 'graduate') {
            return;
        }

        if ($hook === 'post-new.php' || $hook === 'post.php') {
            wp_enqueue_script(
                'graduate-admin-js',
                MY_GRADUATES_PLUGIN_URL . 'assets/js/admin.js',
                [],
                '1.0',
                true
            );

            wp_localize_script('graduate-admin-js', 'myGraduatesL10n', [
                'selectPhoto' => __('Select Photo', 'my-graduates'),
                'useThisPhoto' => __('Use this photo', 'my-graduates'),
                'removePhoto' => __('Remove photo', 'my-graduates'),
            ]);
        }
    }


    public function custom_columns($columns): array
    {
        return [
                'cb' => $columns['cb'],
                'first_name' => __('First Name', 'my-graduates'),
                'last_name' => __('Last Name', 'my-graduates'),
                'description' => __('Description', 'my-graduates'),
                'photo' => __('Photo', 'my-graduates'),
                'date' => $columns['date'],
        ];
    }

    public function custom_column_content($column, $post_id): void
    {
        switch ($column) {
            case 'first_name':
                echo esc_html(get_post_meta($post_id, '_graduate_first_name', true));
                break;
            case 'last_name':
                echo esc_html(get_post_meta($post_id, '_graduate_last_name', true));
                break;
            case 'description':
                $description = get_post_meta($post_id, '_graduate_description', true);
                $truncated = strlen($description) > 50 ? substr($description, 0, 50).'...' : $description;
                echo esc_html($truncated);
                break;
            case 'photo':
                $photo = get_post_meta($post_id, '_graduate_photo', true);
                if ($photo) {
                    echo '<img src="'.esc_url($photo).'" alt="'.esc_attr(get_the_title($post_id)).'" style="max-width:100px;height:auto;" />';
                } else {
                    echo __('No Photo', 'my-graduates');
                }
                break;
        }
    }

    public function sortable_columns($columns): array
    {
        $columns['first_name'] = 'first_name';
        $columns['last_name'] = 'last_name';
        $columns['date'] = 'date';

        return $columns;
    }

    public function handle_custom_sorting($query): void
    {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        if ($query->get('post_type') !== 'graduate') {
            return;
        }

        $orderBy = $query->get('orderby');

        if ($orderBy === 'first_name') {
            $query->set('meta_key', '_graduate_first_name');
            $query->set('orderby', 'meta_value');
        } elseif ($orderBy === 'last_name') {
            $query->set('meta_key', '_graduate_last_name');
            $query->set('orderby', 'meta_value');
        }
    }

    public function update_post_title($data): array
    {
        if ($data['post_type'] !== 'graduate') {
            return $data;
        }

        if (!empty($_POST['graduate_first_name']) && !empty($_POST['graduate_last_name'])) {
            $first_name = sanitize_text_field($_POST['graduate_first_name']);
            $last_name = sanitize_text_field($_POST['graduate_last_name']);
            $title = $first_name . ' ' . $last_name;

            $data['post_title'] = $title;
            $data['post_name'] = sanitize_title($title);
        }

        return $data;
    }
}

add_action('plugins_loaded', function () {
    MyGraduates::getInstance();
});
