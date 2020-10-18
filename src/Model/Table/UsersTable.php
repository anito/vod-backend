<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Rule\IsUnique;
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
            ->email('email')
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
        // Add a rule for preventing to update specific users
        $rules->addUpdate(function ($entity, $options) {
            $notAllowed = [15, 23];
            if(in_array($entity->id, $notAllowed)) return false;
            return true;
        }, 'ruleName');



        return $rules;
    }
}
