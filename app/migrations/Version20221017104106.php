<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221017104106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE "company_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "payment_transaction_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "staff_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "company" (id INT NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, date_created TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4FBF094F7E3C61F9 ON "company" (owner_id)');
        $this->addSql('CREATE TABLE "payment_transaction" (id INT NOT NULL, payer_id INT NOT NULL, payment_reference UUID NOT NULL, checkout_session VARCHAR(255) NOT NULL, amount VARCHAR(255) DEFAULT NULL, currency VARCHAR(5) DEFAULT NULL, payment_mode VARCHAR(255) DEFAULT NULL, payment_for VARCHAR(255) DEFAULT NULL, payment_status VARCHAR(255) DEFAULT NULL, payment_type VARCHAR(255) DEFAULT NULL, payment_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, modified_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, beneficiary INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_84BBD50BC17AD9A9 ON "payment_transaction" (payer_id)');
        $this->addSql('COMMENT ON COLUMN "payment_transaction".payment_reference IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE "staff" (id INT NOT NULL, company_id INT NOT NULL, email VARCHAR(255) DEFAULT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, place_of_birth VARCHAR(255) DEFAULT NULL, date_of_birth TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, nationality VARCHAR(255) DEFAULT NULL, sex VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, modified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_426EF392979B1AD6 ON "staff" (company_id)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, place_of_birth VARCHAR(255) DEFAULT NULL, date_of_birth TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, nationality VARCHAR(255) DEFAULT NULL, sex VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, commune VARCHAR(255) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, modified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE "company" ADD CONSTRAINT FK_4FBF094F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "payment_transaction" ADD CONSTRAINT FK_84BBD50BC17AD9A9 FOREIGN KEY (payer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "staff" ADD CONSTRAINT FK_426EF392979B1AD6 FOREIGN KEY (company_id) REFERENCES "company" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "staff" DROP CONSTRAINT FK_426EF392979B1AD6');
        $this->addSql('ALTER TABLE "company" DROP CONSTRAINT FK_4FBF094F7E3C61F9');
        $this->addSql('ALTER TABLE "payment_transaction" DROP CONSTRAINT FK_84BBD50BC17AD9A9');
        $this->addSql('DROP SEQUENCE "company_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE "payment_transaction_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE "staff_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('DROP TABLE "company"');
        $this->addSql('DROP TABLE "payment_transaction"');
        $this->addSql('DROP TABLE "staff"');
        $this->addSql('DROP TABLE "user"');
    }
}
