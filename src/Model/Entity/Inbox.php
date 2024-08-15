<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Inbox Entity
 *
 * @property string $id
 * @property string $user_id
 * @property string $_from
 * @property bool $_read
 * @property array $message
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\User $user
 */
class Inbox extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected array $_accessible = [
        'user_id' => true,
        '_from' => true,
        '_read' => true,
        '_to' => true,
        'message' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];
}
