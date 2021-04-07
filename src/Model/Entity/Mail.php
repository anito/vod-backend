<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Mail Entity
 *
 * @property string $id
 * @property string $users_id
 * @property string|resource $data
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User $user
 */
class Mail extends Entity
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
        'users_id' => true,
        'sent' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];
}
