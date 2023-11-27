<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\Passwords\Services;

use OCA\Passwords\Helper\Favicon\AbstractFaviconHelper;
use OCA\Passwords\Helper\Favicon\BestIconHelper;
use OCA\Passwords\Helper\Favicon\DefaultFaviconHelper;
use OCA\Passwords\Helper\Favicon\DuckDuckGoHelper;
use OCA\Passwords\Helper\Favicon\FaviconGrabberHelper;
use OCA\Passwords\Helper\Favicon\GoogleFaviconHelper;
use OCA\Passwords\Helper\Favicon\LocalFaviconHelper;
use OCA\Passwords\Helper\Image\AbstractImageHelper;
use OCA\Passwords\Helper\Image\AutoImageHelper;
use OCA\Passwords\Helper\Image\GdHelper;
use OCA\Passwords\Helper\Image\ImagickHelper;
use OCA\Passwords\Helper\Image\ImaginaryHelper;
use OCA\Passwords\Helper\Preview\AbstractPreviewHelper;
use OCA\Passwords\Helper\Preview\BrowshotPreviewHelper;
use OCA\Passwords\Helper\Preview\DefaultPreviewHelper;
use OCA\Passwords\Helper\Preview\PageresCliHelper;
use OCA\Passwords\Helper\Preview\ScreeenlyHelper;
use OCA\Passwords\Helper\Preview\ScreenShotLayerHelper;
use OCA\Passwords\Helper\Preview\ScreenShotMachineHelper;
use OCA\Passwords\Helper\SecurityCheck\AbstractSecurityCheckHelper;
use OCA\Passwords\Helper\SecurityCheck\BigDbPlusHibpSecurityCheckHelper;
use OCA\Passwords\Helper\SecurityCheck\BigLocalDbSecurityCheckHelper;
use OCA\Passwords\Helper\SecurityCheck\HaveIBeenPwnedHelper;
use OCA\Passwords\Helper\SecurityCheck\SmallLocalDbSecurityCheckHelper;
use OCA\Passwords\Helper\Words\AbstractWordsHelper;
use OCA\Passwords\Helper\Words\AutoWordsHelper;
use OCA\Passwords\Helper\Words\LeipzigCorporaHelper;
use OCA\Passwords\Helper\Words\LocalWordsHelper;
use OCA\Passwords\Helper\Words\RandomCharactersHelper;
use OCA\Passwords\Helper\Words\SnakesWordsHelper;
use Psr\Container\ContainerInterface;

/**
 * Class HelperService
 *
 * @package OCA\Passwords\Services
 */
class HelperService {

    const PREVIEW_SCREEN_SHOT_MACHINE = 'ssm';
    const PREVIEW_SCREEN_SHOT_LAYER   = 'ssl';
    const PREVIEW_BROW_SHOT           = 'bws';
    const PREVIEW_PAGERES             = 'pageres';
    const PREVIEW_SCREEENLY           = 'screeenly';
    const PREVIEW_DEFAULT             = 'default';

    const FAVICON_BESTICON        = 'bi';
    const FAVICON_FAVICON_GRABBER = 'fg';
    const FAVICON_DUCK_DUCK_GO    = 'ddg';
    const FAVICON_GOOGLE          = 'gl';
    const FAVICON_LOCAL           = 'local';
    const FAVICON_DEFAULT         = 'default';

    const WORDS_LOCAL   = 'local';
    const WORDS_RANDOM  = 'random';
    const WORDS_SNAKES  = 'wo4snakes';
    const WORDS_LEIPZIG = 'leipzig';
    const WORDS_AUTO    = 'auto';

    const SECURITY_BIGDB_HIBP  = 'bigdb+hibp';
    const SECURITY_BIG_LOCAL   = 'bigdb';
    const SECURITY_SMALL_LOCAL = 'smalldb';
    const SECURITY_HIBP        = 'hibp';

    const IMAGES_AUTO = 'auto';
    const IMAGES_IMAGICK = 'imagick';
    const IMAGES_GDLIB   = 'gdlib';
    const IMAGES_IMAGINARY   = 'imaginary';

