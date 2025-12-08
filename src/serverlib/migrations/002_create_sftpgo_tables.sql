-- ============================================================================
-- SFTPGo Database Schema Creation
-- Creates all required tables for SFTPGo in b1gMail database
-- ============================================================================

USE b1gmail;

-- Schema Version Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_schema_version (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO bm60_sftpgo_schema_version (version) VALUES (29) ON DUPLICATE KEY UPDATE version=29;

-- Users Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255),
    public_keys TEXT,
    home_dir VARCHAR(512),
    uid INT DEFAULT 0,
    gid INT DEFAULT 0,
    max_sessions INT DEFAULT 0,
    quota_size BIGINT DEFAULT 0,
    quota_files INT DEFAULT 0,
    permissions TEXT,
    used_quota_size BIGINT DEFAULT 0,
    used_quota_files INT DEFAULT 0,
    last_quota_update BIGINT DEFAULT 0,
    upload_bandwidth INT DEFAULT 0,
    download_bandwidth INT DEFAULT 0,
    expiration_date BIGINT DEFAULT 0,
    last_login BIGINT DEFAULT 0,
    status INT DEFAULT 1,
    filters TEXT,
    filesystem TEXT,
    additional_info TEXT,
    description VARCHAR(512),
    email VARCHAR(255),
    created_at BIGINT DEFAULT 0,
    updated_at BIGINT DEFAULT 0,
    INDEX idx_username (username),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Folders Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_folders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    path VARCHAR(512),
    used_quota_size BIGINT DEFAULT 0,
    used_quota_files INT DEFAULT 0,
    last_quota_update BIGINT DEFAULT 0,
    description VARCHAR(512),
    filesystem TEXT,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users Folders Mapping
