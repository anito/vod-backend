<?php

namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\Error\Debugger;
use Cake\Event\Event;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Log\Log;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Security;
use Cake\Validation\Validator;
use Exception;
use Firebase\JWT\JWT;

/**
 * Users Model
 *
 * @property \App\Model\Table\GroupsTable&\Cake\ORM\Association\BelongsTo $Groups
 * @property \App\Model\Table\AvatarsTable&\Cake\ORM\Association\HasMany $Avatars
 * @property \App\Model\Table\InboxesTable&\Cake\ORM\Association\HasMany $Inboxes
 * @property \App\Model\Table\SentsTable&\Cake\ORM\Association\HasMany $Sents
 * @property \App\Model\Table\TokensTable&\Cake\ORM\Association\HasOne $Tokens
 * @property \App\Model\Table\VideosTable&\Cake\ORM\Association\BelongsToMany $Videos
 *
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
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

    $this->setTable('users');
    $this->setDisplayField('name');
    $this->setPrimaryKey('id');

    $this->addBehavior('Timestamp');
    $this->addBehavior('Muffin/Footprint.Footprint');

    $this->protectedUserMessage = __('This user is protected');

    $this->belongsTo('Groups', [
      'foreignKey' => 'group_id',
    ]);
    $this->hasOne('Avatars', [
      'foreignKey' => 'user_id',
      'dependent' => true,
      'cascadeCallbacks' => true, // triggers core events on the foreign model (when also dependent set to true)
    ]);
    $this->hasMany('Inboxes', [
      'foreignKey' => 'user_id',
      'dependent' => true,
      'cascadeCallbacks' => true, // triggers core events on the foreign model (when also dependent set to true)
    ]);
    $this->hasMany('Sents', [
      'foreignKey' => 'user_id',
      'dependent' => true,
      'cascadeCallbacks' => true, // triggers core events on the foreign model (when also dependent set to true)
    ]);
    $this->hasOne('Tokens', [
      'foreignKey' => 'user_id',
      'dependent' => true,
      'cascadeCallbacks' => true, // triggers core events on the foreign model (when also dependent set to true)
    ]);
    $this->belongsToMany('Videos', [
      'foreignKey' => 'user_id',
      'targetForeignKey' => 'video_id',
      'joinTable' => 'users_videos',
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
      // dont use uuid validation as long as there are still any scalar user ids
      // ->uuid('id', 'No valid UUID')
      ->scalar('id')
      ->allowEmptyString('id', null, 'create')
      ->add('id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

    $validator
      ->scalar('name')
      ->maxLength('name', 255)
      ->notEmptyString('name', __('Name can not be empty'));

    $validator
      ->email('email')
      ->notEmptyString('email', __('Email can not be empty'));

    $validator
      ->scalar('password')
      ->maxLength('password', 255)
      ->allowEmptyString('password');

    $validator
      ->boolean('active')
      ->allowEmptyString('active');

    $validator
      ->boolean('protected')
      ->allowEmptyString('protected');

    $validator
      ->dateTime('last_login')
      ->allowEmptyDateTime('last_login');

    // $validator
    //   ->add('active', '_protected', [
    //     'rule' => [$this, 'protected'],
    //     'message' => $this->protectedUserMessage,
    //   ])
    //   ->add('name', '_protected', [
    //     'rule' => [$this, 'protected'],
    //     'message' => $this->protectedUserMessage,
    //   ])
    //   ->add('email', '_protected', [
    //     'rule' => [$this, 'protected'],
    //     'message' => $this->protectedUserMessage,
    //   ])
    //   ->add('group_id', '_protected', [
    //     'rule' => [$this, 'protected'],
    //     'message' => $this->protectedUserMessage,
    //   ])
    //   ->add('password', '_protected', [
    //     'rule' => [$this, 'protected'],
    //     'message' => $this->protectedUserMessage,
    //   ]);

    return $validator;
  }

  public function beforeSave(EventInterface $event, EntityInterface $entity, $options)
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

      if ($entity->isNew()) {
        $event = new Event('User.registration', $this, ['user' => $entity]);
        $this->getEventManager()->dispatch($event);
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

  public function beforeDelete(EventInterface $event, EntityInterface $entity, $options)
  {
    if ($entity->protected) {
      throw new ForbiddenException(__('This user is protected'));
    }
  }

  /**
   * @deprecated use protected check in beforeSave instead
   */
  public function protected($value, $context)
  {
    if (isset($context['data']['id'])) {
      $id = $context['data']['id'];
      if (in_array($id, FIXTURE)) {
        return false;
      }
    }
    return true;
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
    $rules->add($rules->isUnique(['email']));
    $rules->add($rules->isUnique(['id']));
    $rules->add($rules->existsIn(['group_id'], 'Groups'));

    return $rules;
  }

  public function findActive(Query $query)
  {
    return $query
      ->where(['Users.active' => 1]);
  }

  public function findSuperusersEmail(Query $query)
  {
    return $query
      ->matching('Groups', function (Query $q) {
        $condition = [
          'Groups.name' => SUPERUSER
        ];

        return $q
          ->where($condition);
      })
      ->select(['Users.email']);
  }

  protected function _getUser($field, $value)
  {
    $_field = 'Users.' . $field;

    return $this
      ->find()
      ->contain(['Groups', 'Tokens'])
      ->where([$_field => $value])
      ->first();
  }

  protected static function checkJWT(Entity $user)
  {
    if (!$user instanceof Entity) {
      return;
    }

    $token = isset($user->token) ? $user->token->token : null;

    if (!$token) {
      throw new UnauthorizedException(__('Invalid token'));
    }

    // Token found in database, check it's validity
    $allowed_algs = ['HS256'];
    try {
      JWT::decode(
        $token,
        Security::getSalt(),
        $allowed_algs
      );
    } catch (Exception $e) {
      throw new UnauthorizedException(__('Invalid token'));
      throw $e;
    }
  }
}
