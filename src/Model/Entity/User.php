<?php
namespace App\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string $username
 * @property string|null $name
 * @property string|null $email
 * @property string|null $password
 * @property bool|null $active
 * @property int|null $group_id
 * @property \Cake\I18n\FrozenTime|null $last_login
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\Group $group
 * @property \App\Model\Entity\Image[] $images
 * @property \App\Model\Entity\Video[] $videos
 */
class User extends Entity
{
    protected function _setPassword($password) {
        if (strlen($password)) {
            $hasher = new DefaultPasswordHasher();

            return $hasher->hash($password);
        }
    }

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
        'username' => true,
        'name' => true,
        'email' => true,
        'password' => true,
        'active' => true,
        'group_id' => true,
        'last_login' => true,
        'created' => true,
        'modified' => true,
        'group' => true,
        'videos' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'password',
    ];
}
