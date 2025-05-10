<?php

class GoogleNewsSearchBridge extends BridgeAbstract {

    const NAME = 'Google News Search';
    const URI = 'https://news.google.com/';
    const DESCRIPTION = 'Recherche d’actualités Google News (titre, description, lien, source)';
    const MAINTAINER = 'GPT-AES';
    const PARAMETERS = [
        [
            'q' => [
                'name' => 'Requête de recherche',
                'type' => 'text',
                'required' => true,
                'exampleValue' => 'mali niger burkina faso aes'
            ],
            'hl' => [
                'name' => 'Langue',
                'type' => 'list',
                'values' => [
                    'Français (fr)' => 'fr',
                    'Anglais (en)' => 'en'
                ],
                'defaultValue' => 'fr'
            ],
            'gl' => [
                'name' => 'Pays',
                'type' => 'text',
                'defaultValue' => 'FR'
            ],
            'ceid' => [
                'name' => 'Édition (ceid)',
                'type' => 'text',
                'defaultValue' => 'FR:fr'
            ]
        ]
    ];

    public function collectData() {
        $query = urlencode($this->getInput('q'));
        $hl = $this->getInput('hl');
        $gl = $this->getInput('gl');
        $ceid = $this->getInput('ceid');

        $url = 'https://news.google.com/search?q=' . $query
            . '&hl=' . $hl . '&gl=' . $gl . '&ceid=' . urlencode($ceid);

        $html = getSimpleHTMLDOM($url)
            or returnServerError('Impossible de charger Google News');

        foreach ($html->find('article') as $article) {
            $titleElement = $article->find('h3 a, h4 a', 0);
            if (!$titleElement) continue;

            $title = html_entity_decode($titleElement->plaintext);
            $relativeLink = $titleElement->href;
            $fullLink = urljoin(self::URI, $relativeLink);
            $fullLink = str_replace('./articles/', 'https://news.google.com/articles/', $fullLink);

            $description = '';
            $descElement = $article->find('span', 1);
            if ($descElement) {
                $description = html_entity_decode($descElement->plaintext);
            }

            $source = '';
            $srcElement = $article->find('div span', 0);
            if ($srcElement) {
                $source = html_entity_decode($srcElement->plaintext);
            }

            $this->items[] = [
                'uri' => $fullLink,
                'title' => $title,
                'timestamp' => time(), // Pas de date précise disponible
                'author' => $source,
                'content' => $description
            ];
        }
    }

    public function getURI() {
        return self::URI;
    }

    public function getName() {
        $query = $this->getInput('q') ?? '';
        return 'Google News Search : ' . $query;
    }
}
