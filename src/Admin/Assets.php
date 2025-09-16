<?php

namespace KJanczyk\MyGraduates\Admin;

class Assets
{
    public function init(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    public function enqueueAdminAssets($hook): void
    {
        global $post_type;

        if ($post_type !== 'graduate') {
            return;
        }

        if (in_array($hook, ['post-new.php', 'post.php'])) {
            $this->enqueueEditPageAssets();
        }

        if ($hook === 'edit.php') {
            $this->enqueueListPageAssets();
        }
    }

    private function enqueueEditPageAssets(): void
    {
        wp_enqueue_style(
            'my-graduates-admin',
            MY_GRADUATES_PLUGIN_URL . 'assets/css/admin.css',
            [],
            MY_GRADUATES_VERSION
        );

        wp_enqueue_media();
        wp_enqueue_script(
            'my-graduates-admin',
            MY_GRADUATES_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery', 'wp-media'],
            MY_GRADUATES_VERSION,
            true
        );

//        wp_localize_script('my-graduates-admin', 'myGraduatesL10n', [
//            'selectPhoto' => __('Select Photo', 'my-graduates'),
//            'useThisPhoto' => __('Use this photo', 'my-graduates'),
//            'removePhoto' => __('Remove photo', 'my-graduates'),
//        ]);
    }

    private function enqueueListPageAssets(): void
    {
        wp_enqueue_style(
            'my-graduates-admin-list',
            MY_GRADUATES_PLUGIN_URL . 'assets/css/admin.css',
            [],
            MY_GRADUATES_VERSION
        );
    }
}
