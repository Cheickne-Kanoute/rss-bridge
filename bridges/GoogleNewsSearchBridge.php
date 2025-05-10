<?php

class GoogleNewsSearchBridge extends BridgeAbstract {

    const NAME = 'Google News Search';
    const URI = 'https://news.google.com/';
    const DESCRIPTION = 'Recherche Google News avec titre, lien, image, auteur';
    const MAINTAINER = 'GPT-AES';
    const PARAMETERS = [[
        'q' => ['name' => 'Mot-clé', 'type' => 'text', 'required' => true, 'exampleValue' => 'Mali'],
        'hl' => ['name' => 'Langue', 'type' => 'list', 'values' => ['Français' => 'fr', 'Anglais' => 'en'], 'defaultValue' => 'fr'],
        'gl' => ['name' => 'Pays', 'type' => 'text', 'defaultValue' => 'FR'],
        'ceid' => ['name' => 'Édition (ceid)', 'type' => 'text', 'defaultValue' => 'FR:fr']
    ]];

    public function collectData() {
        $q = urlencode($this->getInput('q'));
        $hl = $this->getInput('hl');
        $gl = $this->getInput('gl');
        $ceid = $this->getInput('ceid');

        $url = "https://news.google.com/search?q=$q&hl=$hl&gl=$gl&ceid=$ceid";
        $html = getSimpleHTMLDOM($url) or returnServerError('Impossible de charger Google News');

        foreach ($html->find('main article') as $article) {
            // Titre et lien
            $titleTag = $article->find('h3 a, h4 a', 0);
            if (!$titleTag) continue;

            $title = trim($titleTag->plaintext);
            $relativeUrl = $titleTag->href;
            $fullUrl = str_replace('./', self::URI, $relativeUrl);

            // Source (auteur)
            $source = '';
            $sourceTag = $article->find('div.bInasb span', 0);
            if ($sourceTag) {
                $source = trim(str_replace('Par ', '', $sourceTag->plaintext));
            }

            // Image
            $imgUrl = '';
            $imgContainer = $article->find('figure img', 0);
            if ($imgContainer && isset($imgContainer->src)) {
                $imgUrl = $imgContainer->src;
            } elseif ($article->find('a.WwrzSb', 0)) {
                $styleAttr = $article->find('a.WwrzSb', 0)->getAttribute('style');
                if (preg_match('/url\((.*?)\)/', $styleAttr, $matches)) {
                    $imgUrl = $matches[1];
                }
            }

            // Description
            $desc = '';
            $descTag = $article->find('span', 1);
            if ($descTag) $desc = trim($descTag->plaintext);

            // Contenu
            $content = '';
            if ($imgUrl) {
                $content .= '<img src="' . htmlspecialchars($imgUrl) . '" style="max-width:100%;"><br>';
            }
            $content .= htmlspecialchars($desc);

            // Ajouter à la liste
            $this->items[] = [
                'title' => $title,
                'uri' => $fullUrl,
                'author' => $source,
                'timestamp' => time(),
                'content' => $content
            ];
        }
    }

    public function getName() {
        $q = $this->getInput('q') ?? '';
        return 'Google News Search : ' . $q;
    }

    public function getURI() {
        return self::URI;
    }
}
