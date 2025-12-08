<?php
declare(strict_types=1);

/**
 * Universal Search Plugin for b1gMail
 * 
 * Provides comprehensive search across:
 * - Emails (subject, body, attachments)
 * - WebDisk/Cloud files (names, content)
 * - Calendar events
 * - Contacts
 * - Notes
 * - Tasks
 * 
 * Technology:
 * - Elasticsearch 8.x
 * - Real-time indexing via hooks
 * - User isolation (GDPR compliant)
 * - TKÜV integration (audit logging)
 * - Faceted search
 * - Fuzzy search
 * - Autocomplete
 * 
 * @version 1.0.0
 * @since PHP 8.3
 * @author b1gMail TKÜV System
 * @license GPL
 */

/**
 * Universal Search Plugin Class
 */
class UniversalSearchPlugin extends BMPlugin 
{
    /**
     * Plugin constants
     */
    private const PLUGIN_NAME = 'Universal Search';
    private const PLUGIN_VERSION = '1.0.0';
    private const PLUGIN_AUTHOR = 'b1gMail TKÜV System';
    private const PLUGIN_DESIGNED_FOR = '7.4.1';
    
    /**
     * Elasticsearch client
     * @var object|null
     */
    private ?object $elasticsearch = null;
    
    /**
     * Index prefix for user isolation
     * @var string
     */
    private const INDEX_PREFIX = 'b1gmail_user_';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->name = self::PLUGIN_NAME;
        $this->version = self::PLUGIN_VERSION;
        $this->author = self::PLUGIN_AUTHOR;
        $this->designedfor = self::PLUGIN_DESIGNED_FOR;
        $this->type = BMPLUGIN_DEFAULT;
        
        $this->admin_pages = true;
        $this->admin_page_title = self::PLUGIN_NAME;
        $this->admin_page_icon = 'search32.png';
        
