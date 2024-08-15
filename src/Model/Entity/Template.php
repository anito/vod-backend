<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Template Entity
 *
 * @property string $id
 * @property string $slug
 * @property string|null $name
 * @property bool $protected
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\EmailTemplatesbak[] $email_templatesbak
 * @property \App\Model\Entity\Item[] $items
 */
class Template extends Entity
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
        'slug' => true,
        'name' => true,
        'protected' => true,
        'created' => true,
        'modified' => true,
        'items' => true,
    ];
}
