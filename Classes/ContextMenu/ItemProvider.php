<?php
declare(strict_types=1);

namespace Cobweb\BranchCache\ContextMenu;

/*
 * This file is part of the Cobweb/BranchCache project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\ContextMenu\ItemProviders\PageProvider;

/**
 * Class ItemProvider
 */
class ItemProvider extends PageProvider
{
    /**
     * @var array
     */
    protected $itemsConfiguration = [
        'clearBranchCache' => [
            'type' => 'item',
            'label' => 'LLL:EXT:branch_cache/Resources/Private/Language/locallang.xlf:clear.branch.cache',
            'iconIdentifier' => 'actions-system-cache-clear-impact-medium',
            'callbackAction' => 'clearBranchCache'
        ],
    ];

    /**
     * @param string $itemName
     * @param string $type
     * @return bool
     */
    protected function canRender(string $itemName, string $type): bool
    {
        if (in_array($itemName, $this->disabledItems, true)) {
            return false;
        }
        return $this->canClearCache();
    }

    /**
     * This method adds custom item to list of items generated by item providers with higher priority value (PageProvider)
     * You could also modify existing items here.
     * The new item is added after the 'info' item.
     *
     * @param array $items
     * @return array
     */
    public function addItems(array $items): array
    {
        $this->initDisabledItems();

        if (isset($items['clearCache'])) {
            // renders an item based on the configuration from $this->itemsConfiguration
            $localItems = $this->prepareItems($this->itemsConfiguration);
            //finds a position of the item after which 'hello' item should be added
            $position = array_search('clearCache', array_keys($items), true);

            //slices array into two parts
            $beginning = array_slice($items, 0, $position + 1, true);
            $end = array_slice($items, $position, null, true);

            // adds custom item in the correct position
            $items = $beginning + $localItems + $end;
        }
        //passes array of items to the next item provider
        return $items;
    }

    /**
     * This priority should be lower than priority of the PageProvider, so it's evaluated after the PageProvider
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 60;
    }

    /**
     * @param string $itemName
     * @return array
     */
    protected function getAdditionalAttributes(string $itemName): array
    {
        return [
            'data-callback-module' => 'TYPO3/CMS/BranchCache/ContextMenuActions',
        ];
    }

    /**
     * Checks if the user has clear cache rights
     *
     * @return bool
     */
    protected function canClearCache(): bool
    {
        return !$this->isRoot()
            && ($this->backendUser->isAdmin() || $this->backendUser->getTSConfigVal('options.clearCache.pages'));
    }

}