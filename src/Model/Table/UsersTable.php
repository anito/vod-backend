<?php
namespace App\Model\Table;

use Cake\Log\Log;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Security;
use Cake\Validation\Validator;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Core\Configure;
use Exception;
use Firebase\JWT\JWT;

/**
 * Users Model
 *
 * @property \App\Model\Table\GroupsTable&\Cake\ORM\Association\BelongsTo $Groups
 * @property \App\Model\Table\AvatarsTable&\Cake\ORM\Association\HasMany $Avatars
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
            'cascadeCallbacks' => true, // triggers core events on the foreign model (when also dependent set to ttue)
        ]);
        $this->hasOne('Tokens', [
            'foreignKey' => 'user_id',
            'dependent' => true,
            'cascadeCallbacks' => true, // triggers core events on the foreign model (when also dependent set to ttue)
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
    public function validationDefault(Validator $validator)
    {
        $validator
            ->scalar('id')
            ->allowEmptyString('id', null, 'create');

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
            ->dateTime('last_login')
            ->allowEmptyDateTime('last_login');

        $notAllowed = FIXTURE;
        $validator
            ->add('active', 'custom', [
                'rule' => function ($value, $context) use ($notAllowed) {
                    if (isset($context['data']['id'])) {
                        $id = $context['data']['id'];
                    } else {
                        return true;
                    }
                    $index = array_search($id, $notAllowed);
                    if (is_int($index)) {
                        return false;
                    }

                    return true;
                },
                'message' => __('This user is protected and cannot be changed'),
            ])
            ->add('name', 'custom', [
                'rule' => function ($value, $context) use ($notAllowed) {
                    if (isset($context['data']['id'])) {
                        $id = $context['data']['id'];
                    } else {
                        return true;
                    }
                    $index = array_search($id, $notAllowed);
                    if (is_int($index)) {
                        return false;
                    }

                    return true;
                },
                'message' => __('This user is protected and cannot be changed'),
            ])
            ->add('email', 'custom', [
                'rule' => function ($value, $context) use ($notAllowed) {
                    if (isset($context['data']['id'])) {
                        $id = $context['data']['id'];
                    } else {
                        return true;
                    }
                    $index = array_search($id, $notAllowed);
                    if (is_int($index)) {
                        return false;
                    }

                    return true;
                },
                'message' => __('This user is protected and cannot be changed'),
            ])
            ->add('group_id', 'custom', [
                'rule' => function ($value, $context) use ($notAllowed) {
                    if (isset($context['data']['id'])) {
                        $id = $context['data']['id'];
                    } else {
                        return true;
                    }
                    $index = array_search($id, $notAllowed);
                    if (is_int($index)) {
                        return false;
                    }


                    return true;
                },
                'message' => __('This user is protected and cannot be changed'),
            ])
            ->add('password', 'custom', [
                'rule' => function ($value, $context) use ($notAllowed) {

                    if (isset($context['data']['id'])) {
                        $id = $context['data']['id'];
                    } else {
                        return true;
                    }
                    $index = array_search($id, $notAllowed);
                    if (is_int($index)) {
                        return false;
                    }

                    return true;
                },
                'message' => __('This user is protected and cannot be changed'),
            ]);

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
        $rules->add($rules->isUnique(['email']));
        $rules->add($rules->existsIn(['group_id'], 'Groups'));

        return $rules;
    }

    public function findWithEmail(\Cake\ORM\Query $query, array $options)
    {
        $user = $this->getUser('email', $options['username']);
        $this->checkJWT($user);

        
        $query
            ->where(['Users.active' => 1]);
            
        return $query;
    }
    
    public function findWithId(\Cake\ORM\Query $query, array $options)
    {
        
        $user = $this->getUser('id', $options['username']);
        $this->checkJWT($user);

        $query
            ->where(['Users.active' => 1]);
            
        return $query;
    }

    protected function getUser($field, $value) {
        $_field = 'Users.' . $field;

        return $this
            ->find()
            ->contain(['Groups', 'Tokens'])
            ->where([$_field => $value])
            ->first();
    }

    protected function checkJWT($user) {
        if ($user->group->name !== "Administrator") {
            $allowed_algs = ['HS256'];
            $token = isset($user->token) ? $user->token->token : null;

            if (!$token) {

                throw new UnauthorizedException(__('Invalid username or password'));

            }

            try {
                JWT::decode(
                    $token,
                    Security::getSalt(),
                    $allowed_algs
                );
            } catch (Exception $e) {
                if (Configure::read('debug')) {
                    throw $e;
                }
            }
        }

    }

}
