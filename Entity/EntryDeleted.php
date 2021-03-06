<?php

namespace lulzapps\Feed\Entity;

use XF\Mvc\Entity\Structure;

class EntryDeleted extends \XF\Mvc\Entity\Entity
{

public static function getStructure(Structure $structure)
{
    $structure->table = 'lulzapps_feed_entry_deleted';
    $structure->shortName = 'lulzapps\Feed:EntryDeleted';
    $structure->primaryKey = 'entry_deleted_id';
    $structure->columns = 
        [
            'entry_deleted_id' => ['type' => self::UINT, 'nullable' => true, 'autoIncrement' => true, 'required' => false],
            'entry_id' => ['type' => self::UINT, 'required' => true],
            'user_id' => ['type' => self::UINT, 'required' => true],
            'date' => ['type' => self::UINT, 'default' => time()],
            'reason' => ['type' => self::STR, 'required' => true, 'maxLength' => 120],
        ];
    $structure->getters = [];
    $structure->relations['Entry'] = 
        [
            'entity' => 'lulzapps\Feed:Feed',
            'type' => self::TO_ONE,
            'conditions' => 'entry_id',
            'primary' => true
        ];
    $structure->relations['User'] = 
        [
            'entity' => 'XF:User',
            'type' => self::TO_ONE,
            'conditions' => 'user_id',
            'primary' => true
        ];

    return $structure;
}

}