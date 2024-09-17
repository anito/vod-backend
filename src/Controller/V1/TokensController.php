<?php

namespace App\Controller\Api\V1;

use App\Controller\Api\V1\AppController;
use Cake\Core\App;
use Cake\Event\Event;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;

/**
 * Tokens Controller
 *
 *
 * @method \App\Model\Entity\Token[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TokensController extends AppController
{

  public function initialize(): void
  {
    parent::initialize();
    $this->Authentication->allowUnauthenticated([]);

    $this->loadComponent('Crud.Crud', [
      'actions' => [
        'Crud.Index',
        'Crud.View',
        'Crud.Add',
        'Crud.Edit',
        'Crud.Delete',
      ],
      'listeners' => [
        'Crud.Api',
        'Crud.ApiPagination',
      ],
    ]);

    $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');
  }

  /**
   * There is no such thing like altering a token with PUT (PUT contains no body to hold our data)
   * So when regenerating a token we actually have to
   * add a new token and remove the old one (that previously belonged to that user)
   **/
  public function add()
  {
    $uid = $this->getRequest()->getData('user_id');
    $constrained = $this->getRequest()->getData('constrained'); // should expire with latest video

    $this->Crud->on('beforeSave', function (Event $event) use ($uid, $constrained) {

      $entity = $event->getSubject()->entity;
      $timestamp = null;

      // get the latest video subscription to adjust the tokens expiration time
      $videosTable = TableRegistry::getTableLocator()->get('Videos');
      $latestVideo = $videosTable->find('latestVideo', uid: $uid)->first();

      if ($constrained && !empty($latestVideo)) {
        $end = $latestVideo->_matchingData['UsersVideos']->end;
        $end = $end->i18nFormat('yyyy-MM-dd HH:mm:ss');

        // tokens expiration time normally equals last's videos subscription time -
        // so in normal cases this (the token) would not change as long as the videos end date doesn't change
        // in case of tampered or stolen tokens however, we NEED to regenerate the token
        // solution here is to add a timespan (now - time ellapsed today)
        // which should be differ from second to second, hence gives us a new token different from the previous one
        $startOfDayTimestamp = (new DateTime())->setTime(0, 0, 0)->getTimestamp();
        $nowTimestamp = (new DateTime())->getTimestamp();
        $diff = $nowTimestamp - $startOfDayTimestamp;

        $timestamp = strtotime($end) + $diff;
      }

      $jwt = $this->_createToken($uid, $timestamp);
      $this->Tokens->patchEntity($entity, ['token' => $jwt]);

      // since we must use POST (PUT contains no body to hold our data)
      // and eventhough we might only want to update the token,
      // we now have to remove all older tokens belonging to the same user
      $this->Tokens->rebuild($uid, $jwt);
    });

    // normally we would return the newly created token
    // but in our case need the updated user to be sent back to the client
    $this->Crud->on('afterSave', function (Event $event) use ($uid) {

      $usersTable = TableRegistry::getTableLocator()->get('Users');

      $user = $usersTable->get($uid, contain: ['Groups', 'Videos', 'Avatars', 'Tokens']);

      $this->set([
        'data' => $user,
        'message' => __('Token created'),
      ]);
      $this->Crud->action()->serialize(['data', 'message']);
    });

    return $this->Crud->execute();
  }

  public function delete($id)
  {

    $this->Crud->on('afterDelete', function (Event $event) {

      // return the full stacked user
      $uid = $event->getSubject()->entity->user_id;

      $usersTable = TableRegistry::getTableLocator()->get('Users');
      $user = $usersTable->get($uid, contain: ['Groups', 'Videos', 'Avatars', 'Tokens']);

      $this->set([
        'data' => $user,
        'message' => __('Token removed'),
      ]);
      $this->Crud->action()->serialize(['data', 'message']);
    });

    return $this->Crud->execute();
  }
}
