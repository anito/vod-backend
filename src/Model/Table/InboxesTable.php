<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Inboxes Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\Inbox get($primaryKey, $options = [])
 * @method \App\Model\Entity\Inbox newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Inbox[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Inbox|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Inbox saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Inbox patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Inbox[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Inbox findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class InboxesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('inboxes');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', null, 'create')
            ->add('id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('from')
            ->maxLength('from', 256)
            ->requirePresence('from', 'create')
            ->notEmptyString('from');

        $validator
            ->scalar('to')
            ->maxLength('to', 256)
            ->requirePresence('to', 'create')
            ->notEmptyString('to');

        $validator
            ->boolean('_read')
            ->requirePresence('_read', 'create')
            ->notEmptyString('read');

        $validator
            ->requirePresence('message', 'create')
            ->notEmptyString('message');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker
    {
        $rules->add($rules->isUnique(['id']));
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }

    public function findByIdOrEmail(Query $query, array $options)
    {
        $query
            ->where([
                'OR' => [
                    'Inboxes.user_id' => $options['field'],
                    'Inboxes.to' => $options['field'],
                ],
            ])
            ->toArray();

        return $query;
    }
}
