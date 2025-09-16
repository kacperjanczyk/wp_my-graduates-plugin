<?php

namespace KJanczyk\MyGraduates\Admin;

class Columns
{
    public function init(): void
    {
        add_filter('manage_graduate_posts_columns', [$this, 'customColumns']);
        add_action('manage_graduate_posts_custom_column', [$this, 'customColumnContent'], 10, 2);
        add_filter('manage_edit-graduate_sortable_columns', [$this, 'sortableColumns']);
        add_action('pre_get_posts', [$this, 'handleCustomSorting']);
    }

    public function customColumns($columns): array
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

    public function customColumnContent($column, $postId): void
    {
        switch ($column) {
            case 'first_name':
                echo esc_html(get_post_meta($postId, '_graduate_first_name', true));
                break;
            case 'last_name':
                echo esc_html(get_post_meta($postId, '_graduate_last_name', true));
                break;
            case 'description':
                $description = get_post_meta($postId, '_graduate_description', true);
                $truncated = strlen($description) > 50 ? substr($description, 0, 50).'...' : $description;
                echo esc_html($truncated);
                break;
            case 'photo':
                $photo = get_post_meta($postId, '_graduate_photo', true);
                if ($photo) {
                    echo '<img src="'.esc_url($photo).'" alt="'.esc_attr(get_the_title($postId)).'" style="max-width:100px;height:auto;" />';
                } else {
                    echo __('No Photo', 'my-graduates');
                }
                break;
        }
    }

    public function sortableColumns($columns): array
    {
        $columns['first_name'] = 'first_name';
        $columns['last_name'] = 'last_name';
        $columns['date'] = 'date';

        return $columns;
    }

    public function handleCustomSorting($query): void
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
}
