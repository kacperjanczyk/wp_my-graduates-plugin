<?php

namespace KJanczyk\MyGraduates;

use KJanczyk\MyGraduates\Admin\Assets;
use KJanczyk\MyGraduates\Admin\Columns;
use KJanczyk\MyGraduates\Admin\MetaBoxes;
use KJanczyk\MyGraduates\API\RestController;
use KJanczyk\MyGraduates\PostTypes\Graduate;

class Plugin
{
    private static ?Plugin $instance = null;
    private Graduate $graduatePostType;
    private MetaBoxes $metaBoxes;
    private Columns $columns;
    private Assets $assets;
    private RestController $restController;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->initComponents();
        $this->initHooks();
    }

    private function initComponents(): void
    {
        $this->graduatePostType = new Graduate();
        $this->metaBoxes = new MetaBoxes();
        $this->columns = new Columns();
        $this->assets = new Assets();
        $this->restController = new RestController();
    }

    private function initHooks(): void
    {
        add_action('init', [$this->graduatePostType, 'register']);
        add_action('rest_api_init', [$this->restController, 'register_routes']);

        if (is_admin()) {
            $this->metaBoxes->init();
            $this->columns->init();
            $this->assets->init();
        }
    }
}
