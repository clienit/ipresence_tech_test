<?php

namespace App\Service;

use App\Exception\GetQuoteLimitException;

use Symfony\Component\HttpKernel\KernelInterface;
use Ausi\SlugGenerator\SlugGenerator;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class QuoteHandler
{
    public const MAX_NUMBER_OF_QUOTES = 10;

    private $kernel;
    private $slugGenerator;
    private $cache;
    private $TTL;
    private $sourceInterface;

    public function __construct(
        KernelInterface $kernel,
        SlugGenerator $slugGenerator,
        FilesystemAdapter $fileSystemAdapter,
        int $TTL,
        SourceInterface $sourceInterface
    ) {
        $this->kernel = $kernel;
        $this->slugGenerator = $slugGenerator;
        $this->cache = $fileSystemAdapter;
        $this->TTL = $TTL;
        $this->sourceInterface = $sourceInterface;
    }

    public function findByAuthor(string $author, int $limit): array
    {
        // Count N provided MUST be equal or less than 10
        // If not, API should return an error.
        if ($limit > self::MAX_NUMBER_OF_QUOTES) {
            throw new GetQuoteLimitException('Maximum limit value is: ' . self::MAX_NUMBER_OF_QUOTES);
        }

        $quotes = [];
        // Retrieve the cache item
        $cachedQuotes = $this->cache->getItem('quote_' . md5($author));
        if ($cachedQuotes->isHit()) {
            // Quote exist in the cache
            // Retrieve the value stored by the cache
            $quotes = $cachedQuotes->get();
        } else {
            // Retrieve author quotes           
            $quotes = $this->findQuotesByAuthor($author);
            // Update cache
            $cachedQuotes->set($quotes);
            $cachedQuotes->expiresAfter($this->TTL);            
            $this->cache->save($cachedQuotes);
        }
        // Return $limit number of quotes
        return array_slice($quotes, 0, $limit);
    }

    private function findQuotesByAuthor($author): array
    {
        $quotes = [];
        $quotesData = $this->sourceInterface->getData();
        foreach ($quotesData as $quote) {
            $text = utf8_decode($quote['quote']);
            $slug = $this->slugGenerator->generate($quote['author']);

            if($author === $slug) {
                // Add ! to the end of the quote
                if (substr($text, -1) !== '!') {
                    $text = (substr($text, -1) === '.') ? substr_replace($text, '!', strlen($text) - 1) : $text . '!';
                }
                // Return quote in uppercase.
                $quotes[] = strtoupper($text);
            }
        }

        return $quotes;
    }
}
