<?php

namespace KJanczyk\MyGraduates;

use KJanczyk\MyGraduates\Admin\Assets;
use KJanczyk\MyGraduates\Admin\Columns;
use KJanczyk\MyGraduates\Admin\MetaBoxes;
use KJanczyk\MyGraduates\PostTypes\Graduate;

class Plugin
{
    private static ?Plugin $instance = null;
    private Graduate $graduatePostType;
    private MetaBoxes $metaBoxes;
    private Columns $columns;
    private Assets $assets;

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
    }

    private function initHooks(): void
    {
        add_action('init', [$this->graduatePostType, 'register']);

        if (is_admin()) {
            $this->metaBoxes->init();
            $this->columns->init();
            $this->assets->init();
        }
    }
}
