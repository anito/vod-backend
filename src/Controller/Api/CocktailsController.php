<?php
namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Network\Exception\UnauthorizedException;

class CocktailsController extends AppController
{

    public function initialize() {
        parent::initialize();
    }

    public $paginate = [
        'page' => 1,
        'limit' => 50,
        'maxLimit' => 150,
        'sortWhitelist' => [
            'id', 'name'
        ]
    ];
}