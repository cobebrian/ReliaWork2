<?php
/**
 * ReliaWork2 View Helper
 * Renders a view inside a layout using output buffering.
 */

function view(string $viewFile, array $data = [], string $layout = 'main'): void
{
    // Extract data into local scope
    extract($data, EXTR_SKIP);

    // Capture view content
    ob_start();
    $fullPath = VIEW_PATH . '/' . ltrim($viewFile, '/') . '.php';
    if (!file_exists($fullPath)) {
        echo "<p class='text-danger'>View not found: {$viewFile}</p>";
    } else {
        include $fullPath;
    }
    $content = ob_get_clean();

    // Render layout
    $layoutPath = VIEW_PATH . '/layouts/' . $layout . '.php';
    if (file_exists($layoutPath)) {
        include $layoutPath;
    } else {
        echo $content;
    }
}
