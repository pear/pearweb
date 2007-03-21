/**
 * Create tables for user comments
 */

CREATE TABLE manual_notes
(
    note_id int(11) NOT NULL AUTO_INCREMENT,
    page_url varchar(100) NOT NULL,
    user_name varchar(100) NOT NULL,
    note_text text NOT NULL,
    note_time timestamp NOT NULL,
    note_approved varchar(7) NOT NULL DEFAULT 'pending',
    note_approved_by int(11) NULL,
    note_deleted tinyint(1) NULL DEFAULT '0',
    PRIMARY KEY (note_id),
    INDEX idx_page_url (page_url),
    INDEX idx_note_time (note_time)
);
