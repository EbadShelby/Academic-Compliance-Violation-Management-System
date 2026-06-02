<?php

/**
 * Password Reset Model
 *
 * Handles database operations for password reset tokens.
 */

class PasswordReset extends Model
{
    protected string $table = 'password_resets';

    /**
     * Create a new password reset token for an email.
     * Generates a random token, hashes it for DB storage, and returns the raw token.
     * Deletes any existing tokens for that email first.
     *
     * @param string $email
     * @return string Raw token to send in email
     * @throws Exception
     */
    public function createToken(string $email): string
    {
        // Remove old tokens for this email
        $this->deleteByEmail($email);

        // Generate a secure random 32-byte (64 hex char) token
        $rawToken = bin2hex(random_bytes(32));
        
        // Hash it for DB storage (prevents token leakage if DB is compromised)
        $hashedToken = hash('sha256', $rawToken);

        // Token expires in 1 hour
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $this->insert([
            'email'      => strtolower(trim($email)),
            'token'      => $hashedToken,
            'expires_at' => $expiresAt
        ]);

        return $rawToken;
    }

    /**
     * Verify if a token is valid for a given email.
     *
     * @param string $email
     * @param string $rawToken
     * @return bool
     */
    public function isValid(string $email, string $rawToken): bool
    {
        $hashedToken = hash('sha256', $rawToken);
        
        $sql = "SELECT * FROM `password_resets` 
                WHERE email = :email 
                AND token = :token 
                AND expires_at > NOW() 
                LIMIT 1";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email' => strtolower(trim($email)),
            ':token' => $hashedToken
        ]);
        
        return $stmt->fetch() !== false;
    }

    /**
     * Delete tokens for a specific email.
     *
     * @param string $email
     * @return bool
     */
    public function deleteByEmail(string $email): bool
    {
        $sql = "DELETE FROM `password_resets` WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':email' => strtolower(trim($email))]);
    }
    
    /**
     * Clean up expired tokens (can be called periodically).
     */
    public function cleanupExpired(): void
    {
        $sql = "DELETE FROM `password_resets` WHERE expires_at < NOW()";
        $this->db->query($sql);
    }
}
