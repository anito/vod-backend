<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * EmailTemplate Entity
 *
 * @property string $id
 * @property string $template_id
 * @property string $name
 * @property string|null $subject
 * @property string|null $before_content
 * @property string|null $content
 * @property string|null $after_content
 * @property string|null $before_sitename
 * @property string|null $sitename
 * @property string|null $after_sitename
 * @property string|null $before_footer
 * @property string|null $footer
 * @property string|null $after_footer
 * @property bool $protected
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Template $template
 */
class EmailTemplate extends Entity
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
        'template_id' => false,
        'name' => true,
        'subject' => true,
        'before_content' => true,
        'content' => true,
        'after_content' => true,
        'before_sitename' => true,
        'sitename' => true,
        'after_sitename' => true,
        'before_footer' => true,
        'footer' => true,
        'after_footer' => true,
        'protected' => true,
        'created' => true,
        'modified' => true,
        'template' => true,
    ];

    protected $_virtual = [
        'slug',
    ];

    protected function _getSlug()
    {
        return $this->template->slug;
    }
}
