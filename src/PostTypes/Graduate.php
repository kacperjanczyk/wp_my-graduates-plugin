<?php

namespace KJanczyk\MyGraduates\PostTypes;

class Graduate
{
    public function register(): void
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
}
