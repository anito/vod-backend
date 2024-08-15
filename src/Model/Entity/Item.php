<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Item Entity
 *
 * @property string $id
 * @property string $content
 * @property string $field_id
 * @property string $template_id
 *
 * @property \App\Model\Entity\Field $field
 * @property \App\Model\Entity\Template $template
 */
class Item extends Entity
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
        'content' => true,
        'field_id' => true,
        'template_id' => true,
        'field' => true,
        'template' => true,
    ];

    protected array $_virtual = [];
}