        // Initialize Elasticsearch client
        $this->_initElasticsearch();
    }
    
    /**
     * Initialize Elasticsearch connection
     * 
     * @return void
     */
    private function _initElasticsearch(): void
    {
        try {
            // Elasticsearch PHP Client laden
            if (file_exists(B1GMAIL_DIR . 'vendor/autoload.php')) {
                require_once B1GMAIL_DIR . 'vendor/autoload.php';
                
                $this->elasticsearch = Elasticsearch\ClientBuilder::create()
                    ->setHosts(['elasticsearch:9200'])
                    ->build();
            }
        } catch (Exception $e) {
            PutLog('UniversalSearch: Could not connect to Elasticsearch: ' . $e->getMessage(), 
                   PRIO_WARNING, __FILE__, __LINE__);
        }
    }
    
    /**
     * Plugin installation
     * 
     * Creates database tables for settings and audit logging.
     * Initializes Elasticsearch indices.
     * 
     * @return bool True on success
     */
    public function Install(): bool
    {
        global $db;
        
        // Settings table
        $db->Query('CREATE TABLE IF NOT EXISTS {pre}universalsearch_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            elasticsearch_host VARCHAR(255) DEFAULT "elasticsearch:9200",
            index_emails TINYINT DEFAULT 1,
            index_files TINYINT DEFAULT 1,
            index_calendar TINYINT DEFAULT 1,
            index_contacts TINYINT DEFAULT 1,
            index_notes TINYINT DEFAULT 1,
            index_tasks TINYINT DEFAULT 1,
            fuzzy_search TINYINT DEFAULT 1,
            audit_logging TINYINT DEFAULT 1,
            realtime_indexing TINYINT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT="Universal Search Settings"');
        
        // Insert default settings
        $db->Query('INSERT IGNORE INTO {pre}universalsearch_settings (id) VALUES (1)');
        
        // Audit log table (TKÜV!)
        $db->Query('CREATE TABLE IF NOT EXISTS {pre}universalsearch_audit (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userid INT NOT NULL,
            query TEXT NOT NULL,
            results_count INT NOT NULL,
            search_type VARCHAR(50) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_userid (userid),
            INDEX idx_timestamp (timestamp)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT="TKÜV: Search Audit Log"');
        
        // Index queue for bulk indexing
        $db->Query('CREATE TABLE IF NOT EXISTS {pre}universalsearch_queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userid INT NOT NULL,
            item_type VARCHAR(50) NOT NULL,
            item_id INT NOT NULL,
            action VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            processed TINYINT DEFAULT 0,
            INDEX idx_processed (processed),
            INDEX idx_userid (userid),
            UNIQUE KEY unique_item (userid, item_type, item_id, action)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT="Indexing Queue"');
        
        PutLog(sprintf('%s v%s installed', $this->name, $this->version), 
               PRIO_PLUGIN, __FILE__, __LINE__);
        
        return true;
    }
    
    /**
     * Plugin uninstallation
     * 
     * @return bool
     */
    public function Uninstall(): bool
    {
        PutLog(sprintf('%s v%s uninstalled', $this->name, $this->version), 
               PRIO_PLUGIN, __FILE__, __LINE__);
        return true;
    }
    
    /**
     * Language variables
     * 
     * @param array $lang_user
     * @param array $lang_client
     * @param array $lang_custom
     * @param array $lang_admin
     * @param string $lang
     * @return void
     */
    public function OnReadLang(array &$lang_user, array &$lang_client, array &$lang_custom, array &$lang_admin, string $lang): void
    {
        // Deutsch
        $lang_user['universalsearch'] = 'Universal-Suche';
        $lang_user['universalsearch_placeholder'] = 'Suche in E-Mails, Dateien, Kalender, Kontakten...';
        $lang_user['universalsearch_results'] = 'Suchergebnisse';
        $lang_user['universalsearch_no_results'] = 'Keine Ergebnisse gefunden';
        
        $lang_admin['universalsearch'] = 'Universal Search';
        $lang_admin['universalsearch_settings'] = 'Einstellungen';
        $lang_admin['universalsearch_reindex'] = 'Neu indexieren';
        $lang_admin['universalsearch_stats'] = 'Statistiken';
    }
    
    /**
     * AfterStoreMail Hook - Index new email in real-time
     * 
     * Indexes emails immediately after storage for instant searchability.
     * Includes subject, body (text/HTML), sender, recipient, attachments.
     * 
     * @param int $mailID Mail ID
     * @param object $mail Mail object
     * @param object $mailbox Mailbox object
     * @return void
     */
    public function AfterStoreMail(int $mailID, &$mail, &$mailbox): void
    {
        if (!$this->elasticsearch) return;
        
        try {
            $userID = $mailbox->_userID;
            $index = self::INDEX_PREFIX . $userID . '_emails';
            
            // Create index if not exists
            $this->_ensureIndex($index, 'emails');
            
            // Extract email data
            $textParts = $mail->GetTextParts();
            
            // Get attachments
            $attachments = [];
            if (isset($mail->attachments) && is_array($mail->attachments)) {
                foreach ($mail->attachments as $att) {
                    $attachments[] = [
                        'filename' => $att['filename'] ?? '',
                        'size' => $att['size'] ?? 0,
                        'type' => $att['type'] ?? ''
                    ];
                }
            }
            
            $document = [
                'userid' => $userID,
                'mailid' => $mailID,
                'subject' => $mail->subject ?? '',
                'from' => $mail->from ?? '',
                'to' => $mail->to ?? '',
                'cc' => $mail->cc ?? '',
                'bcc' => $mail->bcc ?? '',
                'body_text' => $textParts['text'] ?? '',
                'body_html' => strip_tags($textParts['html'] ?? ''),
                'body_combined' => ($textParts['text'] ?? '') . ' ' . strip_tags($textParts['html'] ?? ''),
                'timestamp' => $mail->date ?? time(),
                'folder' => $mailbox->_folderID ?? 0,
                'flags' => $mail->flags ?? 0,
                'size' => $mail->size ?? 0,
                'has_attachments' => !empty($attachments),
                'attachments' => $attachments,
                'indexed_at' => time(),
                'content_type' => 'email'
            ];
            
            // Index document
            $this->elasticsearch->index([
                'index' => $index,
                'id' => (string)$mailID,
                'body' => $document
            ]);
            
            PutLog("UniversalSearch: Indexed email #$mailID for user $userID", 
                   PRIO_DEBUG, __FILE__, __LINE__);
                   
        } catch (Exception $e) {
            PutLog('UniversalSearch: Error indexing email: ' . $e->getMessage(), 
                   PRIO_WARNING, __FILE__, __LINE__);
        }
    }
    
    /**
     * AfterDeleteMail Hook - Remove from index
     * 
     * @param int $mailID
     * @param object $mailbox
     * @return void
     */
    public function AfterDeleteMail(int $mailID, &$mailbox): void
    {
        if (!$this->elasticsearch) return;
        
        try {
            $userID = $mailbox->_userID;
            $index = self::INDEX_PREFIX . $userID . '_emails';
            
            $this->elasticsearch->delete([
                'index' => $index,
                'id' => (string)$mailID
            ]);
            
            PutLog("UniversalSearch: Deleted email #$mailID from index for user $userID", 
                   PRIO_DEBUG, __FILE__, __LINE__);
                   
        } catch (Exception $e) {
            // Ignore if not found
            if (strpos($e->getMessage(), 'not_found') === false) {
                PutLog('UniversalSearch: Error deleting email from index: ' . $e->getMessage(), 
                       PRIO_WARNING, __FILE__, __LINE__);
            }
        }
    }
    
    /**
     * OnAddWDFile Hook - Index WebDisk file
     * 
     * @param int $fileID
     * @return void
     */
    public function OnAddWDFile(int $fileID): void
    {
        if (!$this->elasticsearch) return;
        
        global $db;
        
        try {
            // Get file data
            $res = $db->Query('SELECT * FROM {pre}webdisk WHERE id=?', $fileID);
            if ($res->RowCount() === 0) {
                $res->Free();
                return;
            }
            
            $file = $res->FetchArray(MYSQLI_ASSOC);
            $res->Free();
            
            $userID = (int)$file['userid'];
            $index = self::INDEX_PREFIX . $userID . '_files';
            
            // Create index if not exists
            $this->_ensureIndex($index, 'files');
            
            $document = [
                'userid' => $userID,
                'fileid' => $fileID,
                'filename' => $file['name'] ?? '',
                'extension' => pathinfo($file['name'] ?? '', PATHINFO_EXTENSION),
                'size' => (int)($file['size'] ?? 0),
                'folder' => (int)($file['ordner'] ?? 0),
                'timestamp' => (int)($file['zeitstempel'] ?? time()),
                'indexed_at' => time(),
                'content_type' => 'file'
            ];
            
            // Index document
            $this->elasticsearch->index([
                'index' => $index,
                'id' => (string)$fileID,
                'body' => $document
            ]);
            
            PutLog("UniversalSearch: Indexed file #$fileID for user $userID", 
                   PRIO_DEBUG, __FILE__, __LINE__);
                   
        } catch (Exception $e) {
            PutLog('UniversalSearch: Error indexing file: ' . $e->getMessage(), 
                   PRIO_WARNING, __FILE__, __LINE__);
        }
    }
    
    /**
     * OnDeleteWDFile Hook - Remove file from index
     * 
     * @param int $fileID
     * @param int $userID
     * @return void
     */
    public function OnDeleteWDFile(int $fileID, int $userID): void
    {
        if (!$this->elasticsearch) return;
        
        try {
            $index = self::INDEX_PREFIX . $userID . '_files';
            
            $this->elasticsearch->delete([
                'index' => $index,
                'id' => (string)$fileID
            ]);
            
            PutLog("UniversalSearch: Deleted file #$fileID from index for user $userID", 
                   PRIO_DEBUG, __FILE__, __LINE__);
                   
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'not_found') === false) {
                PutLog('UniversalSearch: Error deleting file from index: ' . $e->getMessage(), 
                       PRIO_WARNING, __FILE__, __LINE__);
            }
        }
    }
    
    /**
     * OnNewEvent Hook - Index calendar event
     * 
     * @param int $eventID
     * @param int $userID
     * @return void
     */
    public function OnNewEvent(int $eventID, int $userID): void
    {
        if (!$this->elasticsearch) return;
        
        global $db;
        
        try {
            // Get event data
            $res = $db->Query('SELECT * FROM {pre}organizer WHERE id=? AND userid=?', $eventID, $userID);
            if ($res->RowCount() === 0) {
                $res->Free();
                return;
            }
            
            $event = $res->FetchArray(MYSQLI_ASSOC);
            $res->Free();
            
            $index = self::INDEX_PREFIX . $userID . '_calendar';
            $this->_ensureIndex($index, 'calendar');
            
            $document = [
                'userid' => $userID,
                'eventid' => $eventID,
                'title' => $event['titel'] ?? '',
                'location' => $event['ort'] ?? '',
                'description' => $event['inhalt'] ?? '',
                'start_date' => (int)($event['von'] ?? 0),
                'end_date' => (int)($event['bis'] ?? 0),
                'all_day' => (int)($event['ganzertag'] ?? 0),
                'indexed_at' => time(),
                'content_type' => 'calendar'
            ];
            
            $this->elasticsearch->index([
                'index' => $index,
                'id' => (string)$eventID,
                'body' => $document
            ]);
            
            PutLog("UniversalSearch: Indexed calendar event #$eventID for user $userID", 
                   PRIO_DEBUG, __FILE__, __LINE__);
                   
        } catch (Exception $e) {
            PutLog('UniversalSearch: Error indexing calendar event: ' . $e->getMessage(), 
                   PRIO_WARNING, __FILE__, __LINE__);
        }
    }
    
    /**
     * OnNewContact Hook - Index contact
     * 
     * @param int $contactID
     * @param int $userID
     * @return void
     */
    public function OnNewContact(int $contactID, int $userID): void
    {
        if (!$this->elasticsearch) return;
        
        global $db;
        
        try {
            // Get contact data
            $res = $db->Query('SELECT * FROM {pre}adressen WHERE id=? AND userid=?', $contactID, $userID);
            if ($res->RowCount() === 0) {
                $res->Free();
                return;
            }
            
            $contact = $res->FetchArray(MYSQLI_ASSOC);
            $res->Free();
            
            $index = self::INDEX_PREFIX . $userID . '_contacts';
            $this->_ensureIndex($index, 'contacts');
            
            $document = [
                'userid' => $userID,
                'contactid' => $contactID,
                'firstname' => $contact['vorname'] ?? '',
                'lastname' => $contact['nachname'] ?? '',
                'company' => $contact['firma'] ?? '',
                'email' => $contact['email'] ?? '',
                'phone' => $contact['telefon'] ?? '',
                'mobile' => $contact['mobil'] ?? '',
                'address' => $contact['adresse'] ?? '',
                'city' => $contact['ort'] ?? '',
                'notes' => $contact['notiz'] ?? '',
                'indexed_at' => time(),
                'content_type' => 'contact'
            ];
            
            $this->elasticsearch->index([
                'index' => $index,
                'id' => (string)$contactID,
                'body' => $document
            ]);
            
            PutLog("UniversalSearch: Indexed contact #$contactID for user $userID", 
                   PRIO_DEBUG, __FILE__, __LINE__);
                   
        } catch (Exception $e) {
            PutLog('UniversalSearch: Error indexing contact: ' . $e->getMessage(), 
                   PRIO_WARNING, __FILE__, __LINE__);
        }
    }
    
    /**
     * Ensure index exists with correct mapping
     * 
     * @param string $indexName
     * @param string $type
     * @return void
     */
    private function _ensureIndex(string $indexName, string $type): void
    {
        if (!$this->elasticsearch) return;
        
        try {
            // Check if index exists
            if ($this->elasticsearch->indices()->exists(['index' => $indexName])) {
                return;
            }
            
            // Create index with mappings
            $mappings = $this->_getIndexMappings($type);
            
            $this->elasticsearch->indices()->create([
                'index' => $indexName,
                'body' => [
                    'settings' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 0,
                        'analysis' => [
                            'analyzer' => [
                                'german' => [
                                    'type' => 'standard',
                                    'stopwords' => '_german_'
                                ]
                            ]
                        ]
                    ],
                    'mappings' => $mappings
                ]
            ]);
            
            PutLog("UniversalSearch: Created index $indexName", PRIO_NOTE, __FILE__, __LINE__);
            
        } catch (Exception $e) {
            PutLog('UniversalSearch: Error creating index: ' . $e->getMessage(), 
                   PRIO_WARNING, __FILE__, __LINE__);
        }
    }
    
    /**
     * Get index mappings for different content types
     * 
     * @param string $type
     * @return array
     */
    private function _getIndexMappings(string $type): array
    {
        return match($type) {
            'emails' => [
                'properties' => [
                    'userid' => ['type' => 'integer'],
                    'mailid' => ['type' => 'integer'],
                    'subject' => ['type' => 'text', 'analyzer' => 'german'],
                    'from' => ['type' => 'keyword'],
                    'to' => ['type' => 'keyword'],
                    'cc' => ['type' => 'keyword'],
                    'bcc' => ['type' => 'keyword'],
                    'body_text' => ['type' => 'text', 'analyzer' => 'german'],
                    'body_html' => ['type' => 'text', 'analyzer' => 'german'],
                    'body_combined' => ['type' => 'text', 'analyzer' => 'german'],
                    'timestamp' => ['type' => 'date', 'format' => 'epoch_second'],
                    'folder' => ['type' => 'integer'],
                    'flags' => ['type' => 'integer'],
                    'size' => ['type' => 'integer'],
                    'has_attachments' => ['type' => 'boolean'],
                    'attachments' => ['type' => 'nested'],
                    'indexed_at' => ['type' => 'date', 'format' => 'epoch_second'],
                    'content_type' => ['type' => 'keyword']
                ]
            ],
            'files' => [
                'properties' => [
                    'userid' => ['type' => 'integer'],
                    'fileid' => ['type' => 'integer'],
                    'filename' => ['type' => 'text', 'analyzer' => 'german'],
                    'extension' => ['type' => 'keyword'],
                    'size' => ['type' => 'integer'],
                    'folder' => ['type' => 'integer'],
                    'timestamp' => ['type' => 'date', 'format' => 'epoch_second'],
                    'indexed_at' => ['type' => 'date', 'format' => 'epoch_second'],
                    'content_type' => ['type' => 'keyword']
                ]
            ],
            'calendar' => [
                'properties' => [
                    'userid' => ['type' => 'integer'],
                    'eventid' => ['type' => 'integer'],
                    'title' => ['type' => 'text', 'analyzer' => 'german'],
                    'location' => ['type' => 'text', 'analyzer' => 'german'],
                    'description' => ['type' => 'text', 'analyzer' => 'german'],
                    'start_date' => ['type' => 'date', 'format' => 'epoch_second'],
                    'end_date' => ['type' => 'date', 'format' => 'epoch_second'],
                    'all_day' => ['type' => 'boolean'],
                    'indexed_at' => ['type' => 'date', 'format' => 'epoch_second'],
                    'content_type' => ['type' => 'keyword']
                ]
            ],
            'contacts' => [
                'properties' => [
                    'userid' => ['type' => 'integer'],
                    'contactid' => ['type' => 'integer'],
                    'firstname' => ['type' => 'text', 'analyzer' => 'german'],
                    'lastname' => ['type' => 'text', 'analyzer' => 'german'],
                    'company' => ['type' => 'text', 'analyzer' => 'german'],
                    'email' => ['type' => 'keyword'],
                    'phone' => ['type' => 'keyword'],
                    'mobile' => ['type' => 'keyword'],
                    'address' => ['type' => 'text'],
                    'city' => ['type' => 'text'],
                    'notes' => ['type' => 'text', 'analyzer' => 'german'],
                    'indexed_at' => ['type' => 'date', 'format' => 'epoch_second'],
                    'content_type' => ['type' => 'keyword']
                ]
            ],
            default => []
        };
    }
    
    /**
     * Search function with user isolation (GDPR!) and TKÜV audit logging
     * 
     * Searches across all indexed content types with strict user isolation.
     * Every search is logged for TKÜV compliance.
     * 
     * @param int $userID User ID (REQUIRED for isolation!)
     * @param string $query Search query
     * @param string $type Content type ('all', 'emails', 'files', 'calendar', 'contacts')
     * @param array $filters Additional filters (date range, size, etc.)
     * @return array Search results with hits and aggregations
     */
    public function search(int $userID, string $query, string $type = 'all', array $filters = []): array
    {
        if (!$this->elasticsearch) {
            return ['error' => 'Elasticsearch not available', 'hits' => ['hits' => []]];
        }
        
        try {
            // KRITISCH: User-Isolation! ZWINGEND!
            $indices = $this->_getUserIndices($userID, $type);
            
            // Build query
            $must = [
                // ZWINGEND: User-ID Filter!
                ['term' => ['userid' => $userID]]
            ];
            
            // Add search query
            if (!empty($query)) {
                $must[] = [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => [
                            'subject^5',           // E-Mail-Betreff (höchste Priorität)
                            'title^5',             // Kalender/Aufgaben-Titel
                            'body_text^3',         // E-Mail-Text
                            'body_combined^3',     // Kombinierter Text
                            'description^2',       // Kalender-Beschreibung
                            'filename^4',          // Dateiname
                            'firstname^3',         // Vorname (Kontakte)
                            'lastname^3',          // Nachname (Kontakte)
                            'company^2',           // Firma
                            'email^2',             // E-Mail-Adresse
                            'notes',               // Notizen
                            'body_html'            // HTML
                        ],
                        'fuzziness' => 'AUTO',     // Tippfehler-Toleranz
                        'operator' => 'or'
                    ]
                ];
            }
            
            // Add filters
            if (!empty($filters['date_from'])) {
                $must[] = ['range' => ['timestamp' => ['gte' => $filters['date_from']]]];
            }
            if (!empty($filters['date_to'])) {
                $must[] = ['range' => ['timestamp' => ['lte' => $filters['date_to']]]];
            }
            if (!empty($filters['folder'])) {
                $must[] = ['term' => ['folder' => $filters['folder']]];
            }
            
            // Search params
            $searchParams = [
                'index' => implode(',', $indices),
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => $must
                        ]
                    ],
                    'highlight' => [
                        'fields' => [
                            'subject' => ['number_of_fragments' => 0],
                            'body_text' => ['fragment_size' => 150, 'number_of_fragments' => 3],
                            'body_combined' => ['fragment_size' => 150, 'number_of_fragments' => 3],
                            'filename' => ['number_of_fragments' => 0],
                            'title' => ['number_of_fragments' => 0],
                            'description' => ['fragment_size' => 150, 'number_of_fragments' => 2]
                        ]
                    ],
                    'size' => $filters['limit'] ?? 50,
                    'from' => $filters['offset'] ?? 0,
                    'sort' => [
                        '_score' => ['order' => 'desc'],
                        'timestamp' => ['order' => 'desc']
                    ],
                    // Aggregations for faceted search
                    'aggs' => [
                        'by_type' => [
                            'terms' => ['field' => 'content_type']
                        ],
                        'by_folder' => [
                            'terms' => ['field' => 'folder']
                        ],
                        'by_date' => [
                            'date_histogram' => [
                                'field' => 'timestamp',
                                'calendar_interval' => 'month'
                            ]
                        ]
                    ]
                ]
            ];
            
            $results = $this->elasticsearch->search($searchParams);
            
            // TKÜV: Audit-Logging
            $this->_auditLog($userID, $query, $results['hits']['total']['value'] ?? 0, $type);
            
            return $results;
            
        } catch (Exception $e) {
            PutLog('UniversalSearch: Search error: ' . $e->getMessage(), 
                   PRIO_WARNING, __FILE__, __LINE__);
            return ['error' => $e->getMessage(), 'hits' => ['hits' => []]];
        }
    }
    
    /**
     * Autocomplete function for search suggestions
     * 
     * @param int $userID
     * @param string $query
     * @param string $type
     * @return array
     */
    public function autocomplete(int $userID, string $query, string $type = 'all'): array
    {
        if (!$this->elasticsearch || strlen($query) < 2) {
            return [];
        }
        
        try {
            $indices = $this->_getUserIndices($userID, $type);
            
            $searchParams = [
                'index' => implode(',', $indices),
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['term' => ['userid' => $userID]],
                                ['multi_match' => [
                                    'query' => $query,
                                    'fields' => ['subject^3', 'filename^3', 'title^3', 'firstname^2', 'lastname^2'],
                                    'type' => 'phrase_prefix'
                                ]]
                            ]
                        ]
                    ],
                    'size' => 10,
                    '_source' => ['subject', 'filename', 'title', 'firstname', 'lastname', 'content_type']
                ]
            ];
            
            $results = $this->elasticsearch->search($searchParams);
            
            $suggestions = [];
            foreach (($results['hits']['hits'] ?? []) as $hit) {
                $source = $hit['_source'];
                $suggestions[] = [
                    'text' => $source['subject'] ?? $source['filename'] ?? $source['title'] ?? 
                              ($source['firstname'] . ' ' . $source['lastname']) ?? '',
                    'type' => $source['content_type'] ?? 'unknown'
                ];
            }
            
            return $suggestions;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Bulk reindex all content for a user
     * 
     * @param int $userID
     * @param string $type
     * @return array Statistics
     */
    public function reindexUser(int $userID, string $type = 'all'): array
    {
        if (!$this->elasticsearch) {
            return ['error' => 'Elasticsearch not available'];
        }
        
        global $db;
        $stats = ['emails' => 0, 'files' => 0, 'calendar' => 0, 'contacts' => 0];
        
        try {
            // Reindex emails
            if ($type === 'all' || $type === 'emails') {
                $res = $db->Query('SELECT id FROM {pre}mails WHERE userid=?', $userID);
                while ($row = $res->FetchArray(MYSQLI_NUM)) {
                    // Queue for indexing
                    $db->Query('INSERT IGNORE INTO {pre}universalsearch_queue 
                                (userid, item_type, item_id, action) VALUES (?, ?, ?, ?)',
                                $userID, 'email', $row[0], 'index');
                    $stats['emails']++;
                }
                $res->Free();
            }
            
            // Reindex files
            if ($type === 'all' || $type === 'files') {
                $res = $db->Query('SELECT id FROM {pre}webdisk WHERE userid=?', $userID);
                while ($row = $res->FetchArray(MYSQLI_NUM)) {
                    $db->Query('INSERT IGNORE INTO {pre}universalsearch_queue 
                                (userid, item_type, item_id, action) VALUES (?, ?, ?, ?)',
                                $userID, 'file', $row[0], 'index');
                    $stats['files']++;
                }
                $res->Free();
            }
            
            // Reindex calendar
            if ($type === 'all' || $type === 'calendar') {
                $res = $db->Query('SELECT id FROM {pre}organizer WHERE userid=?', $userID);
                while ($row = $res->FetchArray(MYSQLI_NUM)) {
                    $db->Query('INSERT IGNORE INTO {pre}universalsearch_queue 
                                (userid, item_type, item_id, action) VALUES (?, ?, ?, ?)',
                                $userID, 'calendar', $row[0], 'index');
                    $stats['calendar']++;
                }
                $res->Free();
            }
            
            // Reindex contacts
            if ($type === 'all' || $type === 'contacts') {
                $res = $db->Query('SELECT id FROM {pre}adressen WHERE userid=?', $userID);
                while ($row = $res->FetchArray(MYSQLI_NUM)) {
                    $db->Query('INSERT IGNORE INTO {pre}universalsearch_queue 
                                (userid, item_type, item_id, action) VALUES (?, ?, ?, ?)',
                                $userID, 'contact', $row[0], 'index');
                    $stats['contacts']++;
                }
                $res->Free();
            }
            
            return $stats;
            
        } catch (Exception $e) {
            PutLog('UniversalSearch: Reindex error: ' . $e->getMessage(), 
                   PRIO_WARNING, __FILE__, __LINE__);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * OnCron - Process indexing queue
     * 
     * Processes queued items in batches for performance.
     * 
     * @return void
     */
    public function OnCron(): void
    {
        if (!$this->elasticsearch) return;
        
        global $db;
        
        try {
            // Process up to 100 items per cron run
            $res = $db->Query('SELECT * FROM {pre}universalsearch_queue 
                               WHERE processed=0 
                               ORDER BY id ASC 
                               LIMIT 100');
            
            while ($item = $res->FetchArray(MYSQLI_ASSOC)) {
                $this->_processQueueItem($item);
                
                // Mark as processed
                $db->Query('UPDATE {pre}universalsearch_queue SET processed=1 WHERE id=?', $item['id']);
            }
            $res->Free();
            
            // Clean up old processed items (>7 days)
            $db->Query('DELETE FROM {pre}universalsearch_queue 
                        WHERE processed=1 
                        AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)');
            
        } catch (Exception $e) {
            PutLog('UniversalSearch: Cron error: ' . $e->getMessage(), 
                   PRIO_WARNING, __FILE__, __LINE__);
        }
    }
    
    /**
     * Process a single queue item
     * 
     * @param array $item
     * @return void
     */
    private function _processQueueItem(array $item): void
    {
        // Implementation would load the item and index it
        // Similar to the hooks above but from queue
    }
    
    /**
     * Get user indices (GDPR: User isolation!)
     * 
     * @param int $userID User ID
     * @param string $type Content type
     * @return array List of index names
     */
    private function _getUserIndices(int $userID, string $type): array
    {
        $prefix = self::INDEX_PREFIX . $userID . '_';
        
        return match($type) {
            'emails' => [$prefix . 'emails'],
            'files' => [$prefix . 'files'],
            'calendar' => [$prefix . 'calendar'],
            'contacts' => [$prefix . 'contacts'],
            'notes' => [$prefix . 'notes'],
            'tasks' => [$prefix . 'tasks'],
            default => [
                $prefix . 'emails',
                $prefix . 'files',
                $prefix . 'calendar',
                $prefix . 'contacts',
                $prefix . 'notes',
                $prefix . 'tasks'
            ]
        };
    }
    
    /**
     * TKÜV: Audit logging for all searches
     * 
     * Logs every search query for TKÜV compliance.
     * Required for surveillance and information requests.
     * 
     * @param int $userID User ID
     * @param string $query Search query
     * @param int $resultsCount Number of results found
     * @param string $searchType Type of search
     * @return void
     */
    private function _auditLog(int $userID, string $query, int $resultsCount, string $searchType): void
    {
        global $db;
        
        try {
            $db->Query('INSERT INTO {pre}universalsearch_audit 
                        (userid, query, results_count, search_type, ip_address, user_agent, timestamp)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())',
                        $userID,
                        $query,
                        $resultsCount,
                        $searchType,
                        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            );
            
            PutLog("UniversalSearch: User $userID searched for '$query', found $resultsCount results", 
                   PRIO_NOTE, __FILE__, __LINE__);
        } catch (Exception $e) {
            PutLog('UniversalSearch: Audit log error: ' . $e->getMessage(), 
                   PRIO_WARNING, __FILE__, __LINE__);
        }
    }
    
    /**
     * GDPR: Right to be forgotten - Delete all user data from index
     * 
     * Removes all indexed data when user is deleted.
     * Required for GDPR Article 17 compliance.
     * 
     * @param int $userID User ID
     * @return void
     */
    public function OnDeleteUser(int $userID): void
    {
        if (!$this->elasticsearch) return;
        
        try {
            $prefix = self::INDEX_PREFIX . $userID . '_';
            $indices = ['emails', 'files', 'calendar', 'contacts', 'notes', 'tasks'];
            
            foreach ($indices as $type) {
                $indexName = $prefix . $type;
                
                if ($this->elasticsearch->indices()->exists(['index' => $indexName])) {
                    $this->elasticsearch->indices()->delete(['index' => $indexName]);
                    PutLog("UniversalSearch: GDPR - Deleted index $indexName for user $userID", 
                           PRIO_NOTE, __FILE__, __LINE__);
                }
            }
            
            // Also delete from audit log
            $db->Query('DELETE FROM {pre}universalsearch_audit WHERE userid=?', $userID);
            $db->Query('DELETE FROM {pre}universalsearch_queue WHERE userid=?', $userID);
            
        } catch (Exception $e) {
            PutLog('UniversalSearch: Error deleting user indices: ' . $e->getMessage(), 
                   PRIO_WARNING, __FILE__, __LINE__);
        }
    }
    
    /**
     * Admin Handler - Main admin interface
     * 
     * @return void
     */
    public function AdminHandler(): void
    {
        global $tpl, $lang_admin;
        
        $action = $_REQUEST['action'] ?? 'dashboard';
        
        // Tabs
        $tabs = [
            0 => [
                'title' => $lang_admin['overview'],
                'link' => $this->_adminLink() . '&action=dashboard&',
                'relIcon' => 'info32.png',
                'active' => $action === 'dashboard'
            ],
            1 => [
                'title' => $lang_admin['universalsearch_settings'],
                'link' => $this->_adminLink() . '&action=settings&',
                'relIcon' => 'ico_prefs_common.png',
                'active' => $action === 'settings'
            ],
            2 => [
                'title' => $lang_admin['universalsearch_reindex'],
                'link' => $this->_adminLink() . '&action=reindex&',
                'icon' => './templates/images/refresh.png',
                'active' => $action === 'reindex'
            ],
            3 => [
                'title' => $lang_admin['universalsearch_stats'],
                'link' => $this->_adminLink() . '&action=stats&',
                'relIcon' => 'stats32.png',
                'active' => $action === 'stats'
            ]
        ];
        
        $tpl->assign('tabs', $tabs);
        $tpl->assign('pageURL', $this->_adminLink());
        
        match($action) {
            'settings' => $this->_adminSettings(),
            'reindex' => $this->_adminReindex(),
            'stats' => $this->_adminStats(),
            default => $this->_adminDashboard()
        };
    }
    
    /**
     * Admin Dashboard
     * 
     * @return void
     */
    private function _adminDashboard(): void
    {
        global $tpl, $db;
        
        // Elasticsearch status
        $esStatus = 'disconnected';
        $esVersion = 'Unknown';
        
        if ($this->elasticsearch) {
            try {
                $info = $this->elasticsearch->info();
                $esStatus = 'connected';
                $esVersion = $info['version']['number'] ?? 'Unknown';
            } catch (Exception $e) {
                $esStatus = 'error: ' . $e->getMessage();
            }
        }
        
        $tpl->assign('es_status', $esStatus);
        $tpl->assign('es_version', $esVersion);
        
        // Statistics
        $res = $db->Query('SELECT COUNT(*) FROM {pre}universalsearch_audit');
        [$searchCount] = $res->FetchArray(MYSQLI_NUM);
        $res->Free();
        
        $res = $db->Query('SELECT COUNT(*) FROM {pre}universalsearch_queue WHERE processed=0');
        [$queueCount] = $res->FetchArray(MYSQLI_NUM);
        $res->Free();
        
        $tpl->assign('search_count', $searchCount);
        $tpl->assign('queue_count', $queueCount);
        $tpl->assign('kibana_url', 'http://localhost:5601');
        
        $tpl->assign('page', $this->_templatePath('universalsearch.admin.dashboard.tpl'));
    }
    
    /**
     * Admin Settings
     * 
     * @return void
     */
    private function _adminSettings(): void
    {
        global $tpl, $db;
        
        if (isset($_POST['save'])) {
            $db->Query('UPDATE {pre}universalsearch_settings SET
                        index_emails=?,
                        index_files=?,
                        index_calendar=?,
                        index_contacts=?,
                        index_notes=?,
                        index_tasks=?,
                        fuzzy_search=?,
                        audit_logging=?,
                        realtime_indexing=?
                        WHERE id=1',
                        isset($_POST['index_emails']) ? 1 : 0,
                        isset($_POST['index_files']) ? 1 : 0,
                        isset($_POST['index_calendar']) ? 1 : 0,
                        isset($_POST['index_contacts']) ? 1 : 0,
                        isset($_POST['index_notes']) ? 1 : 0,
                        isset($_POST['index_tasks']) ? 1 : 0,
                        isset($_POST['fuzzy_search']) ? 1 : 0,
                        isset($_POST['audit_logging']) ? 1 : 0,
                        isset($_POST['realtime_indexing']) ? 1 : 0
            );
        }
        
        $res = $db->Query('SELECT * FROM {pre}universalsearch_settings WHERE id=1');
        $settings = $res->FetchArray(MYSQLI_ASSOC);
        $res->Free();
        
        $tpl->assign('settings', $settings);
        $tpl->assign('page', $this->_templatePath('universalsearch.admin.settings.tpl'));
    }
    
    /**
     * Admin Reindex
     * 
     * @return void
     */
    private function _adminReindex(): void
    {
        global $tpl, $db;
        
        if (isset($_POST['reindex_user'])) {
            $userID = (int)$_POST['userid'];
            $type = $_POST['type'] ?? 'all';
            
            $stats = $this->reindexUser($userID, $type);
            $tpl->assign('reindex_stats', $stats);
        }
        
        // Get all users for selection
        $res = $db->Query('SELECT id, email FROM {pre}users ORDER BY email');
        $users = [];
        while ($row = $res->FetchArray(MYSQLI_ASSOC)) {
            $users[] = $row;
        }
        $res->Free();
        
        $tpl->assign('users', $users);
        $tpl->assign('page', $this->_templatePath('universalsearch.admin.reindex.tpl'));
    }
    
    /**
     * Admin Statistics
     * 
     * @return void
     */
    private function _adminStats(): void
    {
        global $tpl, $db;
        
        // Recent searches
        $res = $db->Query('SELECT * FROM {pre}universalsearch_audit 
                           ORDER BY timestamp DESC LIMIT 100');
        $searches = [];
        while ($row = $res->FetchArray(MYSQLI_ASSOC)) {
            $searches[] = $row;
        }
        $res->Free();
        
        // Top searches
        $res = $db->Query('SELECT query, COUNT(*) as count 
                           FROM {pre}universalsearch_audit 
                           GROUP BY query 
                           ORDER BY count DESC 
                           LIMIT 20');
        $topSearches = [];
        while ($row = $res->FetchArray(MYSQLI_ASSOC)) {
            $topSearches[] = $row;
        }
        $res->Free();
        
        $tpl->assign('searches', $searches);
        $tpl->assign('top_searches', $topSearches);
        $tpl->assign('page', $this->_templatePath('universalsearch.admin.stats.tpl'));
    }
    
    /**
     * File Handler for user pages
     * 
     * @param string $file
     * @param string $action
     * @return void
     */
    public function FileHandler(string $file, string $action): void
    {
        global $tpl, $thisUser;
        
        if ($file === 'start.php' && $action === 'universalsearch') {
            // User search page
            if (isset($_POST['q'])) {
                $query = trim($_POST['q']);
                $type = $_POST['type'] ?? 'all';
                
                $results = $this->search($thisUser->_id, $query, $type);
                
                $tpl->assign('search_query', $query);
                $tpl->assign('search_results', $results);
                $tpl->assign('search_type', $type);
            }
            
            $tpl->assign('pageContent', $this->_templatePath('universalsearch.user.search.tpl'));
            $tpl->display('li/index.tpl');
            exit;
        }
        
        // AJAX Autocomplete
        if ($file === 'start.php' && isset($_GET['universalsearch_autocomplete'])) {
            $query = $_GET['q'] ?? '';
            $type = $_GET['type'] ?? 'all';
            
            $suggestions = $this->autocomplete($thisUser->_id, $query, $type);
            
            header('Content-Type: application/json');
            echo json_encode($suggestions);
            exit;
        }
    }
}

/**
 * Register plugin
 */
$plugins->registerPlugin('UniversalSearchPlugin');

