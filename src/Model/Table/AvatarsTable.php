<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Filesystem\Folder;
use Cake\Log\Log;

/**
 * Avatars Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\Avatar get($primaryKey, $options = [])
 * @method \App\Model\Entity\Avatar newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Avatar[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Avatar|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Avatar saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Avatar patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Avatar[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Avatar findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AvatarsTable extends Table
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

        $this->setTable('avatars');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
    }

    public function beforeDelete($event, $entity, $options) {

        $id = $entity->id;
        $fn = $entity->src;

        $path = AVATARS . DS . $id;
        $lg_path = $path . DS . 'lg';

        $oldies = glob($lg_path . DS . $fn);

        if (!empty($oldies) && $oldies && !unlink($oldies[0])) {
            $event->stopPropagation();
        } else {
            $f = new Folder($path);
            $f->delete();
        }

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
            ->maxLength('id', 40)
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('src')
            ->maxLength('src', 100)
            ->requirePresence('src', 'create')
            ->notEmptyString('src');

        $validator
            ->integer('filesize')
            ->allowEmptyFile('filesize');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