CREATE TABLE IF NOT EXISTS bm60_sftpgo_users_folders_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    virtual_path VARCHAR(512) NOT NULL,
    quota_size BIGINT DEFAULT -1,
    quota_files INT DEFAULT -1,
    folder_id INT NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES bm60_sftpgo_users(id) ON DELETE CASCADE,
    FOREIGN KEY (folder_id) REFERENCES bm60_sftpgo_folders(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_folder (user_id, folder_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admins Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255),
    email VARCHAR(255),
    status INT DEFAULT 1,
    permissions TEXT,
    filters TEXT,
    additional_info TEXT,
    description VARCHAR(512),
    created_at BIGINT DEFAULT 0,
    updated_at BIGINT DEFAULT 0,
    last_login BIGINT DEFAULT 0,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Groups Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description VARCHAR(512),
    created_at BIGINT DEFAULT 0,
    updated_at BIGINT DEFAULT 0,
    user_settings TEXT,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Keys Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_id VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    api_key VARCHAR(255) NOT NULL,
    scope INT DEFAULT 0,
    created_at BIGINT DEFAULT 0,
    updated_at BIGINT DEFAULT 0,
    last_use_at BIGINT DEFAULT 0,
    expires_at BIGINT DEFAULT 0,
    description VARCHAR(512),
    admin_id INT,
    user_id INT,
    INDEX idx_key_id (key_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shares Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    share_id VARCHAR(60) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(512),
    scope INT DEFAULT 0,
    paths TEXT,
    created_at BIGINT DEFAULT 0,
    updated_at BIGINT DEFAULT 0,
    last_use_at BIGINT DEFAULT 0,
    expires_at BIGINT DEFAULT 0,
    password VARCHAR(255),
    max_tokens INT DEFAULT 0,
    used_tokens INT DEFAULT 0,
    allow_from TEXT,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES bm60_sftpgo_users(id) ON DELETE CASCADE,
    INDEX idx_share_id (share_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Defender Hosts Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_defender_hosts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(50) NOT NULL,
    ban_time BIGINT DEFAULT 0,
    updated_at BIGINT DEFAULT 0,
    UNIQUE KEY unique_ip (ip),
    INDEX idx_ban_time (ban_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Defender Events Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_defender_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_time BIGINT NOT NULL,
    score INT DEFAULT 0,
    host_id INT NOT NULL,
    FOREIGN KEY (host_id) REFERENCES bm60_sftpgo_defender_hosts(id) ON DELETE CASCADE,
    INDEX idx_date_time (date_time),
    INDEX idx_host_id (host_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Active Transfers Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_active_transfers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    connection_id VARCHAR(100) NOT NULL,
    transfer_id BIGINT NOT NULL,
    transfer_type INT DEFAULT 0,
    username VARCHAR(255) NOT NULL,
    folder_name VARCHAR(255),
    ip VARCHAR(50),
    truncated_size BIGINT DEFAULT 0,
    current_ul_size BIGINT DEFAULT 0,
    current_dl_size BIGINT DEFAULT 0,
    created_at BIGINT DEFAULT 0,
    updated_at BIGINT DEFAULT 0,
    UNIQUE KEY unique_connection_transfer (connection_id, transfer_id),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shared Sessions Table  
CREATE TABLE IF NOT EXISTS bm60_sftpgo_shared_sessions (
    `key` VARCHAR(128) PRIMARY KEY,
    data LONGBLOB NOT NULL,
    type INT NOT NULL,
    timestamp BIGINT NOT NULL,
    INDEX idx_timestamp (timestamp),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events Actions Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_events_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description VARCHAR(512),
    type INT NOT NULL,
    options TEXT,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events Rules Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_events_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    status INT DEFAULT 1,
    description VARCHAR(512),
    created_at BIGINT DEFAULT 0,
    updated_at BIGINT DEFAULT 0,
    `trigger` INT NOT NULL,
    conditions TEXT,
    deleted_at BIGINT DEFAULT 0,
    INDEX idx_name (name),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rules Actions Mapping Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_rules_actions_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rule_id INT NOT NULL,
    action_id INT NOT NULL,
    `order` INT DEFAULT 1,
    options TEXT,
    FOREIGN KEY (rule_id) REFERENCES bm60_sftpgo_events_rules(id) ON DELETE CASCADE,
    FOREIGN KEY (action_id) REFERENCES bm60_sftpgo_events_actions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rule_action_order (rule_id, action_id, `order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tasks Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    updated_at BIGINT DEFAULT 0,
    version BIGINT DEFAULT 0,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nodes Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_nodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    data TEXT,
    created_at BIGINT DEFAULT 0,
    updated_at BIGINT DEFAULT 0,
    INDEX idx_name (name),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roles Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description VARCHAR(512),
    created_at BIGINT DEFAULT 0,
    updated_at BIGINT DEFAULT 0,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- IP Lists Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_ip_lists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type INT NOT NULL,
    ipornet VARCHAR(50) NOT NULL,
    mode INT DEFAULT 0,
    description VARCHAR(512),
    first VARBINARY(16) NOT NULL,
    last VARBINARY(16) NOT NULL,
    ip_type INT NOT NULL,
    protocols INT DEFAULT 0,
    created_at BIGINT DEFAULT 0,
    updated_at BIGINT DEFAULT 0,
    deleted_at BIGINT DEFAULT 0,
    UNIQUE KEY unique_type_ipornet (type, ipornet),
    INDEX idx_type (type),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configurations Table
CREATE TABLE IF NOT EXISTS bm60_sftpgo_configurations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    configs TEXT,
    INDEX idx_id (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users Groups Mapping
CREATE TABLE IF NOT EXISTS bm60_sftpgo_users_groups_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    group_id INT NOT NULL,
    group_type INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES bm60_sftpgo_users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES bm60_sftpgo_groups(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_group (user_id, group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admins Groups Mapping
CREATE TABLE IF NOT EXISTS bm60_sftpgo_admins_groups_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    group_id INT NOT NULL,
    options TEXT,
    FOREIGN KEY (admin_id) REFERENCES bm60_sftpgo_admins(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES bm60_sftpgo_groups(id) ON DELETE CASCADE,
    UNIQUE KEY unique_admin_group (admin_id, group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Groups Folders Mapping
CREATE TABLE IF NOT EXISTS bm60_sftpgo_groups_folders_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    folder_id INT NOT NULL,
    virtual_path VARCHAR(512) NOT NULL,
    quota_size BIGINT DEFAULT -1,
    quota_files INT DEFAULT -1,
    FOREIGN KEY (group_id) REFERENCES bm60_sftpgo_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (folder_id) REFERENCES bm60_sftpgo_folders(id) ON DELETE CASCADE,
    UNIQUE KEY unique_group_folder (group_id, folder_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create default admin user (admin/admin)
INSERT INTO bm60_sftpgo_admins (username, password, status, permissions, created_at, updated_at, last_login)
VALUES ('admin', '$2a$10$XKHXRlDnqjcQC2RcxcKNSu6IFUjcQvqPPEWjTNj8vfaNlFZxvHgd6', 1, '["*"]', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0)
ON DUPLICATE KEY UPDATE username=username;

SELECT 'SFTPGo schema created successfully!' AS result;
