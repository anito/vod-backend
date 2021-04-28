<?php
namespace App\Model\Table;

use Cake\Http\Exception\UnauthorizedException;
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
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

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

        $this->notAllowedMessage = __('This user is protected and cannot be changed');
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
        // dont use uuid validation as long as there are still any scalar user ids
        // ->uuid('id', 'No valid UUID')
        ->scalar('id')
            ->allowEmptyString('id', null, 'create')
            ->add('id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->email('email')
            ->allowEmptyString('email');

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

        $validator
            ->add('active', 'checkProtected', [
                'rule' => [$this, 'protectedUser'],
                'message' => $this->notAllowedMessage,
            ])
            ->add('name', 'checkProtected', [
                'rule' => [$this, 'protectedUser'],
                'message' => $this->notAllowedMessage,
            ])
            ->add('email', 'checkProtected', [
                'rule' => [$this, 'protectedUser'],
                'message' => $this->notAllowedMessage,
            ])
            ->add('group_id', 'checkProtected', [
                'rule' => [$this, 'protectedUser'],
                'message' => $this->notAllowedMessage,
            ])
            ->add('password', 'checkProtected', [
                'rule' => [$this, 'protectedUser'],
                'message' => $this->notAllowedMessage,
            ]);

        return $validator;
    }

    public function protectedUser($value, $context)
    {
        if (isset($context['data']['id'])) {
            $id = $context['data']['id'];
        } else {
            return true;
        }
        $index = array_search($id, FIXTURE);
        if (is_int($index)) {
            return __('Fields are protected and cannot be changed');
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
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['email']));
        $rules->add($rules->isUnique(['id']));
        $rules->add($rules->existsIn(['group_id'], 'Groups'));

        return $rules;
    }

    public function findWithEmail(Query $query, array $options)
    {
        $user = $this->_getUser('email', $options['username']);
        $user && $this->checkJWT($user);

        $query
            ->where(['Users.active' => 1]);

        return $query;
    }

    public function findWithId(Query $query, array $options)
    {
        $user = $this->_getUser('id', $options['username']);
        $user && $this->checkJWT($user);

        $query
            ->where(['Users.active' => 1]);

        return $query;
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

    protected function checkJWT(Entity $user)
    {
        if (!$user instanceof Entity) {
            return;
        }
        if ($user->group->name !== "Administrator") {

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
                // throw new UnauthorizedException(__('Invalid token'));
                throw $e;
            }
        }
    }
}
