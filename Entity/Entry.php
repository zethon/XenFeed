<?php

namespace lulzapps\Feed\Entity;

use XF\Mvc\Entity\Structure;

class Entry extends \XF\Mvc\Entity\Entity
{

public function getUserReaction()
{
    $user_id = (\XF::visitor())->user_id;
    $finder = $this->finder('lulzapps\Feed:Reaction');
    $finder->where('entry_id', $this->entry_id);
    $finder->where('user_id', $user_id);

    $reaction = $finder->fetchOne();

    if (!$reaction) return '';
    return $reaction['reaction'];
}

public function getLikesCount()
{
    return $this->db()->fetchOne("
        SELECT COUNT(*)
        FROM 
            lulzapps_feed_reaction
        WHERE 
            entry_id = ?
            AND reaction='like'
    ", 
    $this->entry_id);
}

public function getDislikesCount()
{
    return $this->db()->fetchOne("
        SELECT COUNT(*)
        FROM 
            lulzapps_feed_reaction
        WHERE 
            entry_id = ?
            AND reaction='dislike'
    ", 
    $this->entry_id);
}

public function getRepliesCount()
{
    return $this->db()->fetchOne("
        SELECT COUNT(*)
        FROM 
            lulzapps_feed_entry
        WHERE 
            reply_to = ?
    ", 
    $this->entry_id);
}

public function getReplies()
{
    $finder = $this->finder('lulzapps\Feed:Entry');
    $finder
        ->setDefaultOrder('date', 'DESC')
        ->with('User', false)
        // ->with('Original', false)
        ->with('EntryDeleted', false)
        ->where('reply_to', $this->entry_id);

    return $finder;
}

public function canDelete($type = 'soft', &$error = null)
{
    $visitor = \XF::visitor();
    if (!$visitor->user_id)
    {
        return false;
    }

    if ($visitor->user_id == $this->user_id)
    {
        return true;
    }

    return false;
}

public function canHardDelete()
{
    return false;
}

public static function getStructure(Structure $structure)
{
    $structure->table = 'lulzapps_feed_entry';
    $structure->primaryKey = 'entry_id';
    $structure->contentType = 'lulzapps_feed_entry';
    $structure->columns = 
        [
            'entry_id' => ['type' => self::UINT, 'nullable' => true, 'autoIncrement' => true, 'required' => false],
            'user_id' => ['type' => self::UINT, 'required' => true],
            'comment' => ['type' => self::STR, 'required' => true, 'maxLength' => 255],
            'date' => ['type' => self::UINT, 'default' => time()],
            'reply_to' => ['type' => self::UINT, 'required' => false, 'default' => 0],
            'deleted' => ['type' => self::BOOL, 'required' => true, 'default' => false],
        ];

    $structure->getters = 
        [
            'likes_count' => true,
            'dislikes_count' => true,
            'replies_count' => true,
            'replies' => true
        ];

    $structure->relations['User'] = 
        [
            'entity' => 'XF:User',
            'type' => self::TO_ONE,
            'conditions' => 'user_id',
            'primary' => true
        ];
    $structure->relations['Original'] = 
        [
            'entity' => 'lulzapps\Feed:Entry',
            'type' => self::TO_ONE,
            'conditions' => [ ['entry_id', '=', '$reply_to'] ],
            'primary' => true
        ];
    $structure->relations['EntryDeleted'] = 
        [
            'entity' => 'lulzapps\Feed:EntryDeleted',
            'type' => self::TO_ONE,
            'conditions' => 'entry_id',
            'primary' => true
        ];

    return $structure;
}

}