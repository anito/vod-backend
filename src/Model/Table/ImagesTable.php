<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Images Model
 *
 * @property \App\Model\Table\VideosTable&\Cake\ORM\Association\HasMany $Videos
 *
 * @method \App\Model\Entity\Image get($primaryKey, $options = [])
 * @method \App\Model\Entity\Image newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Image[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Image|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Image saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Image patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Image[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Image findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ImagesTable extends Table
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

        $this->setTable('images');
        $this->setDisplayField('src');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Videos', [
            'foreignKey' => 'image_id',
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
            ->maxLength('id', 36)
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('iso')
            ->maxLength('iso', 100)
            ->allowEmptyString('iso');

        $validator
            ->scalar('longitude')
            ->maxLength('longitude', 100)
            ->allowEmptyString('longitude');

        $validator
            ->scalar('aperture')
            ->maxLength('aperture', 100)
            ->allowEmptyString('aperture');

        $validator
            ->scalar('make')
            ->maxLength('make', 100)
            ->allowEmptyString('make');

        $validator
            ->scalar('model')
            ->maxLength('model', 100)
            ->allowEmptyString('model');

        $validator
            ->scalar('title')
            ->maxLength('title', 100)
            ->allowEmptyString('title');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->scalar('exposure')
            ->maxLength('exposure', 500)
            ->allowEmptyString('exposure');

        $validator
            ->dateTime('captured')
            ->allowEmptyDateTime('captured');

        $validator
            ->scalar('software')
            ->maxLength('software', 500)
            ->allowEmptyString('software');

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
}
