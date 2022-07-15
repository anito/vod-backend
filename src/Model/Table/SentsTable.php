<?php

namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Sents Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\Sent get($primaryKey, $options = [])
 * @method \App\Model\Entity\Sent newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Sent[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Sent|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Sent saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Sent patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Sent[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Sent findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SentsTable extends Table
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

    $this->setTable('sents');
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
      ->scalar('_to')
      ->maxLength('_to', 256)
      ->requirePresence('_to', 'create')
      ->notEmptyString('_to');

    $validator
      ->scalar('_from')
      ->maxLength('_from', 256)
      ->requirePresence('_from', 'create')
      ->notEmptyString('_from');

    $validator
      ->requirePresence('message', 'create')
      ->notEmptyString('message');

    return $validator;
  }

  public function beforeSave_(EventInterface $event, EntityInterface $entity, $options)
  {
    $authUser = $options['_footprint'];
    if (isset($authUser)) {
      // prevent users from deactivating their own profile
      $authId = $authUser->id;
      $userId = $entity->id;
      $active = $entity->active;
      if ($authId === $userId && !$active) {
        throw new ForbiddenException(__('You can not deactivate your own profile'));
      }

      // only Superusers can edit protected users
      if ($entity->protected) {
        $query = $this->find()
          ->matching('Groups', function ($q) {
            return $q->where([
              'Groups.name' => 'Superuser',
            ]);
          })
          ->where(['Users.id' => $authId])
          ->toArray();
        if (empty($query) && ($userId !== $authId)) {
          throw new UnauthorizedException(__('Unauthorized'));
        } else {
          return;
        }
      }
    }
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
          'Sents.user_id' => $options['field'],
          'Sents._from' => $options['field']
        ],
      ])
      ->toArray();

    return $query;
  }
}
