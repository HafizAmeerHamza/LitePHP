<?php

namespace App\Core;

use Exception;

class View
{
    protected static $sections = [];
    protected static $currentSection = null;
    protected static $extendedLayout = null;

    public static function render($view, $data = [])
    {
        extract($data);
        $viewFile = "../resources/views/{$view}.php";

        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View {$view} not found");
        }
    }

    public static function renderWithLayout($view, $data = [], $defaultLayout = 'layouts/app')
    {
        self::$sections = [];
        self::$currentSection = null;
        self::$extendedLayout = null;

        // Load view, which may call extend()
        self::getViewContent($view, $data);

        // Determine layout
        $layout = self::$extendedLayout ?? $defaultLayout;

        // Render layout with section data
        self::render($layout, $data);
    }

    private static function getViewContent($view, $data = [])
    {
        ob_start();
        extract($data);
        include "../resources/views/{$view}.php";
        ob_end_clean(); // We only care about sections
    }

    public static function section($name, $content = null)
    {
        if ($content === null) {
            // Start output buffering
            self::$currentSection = $name;
            ob_start();
        } else {
            self::$sections[$name] = $content;
        }
    }

    public static function endSection()
    {
        if (self::$currentSection) {
            self::$sections[self::$currentSection] = ob_get_clean();
            self::$currentSection = null;
        } else {
            throw new Exception("No section started.");
        }
    }

    public static function yield($name)
    {
        echo self::$sections[$name] ?? '';
    }

    public static function include($view, $data = [])
    {
        extract($data);
        $viewFile = "../resources/views/{$view}.php";

        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("Included view {$view} not found");
        }
    }

    public static function extend($layout)
    {
        self::$extendedLayout = $layout;
    }

    public static function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }
}
