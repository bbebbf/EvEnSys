<?php
declare(strict_types=1);

class AppLogo
{
    private ?string $logoUrl = null;
    private ?string $logoHeight = null;

    public function __construct(?string $logoHeight = null) {

        $this->logoHeight = $logoHeight;
        if (mb_strlen($this->logoHeight) === 0) {
            $this->logoHeight = null;
        }

        $_documentRoot = APP_ROOT . '/public';
        $_logoFilePattern = $_documentRoot . '/assets/app/app-logo.*';
        $_logoFiles = glob($_logoFilePattern);
        if (is_array($_logoFiles)) {
            foreach ($_logoFiles as $filename) {
                if (file_exists($filename)) {
                    $this->logoUrl = str_replace($_documentRoot, '', $filename);
                    break;
                }
            }
        }
    }

    public function getAppLogoExists(): bool
    {
        return $this->logoUrl !== null;
    }

    public function getAppLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function getAppLogoStyle(): string
    {
        return $this->logoHeight !== null ? ' style="height:' . $this->logoHeight . '"' : '';
    }
}
