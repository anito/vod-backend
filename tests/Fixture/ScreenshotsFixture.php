<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ScreenshotsFixture
 */
class ScreenshotsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => '97de4610-eabe-47dc-ae9d-4d52f239e7a9',
                'src' => 'Lorem ipsum dolor sit amet',
                'link' => 'Lorem ipsum dolor sit amet',
                'filesize' => 1,
                'created' => '2024-08-22 21:14:14',
                'modified' => '2024-08-22 21:14:14',
            ],
        ];
        parent::init();
    }
}
