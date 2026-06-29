<?php

use BlockFinder\Search;
use Kirby\Cms\Blueprint;
use Kirby\Http\Response;

@include_once __DIR__.'/vendor/autoload.php';

Kirby::plugin('andrekelling/kirby-block-finder', [
  'options' => [
    'blueprintName' => 'fields/builder',
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
          try {
            $blueprintName = option('andrekelling.kirby-block-finder.blueprintName');
            $blueprintBlocksField = Blueprint::find($blueprintName);
          } catch (\Exception $e) {
            return Response::json([
              'status'  => 'error',
              'message' => $e->getMessage()
            ], 404);
          }

          if ($blueprintBlocksField['type'] !== 'blocks') {
            return Response::json([
              'status'  => 'error',
              'message' => $blueprintName.' field is not of type blocks. We want to find blocks here!'
            ], 400);
          }

          $fieldsets = $blueprintBlocksField['fieldsets'] ?? [];

          if ($fieldsets === []) {
            return Response::json([
              'status'  => 'error',
              'message' => $blueprintName.' fieldsets prop is empty'
            ], 400);
          }

          return $fieldsets;
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