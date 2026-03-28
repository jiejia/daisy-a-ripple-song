<?php

namespace App\Widgets;

/**
 * Widget View Renderer
 *
 * Render PHP widget templates from the theme resources directory.
 */
class WidgetView
{

    /**
     * Render a widget template and return the generated markup.
     *
     * @param string               $template The template file name without extension.
     * @param array<string, mixed> $data     Data extracted into the template scope.
     * @return string Rendered HTML output.
     */
    public static function render(string $template, array $data = []): string
    {
        /** @var string $templatePath Absolute template path. */
        $templatePath = get_template_directory() . '/resources/views/widgets/' . $template . '.php';

        if (!file_exists($templatePath)) {
            return '';
        }

        ob_start();
        extract($data, EXTR_SKIP);
        include $templatePath;

        return (string) ob_get_clean();
    }
}
