<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Video Entity
 *
 * @property string $id
 * @property string|null $image_id
 * @property string|null $title
 * @property string|null $description
 * @property string $src
 * @property int|null $filesize
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property int $sequence
 *
 * @property \App\Model\Entity\Image $image
 * @property \App\Model\Entity\User[] $users
 */
class Video extends Entity
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
        'image_id' => true,
        'title' => true,
        'description' => true,
        'src' => true,
        'filesize' => true,
        'created' => true,
        'modified' => true,
        'sequence' => true,
        'playhead' => true,
        'image' => true,
        'users' => true,
    ];
}
