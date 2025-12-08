<?php
/**
 * WebAuthn Credential Repository
 * 
 * Bridge between b1gMail database and web-auth/webauthn-lib
 * Implements PublicKeyCredentialSourceRepository interface
 */

namespace B1GMail\WebAuthn;

use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;

class CredentialRepository implements PublicKeyCredentialSourceRepository
{
    private $db;
    private $tablePrefix;
    
    public function __construct($db, $tablePrefix = 'bm60_')
    {
        $this->db = $db;
        $this->tablePrefix = $tablePrefix;
    }
    
    /**
     * Find credential source by credential ID
     * 
     * @param string $publicKeyCredentialId Base64url-encoded credential ID
     * @return PublicKeyCredentialSource|null
     */
    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        $result = $this->db->Query(
            "SELECT * FROM {$this->tablePrefix}webauthn_credentials WHERE credential_id = ?",
            $publicKeyCredentialId
        );
        
        if($row = $result->FetchArray(MYSQLI_ASSOC)) {
            return $this->rowToCredentialSource($row);
        }
        
        return null;
    }
    
    /**
     * Find all credentials for user
     * 
     * @param PublicKeyCredentialUserEntity $userEntity User entity
     * @return array Array of PublicKeyCredentialSource
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $userEntity): array
    {
        $userId = (int)$userEntity->getId();
        
        $result = $this->db->Query(
            "SELECT * FROM {$this->tablePrefix}webauthn_credentials WHERE user_id = ? AND is_active = 1",
            $userId
        );
        
        $credentials = [];
        while($row = $result->FetchArray(MYSQLI_ASSOC)) {
            $credentials[] = $this->rowToCredentialSource($row);
        }
        
        return $credentials;
    }
    
    /**
     * Save credential source (create or update)
     * 
     * @param PublicKeyCredentialSource $credentialSource Credential source
     */
    public function saveCredentialSource(PublicKeyCredentialSource $credentialSource): void
    {
        $userId = (int)$credentialSource->getUserHandle();
        $credentialId = $credentialSource->getPublicKeyCredentialId();
        
        // Check if exists
        $result = $this->db->Query(
            "SELECT id FROM {$this->tablePrefix}webauthn_credentials WHERE credential_id = ?",
            base64_encode($credentialId)
        );
        
        if($result->RowCount() > 0) {
            // Update existing
            $this->db->Query(
                "UPDATE {$this->tablePrefix}webauthn_credentials 
                 SET counter = ?, public_key = ?, last_used_at = NOW()
                 WHERE credential_id = ?",
                $credentialSource->getCounter(),
                base64_encode($credentialSource->getCredentialPublicKey()),
                base64_encode($credentialId)
            );
        } else {
            // Insert new
            $this->db->Query(
                "INSERT INTO {$this->tablePrefix}webauthn_credentials 
                 (user_id, credential_id, public_key, counter, aaguid, created_at, is_active)
                 VALUES (?, ?, ?, ?, ?, NOW(), 1)",
                $userId,
                base64_encode($credentialId),
                base64_encode($credentialSource->getCredentialPublicKey()),
                $credentialSource->getCounter(),
                base64_encode($credentialSource->getAaguid())
            );
        }
    }
    
    /**
     * Convert database row to PublicKeyCredentialSource
     * 
     * @param array $row Database row
     * @return PublicKeyCredentialSource
     */
    private function rowToCredentialSource(array $row): PublicKeyCredentialSource
    {
        return new PublicKeyCredentialSource(
            base64_decode($row['credential_id']),
            'public-key',
            [],
            'none',
            null,
            base64_decode($row['public_key']),
            (string)$row['user_id'],
            (int)$row['counter']
        );
    }
    
    /**
     * Delete credential
     * 
     * @param string $credentialId Base64-encoded credential ID
     * @return bool Success
     */
    public function deleteCredential(string $credentialId): bool
    {
        $this->db->Query(
            "DELETE FROM {$this->tablePrefix}webauthn_credentials WHERE credential_id = ?",
            $credentialId
        );
        
        return ($this->db->AffectedRows() > 0);
    }
    
    /**
     * Deactivate credential (soft delete)
     * 
     * @param string $credentialId Base64-encoded credential ID
     * @return bool Success
     */
    public function deactivateCredential(string $credentialId): bool
    {
        $this->db->Query(
            "UPDATE {$this->tablePrefix}webauthn_credentials SET is_active = 0 WHERE credential_id = ?",
            $credentialId
        );
        
        return ($this->db->AffectedRows() > 0);
    }
}
