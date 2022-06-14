<?php

namespace App\Controller\Api;

use Cake\Cache\Cache;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Error\Debugger;
use Cake\Event\Event;
use Cake\Http\Client;
use Cake\Http\Session;
use Cake\I18n\I18n;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use Crud\Controller\ControllerTrait;
use Firebase\JWT\JWT;

class AppController extends Controller
{
	use ControllerTrait;

	public function initialize(): void
	{

		parent::initialize();
		$publ = null;
		if ($this->request->getQuery('login_type') === 'google') {
			$certs = $this->_getCert();

			// $keys = array_keys($certs);
			$publ = $certs['ca00620c5aa7be8cd03a6f3c68406e45e93b3cab'];
		};
		$salt = $publ ?: Security::getSalt();
		Cache::disable();

		$this->loadComponent('RequestHandler');

		$this->loadComponent('Authentication.Authentication');

		$cookies = $this->request->getCookieParams();
		$queries = $this->request->getQueryParams();

		if (isset($queries['locale'])) {
			$locale = $queries['locale'];
		} else if (isset($cookies['locale'])) {
			$locale = $cookies['locale'];
			Log::debug($locale);
		}
		if (isset($locale)) {
			I18n::setLocale($locale);
		};
		// I18n::setLocale('en-US');
		// I18n::setLocale('de-DE');
	}

	protected function _getCert()
	{
		$http = new Client();
		$response = $http->get('https://www.googleapis.com/oauth2/v1/certs');
		$json = $response->getJson();
		return $json;
	}

	protected function _getJWTPayload($jwt)
	{
		$tks = \explode('.', $jwt);
		list($headb64, $bodyb64, $cryptob64) = $tks;
		return JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
	}

	protected function _createToken($id, $exp = null)
	{
		if (!isset($exp)) {
			$expires = time() + Configure::read('Token.lifetime');
		} else {
			$expires = $exp;
		}
		return JWT::encode(
			[
				'sub' => $id,
				'exp' => $expires,
			],
			Security::getSalt()
		);
	}

	protected function _getAuthUser($key = null)
	{
		$result = $this->Authentication->getResult();
		if ($result->isValid()) {
			$uid = $this->Authentication->getIdentity()->getIdentifier();
		}

		if (!isset($uid)) {
			return;
		}

		$user = $this->_getUser($uid);

		if ($key === null) {
			return $user;
		} else {
			return $user[$key];
		}
	}

	protected function _getUser($id, array $config = [])
	{
		$defaults = [
			'contain' => ['Groups', 'Avatars', 'Videos', 'Tokens'],
		];
		$options = array_merge($defaults, $config);
		if (isset($id['sub'])) {
			$id = $id['sub'];
		}

		if (is_array($id)) {
			return $id;
		} else {
			return TableRegistry::getTableLocator()->get('Users')
				->find()
				->contain($options['contain'])
				->where(['Users.id' => $id])
				->first()
				->toArray();
		}
	}

	protected function _isAdmin($user)
	{
		return $this->_getUserRoleName($user) === 'Administrator';
	}

	protected function _getAdmins()
	{
		$table = TableRegistry::getTableLocator()->get('Users');
		$res = $table->find()
			->contain(['Groups'])
			->where(['Users.group_id' => $this->_getRoleIdFromName('Administrator')])
			->toList();
		return $res;
	}

	protected function _getUserRoleName(array $user = [])
	{
		if (isset($user['sub'])) {
			$user = $this->_getUser($user['sub']);
		}
		return TableRegistry::getTableLocator()->get('Groups')
			->find()
			->where(['id' => $user['group_id']])
			->select(['name'])
			->first()
			->toArray()['name'];
	}

	protected function _getRoleIdFromName($rolename = 'User')
	{

		return TableRegistry::getTableLocator()->get('Groups')
			->find()
			->where(['Groups.name' => $rolename])
			->select(['id'])
			->first()
			->toArray()['id'];
	}

	protected function _getGroups()
	{
		$groups = TableRegistry::getTableLocator()->get('Groups');

		$_groups = [];
		$data = $groups->find('all')
			->toArray();

		foreach ($data as $group) {
			$_groups[] = array('name' => $group->name, 'id' => $group->id);
		}
		return $_groups;
	}

	protected function _checkValidationErrors(Event $event)
	{
		if ($event->getSubject()->entity->hasErrors()) {
			$errors = $event->getSubject()->entity->getErrors();

			if (!empty($errors)) {
				$field = array_key_first($errors);
				if (isset($errors[$field])) {
					$rule = array_key_first($errors[$field]);
					$error = $errors[$field][$rule];
					return __($error);
				}
			}
		}
	}
}
