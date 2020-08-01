<?php
namespace App\Model\Table;

use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use Cake\Routing\Router;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Log\Log;
use Cake\ORM\Rule\IsUnique;

/**
 * Mysql Model
 *
 * @method \App\Model\Entity\Mysql get($primaryKey, $options = [])
 * @method \App\Model\Entity\Mysql newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Mysql[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Mysql|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Mysql saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Mysql patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Mysql[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Mysql findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MysqlTable extends Table
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

        $this->setTable('mysql');
        $this->setDisplayField('filename');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->request = Router::getRequest();
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
            ->integer('id');

        $validator
            ->scalar('filename')
            ->maxLength('filename', 255)
            ->requirePresence('filename', 'create')
            ->notEmpty('filename');

        $validator
            ->scalar('description')
            ->maxLength('description', 255)
            ->requirePresence('description', 'create')
            ->notEmpty('description');

        return $validator;
    }

    function beforeSave( $event ) {

        $request = Router::getRequest();
        // Log::write('debug', $request->is('ajax'));
        // Log::write('debug', 'request:');
        // Log::write('debug', $request);

        if(!$request->is('ajax')) {
            // return false;
        }
        if(!empty($request['pass'])) {
            $action = $request['pass'][0];
            // Log::write('debug', '$action');
            // Log::write('debug', $action);

            // if(!empty($request['pass'])) {
            //     $action = $request['pass'][0]; // for routes with joker like /api/mysql/mysql/*
            // }
            $response = mysql( $action ); // $action == dump

            if( !$response['success']) {
                return false;
            }

            $filename = $response['filename'];
            $data = $event->getData();
            $data['entity']->filename = $filename;
            $event->setData('entity', $data );
        }
        return true;
    }

    function afterSave( $event ) {
        $count = $this->find()->count();
        $countToDelete = $count - ( MAX_DUMPS > $count ? $count : ( MAX_DUMPS < 0 ? 0 : MAX_DUMPS ));

        $query = $this->find()
            ->select(['id', 'filename', 'created'])
            ->order(['created' => 'ASC'])
            ->limit($countToDelete);

        foreach( $query as $this->entity) {
            $this->delete($this->entity);
        }
        // $this->deleteAll(null);
    }

    function beforeDelete( $event ) {
        $data = $event->getData();
        $deleted = rm_file($data['entity']->filename);
        return $deleted;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules) {
        $rules->add(new IsUnique(['filename']), 'uniqueFilename');
        return $rules;
    }
}
