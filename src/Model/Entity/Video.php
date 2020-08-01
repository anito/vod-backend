<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Video Entity
 *
 * @property int $id
 * @property string $filename
 * @property string $title
 * @property string $description
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
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
        'src' => true,
        'filesize' => true,
        'order' => true,
        'exposure' => true,
        'iso' => true,
        'longitude' => true,
        'aperture' => true,
        'model' => true,
        'date' => true,
        'title' => true,
        'bias' => true,
        'metering' => true,
        'focal' => true,
        'software' => true,
        'created' => true,
        'modified' => true,
    ];
}


