<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Image Entity
 *
 * @property string $id
 * @property string|null $iso
 * @property string|null $longitude
 * @property string|null $aperture
 * @property string|null $make
 * @property string|null $model
 * @property string|null $title
 * @property string|null $description
 * @property string|null $exposure
 * @property \Cake\I18n\DateTime|null $captured
 * @property string|null $software
 * @property string $src
 * @property int|null $filesize
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Video[] $videos
 */
class Image extends Entity
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
        'id' => true,
        'iso' => true,
        'longitude' => true,
        'aperture' => true,
        'make' => true,
        'model' => true,
        'title' => true,
        'description' => true,
        'exposure' => true,
        'captured' => true,
        'software' => true,
        'src' => true,
        'filesize' => true,
        'created' => true,
        'modified' => true,
        'videos' => true,
    ];
}
