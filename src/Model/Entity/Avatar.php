<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Avatar Entity
 *
 * @property string $id
 * @property string $src
 * @property string $user_id
 * @property int|null $filesize
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\User $user
 */
class Avatar extends Entity
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
    protected $_accessible = [
        'id' => true,
        'src' => true,
        'user_id' => true,
        'filesize' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'user_id', 'filesize', 'created', 'modified'
    ];
}
