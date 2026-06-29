<?php

Kirby::plugin('andrekelling/kirby-block-finder', [
  'options' => [
    'fieldName' => 'blocks'
  ],
  'areas' => [
    'block-finder' => function () {
      return [
        'label' => 'Block Finder',
        'icon' => 'search',
        'menu' => true,
        'views' => [
          [
            'pattern' => 'block-finder',
            'action' => function () {
              return [
                'component' => 'k-block-finder-view',
                'title' => 'Block Usage Finder',
                'search'    => 'pages',
                'props' => [
                  'fieldName' => option('andrekelling.kirby-block-finder.fieldName')
                ],
                'breadcrumb' => [
                  [
                    'label' => 'Block Usage'
                  ]
                ]
              ];
            }
          ]
        ]
      ];
    }
  ],
  'api' => [
    'routes' => [
      [
        'pattern' => 'block-finder/block-types',
        'method'  => 'GET',
        'action'  => function () {
          $blockBlueprints = kirby()->blueprints('blocks');

          return $blockBlueprints;
        }
      ],
      [
        'pattern' => 'block-finder/search',
        'method'  => 'GET',
        'action'  => function () {

          $type = get('type');

          if (!$type) {
            return [];
          }
          $kirby = kirby();
          $results = [];
          $fieldName = option('andrekelling.kirby-block-finder.fieldName');

          foreach (site()->index() as $page) {
            foreach($kirby->languages() as $lang) {
              $langCode = $lang->code();

              if (!$page->content($langCode)->exists()) {
                continue;
              }

              $blocksField = $page->content()->get($fieldName);

              if ($blocksField->isEmpty()) {
                continue;
              }

              $count = 0;
              $allBocksOnPage = $blocksField->value();
              $allBocksOnPageArr = json_decode($allBocksOnPage);
              foreach ($allBocksOnPageArr as $block) {
                if ($block->type === $type) {
                  $count++;
                }
              }
              if ($count > 0) {
                $results[] = [
                  'lang' => $langCode,
                  'pageId' => $page->id(),
                  'title' => $page->title()->value(),
                  'count' => $count,
                  'panelUrl' => $page->panel()->url() . '?language=' . $langCode
                ];
              }
            }
          }

          return $results;
        }
      ]
    ]
  ]
]);