<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20160126095606 extends AbstractMigration {

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        $this->addSql("CREATE TABLE dotpulse_form_domain_model_form (persistence_object_identifier VARCHAR(40) NOT NULL, formidentifier VARCHAR(255) NOT NULL, formlabel VARCHAR(255) NOT NULL, formvalues LONGTEXT NOT NULL COMMENT '(DC2Type:flow_json_array)', crdate DATETIME NOT NULL, PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        $this->addSql("DROP TABLE dotpulse_form_domain_model_form");
    }
}
