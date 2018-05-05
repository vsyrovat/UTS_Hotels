<?php declare(strict_types=1);

namespace App\Service;

use App\Exception\CurrencyRaterException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class CurrencyRater
{
    const FETCH_URL = 'http://www.cbr.ru/scripts/XML_daily.asp';

    private $cachePool;
    private $logger;
    private $ttl = 3600;
    private $fetchUrl;

    public function __construct(CacheItemPoolInterface $cachePool, LoggerInterface $logger)
    {
        $this->cachePool = $cachePool;
        $this->logger = $logger;
        $this->fetchUrl = self::FETCH_URL;
    }

    /**
     * @param string $currency
     * @return float
     * @throws \RuntimeException
     */
    public function getRate(string $currency): float
    {
        $currency = strtoupper($currency);

        if (in_array($currency, ['RUB', 'RUR'])) {
            return 1;
        }

        $rates = $this->getRatesCached();
        if (!isset($rates[$currency])) {
            throw new CurrencyRaterException('Cannot find rate for currency '.$currency);
        }

        return $rates[$currency];
    }

    /**
     * @return array
     */
    private function getRates(): array
    {
        $result = [];
        $content = file_get_contents($this->fetchUrl);
        if ($content) {
            $xml = simplexml_load_string($content);
            foreach ($xml->Valute as $valute){
                $value = floatval(str_replace(',', '.', (string)$valute->Value)) / floatval($valute->Nominal);
                $result[(string)$valute->CharCode] = $value;
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    private function getRatesCached(): array
    {
        try {
            $cacheItem = $this->cachePool->getItem($this->getCacheKey());

            if (!$cacheItem->isHit()) {
                $result = $this->getRates();
                $cacheItem
                    ->set($result)
                    ->expiresAfter($this->ttl);
                $this->cachePool->save($cacheItem);
            }

            return $cacheItem->get();
        } catch (\Psr\Cache\CacheException $e) {
            $this->logger->critical(
                sprintf('Cache subsystem error in %s says: %s, %s, %s',
                    __CLASS__, $e->getMessage(), $e->getCode(), $e->getTraceAsString())
            );

            return $this->getRates();
        }
    }

    /**
     * @return string
     */
    private function getCacheKey()
    {
        return sprintf('CURRENCY_RATES_%s', md5($this->fetchUrl));
    }
}
