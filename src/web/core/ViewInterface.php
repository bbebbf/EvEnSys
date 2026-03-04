<?php
declare(strict_types=1);

interface ViewInterface
{
    public function render(string $template, array $data = [], int $statusCode = 200): void;
    public function renderStandalone(string $template, array $data = [], int $statusCode = 200): void;
}
