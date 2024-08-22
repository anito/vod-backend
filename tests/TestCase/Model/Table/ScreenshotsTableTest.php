<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ScreenshotsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ScreenshotsTable Test Case
 */
class ScreenshotsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ScreenshotsTable
     */
    protected $Screenshots;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Screenshots',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Screenshots') ? [] : ['className' => ScreenshotsTable::class];
        $this->Screenshots = $this->getTableLocator()->get('Screenshots', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Screenshots);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ScreenshotsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\ScreenshotsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
