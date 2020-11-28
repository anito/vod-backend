<?php
namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Log\Log;

/**
 * Users Model
 *
 * @property \App\Model\Table\GroupsTable&\Cake\ORM\Association\BelongsTo $Groups
 * @property \App\Model\Table\ImagesTable&\Cake\ORM\Association\HasMany $Images
 * @property \App\Model\Table\VideosTable&\Cake\ORM\Association\HasMany $Videos
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

        $this->addBehavior('Timestamp', [
            'events' => [
                'Users.login' => [
                    'last_login' => 'always',
                ],
            ],
        ]);

        $this->belongsTo('Groups', [
            'foreignKey' => 'group_id',
        ]);

        $this->hasMany('Avatars', [
            'foreignKey' => 'user_id',
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
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->email('email', null, 'Not a valid email address')
            ->notEmptyString('email');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->minLength('password', 8)
            ->notEmptyString('password');

        $validator
            ->boolean('active')
            ->allowEmptyString('active');

        $validator
            ->dateTime('last_login')
            ->allowEmptyDateTime('last_login');

        
        $notAllowed = FIXTURE;
        $validator
            ->add('name', 'custom', [
                'rule' => function ($value, $context) use ($notAllowed) {
                    if (isset($context['data']['id'])) {
                        $id = $context['data']['id'];
                    } else {
                        return true;
                    }
                    $name = $context['data']['name'];
                    $index = array_search($id, array_column($notAllowed, 'id'));
                    if (is_int($index)) {
                        $validName = $notAllowed[$index]['name'] === $name;
                        if(!$validName) return false;
                    }

                    return true;
                },
                'message' => __('This name is protected and cannot be changed'),
            ])
            ->add('email', 'custom', [
                'rule' => function ($value, $context) use ($notAllowed) {
                    if(isset($context['data']['id'])) {
                        $id = $context['data']['id'];
                    } else {
                        return true;
                    }
                    $email = $context['data']['email'];
                    $index = array_search($id, array_column($notAllowed, 'id'));
                    if (is_int($index)) {
                        $validEmail = $notAllowed[$index]['email'] === $email;
                        if(!$validEmail) return false;
                    }

                    return true;
                },
                'message' => __('This email is protected and cannot be changed'),
            ])
            ->add('group_id', 'custom', [
                'rule' => function ($value, $context) use ($notAllowed) {
                    if(isset($context['data']['id'])) {
                        $id = $context['data']['id'];
                    } else {
                        return true;
                    }
                    $group_id = $context['data']['group_id'];
                    $index = array_search($id, array_column($notAllowed, 'id'));
                    if (is_int($index)) {
                        $validGroupId = $notAllowed[$index]['email'] === $group_id;
                        if(!$validGroupId) return false;
                    }

                    return true;
                },
                'message' => __('This role is protected and cannot be changed'),
            ])
            ->add('password', 'custom', [
                'rule' => function ($value, $context) use ($notAllowed) {
                    
                    if (isset($context['data']['id'])) {
                        $id = $context['data']['id'];
                    } else {
                        return true;
                    }
                    $index = array_search($id, array_column($notAllowed, 'id'));
                    if (is_int($index)) return false;
                    return true;
                },
                'message' => __('This password is protected and cannot be changed'),
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
        // $rules->add(new IsUnique(['email']), __('This email already exists'));
        $rules->add($rules->isUnique(['email'], __('This email already exists')));
        $rules->add($rules->existsIn(['group_id'], 'Groups'));

        return $rules;
    }
}