<?php
declare(strict_types=1);

interface ResponseInterface
{
    public function redirect(string $path): never;
    public function abort403(): never;
    public function abort404(): never;
}
