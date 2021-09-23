<?php

declare(strict_types=1);

use \Bitrix\Main\{
    EventManager
};
use \Bitrix\Crm\{
    Integrity
};

// Find Duplicates
EventManager::getInstance()->addEventHandler('crm', 'OnBeforeCrmLeadAdd', function (&$fields){
    $phones = \array_unique(\array_filter(\array_column(\array_values((array)$fields['FM']['PHONE']), 'VALUE')));
    $emails = \array_unique(\array_filter(\array_column(\array_values((array)$fields['FM']['EMAIL']), 'VALUE')));

    $criterions = [];
    $find = function(string $type, array $values = []) use($criterions): ?Integrity\Duplicate{
        foreach($values as $value){
            $criterion = new Integrity\DuplicateCommunicationCriterion($type, $value);
            foreach($criterions as $variant) {
                /** @var Integrity\DuplicateCriterion $variant */
                if($criterion->equals($variant)) {
                    continue 2;
                }
            }

            $criterions[] = $criterion;

            $duplicate = $criterion->find(\CCrmOwnerType::Lead, 1);
            if($duplicate !== null) {
                return $duplicate;
            }
        }

        return null;
    };

    $duplicate = $find('PHONE', $phones) ?? $find('EMAIL', $emails);
    if($duplicate){
        $entities = $duplicate->getEntities();
        /**
         * @var $parent Integrity\DuplicateEntity
         */
        $parent = \array_shift($entities);

        $fields['TITLE'] .= ' [duplicate]';
        // Add reference to parent lead
        // @todo create field and change ID if required
        $fields['UF_PARENT'] = 'L_' . $parent->getEntityID();
    }
});

// Validate Comment Field
EventManager::getInstance()->addEventHandler('crm', 'OnBeforeCrmLeadAdd', function (&$fields){
    if(\is_string($fields['COMMENTS']) && $fields['COMMENTS'][0] == '@'){
        // @todo check for additional code after #
        $phoneNumber = \str_replace(['+', '-', ' ', '(', ')'], '', \trim(\mb_substr($fields['COMMENTS'], 1)));
        if(\is_numeric($phoneNumber)) {
            if(!\array_key_exists('FM', $fields)){
                $fields['FM'] = [];
            }
            if(!\array_key_exists('PHONE', $fields['FM'])){
                $fields['FM']['PHONE'] = [];
            }

            $fields['FM']['PHONE']['n' . (\count($fields['FM']['PHONE']) + 1)] = [
                'VALUE_TYPE' => 'WORK',
                'VALUE' => (string)$phoneNumber,
            ];

            $fields['COMMENTS'] = '';
        }
    }
}, false,40);