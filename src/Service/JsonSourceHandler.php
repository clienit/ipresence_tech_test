<?php

namespace App\Service;

use Symfony\Component\Finder\Finder;

class JsonSourceHandler implements SourceInterface
{
    private $finder;
    private $folder;

    public function __construct(
        Finder $finder,
        string $folder
    ) {
        $this->finder = $finder;
        $this->folder = $folder;
    }

    public function getData(): array
    {
        // find all JSON files inside $folder
        $this->finder->files()->in('../' . $this->folder . '/');

        $data = [];
        // check if there are any search results
        if ($this->finder->hasResults()) {
            // loop through the files found
            foreach ($this->finder as $file) {
                $absoluteFilePath = $file->getRealPath();
                $jsonData = json_decode(file_get_contents($absoluteFilePath), true);
                $data = $jsonData["quotes"];
            }
        }

        return $data;
    }
}
