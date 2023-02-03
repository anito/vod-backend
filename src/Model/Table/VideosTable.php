<?php

declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Videos Model
 *
 * @property \App\Model\Table\ImagesTable&\Cake\ORM\Association\BelongsTo $Images
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsToMany $Users
 *
 * @method \App\Model\Entity\Video newEmptyEntity()
 * @method \App\Model\Entity\Video newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Video[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Video get($primaryKey, $options = [])
 * @method \App\Model\Entity\Video findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Video patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Video[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Video|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Video saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Video[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Video[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Video[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Video[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class VideosTable extends Table
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

    $this->setTable('videos');
    $this->setDisplayField('title');
    $this->setPrimaryKey('id');

    $this->addBehavior('Timestamp');

    $this->belongsTo('Images', [
      'foreignKey' => 'image_id',
    ]);
    $this->belongsToMany('Users', [
      'foreignKey' => 'video_id',
      'targetForeignKey' => 'user_id',
      'joinTable' => 'users_videos',
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
    $validator
      ->scalar('id')
      ->allowEmptyString('id', null, 'create')
      ->add('id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

    $validator
      ->scalar('title')
      ->maxLength('title', 128)
      ->allowEmptyString('title');

    $validator
      ->scalar('src')
      ->maxLength('src', 128)
      ->requirePresence('src', 'create')
      ->notEmptyString('src');

    $validator
      ->numeric('duration')
      ->allowEmptyString('duration');

    $validator
      ->integer('filesize')
      ->allowEmptyFile('filesize');

    $validator
      ->boolean('teaser')
      ->allowEmptyString('teaser');

    $validator
      ->numeric('playhead')
      ->allowEmptyString('playhead');

    $validator
      ->integer('sequence')
      ->notEmptyString('sequence');

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
    $rules->add($rules->existsIn(['image_id'], 'Images'));

    return $rules;
  }

  public function findWidthImages(Query $query, array $options)
  {
    $query = $this->find('all')
      ->contain('Images');

    return $query;
  }

  public function findLatestVideo(Query $query, $options)
  {

    $uid = $options['uid'];
    return $query
      ->matching('Users', function (Query $q) use ($uid) {
        $now = date('Y-m-d H:i:s');

        $condition = [
          'Users.id' => $uid,
          'UsersVideos.end >' => $now,
        ];

        return $q
          ->where($condition);
      })
      ->select([
        'UsersVideos.user_id',
        'UsersVideos.end',
      ])
      ->order([
        'UsersVideos.end' => 'DESC',
      ]);
  }
}
