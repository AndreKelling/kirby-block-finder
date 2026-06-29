<?php

use BlockFinder\Search;

@include_once __DIR__.'/vendor/autoload.php';

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
          $langCodes = null;

          if ($kirby->multilang()) {
            foreach ($kirby->languages() as $lang) {
              $langCodes[] = $lang->code();
            }
          }

          $search = new Search($langCodes);

          return $search->find($type);
        }
      ]
    ]
  ]
]);