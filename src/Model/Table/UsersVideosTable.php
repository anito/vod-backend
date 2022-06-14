<?php

declare(strict_types=1);

namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UsersVideos Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\VideosTable&\Cake\ORM\Association\BelongsTo $Videos
 *
 * @method \App\Model\Entity\UsersVideo newEmptyEntity()
 * @method \App\Model\Entity\UsersVideo newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\UsersVideo[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UsersVideo get($primaryKey, $options = [])
 * @method \App\Model\Entity\UsersVideo findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\UsersVideo patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UsersVideo[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UsersVideo|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UsersVideo saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UsersVideo[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UsersVideo[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\UsersVideo[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UsersVideo[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class UsersVideosTable extends Table
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

    $this->setTable('users_videos');
    $this->setDisplayField('id');
    $this->setPrimaryKey('id');

    // $this->belongsTo('Users');
    // $this->belongsTo('Videos');

    $this->belongsTo('Users', [
      'foreignKey' => 'user_id',
      'joinType' => 'INNER',
    ]);
    $this->belongsTo('Videos', [
      'foreignKey' => 'video_id',
      'joinType' => 'INNER',
    ]);
  }

  /**
   * Default validation rules.
   *
   * @param \Cake\Validation\Validator $validator Validator instance.
   * @return \Cake\Validation\Validator
   */
  public function validationDefault(Validator $validator): Validator
  {
    return $validator;

    $validator
      ->uuid('id')
      ->allowEmptyString('id', null, 'create');

    $validator
      ->dateTime('start')
      ->allowEmptyDateTime('start');

    $validator
      ->dateTime('end')
      ->allowEmptyDateTime('end');

    $validator
      ->numeric('playhead')
      ->allowEmptyString('playhead');

    $validator
      ->scalar('time_watched')
      ->maxLength('time_watched', 16777215)
      ->allowEmptyString('time_watched');

    return $validator;
  }

  public function beforeSave(EventInterface $event, EntityInterface $entity, $options)
  {

    if (!empty($options['_footprint'])) {

      $group_id = $options['_footprint']['group_id'];
      if ($group_id !== 1) {

        // prevent non admins from editing their own timeframe
        $identical = $this->getIdenticalStartEnd($entity);
        if (empty($identical)) {
          throw new ForbiddenException(__('You can not edit this timeframe'));
        }

        // prevent non admins from adding new videos
        $isNew = $entity->isNew();
        if ($isNew) {
          throw new ForbiddenException(__('You can not add a video'));
        }
      }
    }
  }

  public function getIdenticalStartEnd(Entity $entity)
  {
    $id = $entity->get('id');
    if (!$id) return 1;

    $current = $entity->extractOriginal(['start', 'end', 'playhead']);
    return $this->find('startEnd', [
      'id' => $id,
      'changed' => $current
    ])->toArray();
  }

  public function findStartEnd(Query $query, $options)
  {
    return $query->where([
      'id ' => $options['id'],
      'start' => $options['changed']['start'],
      'end' => $options['changed']['end'],
    ]);
  }

  /**
   * Returns a rules checker object that will be used for validating
   * application integrity.
   *
   * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
   * @return \Cake\ORM\RulesChecker
   */
  public function buildRules(RulesChecker $rules): RulesChecker
  {
    $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
    $rules->add($rules->existsIn(['video_id'], 'Videos'), ['errorField' => 'video_id']);

    return $rules;
  }
}
