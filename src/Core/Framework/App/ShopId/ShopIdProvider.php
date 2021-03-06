<?php declare(strict_types=1);

namespace Shopware\Core\Framework\App\ShopId;

use Shopware\Core\Framework\App\Exception\AppUrlChangeDetectedException;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ShopIdProvider
{
    public const SHOP_ID_SYSTEM_CONFIG_KEY = 'core.app.shopId';
    public const SHOP_DOMAIN_CHANGE_CONFIG_KEY = 'core.app.domainChange';

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public function getShopId(): string
    {
        $shopId = $this->systemConfigService->get(self::SHOP_ID_SYSTEM_CONFIG_KEY);

        if (!$shopId) {
            $newShopId = $this->generateShopId();
            $this->systemConfigService->set(self::SHOP_ID_SYSTEM_CONFIG_KEY, [
                'app_url' => $_SERVER['APP_URL'],
                'value' => $newShopId,
            ]);

            return $newShopId;
        }

        if ($_SERVER['APP_URL'] !== ($shopId['app_url'] ?? '')) {
            $this->systemConfigService->set(self::SHOP_DOMAIN_CHANGE_CONFIG_KEY, true);
            /** @var string $appUrl */
            $appUrl = $_SERVER['APP_URL'];

            throw new AppUrlChangeDetectedException($shopId['app_url'], $appUrl);
        }

        return $shopId['value'];
    }

    private function generateShopId(): string
    {
        return Random::getAlphanumericString(16);
    }
}
