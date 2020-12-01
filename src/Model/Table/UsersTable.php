<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

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
            ->scalar('username')
            ->maxLength('username', 50)
            ->allowEmptyString('username');

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
}
