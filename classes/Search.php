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
      $isMultilang = $kirby->multilang();
      $langCode = null;

      if ($isMultilang) {
        foreach($kirby->languages() as $lang) {
          $langCode = $lang->code();

          if (!$page->content($langCode)->exists()) {
            continue;
          }

          try {
            $blocksField = self::getBlocksField($page, $langCode, $fieldName);
          } catch (\Exception $e) {
            continue;
          }
          $count = self::getBlocksCount($blocksField, $type);

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
      } else {
        try {
          $blocksField = self::getBlocksField($page, $langCode, $fieldName);
        } catch (\Exception $e) {
          continue;
        }
        $count = self::getBlocksCount($blocksField, $type);

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

  private static function getBlocksField($page, $langCode, $fieldName): object {
    $blocksField = $page->content($langCode)->get($fieldName);

    if ($blocksField->isEmpty()) {
      throw new \Exception('Block field is empty');
    }

    return $blocksField;
  }

  private static function getBlocksCount($blocksField, $type): int {
    $count = 0;
    $allBocksOnPage = $blocksField->value();
    $allBocksOnPageArr = json_decode($allBocksOnPage);
    foreach ($allBocksOnPageArr as $block) {
      if ($block->type === $type) {
        $count++;
      }
    }

    return $count;
  }
}