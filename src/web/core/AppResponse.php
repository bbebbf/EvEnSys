<?php
declare(strict_types=1);

class AppResponse implements ResponseInterface
{
    public function redirect(string $path): never
    {
        ControllerTools::redirect($path);
    }

    public function abort403(): never
    {
        ControllerTools::abort_Forbidden_403();
    }

    public function abort404(): never
    {
        ControllerTools::abort_NotFound_404();
    }
}
