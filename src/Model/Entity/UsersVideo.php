<?php

declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * UsersVideo Entity
 *
 * @property string $id
 * @property string $user_id
 * @property string $video_id
 * @property \Cake\I18n\FrozenTime|null $start
 * @property \Cake\I18n\FrozenTime|null $end
 * @property float|null $playhead
 * @property string|null $time_watched
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Video $video
 */
class UsersVideo extends Entity
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
        'user_id' => true,
        'video_id' => true,
        'start' => true,
        'end' => true,
        'playhead' => true,
        'time_watched' => true,
        'user' => true,
        'video' => true,
    ];
}
