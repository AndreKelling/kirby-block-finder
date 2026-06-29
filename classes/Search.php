<?php

namespace BlockFinder;

final class Search
{
  public static function find(): array
  {
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

        $blocksField = $page->content($langCode)->get($fieldName);

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

}