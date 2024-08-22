<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Screenshots Model
 *
 * @method \App\Model\Entity\Screenshot newEmptyEntity()
 * @method \App\Model\Entity\Screenshot newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Screenshot> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Screenshot get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Screenshot findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Screenshot patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Screenshot> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Screenshot|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Screenshot saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Screenshot>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Screenshot>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Screenshot>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Screenshot> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Screenshot>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Screenshot>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Screenshot>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Screenshot> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ScreenshotsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('screenshots');
        $this->setDisplayField('src');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
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
            ->scalar('src')
            ->maxLength('src', 512)
            ->requirePresence('src', 'create')
            ->notEmptyString('src');

        $validator
            ->scalar('link')
            ->maxLength('link', 255)
            ->requirePresence('link', 'create')
            ->notEmptyString('link');

        $validator
            ->integer('filesize')
            ->allowEmptyString('filesize');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['id']), ['errorField' => 'id']);

        return $rules;
    }
}
