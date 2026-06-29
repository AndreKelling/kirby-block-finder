<?php

namespace BlockFinder;

use Kirby\Cms\Page;
use Kirby\Content\Field;

final class Search
{
  private array $langCodes;

  /**
   * normalize langCodes to array
   *
   * @param array|null $langCodes
   */
  public function __construct(?array $langCodes)
  {
    if ($langCodes === null) {
      $this->langCodes = [null];
    } else {
      $this->langCodes = $langCodes;
    }
  }

  /**
   * find all pages with blocks of a given type
   * 1. get all pages
   * 2. get all blocks of a given type
   * 3. count blocks of a given type
   *
   * @param string $type
   * @return array with result object
   */
  public function find(string $type): array
  {
    $results = [];
    $fieldName = \option('andrekelling.kirby-block-finder.fieldName');
    foreach (\site()->index() as $page) {
      foreach ($this->langCodes as $lang) {
        if (!$page->content($lang)->exists()) {
          continue;
        }

        try {
          $blocksField = self::getBlocksField($page, $lang, $fieldName);
        } catch (\Exception) {
          continue;
        }
        $count = self::getBlocksCount($blocksField, $type);

        if ($count > 0) {
          $results[] = [
            'lang' => $lang,
            'pageId' => $page->id(),
            'title' => $page->title()->value(),
            'count' => $count,
            'panelUrl' => $page->panel()->url() . '?language=' . $lang
          ];
        }
      }
    }

    return $results;
  }

  /**
   * get the "blocks" field of a page
   *
   * @param Page $page
   * @param string|null $langCode
   * @param string $fieldName
   * @return Field
   * @throws \Exception silent exception
   */
  private static function getBlocksField(Page $page, ?string $langCode, string $fieldName): Field {
    $blocksField = $page->content($langCode)->get($fieldName);

    if ($blocksField->isEmpty()) {
      throw new \Exception('Block field is empty');
    }

    return $blocksField;
  }

  /**
   * get the count of blocks of a given type
   *
   * @param Field $blocksField
   * @param string $type
   * @return int
   */
  private static function getBlocksCount(Field $blocksField, string $type): int {
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