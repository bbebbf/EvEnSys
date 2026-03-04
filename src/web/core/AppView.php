<?php
declare(strict_types=1);

class AppView implements ViewInterface
{
    public function render(string $template, array $data = [], int $statusCode = 200): void
    {
        View::render($template, $data, $statusCode);
    }

    public function renderStandalone(string $template, array $data = [], int $statusCode = 200): void
    {
        View::renderStandalone($template, $data, $statusCode);
    }
}
