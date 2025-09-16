<?php

namespace KJanczyk\MyGraduates\Admin;

class MetaBoxes
{
    public function init(): void
    {
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post', [$this, 'saveMetaFields']);
        add_filter('wp_insert_post_data', [$this, 'updatePostTitle'], 10, 2);
    }

    public function addMetaBoxes(): void
    {
        add_meta_box(
            'graduate_details',
            __('Graduate Details', 'my-graduates'),
            [$this, 'renderMetaBox'],
            'graduate',
            'normal',
            'high'
        );
    }

    public function renderMetaBox($post): void
    {
        wp_enqueue_media();
        wp_nonce_field('save_graduate_meta', 'graduate_meta_nonce');

        $firstName = get_post_meta($post->ID, '_graduate_first_name', true);
        $lastName = get_post_meta($post->ID, '_graduate_last_name', true);
        $description = get_post_meta($post->ID, '_graduate_description', true);
        $photo = get_post_meta($post->ID, '_graduate_photo', true);

        include MY_GRADUATES_PLUGIN_DIR . 'templates/admin/meta-box.php';
    }

    public function saveMetaFields($postId): void
    {
        if (!$this->shouldSave($postId)) {
            return;
        }

        $this->validateAndSave($postId);
    }

    private function shouldSave($postId): bool
    {
        if (
            !isset($_POST['graduate_meta_nonce']) ||
            !wp_verify_nonce($_POST['graduate_meta_nonce'], 'save_graduate_meta')
        ) {
            return false;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        if (!current_user_can('edit_post', $postId)) {
            return false;
        }

        if (get_post_type($postId) !== 'graduate') {
            return false;
        }

        return true;
    }

    private function validateAndSave($postId): void
    {
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
                update_post_meta($postId, '_'.$field, $value);
            }
        }
    }

    public function updatePostTitle($data): array
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
