<?php

class GoogleNewsSearchBridge extends BridgeAbstract {
    const NAME = 'Google News Search Bridge';
    const URI = 'https://news.google.com/';
    const DESCRIPTION = 'Retourne les résultats de Google News pour une recherche donnée';
    const MAINTAINER = 'cheick';
    const PARAMETERS = [
        'global' => [
            'q' => [
                'name' => 'Mot-clé',
                'type' => 'text',
                'required' => true,
                'exampleValue' => 'mali niger burkina faso aes'
            ],
            'hl' => [
                'name' => 'Langue',
                'type' => 'list',
                'values' => [
                    'Français' => 'fr'
                ],
                'defaultValue' => 'fr'
            ],
            'gl' => [
                'name' => 'Pays',
                'type' => 'text',
                'exampleValue' => 'FR',
                'defaultValue' => 'FR'
            ],
            'ceid' => [
                'name' => 'CEID',
                'type' => 'text',
                'exampleValue' => 'FR:fr',
                'defaultValue' => 'FR:fr'
            ]
        ]
    ];

    public function collectData() {
        $url = 'https://news.google.com/search?' . http_build_query([
            'q' => $this->getInput('q'),
            'hl' => $this->getInput('hl'),
            'gl' => $this->getInput('gl'),
            'ceid' => $this->getInput('ceid')
        ]);

        $html = getSimpleHTMLDOM($url)
            or returnServerError('Impossible de charger Google News');

        foreach ($html->find('article') as $article) {
            $a = $article->find('a', 0);
            if (!$a) continue;

            $title = $a->plaintext;
            $relativeLink = $a->href;
            $link = urljoin('https://news.google.com', $relativeLink);
            $fullLink = str_replace('/articles/', 'https://news.google.com/articles/', $link);

            $description = '';
            $descElem = $article->find('span', 1);
            if ($descElem) {
                $description = $descElem->plaintext;
            }

            $source = '';
            $srcElem = $article->find('div span', 0);
            if ($srcElem) {
                $source = $srcElem->plaintext;
            }

            $timestamp = time(); // pas de date exacte dans le HTML simple

            $this->items[] = [
                'uri' => $fullLink,
                'title' => $title,
                'timestamp' => $timestamp,
                'author' => $source,
                'content' => $description
            ];
        }
    }

    public function getURI() {
        return 'https://news.google.com/';
    }

    public function getName() {
        return 'Google News Search : ' . $this->getInput('q');
    }
}
