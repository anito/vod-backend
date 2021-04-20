<?php
namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EmailTemplates Model
 *
 * @property \App\Model\Table\TemplatesTable&\Cake\ORM\Association\BelongsTo $Templates
 *
 * @method \App\Model\Entity\EmailTemplate get($primaryKey, $options = [])
 * @method \App\Model\Entity\EmailTemplate newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\EmailTemplate[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EmailTemplate|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EmailTemplate saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EmailTemplate patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EmailTemplate[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\EmailTemplate findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EmailTemplatesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('email_templates');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Templates', [
            'foreignKey' => 'template_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', null, 'create')
            ->add('id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('name')
            ->maxLength('name', 50)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('subject')
            ->allowEmptyString('subject');

        $validator
            ->scalar('before_content')
            ->allowEmptyString('before_content');

        $validator
            ->scalar('content')
            ->allowEmptyString('content');

        $validator
            ->scalar('after_content')
            ->allowEmptyString('after_content');

        $validator
            ->scalar('before_sitename')
            ->maxLength('before_sitename', 50)
            ->allowEmptyString('before_sitename');

        $validator
            ->scalar('sitename')
            ->maxLength('sitename', 50)
            ->allowEmptyString('sitename');

        $validator
            ->scalar('after_sitename')
            ->maxLength('after_sitename', 50)
            ->allowEmptyString('after_sitename');

        $validator
            ->scalar('before_footer')
            ->allowEmptyString('before_footer');

        $validator
            ->scalar('footer')
            ->allowEmptyString('footer');

        $validator
            ->scalar('after_footer')
            ->allowEmptyString('after_footer');

        $validator
            ->boolean('protected')
            ->notEmptyString('protected');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['id']));
        $rules->add($rules->existsIn(['template_id'], 'Templates'));

        return $rules;
    }
}
