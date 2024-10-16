<?php

namespace App\Model\Entity;

use ArrayAccess;
use Authentication\IdentityInterface;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\Log\Log;
use Cake\ORM\Entity;
use Cake\Utility\Security;
use Firebase\JWT\JWT;

/**
 * User Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $password
 * @property bool|null $active
 * @property bool|null $protected
 * @property int|null $group_id
 * @property \Cake\I18n\DateTime|null $last_login
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Group $group
 * @property \App\Model\Entity\Avatar[] $avatars
 * @property \App\Model\Entity\Inbox[] $inboxes
 * @property \App\Model\Entity\Mail[] $sents
 * @property \App\Model\Entity\Token[] $tokens
 * @property \App\Model\Entity\Video[] $videos
 */
class User extends Entity implements IdentityInterface
{

  protected function _setPassword($password): ?string
  {
    if (strlen($password) > 0) {
      $hasher = new DefaultPasswordHasher();

      return $hasher->hash($password);
    }
  }
  /**
   * Fields that can be mass assigned using newEntity() or patchEntity().
   *
   * Note that when '*' is set to true, this allows all unspecified fields to
   * be mass assigned. For security purposes, it is advised to set '*' to false
   * (or remove it), and explicitly make individual fields accessible as needed.
   *
   * @var array
   */
  protected array $_accessible = [
    'id' => true,
    'name' => true,
    'email' => true,
    'password' => true,
    'active' => true,
    'protected' => true,
    'group_id' => true,
    'token_id' => false,
    'last_login' => false,
    'created' => false,
    'modified' => false,
    'modified_by' => false,
    'group' => true,
    'avatar' => true,
    'inboxes' => true,
    'sents' => true,
    'token' => true,
    'videos' => true,
  ];

  /**
   * Authentication\IdentityInterface method
   */
  public function getIdentifier(): array|string|int|null
  {
    return $this->id;
  }

  /**
   * Authentication\IdentityInterface method
   */
  public function getOriginalData(): ArrayAccess|array
  {
    return $this;
  }

  /**
   * Fields that are excluded from JSON versions of the entity.
   *
   * @var array
   */
  protected array $_hidden = [
    'password', 'token', 'created', 'modified', 'modified_by', 'last_login'
  ];

  protected array $_virtual = [
    'expires', 'jwt', 'token_id', 'role'
  ];

  protected function _getExpires()
  {
    if (isset($this->token)) {
      $jwt = $this->token->token;

      $tks = \explode('.', $jwt);
      list($headb64, $bodyb64, $cryptob64) = $tks;
      return JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64))->exp;
    }
  }

  protected function _getTokenId()
  {
    if (isset($this->token)) {
      return $this->token->id;
    }
  }

  protected function _getJwt()
  {
    if (isset($this->token)) {
      return $this->token->token;
    }
  }

  protected function _getRole()
  {
    if (isset($this->group)) {
      return $this->group->name;
    }
  }
}