    /**
     * @var ConfigurationService
     */
    protected ConfigurationService $config;

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * FaviconService constructor.
     *
     * @param ConfigurationService $config
     * @param ContainerInterface        $container
     */
    public function __construct(ConfigurationService $config, ContainerInterface $container) {
        $this->config    = $config;
        $this->container = $container;
    }

    /**
     * @param string|null $service
     *
     * @return AbstractImageHelper
     */
    public function getImageHelper(string $service = null): AbstractImageHelper {
        if($service === null) $service = $this->config->getAppValue('service/images', self::IMAGES_AUTO);

        return match ($service) {
            self::IMAGES_IMAGICK => $this->container->get(ImagickHelper::class),
            self::IMAGES_GDLIB => $this->container->get(GdHelper::class),
            self::IMAGES_IMAGINARY => $this->container->get(ImaginaryHelper::class),
            default => $this->container->get(AutoImageHelper::class),
        };
    }

    /**
     * @param string|null $service
     *
     * @return AbstractPreviewHelper
     */
    public function getWebsitePreviewHelper(string $service = null): AbstractPreviewHelper {
        if($service === null) $service = $this->config->getAppValue('service/preview', self::PREVIEW_DEFAULT);

        return match ($service) {
            self::PREVIEW_PAGERES => $this->container->get(PageresCliHelper::class),
            self::PREVIEW_BROW_SHOT => $this->container->get(BrowshotPreviewHelper::class),
            self::PREVIEW_SCREEN_SHOT_LAYER => $this->container->get(ScreenShotLayerHelper::class),
            self::PREVIEW_SCREEN_SHOT_MACHINE => $this->container->get(ScreenShotMachineHelper::class),
            self::PREVIEW_SCREEENLY => $this->container->get(ScreeenlyHelper::class),
            default => $this->container->get(DefaultPreviewHelper::class),
        };
    }

    /**
     * @param string|null $service
     *
     * @return AbstractFaviconHelper
     */
    public function getFaviconHelper(string $service = null): AbstractFaviconHelper {
        if($service === null) $service = $this->config->getAppValue('service/favicon', self::FAVICON_DEFAULT);

        return match ($service) {
            self::FAVICON_BESTICON => $this->container->get(BestIconHelper::class),
            self::FAVICON_FAVICON_GRABBER => $this->container->get(FaviconGrabberHelper::class),
            self::FAVICON_DUCK_DUCK_GO => $this->container->get(DuckDuckGoHelper::class),
            self::FAVICON_GOOGLE => $this->container->get(GoogleFaviconHelper::class),
            self::FAVICON_LOCAL => $this->container->get(LocalFaviconHelper::class),
            default => $this->container->get(DefaultFaviconHelper::class),
        };
    }

    /**
     * @param string|null $service
     *
     * @return AbstractWordsHelper
     */
    public function getWordsHelper(string $service = null): AbstractWordsHelper {
        if($service === null) $service = $this->config->getAppValue('service/words', HelperService::WORDS_AUTO);

        return match ($service) {
            self::WORDS_LOCAL => $this->container->get(LocalWordsHelper::class),
            self::WORDS_LEIPZIG => $this->container->get(LeipzigCorporaHelper::class),
            self::WORDS_SNAKES => $this->container->get(SnakesWordsHelper::class),
            self::WORDS_RANDOM => $this->container->get(RandomCharactersHelper::class),
            default => $this->container->get(AutoWordsHelper::class),
        };
    }

    /**
     * @param string|null $service
     *
     * @return AbstractSecurityCheckHelper
     */
    public function getSecurityHelper(string $service = null): AbstractSecurityCheckHelper {
        $service = $this->config->getAppValue('service/security', $service ?? self::SECURITY_HIBP);

        return match ($service) {
            self::SECURITY_BIG_LOCAL => $this->container->get(BigLocalDbSecurityCheckHelper::class),
            self::SECURITY_SMALL_LOCAL => $this->container->get(SmallLocalDbSecurityCheckHelper::class),
            self::SECURITY_BIGDB_HIBP => $this->container->get(BigDbPlusHibpSecurityCheckHelper::class),
            default => $this->container->get(HaveIBeenPwnedHelper::class),
        };
    }

    /**
     * @return DefaultFaviconHelper
     */
    public function getDefaultFaviconHelper(): DefaultFaviconHelper {
        return $this->container->get(DefaultFaviconHelper::class);
    }
}